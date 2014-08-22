<?php 
/**
 * @author Pavel Pisklakov
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package auth/woo2moodle
 * @version 0.2
 * Based on Tim St.Clair's wp2moodle plugin
 *
 * Moodle-end component of the Woo2Moodle Wordpress plugin.
 * Accepts user details passed across from Wordpress, creates a user in Moodle, authenticates them, and enrols them in the specified Cohort(s) or Group(s)
 *
**/


global $CFG, $USER, $SESSION, $DB;

require('../../config.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot.'/group/lib.php');

// logon may somehow modify this
$SESSION->wantsurl = $CFG->wwwroot.'/';

// $PASSTHROUGH_KEY = "the quick brown fox humps the lazy dog"; // must match woo2moodle wordpress plugin setting
$PASSTHROUGH_KEY = get_config('auth/woo2moodle', 'sharedsecret');
if (!isset($PASSTHROUGH_KEY)) {
	echo "Sorry, this plugin has not yet been configured. Please contact the Moodle administrator for details.";
}

/**
 * Handler for decrypting incoming data (specially handled base-64) in which is encoded a string of key=value pairs
 */
function decrypt_string($base64, $key) {
	if (!$base64) { return ""; }
	$data = str_replace(array('-','_'),array('+','/'),$base64); // manual de-hack url formatting
    $mod4 = strlen($data) % 4; // base64 length must be evenly divisible by 4
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $crypttext = base64_decode($data);
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key.$key), $crypttext, MCRYPT_MODE_ECB, $iv);
	return trim($decrypttext);
}

/**
 * querystring helper, returns the value of a key in a string formatted in key=value&key=value&key=value pairs, e.g. saved querystrings
 */
function get_key_value($string, $key) {
    $list = explode( '&', $string);
    foreach ($list as $pair) {
    	$item = explode( '=', $pair);
		if (strtolower($key) == strtolower($item[0])) {
			return urldecode($item[1]); // not for use in $_GET etc, which is already decoded, however our encoder uses http_build_query() before encrypting
		}
    }
    return "";
}

// truncate_userinfo requires and returns an array
// but we want to send in and return a user object
function truncate_user($userobj) {
	$user_array = truncate_userinfo((array) $userobj);
	$obj = new stdClass();
	foreach($user_array as $key=>$value) {
	    $obj->{$key} = $value;
	}
	return $obj;
}

$rawdata = $_GET['data'];

if (!empty($_GET)) {

	// get the data that was passed in
	$userdata = decrypt_string($rawdata, $PASSTHROUGH_KEY);

	// time (in minutes) before incoming link is considered invalid
	$timeout = (integer) get_config('auth/woo2moodle', 'timeout');
	if ($timeout == 0) { $timeout = 5; }

	// check the timestamp to make sure that the request is still within a few minutes of this servers time
	// if userdata didn't decrypt, then timestamp will = 0, so following code will be bypassed anyway (e.g. bad data)
	$timestamp = (integer) get_key_value($userdata, "stamp"); // remote site should have set this to new DateTime("now").getTimestamp(); which is a unix timestamp (utc)
	$theirs = new DateTime("@$timestamp"); // @ format here: http://www.gnu.org/software/tar/manual/html_node/Seconds-since-the-Epoch.html#SEC127
	$diff = floatval(date_diff(date_create("now"), $theirs)->format("%i")); // http://www.php.net/manual/en/dateinterval.format.php
	
	if ($timestamp > 0 && $diff <= $timeout) { // less than N minutes passed since this link was created, so it's still ok
		
		$username = trim(strtolower(get_key_value($userdata, "username"))); // php's tolower, not moodle's
		$hashedpassword = get_key_value($userdata, "passwordhash");
		$firstname = get_key_value($userdata, "firstname"); if (empty($firstname)===true) { $firstname = 'no-firstname'; }
		$lastname = get_key_value($userdata, "lastname"); if (empty($lastname)===true) { $lastname = 'no-lastname'; }
		$city = get_key_value($userdata, "city"); if (empty($city)===true) { $city = 'no-city'; }
		$country = get_key_value($userdata, "country");
		$email = get_key_value($userdata, "email");
		$idnumber = get_key_value($userdata, "idnumber"); // the users id in the wordpress database, stored here for possible user-matching
		$cohort = get_key_value($userdata, "cohort"); // the cohort to map the user user; these can be set as enrolment options on one or more courses, if it doesn't exist then skip this step
		$group = get_key_value($userdata, "group");
		$auth_type = get_key_value($userdata, "auth"); // where user should be authenticate - ldap or wordpress
		$updatefields = (get_key_value($userdata, "updatable") != "false");	// if true or not set, update fields like email, username, etc.
		
		// mdl_user.idnumber is the wordpress wp_users.id
		// TODO: if (get_field('user', 'id', 'username', $username, 'deleted', 1, '')) ----> error since the user is now deleted

//		if ($DB->record_exists('user', array('username'=>$username))) { // update user
			if($auth_type == 'woo')
				$username = trim(strtolower($email));

			if ($updateuser = get_complete_user_data('username', $username)) { // update user
				switch($updateuser->auth){
					case 'manual':
					case 'wp2moodle':
					case 'woo2moodle':
						// update manually created user that has the same username
						$updateuser->profile['wooid'] = $idnumber;
						$updateuser->auth = 'woo2moodle';
						if ($updatefields) {
							$updateuser->email = $email;
							$updateuser->firstname = $firstname;
							$updateuser->lastname = $lastname;
							$updateuser->city = $city;
							$updateuser->country = $country;
							// don't update password, we don't know it
						}
						break;
					case 'ldap':
					case 'univeris':
						$updateuser->profile['wooid'] = $idnumber;
						// don't update auth (ldap & univeris are main sources of authentication)						
						if ($updatefields) {
							$updateuser->email = $email;
							$updateuser->city = $city;
							$updateuser->country = $country;
							// don't update firstname, it should be updated through ldap only
							// don't update lastname, it should be updated through ldap only
							// don't update password, we don't know it
						}
						break;
				}

				// make sure we haven't exceeded any field limits
				$updateuser = truncate_user($updateuser); // typecast obj to array, works just as well

				$updateuser->timemodified = time(); // record that we changed the record
				$DB->update_record('user', $updateuser);
				//Save wooid 
				profile_save_data((object) $updateuser);

				// trigger correct update event
//        	    events_trigger('user_updated', $DB->get_record('user', array('id'=>$updateuser->id)));
			    // Trigger event.
    			\core\event\user_updated::create_from_userid($updateuser->id)->trigger();

				
				// ensure we have the latest data
				$user = get_complete_user_data('id', $updateuser->id);

			} else { // create new user
				//code based on moodlelib.create_user_record($username, $password, 'manual')
				if ($auth_type == 'ldap') 
					$auth = 'ldap'; // so they log in with ldap plugin
				else if($auth_type == 'woo') 	
					$auth = 'woo2moodle'; // so they log in with this plugin
			    $authplugin = get_auth_plugin($auth);
			    $newuser = new stdClass();
				if ($newinfo = $authplugin->get_userinfo($username)) {
					$newinfo = truncate_user($newinfo);
					foreach ($newinfo as $key => $value){
				    	$newuser->$key = $value;
					}
				}
	
			    if (!empty($newuser->email)) {
			        if (email_is_not_allowed($newuser->email)) {
		    	        unset($newuser->email);
		        	}
			    }
			    if (!isset($newuser->city)) {
			        $newuser->city = '';
		    	}
			    $newuser->auth = $auth;
				$newuser->policyagreed = 1;
//				$newuser->idnumber = $idnumber;
			    $newuser->username = $username;
		        $newuser->password = md5($hashedpassword); // manual auth checks password validity, so we need to set a valid password

		        // $DB->set_field('user', 'password',  $hashedpassword, array('id'=>$user->id));
   				$newuser->firstname = $firstname;
				$newuser->lastname = $lastname;
				$newuser->email = $email;
				$newuser->city = $city;
				$newuser->country = $country;
			    if (empty($newuser->lang) || !get_string_manager()->translation_exists($newuser->lang)) {
		    	    $newuser->lang = $CFG->lang;
			    }
			    $newuser->confirmed = 1; // don't want an email going out about this user
			    $newuser->lastip = getremoteaddr();
		    	$newuser->timecreated = time();
			    $newuser->timemodified = $newuser->timecreated;
			    $newuser->mnethostid = $CFG->mnet_localhost_id;

				// make sure we haven't exceeded any field limits
				$newuser = truncate_user($newuser);
	
			    $newuser->id = $DB->insert_record('user', $newuser);
	
			    $user = get_complete_user_data('id', $newuser->id);

//			    events_trigger('user_created', $DB->get_record('user', array('id'=>$user->id)));
			    // Trigger event.
    			\core\event\user_created::create_from_userid($newuser->id)->trigger();
			}
			
		// if we can find a cohortid matching what we sent in, enrol this user in that cohort by adding a record to cohort_members
		if (!empty($cohort)) {
			$ids = explode(',',$cohort);
			foreach ($ids as $cohort) {
				if ($DB->record_exists('cohort', array('idnumber'=>$cohort))) {
			        $cohortrow = $DB->get_record('cohort', array('idnumber'=>$cohort));
					if (!$DB->record_exists('cohort_members', array('cohortid'=>$cohortrow->id, 'userid'=>$user->id))) {
						// internally triggers cohort_member_added event
						cohort_add_member($cohortrow->id, $user->id);
					}
					
					// if the plugin auto-opens the course, then find the course this cohort enrols for and set it as the opener link
					if (get_config('auth/woo2moodle', 'autoopen') == 'yes')  {
				        if ($enrolrow = $DB->get_record('enrol', array('enrol'=>'cohort','customint1'=>$cohortrow->id,'status'=>0))) {
							$SESSION->wantsurl = new moodle_url('/course/view.php', array('id'=>$enrolrow->courseid));
						}
					}
				}
			}
		}

		// also optionally find a groupid we sent in, enrol this user in that group, and optionally open the course
		if (!empty($group)) {
			$ids = explode(',',$group);
			foreach ($ids as $group) {
				if ($DB->record_exists('groups', array('idnumber'=>$group))) {
			        $grouprow = $DB->get_record('groups', array('idnumber'=>$group));
					if (!$DB->record_exists('groups_members', array('groupid'=>$grouprow->id, 'userid'=>$user->id))) {
						// internally triggers groups_member_added event
						groups_add_member($grouprow->id, $user->id); //  not a component ,'enrol_wp2moodle');
					}
					
					// if the plugin auto-opens the course, then find the course this group is for and set it as the opener link
					if (get_config('auth/woo2moodle', 'autoopen') == 'yes')  {
						$SESSION->wantsurl = new moodle_url('/course/view.php', array('id'=>$grouprow->courseid));
					}
				}
			}
		}	
		
		// all that's left to do is to authenticate this user and set up their active session
		switch($auth_type){
			case 'ldap':
				$authplugin = get_auth_plugin('ldap'); // so they log in with ldap plugin
				break;
			case 'woo':
			    $authplugin = get_auth_plugin('woo2moodle'); // me!
				break;
		}

//	    $authplugin = get_auth_plugin('woo2moodle'); // me!
		if ($authplugin->user_login($user->username, null)) {
			$user->loggedin = true;
			$user->site     = $CFG->wwwroot;
			complete_user_login($user);

//	        add_to_log(SITEID, 'user', 'login', "view.php?id=$user->id&course=".SITEID,$user->id, 0, $user->id);
			// Trigger login event.
		    $event = \core\event\user_loggedin::create(
        		array(
            		'userid' => $user->id,
		            'objectid' => $user->id,
		            'other' => array('username' => $user->username),
        		)                  
		    );                     
		    $event->trigger();
		}
		
	} else {
		redirect($SESSION->wantsurl, "Sorry, this link is not valid. Timeout");
	}
	
}

// redirect to the homepage
redirect($SESSION->wantsurl);
?>

<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for Woocommerce/Moodle Single Sign On plugin
 *
 * @package   auth_woo2moodle
 * @copyright 2014 onwards Pavel Pisklakov
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Based on Tim St.Clair's wp2moodle plugin (http://timstclair.me)
 */

$string['auth_woo2moodle_secretkey'] = 'Encryption key';
$string['auth_woo2moodle_secretkey_desc'] = 'Must match Wordpress plugin setting';

$string['auth_woo2moodledescription'] = 'Uses Wordpress Woocommerce user details to create user & log onto Moodle';
$string['pluginname'] = 'WooCommerce 2 Moodle (SSO)';

$string['auth_woo2moodle_timeout'] = 'Link timeout';
$string['auth_woo2moodle_timeout_desc'] = 'Minutes before incoming link is considered invalid (allow for reading time on Wordpress page or in e-mail)';

$string['auth_woo2moodle_logoffurl'] = 'Logoff Url';
$string['auth_woo2moodle_logoffurl_desc'] = 'Url to redirect to if the user presses Logoff';

$string['auth_woo2moodle_autoopen_desc'] = 'Automatically open the cohorts first course after successful auth';
$string['auth_woo2moodle_autoopen'] = 'Auto open course?';

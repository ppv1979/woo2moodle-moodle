woo2moodle-moodle
=================

This is the Moodle-end of a two-part plugin that allows users to authenticate within WordPress and open a Moodle site. To get the Wordpress-end plugin, check this git: https://github.com/ppv1979/woo2moodle-wordpress

Data is encrypted at the Wordpress end and handed over a standard http GET request. Only the minimum required information is sent in order to create a Moodle user record. The user is automatically created if not present at the Moodle end, and then authenticated, and (optionally) enrolled in a Cohort, a Group, or both..

How to install this plugin
---------------------
Note, this plugin must exist in a folder named "woo2moodle" - rename the zip file or folder before you upload it.

1. Upload/extract this to your moodle/auth folder (should be called "/~/auth/woo2moodle/", where ~ is your Moodle root)
2. Activate the plugin in the administration / authentication section
3. Click settings and enter the same shared secret that you enter for the woo2moodle settings in Wordpress
4. The logoff url will perform a Moodle logout, then redirect to this url. You can get it to log off in Wordpress as well by hitting the wordpress-end logout page too; typically this is http://<your-wordpress-url/wp-login.php?action=logout
5. The link timeout is the number of minutes before the incoming link is thought to be invalid (to allow for variances in server times). This means links that were generated in the past can't be re-used, copied, bookmarked, etc.
5. Disable any other authentication methods as required. You can still use as many as you like.

Usage:
------
You can not use this plugin directly; it is launched by woo2moodle from within Wordpress.

Licence:
--------
GPL2, as per Moodle.


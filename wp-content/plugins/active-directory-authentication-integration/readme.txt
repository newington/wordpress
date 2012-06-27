=== Active Directory Authentication Integration ===
Contributors: cgrymala
Tags: active directory, ldap, login, authentication
Requires at least: 3.0
Tested up to: 3.2.1
Donate link: http://www.umw.edu/gift/make_a_gift/
Stable tag: 0.6

Allows WordPress to authenticate, authorize, create and update users through Active Directory

== Description ==

This plugin allows WordPress to authenticate, authorize, create and update against an Active Directory domain. This plugin is based heavily on the [Active Directory Integration](http://wordpress.org/extend/plugins/active-directory-integration/) plugin, but has been modified to work with Multi Site and even Multi Network installations of WordPress.

Some of the features included in this plugin are:

* authenticate against more than one AD Server (for balanced load)
* authorize users by Active Directory group memberships
* auto create and update users that can authenticate against AD
mapping of AD groups to WordPress roles
* use TLS (or LDAPS) for secure communication to AD Servers (recommended)
* use non standard port for communication to AD Servers
* protection against brute force attacks
* user and/or admin e-mail notification on failed login attempts
* determine WP display name from AD attributes (sAMAccountName, displayName, description, SN, CN, givenName or mail)
* enable/disable password changes for local (non AD) WP users
* WordPress 3.0/3.1 compatibility, including Multi Site and Multi Network

This plugin is based on [glatze's Active Directory Integration](http://wordpress.org/extend/plugins/active-directory-integration/) plugin, which is based upon [Jonathan Marc Bearak's Active Directory Authentication](http://wordpress.org/extend/plugins/active-directory-authentication/) plugin and [Scott Barnett's adLDAP](http://adldap.sourceforge.net/), a very useful PHP class.

Aside from the changes to make this plugin work more effectively with WordPress Multi Site, this version of the plugin also encrypts the password used to connect to the AD server when it is stored in the database.

This plugin was developed by [Curtiss Grymala](http://wordpress.org/extend/plugins/profile/cgrymala) for the [University of Mary Washington](http://umw.edu/). It is licensed under the GPL2, which basically means you can take it, break it and change it any way you want, as long as the original credit and license information remains somewhere in the package.

== Important Notice ==

Since I don't currently have access to multiple AD servers, this plugin has only been tested on a single installation of WordPress with a single AD server. Therefore, it is entirely possible that there are major bugs.

At this time, I am seeking people to test the plugin, so please report any issues you encounter.

== Requirements ==

* This plugin requires WordPress. It might work with versions older than 3.0, but it has not been tested with those.

* This plugin also requires PHP5. Some attempt has been made to make it compatible with PHP4, but it has not been tested in that environment.

* This plugin requires LDAP support to be compiled into PHP. If the `ldap_connect()` function is not available, this plugin will output an error message and will not do anything.

== Installation ==

1. Download the latest ZIP file of this plugin
1. Unzip the file and upload the active-directory-authentication-integration directory to the wp-content/plugins/ folder on your Web server
1. Network Activate the plugin and adjust the settings
1. If you have [John James Jacoby's WP Multi Network plugin](http://wordpress.org/extend/plugins/wp-multi-network/), [David Dean's Networks for WordPress](http://wordpress.org/extend/plugins/networks-for-wordpress/) or [Ron and Andrea Rennick's Networks+](http://wpebooks.com/networks/) installed and activated, you will then have the option to activate this plugin on all networks. Do so.
1. Adjust the settings

== To Do ==

* Add ability to validate against multiple AD servers (check one, then the other - rather than just load-balancing as the plugin currently does)
* DONE as of 0.4a - Update admin interface to utilize native meta box interface rather than custom layout
* DONE as of 0.3a - Separate the profile information from the role equivalent groups in the "auto update user" setting

== Upgrade Notice ==
= 0.6 =
This is a complete rewrite of the authentication system; using the newest version of the adLDAP class and using less of the original code from glatze's plugin. Please re-check your settings after upgrading the plugin.

= 0.5a =
If you previously updated to 0.4a, this is a critical update. 0.4a introduced a bug that stopped the options from being saved in certain circumstances. 0.5a corrects that issue.

= 0.2a =
There seemed to be a few major bugs in version 0.1a that are hopefully corrected in this version. Updating is highly recommended.

== Frequently Asked Questions ==

= Can I use this plugin if I'm not running Multi Site? =

You certainly can. This plugin should be fully compatible with a regular WordPress installation, a WordPress Multi Site installation and even a WordPress Multi Network installation.

= Why am I able to login using AD on one site, but not another in a multisite installation? =

This plugin will only affect sites on which it is activated. If you do not network-activate it in a multisite installation, you won't be able to login using AD credentials on any of the sites on which it's not activated. Likewise, if you are running a multi-network installation, the plugin will need to be network-activated on all of your networks (there is an option in the plugin once it's activated on one network to activate it on all networks) in order for login to check the Active Directory on all networks.

= Can I use this plugin for normal LDAP authentication? =

I'm honestly not sure. As far as I know, this plugin is only compatible with Active Directory servers, but it's possible it might work with other implementations of LDAP.

= Why am I seeing a message about LDAP not being supported? =

This plugin requires that LDAP support be compiled into PHP in order to work properly. If you are seeing that error message, it means that the plugin detected that the PHP `ldap_connect()` function is not available.

= Is it possible to use TLS with a self-signed certificate on the AD server? =

Yes, this works. But you have to add the line `TLS_REQCERT` never to your ldap.conf on your web server. If you don't already have one, create it. On Windows systems the path should be `c:\openldap\sysconf\ldap.conf`.

= Can I use LDAPS instead of TLS? =

Yes, you can. Just put `ldaps://` in front of the server in the option labeled "Domain Controller" (e.g. `ldaps://dc.domain.tld`), enter 636 as port and deactivate the option "Use secure connection?".

= Why do I see "Should this set of options be updated for all of your networks?" at the top of each settings section? =

That means that this plugin detected that you have either the WP Multi Network plugin or the Networks for WordPress plugin installed and activated. If you leave this checkbox ticked, any changes you make to that section of settings will be saved on all of the networks, rather than just being saved on the current network.

If you do not have either plugin installed and activated, you should not see this option. If you do, that is a bug and should be reported.

= Why do I see the checkbox mentioned above on one network, but not another? =

Again, that option will only appear on sites where the Multi Network or Networks for WordPress plugin is active. If you only have that plugin activated on a single site, this AD Authentication Integration plugin will have no way of knowing that you are running multiple networks.

= How do I request new features or report a bug with this plugin? =

Please either start a new topic in the [official WordPress support forums](http://wordpress.org/tags/active-directory-authentication-integration) or make a comment on the appropriate post within [my plugins blog](http://plugins.ten-321.com/active-directory-authentication-integration/).

= How do I enable debug information? =

There are multiple levels of debug information within this plugin, all carried over from glatze's plugin. To set the debug level, find the following line in the active-directory-authentication-integration.php file:
`$ADAuthIntObj->setLogLevel(ADAI_LOG_NONE);`
and change it to:
`$ADAuthIntObj->setLogLevel(ADAI_LOG_DEBUG);`
Other than "none" and "debug", there are 5 other levels of debug information. ADAI_LOG_DEBUG is the highest level, meaning that all debug information output from this plugin will be displayed on-screen. ADAI_LOG_NONE is the lowest level, meaning that no information will be displayed on the screen. The levels of logging are (from highest to lowest):

* ADAI_LOG_DEBUG
* ADAI_LOG_INFO
* ADAI_LOG_NOTICE
* ADAI_LOG_WARN
* ADAI_LOG_ERROR
* ADAI_LOG_FATAL
* ADAI_LOG_NONE

== Screenshots ==

1. The opening page of the admin settings area for this plugin. This is where you will input information about the AD server against which you're authenticating (shown running on WordPress 3.2-bleeding).
2. The "User" settings area for this plugin. This is where you will set up the way users are created in WordPress and how user accounts are handled (shown running on WordPress 3.2-bleeding).
3. The "Authorization" settings area for this plugin. This is where you will indicate whether or not users from specific AD groups should be the only ones able to login with AD credentials; as well as indicating which AD groups should match up with which WordPress user roles (shown running on WordPress 3.2-bleeding).
4. The "Security" settings area for the plugin, where you indicate how many failed login attempts should cause the user account to be locked out, and for how long (shown running on WordPress 3.2-bleeding).

== Changelog ==
= 0.6 =
* Updated adLDAP class to latest version
* Added ability to authenticate against multiple servers in succession, rather than just load-balancing against mirrors
* Updated a lot of the labels and language strings to be more explanatory
* Added new options to Display Name selector
* Added option to randomize user's WordPress password each time they login through AD
* Rewrote entire authentication system
* Added ability to auto-add users to other sites in multisite/multinetwork (assuming they are already signed into another site in the install)
* Fixed potential bug/issue in user role equivalency
* Added options page to individual sites in multisite environment for mapping groups to roles and authorizing by AD group
* Moved failed logins from separate database table to native WordPress transients
* Added custom error messages for login failures
* Added option to display custom message when user attempts to reset/retrieve lost password

= 0.5a =
* Updated some of the labels on the options screen to make them a little more explanatory.
* Fixed a critical bug that stopped the options from being saved in certain situations.
* Updated options page to use HTML label elements properly for each field
* Tested for compatibility with WordPress 3.2

= 0.4a =
* Updated administrative user interface to use native WordPress metaboxes

= 0.3a =
* Separated the option to append user suffixes during validation against the AD server and appending user suffixes to the WordPress account username (previously, if you appended user suffixes to the WordPress account username, that suffix was also used in the validation process; which caused validation to fail on some AD servers). There are now two separate setting for "WordPress account suffix" and "AD Account Suffix".
* Updated the way "automatic user update" is handled. Previously, if you had automatic user update enabled, and you had role-equivalent settings configured, all users that matched those role-equivalent settings would be given those roles when they logged in; even if you had previously promoted a specific user to a higher WordPress role. You now have the option to enable that feature or not; separately from the setting that updates the user's contact information on login.

= 0.2a =
* Updated the way scripts and styles are registered within the plugin
* Added support for [Networks for WordPress multi-network plugin](http://wordpress.org/extend/plugins/networks-for-wordpress/) and [Networks+ multi-network plugin](http://wpebooks.com/networks/)
* Hopefully fixed bug that caused existing users to not be able to login with AD credentials
* Updated the way multi-network plugins are detected, allowing the plugin to identify multi-network setups even when the multi-network plugin is only active on one network
* Included AD Connection Test script (a modified version) from glatze's plugin for testing/debugging purposes
* Added more debug information
* Hopefully fixed a bug in authorization by AD groups
* Fixed a bug that caused admins not to be able to configure plugin in non-Multisite installations

= 0.1a =
* This is the first version

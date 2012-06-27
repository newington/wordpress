<?php
/**
 * Default options, their definitions and values for the 
 * 		Active Directory Authentication Integration plugin
 * 		for WordPress
 * @package WordPress
 * @subpackage ADAuthInt
 * @version 0.6
 */
$default_options_info = array(
	'adauthint_server_opts'	=> array(
		/**
		 * List of servers to bind against
		 */
		'domain_controllers'	=> array(
			'opt_type'		=> 'string',
			'opt_default'	=> NULL,
			'opt_val'		=> $this->domain_controllers,
			'opt_label'		=> __( 'Domain controllers', ADAUTHINT_TEXT_DOMAIN ),
			'opt_note'		=> __( 'The list of domain controllers against which to authenticate users. Separate multiple controllers with semicolons, e.g. "dc1.domain.tld;dc2.domain.tld".', ADAUTHINT_TEXT_DOMAIN ),
		),
		/**
		 * Whether to randomize the bind or not
		 * @since 0.6
		 */
		'randomize_dc'		=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> true,
			'opt_val'		=> $this->randomize_dc,
			'opt_label'		=> __( 'Randomize the domain controllers?', ADAUTHINT_TEXT_DOMAIN ),
			'opt_note'		=> __( 'If you included multiple domain controllers above, would you like them to be used to load-balance (a single controller in the list is bound randomly - assumes that all of the controllers contain the same information) or recursively bound (each controller is bound one at a time until either the user\'s information is found or the list of controllers is exhausted)? If checked, the domain controllers will be randomized; if unchecked, the list will be handled recursively.', ADAUTHINT_TEXT_DOMAIN ),
			'opt_choices'	=> array(
				0	=> 'Randomize',
				1	=> 'Recurse',
			),
		),
		/**
		 * Which port to bind on
		 */
		'port'				=> array(
			'opt_type'		=> 'int',
			'opt_default'	=> 389,
			'opt_val'		=> $this->port,
			'opt_label'		=> __( 'Port on which the AD listens', ADAUTHINT_TEXT_DOMAIN ),
			'opt_note'		=> __( 'Defaults to 389 for non-SSL requests; defaults to 636 for SSL requests.', ADAUTHINT_TEXT_DOMAIN ),
		),
		/**
		 * Whether or not to use SSL during the bind
		 * @since 0.6
		 */
		'use_ssl'			=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> false,
			'opt_val'		=> $this->use_ssl,
			'opt_label'		=> __( 'Use SSL?', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'Secure the connection by binding through <strong>SSL</strong> (if enabled, and the port above is left empty, the port will default to 636)?', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * Whether or not to use TLS after bind
		 */
		'secure_connection'		=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> false,
			'opt_val'		=> $this->secure_connection,
			'opt_label'		=> __( 'Use TLS?', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'Secure the connection between the WordPress and the Active Directory Servers using <strong>TLS</strong> after the initial bind is made?', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * The user string to use to bind to the server
		 */
		'bind_user' 			=> array(
			'opt_type'		=> 'string',
			'opt_default'	=> NULL,
			'opt_val'		=> $this->bind_user,
			'opt_label'		=> __( 'Bind user string', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'User string to use when connecting to the Active Directory server. If an account suffix is required to bind to the server, include that in the user string. This option should either look like "username" or "username@example.local". Leave empty for anonymous requests.', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * The password to use when binding to the AD server
		 */
		'bind_user_password'	=> array(
			'opt_type'		=> 'password',
			'opt_default'	=> NULL,
			'opt_val'		=> $this->bind_user_password,
			'opt_label'		=> __( 'Bind user password', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'Leave this field empty for anonymouse requests.', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * Base DN to append to the bind string
		 */
		'base_dn' 				=> array(
			'opt_type'		=> 'string',
			'opt_default'	=> NULL,
			'opt_val'		=> $this->base_dn,
			'opt_label'		=> __( 'Base DN', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'The distinguished name string used to bind to the domain controller (e.g., "ou=people,dc=example,dc=local")', ADAUTHINT_TEXT_DOMAIN ), 
		),
	),
	'adauthint_user_opts' 	=> array(
		/**
		 * Whether or not to automatically create new users
		 */
		'auto_user_create' 		=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> true,
			'opt_val'		=> $this->auto_user_create,
			'opt_label'		=> __( 'Automatically create new user?', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> array(
				__( 'Should a new user be created automatically if not already in the WordPress database?', ADAUTHINT_TEXT_DOMAIN ), 
				__( 'Created users will obtain the role defined under "New User Default Role" on the <a href="options-general.php">General Options</a> page.', ADAUTHINT_TEXT_DOMAIN ), 
				__( 'This setting is separate from the Role Equivalent Groups option, below.', ADAUTHINT_TEXT_DOMAIN ), 
				__( '<strong>Users with role equivalent groups will be created even if this setting is turned off</strong> (because if you didn\'t want this to happen, you would leave that option blank.)', ADAUTHINT_TEXT_DOMAIN ), 
			),
		),
		/**
		 * Whether to automatically update a user's contact information
		 */
		'auto_user_update'		=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> false,
			'opt_val'		=> $this->auto_user_update,
			'opt_label'		=> __( 'Automatically update users on each login?', ADAUTHINT_TEXT_DOMAIN ),
			'opt_note'		=> array(
				__( 'Should the users\' contact information be updated in the WordPress database everytime they login?', ADAUTHINT_TEXT_DOMAIN ), 
				__( '<strong>Works only if Automatic User Creation is turned on.</strong>', ADAUTHINT_TEXT_DOMAIN ), 
			),
		),
		/**
		 * The default email suffix to use when a user's mail attribute is empty
		 */
		'default_email_domain'	=> array(
			'opt_type'		=> 'string',
			'opt_default'	=> NULL,
			'opt_val'		=> $this->default_email_domain,
			'opt_label'		=> __( 'Default email domain', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'If the Active Directory attribute \'mail\' is blank, a user\'s email will be set to username@whatever-this-says. It is not necessary to include the "@" symbol in this field, as it will automatically be prepended to this string.', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * What to do if an email address already exists
		 */
		'dup_account_handling'	=> array(
			'opt_type'		=> 'select',
			'opt_default'	=> 'prevent',
			'opt_val'		=> $this->dup_account_handling,
			'opt_label'		=> __( 'Email Address Conflict Handling', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'Choose how to handle email address conflicts.<dl><dt>Prevent:</dt><dd>User is not created, if his email address is already in use by another user. (recommended)</dd><dt>Allow:</dt><dd>Allow users to share one email address. (UNSAFE)</dd><dt>Create:</dt><dd>In case of conflict, the new user is created with a unique email address.</dd></dl>', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_choices'	=> array(
					'prevent'	=> __( 'Prevent (recommended)', ADAUTHINT_TEXT_DOMAIN ), 
					'allow'		=> __( 'Allow (UNSAFE)', ADAUTHINT_TEXT_DOMAIN ), 
					'create'	=> __( 'Create', ADAUTHINT_TEXT_DOMAIN ), 
			),
		),
		/**
		 * Whether to append a suffix to new usernames created by this plugin
		 */
		'append_user_suffix'	=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> true,
			'opt_val'		=> $this->append_user_suffix,
			'opt_label'		=> __( 'Append account suffix to newly created usernames?', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'If checked, the account suffix (see below) will be appended to the usernames of newly created users. This setting does not affect the way users login; they will still just use their usernames to login to WordPress. It only changes the way the usernames are stored in the database and displayed on the site.', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * What account suffix to append
		 */
		'user_account_suffix'	=> array(
			'opt_type'		=> 'string',
			'opt_default'	=> NULL,
			'opt_val'		=> $this->user_account_suffix,
			'opt_label'		=> __( 'WordPress Account Suffix', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'If the option above is enabled, this suffix will be appended to all usernames when they are inserted into the WordPress database (helps distinguish between users that were automatically created by this plugin and users that were manually created through the WordPress interface)', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * Whether to append an AD suffix before authenticating
		 * @since 0.4a
		 */
		'append_ad_user_suffix'	=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> true,
			'opt_val'		=> $this->append_ad_suffix,
			'opt_label'		=> __( 'Append account suffix to AD usernames before being validated?', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'If checked, the AD account suffix (see below) will be appended to usernames before they are checked against the AD server (in some cases, it is necessary to append a suffix like "@example.local" to the username before it is authenticated).', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * What AD suffix to append
		 * @since 0.4a
		 */
		'ad_account_suffix'		=> array(
			'opt_type'		=> 'string',
			'opt_default'	=> NULL,
			'opt_val'		=> $this->ad_account_suffix,
			'opt_label'		=> __( 'AD Account Suffix', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'If the option above is enabled, this suffix will be appended to all usernames before they are checked against the AD server (generally something like "@example.local")', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * Which AD attribute to use as the user's display name
		 */
		'display_name'			=> array(
			'opt_type'		=> 'select',
			'opt_default'	=> 'samaccountname',
			'opt_val'		=> $this->display_name,
			'opt_label'		=> __( 'Display name', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'Choose user\'s Active Directory attribute to be used as display name.', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_choices'	=> array(
				'displayname'		=> __( 'displayName', ADAUTHINT_TEXT_DOMAIN ), 
				'firstlast'			=> __( 'givenName SN (first name then last name)', ADAUTHINT_TEXT_DOMAIN ), 
				'lastfirst'			=> __( 'SN, givenName (last name then first name)', ADAUTHINT_TEXT_DOMAIN ), 
				'samaccountname'	=> __( 'sAMAccountName (the username)', ADAUTHINT_TEXT_DOMAIN ), 
				'givenname'			=> __( 'givenName (firstname)', ADAUTHINT_TEXT_DOMAIN ), 
				'sn'				=> __( 'SN (lastname)', ADAUTHINT_TEXT_DOMAIN ), 
				'cn'				=> __( 'CN (Common Name, the whole name)', ADAUTHINT_TEXT_DOMAIN ), 
				'mail'				=> __( 'mail', ADAUTHINT_TEXT_DOMAIN ), 
				'description'		=> __( 'description', ADAUTHINT_TEXT_DOMAIN ), 
			),
		),
		/**
		 * Whether to allow a user to change their WordPress password
		 */
		'allow_local_password'	=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> false,
			'opt_val'		=> $this->append_user_suffix,
			'opt_label'		=> __( 'Enable local password changes', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> array(
				__( 'Allow users to change their local (<strong>non AD</strong>) WordPress password?', ADAUTHINT_TEXT_DOMAIN ), 
				__( '<strong>If activated, a password change will update the local WordPress database only. No changes in Active Directory will be made.</strong>', ADAUTHINT_TEXT_DOMAIN ), 
				__( 'If activated, the user will be able to login to WordPress using either their Active Directory password or the password they set locally.', ADAUTHINT_TEXT_DOMAIN ), 
			),
		),
		/**
		 * Message to display when a user tries to reset/retrieve their password
		 */
		'_lost_password_message'=> array(
			'opt_type'		=> 'textarea',
			'opt_default'	=> $this->_get_lost_password_message(),
			'opt_val'		=> $this->_lost_password_message,
			'opt_label'		=> __( 'Message to display when a user attempts to reset or retrieve their password', ADAUTHINT_TEXT_DOMAIN ),
			'opt_note'		=> __( 'This message is only displayed if local password changes are disabled (above). Otherwise, the default WordPress password retrieval/reset form is displayed.', ADAUTHINT_TEXT_DOMAIN ),
		),
		/**
		 * Whether the user's WordPress password should be randomized on each login
		 * @since 0.6
		 */
		'randomize_password'	=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> true,
			'opt_val'		=> $this->randomize_password,
			'opt_label'		=> __( 'Randomize passwords on login?', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'By default, this plugin inserts a random string as the user\'s local (WordPress) password when the user account is created. If this option is enabled, a new random string will be set as the user\'s local password each time they login, rather than just the first time; adding an extra layer of security by abstraction. <strong>This option should not be enabled if local password changes are enabled.</strong>', ADAUTHINT_TEXT_DOMAIN ), 
		),
	),
	'adauthint_auth_opts'	=> array(
		/**
		 * Whether to authorize users based on AD group membership
		 */
		'auth_from_ad_grp'		=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> false,
			'opt_val'		=> $this->auth_from_ad_grp,
			'opt_label'		=> __( 'Only allow members of the following groups to login through Active Directory?', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'If a user tries to login using Active Directory credentials and does not belong to any of the user groups listed below, they will not be authenticated.', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * Which groups to authorize against
		 */
		'auth_groups'			=> array(
			'opt_type'		=> 'string',
			'opt_default'	=> NULL,
			'opt_val'		=> $this->auth_groups,
			'opt_label'		=> __( 'Group(s) to allow:', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'If the option above is enabled, only members of the groups listed in this field will be allowed to login using Active Directory credentials. Please separate multiple groups by semicolon (e.g. "domain-users;WP-Users;test-users").', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * Whether or not to map AD groups to WP roles
		 * @since 0.6
		 */
		'use_role_equiv'		=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> false,
			'opt_val'		=> $this->use_role_equiv,
			'opt_label'		=> __( 'Map WordPress user roles to Active Directory groups?', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'If enabled, the user\'s role will be determined based on the AD groups to which the user belongs.', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * Which AD groups map to which WordPress roles
		 */
		'role_equiv_groups'		=> array(
			'opt_type'		=> 'string',
			'opt_default'	=> NULL,
			'opt_val'		=> $this->role_equiv_groups,
			'opt_label'		=> __( 'Role Equivalent Groups', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> array(
				__( 'List of Active Directory groups which correspond to WordPress user roles.', ADAUTHINT_TEXT_DOMAIN ), 
				__( 'When users are created, their roles will correspond to what is specified here. The first match found (left-to-right) among the AD Groups will be used to set the user\'s role, so make sure that the mapping is listed from most powerful (e.g. "administrator") to least powerful (e.g. "subscriber"). Custom roles created by themes or plugins should be fully compatible with this plugin.<br/>Format: AD-Group1=WordPress-Role1;AD-Group2=WordPress-Role2;...', ADAUTHINT_TEXT_DOMAIN ), 
				__( 'E.g., "Soc-Faculty=administrator" or "Soc-Faculty=administrator;Faculty=contributor;Students=subscriber"', ADAUTHINT_TEXT_DOMAIN ), 
				__( '<ol><li>WordPress stores roles as lower case ("Subscriber" is stored as "subscriber")</li><li>Active Directory groups are case-sensitive.</li><li>Group memberships cannot be checked across domains.  So if you have two domains, instr and qc, and qc is the domain specified above, if instr is linked to qc, this plugin can authenticate instr users, but not check instr group memberships.</li></ol>', ADAUTHINT_TEXT_DOMAIN ), 
			),
		),
		/**
		 * Whether or not to automatically update the user's WordPress role each time they login
		 * @since 0.4a
		 */
		'auto_update_user_group'	=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> false,
			'opt_val'		=> $this->auto_update_user_group,
			'opt_label'		=> __( 'Automatically update WordPress roles?', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> array(
				__( 'If checked, all users will automatically be reassigned to the appropriate "role equivalent group" (see above) <strong>every</strong> time they login.', ADAUTHINT_TEXT_DOMAIN ), 
				__( 'If left unchecked, users will only be assigned to the appropriate "role equivalent group" when they login to a site for the first time (i.e. when the WordPress user account is created).', ADAUTHINT_TEXT_DOMAIN ), 
				__( 'This setting will have no effect if the "automatically update users" option is unchecked above.', ADAUTHINT_TEXT_DOMAIN ), 
			),
		),
	),
	'adauthint_security_opts'	=> array(
		/**
		 * How many times should a user be allowed to try to login before account is temporarily locked?
		 */
		'max_login_attempts'	=> array(
			'opt_type'		=> 'int',
			'opt_default'	=> 3,
			'opt_val'		=> $this->max_login_attempts,
			'opt_label'		=> __( 'How many unsuccessful logins in a row should cause an account lockout?', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'Maximum number of failed login attempts before a user account is blocked. If empty or "0" Brute Force Protection is turned off.', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * How many seconds between unsuccessful login attempts is acceptable?
		 */
		/*'login_interval'		=> array(
			'opt_type'		=> 'int',
			'opt_default'	=> 10,
			'opt_val'		=> $this->login_interval,
			'opt_label'		=> __( ' times within: ', ADAUTHINT_TEXT_DOMAIN ),
			'opt_note'		=> __( 'How many seconds should be allowed between unsuccessful login attempts before the number of attempts is reset?', ADAUTHINT_TEXT_DOMAIN ),
		),*/
		/**
		 * How long should an account be locked
		 */
		'blocking_time' 		=> array(
			'opt_type'		=> 'int',
			'opt_default'	=> 30,
			'opt_val'		=> $this->blocking_time,
			'opt_label'		=> __( 'How long (in seconds) should the account be locked?', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'After reaching the maximum number of failed attempts', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * Whether or not to notify the user if their account is locked
		 */
		'notify_user'			=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> false,
			'opt_val'		=> $this->notify_user,
			'opt_label'		=> __( 'Notify the user when their account is locked?', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * Whether or not to notify the admin when an account is locked
		 */
		'notify_admin'			=> array(
			'opt_type'		=> 'bool',
			'opt_default'	=> false,
			'opt_val'		=> $this->notify_admin,
			'opt_label'		=> __( 'Notify an administrator when a user account is locked?', ADAUTHINT_TEXT_DOMAIN ), 
		),
		/**
		 * The admin email address to use for notifications
		 */
		'admin_email'			=> array(
			'opt_type'		=> 'string',
			'opt_default'	=> NULL,
			'opt_val'		=> $this->admin_email,
			'opt_label'		=> __( 'Administrator email address(es)', ADAUTHINT_TEXT_DOMAIN ), 
			'opt_note'		=> __( 'If specifying more than one email address, please separate them with a semi-colon (;).', ADAUTHINT_TEXT_DOMAIN ), 
		),
	),
);
?>
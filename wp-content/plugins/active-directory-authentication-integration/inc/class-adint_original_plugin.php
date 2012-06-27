<?php
/**
 * Base Class and Methods for AD Integration Plug-in
 * @package wordpress
 * @subpackage ADAuthInt
 * @version 0.6
 */
/*
 * Active Directory Integration Plug-in Class
 *
 * Various functions that were included in the original
 * 		AD Integration plug-in are defined within this class.
 * Each of these methods has been rewritten by Curtiss Grymala
 * 		to mesh better with the ADAuthInt plugin and to use the
 * 		latest version of the adLDAP PHP class
 */
class ADInt_Original_Plugin {
/**
 * ===============================================================
 * = Additional functions to be pulled into the ADAuthInt_Plugin =
 * = class.                                                      =
 * ===============================================================
 */

	const TABLE_NAME = 'adauthint';
	
	/**
	 * Establish a new bind to the AD server
	 * @param array $options the array of AD options to pass to the new object
	 * @return mixed the resource handle for the bind
	 */
	public function _ad_connect( $options = array() ) {
		// Connect to Active Directory
		try {
			$conn = @new adLDAPE( $options );
		} catch ( Exception $e ) {
    		$this->_log( ADAI_LOG_ERROR, 'adLDAP exception: ' . $e->getMessage() );
			$this->_log_flush();
    		return false;
		}
		return $conn;
	}
	
	/**
	 * Wrapper
	 * 
	 * @param $arg1 WP_User or username
	 * @param $arg2 username or password
	 * @param $arg3 passwprd or empty
	 * @return WP_User
	 * @uses ADAuthInt_Plugin::ad_authenticate()
	 * @uses ADAI_LOG_INFO
	 * @uses $wp_version
	 */
	public function authenticate($arg1 = NULL, $arg2 = NULL, $arg3 = NULL) {
		global $wp_version;
		
		$this->_log(ADAI_LOG_INFO,'method authenticate() called');
		
		$this->_log(ADAI_LOG_INFO,'WP version: ' . $wp_version);
		
		if (version_compare($wp_version, '2.8', '>=')) {
			return $this->ad_authenticate($arg1, $arg2, $arg3); 
		} else {
			return $this->ad_authenticate(NULL, $arg1, $arg2);
		}
	} /* Wrapper function */
	
	protected function _set_adldap() {
		// Load options from WordPress-DB.
		$this->_load_options();
		
		/* Separate the domain controllers into an array, then set the options that will be sent to the ADLDAPE class */
		$dc = explode( ';', $this->domain_controllers );
		if( !isset( $this->dc_index ) )
			$this->dc_index = 0;
		
		$LDAP_Options = array(
			'base_dn'				=> $this->base_dn, 
			'ad_username'			=> $this->bind_user,
			'ad_password'			=> $this->bind_user_password,
			'ad_port'				=> $this->port,
			'use_tls'				=> $this->secure_connection,
			'use_ssl'				=> $this->use_ssl,
			'account_suffix'		=> $this->append_ad_user_suffix ? $this->ad_account_suffix : '',
		);
		
		if( $this->randomize_dc ) {
			$LDAP_Options['domain_controllers'] = $dc;
			
			// Log some connection information
			$this->_log( ADAI_LOG_INFO, "Options for adLDAP connection:\n" .
				"'base_dn'				=> $this->base_dn, \n" . 
				"'domain_controllers'	=> " . implode( ';', $dc ) . ", \n" .
				"'ad_username'			=> $this->bind_user, \n" . 
				"'ad_password'			=> $this->bind_user_password, \n" .
				"'ad_port'				=> $this->port, \n" .
				"'use_tls'				=> $this->secure_connection, \n" . 
				"'use_ssl'				=> $this->use_ssl, \n" . 
				"'account_suffix'		=> " . ( $this->append_ad_user_suffix ? $this->ad_account_suffix : '' ) . ", \n"
			);
			
			$this->_adldap = $this->_ad_connect( $LDAP_Options );
		} else {
			$this->_adldap = array();
			foreach( $dc as $k=>$d ) {
				$LDAP_Options['domain_controllers'] = array( $d );
				$this->_adldap[$k] = $this->_ad_connect( $LDAP_Options );
			}
		}
	}
	
	/**
	 * Return a WP_Error to be displayed on the login page
	 */
	function generate_error_not_in_group() {
		return new WP_Error( 'not_in_group', __( '<strong>ERROR:</strong> The username provided is not a member of the groups that are allowed to access this site', ADAUTHINT_TEXT_DOMAIN ) );
	}
	
	/**
	 * Return a WP_Error to be displayed on the login page
	 */
	function generate_error_not_allowed() {
		return new WP_Error( 'not_allowed', __( '<strong>ERROR</strong>: This user exists in Active Directory, but has not been granted access to this installation of WordPress.', ADAUTHINT_TEXT_DOMAIN ) );
	}
	
	/**
	 * Return a WP_Error to be displayed on the login page
	 */
	function generate_error_no_ad_connection() {
		return new WP_Error( 'no_ad_connection', __( '<strong>ERROR</strong>: A connection to the Active Directory server could not be established', ADAUTHINT_TEXT_DOMAIN ) );
	}
	
	function generate_error_incorrect_password() {
		return new WP_Error( 'incorrect_password', __( '<strong>ERROR</strong>: Either the username or password provided was incorrect.', ADAUTHINT_TEXT_DOMAIN ) );
	}
	
	/**
	 * Add our custom errors to the array of errors that cause the screen to shake
	 */
	function add_error_shakes( $errors ) {
		array_push( $errors, 'not_in_group', 'not_allowed', 'no_ad_connection' );
		return $errors;
	}

	/**
	 * If the REMOTE_USER evironment is set, use it as the username.
	 * This assumes that you have externally authenticated the user.
	 * / Options /
	 * @uses ADAuthInt_Plugin::user_account_suffix
	 * @uses ADAuthInt_Plugin::base_dn
	 * @uses ADAuthInt_Plugin::domain_controllers
	 * @uses ADAuthInt_Plugin::bind_user
	 * @uses ADAuthInt_Plugin::bind_user_password
	 * @uses ADAuthInt_Plugin::port
	 * @uses ADAuthInt_Plugin::secure_connection
	 * @uses ADAuthInt_Plugin::max_login_attempts
	 * @uses ADAuthInt_Plugin::notify_user
	 * @uses ADAuthInt_Plugin::notify_admin
	 * @uses ADAuthInt_Plugin::auth_from_ad_group
	 * @uses ADAuthInt_Plugin::user_account_suffix
	 * @uses ADAuthInt_Plugin::auto_user_create
	 *
	 * / System Vars /
	 * @uses ADAuthInt_Plugin::_authenticated
	 * @uses ADAuthInt_Plugin::_adldap
	 * @uses ADAuthInt_Plugin::_loglevel
	 *
	 * / System Constants /
	 * @uses ADAI_LOG_NOTICE
	 * @uses ADAI_LOG_DEBUG
	 * @uses ADAI_LOG_INFO
	 * @uses ADAI_LOG_ERROR
	 * @uses ADAI_LOG_NONE
	 *
	 * / System Functions /
	 * @uses ADAuthInt_Plugin::_log()
	 * @uses ADAuthInt_Plugin::_load_options()
	 * @uses ADAuthInt_Plugin::_get_failed_logins_within_block_time()
	 * @uses ADAuthInt_Plugin::_display_blocking_page()
	 * @uses ADAuthInt_Plugin::_store_failed_login()
	 * @uses ADAuthInt_Plugin::_cleanup_failed_logins()
	 * @uses ADAuthInt_Plugin::_check_authorization_by_group()
	 * @uses ADAuthInt_Plugin::_get_user_role_equiv()
	 * @uses ADAuthInt_Plugin::_get_display_name_from_AD()
	 * @uses ADAuthInt_Plugin::_create_user()
	 * @uses ADAuthInt_Plugin::_update_user()
	 *
	 * / WordPress Functions /
	 * @uses get_userdatabylogin()
	 * @uses username_exists()
	 * @uses WP_User::_construct()
	 */
	public function ad_authenticate( $user = NULL, $username = '', $password = '' ) {
		$user_id = NULL;
		$this->_authenticated = false;
		if( empty( $username ) || empty( $password ) ) {
			$this->_log( ADAI_LOG_DEBUG, 'The username and/or password was empty' );
			return empty( $username ) ? 
				new WP_Error( 'empty_username', __( 'No username was provided', ADAUTHINT_TEXT_DOMAIN ) ) : 
				new WP_Error( 'empty_password', __( 'No password was provided', ADAUTHINT_TEXT_DOMAIN ) );
		}
		
		// IMPORTANT!
		$username = strtolower( $username );
		
		$this->_log( ADAI_LOG_NOTICE, 'username: ' . $username );
		$this->_log( ADAI_LOG_DEBUG, 'password: ' . ( $this->_mask_passwords_in_log ? md5($password) : $password ) );
		
		$this->_set_adldap();
		
		if( false === $this->_adldap || ( is_array( $this->_adldap ) && in_array( false, $this->_adldap ) ) ) {
			ob_start();
			var_dump( $this );
			$this->_log( ADAI_LOG_DEBUG, ob_get_contents() );
			ob_end_clean();
			add_action( 'wp_authenticate_user', array( &$this, 'generate_error_no_ad_connection' ) );
			return false;
		}
		
		$this->_log( ADAI_LOG_NOTICE, 'adLDAP object created.' );
		ob_start();
		var_dump( $this->_adldap );
		$this->_log( ADAI_LOG_INFO, ob_get_contents() );
		ob_end_clean();
		
		// Check for maximum login attempts
		$this->_log( ADAI_LOG_INFO, 'max_login_attempts: ' . $this->max_login_attempts );
		if( 0 < $this->max_login_attempts ) {
			$failed_logins = $this->_get_failed_logins( $username );
			$this->_log( ADAI_LOG_INFO, 'users failed logins: ' . $failed_logins );
			if ( $this->max_login_attempts <= $failed_logins ) {
				$this->_authenticated = false;

				$this->_log( ADAI_LOG_ERROR, 'Authentication failed' );
				$this->_log( ADAI_LOG_ERROR, sprintf( 'Account \'%s\' blocked for %d seconds', $username, $this->blocking_time ) );
				
				// e-mail notfications if user is blocked
				if( $this->_notify_user( $username ) )
					$this->_log( ADAI_LOG_NOTICE, 'Notification sent to user.' );
				if( $this->_notify_admin( $username ) )
					$this->_log( ADAI_LOG_NOTICE, 'Notification sent to admin(s).' );
				
				// Show the blocking page to the user (only if we are not in debug/log mode)
				if ( ADAI_LOG_NONE == $this->_loglevel )
					$this->_display_blocking_page( $username );
				
				die(); // important !
			} 
		}
		
		// This is where the action is.
		$tmp = false;
		if( !is_array( $this->_adldap ) ) {
			try {
				$tmp = $this->_adldap->authenticate( $username, $password );
			} catch ( Exception $e ) {
				$this->_log( ADAI_LOG_NOTICE, 'Authentication unsuccessful: ' . $e->getMessage() );
			}
			if( $tmp ) {
				$this->_log( ADAI_LOG_NOTICE, 'Authentication successful' );
				$this->_authenticated = true;
			}
		} else {
			foreach( $this->_adldap as $_adldap ) {
				try {
					$tmp = $_adldap->authenticate( $username, $password );
				} catch ( Exception $e ) {
					$this->_log( ADAI_LOG_NOTICE, 'Authentication unsuccessful: ' . $e->getMessage() );
				}
				if( $tmp ) {
					$this->_log( ADAI_LOG_NOTICE, 'Authentication successful' );
					$this->_authenticated = true;
					$this->_adldap = $_adldap;
					continue;
				}
			}
		}
		unset( $tmp );

		if( false == $this->_authenticated ) {
			$this->_log( ADAI_LOG_ERROR, 'Authentication failed' );
			$this->_store_failed_login( $username );
			$this->_log_flush();
			$this->_authenticated = false;
			
			/**
			 * If the line of code below is uncommented, only users in the AD will be allowed to login to WordPress
			 */
			/*add_action( 'wp_authenticate_user', array( &$this, 'generate_error_incorrect_password' ) );*/
			return false;
		}
		
		/* Remove any previous failed logins */
		$this->_cleanup_failed_logins( $username );

		// Check the authorization
		$this->_authenticated = $this->_check_authorization_by_group( $username );
		if( false === $this->_authenticated ) {
			$this->_log_flush();
			/*$GLOBALS['error'] = __( 'The username provided is not a member of the groups that are allowed to access this site', ADAUTHINT_TEXT_DOMAIN );*/
			add_action( 'wp_authenticate_user', array( &$this, 'generate_error_not_in_group' ) );
			return false;
		}
		
		$ad_username = $username;
		/**
		 * Attempt to retrieve the user's information from the WordPress database
		 */
		$user = get_userdatabylogin( $username );
		if( !$user && $this->append_user_suffix ) {
			/**
			 * If the user wasn't retrieved with the provided username, try appending 
			 * 		the account suffix to the username
			 */
			$username .= $this->user_account_suffix;
			$user = get_userdatabylogin( $username );
		}
		
		/**
		 * If the user doesn't seem to exist
		 */
		if( !$user || $user->user_login != $username ) {
			$user_role = trim( $this->_get_user_role_equiv( $ad_username ) );
			if( $this->auto_user_create || !empty( $user_role ) ) {
					// create user
					$userinfo = $this->_adldap->user_info( $ad_username, array( 'displayname', 'givenname', 'sn', 'samaccountname', 'cn', 'mail', 'description' ) );
					$userinfo = $userinfo[0];
					$email = $userinfo['mail'][0];
					$first_name = $userinfo['givenname'][0];
					$last_name = $userinfo['sn'][0];
					$display_name = $this->_get_display_name_from_AD( $username, $userinfo );
					$user_id = $this->_create_user( $ad_username, $email, $first_name, $last_name, $display_name, $user_role );
			} else {
				// Bail out to avoid showing the login form
				$this->_log( ADAI_LOG_ERROR, 'This user exists in Active Directory, but has not been granted access to this installation of WordPress.' );
				$this->_log_flush();
				add_action( 'wp_authenticate_user', array( &$this, 'generate_error_not_allowed' ) );
				return false;
			}
		} else {
			$notmember = false;
			/**
			 * If this is a multisite install and the user is not already a member of this site, 
			 * 		force an update to the user's role (if auto_user_update is enabled)
			 */
			if( is_multisite() && !is_user_member_of_blog( $user->ID, $GLOBALS['blog_id'] ) )
				$this->auto_update_user_group = $notmember = true;
			
			$this->_log( ADAI_LOG_DEBUG, 'Checked to see if this was multisite and if the user is a member of this blog. The notmember var was set to ' . ( $notmember ? 'true' : 'false' ) );
			
			/* If auto_user_update is enabled, update an existing user */
			if ( $this->auto_user_update || $notmember ) {
				$this->_log( ADAI_LOG_DEBUG, 'Entering the process of checking the user\'s equivalent role and updating the contact info' );
				/**
				 * If we are supposed to update the user group every time, or if this is a multisite
				 * 		installation and the user is not already a member of the current site, set the
				 * 		user's role for this site
				 */
				if( $this->auto_update_user_group ) {
					$this->_log( ADAI_LOG_DEBUG, 'Either the auto_update_user_group option is set to true, or the user ' . $ad_username . ' is not currently a member of the blog with an ID of ' . $GLOBALS['blog_id'] . '. Therefore, the user role is being set based on the equivalent AD user group.' );
					// Update users role
					$user_role = $this->_get_user_role_equiv( $ad_username );
				} else {
					$this->_log( ADAI_LOG_DEBUG, 'The auto_update_user_group option is disabled' . ( is_multisite() ? ', and the user ' . $ad_username . ' appears to be a member of the blog with an ID of ' . $GLOBALS['blog_id'] : '' ) . '. Therefore, no changes were made to this user\'s role.' );
					$user_role = '';
				}
				
				if( $this->auto_user_update ) {
					$userinfo = $this->_adldap->user_info( $ad_username, array( 'displayname', 'givenname', 'sn', 'samaccountname', 'cn', 'mail', 'description' ) );
					$userinfo = $userinfo[0];
					
					$email = $userinfo['mail'][0];
					$first_name = $userinfo['givenname'][0];
					$last_name = $userinfo['sn'][0];
					$common_name = $userinfo['cn'][0];
					$display_name = $this->_get_display_name_from_AD( $username, $userinfo );
					$this->_log( ADAI_LOG_DEBUG, 'The display name returned from the function was: ' . $display_name );
					$user_id = $this->_update_user( $ad_username, $email, $first_name, $last_name, $display_name, $user_role );
				} else {
					$user_id = $this->_add_user_to_blog( $user->ID, $user_role );
				}
			}
		}
		
		// load user object
		if( empty( $user_id ) ) {
			require_once( ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'registration.php' ); 
			$user_id = username_exists( $username );
			$this->_log( ADAI_LOG_NOTICE, 'user_id: ' . $user_id );
		}
		$user = new WP_User( $user_id );

		$this->_log( ADAI_LOG_NOTICE, 'FINISHED' );
		$this->_log_flush();
		return $user;
	} /* AD_Authenticate function */

	/**
	 * Determines the number of failed login attempts of specific user within a specific time from now to the past.
	 * 
	 * @param $username
	 * @return number of failed login attempts  
	 *
	 * @uses ADInt_Original_Plugin::get_transient()
	 */
	protected function _get_failed_logins( $username ) {
		return $this->get_transient( $this->_generate_failed_logins_transient_name( $username ) );
	} /* _get_failed_logins_within_block_time function */

	/**
	 * Send an email to the user who's account is blocked
	 * 
	 * @param $username string
	 * @return bool
	 */
	protected function _notify_user($username) {
		if( true !== $this->notify_user )
			return false;
		
		list( $email, $first_name, $last_name ) = $this->_get_user_info( $username );
		
		// do we have a correct email address?
		if( !is_email( $email ) )
			return false;
		
		$blog_url = get_bloginfo( 'url' );
		$blog_name = get_bloginfo( 'name' );

		$subject = sprintf( __( '[%s] User Account Locked', ADAUTHINT_TEXT_DOMAIN ), $blog_name );
		$body = sprintf( __( 'Someone attempted to login to %s (%s) with your username (%s) using an incorrect password. For security reasons this user account has been locked out for %d seconds.', ADAUTHINT_TEXT_DOMAIN ), $blog_name, $blog_url, $username, $this->blocking_time );
		$body .= "\n\r";
		$body .= strtoupper( sprintf( __( 'This is a system-generated email message created at %s. Please do not respond to the email address specified above.', ADAUTHINT_TEXT_DOMAIN ), date( "Y-m-d H:i:s" ) ) );
		
		$header = null;
		return wp_mail( $email, $subject, $body, $header );
	}
	
	/**
	 * Retrieve information about a specific user
	 */
	protected function _get_user_info( $username=null, $user_id=0 ) {
		/* Instantiate our variables */
		$last_name = $first_name = $email = null;
		
		// if auto creation is enabled look for the user in AD 
		if( !empty( $username ) || !empty( $user_id ) ) {
			$userinfo = empty( $user_id ) ? get_userdatabylogin( $username ) : get_userdata( $user_id );
			if( false === $userinfo && !empty( $this->user_account_suffix ) && $this->append_user_suffix )
				$userinfo = get_userdatabylogin( $username . $this->user_account_suffix );
			
			if( !empty( $userinfo ) ) {
				$last_name	= $userinfo->last_name;
				$first_name	= $userinfo->first_name;
				$email		= $userinfo->user_email;
			} else {
				$email		= false;
			}
		}
		
		if( empty( $email ) && $this->auto_user_create && !empty( $username ) ) {
			$userinfo = $this->_adldap->user_info($username, array("sn", "givenname", "mail"));
			if( $userinfo ) {
				$userinfo	= $userinfo[0];
				$email		= $userinfo['mail'][0];
				$first_name	= $userinfo['givenname'][0];
				$last_name	= $userinfo['sn'][0];
			} else { 
				$email		= false;
			}
		} 
		
		if( empty( $email ) && empty( $this->default_email_domain ) )
			return false;
		elseif( empty( $email ) )
			$email = $username . '@' . $this->default_email_domain;
		
		return array( $email, $first_name, $last_name );
	}
	
	/**
	 * Notify administrator(s) by e-mail if an account is blocked
	 * 
	 * @param $username username of the blocked account
	 * @return boolean false if no email is sent, true on success
	 */
	protected function _notify_admin( $username ) {
		$arrEmail = array(); // list of recipients
		
		if( true !== $this->notify_admin )
			return false;
		
		$email = $this->admin_email;
		
		// Should we use Blog-Administrator's e-mail
		$email = trim( $email );
		
		if ( empty( $email ) )
			/* Use the blog's admin email address if the field was empty */
			$arrEmail = array( trim( get_bloginfo( 'admin_email' ) ) );
		else
			/* Use the list of email addresses provided if not empty */
			$arrEmail = array_filter( explode( ';', $email ), 'is_email' );
		
		/* Do we have at least one valid email address? If not, return false */
		if( !count( $arrEmail ) )
			return false;
			
		list( $email, $first_name, $last_name ) = $this->_get_user_info( $username );
		$blog_url = get_bloginfo('url');
		$blog_name = get_bloginfo('name');

		$subject = sprintf( __( '[%s] User account locked', ADAUTHINT_TEXT_DOMAIN ), $blog_name );
		$body = sprintf( __( 'Someone tried to login to %s (%s) with the username "%s" (%s %s) using an incorrect password. For security reasons this account has been locked out for %d seconds.', ADAUTHINT_TEXT_DOMAIN ), $blog_name, $blog_url, $username, $first_name, $last_name, $this->blocking_time );
		$body .= "\n\r";
		$body .= sprintf( __( 'The login attempt was made from IP-Address: %s', ADAUTHINT_TEXT_DOMAIN ), $_SERVER['REMOTE_ADDR']);
		$body .= "\n\r";
		$body .= strtoupper( __( 'This is a system-generated email message. Please do not respond to the email address specified above.', ADAUTHINT_TEXT_DOMAIN ) );
	
		/* Send the email messages */
		return wp_mail( $arrEmail, $subject, $body, $header );
	} 
	
	/**
	 * Output debug informations
	 * 
	 * @param integer level
	 * @param string $notice
	 */
	protected function _log($level = 0, $info = '') {
		if ($level <= $this->_loglevel) {
			$this->_debug .= '[' .$level . '] '.$info."\n\r";
		}
	} /* _log function */
	
	/**
	 * Output debug information and clean the log
	 */
	protected function _log_flush() {
		if( ADAI_LOG_NONE == $this->_loglevel )
			return $this->_debug = '';
		
		echo "[0] Log Level set to " . $this->_loglevel . "\n";
		echo $this->_debug;
		$this->_debug = '';
	}

	/**
	 * Show a blocking page for blocked accounts.
	 * 
	 * @param $username
	 * @uses ADAuthInt_Plugin::_get_rest_of_blocking_time()
	 */
	protected function _display_blocking_page($username) {
		wp_die( '
<div id="login">
	<h1>
    	<a href="' . get_bloginfo( 'url' ) . '" title="' . apply_filters( 'login_headertitle', __('Powered by WordPress') ) . '">
			' . get_bloginfo( 'name' ) . '
        </a>
    </h1>
	<div id="login_error">
		' . sprintf( __( 'This account is currently locked out, due to at least %d unsuccessful login attempts', ADAUTHINT_TEXT_DOMAIN ), $this->max_login_attempts ) . '
	</div>
</div>' );
	} /* _display_blocking_page */
	
	/**
	 * Stores the username and the current time in the db.
	 * 
	 * @param $username
	 * @return unknown_type
	 * @uses ADAuthInt_Plugin::get_table_name()
	 * @uses $wpdb
	 */
	protected function _store_failed_login( $username ) {
		$this->_log( ADAI_LOG_WARN, sprintf( 'Storing failed login for "%s"', $username ) );
		$failed_logins = (int)$this->get_transient( $this->_generate_failed_logins_transient_name( $username ) );
		
		$this->_cleanup_failed_logins( $username );
		
		if( !is_numeric( $failed_logins ) ) {
			$failed_logins = 0;
		}
		$failed_logins++;
		return $this->set_transient( $this->_generate_failed_logins_transient_name( $username ), $failed_logins, $this->blocking_time );
	} /* _store_failed_login function */

	/**
	 * Delete the number of failed logins for a user
	 * @param string $username the username of the person for whom failed logins should be deleted
	 */
	protected function _cleanup_failed_logins($username = NULL) {
		return $this->delete_transient( $this->_generate_failed_logins_transient_name( $username ) );
	} /* _cleanup_failed_logins function */
	
	/**
	 * Generate the proper name for the failed logins transient being manipulated
	 * @param string $username
	 * @return string the name of the transient
	 */
	protected function _generate_failed_logins_transient_name( $username ) {
		return '_' . esc_attr( $username ) . '_adai_failed_logins';
	}

	/**
	 * Checks if the user is member of the group(s) allowed to login
	 * 
	 * @param $username
	 * @return boolean
	 *
	 * @uses ADAuthInt_Plugin::auth_from_ad_grp
	 * @uses ADAuthInt_Plugin::auth_groups
	 * @uses ADAuthInt_Plugin::_adldap
	 * @uses ADAuthInt_Plugin::_log()
	 */
	protected function _check_authorization_by_group($username) {
		if( !$this->auth_from_ad_grp )
			return true;
			
		$authorization_groups = array_filter( explode( ';', $this->auth_groups ) );
		foreach( $authorization_groups as $group ) {
			if( $this->_adldap->user_ingroup( $username, $group, true ) ) {
				$this->_log( ADAI_LOG_NOTICE, sprintf( 'Authorized by membership of group "%s"', $group ) );
				return true;
			}
		}
		$this->_log( ADAI_LOG_WARN, 'Authorization by group failed. User is not authorized.' );
		return false;
	} /* _check_authorization_by_group function */
	
	/**
	 * Get the first matching role from the list of role equivalent groups the user belongs to.
	 * 
	 * @param $ad_username 
	 * @return string matching role
	 *
	 * @uses ADAuthInt_Plugin::role_equiv_groups
	 * @uses ADAuthInt_Plugin::_adldap
	 * @uses ADAuthInt_Plugin::_log
	 * @uses ADAI_LOG_INFO
	 */
	protected function _get_user_role_equiv($ad_username) {
		if( !$this->use_role_equiv )
			return $user_role = get_option( 'default_role', false );
		if( is_numeric( $ad_username ) ) {
			$userinfo = get_userdata( $ad_username );
			$ad_username = $userinfo->user_login;
			unset( $userinfo );
		}
			
		if( !empty( $this->ad_account_suffix ) )
			$ad_username = str_replace( $this->ad_account_suffix, '', $ad_username );
		
		$role_equiv_groups = array_filter( explode( ';', $this->role_equiv_groups ) );
		
		$user_role = '';
		foreach ($role_equiv_groups as $role_group) {
			$role_group = explode( '=', $role_group );
			if ( 2 != count( $role_group ) )
				continue;
			
			$ad_group = $role_group[0];
			$corresponding_role = $role_group[1];
			
			$tmp = $this->_adldap->user_ingroup( $ad_username, $role_group[0], true );
			$this->_log( ADAI_LOG_INFO, 'Checked if "' . $ad_username . '" was in "' . $role_group[0] . '". The result was ' . ( $tmp ? 'true' : 'false' ) );
			if ( $tmp ) {
				$this->_log( ADAI_LOG_INFO, 'User role set to: ' . $role_group[1] );
				return $user_role = $role_group[1];
			}
		}
		$this->_log_flush();
		
		if( $this->auto_add_to_sites && $this->_check_authorization_by_group() )
			$user_role = get_option( 'default_role', false );
		
		return $user_role;
	} /* _get_user_role_equiv function */

	/**
	 * Determine the display_name to be stored in WP database.
	 * @param $username  the username used to login
	 * @param $userinfo  the array with data returned from AD
	 * @return string  display_name
	 * @uses ADAuthInt_Plugin::display_name
	 */
	protected function _get_display_name_from_AD( $username, $userinfo ) {
		if ( empty( $this->display_name ) || strtolower( $this->display_name ) == strtolower( 'samaccountname' ) || !is_array( $userinfo ) )
			return $username;
		
		$this->display_name = strtolower( $this->display_name );
		
		$displayprops = array(
			'displayname' 	=> '%1$s',
			'firstlast'		=> '%2$s %3$s',
			'lastfirst'		=> '%3$s, %2$s',
			'samaccountname'=> '%4$s',
			'givenname'		=> '%2$s',
			'sn'			=> '%3$s',
			'cn'			=> '%5$s',
			'mail'			=> '%6$s',
			'description'	=> '%7$s',
		);
		
		$displayopts = array( 'displayname', 'givenname', 'sn', 'samaccountname', 'cn', 'mail', 'description' );
		$displayvals = array();
		
		/* Check to see if the property exists */
		foreach( $displayopts as $d ) {
			$displayvals[$d] = array_key_exists( $d, $userinfo ) ? $userinfo[$d][0] : '';
		}
		
		$this->_log( ADAI_LOG_DEBUG, 'Display Name property is: ' . $this->display_name );
		$this->_log( ADAI_LOG_DEBUG, 'The display properties array looks like: ' . print_r( $displayprops, true ) );
		$this->_log( ADAI_LOG_DEBUG, 'Display values are: ' . print_r( $displayvals, true ) );
		$displayname = vsprintf( $displayprops[$this->display_name], $displayvals );
		$this->_log( ADAI_LOG_DEBUG, 'Preparing to return the display name of: ' . $displayname );
		
		/* If the property existed and was not empty, return it. Otherwise, return the samaccountname */
		return empty( $displayname ) ?
			$username :
			$displayname;
	} /* _get_display_name_from_AD function */

	/**
	 * Create a new WordPress account for the specified username.
	 * @param $username
	 * @param $email
	 * @param $first_name
	 * @param $last_name
	 * @param $display_name
	 * @param $role
	 * @return integer user_id
	 *
	 * @uses ADAuthInt_Plugin::_get_password()
	 * @uses ADAuthInt_Plugin::_create_non_duplicate_email()
	 * @uses ADAuthInt_Plugin::_log()
	 *
	 * @uses ADAuthInt_Plugin::default_email_domain
	 * @uses ADAuthInt_Plugin::append_user_suffix
	 * @uses ADAuthInt_Plugin::user_account_suffix
	 * @uses ADAuthInt_Plugin::dup_account_handling
	 *
	 * / WordPress Constants /
	 * @uses ABSPATH
	 * @uses WPINC
	 * @uses DIRECTORY_SEPARATOR
	 *
	 * / WordPress Functions /
	 * @uses wp_create_user()
	 * @uses is_wp_error()
	 * @uses username_exists()
	 * @uses update_user_meta()
	 * @uses update_usermeta()
	 * @uses wp_update_user()
	 */
	protected function _create_user($username, $email, $first_name, $last_name, $display_name = '', $role = '') {
		global $wp_version;
		
		$password = $this->_get_password();
		$email = trim( $email );
		$this->default_email_domain = trim( $this->default_email_domain );
		
		if ( empty( $email ) ) {
			if ( is_email( $username ) )
				$email = $username;
			elseif( !empty( $this->default_email_domain ) )
				$email = $username . '@' . $this->default_email_domain;
		}
				
		// append account suffix to new users? 
		if( $this->append_user_suffix )
			$username .= $this->user_account_suffix;
		
		$this->_log( ADAI_LOG_NOTICE, "Creating user '$username' with following data:\n".
					  "- email: $email\n".
					  "- first name: $first_name\n".
					  "- last name: $last_name\n".
					  "- display name: $display_name\n".
					  "- role: $role" );
		
		require_once( ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'registration.php' );
		
		if ( $this->dup_account_handling == 'allow' )
			if ( !defined( 'WP_IMPORTING' ) )
				define( 'WP_IMPORTING', true ); // This is a dirty hack. See wp-includes/registration.php
		
		if ( $this->dup_account_handling == 'create' ) {
			$new_email = $this->_create_non_duplicate_email($email);
			
			if ($new_email != $email)
				$this->_log( ADAI_LOG_NOTICE, "Duplicate email address prevention: Email changed from $email to $new_email." );
			
			$email = $new_email;
		}
		
		// Here we go!
		$return = empty( $email ) ? wp_create_user( $username, $password ) : wp_create_user( $username, $password, $email );

		// log errors
		if ( is_wp_error( $return ) )
   			$this->_log( ADAI_LOG_ERROR, $return->get_error_message() );
		
		$user_id = username_exists( $username );
		
		if ( !$user_id ) {
			$this->_log( ADAI_LOG_FATAL, 'Error creating user.' );
			wp_die( '<p>' . __( 'Error creating user!', ADAUTHINT_TEXT_DOMAIN ) . '</p>' . ( isset( $errorlog ) ?  '<p>' . $errorlog . '</p>' : '' ) . $this->_log_flush() );
			return;
		}
		
		$this->_log( ADAI_LOG_NOTICE, ' - user_id: ' . $user_id );
		
		update_user_meta( $user_id, 'first_name', $first_name );
		update_user_meta( $user_id, 'last_name', $last_name );
		
		// set display_name
		if ( !empty( $display_name ) )
			$return = wp_update_user( array(
				'ID' => $user_id, 
				'display_name' => $display_name
			) );
		
		// set role
		if ( !empty( $role ) )
			$return = wp_update_user( array( 
				'ID' => $user_id, 
				'role' => $role
			) );
		
		return $user_id;
	}
	
	/**
	 * Returns the given email address or a newly created so no 2 users
	 * can have the same email address.
	 * 
	 * @param $email original email address
	 * @return unique email address
	 */
	protected function _create_non_duplicate_email( $email ) {
		if ( !email_exists( $email ) )
			return $email;
		
		// Ok, lets create a new email address that does not already exists in the database
		$arrEmailParts = split( '@', $email );
		
		/* If the email address didn't split into at least 2 parts, we've failed */
		if( count( $arrEmailParts < 2 ) )
			return null;
		
		$counter = 1;
		$separator = '.';
		$ok = false;
		$emailusername = array_shift( $arrEmailParts );
		while ( $ok !== true ) {
			$email = $emailusername . $separator . $counter . '@' . implode( '@', $arrEmailParts );
			$ok = !email_exists( $email );
			$counter++;	
		}
		return $email;
	}
	
	/**
	 * Updates a specific Wordpress user account
	 * 
	 * @param $username
	 * @param $email
	 * @param $first_name
	 * @param $last_name
	 * @param $display_name
	 * @param $role
	 * @return integer user_id
	 *
	 * @uses $wp_version
	 * @uses ADAuthInt_Plugin::default_email_domain
	 * @uses ADAuthInt_Plugin::append_user_suffix
	 * @uses ADAuthInt_Plugin::user_account_suffix
	 * @uses ADAuthInt_Plugin::_log()
	 * @uses ADAI_LOG_NOTICE
	 * @uses username_exists()
	 * @uses ADAI_LOG_FATAL
	 * @uses update_user_meta
	 * @uses update_usermeta
	 * @uses wp_update_user
	 * @uses is_wp_error()
	 */
	protected function _update_user( $username, $email, $first_name, $last_name, $display_name = '', $role = '' ) {
		if( !$this->auto_user_update && !$this->auto_user_create )
			return;
		
		$this->_log( ADAI_LOG_DEBUG, 'Preparing to update user information' );
		$email = trim( $email );
		$display_name = trim( $display_name );
		$role = trim( $role );
		$this->default_email_domain = trim( $this->default_email_domain );
		
		if ( empty( $email ) ) {
			if( is_email( $username ) )
				$email = $username;
			elseif ( !empty( $this->default_email_domain ) )
				$email = $username . '@' . $this->default_email_domain;
		}
		
		$user_id = username_exists( $username );
		if( !$user_id && $this->append_user_suffix ) {
			$username .= $this->user_account_suffix;
			$user_id = username_exists( $username );
		}
		
		$this->_log( ADAI_LOG_NOTICE, 'Updating user "'.$username."\" with following data:\n".
					  "- email: $email\n".
					  "- first name: $first_name\n".
					  "- last name: $last_name\n".
					  "- display name: $display_name\n".
					  "- role: $role" );
		
		require_once(ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'registration.php');
		
		$this->_log(ADAI_LOG_NOTICE,' - user_id: '.$user_id);
		if ( !$user_id ) {
			$this->_log(ADAI_LOG_FATAL,'Error updating user.');
			wp_die('<p>' . __( 'Error updating user!', ADAUTHINT_TEXT_DOMAIN ) . '</p>' . '<p>' . sprintf( __( 'For some reason, a user with the username %s does not appear to exist in the WordPress database.', ADAUTHINT_TEXT_DOMAIN), $username ) . '</p>' . $this->_log_flush() );
			return;
		} else {
			if( is_multisite() && !is_user_member_of_blog( $user_id, $GLOBALS['blog_id'] ) )
				add_user_to_blog( $GLOBALS['blog_id'], $user_id, NULL );
			
			update_user_meta( $user_id, 'first_name', $first_name );
			update_user_meta( $user_id, 'last_name', $last_name );
			
			$user_meta = array( 'ID' => $user_id );
			if( !empty( $display_name ) )
				$user_meta['display_name']	= $display_name;
			if( !empty( $role ) )
				$user_meta['role']			= $role;
			if( !empty( $email ) )
				$user_meta['user_email']	= $email;
			if( $this->randomize_password )
				$user_meta['user_pass']		= $this->_get_password();
			
			$user_meta_copy = $user_meta;
			if( array_key_exists( 'user_pass', $user_meta_copy ) )
				unset( $user_meta_copy['user_pass'] );
			
			$this->_log( ADAI_LOG_DEBUG, 'Preparing to run the wp_update_user func to update the user information with the following data: ' . print_r( $user_meta_copy, true ) );
			if( count( $user_meta ) > 1 )
				$return = wp_update_user( $user_meta );
		}
		
		// log errors
		if( isset( $return ) )
			if( is_wp_error( $return ) )
	   			$this->_log(ADAI_LOG_ERROR, $return->get_error_message());
		
		return $user_id;
	} /* _update_user function */
	
	/**
	 * Add a user to a blog in a multisite install
	 * @param int $user_id the ID of the user to add
	 * @param string $user_role the WordPress role to give the user
	 * @return string the role assigned to the user
	 * @since 0.6a
	 */
	function _add_user_to_blog( $user_id, $user_role ) {
		if( !is_multisite() )
			return $user_role;
		
		if( empty( $user_role ) )
			$user_role = $this->_get_user_role_equiv( $user_id );
		if( empty( $user_role ) )
			return false;
		
		$user = new WP_User( $user_id );
		
		if ( !get_user_meta($user_id, 'primary_blog', true) ) {
			update_user_meta($user_id, 'primary_blog', $GLOBALS['blog_id']);
			$details = get_blog_details($GLOBALS['blog_id']);
			update_user_meta($user_id, 'source_domain', $details->domain);
		}
		$user->set_role($user_role);
		return $user_role;
	}
	
	/**
	 * Check to see if a user has a role on the current site
	 * @return bool whether or not the user has a role
	 * @since 0.6a
	 */
	function is_user_member_of_blog() {
		if( !is_multisite() )
			return;
		
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		$blog_id = $GLOBALS['blog_id'];
		$ad_username = $current_user->user_login;
		if( $this->append_ad_user_suffix )
			$ad_username = str_replace( $this->ad_account_suffix, '', $ad_username );
		
		if( is_user_member_of_blog( $user_id, $blog_id ) )
			return true;
		
		$this->_set_adldap();
		if( is_array( $this->_adldap ) ) {
			$found = false;
			foreach( $this->_adldap as $_adldap ) {
				if( !is_object( $_adldap ) )
					continue;
				if( false !== ( $ad_username = $_adldap->user_info($ad_username, array("samaccountname")) ) ) {
					$found = true;
				}
			}
			if( false === $found ) {
				$this->_log( ADAI_LOG_DEBUG, 'The user ' . $ad_username . ' was not found in any of the active directory servers while checking to see if the user was a member of the blog ' . $GLOBALS['blog_id'] . '.' );
				return false;
			}
		} elseif( !is_object( $this->_adldap ) ) {
			return false;
		} else {
			if( false === ( $ad_username = $this->_adldap->user_info($ad_username, array("samaccountname")) ) ) {
				$this->_log( ADAI_LOG_DEBUG, 'The user ' . $ad_username . ' was not found in the active directory while checking to see if the user was a member of the blog ' . $GLOBALS['blog_id'] . '.' );
				return false;
			}
		}
		
		$user_role = $this->_get_user_role_equiv( $user_id );
		if( !empty( $user_role ) ) {
			add_user_to_blog( $blog_id, $user_id, $user_role );
			return true;
		}
		return false;
	}
	
	/*
	 * Used to disable certain login functions, e.g. retrieving a
	 * user's password.
	 */
	public function disable_function() {
		if( !isset( $_GET['action'] ) )
			$_GET['action'] = 'null';
		
		switch( $_GET['action'] ) {
			case 'lostpassword':
			case 'retrieve_password':
			case 'password_reset':
				wp_die( $this->_get_lost_password_message(), __( 'Password Instructions', ADAUTHINT_TEXT_DOMAIN ) );
				break;
			default:
				wp_die( 'This action has been disabled', 'Disabled' );
		}
		exit();
	} /* disable_function function */

	/*
	 * Generate a password for the user. This plugin does not
	 * require the user to enter this value, but we want to set it
	 * to something nonobvious.
	 */
	public function generate_password( $username, $password1, $password2 ) {
		if( !$this->append_user_suffix || stristr( $username, $this->user_account_suffix ) )
			$password1 = $password2 = $this->_get_password();
	} /* generate_password function */

	/*
	 * Used to disable certain display elements, e.g. password
	 * fields on profile screen.
	 */
	public function disable_password_fields( $show_password_fields ) {
		return false;
	} /* disable_password_fields function */
	
	/**
	 * Generate a random password.
	 * 
	 * @param int $length Length of the password
	 * @return password as string
	 */
	protected function _get_password( $length = 15 ) {
		return substr( md5( uniqid( microtime() ) ), 0, $length );
	}

	/**
	 * Wrapper function for transient retrieval
	 * @param string $tname the name of the transient to be retrieved
	 */
	function get_transient( $tname ) {
		if( function_exists( 'is_multinetwork' ) && is_multinetwork() && function_exists( 'get_mnetwork_transient' ) )
			return get_mnetwork_transient( $tname );
		elseif( is_multisite() )
			return get_site_transient( $tname );
		else
			return get_transient( $tname );
		
		return false;
	}
	
	/**
	 * Wrapper function for transient storage
	 * @param string $tname the name of the transient to be set
	 * @param mixed $val the value to be stored
	 * @param int $exp the length of time (in seconds) to keep the transient
	 */
	function set_transient( $tname, $val=false, $exp=0 ) {
		if( function_exists( 'is_multinetwork' ) && is_multinetwork() && function_exists( 'set_mnetwork_transient' ) )
			return set_mnetwork_transient( $tname, $val, $exp );
		elseif( is_multisite() )
			return set_site_transient( $tname, $val, $exp );
		else
			return set_transient( $tname, $val, $exp );
		
		return false;
	}
	
	/**
	 * Wrapper function for transient removal
	 * @param string $tname the name of the transient to delete
	 */
	function delete_transient( $tname ) {
		if( function_exists( 'is_multinetwork' ) && is_multinetwork() && function_exists( 'delete_mnetwork_transient' ) )
			return delete_mnetwork_transient( $tname );
		elseif( is_multisite() )
			return delete_site_transient( $tname );
		else
			return delete_transient( $tname );
		
		return false;
	}
	
	/****************************************************************
	 * STATIC FUNCTIONS
	 ****************************************************************/

	/**
	 * Determine global table prefix, usually "wp_".
	 * 
	 * @return string table prefix
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->base_prefix . self::TABLE_NAME;
	} /* get_table_name function */
}
?>
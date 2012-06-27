<?php
/**
 * Class and method definitions for the Active Directory Authentication Integration plug-in
 * @package wordpress
 * @subpackage ADAuthInt
 * @version 0.6
 */

/**
 * Require the constant definitions for this plug-in
 */
require_once( str_replace( '/class-', '/inc/constants-', __FILE__ ) );

/**
 * Require the class definition for the ADInt_Original_Plugin class, which
 * is extended by the class defined below.
 */
require_once( ADAUTHINT_ABS_DIR . '/inc/class-adint_original_plugin.php' );

/**
 * Require the class definition for individual options used with this plug-in
 */
require_once( ADAUTHINT_ABS_DIR . '/inc/class-adauthint_option.php' );

if( !class_exists( 'ADAuthInt_Plugin' ) ) {
	/**
	 * Active Directory Authentication Integration class
	 *
	 * The main class definition used for the AD AuthInt plug-in
	 * This class extends the ADInt_Original_Plugin class. This
	 * class includes all of the functions and options that either
	 * needed to be rewritten from the AD Integration plug-in or
	 * that needed to be created anew.
	 *
	 * @package wordpress
	 * @subpackage ADAuthInt
	 */
	class ADAuthInt_Plugin extends ADInt_Original_Plugin {
		/**#@+
		 * @access private
		 * @var string
		 * @default NULL
		 */
		/**
		 * Server Info - Domain Controllers
		 */
		protected $domain_controllers = NULL;
		/**
		 * Server Info - Bind User
		 */
		protected $bind_user = NULL;
		/**
		 * Server Info - Bind User Password
		 */
		protected $bind_user_password = NULL;
		/**
		 * Server Info - BASE DN
		 */
		protected $base_dn = NULL;
		/**
		 * User Info - Default E-mail Domain
		 */
		protected $default_email_domain = NULL;
		/**
		 * User Info - Duplicate Account Handling
		 *
		 * Indicates what to do if a new user account with a duplicate e-mail address is
		 * in the process of being created.
		 * Three Options:
		 * - Prevent: User is not created, if his email address is already in use by another user. (recommended)
		 * - Allow: Allow users to share one email address. (UNSAFE)
		 * - Create: In case of conflict, the new user is created with a unique email address.
		 * @default 'prevent'
		 */
		protected $dup_account_handling = 'prevent';
		/**
		 * User Info - WordPress User Account Suffix
		 */
		protected $user_account_suffix = NULL;
		/**
		 * User Info - AD User Account Suffix
		 */
		protected $ad_account_suffix = NULL;
		/**
		 * User Info - Default Display Name
		 *
		 * Indicates from where the user's default display name should be generated, based on
		 * the information passed by the AD server
		 * Seven Options:
		 * - sAMAccountName (the AD username)
		 * - displayName (User's full name from online directory)
		 * - description
		 * - givenName (User's first name)
		 * - SN (User's last name)
		 * - CN (User's common name - varies on a case-by-case basis)
		 * - mail (User's full e-mail address)
		 * @default sAMAccountName
		 */
		protected $display_name = 'sAMAccountName';
		/**
		 * User Info - Message to display when retrieving/resetting password
		 */
		protected $_lost_password_message = NULL;
		/**
		 * Auth Info - Users are authorized to login if they are members of the specified groups
		 */
		protected $auth_groups = NULL;
		/**
		 * Auth Info - Role Equivalent Groups
		 */
		protected $role_equiv_groups = NULL;
		/**
		 * Security Info - Administrator E-mail Address
		 */
		protected $admin_email = NULL;
		/**#@-*/
		
		/**#@+
		 * @access private
		 * @var int
		 */
		/**
		 * Server Info - Randomize controllers
		 * @default 0
		 * @since 0.6
		 */
		protected $randomize_dc = true;
		/**
		 * Server Info - Port
		 * @default 389
		 */
		protected $port = 389;
		/**
		 * Security Info - Max Login Attempts
		 *
		 * Locks out a user account if this number of login attempts fail
		 * @default 3
		 */
		protected $max_login_attempts = 3;
		/**
		 * Security Info - Login Interval Time
		 *
		 * If the login attempts are less than or equal to this number of seconds, add 
		 * 		one to the number of unsuccessful login attempts
		 * Until I figure out a better method of storing the transient information, this 
		 * 		option will be ignored, using the $blocking_time value instead.
		 * 		
		 * @default 10
		 */
		protected $login_interval = 10;
		/**
		 * Security Info - Blocking Time
		 *
		 * Amount of time to lock out a user account if the number of login attempts is exceeded
		 * @default 30
		 */
		protected $blocking_time = 30;
		/**#@-*/
		
		/**#@+
		 * @access private
		 * @var bool
		 */
		/**
		 * Server Info - SSL Connection
		 * @since 0.6
		 */
		protected $use_ssl = false;
		/**
		 * Server Info - TLS Connection
		 *
		 * Indicates whether the connection to the AD server is secure or not
		 * @default false
		 */
		protected $secure_connection = false;
		/**
		 * User Info - Automatic User Creation
		 *
		 * Indicates whether a new user should automatically be created based on the 
		 * AD credentials if one doesn't already exist
		 * @default true
		 */
		protected $auto_user_create = true;
		/**
		 * User Info - Automatic User Update
		 *
		 * Indicates whether the WordPress user information should be automatically
		 * updated based on the AD information each time the user logs in
		 * @default false
		 */
		protected $auto_user_update = false;
		/**
		 * User Info - Append User Suffix
		 *
		 * Indicates whether or not to append the account suffix to each new username
		 * @default true
		 */
		protected $append_user_suffix = true;
		/**
		 * User Info - Append AD User Suffix
		 * 
		 * Indicates whether or not to append the AD account suffix to each username before 
		 * 		it's authenticated against the AD server
		 * @default true
		 */
		protected $append_ad_user_suffix = true;
		/**
		 * User Info - Allow Local Password Changes
		 *
		 * Indicates whether AD-created users should be allowed to change their passwords within
		 * the WordPress user system
		 * @default false
		 */
		protected $allow_local_password = false;
		/**
		 * User Info - Randomize passwords on login
		 * @default true
		 * @since 0.6
		 */
		protected $randomize_password = true;
		/**
		 * Auth Info - Users are authorized only when members of specific AD group(s)
		 * @default false
		 */
		protected $auth_from_ad_grp = false;
		/**
		 * Auth Info - Whether to map AD groups to WP roles
		 * @default false
		 * @since 0.6
		 */
		protected $use_role_equiv = false;
		/**
		 * Auth Info - Automatically update a user's usergroup according to role equivalent
		 * 		groups each time the user logs in (will override any manual changes to the
		 * 		user's group
		 * @default false
		 */
		protected $auto_update_user_group = false;
		/**
		 * Security Info - Notify User
		 *
		 * Indicates whether or not to notify users by e-mail when their accounts are locked out
		 * @default false
		 */
		protected $notify_user = false;
		/**
		 * Security Info - Notify Admin
		 *
		 * Indicates whether or not to notify the admin by e-mail when a user account is locked out
		 */
		protected $notify_admin = false;
		/**#@-*/
		
		/**
		 * Information about the various options available in this plugin
		 * @var array $options_info
		 * @access private
		 */
		protected $options_info = array();
		
		/**
		 * The actual options for this plug-in
		 * @var array $options
		 * @access private
		 */
		protected $options = array();
		
		/**
		 * An array to hold our options objects
		 * @var array $options_objs
		 * @access private
		 */
		protected $options_objs = array();
		
		/**
		 * Subtitles for our settings sections
		 * @var array $subtitles
		 * @access private
		 */
		protected $subtitles = array(
			'adauthint_server_opts' => 'Active Directory Server Settings',
			'adauthint_user_opts' => 'User Information Settings',
			'adauthint_auth_opts' => 'Authorization and Authentication Settings',
			'adauthint_security_opts' => 'Brute Force Security Settings',
		);
		
		/**
		 * Has the user been authenticated already?
		 * @var bool
		 * @access private
		 */
		protected $_authenticated = false;
		
		/**
		 * adLDAP Object
		 * @var adLDAP
		 * @access private
		 */
		protected $_adldap = NULL;
		
		/**
		 * Logging/Debug Level
		 */
		protected $_loglevel = ADAI_LOG_NONE;
		
		/**
		 * Debug Log
		 * @var string
		 */
		protected $_debug = '';
		
		/**
		 * Whether or not to mask passwords in the log
		 * @var bool
		 */
		protected $_mask_passwords_in_log = true;
		
		/**
		 * An array indicating which sets of options were saved successfully.
		 */
		protected $updated = array();
		
		/**
		 * Build our new ADAuthInt_Plugin object in PHP4
		 *
		 * Simply calls and returns the PHP5 __construct() function
		 * @uses ADAuthInt_Plugin::__construct()
		 * @deprecated
		 */
		function ADAuthInt_Plugin() {
			return $this->__construct();
		} /* ADAuthInt_Plugin() function */
		
		/**
		 * Build our new ADAuthInt_Plugin object
		 * @uses load_plugin_textdomain()
		 * @uses is_plugin_active_for_network()
		 * @uses ADAuthInt_Plugin::_load_options()
		 */
		function __construct() {
			global $wp_version, $wpdb;
			
			if( !defined( 'ADAI_MU_PLUGIN' ) )
				define( 'ADAI_MU_PLUGIN', stristr( 'mu-plugins/', __FILE__ ) );
			
			if( !defined( 'ADAI_IS_WPMU' ) )
				define( 'ADAI_IS_WPMU', is_multisite() );
			
			add_filter('plugin_action_links_' . ADAUTHINT_PLUGIN_BASENAME, array($this, 'add_settings_link'));
			add_filter('network_admin_plugin_action_links_' . ADAUTHINT_PLUGIN_BASENAME, array($this, 'add_settings_link'));
			add_action('init', array($this, 'init_plugin'));
			
			add_action( 'network_admin_menu', array( $this, 'setup_admin' ) );
			add_action('admin_menu', array($this, 'setup_admin') );
			
			add_action('admin_init', array($this, 'init_admin'));
			if( is_admin() && $_REQUEST['page'] == ADAUTHINT_OPTIONS_PAGE ) {
				wp_register_script( 
					/*$handle = */'adauthint-scripts', 
					/*$src = */plugins_url( '/scripts/active-directory-authentication-integration.js', ADAUTHINT_PLUGIN_BASENAME ), 
					/*$deps = */array( 'jquery', 'post' ), 
					/*$ver = */ADAI_PLUGIN_VERSION, 
					/*$in_footer = */true
				);
				wp_enqueue_script( 'adauthint-scripts' );
			}
			
			$this->_load_options();
			
			$tablename = $this->get_table_name();
			
			if (  false === $this->_get_option( 'adauthint_removed_old_table', false ) ) {
				if( 1 == $wpdb->get_var( $wpdb->prepare( "SELECT 1 res FROM " . $tablename . " LIMIT 1" ) ) ) {
					/**
					 * This table is no longer used as of version 0.6, so we should remove it
					 * 		if it exists.
					 */
					$wpdb->query( 'DROP TABLE IF EXISTS ' . $tablename );
				}
				
				/**
				 * Insert an option to indicate we've checked for the table, so 
				 * 		we don't have to check for the table again in the future
				 */
				if ( ADAI_IS_MULTINETWORK && function_exists( 'add_mnetwork_option' ) ) {
					add_mnetwork_option( 'adauthint_removed_old_table', 1 );
				} else if ( ADAI_IS_NETWORK_ACTIVE ) {
					add_site_option( 'adauthint_removed_old_table', 1 );
				} else {
					add_option( 'adauthint_removed_old_table', 1 );
				}
			}
			
			add_action( 'admin_notices', array( $this, '_flush_log_to_notice' ) );
			
			if( !function_exists( 'ldap_connect' ) ) {
				add_action( 'admin_notices', array( $this, '_ldap_not_supported' ) );
				return;
			}
			
			// WP 2.8 and above?
			if (version_compare($wp_version, '2.8', '>=')) {
				add_filter('authenticate', array(&$this, 'authenticate'), 10, 3);
			} else {
				add_action('wp_authenticate', array(&$this, 'authenticate'), 10, 2);
			}
			add_filter( 'shake_error_codes', array( &$this, 'add_error_shakes' ) );
			
			add_action( 'add_user_to_blog', array( &$this, '_add_user_to_blog' ), 10, 2 );
			add_action( 'lost_password', array( &$this, 'disable_function' ) );
			add_action( 'retrieve_password', array( &$this, 'disable_function' ) );
			add_action( 'password_reset', array( &$this, 'disable_function' ) );
			
			if( is_admin() ) {
				add_action( 'init', array( $this, 'is_user_member_of_blog' ) );
			}
			
			if( !$this->allow_local_password ) {
				// disable password fields
				add_filter('show_password_fields', array(&$this, 'disable_password_fields'));
				
				// generate a random passwords for manually added users 
				add_action('check_passwords', array(&$this, 'generate_password'), 10, 3);
			}
			
			if( !class_exists( 'adLDAPE' ) )
				require_once( 'inc/adLDAP-extended.php' );
			
		} /* __construct() function */
		
		/**
		 * Get the message that should be displayed when someone tries to retrieve/reset their password
		 */
		function _get_lost_password_message() {
			if( !empty( $this->_lost_password_message ) )
				return $this->_lost_password_message;
			
			return __( 'The passwords used within this installation of WordPress are managed within Active Directory. To reset or retrieve your password, please visit the help system for your organization\'s Active Directory.', ADAUTHINT_TEXT_DOMAIN );
		}
		
		/**
		 * Print an Admin Notice about lack of LDAP Support
		 */
		function _ldap_not_supported() {
			echo '<div class="error"><p><strong>' . __( 'LDAP Not Supported', ADAUTHINT_TEXT_DOMAIN ) . '</strong></p><p>' . __( 'Your PHP configuration does not appear to support LDAP connections; therefore, the Active Directory Authentication Integration plug-in will not work at all. It is recommended that you deactivate the plug-in until you are able to update your PHP configuration to support LDAP.', ADAUTHINT_TEXT_DOMAIN ) . '</p></div>';
		} /* _ldap_not_supported() function */
		
		/**
		 * Print the current log to an admin notice
		 */
		function _flush_log_to_notice() {
			if( $this->_loglevel && !empty( $this->_debug ) ) {
				echo '<div class="updated"><p><strong>' . __( 'ADAuthInt Log Information', ADAUTHINT_TEXT_DOMAIN ) . '</strong></p>';
				echo '<div>';
				$this->_log_flush();
				echo '</div>';
				echo '</div>';
			}
		} /* _flush_log_to_notice() function */
		
		/**
		 * Set the log level
		 * @param int the level to which to set the log
		 */
		function setLogLevel($level=ADAI_LOG_NONE) {
			if( !is_numeric( $level ) )
				return;
			$this->_loglevel = $level;
			return true;
		}
		
		/**
		 * Initiate the plug-in
		 *
		 * Loads the WordPress text domain for this plugin
		 * @uses load_plugin_textdomain
		 * @uses ADAUTHINT_TEXT_DOMAIN
		 * @uses ADAUTHINT_PATH
		 */
		function init_plugin() {
			load_plugin_textdomain( ADAUTHINT_TEXT_DOMAIN, false, ADAUTHINT_PATH . '/languages/' );
		}
		
		/**
		 * Load (and instantiate, if necessary) all of the options for this plug-in
		 * @param array $values_to_use the array of values to use for the options
		 * @param bool $force_update whether or not to force an update to the stored options
		 * @uses ADAuthInt_Plugin::options_info
		 * @uses ADAuthInt_Plugin::options
		 * @uses maybe_unserialize()
		 * @uses get_site_option()
		 * @uses get_option()
		 * @uses ADAuthInt_Plugin::build_options_objs()
		 */
		function _load_options( $values_to_use=NULL, $force_update=false ) {
			$this->options_info = $this->get_options_info();
			
			foreach( $this->options_info as $opt=>$optinfo ) {
				$tmpopt = $optinfo;
				array_walk( $tmpopt, 'extract_val_from_optinfo' );
				$this->_get_option( $opt, $values_to_use );
				
				if( $this->options[$opt] === false || empty( $this->options[$opt] ) ) {
					$this->options[$opt] = $this->_add_options_to_database( $tmpopt, $opt );
				} elseif( $force_update ) {
					$this->options[$opt] = $this->_add_options_to_database( $this->options[$opt], $opt );
				}
			}
			$this->build_options_objs();
		} /* _load_options() function */
		
		/**
		 * Retrieves and sets a specific option
		 * @param string $key the name of the option to retrieve
		 * @param array $values_to_use the array of values to use
		 */
		protected function _get_option( $key, $values_to_use=array() ) {
			if( is_array( $values_to_use ) && array_key_exists( $key, $values_to_use ) )
				return $this->options[$key] = $values_to_use[$key];
			
			if( false === ( $tmp = get_option( $key, false ) ) ) {
				if( false === ( $tmp = get_site_option( $key, false ) ) ) {
					if( function_exists( 'get_mnetwork_option' ) ) {
						$tmp = get_mnetwork_option( $key, false );
					}
				}
			}
			return $this->options[$key] = $tmp;
		}
		
		/**
		 * Add this plugin's options to the database
		 *
		 * If the options for this plugin are not found in the database, or 
		 * the options array is empty, we need to either add the new options
		 * or update them appropriately.
		 * @param array $tmpopt The array of default options to be added
		 * @param string $opt The name of the option to be updated
		 * @return array The array of updated options retrieved from the database
		 * @uses ADAuthInt_Plugin::_log()
		 * @uses ADAuthInt_Plugin::options
		 * @uses add_site_option()
		 * @uses update_site_option()
		 * @uses add_option()
		 * @uses update_option()
		 * @uses maybe_unserialize()
		 * @uses ADAI_IS_NETWORK_ACTIVE
		 */
		protected function _add_options_to_database( $tmpopt = array(), $opt = NULL ) {
			if( empty( $opt ) ) /* If our option name is empty, return our original array */
				return $tmpopt;
			if( !count( $tmpopt ) ) /* If our default options are empty, return the database options */
				return maybe_unserialize( ( ADAI_IS_NETWORK_ACTIVE ) ? get_site_option( $opt ) : get_option( $opt ) );
			
			if( ADAI_IS_NETWORK_ACTIVE ) {
				if( $this->options[$opt] === false ) {
					add_site_option( $opt, $tmpopt );
					$this->_log( ADAI_LOG_INFO, 'The network options did not exist for ' . $opt . ', so they were added to the database.' );
				} else {
					update_site_option( $opt, $tmpopt );
					$this->_log( ADAI_LOG_INFO, 'The network options existed for ' . $opt . ', but they were empty, so they were updated in the database.' );
				} /* If the database options are boolean false (don't exist) or just empty */
				return maybe_unserialize( get_site_option( $opt ) );
			} else {
				if( $this->options[$opt] === false ) {
					add_option( $opt, $tmpopt );
					$this->_log( ADAI_LOG_INFO, 'The options did not exist for ' . $opt . ', so they were added to the database.' );
				} else {
					update_option( $opt, $tmpopt );
					$this->_log( ADAI_LOG_INFO, 'The options existed for ' . $opt . ', but they were empty, so they were updated in the database.' );
				} /* If the database options are boolean false (don't exist) or just empty */
				return maybe_unserialize( get_option( $opt ) );
			} /* If ADAI_IS_NETWORK_ACTIVE or not */
		} /* _add_options_to_database() function */
		
		/**
		 * Builds the ADAuthInt_Plugin::options_info array
		 */
		function get_options_info() {
			require( ADAUTHINT_ABS_DIR . '/inc/active-directory-authentication-integration.default-options.php' );
			if( is_array( $default_options_info ) )
				return $default_options_info;
			else
				return array();
		} /* get_options_info() function */
		
		/**
		 * Instantiate our ADAuthInt_Option objects
		 * @uses ADAuthInt_Plugin::options_info
		 * @uses ADAuthInt_Plugin::options_objs
		 * @uses ADAuthInt_Option::__construct()
		 */
		function build_options_objs() {
			foreach( $this->options_info as $optgroupname=>$optgroup ) {
				if( !array_key_exists( $optgroupname, $this->options_objs ) )
					$this->options_objs[ $optgroupname ] = array();
				
				foreach( $optgroup as $optname=>$optinfo ) {
					if( property_exists( $this, $optname ) ) {
						if( array_key_exists( $optname, $this->options[$optgroupname] ) ) {
							$this->_log( ADAI_LOG_DEBUG, 'The ' . $optname . ' key exists in our options array.' );
							if( $optinfo['opt_type'] == 'password' ) {
								$this->_log( ADAI_LOG_DEBUG, 'Preparing to decode the ' . $optinfo['opt_name'] . ' field from ' . $this->options[$optgroupname][$optname] );
								$this->options[$optgroupname][$optname] = base64_decode( $this->options[$optgroupname][$optname] );
							}
							$this->$optname = ( $this->options[$optgroupname][$optname] === 'false' ) ? false : $this->options[$optgroupname][$optname];
							$optinfo['opt_val'] = $this->$optname;
						} else {
							$this->_log( ADAI_LOG_DEBUG, 'The ' . $optname . ' key does not exist in our options array.' );
							$this->$optname = $optinfo['opt_val'];
						}
					}
					
					$this->options_info[$optgroupname][$optname]['opt_val'] = $optinfo['opt_val'];
					$options_info = array_merge( array('opt_name' => $optname, 'opt_val' => $optinfo['opt_val'], 'opt_section' => $optgroupname ), $this->options_info[$optgroupname][$optname] );
					$this->options_objs[$optgroupname][$optname] = new ADAuthInt_Option( $options_info );
				}
			}
			return;
		} /* build_options_objs() function */
		
		/**
		 * Display our options page in the WordPress admin area
		 * @uses $this->options
		 * @uses settings_fields()
		 * @uses do_settings_sections()
		 */
		function display_admin_page() {
			if( ( ADAI_IS_NETWORK_ACTIVE && !current_user_can( 'delete_users' ) ) || ( !ADAI_IS_NETWORK_ACTIVE && !current_user_can( 'manage_options' ) ) ) {
	?>
	<div class="wrap">
		<h2><?php _e( 'Active Directory Settings', ADAUTHINT_TEXT_DOMAIN ) ?></h2>
		<p><?php _e( 'You do not have the appropriate permissions to update these options. Please work with an administrator of the site to update the options. Thank you.', ADAUTHINT_TEXT_DOMAIN ) ?></p>
	</div>
	<?php
				return;
			}
			if( isset( $_GET['options-action'] ) ) {
				require_once( ADAUTHINT_ABS_DIR . '/inc/delete-options.php' );
				return;
			}
			if( isset( $_POST['action'] ) && $_POST['action'] == 'update' ) {
				foreach( $this->options_info as $optgroup=>$opts ) {
					/*$this->options[$optgroup] = $this->validate_options_group( $opts, $optgroup, NULL );*/
					$this->options[$optgroup] = $_POST[$optgroup];
				}
				$this->update_wpms_options();
				echo '<div class="updated"><ul>';
				foreach( $this->updated as $k=>$upd ) {
					if( is_array( $upd ) ) {
						echo '<li><h4>Options for Site #' . $k . ':</h4><ul>';
						foreach( $upd as $key=>$up ) {
							printf( __( '<li>The options for the %s group were %supdated%s.</li>', ADAUTHINT_TEXT_DOMAIN ), $this->subtitles[$key], ( $up ? '' : '<strong>not</strong> ' ), ( $up ? ' successfully' : '' ) );
						}
						echo '</ul>';
					} else {
						printf( __( '<li>The options for the %s group were %supdated%s.</li>', ADAUTHINT_TEXT_DOMAIN ), $this->subtitles[$k], ( $upd ? '' : '<strong>not</strong> ' ), ( $upd ? ' successfully' : '' ) );
					}
				}
				echo '</ul><p><em>' . __( 'If any groups indicate they were not successfully updated, that could be because no options within that group were modified. If you did make modifications to those groups; you should check to verify that those modifications were correctly committed.' ) . '</em></p></div>';
			}
			if( $this->_loglevel >= ADAI_LOG_DEBUG ) {
				ob_start();
				echo "Options Info:\n";
				var_dump( $this->options_info );
				echo "\n\nOptions:\n";
				var_dump( $this->options );
				echo "\n\nOptions Objects:\n";
				var_dump( $this->options_objs );
				$this->_log( ADAI_LOG_DEBUG , ob_get_contents() );
				ob_end_clean();
			}
	?>
	<div class="wrap">
		<h2><?php _e( 'Active Directory Settings', ADAUTHINT_TEXT_DOMAIN ) ?></h2>
		<div id="poststuff" class="metabox-holder">
			<div id="post-body">
				<div id="post-body-content">
		<form method="post" action="<?php echo ( ADAI_IS_NETWORK_ACTIVE && is_network_admin() ) ? '' : 'options.php'; ?>">
        	<?php settings_fields( ADAUTHINT_OPTIONS_PAGE ); ?>
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
            <input type="hidden" name="action" value="update"/>
	<?php
			do_meta_boxes( ADAUTHINT_OPTIONS_PAGE, 'normal', NULL );
			/*settings_fields( ADAUTHINT_OPTIONS_PAGE );
			do_settings_sections( ADAUTHINT_OPTIONS_PAGE );*/
	?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes', ADAUTHINT_TEXT_DOMAIN) ?>"/>
			</p>
		</form>
				</div><!-- #post-body-content -->
			</div><!-- #post-body -->
		</div><!-- #poststuff --><br class="clear">
	</div><!-- .wrap -->
	<?php
			$this->_log_flush();
			return true;
		} /* display_admin_page() function */
		
		/**
		 * Update options for a whole network
		 *
		 * Only invoked when the plugin is network-active. If
		 * the plugin is only active on one blog/site, or the
		 * multi-site functions of WordPress are not set up,
		 * this function is not called.
		 */
		function update_wpms_options() {
			if( !is_network_admin() )
				return;
			if( ADAI_IS_NETWORK_ACTIVE ) {
				/* If the WP Multi Network plug-in is installed and active, 
				 * then we should propagate the settings to all other networks
				 */
				if( ADAI_IS_MULTINETWORK && $this->update_all_networks ) {
					global $wpdb;
					$networks = $wpdb->get_results( $wpdb->prepare( 'SELECT DISTINCT id FROM ' . $wpdb->site ) );
					if( is_array( $networks ) ) { /* We retrieved an array of site IDs */
						if( !method_exists( $this, 'switch_to_site' ) )
							continue;
							
						foreach( $networks as $network ) {
							global $adai_current_network, $adai_original_network, $site_id;
							$adai_current_network = $network->id;
							$adai_original_network = $site_id;
							
							$this->_log( ADAI_LOG_INFO, 'Preparing to update sitewide options for ' . $network->id );
							$this->switch_to_site( $adai_current_network );
							if( current_user_can( 'delete_users' ) ) {
								$this->update_site_options( $adai_current_network, $adai_original_network );
							} else {
								$this->_log( ADAI_LOG_ERROR, 'The current user does not appear to be a super admin for the network with an ID of ' . $adai_current_network . ', therefore, the site options for that network were not updated.' );
							}
							$this->restore_current_site();
						}
					} elseif( current_user_can( 'delete_users' ) ) { /* We only found one network */
						$this->_log( ADAI_LOG_INFO, 'WPMN plugin was found, but only one network seems to exist, so only options for the current network are updated.' );
						$this->update_site_options();
					} else {
						$this->_log( ADAI_LOG_ERROR, 'Multiple networks were not found, so an attempt was made to update the current network. However, the current user does not appear to have the appropriate permissions to perform this action. Therefore, no options were updated.' );
					}
				} else { /* The WPMN plugin is not installed or active */
					$this->_log( ADAI_LOG_INFO, 'The WPMN plugin was not found, so only options for current network are updated' );
					if( current_user_can( 'delete_users' ) )
						$this->update_site_options();
					else
						$this->_log( ADAI_LOG_ERROR, 'The current user does not appear to have the appropriate permissions to update these options.' );
				}
			} else {
				foreach( array_keys( $this->options_info ) as $optgroup ) {
					$this->updated[$optgroup] = update_option( $optgroup, $this->options[$optgroup] );
				}
			}
		} /* update_wpms_options() function */
		
		function update_site_options( $current_network=NULL, $original_network=NULL ) {
			foreach( array_keys( $this->options_info ) as $optgroup ) {
				if( $current_network == $original_network || ( array_key_exists( 'update_all_networks', $_POST[ $optgroup ] ) && $_POST[$optgroup]['update_all_networks'] == 'true' ) ) {
					$this->_log( ADAI_LOG_INFO, 'The network options for ' . $optgroup . ' were updated' . ( ( !empty( $current_network ) ) ? ' on the network with an ID of ' . $current_network : '' ) . '.' );
					if( false === get_site_option( $optgroup ) )
						$this->updated[$current_network][$optgroup] = add_site_option( $optgroup, $this->options[$optgroup] );
					else
						$this->updated[$current_network][$optgroup] = update_site_option( $optgroup, $this->options[$optgroup] );
				}
			}
			if( is_null( $current_network ) && is_null( $original_network ) ) {
				$tmp = array();
				foreach( $this->updated[$current_network] as $k=>$v ) {
					$tmp[$k] = $v;
				}
				$this->updated = $tmp;
			}
		}
		
		/**
		 * Initiate our admin page
		 * @uses ADAuthInt_Plugin::options
		 * @uses register_setting()
		 * @uses add_settings_section()
		 * @uses add_settings_field()
		 */
		function init_admin(){
			if( !count( $this->options_objs ) )
				$this->_load_options();
			
			foreach( $this->options_info as $optgroup=>$options ) {
				if( ADAI_IS_NETWORK_ACTIVE && !is_network_admin() && 'adauthint_auth_opts' != $optgroup ) {
					continue;
				}
				if( is_array( $options ) ) {
					if( function_exists( 'add_meta_box' ) ) {
						add_meta_box( 'meta-' . $optgroup, __( $this->subtitles[$optgroup], ADAUTHINT_TEXT_DOMAIN ), array( $this, 'make_settings_meta_boxes' ), ADAUTHINT_OPTIONS_PAGE, 'normal', 'high', array( 'id' => $optgroup ) );
					}
					
					register_setting( ADAUTHINT_OPTIONS_PAGE, $optgroup, array( $this, 'validate_options_' . $optgroup ) );
					
					add_settings_section( 
						/*$id = */$optgroup . '_group', 
						/*$title = */__( $this->subtitles[$optgroup], ADAUTHINT_TEXT_DOMAIN ), 
						/*$callback = */array( $this, 'build_options_' . $optgroup ), 
						/*$page = */ADAUTHINT_OPTIONS_PAGE
					);
					
					foreach( array_keys( $options ) as $k ) {
						add_settings_field( 
							/*$id =*/ $k, 
							/*$title =*/ __( $this->options_objs[$optgroup][$k]->opt_label, ADAUTHINT_TEXT_DOMAIN ), 
							/*$callback =*/ array( &$this->options_objs[$optgroup][$k], 'build_field' ), 
							/*$page =*/ ADAUTHINT_OPTIONS_PAGE, 
							/*$section =*/ $optgroup . '_group', 
							/*$args =*/ array( 'label_for' => $this->options_objs[$optgroup][$k]->opt_section . '_' . $k )
						);
					}
				}
			}
		} /* init_admin() function */
		
		/**
		 * Output the appropriate meta box for our settings
		 */
		function make_settings_meta_boxes() {
			$opt = func_get_args();
			$opt = array_pop( $opt );
			$id = str_replace( 'meta-', '', $opt['id'] );
			
			$section_notes = array(
				'adauthint_server_opts'		=> 'Information about the active directory server.',
				'adauthint_user_opts'		=> 'Information about how to handle individual user accounts',
				'adauthint_security_opts'	=> 'Various security options to help prevent brute-force attacks',
				'adauthint_auth_opts'		=> 'Authorization options, allowing you to set up specific user groups based on Active Directory groups, etc.',
			);
			
?>
			<div class="note" id="<?php echo $id ?>_note" style="margin: -6px -6px 0; padding: 1em; background: #f9f9f9;">
				<p><strong><?php echo $section_notes[$id]; ?></strong></p>
<?php
					if( ADAI_IS_MULTINETWORK && is_network_admin() ) {
?>
				<p>
					<label>
						<input type="checkbox" name="<?php echo $id ?>[update_all_networks]" value="true" checked="checked"/> 
						Should this set of options be updated for all of your networks?
					</label>
				</p>
<?php
					}
?>
			</div>
		<table class="form-table">
<?php
			do_settings_fields( ADAUTHINT_OPTIONS_PAGE, $id . '_group' );
?>
        </table>
<?php
		}
		
		/**
		 * Build the Server Options group
		 * @uses ADAuthInt_Plugin::build_options_group()
		 */
		function build_options_adauthint_server_opts() {
			$this->build_options_group( $this->options_info['adauthint_server_opts'], 'adauthint_server_opts' );
			return;
		} /* build_options_adauthint_server_opts function */
		/**
		 * Build the User Options group
		 * @uses ADAuthInt_Plugin::build_options_group()
		 */
		function build_options_adauthint_user_opts() {
			$this->build_options_group( $this->options_info['adauthint_user_opts'], 'adauthint_user_opts' );
			return;
		} /* build_options_adauthint_user_opts function */
		/**
		 * Build the Authority Options group
		 * @uses ADAuthInt_Plugin::build_options_group()
		 */
		function build_options_adauthint_auth_opts() {
			$this->build_options_group( $this->options_info['adauthint_auth_opts'], 'adauthint_auth_opts' );
			return;
		} /* build_options_adauthint_auth_opts function */
		/**
		 * Build the Security Options group
		 * @uses ADAuthInt_Plugin::build_options_group()
		 */
		function build_options_adauthint_security_opts() {
			$this->build_options_group( $this->options_info['adauthint_security_opts'], 'adauthint_security_opts' );
			return;
		} /* build_options_adauthint_security_opts function */
		/**
		 * General options building function
		 *
		 * This function is used to build each of the options groups for this plug-in
		 * @param array $group The appropriate portion of the ADAuthInt_Plugin::options_info array for this group of options
		 * @param string $sect The name of the options group to build
		 */
		function build_options_group( $group=array(), $sect ) {
			if( $this->_loglevel >= ADAI_LOG_DEBUG ) {
				ob_start();
				var_dump( $this->options_objs );
				$this->_log( ADAI_LOG_DEBUG, ob_get_contents() );
				ob_end_clean();
			}
			
			$section_notes = array(
				'adauthint_server_opts'		=> 'Information about the active directory server.',
				'adauthint_user_opts'		=> 'Information about how to handle individual user accounts',
				'adauthint_security_opts'	=> 'Various security options to help prevent brute-force attacks',
				'adauthint_auth_opts'		=> 'Authorization options, allowing you to set up specific user groups based on Active Directory groups, etc.',
			);
			echo '
			<div class="note" id="' . $sect . '_note">
				' . $section_notes[$sect];
					if( ADAI_IS_MULTINETWORK && is_network_admin() )
						echo '
				<p>
					<label>
						<input type="checkbox" name="' . $sect . '[update_all_networks]" value="true" checked="checked"/> 
						Should this set of options be updated for all of your networks?
					</label>
				</p>';
			echo
				'
			</div>';
			
			return;
		} /* build_options_group function */
	
		/**
		 * Validate the Server Options
		 * @uses ADAuthInt_Plugin::validate_options_group()
		 */
		function validate_options_adauthint_server_opts( $input ) {
			$input = $this->validate_options_group( $this->options_info['adauthint_server_opts'], 'adauthint_server_opts', $input, 'validate_options_adauthint_server_opts' );
			if( $this->_loglevel >= ADAI_LOG_DEBUG ) {
				ob_start();
				var_dump( $input );
				$this->_log( ADAI_LOG_DEBUG, ob_get_contents() );
				ob_end_clean();
			}
			return $input;
		} /* validate_options_adauthint_server_opts function */
		/**
		 * Validate the User Options
		 * @uses ADAuthInt_Plugin::validate_options_group()
		 */
		function validate_options_adauthint_user_opts( $input ) {
			$input = $this->validate_options_group( $this->options_info['adauthint_user_opts'], 'adauthint_user_opts', $input, 'validate_options_adauthint_user_opts' );
			if( $this->_loglevel >= ADAI_LOG_DEBUG ) {
				ob_start();
				var_dump( $input );
				$this->_log( ADAI_LOG_DEBUG, ob_get_contents() );
				ob_end_clean();
			}
			return $input;
		} /* validate_options_adauthint_user_opts */
		/**
		 * Validate the Authority Options
		 * @uses ADAuthInt_Plugin::validate_options_group()
		 */
		function validate_options_adauthint_auth_opts( $input ) {
			$input = $this->validate_options_group( $this->options_info['adauthint_auth_opts'], 'adauthint_auth_opts', $input, 'validate_options_adauthint_auth_opts' );
			if( $this->_loglevel >= ADAI_LOG_DEBUG ) {
				ob_start();
				var_dump( $input );
				$this->_log( ADAI_LOG_DEBUG, ob_get_contents() );
				ob_end_clean();
			}
			return $input;
		} /* validate_options_adauthint_auth_opts */
		/**
		 * Validate the Security Options
		 * @uses ADAuthInt_Plugin::validate_options_group()
		 */
		function validate_options_adauthint_security_opts( $input ) {
			$input = $this->validate_options_group( $this->options_info['adauthint_security_opts'], 'adauthint_security_opts', $input, 'validate_options_adauthint_security_opts' );
			if( $this->_loglevel >= ADAI_LOG_DEBUG ) {
				ob_start();
				var_dump( $input );
				$this->_log( ADAI_LOG_DEBUG, ob_get_contents() );
				ob_end_clean();
			}
			return $input;
		} /* validate_options_adauthint_security_opts */
		/**
		 * General Validation Function
		 *
		 * This function is used to validate all options used by this function
		 * @param array $group the appropriate part of the options_info array
		 * @param string $sect the name of the group of options to be validated
		 * @param array $input the array of submitted form values
		 */
		function validate_options_group( $group=array(), $sect, $input, $referring_function=NULL ) {
			$this->_log( ADAI_LOG_DEBUG, 'Preparing to save ' . $sect . ' site options as called by the $referring_function function.' );
			if( !wp_verify_nonce( $_REQUEST['_wpnonce'], ADAUTHINT_OPTIONS_PAGE . '-options' ) )
				return false;
			
			$options = ( ADAI_IS_NETWORK_ACTIVE ) ? get_site_option( $sect ) : get_option( $sect );
			$options = maybe_unserialize( $options );
			
			if( empty( $input ) )
				$input = $_POST[$sect];
			if( $this->_loglevel >= ADAI_LOG_DEBUG ) {
				ob_start();
				var_dump( $input );
				$this->_log( ADAI_LOG_DEBUG, ob_get_contents() );
				ob_end_clean();
			}
			
			foreach( $this->options_info[$sect] as $optname=>$optval ) {
				if( $this->_loglevel >= ADAI_LOG_DEBUG ) {
					ob_start();
					var_dump( $options );
					print('<br/>');
					var_dump( $optname );
					$this->_log( ADAI_LOG_DEBUG, ob_get_contents() );
					ob_end_clean();
				}
				if( !array_key_exists( $optname, $input ) )
					$input[$optname] = false;
				$options[$optname] = $this->options_objs[$sect][$optname]->validate_field( $input[$optname] );
				$this->options_info[$sect][$optname]['opt_val'] = $this->options_objs[$sect][$optname]->opt_value;
				$this->$optname = $this->options_objs[$sect][$optname]->opt_value;
				$this->options[$sect][$optname] = $this->options_objs[$sect][$optname]->opt_value;
			}
			
			if( $this->_loglevel >= ADAI_LOG_DEBUG ) {
				ob_start();
				print( "<!-- Our updated options for the $sect group:\n" );
				var_dump( $options );
				print( "\n-->\n" );
				var_dump( $options );
				print( '<br/><br/>' );
				$this->_log( ADAI_LOG_DEBUG, ob_get_contents() );
				ob_end_clean();
			}
			
			return $options;
		} /* validate_options_group function */
	
		/**
		 * Adds 'Settings' link to main WordPress Plugins page
		 */
		function add_settings_link( $links ){
			global $wp_version;
			$options_page = ( ( ADAI_IS_NETWORK_ACTIVE ) ? ( ( version_compare( $wp_version, '3.0.9', '>' ) ) ? 'settings' : 'ms-admin' ) : 'options-general' );
			
			$slink = array( 'settings_link' => '<a href="' .
				$options_page .
					'.php?page=' .
					ADAUTHINT_OPTIONS_PAGE .
				'">' .
				__( 'Settings', ADAUTHINT_TEXT_DOMAIN ) .
				'</a>' );
			$links = array_merge( $slink, $links );
			
			$slink = array( 'delete_settings_link' => '<a href="' .
				wp_nonce_url( $options_page . 
					'.php?options-action=delete&page=' .
					ADAUTHINT_OPTIONS_PAGE, '_adai_options' ) .
				'">' .
				__( 'Delete Settings', ADAUTHINT_TEXT_DOMAIN ) .
				'</a>' );
			$links = array_merge( $links, $slink );
			
			return $links;
		} /* add_settings_link function */
	
		/**
		 * Setup admin settings page
		 */
		function setup_admin(){
			global $wp_version;
			
			if( ADAI_IS_NETWORK_ACTIVE ) { /* This plug-in is network activated */
				$options_page = ( version_compare( $wp_version, '3.0.9', '>' ) ) ? 'settings.php' : 'ms-admin.php';
				/* Add the new options page to the Super Admin menu */
				add_submenu_page( 
					/*$parent_slug = */$options_page, 
					/*$page_title = */'AD Authentication Integration Settings', 
					/*$menu_title = */'AD Authentication Integration', 
					/*$capability = */'delete_users', 
					/*$menu_slug = */ADAUTHINT_OPTIONS_PAGE, 
					/*$function = */array($this, 'display_admin_page')
				);
			}
			if( !is_network_admin() && function_exists( 'add_options_page' ) ) { /* This plug-in is only active on 1 blog */
				/* Add the new options page to the Settings menu */
				add_options_page(
					/*$page_title = */'AD Authentication Integration Settings', 
					/*$menu_title = */'AD Authentication Integration', 
					/*$capability = */'delete_users', /* Should restrict settings to admins for regular installs and super admins for multi-site installs */
					/*$menu_slug = */ADAUTHINT_OPTIONS_PAGE, 
					/*$function = */array($this, 'display_admin_page')
				);
			}
		} /* setup_admin function */
		
		function _set_masked_passwords_for_log( $mask ) {
			$this->_mask_passwords_in_log = ( false === $mask ) ? false : true;
		}
		
	} /* ADAuthInt_Plugin class */
}
?>
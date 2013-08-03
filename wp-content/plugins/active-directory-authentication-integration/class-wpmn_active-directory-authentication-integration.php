<?php
/**
 * Class and method definitions for the Multi-Network version of the Active Directory Authentication Integration plug-in
 * @package wordpress
 * @subpackage ADAuthInt
 * @version 0.6
 */

if( !class_exists( 'ADAuthInt_Plugin' ) )
	/**
	 * Require the original class definition for this plugin
	 */
	require_once( str_replace( 'class-wpmn_', 'class-', __FILE__ ) );

if( !class_exists( 'WPMN_ADAuthInt_Plugin' ) ) {
	/**
	 * An extended class to handle Multi-Network aspects of the ADAuthInt plugin
	 */
	class WPMN_ADAuthInt_Plugin extends ADAuthInt_Plugin {
		/**
		 * General Option - Update All Networks
		 *
		 * If the WP Multi Network plugin is installed and activated, we need to know whether or not
		 * to propagate the settings for this plug-in across all networks, or just the current network.
		 * @default true
		 * @access private
		 * @var bool
		 */
		protected $update_all_networks = true;
		
		/**
		 * PHP4 Constructor function
		 */
		function WPMN_ADAuthInt_Plugin() {
			return $this->__construct();
		}
		
		/**
		 * PHP5 Constructor function
		 */
		function __construct() {
			parent::__construct();
		}
		
		function add_settings_link( $links ) {
			$links = parent::add_settings_link( $links );
			if( strstr( __FILE__, 'mu-plugins' ) )
				return $links;
			
			global $wp_version;
			$options_page = ( ( ADAI_IS_NETWORK_ACTIVE ) ? ( ( version_compare( $wp_version, '3.0.9', '>' ) ) ? 'settings' : 'ms-admin' ) : 'options-general' );
			
			$links['multinetwork_activate'] = '<br/><a href="' .
				wp_nonce_url( $options_page .
					'.php?options-action=multi_network_activate&page=' .
					ADAUTHINT_OPTIONS_PAGE, '_adai_options' ) .
				'">' .
				__( 'Multi-Network Activate', ADAUTHINT_TEXT_DOMAIN ) .
				'</a>';
			$links['multinetwork_deactivate'] = '<a href="' .
				wp_nonce_url( $options_page .
				'.php?options-action=multi_network_deactivate&page=' .
				ADAUTHINT_OPTIONS_PAGE, '_adai_options' ) .
				'">' .
				__( 'Deactivate On All Networks', ADAUTHINT_TEXT_DOMAIN ) .
				'</a>';
			
			return $links;
		}
		
		function setup_admin() {
			if( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'networks' ) {
				/* If the WP Multi Network plug-in is active, we want to add
				 * the settings for this plug-in to the array of options to
				 * be cloned when a new network is created. */
				global $options_to_copy;
				if( is_array( $options_to_copy ) ) {
					$options_to_copy = array_merge( array( 
					'adauthint_server_opts'		=> __( 'Server Options for the AD Authentication Integration plugin', ADAUTHINT_TEXT_DOMAIN ),
					'adauthint_user_opts'		=> __( 'User Options for the AD Authentication Integration plugin', ADAUTHINT_TEXT_DOMAIN ),
					'adauthint_auth_opts'		=> __( 'Authorization Options for the AD Authentication Integration plugin', ADAUTHINT_TEXT_DOMAIN ),
					'adauthint_security_opts'	=> __( 'Security Options for the AD Authentication Integration plugin', ADAUTHINT_TEXT_DOMAIN ),
					), $options_to_copy );
				}
			}
			
			parent::setup_admin();
		}
		
		function display_admin_page() {
			
			if( isset( $_GET['options-action'] ) && stristr( $_GET['options-action'], 'network_' ) )
				return require_once( ADAUTHINT_ABS_DIR . '/inc/multi_network_activation.php' );

			return parent::display_admin_page();
		}
		
		/**
		 * Determine how to best switch networks
		 * @param int $site_id the ID of the network to which to switch
		 * @uses wpmn_super_admins::switch_to_network()
		 * @since 0.1a
		 */
		function switch_to_site( $site_id ) {
			$this->switch_to_network( $site_id );
			
			return true;
		}
		/**
		 * Perform the actual network switch
		 * @param int $new_site_id the ID of the network to which to switch
		 * @uses $GLOBALS['wpdb']
		 * @uses $GLOBALS['previous_site']
		 * @uses $GLOBALS['current_site']
		 * @uses get_current_site()
		 * @since 0.2a
		 */
		function switch_to_network( $new_site_id ) {
			global $wpdb;
			$site_info = $wpdb->get_results( $wpdb->prepare( "SELECT site_id, blog_id FROM $wpdb->blogs GROUP BY site_id" ) );
			if( empty( $site_info ) )
				return false;
			
			foreach( $site_info as $s ) {
				if( $new_site_id == $s->site_id )
					$new_blog_id = $s->blog_id;
			}
			if( empty( $new_blog_id ) )
				return false;
			
			$GLOBALS['previous_site']->site_id = $GLOBALS['site_id'];
			$GLOBALS['previous_site']->blog_id = $wpdb->set_blog_id( $new_blog_id, $new_site_id );
			$GLOBALS['current_site'] = get_current_site();
			return true;
		}
		
		/**
		 * Determine how to best switch back to original network
		 * @uses wpmn_super_admins::restore_current_network()
		 * @since 0.1a
		 */
		function restore_current_site() {
			$this->restore_current_network();
			return true;
		}
		/**
		 * Perform the actual network switch
		 * @uses $GLOBALS['wpdb']
		 * @uses $GLOBALS['previous_site']
		 * @uses $GLOBALS['current_site']
		 * @uses get_current_site()
		 * @since 0.2a
		 */
		function restore_current_network() {
			if( !isset( $GLOBALS['previous_site'] ) || empty( $GLOBALS['previous_site'] ) )
				return false;
			
			global $wpdb;
			$wpdb->set_blog_id( $GLOBALS['previous_site']->blog_id, $GLOBALS['previous_site']->site_id );
			$GLOBALS['current_site'] = get_current_site();
		}
	}
}
?>
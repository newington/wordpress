<?php

/*
Plugin Name: Active Directory Authentication Integration 
Version: 0.6.1
Plugin URI: http://plugins.ten-321.com/category/active-directory-authentication-integration/
Description: Allows WordPress to authenticate, authorize, create and update users through Active Directory
Author: Curtiss Grymala
Author URI: http://ten-321.com/

The work is derived version 0.9.9.9 of the Active Directory Integration plug-in by Christoph Steindorff, ECW GmbH, which is in turn derived from version 1.0.5 of the plugin Active Directory Authentication:
Active Directory Authentication URI: http://soc.qc.edu/jonathan/wordpress-ad-auth
Active Directory Authentication Description: Allows WordPress to authenticate users through Active Directory
Active Directory Authentication Author: Jonathan Marc Bearak
Active Directory Authentication Author URI: http://soc.qc.edu/jonathan

Active Directory Integration URI: http://blog.ecw.de/wp-ad-integration
Active Directory Integration Description: Allows WordPress to authenticate, authorize, create and update users through Active Directory
Active Directory Integration Author: Christoph Steindorff, ECW GmbH
Active Directory Integration Author URI: http://blog.ecw.de/
*/

require_once( 'inc/constants-active-directory-authentication-integration.php' );

if( !function_exists( 'is_multinetwork' ) || ! function_exists( 'is_plugin_active_for_network' ) || ! function_exists( 'multinetwork_activate' ) )
	require_once( 'inc/function-is_multinetwork.php' );

global $ADAuthIntObj;
add_action( 'plugins_loaded', 'instantiate_adai_plugin' );
function instantiate_adai_plugin() {
	global $ADAuthIntObj;
	if( !defined( 'ADAI_IS_NETWORK_ACTIVE' ) )
		define( 'ADAI_IS_NETWORK_ACTIVE', ( stristr( __FILE__, 'mu-plugins' ) ) ? 
			true : 
			is_plugin_active_for_network( ADAUTHINT_PLUGIN_BASENAME ) );
	
	if( !defined( 'ADAI_IS_MULTINETWORK' ) )
		define( 'ADAI_IS_MULTINETWORK', is_multinetwork() );
		
	if( !class_exists( 'ADAuthInt_Plugin' ) ) {
		if( ADAI_IS_MULTINETWORK )
			require_once( 'class-wpmn_' . basename(__FILE__) );
		else
			require_once( 'class-' . basename(__FILE__) );
	}
	$ADAuthIntObj = ( ADAI_IS_MULTINETWORK ) ? new WPMN_ADAuthInt_Plugin() : new ADAuthInt_Plugin();
	$ADAuthIntObj->setLogLevel(ADAI_LOG_NONE);
	return $ADAuthIntObj;
}
?>
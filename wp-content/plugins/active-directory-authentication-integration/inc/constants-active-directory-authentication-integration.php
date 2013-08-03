<?php
/**
 * Constant definitions for the Active Directory Authentication Integration plug-in
 * @package wordpress
 * @subpackage ADAuthInt
 * @version 0.6
 */

if( !defined( 'ADAUTHINT_PLUGIN_BASENAME' ) )
	/**
	 * A constant to hold the folder/filename of this plug-in
	 */
	define( 'ADAUTHINT_PLUGIN_BASENAME', plugin_basename( str_replace( 'inc/constants-', '', __FILE__ ) ) );

if( !defined( 'ADAUTHINT_PATH' ) )
	/**
	 * A constant to hold the folder in which this plug-in is stored
	 */
	define( 'ADAUTHINT_PATH', dirname( ADAUTHINT_PLUGIN_BASENAME ) );

if( !defined( 'ADAUTHINT_ABS_PATH' ) )
	/**
	 * A constant to hold the absolute path to our main plug-in file
	 */
	define( 'ADAUTHINT_ABS_PATH', ( ( stristr( __FILE__, 'mu-plugins' ) ) ? WPMU_PLUGIN_DIR : WP_PLUGIN_DIR ) . '/' . ADAUTHINT_PLUGIN_BASENAME );
	
if( !defined( 'ADAUTHINT_ABS_DIR' ) )
	/**
	 * A constant to hold the absolute path to the directory in which our plug-in is stored
	 */
	define( 'ADAUTHINT_ABS_DIR', ( ( stristr( __FILE__, 'mu-plugins' ) ) ? WPMU_PLUGIN_DIR : WP_PLUGIN_DIR ) . '/' . str_replace( '/inc/' . basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) );
	
if( !defined( 'ADAUTHINT_OPTIONS_PAGE' ) )
	/**
	 * A constant to hold the name of our options page
	 */
	define( 'ADAUTHINT_OPTIONS_PAGE', /*str_replace( 'class-', '', basename( __FILE__ ) )*/'adauthint_options' );

if( !defined( 'ADAUTHINT_TEXT_DOMAIN' ) )
	/**
	 * A constant to hold the name of our WordPress plugin_text_domain
	 */
	define( 'ADAUTHINT_TEXT_DOMAIN', 'adauthint_dom' );

if( !defined( 'ADAI_PLUGIN_VERSION' ) )
	/**
	 * The version number for this plug-in
	 */
	define( 'ADAI_PLUGIN_VERSION', '0.1' );

if( !defined( 'ADAI_LOG_DEBUG' ) ) {
	define('ADAI_LOG_DEBUG', 6);
	define('ADAI_LOG_INFO',  5);
	define('ADAI_LOG_NOTICE',4);
	define('ADAI_LOG_WARN',  3);
	define('ADAI_LOG_ERROR', 2);
	define('ADAI_LOG_FATAL', 1);
	define('ADAI_LOG_NONE',  0);
}

if( !function_exists( 'extract_val_from_optinfo' ) ) {
	/**
	 * Extract the option value from the options_info array
	 *
	 * This is a callback used with the PHP array_walk() function
	 * @param array &$array the array being walked
	 * @param string $key the array key of the item currently being manipulated
	 */
	function extract_val_from_optinfo( &$array, $key ) {
		if( !is_array( $array ) )
			return;
		$array = ( !empty( $array['opt_val'] ) ) ? $array['opt_val'] : '';
	} /* extract_val_from_optinfo() function */
}

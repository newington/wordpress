<?php
 
/**
 * lg_version()
 * Get Lazyest Gallery Current version from plugin file
 * @return string
 * 
 */
function lg_version() {
  require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
  global $lg_gallery;
  $plugin_data = get_plugin_data( $lg_gallery->plugin_file );
  return $plugin_data['Version'];
}  

/**
 * Last version where options or database settings have changed
 */
define('LG_SECURE_VERSION', '1.1');

?>
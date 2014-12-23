<?php
/*
Plugin Name: IsMultiNetwork Functions
Plugin URI: http://umw.edu/
Description: This plugin doesn't instantiate any actions automatically, but it does add a handful of functions to make plugin management simpler in a multinetwork installation. Functions are available to check to see if this is a multinetwork installation and to activate/deactivate plugins across all networks in the installation.
Version: 0.2
Author: Curtiss Grymala (http://umw.edu/)
*/
/**
 * Various functions to perform actions in a multi-network environment
 * @package WordPress
 * @subpackage MultiNetwork
 * @version 0.2
 */

if( !function_exists( 'is_multinetwork' ) ) {
	/**
	 * Checks to see if this is a multi-network environment
	 *
	 * Checks to see if the Networks+, WP MultiNetwork or Networks for WordPress plugin
	 * 		is installed and active
	 * @return bool whether or not this is multi-network
	 * @since 0.1
	 * 
	 * @uses WP_PLUGIN_DIR
	 * @uses WPMU_PLUGIN_DIR
	 * @uses $wpdb
	 */
	function is_multinetwork() {
		if( function_exists( 'wpmn_switch_to_network' ) || function_exists( 'switch_to_site' ) || function_exists( 'ra_network_page' ) )
			return true;
			
		if( !file_exists( WP_PLUGIN_DIR . '/wordpress-multi-network/wordpress-multi-network.php' ) && !file_exists( WPMU_PLUGIN_DIR . '/wordpress-multi-network.php' ) && !file_exists( WP_PLUGIN_DIR . '/networks-for-wordpress/index.php' ) && !file_exists( WPMU_PLUGIN_DIR . '/networks-for-wordpress.php' ) && !file_exists( WP_PLUGIN_DIR . '/Networks-Plus/ra-networks.php' ) && !file_exists( WPMU_PLUGIN_DIR . '/ra-network.php' ) )
			return false;
		
		global $wpdb;
		$plugins = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM " . $wpdb->sitemeta . " WHERE meta_key = 'active_sitewide_plugins'" ) );
		foreach( $plugins as $plugin ) {
			if( array_key_exists( 'wordpress-multi-network/wordpress-multi-network.php', maybe_unserialize( $plugin->meta_value ) ) ) {
				require_once( WP_PLUGIN_DIR . '/wordpress-multi-network/wordpress-multi-network.php' );
				return true;
			} elseif( array_key_exists( 'networks-for-wordpress/index.php', maybe_unserialize( $plugin->meta_value ) ) ) {
				require_once( WP_PLUGIN_DIR . '/networks-for-wordpress/index.php' );
				return true;
			} elseif( array_key_exists( 'Networks-Plus/ra-networks.php', maybe_unserialize( $plugin->meta_value ) ) ) {
				require_once( WP_PLUGIN_DIR . '/Networks-Plus/ra-networks.php' );
				return true;
			}
		}
		$sites = $wpdb->get_results( $wpdb->prepare( "SELECT blog_id FROM " . $wpdb->blogs ) );
		foreach( $sites as $site ) {
			$oldblog = $wpdb->set_blog_id( $site->blog_id );
			$plugins = $wpdb->get_results( $wpdb->prepare( "SELECT option_value FROM " . $wpdb->options . " WHERE option_name = 'active_plugins'" ) );
			foreach( $plugins as $plugin ) {
				if( array_key_exists( 'wordpress-multi-network/wordpress-multi-network.php', maybe_unserialize( $plugin->option_value ) ) ) {
					require_once( WP_PLUGIN_DIR . '/wordpress-multi-network/wordpress-multi-network.php' );
					return true;
				} elseif( array_key_exists( 'networks-for-wordpress/index.php', maybe_unserialize( $plugin->option_value ) ) ) {
					require_once( WP_PLUGIN_DIR . '/networks-for-wordpress/index.php' );
					return true;
				} elseif( array_key_exists( 'Networks-Plus/ra-networks.php', maybe_unserialize( $plugin->option_value ) ) ) {
					require_once( WP_PLUGIN_DIR . '/Networks-Plus/ra-networks.php' );
					return true;
				}
			}
			$wpdb->set_blog_id( $oldblog );
		}
		
		return false;
	}
}

if( !function_exists( 'is_plugin_active_for_network' ) ) {
	/**
	 * Check to see if a plug-in is network activated
	 * @uses maybe_unserialize()
	 * @uses get_site_option()
	 */
	/*function is_plugin_active_for_network( $plugin_file ) {
		if( !is_array( $asp = maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) ) ) )
			return false;
		return ( array_key_exists( $plugin_file, $asp ) );
	}*/ /* is_plugin_network_active() function */
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if( !function_exists( 'multinetwork_activate' ) ) {
	/**
	 * Network-activate a plugin across all networks in a multi-network installation
	 * 
	 * @param string $file the plugin to be deactivated
	 * @param string $redirect the location to which to redirect once we're finished
	 * @param bool $network_wide whether this plugin should be network-activated or not (should
	 *		always be true)
	 * @param bool $silent whether to fire the normal deactivation actions/filters
	 * @return bool false if this is not a multi-network environment; true if it is
	 * @since 0.2
	 *
	 * @uses $wpdb to perform various actions on the database
	 * @uses do_action() hooks the pre_mn_deactivate_$plugin action to perform any actions
	 * 		that need to occur prior to deactivation (such as removing settings)
	 *		Sends the site_id and blog_id of the network on which the plugin is being deactivated
	 * @uses do_action() hooks the mn_deactivated_$plugin action to perform any actions that
	 * 		need to occur once the plugin has been deactivated
	 *		Sends the site_id and blog_id of the network on which the plugin is being deactivated
	 */
	function multinetwork_activate( $file, $redirect='', $network_wide=true, $silent=false ) {
		if( !is_multinetwork() )
			return false;
		
		$plugin = plugin_basename( trim( $file ) );
		$rt = array();
			
		global $wpdb;
		$site_info = $wpdb->get_results( $wpdb->prepare( "SELECT site_id, blog_id FROM $wpdb->blogs WHERE site_id != %d GROUP BY site_id", $GLOBALS['site_id'] ) );
		
		if( !empty( $site_info ) ) {
			if( !isset( $original_site ) ) {
				$original_site = array(
					'site_id' => $GLOBALS['site_id'],
					'blog_id' => $GLOBALS['blog_id'],
				);
			}
			array_unshift( $site_info, (object)$original_site );
			foreach( $site_info as $network ) {
				$update = true;
				$create = false;
				if( !current_user_can( 'manage_network_plugins' ) )
					continue;
				if( !$silent )
					do_action( "pre_mn_activate_$plugin", $network );
				
				$asp = maybe_unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->sitemeta} WHERE meta_key=%s AND site_id=%d", 'active_sitewide_plugins', $network->site_id ) ) );
				if( is_array( $asp ) && !array_key_exists( $plugin, $asp ) ) {
					$rt[] = sprintf( __( 'The %s plugin was added to the list of active sitewide plugins for the network with an ID of %s', ISMN_TEXT_DOMAIN ), $plugin, $network->site_id );
					$asp = array_merge( $asp, array( $plugin => time() ) );
				} elseif( empty( $asp ) ) {
					$rt[] = sprintf( __( 'The %s plugin was added as the first item in the list of active sitewide plugins for the network with an ID of %d', ISMN_TEXT_DOMAIN ), $plugin, $network->site_id );
					$asp = array( $plugin => time() );
					$create = true;
				} else {
					$update = false;
					$rt[] = sprintf( __( 'The %s plugin is already active on network %d', ISMN_TEXT_DOMAIN ), $plugin, $network->site_id );
				}
				
				$wpdb->update( $wpdb->sitemeta, array( 'meta_value' => maybe_serialize( $asp ) ), array( 'meta_key' => 'active_sitewide_plugins' , 'site_id' => $network->site_id ), array( '%s' ), array( '%s', '%d' ) );
				
				if( $update ) {
					if( $create )
						$activated = $wpdb->insert( $wpdb->sitemeta, array( 'meta_value' => maybe_serialize( $asp ), 'meta_key' => 'active_sitewide_plugins', 'site_id' => $network->site_id ), array( '%s', '%s', '%d' ) );
					else
						$activated = $wpdb->update( $wpdb->sitemeta, array( 'meta_value' => maybe_serialize( $asp ) ), array( 'meta_key' => 'active_sitewide_plugins' , 'site_id' => $network->site_id ), array( '%s' ), array( '%s', '%d' ) );
					if( false === $activated )
						$rt[] = sprintf( __( 'There was an error executing the query to activate the %s plugin on the network with an ID of %d:<br/>%s', ISMN_TEXT_DOMAIN ), $plugin, $network->site_id, $wpdb->print_error() );
					else
						$rt[] = sprintf( __( 'The %s plugin was activated successfully on network %d', ISMN_TEXT_DOMAIN ), $plugin, $network->site_id );
				if( !$silent )
					do_action( "mn_activated_$plugin", $network );
				}
			}
			unset( $original_site );
			return $rt;
		}
		
		return true;
	}
}

if( !function_exists( 'multinetwork_deactivate' ) ) {
	/**
	 * Network-deactivate a plugin or multiple plugins across all networks 
	 * in a multi-network installation
	 *
	 * @param array|string $plugins the list of plugins to be deactivated
	 * @param bool $silent whether to fire the normal deactivation actions/filters
	 * @return bool false if this is not a multi-network environment; true if it is
	 * @since 0.2
	 *
	 * @uses $wpdb to perform various actions on the database
	 * @uses do_action() hooks the pre_mn_deactivate_$plugin action to perform any actions
	 * 		that need to occur prior to deactivation (such as removing settings)
	 *		Sends the site_id and blog_id of the network on which the plugin is being deactivated
	 * @uses do_action() hooks the mn_deactivated_$plugin action to perform any actions that
	 * 		need to occur once the plugin has been deactivated
	 *		Sends the site_id and blog_id of the network on which the plugin is being deactivated
	 */
	function multinetwork_deactivate( $plugins, $silent = false ) {
		if( !is_multinetwork() )
			return false;
		
		$rt = array();
		foreach( (array)$plugins as $plugin ) {
			$plugin = plugin_basename( trim( $plugin ) );
			
			global $wpdb;
			$site_info = $wpdb->get_results( $wpdb->prepare( "SELECT site_id, blog_id FROM $wpdb->blogs WHERE site_id != %d GROUP BY site_id", $GLOBALS['site_id'] ) );
			if( !empty( $site_info ) ) {
				if( !isset( $original_site ) ) {
					$original_site = array(
						'site_id' => $GLOBALS['site_id'],
						'blog_id' => $GLOBALS['blog_id'],
					);
				}
				array_unshift( $site_info, (object)$original_site );
				foreach( $site_info as $network ) {
					$update = true;
					if( !current_user_can( 'manage_network_plugins' ) )
						continue;
					if( !$silent )
						do_action( "pre_mn_deactivate_{$plugin}", $network );
					
					$asp = maybe_unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->sitemeta} WHERE meta_key=%s AND site_id=%d", 'active_sitewide_plugins', $network->site_id ) ) );
					if( is_array( $asp ) && array_key_exists( $plugin, $asp ) ) {
						$rt[] = sprintf( __( 'The plugin %s was removed from the list of active plugins on the network with an ID of %d', ISMN_TEXT_DOMAIN ), $plugin, $network->site_id );
						unset( $asp[$plugin] );
					} else {
						$rt[] = sprintf( __( 'The plugin %s was not active on network %d, so no action was taken.', ISMN_TEXT_DOMAIN ), $plugin, $network->site_id );
						$update = false;
					}
					
					if( $update ) {
						$activated = $wpdb->update( $wpdb->sitemeta, array( 'meta_value' => maybe_serialize( $asp ) ), array( 'meta_key' => 'active_sitewide_plugins' , 'site_id' => $network->site_id ), array( '%s' ), array( '%s', '%d' ) );
						if( false === $activated )
							$rt[] = sprintf( __( 'There was an error executing the query to deactivate the %s plugin on the network with an ID of %d:<br/>%s', ISMN_TEXT_DOMAIN ), $plugin, $network->site_id, $wpdb->print_error() );
						else
							$rt[] = sprintf( __( 'The %s plugin was deactivated successfully on network %d', ISMN_TEXT_DOMAIN ), $plugin, $network->site_id );
					if( !$silent )
						do_action( "mn_deactivated_{$plugin}", $network );
					}
				}
				unset( $original_site );
			}
		}
		
		return count($rt) ? $rt : true;
	}
}
if( !function_exists( 'multinetwork_enable' ) ) {
	/**
	 * Enables a theme across all networks
	 * @param string $theme the folder in which the theme exists
	 * @return bool|array returns false if this isn't multinetwork, true if no sites are 
	 * 		found or any array of messages about the enable process on each individual network
	 */
	function multinetwork_enable( $theme ) {
		if( !is_multinetwork() )
			return false;
		$rt = array();
		if( is_array( $theme ) ) {
			foreach( $theme as $t ) {
				$tmp = multinetwork_enable( $t );
				if( is_array( $tmp ) ) {
					if( is_array( $rt ) )
						$rt = array_merge( $rt, $tmp );
				} else {
					$rt = $tmp;
				}
			}
		}
		global $wpdb;
		$site_info = $wpdb->get_results( $wpdb->prepare( "SELECT site_id, blog_id FROM $wpdb->blogs WHERE site_id != %d GROUP BY site_id", $GLOBALS['site_id'] ) );
		if( !empty( $site_info ) ) {
			if( !isset( $original_site ) ) {
				$original_site = array(
					'site_id' => $GLOBALS['site_id'],
					'blog_id' => $GLOBALS['blog_id'],
				);
			}
			array_unshift( $site_info, (object)$original_site );
			foreach( $site_info as $network ) {
				$update = true;
				if( !current_user_can( 'manage_network_themes' ) )
					continue;
				$allowed_themes = maybe_unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->sitemeta WHERE site_id = %d AND meta_key = %s", $network->site_id, 'allowedthemes' ) ) );
				if( is_array( $allowed_themes ) && !array_key_exists( $theme, $allowed_themes ) ) {
					$rt[] = sprintf( __( 'The %s theme was added to the list of enabled themes for the network with an ID of %d', ISMN_TEXT_DOMAIN ), $theme, $network->site_id );
					$allowed_themes[$theme] = true;
				} elseif( empty( $allowed_themes ) ) {
					$rt[] = sprintf( __( 'The %s theme was added as the first item in the list of enabled themes for the network with an ID of %d', ISMN_TEXT_DOMAIN ), $theme, $network->site_id );
					$allowed_themes = array( $theme => true );
				} else {
					$update = false;
					$rt[] = sprintf( __( 'The %s theme is already enabled on network %d', ISMN_TEXT_DOMAIN ), $theme, $network->site_id );
				}
				if( $update ) {
					$activated = $wpdb->update( $wpdb->sitemeta, array( 'meta_value' => maybe_serialize( $allowed_themes ) ), array( 'meta_key' => 'allowedthemes' , 'site_id' => $network->site_id ), array( '%s' ), array( '%s', '%d' ) );
					if( false === $activated )
						$rt[] = sprintf( __( 'There was an error executing the query to enable the %s theme on the network with an ID of %d:<br/>%s', ISMN_TEXT_DOMAIN ), $theme, $network->site_id, $wpdb->print_error() );
					else
						$rt[] = sprintf( __( 'The %s theme was enabled successfully on network %d', ISMN_TEXT_DOMAIN ), $theme, $network->site_id );
				}
			}
			unset( $original_site );
			return $rt;
		}
		return true;
	}
}
if( !function_exists( 'multinetwork_disable' ) ) {
	/**
	 * Disables a theme across all networks
	 * @param string $theme the folder in which the theme exists
	 * @return bool|array returns false if this isn't multinetwork, true if no sites are 
	 * 		found or any array of messages about the disable process on each individual network
	 */
	function multinetwork_disable( $theme ) {
		if( !is_multinetwork() )
			return false;
		$rt = array();
		if( is_array( $theme ) ) {
			foreach( $theme as $t ) {
				$tmp = multinetwork_disable( $t );
				if( is_array( $tmp ) ) {
					if( is_array( $rt ) )
						$rt = array_merge( $rt, $tmp );
				} else {
					$rt = $tmp;
				}
			}
			return $rt;
		}
		global $wpdb;
		$site_info = $wpdb->get_results( $wpdb->prepare( "SELECT site_id, blog_id FROM $wpdb->blogs WHERE site_id != %d GROUP BY site_id", $GLOBALS['site_id'] ) );
		if( !empty( $site_info ) ) {
			if( !isset( $original_site ) ) {
				$original_site = array(
					'site_id' => $GLOBALS['site_id'],
					'blog_id' => $GLOBALS['blog_id'],
				);
			}
			array_unshift( $site_info, (object)$original_site );
			foreach( $site_info as $network ) {
				$update = true;
				if( !current_user_can( 'manage_network_themes' ) )
					continue;
				$allowed_themes = maybe_unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->sitemeta WHERE site_id = %d AND meta_key = %s", $network->site_id, 'allowedthemes' ) ) );
				if( is_array( $allowed_themes ) && array_key_exists( $theme, $allowed_themes ) ) {
					$rt[] = sprintf( __( 'The %s theme was removed from the list of enabled themes for the network with an ID of %d', ISMN_TEXT_DOMAIN ), $theme, $network->site_id );
					unset($allowed_themes[$theme]);
				} else {
					$update = false;
					$rt[] = sprintf( __( 'The %s theme is already disabled on network %d', ISMN_TEXT_DOMAIN ), $theme, $network->site_id );
				}
				if( $update ) {
					$activated = $wpdb->update( $wpdb->sitemeta, array( 'meta_value' => maybe_serialize( $allowed_themes ) ), array( 'meta_key' => 'allowedthemes' , 'site_id' => $network->site_id ), array( '%s' ), array( '%s', '%d' ) );
					if( false === $activated )
						$rt[] = sprintf( __( 'There was an error executing the query to enable the %s theme on the network with an ID of %d:<br/>%s', ISMN_TEXT_DOMAIN ), $theme, $network->site_id, $wpdb->print_error() );
					else
						$rt[] = sprintf( __( 'The %s theme was disabled successfully on network %d', ISMN_TEXT_DOMAIN ), $theme, $network->site_id );
				}
			}
			unset( $original_site );
			return $rt;
		}
		return true;
	}
}
if( !function_exists( 'multinetwork_activate_theme' ) ) {
	/**
	 * Activates a theme on all of the existing networks
	 * @param string $theme the folder in which the theme exists (parent theme)
	 * @param string $theme the folder in which the stylesheet exists (child theme)
	 * @return array() a list of messages about whether or not the theme was activated on each network
	 */
	function multinetwork_activate_theme( $theme, $stylesheet ) {
		if( !is_multinetwork() )
			return false;
		$rt = array();
		global $wpdb;
		$blog_list = maybe_unserialize( $wpdb->get_results( $wpdb->prepare( "SELECT site_id, blog_id FROM $wpdb->blogs ORDER BY site_id" ) ) );
		$original_site = array( 'site_id' => $GLOBALS['site_id'], 'blog_id' => $GLOBALS['blog_id'] );
		foreach( $blog_list as $blog ) {
			$wpdb->set_blog_id( $blog->blog_id, $blog->site_id );
			$wpdb->update( $wpdb->options, array( 'option_value' => $theme ), array( 'option_name' => 'template' ), '%s', '%s' );
			$wpdb->update( $wpdb->options, array( 'option_value' => $stylesheet ), array( 'option_name' => 'stylesheet' ), '%s', '%s' );
			$rt[] = sprintf( __( 'Updated active theme for blog with an ID of %d on the network %d to %s', ISMN_TEXT_DOMAIN ), $blog->blog_id, $blog->site_id, $theme );
		}
		$wpdb->set_blog_id( $original_site['blog_id'], $original_site['site_id'] );
		return $rt;
	}
}
if( !function_exists( 'get_multinetwork_active_plugins' ) ) {
	/**
	 * Retrieves a list of the plugins that are currently active on all networks
	 * @return array the list of plugins
	 */
	function get_multinetwork_active_plugins() {
		return get_mnetwork_option( 'mnetwork_active_plugins', array() );
	}
}
if( !function_exists( 'is_multinetwork_active' ) ) {
	/**
	 * Checks to see if a plugin is active on all networks
	 * @param string $plugin the plugin to check
	 * @return bool whether or not the plugin is active on all networks
	 * @uses get_multinetwork_active_plugins()
	 */
	function is_multinetwork_active( $plugin ) {
		$mnap = get_multinetwork_active_plugins();
		return array_key_exists( $plugin, $mnap );
	}
}
if( !function_exists( 'recount_multinetwork_active_plugins' ) ) {
	/**
	 * Rebuilds the list of plugins that are active on all networks
	 * @return array the list of plugins
	 *
	 * @uses update_mnetwork_option()
	 */
	function recount_multinetwork_active_plugins() {
		global $wpdb;
		$plugins = $wpdb->get_col( $wpdb->prepare( "SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key=%s", 'active_sitewide_plugins' ) );
		$p = null;
		foreach( $plugins as $pl ) {
			$pl = maybe_unserialize( $pl );
			if( empty( $pl ) || !is_array( $pl ) )
				return array();
			if( null === $p )
				$p = $pl;
			$p = array_intersect( $p, $pl );
		}
		update_mnetwork_option( 'mnetwork_active_plugins', $p );
		return $p;
	}
}
?>
<?php
/**
 * Handle Multi-Network actions for the ADAuthInt Plugin
 * @package wordpress
 * @subpackage ADAuthInt
 * @version 0.6
 */

check_admin_referer( '_adai_options' );

if( !isset( $_GET['options-action'] ) )
	exit;

if( !defined('ADAI_IS_MULTINETWORK') || !ADAI_IS_MULTINETWORK )
	exit;

global $wpdb;

echo '
	<div class="wrap">
		<h2>' . __('AD Authentication Integration - Multi-Network Activation') . '</h2>';

if( !class_exists( 'WPMN_ADAuthInt_Plugin' ) ) {
	require_once( ADAUTHINT_ABS_DIR . '/class-wpmn_active-directory-authentication-integration.php' );
}

$WPMN_ADAuthInt_Plugin_Obj = new WPMN_ADAuthInt_Plugin;

if( $_GET['options-action'] == 'multi_network_activate' ) {
	$main_site_id = $wpdb->siteid;
	
	$networks = $wpdb->get_results( $wpdb->prepare( 'SELECT DISTINCT id FROM ' . $wpdb->site ) );
	if( count( $networks ) ) {
		$GLOBALS['adai_options'] = array();
		foreach( array_keys( $WPMN_ADAuthInt_Plugin_Obj->options_info ) as $optgroup ) {
			$GLOBALS['adai_options'][$optgroup] = maybe_unserialize( get_site_option( $optgroup ) );
			if( false === $GLOBALS['adai_options'][$optgroup] )
				maybe_unserialize( get_option( $optgroup ) );
			if( false == $GLOBALS['adai_options'][$optgroup] )
				unset( $GLOBALS['adai_options'][$optgroup] );
		}
		if( empty( $GLOBALS['adai_options'] ) )
			$GLOBALS['adai_options'] = NULL;
		
		$GLOBALS['force_adai_options_update'] = true;
		
		foreach( $networks as $network ) {
			if( $main_site_id == $network->id ) {
				print( '<p>' . sprintf( __( 'We skipped over the network with an ID of %d, because the plugin already appears to be network active on that site.', ADAUTHINT_TEXT_DOMAIN ), $network->id ) . '</p>' );
				continue;
			}
			
			$output = '';
			
			$opts_updated = false;
			$WPMN_ADAuthInt_Plugin_Obj->switch_to_site( $network->id );
			if( current_user_can( 'delete-users' ) ) {
				$asp = maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) );
				if( empty( $asp ) || !array_key_exists( ADAUTHINT_PLUGIN_BASENAME, $asp ) ) {
					if( empty( $asp ) ) {
						$asp = array( ADAUTHINT_PLUGIN_BASENAME => time() );
					} else {
						$asp = array_merge( $asp, array( ADAUTHINT_PLUGIN_BASENAME => time() ) );
					}
					update_site_option( 'active_sitewide_plugins', $asp );
					if( !isset( $WPMN_ADAuthInt_Plugin_Obj ) ) {
						$WPMN_ADAuthInt_Plugin_Obj = new WPMN_ADAuthInt_Plugin();
						$opts_updated = true;
					} else {
						$WPMN_ADAuthInt_Plugin_Obj->_load_options( $GLOBALS['adai_options'], $GLOBALS['force_adai_options_update'] );
						$opts_updated = true;
					}
					if( $opts_updated ) {
						$output .= '<p>' . sprintf( __( 'The AD Authentication Integration options were successfully updated for the network with an ID of %d, as well.', ADAUTHINT_TEXT_DOMAIN ), $network->id ) . '</p>';
					}
					$output = '<p>' . __( 'The AD Authentication Integration plug-in was successfully network-activated on the network with an ID of ', ADAUTHINT_TEXT_DOMAIN ) . $network->id . '</p>' . $output;
				} else {
					$output = '<p>' . sprintf( __( 'The AD Authentication Integration plug-in was already network-active on the network with an ID of %d, therefore, no changes were made.', ADAUTHINT_TEXT_DOMAIN ), $network->id ) . '</p>' . $output;
				}
			} else {
				$output = '<p>' . __( 'You do not have the appropriate permissions to network activate this plug-in on the network with an ID of ', ADAUTHINT_TEXT_DOMAIN ) . $network->id . '</p>' . $output;
			}
			echo $output;
			
			$WPMN_ADAuthInt_Plugin_Obj->restore_current_site();
		}
		echo '</div>';
	} else {
		echo '<p>' . __( 'Multiple networks could not be found, therefore, no additional changes were made.', ADAUTHINT_TEXT_DOMAIN ) . '</p>';
		echo '</div>';
	}
} elseif( $_GET['options-action'] == 'multi_network_deactivate' ) {
	$networks = $wpdb->get_results( $wpdb->prepare( 'SELECT DISTINCT id FROM ' . $wpdb->site ) );
	if( count( $networks ) ) {
		foreach( $networks as $network ) {
			$WPMN_ADAuthInt_Plugin_Obj->switch_to_site( $network->id );
			if( current_user_can( 'delete-users' ) ) {
				$asp = maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) );
				if( is_array( $asp ) && array_key_exists( ADAUTHINT_PLUGIN_BASENAME, $asp ) ) {
					unset( $asp[ADAUTHINT_PLUGIN_BASENAME] );
					/*$asp = array_splice( $asp, array_search( $asp[ADAUTHINT_PLUGIN_BASENAME], $asp ), 1 );*/
					update_site_option( 'active_sitewide_plugins', $asp );
					echo '<p>' . __( 'The AD Authentication Integration plug-in was successfully deactivated for the network with an ID of ', ADAUTHINT_TEXT_DOMAIN ) . $network->id . '</p>';
				} else {
					echo '<p>';
					printf( __( 'The AD Authentication Integration plug-in was not network-active on the network with an ID of %d, therefore, no changes were made.', ADAUTHINT_TEXT_DOMAIN ), $network->id );
					echo '</p>';
				}
			} else {
				echo '<p>' . __( 'You do not have the appropriate permissions to network deactivate this plug-in on the network with an ID of ', ADAUTHINT_TEXT_DOMAIN ) . $network->id . '</p>';
			}
			$WPMN_ADAuthInt_Plugin_Obj->restore_current_site();
		}
		echo '</div>';
	} else {
		echo '<p>' . __( 'Multiple networks could not be found, therefore, no additional changes were made.', ADAUTHINT_TEXT_DOMAIN ) . '</p>';
		echo '</div>';
	}
}
<?php
/*
Plugin Name: Lazyest Gallery Extra Fields
Plugin URI: http://brimosoft.nl/lazyest/gallery/
Description: Add your extra fields to Folders and Images in Lazyest Gallery 
Author: Brimosoft
Author URI: http://brimosoft.nl
Version: 1.1.12
Date: 2012 June
License: GNU GPLv2
Text Domain: lazyest-gallery/languages
*/

/**
 * LazyestFields
 * This Plugin adds functionality to Lazyest Gallery to add user defined fields
 * - Adds box to Lazyest Gallery setting to edit field  names and types
 * - Adds simple filters and actions to display the fields 
 * 
 * @package Lazyest Gallery
 * @author Marcel Brinkkemper
 * @copyright 2011-2012 Brimosoft
 * @version 1.1.0
 * @access public
 */
class LazyestFields {
  
  var $fields;
    
  /**
   * LazyestFields::__construct()
   * 
   * @since 1.1.0
   * @uses add_action()
   * @uses add_filter()
   * @uses get_option()
   * @return void
   */
  function __construct() {
    // core actions
    add_action( 'lazyest_ready', array( &$this, 'ready' ) );  
    add_action( 'lazyest_settings_main', array( &$this, 'settings_main' ) );
    
    // admin filters
    add_filter( 'lazyest_update_options', array( &$this, 'update_options') );
    
    // frontend filters and actions
    add_filter( 'lazyest_thumbs_folder_header', array( &$this, 'folder_header' ), 5, 2 );
    add_filter( 'lazyest_after_folder_description', array( &$this, 'folder_description' ), 5, 2 );
    add_filter( 'lazyest_thumb_description', array( &$this, 'thumb_description' ), 5, 2 );
    add_action( 'lazyest_frontend_slide', array( &$this, 'frontend_slide' ), 5, 2 );
    
    $options = get_option( 'lazyest-fields' );
    $this->fields = ( false !== $options ) ? $options : array();
    
  }
  
  /**
   * LazyestFields::ready()
   * Adds fields to Lazyest Gallery
   * 
   * @since 1.1.0
   * @return void
   */
  function ready() {
    if ( 0 < count( $this->fields) ) {
      foreach( $this->fields as $field ) {
        lg_add_extrafield( $field['name'], $field['display'], $field['target'], $field['edit'] ); 
      }
    }
  }
  
  /**
   * LazyestFields::fields_rows()
   * Adds rows to edit fields in the Settings page
   * 
   * @since 1.1.0
   * @return void
   */
  function fields_rows() {
    $tdname = '<td><input id="name_%d" name=lazyest-gallery[extra][%d][name] type="text" value="%s" size="16"  /></td>';
    $tddisplay = '<td><input id="display_%d" name="lazyest-gallery[extra][%d][display]" type="text" value="%s" size="32"  /></td>';
    $tdtarget = '<td><select id="target_%d" name="lazyest-gallery[extra][%d][target]"><option value="image"%s>%s</option><option value="folder"%s>%s</option></select></td>';
    $tdedit = '<td><input type="checkbox" id="edit_%d" name="lazyest-gallery[extra][%d][edit]" %s /></td>';
    $i = 0;
    $row = '<tr>';
    $row .= sprintf( $tdname, $i, $i, '' ); 
    $row .= sprintf( $tddisplay, $i, $i, '' );
    $row .= sprintf( $tdtarget, $i, $i, '',
      esc_html__( 'Image', 'lazyest-gallery' ), '',
      esc_html__( 'Folder', 'lazyest-gallery' )
    );
    $row .= sprintf( $tdedit, $i, $i, '' );
    $row .= '</tr>'; 
    echo $row;  
    if ( 0 < count( $this->fields ) ) {
      for( $i = 1; $i <= count( $this->fields ); $i++ ) {
        $field = $this->fields[$i-1]; 
        if ( '' == $field['name'] )
          continue;
        $row = '<tr>';
        $row .= sprintf( $tdname, $i, $i, esc_attr( $field['name'] ) ); 
        $row .= sprintf( $tddisplay, $i, $i, esc_attr( $field['display'] ) ); 
        $row .= sprintf( $tdtarget, $i, $i,
          ( 'image' == $field['target'] ) ? ' selected="selected"' : '',
          esc_html__( 'Image', 'lazyest-gallery' ),
          ( 'folder' == $field['target'] ) ? ' selected="selected"' : '',
          esc_html__( 'Folder', 'lazyest-gallery' )
        );
        $row .= sprintf( $tdedit, $i, $i, $field['edit'] ? 'checked="checked"' : '' );
        $row .= '</tr>';
        echo $row;
      }
    }  
  }                          
  
  /**
   * LazyestFields::settings_main()
   * Add extra box with filed edits to the settings page
   * 
   * @since 1.1.0
   * @return void
   */
  function settings_main() {
    global $lg_gallery;
    ?>
    <div id="lg_extra_field_options" class="postbox">
      <h3 class="hndle"><span><?php esc_html_e( 'Extra Fields', 'lazyest-gallery' ) ?></span></h3>
      <div class="inside">
        <p><?php esc_html_e( 'Enter your own fields to be stored along with your Folders or Images.', 'lazyest-gallery' ); ?></p>
      </div>
      <table id="lg_extra_fields_table" class="widefat">
        <thead>
          <tr>
            <th scope="col"><?php esc_html_e( 'Name', 'lazyest-gallery' ) ?></th>
            <th scope="col"><?php esc_html_e( 'Display Name', 'lazyest-gallery' ) ?></th>
            <th scope="col"><?php esc_html_e( 'Type', 'lazyest-gallery' ) ?></th>
            <th scope="col"><?php esc_html_e( 'Editable', 'lazyest-gallery' ) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php $this->fields_rows(); ?>                              								
        </tbody>
      </table>
    </div>
    <?php    
  }
  
  
  /**
   * LazyestFields::update_options()
   * Filter the options from the settings page and save the fields
   * 
   * @since 1.1.0
   * @uses esc_attr()
   * @uses update_option()
   * @param array $options
   * @return array
   */
  function update_options( $options ) {
    if ( isset( $options['extra'] ) ) {      
      $this->fields = array();
      foreach( $options['extra'] as $field ) {  
        if ( '' == $field['name'] )
          continue;
        $temp = esc_attr( $field['name'] ); 
        $field['name'] = esc_attr( strtolower( str_replace( ' ', '', $field['name'] ) ) );
        $field['display'] = ( isset( $field['display']) && ( '' != $field['display'] ) ) ? strip_tags( $field['display'] ) : ucfirst( $temp );
        $field['edit'] = ( isset( $field['edit'] ) ) ? true : false;
        $this->fields[] = $field;
      }
      update_option( 'lazyest-fields', $this->fields );
      unset( $options['extra'] ); // don't save them with lazyest-gallery options
    }
    return $options;
  }
  
  /**
   * LazyestFields::folder_header()
   * Appends the Folder fields to the header above the thumbs
   * 
   * @since 1.1.0
   * @param string $header
   * @param LazyestFolders $folder
   * @return string
   */
  function folder_header( $header, $folder ) {
    if ( 0 != count( $this->fields ) ) {
      foreach( $this->fields as $field ) {
        if ( 'folder' == $field['target'] ) {
          $header .= sprintf ( '<div class="extra-field %s"><p><span class="name">%s</span> <span class="value">%s</span></p></div>', $field['name'],
            esc_html( $field['display'] ), 
            lg_html( $folder->get_extra_field( $field['name'] ) )
          );
        }
      } 
    }
    return $header;
  }
  
  /**
   * LazyestFields::folder_description()
   * Appends extra fileds to folder description in thumbnail view
   * 
   * @since 1.1.10
   * @param string $description
   * @param LazyestFolder $folder
   * @return void
   */
  function folder_description( $after, $folder ) {
  	if ( 0 != count( $this->fields ) ) {
      foreach( $this->fields as $field ) {
        if ( 'folder' == $field['target'] ) {
          $after .= sprintf ( '<div class="extra-field %s"><p><span class="name">%s</span> <span class="value">%s</span></p></div>', $field['name'],
            esc_html( $field['display'] ), 
            lg_html( $folder->get_extra_field( $field['name'] ) )
          );
        }
      } 
    }
    return $after;
  }
  
  /**
   * LazyestFields::frontend_slide()
   * Appends the fields to the text below the slide
   * 
   * @since 1.1.0
   * @param LazyestImage $image
   * @return void
   */
  function frontend_slide( $image ) {
    if ( 0 != count( $this->fields ) ) {
      foreach( $this->fields as $field ) {
        if ( 'image' == $field['target'] ) {
          printf ( '<div class="extra-field %s"><p><span class="name">%s</span> <span class="value">%s</span></p></div>', $field['name'],
            esc_html( $field['display'] ), 
            lg_html( $image->get_extra_field( $field['name'] ) )
          );
        }
      } 
    }
  }
  
  /**
   * LazyestFields::thumb_description()
   * 
   * @param string $description
   * @param LazyestImage $image
   * @return string
   */
  function thumb_description( $description, $image ) {
  	if ( 0 != count( $this->fields ) ) {
      foreach( $this->fields as $field ) {
        if ( 'image' == $field['target'] ) {
          $description .= sprintf ( '<div class="extra-field %s"><p><span class="name">%s</span> <span class="value">%s</span></p></div>', $field['name'],
            esc_html( $field['display'] ), 
            lg_html( $image->get_extra_field( $field['name'] ) )
          );
        }
      }     
		}
		return $description;
  }
  
} // LazyestFields

$lg_fields = new LazyestFields();
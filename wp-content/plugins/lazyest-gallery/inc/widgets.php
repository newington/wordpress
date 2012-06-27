<?php

/**
 * Sidebar Widgets for Lazyest Gallery
 * 
 * @package Lazyest-Gallery  
 * @author Marcel Brinkkemper
 * @copyright 2008-2012 Brimosoft
 * @todo cast into classes
 */

add_action( 'widgets_init', 'lg_lazyest_widgets' );

function lg_lazyest_widgets() {

  if ( !  function_exists('wp_register_sidebar_widget') )
    return;

  function lg_widget_list_folders( $args ) {
    extract( $args );
    $options = get_option( 'widget_lg_list_folders' );
    $title = $options['title'];
    if ( empty($title) )
      $title = esc_html__( 'LG Folders', 'lazyest-gallery' );
    echo $before_widget . $before_title . $title . $after_title;
    lg_list_folders( '' );
    echo $after_widget;
  }

  function lg_widget_list_folders_control() {
    $options = $newoptions = get_option( 'widget_lg_list_folders' );
    if ( isset( $_POST["lg_list_folders-submit"] ) ) {
      $newoptions['title'] = strip_tags( stripslashes($_POST["lg_list_folders-title"]) );
    }
    if ( $options != $newoptions ) {
      $options = $newoptions;
      update_option( 'widget_lg_list_folders', $options );
    }
    $title = esc_attr( $options['title'] );

?>
			<p><label for="lg_list_folders-title"><?php

    esc_html_e( 'Title:' );

?> <input class="widefat" id="lg_list_folders-title" name="lg_list_folders-title" type="text" value="<?php

    echo $title;

?>" /></label></p>
			<input type="hidden" id="lg_list_folders-submit" name="lg_list_folders-submit" value="1" />
<?php

  }

  function lg_widget_random_image( $args ) {
    global $lg_gallery;    
    extract( $args );
    $options = get_option( 'widget_lg_random_image' );
    $title = $options['title'];
    $count = $options['count'];
    $folder = utf8_decode( stripslashes( rawurldecode( $options['folder'] ) ) );
    $sub = isset( $options['subfolders'] );
    if ( empty($count) )
      $count = '1';
    if ( empty($title) )
      $title = __( 'LG Random Image' );
    echo $before_widget . $before_title . $title . $after_title;
    lg_random_image( '', $count, $folder, $sub );
    echo $after_widget;
  }

  function lg_widget_random_image_control() {
    global $lg_gallery;
    $options = get_option( 'widget_lg_random_image' );
    if ( isset( $_POST["lg_random_image-submit"] ) ) {
      $options['title'] = isset( $_POST["lg_random_image-title"] ) ? strip_tags( stripslashes( $_POST["lg_random_image-title"] ) ) : '';
      $options['count'] = isset( $_POST['lg_random_image-count'] ) ? $_POST['lg_random_image-count'] : 1;
      $options['folder'] = isset( $_POST['lg_random_image-folder'] ) ? $_POST['lg_random_image-folder'] : '';
      $options['subfolders'] = isset(  $_POST['lg_random_image-sub'] ) ? $_POST['lg_random_image-sub']: '';
      if ( $options['folder'] == '' )
        $options['subfolders'] = 'on';
      update_option( 'widget_lg_random_image', $options );
    }

    $title = esc_attr( $options['title'] );
    $count = $options['count'];
    if ( $count == '' )
      $count = '1';
    $folder = $options['folder'];
    $sub = $options['subfolders'] ? 'checked="checked"' : '';

    $dirlist = $lg_gallery->folders( 'subfolders', 'visible' );

?>
			<p><label for="lg_random_image-title"><?php

    esc_html_e( 'Title:', 'lazyest-gallery' );

?> <input class="widefat" id="lg_random_image-title" name="lg_random_image-title" type="text" value="<?php

    echo $title;

?>" /></label></p>
			<p><label for="lg_random_image-count"><?php

    esc_html_e( 'Number of Images:', 'lazyest-gallery' );

?> <input class="widefat" id="lg_random_image-count" name="lg_random_image-count" type="text" size="2" value="<?php

    echo $count;

?>" /></label></p>
			<p><label for="lg_random_image-folder"><?php

    esc_html_e( 'Folder', 'lazyest-gallery' );

?> <select id="lg_random_image-folder" name="lg_random_image-folder">
				<option value="" <?php

    if ( $folder == '' )
      echo 'selected="selected"';

?> ><?php

    esc_html_e( '(all)', 'lazyest-gallery' );

?></option>
				<?php

    foreach ( $dirlist as $dir ) {
      echo '<option value="' . lg_nice_link( $dir->curdir ) . '"';
      if ( $folder == $dir->curdir )
        echo 'selected="selected"';
      echo ' >' . htmlentities( $dir->curdir ) . '</option>';
    }

?>
			</select></label></p>
			<p><label for="lg_random_image-sub"><?php

    esc_html_e( 'Include Sub Folders', 'lazyest-gallery' );

?> <input type="checkbox" id="lg_random_image-sub" name="lg_random_image-sub" <?php

    echo $sub;

?> /></label></p>
			<input type="hidden" id="lg_random_image-submit" name="lg_random_image-submit" value="1" />
<?php
  
  }

  function lg_widget_slide_show( $args ) {
    global $lg_gallery;
    extract( $args );
    if ( '' == $lg_gallery->get_option('enable_slide_show') ) return false;
    $options = get_option( 'widget_lg_slide_show' );
    $title = $options['title'];
    $count = $options['count'];
    $display = $options['display'];
    $folder = utf8_decode( stripslashes( rawurldecode( $options['folder'] ) ) );
    $sub = $options['subfolders'];
    if ( empty($count) )
      $count = '1';
    if ( empty($title) )
      $title = __( 'LG Slide Show' );
    echo $before_widget . $before_title . $title . $after_title;
    lg_random_slideshow( '', $count, $display, $folder, $sub == 'on' );
    echo $after_widget;
  }

  function lg_widget_slide_show_control() {
    global $lg_gallery;
    if ( '' == $lg_gallery->get_option('enable_slide_show') ) return false;
    $options = get_option( 'widget_lg_slide_show' );
    if ( isset($_POST["lg_slide_show-submit"]) ) {
      $options['title'] = isset( $_POST["lg_slide_show-title"] ) ? strip_tags( stripslashes($_POST["lg_slide_show-title"]) ) : '';
      $options['count'] = isset( $_POST['lg_slide_show-count'] ) ? $_POST['lg_slide_show-count'] : 2;
      $options['display'] = isset( $_POST['lg_slide_show-time'] ) ? $_POST['lg_slide_show-time'] : 5;
      $options['folder'] = isset( $_POST['lg_slide_show-folder'] ) ? $_POST['lg_slide_show-folder'] : '';
      $options['subfolders'] = isset( $_POST['lg_slide_show-sub'] ) ? $_POST['lg_slide_show-sub'] : '';
      if ( $options['folder'] == '' )
        $options['subfolders'] = 'on';
      update_option( 'widget_lg_slide_show', $options );
    }

    $title = esc_attr( $options['title'] );
    $count = $options['count'];
    $display = $options['display'];
    if ( $count == '' )
      $count = '2';
    if ( $display == '' )
      $display = '5';
    $folder = $options['folder'];
    $sub = $options['subfolders'] ? 'checked="checked"' : '';
    $dirlist = $lg_gallery->folders( 'subfolders', 'visible' );

?>
			<p><label for="lg_slide_show-title"><?php

    esc_html_e( 'Title:' );

?> <input class="widefat" id="lg_slide_show-title" name="lg_slide_show-title" type="text" value="<?php

    echo $title;

?>" /></label></p>
			<p><label for="lg_slide_show-count"><?php

    esc_html_e( 'Number of Images:' );

?> <input class="widefat" id="lg_slide_show-count" name="lg_slide_show-count" type="text" size="2" value="<?php

    echo $count;

?>" /></label></p>
			<input class="widefat" id="lg_slide_show-time" name="lg_slide_show-time" type="hidden" size="2" value="<?php

    echo $display;

?>" /></label></p>	
			<p><label for="lg_slide_show-folder"><?php

    esc_html_e( 'Folder', 'lazyest-gallery' );

?> <select id="lg_slide_show-folder" name="lg_slide_show-folder">
				<option value="" <?php

    if ( $folder == '' )
      echo 'selected="selected"';

?> ><?php

    esc_html_e( '(all)', 'lazyest-gallery' );

?></option>
				<?php

    foreach ( $dirlist as $dir ) {
      echo '<option value="' . lg_nice_link( $dir->curdir ) . '"';
      if ( $folder == $dir->curdir )
        echo 'selected="selected"';
      echo ' >' . htmlentities( $dir->curdir ) . '</option>';
    }

?>
			</select></label></p>
			<p><label for="lg_slide_show-sub"><?php

    esc_html_e( 'Include Sub Folders', 'lazyest-gallery' );

?> <input type="checkbox" id="lg_slide_show-sub" name="lg_slide_show-sub" <?php

    echo $sub;

?> /></label></p>
			
			<input type="hidden" id="lg_slide_show-submit" name="lg_slide_show-submit" value="1" />
<?php

  }
global $lg_gallery;
  $widget_ops = array( 'classname' => 'lg_list_folders', 'description' => __("A list of all your Lazyest Gallery Folders",
    'lazyest-gallery') );
  wp_register_sidebar_widget( 'lg-list-folders', __('LG List Folders'),
    'lg_widget_list_folders', $widget_ops );
  wp_register_widget_control( 'lg-list-folders', __('LG List Folders'),
    'lg_widget_list_folders_control' );

  $widget_ops = array( 'classname' => 'lg_random_image', 'description' => __("Random Images from your Lazyest Gallery",
    'lazyest-gallery' ) );
  wp_register_sidebar_widget( 'lg-random-image', __('LG Random Image'),
    'lg_widget_random_image', $widget_ops );
  wp_register_widget_control( 'lg-random-image', __('LG Random Image', 'lazyest-gallery'),
    'lg_widget_random_image_control' );

  if ( 'TRUE' == $lg_gallery->get_option('enable_slide_show') ) {
  $widget_ops = array( 'classname' => 'lg-slide-show', 'description' => __("Slide Show of Thumbnails from your Lazyest Gallery",
    'lazyest-gallery') );
  wp_register_sidebar_widget( 'lg-slide-show', __('LG Slide Show'),
    'lg_widget_slide_show', $widget_ops );
  wp_register_widget_control( 'lg-slide-show', __('LG Slide Show', 'lazyest-gallery'),
    'lg_widget_slide_show_control' );
  }

}

?>
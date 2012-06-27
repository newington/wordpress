<?php
/**
 * LazyestThemes
 * Class to manage and edit Lazyest Gallery themes
 * 
 * @package Lazyest-Gallery
 * @subpackage Themes
 * @version 1.1
 * @since 1.1.0
 * @author Marcel Brinkkemper (lazyest@brimosoft.nl)
 * @copyright 2010-2012 Marcel Brinkkemper 
 * @license GNU GPL
 * 
 */
class LazyestThemes {
  
  /**
   * LazyestThemes::get_available_themes()
   * Retrieves all available themes from the themes directory
   * 
   * @param string $styles_dir
   * @return array filename of stylesheets
   * @since 1.1.0
   */
  function get_available_themes( $styles_dir ) {
    global $lg_gallery;
    $available = array();
    $current = $lg_gallery->get_option( 'style_css' );
    if ( $dir_handle = @opendir( $styles_dir ) ) {
      while ( false !== ( $dir_file = readdir( $dir_handle ) ) ) {
        if ( ! is_dir( $dir_file ) ) {
          $pathinfo = pathinfo( $dir_file );
          if ( isset( $pathinfo['extension'] ) ) {       
            if ( ( 'css' == strtolower( $pathinfo['extension'] ) ) && ( '_' != $pathinfo['basename'][0] ) ) {
              if ( $pathinfo['basename'] != $current ) {
                $available[] = $pathinfo['basename'];  
              }          
            }
          }
        }
      }        
    }
    return $available;
  }
  
  /**
   * LazyestThemes::read_theme_header()
   * Gets all info from a stylesheet header
   * 
   * @param string $style_file
   * @return array 
   * @since 1.1.0
   */
  function read_theme_header( $style_file ) {
    $items = array();
    $unknown = esc_html__( 'Unknown', 'lazyest-gallery' );    
    $items['name'] = $items['author'] = $items['uri'] = $items['description'] = $items['version'] = $items['author_uri'] = $unknown; 
    $items['settings'] = $items['javascript'] = array();
    $contents = file( $style_file );
    $lazyest_theme = false;
    foreach( $contents as $ln => $line ) {
      $end = strpos( $line, '*/' );
      if ( false !== $end ) break;
      if ( false === $lazyest_theme ) $lazyest_theme = strpos( 'Lazyest Gallery Theme' , $line );
      $hit = strpos( $line, 'Theme Name:' );
      if ( false !== $hit ) {
        $items['name'] = trim( substr( $line, 11 ) );
        continue;
      }
      $hit = strpos( $line, 'Theme URI:' );
      if ( false !== $hit ) {
        $items['uri'] = trim( substr( $line, 10 ) );
        continue;
      }
      $hit = strpos( $line, 'Description:' );
      if ( false !== $hit ) {
        $items['description'] = trim( substr( $line, 12 ) );
        continue;
      }
      $hit = strpos( $line, 'Version:' );
      if ( false !== $hit ) {
        $items['version'] = trim( substr( $line, 8 ) );
      }  
      $hit = strpos( $line, 'Author:' );
      if ( false !== $hit ) {
        $items['author'] = trim( substr( $line, 7 ) );
      }  
      $hit = strpos( $line, 'Author URI:' );
      if ( false !== $hit ) {
        $items['author_uri'] = trim( substr( $line, 11 ) );
      }  
      $hit = strpos( $line, 'Required Settings:' ); 
      if ( false !== $hit ) {
        $settings = trim( substr( $line, 18 ) );        
        if ( 'none' != $settings ) {
          $allsettings = explode( ';', $settings );
          foreach( $allsettings as $setting ) {
            $option = explode( '=', $setting );            
            if ( isset( $option[1] ) ) $items['settings'][$option[0]] = $option[1];
          }
        }
      }  
    }        
    unset( $contents );
    return $items;
  }
  
  /**
   * LazyestThemes::_set_td_style()
   * Sets class for the theme browser table cell
   * 
   * @param int $c #column
   * @param int $r #row
   * @param int $rows # of rows
   * @return
   */
  function _set_td_style( $c, $r, $rows ) {
    $class = '';
    switch ( $c ) {
      case 0: $class .= ' left';
      break;
      case 2: $class .= ' right';
      break; 
    }  
    if ( $r == 0 ) $class .= ' top';
    if ( $r == $rows-1 ) $class .= ' bottom';
    return $class;    
  }  
    
  function option_strings() {
    return array(
      'pictwidth' => esc_html__( 'Maximum Slides Width' ),
      'pictheight' => esc_html__( 'Maximum Slides Height' ),
      'thumbwidth' => esc_html__( 'Maximum Thumbnail Width' ),
      'thumbheight' => esc_html__( 'Maximum Thumbnail Height' ),
      'thumbs_page' => esc_html__( 'Thumbnails per Page' ),
      'folders_page' => esc_html__( 'Folders per Page' ),
      'folders_columns'=> esc_html__( 'Folder Columns' ),
      'thumbs_columns'=> esc_html__( 'Thumbnail Columns' ),
      'folder_image' => esc_html__( 'Folder Icons' ),
      'use_cropping' => esc_html__( 'Cropping' ),
      'enable_captions' => esc_html__( 'Use image captions instead of file names ' ),
      'use_folder_captions' => esc_html__( 'Use folder captions instead of folder names ' ),
      'enable_exif' => esc_html__( 'Exif Data' ),
      'captions_length' => esc_html__( 'Length of Captions in Thumbnail View' ),      
      'on_thumb_click' => esc_html__( 'Thumbnail On Click' ),
      'on_slide_click' => esc_html__( 'Slide On Click' ),
      'count_subfolders' => esc_html__( 'Count Images' ),
      'random_subfolder' => esc_html__( 'Folder Icons' ),
      'listed_as' => esc_html__( 'The images in the Gallery should be listed as' ),
      'show_credits' => esc_html__( 'Credits' ),
      'theme_javascript' => esc_html__( 'Load Javascript' ),
      'table_layout' => esc_html__( 'Gallery Layout' )
    );
  }
  
  /**
   * LazyestThemes::dont_change()
   * Settings that may not be changed by a theme
   * @return array
   */
  function dont_change() {
    return array( 'new_install', 
                  'gallery_folder', 
                  'gallery_prev', 
                  'gallery_id', 
                  'excluded_folders',
                  'sort_alphabetically',
                  'use_slides_popup',
                  'disable_full_size',
                  'enable_cache',
                  'enable_slides_cache',
                  'allow_comments', 
                  'resample_quality', 
                  'fileupload_allowedtypes', 
                  'manager_roles',                        
                  'enable_slide_show',
                  'enable_mwp_support', 
                  'wizard_user',
                  'wizard_password',
                  'image_indexing',
                  'use_permalinks',
                  'gallery_secure', 
                  'style.css',
                  'flash_upload',
                  'append_search',
                  'slide_show_duration',
                  'async_cache' );
  }
  
  
  /**
   * LazyestThemes::activate_theme()
   * Activates a theme in the theme browser and applies changes to settings 
   * @param mixed $new_theme
   * @return void
   */
  function activate_theme( $new_theme ) {
    global $lg_gallery;
    if ( ( 'no_style' == $new_theme ) ) {      
      $lg_gallery->update_option( 'style_css', $new_theme );
      $success = true;             
      $message = esc_html__( "Lazyest Gallery will use your Blog's Theme", 'lazyest-gallery' );
    } else {
      $changed = false;
      $dont =  $this->dont_change();
      $new_file = trailingslashit( $lg_gallery->themes_dir() ) . $new_theme;
      $success = file_exists( $new_file );
      $defaults = $lg_gallery->defaults();
      if ( $success ) {        
        $lg_gallery->update_option( 'style_css', $new_theme );
        $items = $this->read_theme_header( $new_file );
        if ( 0 < count( $items) ) {
          if ( isset( $items['settings'] ) ) {
              if ( 0 < count( $items['settings'] ) ) {
                if ( ! isset( $items['settings']['theme_javascript'] ) ) {
                  $lg_gallery->change_option( 'theme_javascript', '' );
                }
                foreach ( $items['settings'] as $option=>$setting ) {
                  if ( array_key_exists( $option, $defaults ) ) { // is it a valid lazyest-gallery option?
                    if ( ! in_array( $option, $dont ) ) { // don't change the gallery main options                              
                      $lg_gallery->change_option( $option, strtoupper( $setting ) );  
                    }
                  }
                }
              $lg_gallery->store_options();
              $changed = true;
            }
          } 
        }
      }
      $message = ( $success ) ? esc_html__( 'New gallery theme activated.', 'lazyest-gallery' ) : esc_html__( 'Lazyest Gallery could not activate this theme.', 'lazyest-gallery' );
      
      if ( $changed )                           /* translators: 1: <a href="">, 2: </a> */
				$message .= ' '  . sprintf( esc_html__( 'This theme has changed your settings, please visit the %1sLazyest Gallery Settings%2s screen to verify them.', 'lazyest-gallery' ),
					sprintf( '<a href="%s">', admin_url( 'options-general.php?page=lazyest-gallery' ) ),
					'</a>'
			 	);  
      }    
    $lg_gallery->message = $message;
    $lg_gallery->success = $success; 
  }
  
  /**
   * LazyestThemes::themes_page()
   * Display the Manage Themes page for Lazyest Gallery
   * 
   * @return void
   */
  function themes_page() {
    global $lg_gallery; 
    if ( ! current_user_can( 'edit_themes' ) ) {      
      wp_die( esc_html__( 'You do not have permission to change themes.', 'lazyest-gallery' ) );
    } 
    if ( isset( $_GET['activate'] ) ) {      
      $nonce=$_REQUEST['_wpnonce'];
      if ( ! wp_verify_nonce( $nonce, 'lg_activate_theme-nonce') ) wp_die('Security check');
      $this->activate_theme( $_GET['activate'] );
    } 				    
    if ( isset( $_GET['edit_theme'] ) ) {
      $this->edit_theme( $_GET['edit_theme'] );
      return;
    }      
    $themes_dir = $lg_gallery->themes_dir();
    $themes_url = $lg_gallery->themes_url();
    $style = $lg_gallery->get_option( 'style_css' ); // get current theme stylesheet
    $style_file = trailingslashit( $themes_dir ) . $style;
    if ( ! file_exists( $style_file ) && ( 'no_style' != $style ) ) {
      $lg_gallery->message = __( "Lazyest Gallery cannot find your theme. Your blog's theme will be used instead", 'lazyest-gallery');
      $lg_gallery->success = false;
      $style = 'no_style';
      $lg_gallery->update_option( 'style_css', $style );
    }
    if ( ( $style != 'no_style' ) && ( $style != '' ) ) { // get stylesheet if blog theme stylesheet is not used for LG
      $pict =  str_replace( '.css', '.jpg', $style ); 
      $style_prev = trailingslashit( $themes_url ) . $pict;      
      $edit_url = wp_nonce_url( "admin.php?page=lazyest-themesmanager&edit_theme=$style", 'lg_edit_theme_nonce');
    } else {
      $style_dir = trailingslashit( get_template_directory() );
      $style_file = $style_dir . 'style.css';
      $exts = array( '.gif', '.jpg', '.jpeg', '.png' );
      $stub = $style_dir . 'screenshot';
      foreach( $exts as $ext ) {  
        if ( file_exists( $stub . $ext ) ) {
          $the_ext = $ext;
        }
        if ( file_exists( $stub . strtoupper( $ext ) ) )
          $the_ext = strtoupper( $ext );
      }
      $style_prev = trailingslashit( get_bloginfo( 'template_url' ) ) . 'screenshot' . $the_ext;
      $edit_url = "theme-editor.php";
    }
    $items = $this->read_theme_header( $style_file );    
    $available = $this->get_available_themes( $themes_dir );
    $title = ( $style != 'no_style' ) ? esc_html__( 'Current Lazyest Gallery Theme', 'lazyest-gallery' ) : esc_html__( 'You use your Blog Theme', 'lazyest-gallery' );
    $c = $a = $r = 0;
    $rows = ceil( count( $available ) /3 );
    
    ?>    
  	<div class="wrap">
      <?php screen_icon( 'themez' ); ?>
      <h2><?php echo esc_html__( 'Manage Lazyest Gallery Themes', 'lazyest-gallery' ); ?></h2>
      <?php $lg_gallery->options_message(); ?>
      <h3><?php echo $title; ?></h3>
      <div id="current-theme">
      <img alt="<?php esc_html_e( 'Current theme preview', 'lazyest-gallery' ); ?>" src="<?php echo $style_prev ?>" />
      <h4><?php echo $items['name'] . __(' by ', 'lazyest-gallery' ); ?><a href="<?php echo $items['author_uri']; ?>" title="<?php esc_html_e( 'Visit author homepage', 'lazyest-gallery'); ?>"><?php echo $items['author']; ?></a></h4>
        <p class="theme-description"><?php echo $items['description']; ?></p>
        <span class="action-links"><a href="<?php echo $edit_url; ?>" class="activatelink" title="<?php esc_attr_e('Edit', 'lazyest-gallery' ) . ' ' . $items['name']; ?>"><?php esc_html_e('Edit', 'lazyest-gallery'); ?></a></span>
      </div>      
      <div class="tablenav">
        <br class="clear" />
      </div>
      <h3><?php esc_html_e( 'Available Themes', 'lazyest-gallery' ); ?></h3>
      <table id="availablethemes" cellspacing="0" cellpadding="0">
        <tbody class="list:themes" id="the-list">
          <tr> 
      <?php 
      while ( $a < count( $available ) ) { 
        $astyle = trailingslashit( $themes_dir ) . $available[$a];
        $aprev = trailingslashit( $themes_url ) . str_replace( '.css', '.jpg', $available[$a] );
        $items = $this->read_theme_header( $astyle );
        $activate_url = wp_nonce_url( "admin.php?page=lazyest-themesmanager&activate=$available[$a]", 'lg_activate_theme-nonce' );
        $edit_url = wp_nonce_url( "admin.php?page=lazyest-themesmanager&edit_theme=$available[$a]", 'lg_edit_theme_nonce');
      ?>
            <td class="available-theme<?php echo $this->_set_td_style( $c, $r, $rows ); ?>">
              <a href="#" class="screenshot"><img src="<?php echo $aprev; ?>" alt="" /></a>
              <h3><?php echo $items['name'] . ' ' . esc_html__( 'by', 'lazyest-gallery' ) . ' ' . $items['author']; ?></h3> 
              <p class="description"><?php echo $items['description'] ?></p> 
              <span class="action-links">
                <a href="<?php echo $activate_url; ?>" class="activatelink" title="<?php echo esc_attr_e( 'Activate', 'lazyest-gallery' ) . ' ' . $items['name']; ?>"><?php esc_html_e('Activate', 'lazyest-gallery' ); ?></a>
                |
                <a href="<?php echo $edit_url; ?>" class="activatelink" title="<?php esc_attr_e( 'Edit', 'lazyest-gallery' ) . ' ' . $items['name']; ?>"><?php esc_html_e( 'Edit', 'lazyest-gallery'); ?></a>
              </span>
          <?php 
          $defaults = $lg_gallery->defaults();
          $dont = $this->dont_change();
          $strings = $this->option_strings();
          $settings = '';
          if ( isset( $items['settings'] ) ) {
            if ( 0 < count( $items['settings'] ) ) {
              echo '<p>' . esc_html__( 'This theme will change the following settings:', 'lazyest-gallery' ) . '</p>';
              echo '<p class="description">';                
              foreach ( $items['settings'] as $option=>$setting ) {
                if ( array_key_exists( $option, $defaults ) ) { // is it a valid lazyest-gallery option?
                  if ( ! in_array( $option, $dont ) ) { // don't change the gallery main options 
                    $settings .= "&apos;$strings[$option]&apos;, ";
                  }
                }
              }
              $settings = rtrim( $settings, ', ' );
              echo "$settings</p>";
            } 
          }
          ?>   
            </td>  
      <?php
        ++$a;    
        ++$c;    
        if ( $c == 3 ) {      
          ++$r;
          $c=0;
      ?>
        </tr>
        <tr>
      <?php
        } 
      }               
      if ( ($c < 3) && ( $c != 0 ) ) {
        while ( $c < 3 ) {
          ?> 
            <td class="available-theme<?php echo $this->_set_td_style( $c, $r, $rows ); ?>"></td>
          <?php
          ++$c;
        }
      ?> 
          </tr>
      <?php 
      } 
      ?>        
        </tbody>
      </table>
  <?php
    $activate_url = wp_nonce_url( "admin.php?page=lazyest-themesmanager&activate=no_style", 'lg_activate_theme-nonce' );
    ?>       
      <strong><?php esc_html_e( 'None of the above', 'lazyest-gallery' ) . ' '; ?><a class="button-secondary" href="<?php echo $activate_url; ?>">Use my Blog's theme</a></strong>
      <p><?php esc_html_e( 'Use this when you have put your gallery styles into your theme stylesheet', 'lazyest-gallery' ); ?></p>
	</div>
  <?php
}

/**
 * LazyestThemes::edit_theme()
 * Display the theme editor or redirection link to WordPress theme editor
 * 
 * @return
 */
function edit_theme( $stylesheet ) { 
  global $lg_gallery;
  if ( ! current_user_can( 'edit_themes' ) ) {      
    wp_die( esc_html__( 'You do not have permission to edit themes.', 'lazyest-gallery' ) );
  }
  $nonce=$_REQUEST['_wpnonce'];
  if ( ! wp_verify_nonce($nonce, 'lg_edit_theme_nonce') ) wp_die('Security check');
   ?>
    <div class="wrap">
    <div id="icon-themes" class="icon32"></div>
  <?php
  $style_dir = $lg_gallery->themes_dir(); 
  if ( ( 'no_style' == $stylesheet ) ) {
    ?>
    <h2><?php esc_html_e( 'Edit Theme', 'lazyest-gallery' ); ?></h2> 
    <?php $lg_gallery->options_message(); ?>             
    <?php
    /* translators: 1: <a href="">, 2: </a> */
    $message = sprintf( esc_html__( "You have selected to use your blog's theme for Lazyest Gallery, please use the WordPress %1sTheme Editor%2s", 'lazyest-gallery' ),
			sprintf( '<a href="%s">', admin_url( 'wp-admin/theme-editor.php' ) ),
			'</a>' );      
    $lg_gallery->message = $message;
    $lg_gallery->success = false;
    ?>
    </div> <!-- wrap -->
    <?php
    return;
  }      
  $success = false;
  $updated = false;    
  $style_file = trailingslashit( $lg_gallery->themes_dir() ). $stylesheet;
  $handle = @fopen($style_file, 'r');
  if ( $handle ) {
  	$content = @fread( $handle, filesize( $style_file ) );
    @fclose( $handle );
    if ( $content ) {
      $content = htmlspecialchars($content);
      $success = true;
      $updated = false;
      if ( isset( $_POST['action'] ) ) {
        $success = $updated = false;
  		  $newcontent = stripslashes( $_POST['newcontent'] );
  		  if ( is_writeable( $style_file ) ) {
  			 $handle = @fopen( $style_file, 'w+' );
          if ( $handle ) {
    			 fwrite( $handle, $newcontent );
    			 fclose( $handle );
            $success = $updated = true;
          }
  		  }
  		  $content = htmlspecialchars( $newcontent );
        $message = ( $success ) ? esc_html__( 'File edited successfully', 'lazyest-gallery' ) : esc_html__( 'Error saving file', 'lazyest-gallery' );
      }
    }
  } else {
    $success = false;
    $message = esc_html__( 'Lazyest Gallery cannot open Stylesheet' );
  }
  $title = ( is_writeable( $style_file ) ) ? esc_html__( 'Edit Lazyest Gallery Stylesheet', 'lazyest-gallery' ) : esc_html__( 'Browse Lazyest Gallery Stylesheet', 'lazyest-gallery' ); 		
	if ( $updated || ! $success ) {
	 $lg_gallery->message = $message;
   $lg_gallery->success = $success;  
	}
    ?>
    <h2><?php echo $title; ?></h2>
		<?php $lg_gallery->options_message();  ?> 		
		<div class="tablenav">
			<div class="alignleft">
				<p><big><strong><?php echo esc_html__('Stylesheet ' ); ?></strong>(<?php echo $lg_gallery->get_option( 'style_css' );?>)</big></p>
			</div>
			<br class="clear" />
		</div>
		<br class="clear" />  		
		<div class="templateside">
		<form name="template" id="template" method="post" action="">
				<textarea cols="80" rows="25" name="newcontent" id="newcontent" class="codepress css"><?php echo $content ?></textarea>
				<input type="hidden" name="action" value="update" />
		<?php if (is_writeable( $style_file ) ) { ?>
				<p class="submit">
			    <input class="button-primary" type="submit" name="submit" value="<?php esc_html_e( 'Update File', 'lazyest-gallery' ); ?>" />
				</p>
			<?php } else { ?>
				<p><em><?php esc_html_e( 'If this file were writable you could edit it.', 'lazyest-gallery' ); ?></em></p>
			<?php } ?>
		</form>
		</div>
	</div>
    <?php 
  }
  
} // LazyestThemes
?>
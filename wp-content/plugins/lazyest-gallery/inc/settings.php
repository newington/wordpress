<?php  
/** 
 * This file contains the Lazyest Gallery Settings screen
 * 
 * @since 1.1.0
 */ 
 
class LazyestSettings {
  
  var $new_install;
  var $installstyle;
  var $pages;
  var $pagecount;
  var $other_page;

  function __construct() {
    global $lg_gallery;
    if ( ! current_user_can( 'manage_options' ) ) {      
      wp_die( esc_html__( 'You do not have permission to change these settings.', 'lazyest-gallery' ) );
    }
   	$this->pages = get_pages( 'post_type=page&post_status=publish&hierarchical=0');
   	$this->pagecount = count( $this->pages );
		$this->other_page = false;  	
  }
  
  function do_actions() {
    global $lg_gallery;
    if ( ! isset( $_GET['updated'] ) && ! isset( $_GET['settings-updated'] ) ) { // only take other actions when update button has not been clicked       
      if ( isset( $_GET['xp_wiz'] ) ) {
          $lg_gallery->wizard_form(); 
					$this->other_page = true; 
      }	else {
        if ( isset( $_GET['create_folder'] ) ) {
          $lg_gallery->create_gallery_folder( $_GET['create_folder'] );  
        } 
        if ( isset( $_GET['insert_shortcode'] ) ) {
          $lg_gallery->insert_shortcode( $_GET['insert_shortcode'] );  
        }
        if ( isset( $_GET['reset_options'] ) ) {
          $defaults = $lg_gallery->defaults();
          $lg_gallery->options = &$defaults;
          $lg_gallery->store_options();
          wp_redirect( admin_url( 'admin.php?page=lazyest-gallery' ) );
          exit;
        }
        if ( isset( $_GET['clear_cache'] ) ) {
          $lg_gallery->admin_clear_cache();
        } else {
      		do_action( 'lazyest-gallery-settings_actions' );
        	if ( has_action( 'lazyest-gallery-settings_pages' ) ) {        		
      			do_action( 'lazyest-gallery-settings_pages', $this );
					}
        }   
      }   
    }  
    $dangerous = get_transient( 'lg_dangerous_path' ); 
    if ( false !== $dangerous ) {    
      $lg_gallery->message = $dangerous ? __( 'You cannot set your Gallery Folder in a WordPress directory. Folder set to default' ) : '';  
      $lg_gallery->success = false;
      delete_transient( 'lg_dangerous_path' );      
    }  
  }
  
  function display() {
    global $lg_gallery, $wp_version;
    $this->do_actions();   
    if ( $this->other_page )
			return; 	 	          	
   	$this->new_install = ( $lg_gallery->get_option( 'new_install' ) == 'TRUE' ) || 
			! $lg_gallery->valid() || 
				( 0 == $this->pagecount ) || 
					! file_exists( $lg_gallery->get_absolute_path( ABSPATH . $lg_gallery->get_option('gallery_folder') ) );			   	
		$this->installstyle = $this->new_install ? 'new_install' : '';    
    ?>
    <div class="wrap">
      <?php screen_icon( 'folders' ); ?>
      <h2><?php esc_html_e( 'Lazyest Gallery Settings', 'lazyest-gallery' ); ?></h2>
      <?php $lg_gallery->options_message(); ?>  
      <div id="ajax-div"></div>    
      <?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?> 
      <div id="poststuff" class="metabox-holder has-right-sidebar">
      <?php else : ?> 
      <div id="poststuff" class="metabox-holder">
      <?php endif; ?>
        <form method="post" action="options.php">      
  		    <?php settings_fields( 'lazyest-gallery' ); ?>
          <input type="hidden" id="lg_settings" name="lg_settings" value="<?php echo wp_create_nonce( 'settings' ) ?>" />
          <?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?> 
						<?php $this->sidebar() ?>         		
          	<div id="post-body">
         	<?php else : ?>
						<div id="post-body" class="metabox-holder columns-2">		              	
         		<?php $this->sidebar() ?>
         	<?php endif; ?>
					 <div id="post-body-content">
              <?php $this->main_options(); ?>
	            <?php $this->thumbnail_options(); ?>
	            <?php $this->slide_options(); ?>
	            <?php $this->caption_options(); ?>
	            <?php $this->upload_options(); ?>
	            <?php $this->advanced_options(); ?>
	            <?php if ( ! $this->new_install ) : ?>
            		<?php do_action( 'lazyest_settings_main' );?>
	            <?php endif; ?>
              <?php if ( 0 < $this->pagecount ) : ?>
              <div class="submit">
                <input class="button-primary" type="submit" name="lazyest-gallery[update_options]" value="<?php	esc_html_e( 'Save Changes', 'lazyest-gallery' );	?>" />
              </div>      
              <?php endif ?>     
            </div>
          </div>        
         </form> 
      </div>
    </div>
    <?php
  }
  
  function sidebar() {
    global $lg_gallery, $wp_version;
    ?>
    <?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?> 
    <div id="side-info-column" class="inner-sidebar <?php echo $this->installstyle; ?>">
    <?php else : ?>
    <div id="postbox-container-1" class="postbox-container <?php echo $this->installstyle; ?>">
    <?php endif; ?>
      <div id="side-sortables" class="meta-box-sortables ui-sortable">
        <?php $this->aboutbox(); ?>
        <?php $this->utilities(); ?>        
        <?php do_action( 'lazyest_settings_sidebar' );?>
      </div>
    </div>
    <?php
  }
  
  /**
   * LazyestSettings::main_options()
   * 
   * @return void
   */
  function main_options() {  
    global $lg_gallery;       
    $gallery_folder = str_replace( array('/', '\\'), DIRECTORY_SEPARATOR, $lg_gallery->get_option( 'gallery_folder' ) );   
    $createfolder_url = add_query_arg( 'create_folder', $gallery_folder, admin_url( 'options-general.php?page=lazyest-gallery' ) );
    $createpage_url = admin_url( 'page-new.php' );
    $poptions = array();
    if ( 0 < $this->pagecount )  {
      foreach( $this->pages as $apage ) {
        $selected = ( $apage->ID == $lg_gallery->get_option( 'gallery_id' ) ) ? 'selected="selected"' : '';
        $poptions[] = sprintf( '<option value="%s" %s>%s</option>', $apage->ID, $selected, esc_attr( $apage->post_title ) );        
      }
    } 
    if ( $this->new_install ) { 
      $poptions[] = sprintf( '<option value="-1">%s</option>', esc_attr__( 'Create a New Page', 'lazyest-gallery' ) );
    }
    ?>
    <script type="text/javascript">
    /* <! [CDATA[ */
    pageURLs=new Array();                  
    pageCodes=new Array();
    <?php
    if ( 0 < $this->pagecount )  {      
      $script = '';
      foreach( $this->pages as $apage ) { 
      	$content = $apage->post_content;
				if(	! $content )
					continue;
        $is_gallery = ( strpos( $content, '[lg_gallery' ) );        
        $str = ( false !== $is_gallery ) ? 'true' : 'false';
        $script .= sprintf( "pageURLs['%s']='%s';\n",
          $apage->ID,
          trailingslashit( get_page_link( $apage->ID ) ) 
        );
        $script .= sprintf( "pageCodes['%s']=%s;\n",
           $apage->ID,
           $str
        );
      }
      echo $script;
    }
    ?>
    /* //]]> */
    </script>
    <?php                              
    $page_id = $lg_gallery->get_option( 'gallery_id' );
    if (  '' != $page_id ) {                      
      $apage = get_page( $page_id );
      if ( ! $apage || ( 'publish' != $apage->post_status ) ) { // page should be published
        $page_id = '';
      }
    }                        
    if (  '' == $page_id ) { // page id is not set, just pick first in line
      $apage = $this->pages[0];
      $page_id = $apage->ID;
      $lg_gallery->change_option( 'gallery_prev', trailingslashit( get_page_link( $apage->ID ) ) );                        
    }                                                         
    $apage = get_page( $page_id ); 
    $is_gallery = strpos( $apage->post_content, '[lg_gallery' ); 
    $astyle = ( $is_gallery === false ) ? 'display:block' : 'display:none';    
    ?>    
    <div id="lg_main_options" class="postbox">
      <h3 class="hndle"><span><?php esc_html_e( 'Main Gallery Options', 'lazyest-gallery'); ?></span></h3>
        <?php if ( $this->new_install ) : ?>
      <div class="inside"> 
        <div class="update below-h2">
          <h1><?php esc_html_e( 'Welcome to Lazyest Gallery', 'lazyest-gallery' ); ?></h1>
          <p><?php esc_html_e( 'Before you can enjoy all the features of Lazyest Gallery, please enter the folder to store your images, and your blog page where you show your Gallery', 'lazyest-gallery' ); ?></p>
        </div>           
      </div>      
      <?php endif; ?>                
      <table id="lg_main_options_table" class="widefat">
        <tbody>
          <tr>
            <th scope="row"><label for="gallery_folder"><?php esc_html_e( 'Your Gallery Folder', 'lazyest-gallery' ); ?></label></th>
            <td>
              <input name="lazyest-gallery[gallery_folder]" id="gallery_folder" value="<?php echo $gallery_folder ?>" size="60" class="code" type="text" /> <br />
							<p><?php esc_html_e( 'Relative to the WordPress installation folder', 'lazyest-gallery') ?></p>												            
              <?php
								$gallery_path = $lg_gallery->get_absolute_path( ABSPATH . $gallery_folder );
								if ( ! file_exists( $gallery_path ) ) : 
							?>
              <div class="error below-h2">
              	<p><strong><?php esc_html_e( 'WARNING', 'lazyest-gallery' ); ?></strong> <?php esc_html_e( 'The specified gallery folder does not exist', 'lazyest-gallery' ); ?>:
                <code><?php $gallery_folder; ?></code></p>
                <p><a href="<?php echo $createfolder_url; ?>"><?php esc_html_e( 'Let Lazyest Gallery create this folder for me.', 'lazyest-gallery' ); ?></a></p>
              </div>
              <?php endif; ?>
              <?php if ( $this->new_install ) : ?>
              	<input type="hidden" name="lazyest-gallery[new_install]" value="TRUE" />
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="gallery_id"><?php esc_html_e( 'Your Gallery Page', 'lazyest-gallery' ); ?></label></th>            
            <td>                  
              <?php if ( 0 == $this->pagecount ) { ?>                    
              <a href="<?php echo $createpage_url ?>"><?php esc_html_e( 'Your blog has no pages yet, please create a page for your gallery', 'lazyest-gallery' ); ?></a>  
              <?php } else { ?>
              <select class="postform" name="lazyest-gallery[gallery_id]" id="gallery_id" onchange="lg_page_change()" >
                <?php echo implode( $poptions ); ?>                    
              </select><br />                
              <p><?php esc_html_e( 'The exact address where your main gallery is browsable:', 'lazyest-gallery' ); ?></p>
              <p id="gallery_prev_p"><?php echo $lg_gallery->get_option( 'gallery_prev' ); ?></p>
              <div id="lg_insertcode" class="error below-h2" style="<?php echo $astyle; ?>">
                <p><strong style='color:#ff0000;'><?php esc_html_e( 'WARNING', 'lazyest-gallery' ); ?></strong>: <?php sprintf( esc_html__( 'The Lazyest Gallery shortcode %s cannot be found on this page.', 'lazyest-gallery'), '<code>[lg_gallery]</code>' );?></p>
                <p><a id="a_insert_shortcode" href="admin.php?page=lazyest-gallery&amp;insert_shortcode=<?php echo $apage->ID ?>">Let Lazyest Gallery insert the shortcode for me</a></p>
              </div>    
              <input type="hidden" name="lazyest-gallery[gallery_prev]" id="gallery_prev" value="<?php echo $lg_gallery->get_option( 'gallery_prev' ); ?>" />
              <input type="hidden" name="lazyest-gallery[new_install]" id="new_install" value="FALSE" />
              <?php } ?> 
              <br class="clear" />
            </td>
          </tr>
        </tbody>
      </table>   
    </div>
    <?php    
    unset( $apage );
    unset( $this->pages );
  } // LazyestSettings::main_options()
  
  
  /**
   * LazyestSettings::thumbnail_options()
   * 
   * @return void
   */
  function thumbnail_options() {
    global $lg_gallery;  
    ?>
    <div id="lg_thumbnail_options" class="postbox <?php echo $this->installstyle; ?>" >
    <h3 class="hndle"><span><?php esc_html_e( 'Thumbnail View Options' , 'lazyest-gallery' ); ?></span></h3>
    <table id="lg_thumbnail_options_table" class="widefat">
      <tbody>
        <tr>
          <th scope="row"><label for="thumbwidth"><?php esc_html_e( 'Maximum Thumbnail Width' , 'lazyest-gallery' ); ?></label></th>
          <td><input name="lazyest-gallery[thumbwidth]" id="thumbwidth" value="<?php echo $lg_gallery->get_option( 'thumbwidth' ); ?>" size="10" class="code" type="text" /> pixels</td>
        </tr>
        <tr>
          <th scope="row"><label for="thumbheight"><?php esc_html_e( 'Maximum Thumbnail Height', 'lazyest-gallery' ); ?></label></th>
          <td><input name="lazyest-gallery[thumbheight]" id="thumbheight" value="<?php echo $lg_gallery->get_option( 'thumbheight' ); ?>" size="10" class="code" type="text" /> pixels</td>
        </tr>								
        <tr>
          <th scope="row"><label for="thumbspage"><?php esc_html_e( 'Thumbnails per Page', 'lazyest-gallery' ); ?></label></th>
          <td><input name="lazyest-gallery[thumbs_page]" id="thumbs_page" value="<?php echo $lg_gallery->get_option( 'thumbs_page' ); ?>" size="5" class="code" type="text" /><br />
          <p><?php esc_html_e( 'Set to 0 to disable pagination.', 'lazyest-gallery' ); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="folders_page"><?php esc_html_e( 'Folders per Page', 'lazyest-gallery' ); ?></label></th>
          <td><input name="lazyest-gallery[folders_page]" id="folders_page" value="<?php echo $lg_gallery->get_option( 'folders_page' ); ?>" size="5" class="code" type="text" /><br />
          <p><?php esc_html_e( 'Set to 0 to disable pagination.', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="count_subfolders"><?php esc_html_e( 'Count Images', 'lazyest-gallery'); ?></label></th>
          <td>
            <select id="count_subfolders" name="lazyest-gallery[count_subfolders]">
              <option value="none" <?php selected( 'none', $lg_gallery->get_option( 'count_subfolders' ) ); ?>><?php esc_attr_e( 'Show number of images in folder only', 'lazyest-gallery' ) ?></option>                          
              <option value="include" <?php selected( 'include', $lg_gallery->get_option( 'count_subfolders' ) ); ?>><?php esc_attr_e( 'Show number of images in folder including subfolders', 'lazyest-gallery' ); ?></option>                           
              <option value="separate" <?php selected( 'separate', $lg_gallery->get_option( 'count_subfolders' ) ); ?>><?php esc_attr_e( 'Show number of images in folder and subfolders separately', 'lazyest-gallery' ); ?></option>
              <option value="nothing" <?php selected( 'nothing', $lg_gallery->get_option( 'count_subfolders' ) ); ?>><?php esc_attr_e("Don't show number of images in folder", 'lazyest-gallery' ); ?></option>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="sort_alphabetically"><?php esc_html_e( 'Sort Images by', 'lazyest-gallery' ) ?></label></th>
            <td> 
              <select id="sort_alphabetically" name="lazyest-gallery[sort_alphabetically]">
              <option value="TRUE" <?php selected( 'TRUE' , $lg_gallery->get_option( 'sort_alphabetically' ) ); ?>><?php esc_attr_e('Name, ascending ( A &rarr; Z )', 'lazyest-gallery' ) ?></option>
              <option value="DTRUE" <?php selected( 'DTRUE' , $lg_gallery->get_option( 'sort_alphabetically' ) ); ?>><?php esc_attr_e('Name, descending ( Z &rarr; A )', 'lazyest-gallery' ) ?></option>
              <option value="CAPTION" <?php selected( 'CAPTION' , $lg_gallery->get_option( 'sort_alphabetically' ) ); ?>><?php esc_attr_e('Caption, ascending ( A &rarr; Z )', 'lazyest-gallery' ) ?></option>
              <option value="DCAPTION" <?php selected( 'DCAPTION' , $lg_gallery->get_option( 'sort_alphabetically' ) ); ?>><?php esc_attr_e('Caption, descending ( Z &rarr; A )', 'lazyest-gallery' ) ?></option>                        
              <option value="DFALSE" <?php selected( 'DFALSE' , $lg_gallery->get_option( 'sort_alphabetically' ) ); ?>><?php esc_attr_e('Date, newest first', 'lazyest-gallery' ) ?></option>                                  
              <option value="FALSE" <?php selected( 'FALSE' , $lg_gallery->get_option( 'sort_alphabetically' ) ); ?>><?php esc_attr_e('Date, oldest first', 'lazyest-gallery' ) ?></option>                     
              <option value="MANUAL" <?php selected( 'MANUAL' , $lg_gallery->get_option( 'sort_alphabetically' ) ); ?>><?php esc_attr_e('Manually', 'lazyest-gallery' ) ?></option>                                                  
            </select>                     
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="sort_folders"><?php esc_html_e( 'Sort Folders by', 'lazyest-gallery' ) ?></label></th>
            <td> 
              <select id="sort_folders" name="lazyest-gallery[sort_folders]">
              <option value="TRUE" <?php selected( 'TRUE' , $lg_gallery->get_option( 'sort_folders' ) ); ?>><?php esc_attr_e('Name, ascending ( A &rarr; Z )', 'lazyest-gallery' ) ?></option>
              <option value="DTRUE" <?php selected( 'DTRUE' , $lg_gallery->get_option( 'sort_folders' ) ); ?>><?php esc_attr_e('Name, descending ( Z &rarr; A )', 'lazyest-gallery' ) ?></option>
              <option value="CAPTION" <?php selected( 'CAPTION' , $lg_gallery->get_option( 'sort_folders' ) ); ?>><?php esc_attr_e('Caption, ascending ( A &rarr; Z )', 'lazyest-gallery' ) ?></option>
              <option value="DCAPTION" <?php selected( 'DCAPTION' , $lg_gallery->get_option( 'sort_folders' ) ); ?>><?php esc_attr_e('Caption, descending ( Z &rarr; A )', 'lazyest-gallery' ) ?></option>                        
              <option value="DFALSE" <?php selected( 'DFALSE' , $lg_gallery->get_option( 'sort_folders' ) ); ?>><?php esc_attr_e('Date, newest first', 'lazyest-gallery' ) ?></option>                                  
              <option value="FALSE" <?php selected( 'FALSE' , $lg_gallery->get_option( 'sort_folders' ) ); ?>><?php esc_attr_e('Date, oldest first', 'lazyest-gallery' ) ?></option>                     
              <option value="MANUAL" <?php selected( 'MANUAL' , $lg_gallery->get_option( 'sort_folders' ) ); ?>><?php esc_attr_e('Manually', 'lazyest-gallery' ) ?></option>                                                  
            </select>                     
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="folders_columns"><?php esc_html_e( 'Folder Columns', 'lazyest-gallery' ) ?></label></th>
          <td><input name="lazyest-gallery[folders_columns]" id="folders_columns" value="<?php echo $lg_gallery->get_option( 'folders_columns' ); ?>" size="5" class="code" type="text" />
          	<?php if( 'TRUE' != $lg_gallery->get_option('table_layout') ) echo '<p>' . esc_html__( 'Set to 0 for a maximum fill per row', 'lazyest-gallery' ) . '</p>'; ?>
					</td>
        </tr>
        <tr>
          <th scope="row"><label for="thumbs_columns"><?php esc_html_e( 'Thumbnail Columns', 'lazyest-gallery' ) ?></label></th>
          <td><input name="lazyest-gallery[thumbs_columns]" id="thumbs_columns" value="<?php echo $lg_gallery->get_option( 'thumbs_columns' ); ?>" size="5" class="code" type="text" />
          <?php if( 'TRUE' != $lg_gallery->get_option('table_layout') ) echo '<p>' . esc_html__( 'Set to 0 for a maximum fill per row', 'lazyest-gallery' ) . '</p>'; ?>
					</td>
        </tr>			
        <tr>
          <th scope="row"><label for="folder_image"><?php esc_html_e( 'Folder Icons', 'lazyest-gallery' ) ?></label></th>
          <td>
            <select id="folder_image" name="lazyest-gallery[folder_image]" onchange="lg_random_change()">
              <option value="icon" <?php selected( 'icon', $lg_gallery->get_option( 'folder_image') ); ?>><?php esc_attr_e('Folder icon', 'lazyest-gallery' ) ?></option>
              <option value="random_image" <?php selected( 'random_image', $lg_gallery->get_option( 'folder_image') )?>><?php esc_attr_e( 'Random image from folder', 'lazyest-gallery' ) ?></option>                      
              <option value="none" <?php selected( 'none', $lg_gallery->get_option( 'folder_image') ); ?>><?php esc_attr_e( 'None', 'lazyest-gallery' ) ?></option>
            </select> 
            <div id="random_subfolder_div"<?php if ( 'random_image' != $lg_gallery->get_option( 'folder_image' ) ) echo 'style="display:none;"' ?>>
              <label><input name="lazyest-gallery[random_subfolder]" type="checkbox" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option( 'random_subfolder' ) ); ?> /> <?php esc_html_e( 'Include images from sub folders', 'lazyest-gallery' ); ?> </label>
            </div>
           </td>                    
        </tr>                    
        <tr>
          <th scope="row"><?php esc_html_e( 'Caching', 'lazyest-gallery' ) ?></th>
          <td>
            <label><input type="checkbox" name="lazyest-gallery[enable_cache]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option( 'enable_cache' ) ); ?> /><?php esc_html_e( ' Enable thumbnail caching', 'lazyest-gallery' ); ?></label><br />            
            <br /> 
            <label><input type="checkbox" name="lazyest-gallery[async_cache]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option( 'async_cache' ) ); ?> /><?php ?><?php esc_html_e( ' Create cached thumbnails after page has loaded in the browser', 'lazyest-gallery' ); ?></label>
                                     
            <br /><label><?php esc_html_e( 'Store cached thumbnails in sub folders named: ', 'lazyest-gallery' ) ?><input name="lazyest-gallery[thumb_folder]" id="thumb_folder" value="<?php echo $lg_gallery->get_option('thumb_folder'); ?>" size="25" class="code" type="text" /></label>
            <br />
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e( 'Cropping', 'lazyest-gallery' ) ?></th>
          <td>
            <label><input type="checkbox" name="lazyest-gallery[use_cropping]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option( 'use_cropping' ) ); ?> /><?php esc_html_e( ' Enable thumbnail cropping', 'lazyest-gallery'); ?></label>            
          </td>
        </tr>
      
      <tr>
        <th><?php esc_html_e( 'On Click', 'lazyest-gallery' ); ?></th>
        <td>
          <p><?php esc_html_e( 'Perform the following action when thumbnails are clicked:', 'lazyest-gallery' ); ?><br /></p>
          <select name="lazyest-gallery[on_thumb_click]">                    
            <option value="nothing" <?php selected( 'nothing', $lg_gallery->get_option( 'on_thumb_click' ) ); ?>><?php esc_attr_e( 'Nothing', 'lazyest-gallery' ); ?></option>
            <option value="slide" <?php selected( 'slide', $lg_gallery->get_option( 'on_thumb_click' ) ); ?>><?php esc_attr_e( 'Show slide in slide view', 'lazyest-gallery' ); ?></option>
            <option value="lightslide" <?php selected( 'lightslide', $lg_gallery->get_option( 'on_thumb_click' ) ); ?>><?php esc_attr_e( 'Show slide in Lightbox', 'lazyest-gallery' ); ?></option>
            <option value="thickslide" <?php selected( 'thickslide', $lg_gallery->get_option( 'on_thumb_click' ) ); ?>><?php esc_attr_e( 'Show slide in Thickbox', 'lazyest-gallery' ); ?></option>
            <option value="fullimg" <?php selected( 'fullimg', $lg_gallery->get_option( 'on_thumb_click' ) ); ?>><?php esc_attr_e( 'Show full size image', 'lazyest-gallery' ); ?></option>                                           
            <option value="lightbox" <?php selected( 'lightbox', $lg_gallery->get_option( 'on_thumb_click' ) ); ?>><?php esc_attr_e( 'Show full size image in Lightbox', 'lazyest-gallery' ); ?></option>                                           
            <option value="thickbox" <?php selected( 'thickbox', $lg_gallery->get_option( 'on_thumb_click' ) ); ?>><?php esc_attr_e( 'Show full size image in Thickbox', 'lazyest-gallery' ); ?></option>
          </select>
          <p>                   
    <?php if ( ! $lg_gallery->some_lightbox_plugin() ) {
            esc_html_e( ' (A supported Lightbox plugin was not detected.)', 'lazyest-gallery' ); 
          } 
    ?>    <br />
    <?php if ( ! $lg_gallery->some_thickbox_plugin() ) { 
            esc_html_e( ' (A supported Thickbox plugin was not detected.)', 'lazyest-gallery' ); 
          } 
    ?>
          </p>
        </td>
      </tr>
      <?php do_action( 'lazyest-gallery-settings_thumbnails' ); ?>
      </tbody>
    </table>
  </div>
  <?php    
  } // LazyestSettings::thumbnail_options()
  
  
  /**
   * LazyestSettings::slide_options()
   * 
   * @return void
   */
  function slide_options() {
    global $lg_gallery;
    ?>
    <div id="lg_slide_options" class="postbox <?php echo $this->installstyle; ?>">
      <h3 class="hndle"><span><?php esc_html_e( 'Slide View Options', 'lazyest-gallery' ) ?></span></h3>
      <table id="lg_slide_options_table" class="widefat">
        <tbody>
        <tr>
          <th scope="row"><?php esc_html_e( 'Comments', 'lazyest-gallery' ) ?></th>
          <td>
            <label><input name="lazyest-gallery[allow_comments]" type="checkbox" value="TRUE" <?php checked ( 'TRUE', $lg_gallery->get_option( 'allow_comments' ) ); ?>  />
              <?php esc_html_e( 'Enable user comments on slides', 'lazyest-gallery' );?>
            </label>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="pictwidth"><?php esc_html_e( 'Maximum Slides Width', 'lazyest-gallery' ) ?></label></th>
          <td><input name="lazyest-gallery[pictwidth]" id="pictwidth" value="<?php echo $lg_gallery->get_option( 'pictwidth' ); ?>" size="10" class="code" type="text" /> pixels</td>
        </tr>
        <tr>
          <th><label for="pictheight"><?php esc_html_e( 'Maximum Slides Height', 'lazyest-gallery' ) ?></label></th>
          <td><input name="lazyest-gallery[pictheight]" id="pictheight" value="<?php echo $lg_gallery->get_option( 'pictheight' ); ?>" size="10" class="code" type="text" /> pixels</td>
        </tr>
        
        <tr>
          <th scope="row"><?php esc_html_e( 'Caching', 'lazyest-gallery' ) ?></th>
          <td>
            <label><input type="checkbox" name="lazyest-gallery[enable_slides_cache]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option( 'enable_slides_cache' ) ); ?> /><?php esc_html_e( ' Enable slide caching', 'lazyest-gallery' ) ?></label><br />
            <label><?php esc_html_e( 'Store cached slides in sub folders named: ', 'lazyest-gallery' ) ?><input name="lazyest-gallery[slide_folder]" id="slide_folder" value="<?php echo $lg_gallery->get_option( 'slide_folder' ); ?>" size="25" class="code" type="text" /></label> <br />
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e( 'Slide Show', 'lazyest-gallery' ) ?></th>
          <td>
            <label><input type="checkbox" name="lazyest-gallery[enable_slide_show]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option( 'enable_slide_show' ) ); ?> /><?php esc_html_e( ' Enable Slide Show', 'lazyest-gallery' ) ?></label><br />
            <?php if ( '' == $lg_gallery->get_option('slide_show_duration') ) { $lg_gallery->change_option('slide_show_duration', '5'); } ?>
            <label><?php esc_html_e( 'Each slide will show for: ', 'lazyest-gallery' ); ?><input type="text" name="lazyest-gallery[slide_show_duration]" size="3" value="<?php echo $lg_gallery->get_option('slide_show_duration') ?>" /><?php esc_html_e( ' seconds', 'lazyest-gallery' ); ?></label><br />            
          </td>
        </tr>


        <tr>
          <th scope="row"><?php esc_html_e( 'On Click', 'lazyest-gallery' ) ?></th>
          <td>
            <?php esc_html_e( 'Perform the following action when slides are clicked:', 'lazyest-gallery' ) ?><br />
            <select name="lazyest-gallery[on_slide_click]">
              <option value="nothing" <?php selected( 'nothing', $lg_gallery->get_option( 'on_slide_click' ) ) ?>><?php esc_attr_e( 'Nothing', 'lazyest-gallery' ) ?></option>
              <option value="fullimg" <?php selected( 'fullimg', $lg_gallery->get_option( 'on_slide_click' ) ) ?>><?php esc_attr_e( 'Show full size image', 'lazyest-gallery' ) ?></option>
              <option value="popup" <?php selected( 'popup', $lg_gallery->get_option( 'on_slide_click' ) ) ?>><?php esc_attr_e( ' Show image in pop-up window', 'lazyest-gallery' ) ?></option>
              <option value="lightbox" <?php selected( 'lightbox', $lg_gallery->get_option( 'on_slide_click' ) ) ?>><?php esc_attr_e( 'Show image in Lightbox', 'lazyest-gallery' ) ?></option>
              <option value="thickbox" <?php selected( 'thickbox', $lg_gallery->get_option( 'on_slide_click' ) ) ?>><?php esc_attr_e( 'Shhow image in Thickbox', 'lazyest-gallery' ) ?></option>
            </select>
            <p>
            <?php if ( ! $lg_gallery->some_lightbox_plugin() ) {
              esc_html_e( '(A supported Lightbox plug-in was not detected.)', 'lazyest-gallery' ); 
            } 
            ?>
            <br />
            <?php if ( ! $lg_gallery->some_thickbox_plugin() ) { 
                esc_html_e( ' (A supported Thickbox plug-in was not detected.)', 'lazyest-gallery' ); 
              } 
            ?>
            </p>
          </td>
        </tr>
        <?php if ( function_exists( 'exif_read_data' ) ) : ?>  
        <tr>
          <th scope="row"><?php esc_html_e( 'Exif Data', 'lazyest-gallery' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="lazyest-gallery[enable_exif]" id="enable_exif" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option( 'enable_exif' ) ); ?> />
                <?php esc_html_e( 'Display image Exif data', 'lazyest-gallery' ); ?>                 
            </label>
          </td>  
        </tr>
        <?php endif; ?>        
      	<?php do_action( 'lazyest-gallery-settings_slides' ); ?>
        </tbody>
      </table>
    </div>                 
    <?php
  } // LazyestSettings::slide_options()
  
  /**
   * LazyestSettings::caption_options()
   * 
   * @return void
   */
  function caption_options() {
    global $lg_gallery;
    ?>        
    <div id="lg_caption_options" class="postbox <?php echo $this->installstyle; ?>">
      <h3 class="hndle"><span><?php esc_html_e( 'Caption Options', 'lazyest-gallery' ) ?></span></h3>
      <table id="lg_caption_options_table" class="widefat">
        <tbody>
        <tr>
          <th scope="row" colspan="3" class="th-full">
            <label>
              <input type="checkbox" name="lazyest-gallery[enable_captions]" value="TRUE" <?php checked ( 'TRUE', $lg_gallery->get_option( 'enable_captions' ) ); ?> />
              <?php esc_html_e( 'Use image captions instead of file names', 'lazyest-gallery' ) ?>
            </label>
          </th>
        </tr>
        
        <tr>
          <th scope="row" colspan="3" class="th-full">
            <label>
              <input type="checkbox" name="lazyest-gallery[use_folder_captions]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option( 'use_folder_captions' ) ); ?> />
              <?php esc_html_e( 'Use folder captions instead of folder names', 'lazyest-gallery' ) ?>
            </label>
          </th>
        </tr>
         
        <tr>
          <th scope="row" colspan="3" class="th-full">
            <label>
              <input type="checkbox" name="lazyest-gallery[thumb_description]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option( 'thumb_description' ) ); ?> />
              <?php esc_html_e( 'Show descriptions in thumbnail view', 'lazyest-gallery' ) ?>
            </label>
          </th>
        </tr>
        <tr>
          <th scope="row">
            <label for="captions_length">                
              <?php esc_html_e( 'Length of Captions in Thumbnail View', 'lazyest-gallery' ) ?>							
          		<input type="text" id="captions_length" name="lazyest-gallery[captions_length]" size="3" value="<?php echo $lg_gallery->get_option( 'captions_length' ); ?>" /> 
            	<?php esc_html_e( 'characters', 'lazyest-gallery' ) ?>                
            </label>
          </th>         
          <td>
          	<p><?php esc_html_e( 'Set to -1 to disable captions in thumbnail view.', 'lazyest-gallery' ); ?></p>
          	<p><?php esc_html_e( 'Set to  0 to disable cropping of captions text in thumbnail view.', 'lazyest-gallery' ); ?></p>
            <p></p><?php esc_html_e( 'Disable captions will also disable descriptions in thumbnail view.', 'lazyest-gallery' ); ?></p>
          </td>
        </tr>        
      	<?php do_action( 'lazyest-gallery-settings_captions' ); ?>
        </tbody>
      </table>
    </div>
    <?php    
  } //LazyestSettings::caption_options()
  
  
  /**
   * LazyestSettings::upload_options()
   * 
   * @return void
   */
  function upload_options() {
    global $lg_gallery;
    ?>
    <div id="lg_upload_options" class="postbox <?php echo $this->installstyle; ?>">
      <h3 class="hndle"><span><?php esc_html_e( 'Upload Options', 'lazyest-gallery' ) ?></span></h3>
      <div class="inside">
        <p><?php esc_html_e( 'These settings only affect the Lazyest Gallery image upload forms, not the standard WordPress uploader.', 'lazyest-gallery' ); ?></p>
      </div>
      <table id="lg_upload_options_table" class="widefat">
        <tbody>                    
        <tr>
          <th scope="row"><label for="fileupload_allowedtypes"><?php esc_html_e( 'Allowed File Extensions', 'lazyest-gallery' ) ?></label></th>
          <td><input name="lazyest-gallery[fileupload_allowedtypes]" type="text" id="fileupload_allowedtypes" value="<?php echo $lg_gallery->get_option( 'fileupload_allowedtypes' ); ?>" size="40" />
            <p><?php printf( esc_html__( 'Recommended: %sjpg jpeg gif png%s. Separate extensions by spaces (" ").', 'lazyest-gallery' ), '<code>', '</code>' ) ?></p>
          </td>
        </tr>                                								
        <tr>
          <th scope="row"><label for="flash_upload"><?php esc_html_e( 'Enable Flash Uploader', 'lazyest-gallery' ) ?></label></th>
          <td>
            <input type="checkbox" id="flash_upload" name="lazyest-gallery[flash_upload]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option('flash_upload') ); ?> /><br />
            <p><?php esc_html_e( 'Use the Adobe Flash Player to upload multiple images at once.', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>		
				<tr>
          <th scope="row"><label for="preread"><?php esc_html_e( 'Auto Read Image Data', 'lazyest-gallery' ) ?></label></th>
          <td>
            <input type="checkbox" id="preread" name="lazyest-gallery[preread]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option('preread') ); ?> /><br />
            <p><?php esc_html_e( 'Read image meta data for newly add images', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>								
        <tr>
          <th scope="row"><label for="enable_mwp_support"><?php esc_html_e( 'Enable Web Publishing Wizard Support', 'lazyest-gallery' ) ?></label></th>
          <td>
            <input type="checkbox" id="enable_mwp_support" name="lazyest-gallery[enable_mwp_support]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option('enable_mwp_support') ); ?> /><br />
            <p><?php esc_html_e( 'Note: This feature is only supported for use with Microsoft Windows XP.', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>
        <?php if ( 'TRUE' == $lg_gallery->get_option( 'enable_mwp_support' ) ) { ?>
        <tr id="mwp_support">
          <th scope="row">&nbsp;</th>
          <td>
            <div class="submit">
              <a class="button" href="admin.php?page=lazyest-gallery&amp;xp_wiz" ><?php esc_html_e( 'Upload Wizard Settings', 'lazyest-gallery' ) ?></a>
            </div>  
          </td>
        </tr> 
        <?php } ?>         
      	<?php do_action( 'lazyest-gallery-settings_upload' ); ?>
        </tbody>
      </table>
    </div>
    <?php    
  } // LazyestSettings::upload_options()
  
  /**
   * LazyestSettings::advanced_options()
   * 
   * @return void
   */
  function advanced_options() {
    global $lg_gallery;
    
    $add = esc_html__( 'Add', 'lazyest-gallery' ) . ' &raquo;';
    $remove = '&laquo; ' . esc_html__( 'Remove', 'lazyest-gallery' );
    $users = esc_html__( 'Users', 'lazyest-gallery' );    
    $option = '<option value="%1s">%2s</option>';   
    $blogusers = lg_get_users_of_blog();
    foreach( $blogusers as $user ) {
    	$user->get_role_caps();
      if ( ! $user->has_cap( 'manage_options') ) { // Administrators are gallery administators by default. They cannot be removed from this role		        
        $optionval = sprintf( $option, $user->ID, esc_attr( $user->user_nicename ) );
        if ( $user->has_cap( 'lazyest_manager' ) ) { // user has manager capabilities
          $moptions['has'][] = $optionval;
        } else {
          $moptions['not'][] = $optionval;
        }  
      }
    }    
    ?>
    <div id="lg_advanced_options" class="postbox <?php echo $this->installstyle; ?>">
      <h3 class="hndle"><span><?php esc_html_e( 'Advanced Options', 'lazyest-gallery' ) ?></span></h3>
      <table id="lg_advanced_options_table" class="widefat">
        <tbody>
        <tr>
          <th scope="col"><?php esc_html_e( 'Lazyest Gallery Administrators', 'lazyest-gallery' ) ?></th>
          <td>
          	<div id="lazyest-managers"> 
                <div id="not-manager" class="has_role">
                  <p><strong><?php echo $users ?></strong></p>
                  <select class="multiple" id="not-managers" name="lazyest-gallery[not-managers][]" multiple="multiple" size="10">
                    <?php if ( isset( $moptions['not'] ) ) { foreach ( $moptions['not'] as $eoption ) echo $eoption ; }  ?>                             
                  </select>
                  <p class="authorbutton"><input class="button-secondary" id="add-manager" name="lazyest-gallery[add-manager]" type="submit" value="<?php echo $add ?>" /> <img alt="" id="manager-ajax-loading" src="images/wpspin_light.gif" class="ajax-loading" /></p>      
                </div>
                <div id="is-manager" class="has_role">
                  <p><strong><?php esc_html_e( 'Administrators', 'lazyest-gallery' ); ?></strong></p>
                  <select class="multiple" id="is-managers" name="lazyest-gallery[is-managers][]" multiple="multiple" size="10">
                    <?php if ( isset( $moptions['has'] ) ) { foreach ( $moptions['has'] as $eoption ) echo $eoption; }  ?>                             
                  </select>      
                  <p class="authorbutton"><input class="button-secondary" id="remove-manager" name="lazyest-gallery[remove-manager]" type="submit" value="<?php echo $remove ?>" /></p>
                </div>
              <div class="clear"></div> 
            </div>
            <p><?php esc_html_e( 'Blog Administrators are Lazyest Gallery Administrators by default', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>      
        <tr>
          <th scope="col"><label for="use_permalinks"><?php esc_html_e( 'Use Permalinks for the Gallery', 'lazyest-gallery' ) ?></label></th>
          <td>
            <input type="checkbox" id="use_permalinks" name="lazyest-gallery[use_permalinks]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option('use_permalinks') ); ?> /><br />
            <p><?php esc_html_e( 'Enable this to show Gallery Folders as subpages of your Gallery Page.', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>
        <tr>
          <th scope="col"><label for="rel_canonical"><?php esc_html_e( 'Use Canonical links', 'lazyest-gallery' ) ?></label></th>
          <td>
            <input type="checkbox" id="rel_canonical" name="lazyest-gallery[rel_canonical]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option('rel_canonical') ); ?> /><br />
            <p><?php esc_html_e( 'Enable this to add a canonical link for the gallery in your page header.', 'lazyest-gallery' ) ?></p>
            <p><?php printf( esc_html__( 'This could interfere with SEO plugins. Please read %smore about canonical links%s', 'lazyest-gallery' ), '<a href="http://brimosoft.nl/2011/09/05/canonical-urls-revisited/">', '</a>' ); ?></p> 
          </td>
        </tr>
        <tr>
          <th scope="col"><label for="append_search"><?php esc_html_e( 'Append Gallery to Wordpress search results', 'lazyest-gallery' ) ?></label></th>
          <td>
            <input type="checkbox" id="append_search" name="lazyest-gallery[append_search]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option('append_search') ); ?> /><br />
            <p><?php esc_html_e( 'Enable this to show Gallery Folders and Images in the Wordpress search results.', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>
        <tr>
          <th scope="col"><label for="excluded_folders_string"><?php esc_html_e( 'Excluded Folders', 'lazyest-gallery' ) ?></label></th>
          <td>
            <input name="lazyest-gallery[excluded_folders_string]" id="excluded_folders_string" value="<?php echo implode( ',', $lg_gallery->get_option( 'excluded_folders' ) ); ?>" size="60" class="code" type="text" /> <br />
            <p><?php esc_html_e( 'List folders to exclude from the gallery.  Separate folders with commas (",") while omitting spaces.', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>
        <tr>
          <th scope="col"><label for="resample_quality"><?php esc_html_e( 'Image Resampling Quality', 'lazyest-gallery' ) ?></label></th>
          <td>
            <input name="lazyest-gallery[resample_quality]" id="resample_quality" value="<?php echo $lg_gallery->get_option( 'resample_quality' ); ?>" size="10" class="code" type="text" /><br />
            <p><?php esc_html_e( 'Valid settings range from 0 (low quality) to 100 (best quality).  This setting only applies to JPEG files.', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>
        <tr>
          <th scope="col"><label for="link_to_gallery"><?php esc_html_e( 'Shortcode links to Gallery', 'lazyest-gallery' ) ?></label></th>
          <td>
            <input type="checkbox" id="link_to_gallery" name="lazyest-gallery[link_to_gallery]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option('link_to_gallery') ); ?> /><br />
            <p><?php esc_html_e( 'Enable this to jump to the Gallery after a user clicks on an folder shortcode in a post.', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>
        <tr>
          <th scope="col"><label for="listed_as"><?php esc_html_e( 'The images in the Gallery should be listed as', 'lazyest-gallery' ); ?></label></th>
          <td>
            <input name="lazyest-gallery[listed_as]" id="listed_as" value="<?php echo $lg_gallery->get_option( 'listed_as' ) ?>" size="12" type="text" />
          </td>
        </tr>
        <tr>
          <th scope="col"><label for="show_credits"><?php esc_html_e( 'Credits', 'lazyest-gallery' ); ?></label></th>
          <td>
            <input type="checkbox" name="lazyest-gallery[show_credits]" id="show_credits" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option( 'show_credits' ) ); ?> /><br />
            <p><?php esc_html_e( 'Enable this to support Lazyest Gallery by showing the "Powered by Lazyest Gallery" banner below your gallery', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>
        <tr>
          <th><label for="memory_ok"><?php esc_html_e( 'Do not check Memory before creating images', 'lazyest-gallery' ) ?></label></th>
          <td>
            <input type="checkbox" id="memory_ok" name="lazyest-gallery[memory_ok]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option('memory_ok') ); ?> /><br />
            <p><?php esc_html_e( 'Enable this to skip the memory check. Warning, this could crash your gallery.', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>
        <tr>
          <th><label for="table_layout"><?php esc_html_e(  'Use <table> element for gallery layout', 'lazyest-gallery' ); ?></label></th>
          <td>
            <input type="checkbox" id="table_layout" name="lazyest-gallery[table_layout]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option('table_layout') ); ?> /><br />
            <p><?php esc_html_e( 'Enable this to use a <table> element to display the gallery as in previous Lazyest Gallery versions.', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>
        <tr>
          <th><label for="ajax_pagination"><?php esc_html_e(  'Use AJAX to refresh thumbnail pages', 'lazyest-gallery' ); ?></label></th>
          <td>
            <input type="checkbox" id="ajax_pagination" name="lazyest-gallery[ajax_pagination]" value="TRUE" <?php checked( 'TRUE', $lg_gallery->get_option('ajax_pagination') ); ?> /><br />
            <p><?php esc_html_e( 'Enable this to refresh the gallery without refreshing the whole page', 'lazyest-gallery' ) ?><br />
							 <?php esc_html_e( 'Warning: This will stop lightbox or thickbox on secondary pages', 'lazyest-gallery' ) ?></p>
          </td>
        </tr>        
      	<?php do_action( 'lazyest-gallery-settings_advanced' ); ?>
        </tbody>
      </table>
    </div>
    <?php    
  } // LazyestSettings::advanced_options()
  
  function utilities() {
    global $lg_gallery;
    ?>
    <div id="submitdiv" class="postbox <?php echo $this->installstyle; ?>">    
      <h3 class="hndle"><span><?php esc_html_e( 'Utility functions', 'lazyest-gallery' ) ?></span></h3>
      <div class="inside">
        <div id="submitpost" class="submitbox">
          <div id="misc-publishing-actions">
            <div class="misc-pub-section">
              <div id="cache-rebuilder" class="hide-if-no-js alignleft">
                <p><a id="rebuild-cache" class="button" href="#" title="<?php esc_html_e( 'Create missing thumbs and slides for your gallery', 'lazyest-gallery' ) ?>"><?php esc_html_e( 'Fill up Cache', 'lazyest-gallery' ) ?></a></p>
                <p><span id="cache-bar" class="progressBar ajax-loading"></span></p>
              </div>
              <div id="preview-action">
                <p><a class="button" href="admin.php?page=lazyest-gallery&amp;clear_cache" title="<?php esc_html_e( 'Clear thumbs and slides from your gallery', 'lazyest-gallery' ) ?>"><?php esc_html_e( 'Clear Cache', 'lazyest-gallery' ) ?></a></p>                            
              </div>
              <br class="clear"/>    
            </div>
            <div class="misc-pub-section misc-pub-section-last">
              <p><a id="rebuild-database" class="button" href="#" title="<?php esc_html_e( 'Insert Images paths into the WordPress database', 'lazyest-gallery' ) ?>"><?php esc_html_e( 'Build Links Database', 'lazyest-gallery' ) ?></a></p>
              <p><span id="database-bar" class="progressBar ajax-loading"></span></p>
              <p><?php echo __( 'Use this function if you see an increase in loading time for your comments.', 'lazyest-gallery' ) ?></p>
            </div>        
          </div>          
          <div id="major-publishing-actions">
            <div id="delete-action">
              <a onclick="if ( confirm('<?php echo __( 'You are about to Reset all Options.', 'lazyest-gallery'); ?>\n  \'<?php echo __('Cancel', 'lazyest-gallery'); ?>\'<?php echo __(' to stop, '); ?> \'OK\'<?php echo __(' to delete.'); ?>')  { return true;}return false;" class="submitdelete deletion" href="admin.php?page=lazyest-gallery&amp;reset_options" title="<?php esc_attr_e( 'Reset all options to their default values.', 'lazyest-gallery' )  ?>"><?php esc_html_e( 'Reset Options', 'lazyest-gallery' ) ?></a>
            </div> 
            <div id="publishing-action">
              <input class="button-primary" type="submit" name="lazyest-gallery[update_options-s]" value="<?php	esc_html_e( 'Save Changes', 'lazyest-gallery' )	?>" />
            </div> 
            <div class="clear"></div>
          </div>
        </div>
      </div>
    </div>
    <?php    
  }
  
  function aboutbox() {
    ?>
    <div id="aboutbox" class="postbox <?php echo $this->installstyle; ?>">
      <h3 class="hndle"><span><?php esc_html_e( 'About Lazyest Gallery', 'lazyest-gallery' ); ?></span></h3>
      <div class="inside">
        <div id="version" class="misc-pub-section">               
          <div class="versions">
            <p><span id="lg-version-message"><strong><?php esc_html_e( 'Version', 'lazyest-gallery' ); ?></strong>: <?php echo  lg_version(); ?></span></p>
          </div>
        </div>
        <div id="links" class="misc-pub-section">
          <p><a class="home" target="_blank" href="http://brimosoft.nl/lazyest/gallery/"><?php esc_html_e( 'Plugin Homepage', 'lazyest-gallery' ); ?></a></p>
          <p><a class="notepad" target="_blank" href="http://brimosoft.nl/lazyest/gallery/user-guide/"><?php esc_html_e( 'User Guide', 'lazyest-gallery' ); ?></a></p>
          <p><a class="popular" target="_blank" href="http://brimosoft.nl/lazyest/gallery/frequently-asked-questions/"><?php esc_html_e( 'Frequently Asked Questions', 'lazyest-gallery' ); ?></a></p>
          <p><a class="add" target="_blank" href="http://brimosoft.nl/forums/forum/requests/"><?php esc_html_e( 'Suggest a Feature', 'lazyest-gallery' ); ?></a></p>
          <p><a class="rss" target="_blank" href="http://brimosoft.nl/category/lazyest-gallery/feed/"><?php esc_html_e( 'Lazyest Gallery News', 'lazyest-gallery' ); ?></a></p>
          <p><a class="user-group" target="_blank" href="http://wordpress.org/tags/lazyest-gallery?forum_id=10"><?php esc_html_e( 'WordPress Forum', 'lazyest-gallery' ); ?></a></p>
        </div>        
        <div id="donate" class="misc-pub-section misc-pub-section-last">
          <p style="text-align:center"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=1257529" title="<?php esc_html_e( 'Donate with PayPal', 'lazyest-gallery' ); ?>"><img src="https://www.paypal.com/en_US/NL/i/btn/btn_donateCC_LG.gif" alt="" /></a></p>
        </div>
      </div>
    </div>
    <?php
  }
    
} // class LazyestSettings
 
?>
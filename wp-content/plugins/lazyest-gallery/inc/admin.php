<?php
/**
 * LazyestAdmin
 * 
 * This class contains all functions and actions required for Lazyest Gallery to work in the admin of WordPress
 * Thanks to:
 * Denis Howlett <feedback@isocra.com> // WWW: http://www.isocra.com/ for Table drag and Drop
 * Stuart Langridge, http://www.kryogenix.org/days/2007/04/07/sorttable-v2-making-your-tables-even-more-sortable for Table Sorting script
 * 
 * @package Lazyest Gallery
 * @author Marcel Brinkkemper
 * @copyright 2010-2012 Brimosoft
 * @version 1.1.10
 * @access public
 * @since 0.16.0
 * 
 */

class LazyestAdmin extends LazyestGallery {
  
  var $plugin_basename;
  
  /**
   * Wether the tables should be manually sortable
   * 
   * @var array
   * @since 1.1.0
   */
  var $sortit;
  
  /**
   * Holds the Admin message displayed below h2
   * 
   * @var string
   * @since 1.1.0
   */
  var $message;
  
  
  /**
   * Holds the result of last change
   * 
   * @var bool
   * @since 1.1.0
   */
  var $success;
     
  /**
   * LazyestAdmin::__construct()
   * 
   * @return void
   */
  function __construct() {
    LazyestGallery::__construct();        
    if ( isset( $_GET['flash'] ) ) {
      $option = ( '1' == $_GET['flash'] ) ? 'TRUE' : 'FALSE';
      $this->update_option( 'flash_upload', $option );
    }
    
    // admin settings actions and filters
    add_action( 'admin_menu', array( &$this, 'add_pages' ) );   
    add_action( 'admin_init', array( &$this, 'register_settings' ) );
    add_action( 'init', array( &$this, 'register_scripts' ) );
    add_action( 'init', array( &$this, 'register_styles' ) );
    add_filter( 'plugin_action_links', array( &$this, 'filter_plugin_actions' ), 10, 2 );
    add_filter( 'editable_roles', array( &$this, 'editable_roles' ) );
    
    // media upload actions and filters
    add_filter( 'media_upload_tabs', array( &$this, 'upload_tabs' ) );
    add_action( 'media_upload_lazyestgallery', array( &$this, 'upload_lazyestgallery' ) );
    add_action( 'admin_print_scripts-media-upload-popup', array( &$this, 'media_upload_js') );
    add_action( 'admin_print_styles-media-upload-popup', array( &$this, 'manager_css' ) ); 
    
    $this->sortit = array();
    $this->sortit['images'] = ( 'MANUAL' == $this->get_option( 'sort_alphabetically' ) );    
    $this->sortit['folders'] = ( 'MANUAL' == $this->get_option( 'sort_folders' ) );
    $this->success = true;
  }
  
  
  /* 
   * Section: WordPress actions and filters
   */
  
  
  /**
   * LazyestAdmin::add_pages()
   * Adds Lazyest Gallery Admin pages
   * 
   * @return void
   */
  function add_pages() {
    
    // add settings page for administrators
   	if ( current_user_can ( 'manage_options' ) && function_exists ( 'add_options_page' ) ) {
      $menu_page_hookname = add_options_page( __('Lazyest Gallery Settings'), __('Lazyest Gallery'), 'manage_options', 'lazyest-gallery', array( &$this , 'settings_page' ) );
      add_action( "admin_print_scripts-{$menu_page_hookname}", array(&$this, 'admin_js' ) );
      add_action( "admin_print_styles-{$menu_page_hookname}", array( &$this, 'manager_css' ) );  
    }
    
    // show manage pages for user that have viewer access and have at least default editor capabilities    
    $capability = $this->level_cap( $this->get_option( 'viewer_level' ) );
		if ( ( $this->get_option('new_install') != 'TRUE' ) && $this->valid() && ( current_user_can( $capability ) && current_user_can( $this->default_editor_capability() ) ) ) {            
      
			$filemanager_page_hookname = add_menu_page( __('Lazyest Gallery'), __('Lazyest Gallery'), 'read', 'lazyest-filemanager', array( &$this , 'filemanager_page' ), path_join( $this->plugin_url, 'images/file-manager.png' ) );
			add_action( "admin_print_scripts-{$filemanager_page_hookname}", array( &$this,  'manager_js' ) );
      add_action( "admin_print_styles-{$filemanager_page_hookname}", array( &$this, 'manager_css' ) ); 
      
      if ( 'TRUE' == $this->get_option('allow_comments') ){
        $allcomments_page_hookname = add_submenu_page('lazyest-filemanager', __('Lazyest Gallery Comments'), __('Comments'), 'edit_posts', 'lazyest-comments', array( &$this, 'allcomments_page' ) );
        add_action( "admin_print_scripts-{$allcomments_page_hookname}", array( &$this,  'manager_js' ) );
      	add_action( "admin_print_styles-{$allcomments_page_hookname}", array( &$this, 'manager_css' ) );  
      }
      
      
      $theme_page_hookname = add_submenu_page('lazyest-filemanager', __('Lazyest Gallery Themes'), __('Themes'), 'edit_themes', 'lazyest-themesmanager', array( &$this, 'themes_page') );              
      add_action( "admin_print_scripts-{$theme_page_hookname}", array( &$this,  'manager_js' ) );
      add_action( "admin_print_styles-{$theme_page_hookname}", array( &$this, 'manager_css' ) ); 
			     
			add_filter ( "plugin_action_links-{$this->plugin_basename}", array( &$this, 'filter_plugin_actions' ) );            
    }
  }
  
  /**
	 * LazyestAdmin::register_settings()
   * Register settings for WP DB
	 * 
	 * @return void
	 */
	function register_settings () {
		register_setting( 'lazyest-gallery' , 'lazyest-gallery' , array( &$this , 'update' ) );
	}
  
  /**
   * LazyestAdmin::register_scripts()
   * Script used in Lazyest Gallery Admin
   * 
   * @return void
   */
  function register_scripts() {    
    $j = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'dev.js' : 'js';
    wp_register_script( 'tablednd', $this->plugin_url . "/js/jquery.tablednd_0_5.$j", array('jquery'), '0.5', false );
    wp_register_script( 'lg_sorttable', $this->plugin_url . "/js/lazyest-sorttable.$j", array(), '1.1', true );
    wp_register_script( 'lg_context', $this->plugin_url . "/js/jquery.contextmenu.$j", array('jquery'), '1.01', true );
    wp_register_script( 'lg_progressbar', $this->plugin_url . "/js/jquery.progressbar.$j", array( 'jquery' ), '2.01', true );  
    wp_register_script( 'lg_manager', $this->plugin_url . "/js/lazyest-manager.$j", array('tablednd', 'lg_context', 'lg_progressbar', 'lg_sorttable' ), lg_version(), true );       
    wp_register_script( 'lg_loader', $this->plugin_url . "/js/lazyest-loader.$j", array( 'jquery' ), '1.1', true );      
		add_thickbox(); 
  }
  
  /**
   * LazyestAdmin::register_styles()
   * 
   * @since 1.1.0
   * @return void
   */
  function register_styles() {
    $c = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'dev.css' : 'css';
    wp_register_style( 'lg_admin_style', $this->plugin_url . "/css/_admin.$c" );
  }
  
  /**
   * LazyestAdmin::admin_js()
   * Script used in Lazyest Gallery Settings screen 
   * 
   * @return void
   */
  function admin_js() {    
    wp_enqueue_script( 'lg_manager' );
    wp_localize_script( 'lg_manager', 'lazyestmgr', $this->localize_manager() );
    wp_enqueue_script( 'lg_progressbar' );
  }
  
  /**
   * LazyestAdmin::media_upload_js()
   * Load script for media-upload tab in header
   * @return void
   */
  function media_upload_js() {    
    $j = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'dev.js' : 'js';
    wp_enqueue_script( 'lg_media_manager', $this->plugin_url . "/js/lazyest-manager.$j", array( 'jquery' ), '1.1', false );    
    wp_localize_script( 'lg_media_manager', 'lazyestmgr', $this->localize_manager() );
    wp_enqueue_script( 'lg_loader' ); 
    wp_localize_script( 'lg_loader', 'lazyestimg', $this->localize_loader() );  
  }
  
  /**
   * LazyestAdmin::manager_js()
   * Scripts used by Folder and Image Admin screens 
   * 
   * @return void
   */
  function manager_js() {
    if ( function_exists( 'wp_enqueue_script' ) ) {
      wp_enqueue_script( 'tablednd' );
      wp_enqueue_script( 'lg_sorttable' );
      wp_enqueue_script( 'lg_context' );
      wp_enqueue_script( 'lg_manager' );
      wp_localize_script( 'lg_manager', 'lazyestmgr', $this->localize_manager() );
      wp_enqueue_script( 'lg_loader' ); 
      wp_localize_script( 'lg_loader', 'lazyestimg', $this->localize_loader() );    
      if (  'TRUE' == $this->get_option( 'flash_upload' ) )  {
        $this->update_option( 'flash_upload', 'TRUE' );
      }  
      if ( isset($_GET['flash'] ) ) {     
        if ( '0' == $_GET['flash'] ) { 
          $this->update_option( 'flash_upload', 'FALSE' );
        }
      }  
    }   
  }
  
  /**
   * LazyestAdmin::manager_css()
   * add stylesheet for Admin pages
   * 
   * @since 1.1.0
   * @return void
   */
  function manager_css() {
    wp_enqueue_style( 'lg_admin_style' );
  }
  
  /**
   * LazyestAdmin::localize_manager()
   * Strings used in manager javascript
   * 
   * @return
   */
  function localize_manager() {
  	$manual = ( 'MANUAL' == $this->get_option( 'sort_alphabetically' ) ) || ( 'MANUAL' == $this->get_option( 'sort_folders' ) ) ?
  		'MANUAL' : '';
    return array(    
      'manual' => $manual,
      'newpage' => __( "Are you sure you want to close this page?\n\nThe Changes you made will be lost if you navigate away from this page.\n\nClick OK to continue or Cancel to stay on the current page. ", 'lazyest-gallery'),
      'foldersuccess' =>  esc_html__( 'Folder %s created successfully', 'lazyest-gallery' ),
      'viewersuccess' => esc_html__( 'Viewer level updated successfully', 'lazyest-gallery' ),
      'thumbs' => $this->get_option( 'thumb_folder' ),
      'slides' => $this->get_option( 'slides_folder' ),
      'boxImage' => $this->plugin_url . '/images/progressbar.gif',
      'barImage' => $this->plugin_url . '/images/progressbg_green.gif',
      'rebuildReady' => esc_html__( 'Rebuild Ready', 'lazyest-gallery', 'lazyest-gallery' ),
      'cannotTruncate' => esc_html__( 'Something went wrong accessing the Gallery Database table', 'lazyest-gallery' )
    );
  }
  
  /**
   * LazyestAdmin::localize_script()
   * Strings used in SWF upload
   * 
   * @return void
   */
  function localize_swf()  {
   	$max_upload_size = ( (int) ( $max_up = @ini_get('upload_max_filesize') ) < (int) ( $max_post = @ini_get('post_max_size') ) ) ? $max_up : $max_post;
  	if ( empty( $max_upload_size ) )
		$max_upload_size = __('not configured');

  	return array(
			'queue_limit_exceeded' => esc_html__('You have attempted to queue too many files.', 'lazyest-gallery' ),
			'file_exceeds_size_limit' => sprintf( esc_html__('This file is too big. The maximum upload size for your server is %s.', 'lazyest-gallery' ), $max_upload_size ),
			'zero_byte_file' => esc_html__('This file is empty. Please try another.', 'lazyest-gallery' ),
			'invalid_filetype' => esc_html__('This file type is not allowed. Please try another.', 'lazyest-gallery' ),
			'default_error' => esc_html__('An error occurred in the upload. Please try again later.', 'lazyest-gallery' ),
			'missing_upload_url' => esc_html__('There was a configuration error. Please contact the server administrator.', 'lazyest-gallery' ),
			'upload_limit_exceeded' => esc_html__('You may only upload 1 file.', 'lazyest-gallery' ),
			'http_error' => esc_html__('HTTP error.', 'lazyest-gallery' ),
			'upload_failed' => esc_html__('Upload failed.', 'lazyest-gallery' ),
			'io_error' => esc_html__('IO error.', 'lazyest-gallery' ),
			'security_error' => esc_html__('Security error.', 'lazyest-gallery' ),
			'file_cancelled' => esc_html__('File cancelled.', 'lazyest-gallery' ),
			'upload_stopped' => esc_html__('Upload stopped.', 'lazyest-gallery' ),
			'dismiss' => esc_html__('Dismiss'),
			'crunching' => esc_html__('Crunching...', 'lazyest-gallery' ),
			'deleted' => esc_html__('moved to the trash.', 'lazyest-gallery' ),
			'ready' => esc_html__( 'Ready', 'lazyest-gallery' )
    );
  }
    
  /**
   * LazyestAdmin::filter_plugin_actions()
   * Link to Lazyest Gallery Settings
   * 
   * @param mixed $links
   * @return
   */
  function filter_plugin_actions( $links, $file ) {
    if ( $file == $this->plugin_basename ) {      
		  $links[] = '<a href="admin.php?page=lazyest-gallery">' . esc_html__( 'Settings' ) . '</a>';
		  if (( $this->get_option( 'new_install' ) != 'TRUE' ) 
				&& $this->valid() 
					&& file_exists( $this->get_absolute_path( ABSPATH . $this->get_option('gallery_folder') ) ) )
    				$links[] = '<a href="admin.php?page=lazyest-filemanager">' . esc_html__( 'Manage', 'lazyest-gallery' ) . '</a>'; 
    }
		return $links;
	}
	
  
   
  /*
   * Section: main manager screen
   */  
  
  /**
   * LazyestAdmin::manage()
   * The main manager screen
   * 
   * @return void
   */
  function manage() {   
  	global $wp_version;
    if ( isset( $_POST['sort_gallery_structure'] ) || isset( $_POST['sort_gallery_structure-s'] ) ) {
		  $this->save_changed_folders();		
    } 
		 if ( isset( $_REQUEST['create_new_folder'] ) ) {
      if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'lg_manage_folder' ) ) {      	
      $thisname = $this->curdir . $_REQUEST['new_folder_name'];
      	$this->new_gallery_folder( $thisname );
			}
    }  
    $message = '';
   	$folders = $this->folders( 'root', 'hidden' );
    if ( $this->valid() ) {
      $can_save = true;      
  		if ( 0 < count( $folders ) ) {
        for ( $i = 0; $i != count( $folders ); $i++ ) {
    		  $folder = $folders[$i];
          if ( ! $folder->can_save() ) {
            $can_save = false;
          }
    		}
      } else {
      	/* translators 1; <a href="">, 2: </a> */
        $this->message = sprintf( esc_html__( 'Start building your gallery here. You have no folders in your gallery yet. Please create a folder in the %1sNew Folder box%2s', 'lazyest-gallery' ), '<a href="#newfolder">', '</a>' );
      }
    } else {
      $this->success = false;
      $this->message = sprintf( esc_html__( 'The directory where the gallery should be, does not exist. Please go to %1ssettings%2s to create this directory', 'lazyest-gallery' ), '<a href="options-general.php?page=lazyest-gallery">', '</a>' );
    }
    $title = ( 0 != count( $folders ) ) ? __( 'Edit Lazyest Gallery Structure', 'lazyest-gallery' ) : __( 'Welcome to Lazyest Gallery', 'lazyest-gallery' ); 	 
      ?>
      <div class="wrap">        
      <?php screen_icon( 'folders' ); ?>
      <h2><?php echo esc_html( $title ); ?></h2>  
      <?php $this->options_message(); ?>
      <div id="ajax-div"></div>
			<?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?>			
      <div id="poststuff" class="metabox-holder has-right-sidebar"> 
  		<?php else : ?>  		
      <div id="poststuff" class="metabox-holder"> 
			<?php endif; ?>      	         
        <form name="sort_gallery_form" method="post" id="sort_gallery_form" action="admin.php?page=lazyest-filemanager">
					<input id="folder_id" type="hidden" name="folder_id" value="0" />
	        <?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?>					            
	        <?php $this->sidebar(); ?>        
	      	<div id="post-body">
	      	<?php else : ?>      
	      		<div id="post-body" class="metabox-holder columns-2">	      	            
	        	<?php $this->sidebar(); ?>  
	      	<?php endif; ?>
	        <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('lg_manage_gallery');  ?>                	        
	          <div id="post-body-content">          
	            <div id="foldersdiv"> 
	              <?php $this->foldersbox( $folders ); ?>
	            </div>
	          </div>
					</div>    
      		</div>
	        </form>
      <br class="clear" />
      </div>
    <?php 
  }
  
  /**
   * LazyestAdmin::foldersbox()
   * Manage the root folders in the gallery 
   * 
   * @param mixed $folders
   * @return void
   */
  function foldersbox( $folders ) {	
    $buttontext =  __( 'Save Changes', 'lazyest-gallery' ); 
    $hidden = ( 0 == count( $folders ) ) ? 'style="display: none;"' : '';
    $pagination = $this->pagination( 'afolders',  $folders );     
  	$can_save = true;    
  	for ( $i = 0; $i != count( $folders ); $i++ ) {
  	  $subfolder = $folders[$i];
  		if ( ! $subfolder->can_save() ) $can_save = false;
  	}
    $folder_table = new LazyestFolderTable( $folders );      
    ?>
    <div id="folderbox" class="postbox" <?php echo $hidden ?>>
      <?php if ( ! $this->sortit['folders'] && ( 20 < count( $folders ) ) ) { ?>
      <div class="inside">
        <div class="tablenav">                   
          
          <?php echo $pagination ?>
        </div>
      </div>
      <br class="clear" />      
      <?php	} ?>      
      <div id="admin_folders_div">      
      <?php $folder_table->display(); ?>      
      </div>
  		<?php if ( count( $folders ) > 10 ) { ?>
      <div class="inside">
        <div class="tablenav">                    
          <?php if ( ! $this->sortit['folders'] && ( 20 < count( $folders ) ) ) { ?>
            <?php echo $pagination ?>
          <?php	} ?>  
          <?php if ( current_user_can( 'create_lazyest_folder' ) || current_user_can( 'manage_options' )  ) { ?>            
          <input class="button-primary" name="sort_gallery_structure" type="submit" value="<?php echo esc_html( $buttontext ) ?>" />
          <?php } ?>          
        </div>
      </div>
      <br class="clear" />
      <?php	} ?>    
    </div>
    <?php
    unset( $folder_table );
    return true;
  }
   
  /**
   * LazyestAdmin::sidebar()
   * The sidebar of the main gallery screen 
   * @return void
   */
  function sidebar() {
		global $wp_version;
    ?>
    <?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?> 
	    <div id="side-info-column" class="inner-sidebar">
	    <?php else : ?>
	    <div id="postbox-container-1" class="postbox-container inner-sidebar">
	    <?php endif; ?>
      <div id="side-sortables" class="meta-box-sortables ui-sortable">
      <?php $this->submitbox(); ?>
      <?php $this->newfolderbox(); ?>
      <?php $this->editorbox(); ?>
      <?php $this->viewerbox(); ?>
      </div>
    </div>
    <?php  
  }
  
  
  /**
   * LazyestAdmin::submitbox()
   * Box with page information and main submit button
   * 
   * @since 1.1.0
   * @return void
   */
  function submitbox() {
    global $post;
 		$page_id = $this->get_option( 'gallery_id' );
  	$page = get_page( $page_id );       
		$edit_link = get_edit_post_link( $page->ID ); 
    $post = $page;
	  setup_postdata( $page );
		if ( 'TRUE' == $this->get_option( 'allow_comments' ) ) { 
		  $comment_count = $this->commentor->count_comments( -1 );
		  $comments_url = 'admin.php?page=lazyest-comments';
    } else {
      $comment_count = $page->comment_count;
      $comments_url = 'edit-comments.php?p=' . $this->get_option( 'gallery_id' );
    }    
    $title = esc_html( $page->post_title );
    /* translators: UNIX time format string for creation date */
    $datef = __( 'M j, Y @ G:i', 'lazyest-gallery' );
    $stamp = __( 'Created on: <b>%1$s</b>', 'lazyest-gallery' );

    $date = date_i18n( $datef, strtotime( $post->post_date ) );
    ?>     
    <div id="submitdiv" class="postbox">
      <h3 class="hndle"><span><?php echo esc_html__( 'Gallery', 'lazyest-gallery') ?></span></h3>
      <div class="inside">
        <div id="submitpost" class="submitbox">
          <div id="minor-publishing-actions">
            <div id="save-action">
              <img alt="" id="draft-ajax-loading" class="ajax-loading" src="<?php echo admin_url( 'images/wpspin_light.gif' ) ?>" style="visibility:hidden;" />
            </div>
            <div id="preview-action">
              <a class="preview button" href="<?php echo $this->uri(); ?>" target="_blank"><?php esc_html_e( 'View the Gallery','lazyest-gallery' ); ?></a>
              <br />
            </div>
            <div class="clear"></div>
            <div class="misc-pub-section "></div>
          </div>
          <div id="misc-publishing-actions">
            <div id="ftitle" class="misc-pub-section">            
              <?php esc_html_e( 'Title', 'lazyest-gallery' ); ?>:
              <strong><?php echo $title; ?></strong> 
            </div>            
            <div id="author" class="misc-pub-section">            
              <?php esc_html_e( 'Author', 'lazyest-gallery' ); ?>:
              <strong><?php the_author() ?></strong> 
            </div>        
            <div id="images" class="misc-pub-section hide-if-no-js">            
              <?php esc_html_e( 'Images', 'lazyest-gallery' ); ?>:
              <span class="lg_folder_subcount" title="<?php echo esc_attr( '/' ) ?>" id="lg_sc_-1"><?php echo sprintf( __( '%s in folders'), '0'  ) ?></span> 
            </div>       
            <div id="comments" class="misc-pub-section">
            <div class="wrapper-wrapper"><div class="post-com-count-wrapper"><a class="post-com-count" href="<?php echo $comments_url; ?>"><span class="comment-count"><?php echo $comment_count ?></span></a></div></div>
            <div><?php esc_html_e( 'Comments', 'lazyest-gallery' ); ?>:</div>             
            <div class="clear"></div>
            </div>          
            <div class="misc-pub-section curtime misc-pub-section-last">
              <span id="timestamp"><?php printf($stamp, $date); ?></span>
            </div>     
          </div>
          <?php if ( current_user_can( 'create_lazyest_folder' ) || current_user_can( 'manage_options' ) ) { ?>
          <div id="major-publishing-actions">
            <div id="publishing-action">
              <img alt="" id="ajax-loading" class="ajax-loading" src="<?php echo admin_url( 'images/wpspin_light.gif' ) ?>" style="visibility: hidden;" />
              <input type="submit" class="button button-highlighted button-primary" name="sort_gallery_structure" value="<?php esc_html_e( 'Save Changes', 'lazyest-gallery' ); ?>" />
            </div>
            <div class="clear"></div>
          </div>
          <?php } ?>                		
        </div>
      </div>
    </div>
    <?php
  }
  
  /**
   * LazyestAdmin::newfolderbox()
   * Show the box to enter a new subfolder
   * 
   * @return void
   */
  function newfolderbox( $folder = null ) { 
    $can_folder = ( $folder != null ) ? $folder->user_can( 'editor' ) : false;
    if ( current_user_can( 'manage_options' ) || current_user_can( 'create_lazyest_folder' ) ) {
      ?>
      <div id="newfolder" class="postbox">
        <h3 class="hndle"><?php esc_html_e( 'New Folder', 'lazyest-gallery' ); ?></h3>
        <div class="inside">
          <a name="newfolderbox"></a>
          <input id="lg_nw_folder" type="text" name="new_folder_name" /><input type="submit" id="lgn_button_submit" name="create_new_folder" class="button-secondary hide-if-js lgn_button" value="<?php esc_html_e( 'Add', 'lazyest-gallery' ); ?>" />
          <input id="lgn_button_a" type="submit" name="create_new_folder" class="button-secondary lgn_button" value="<?php esc_html_e( 'Add', 'lazyest-gallery' ); ?>" />        
          <span><img class="ajax-loading" alt="" id="lgn_ajax-loading" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" /></span>
          <br class="clear" />
        </div>      
      </div>
      <?php
    }
  }
  
  /**
   * LazyestAdmin::editorbox()
   * Box with Author and ditor information
   * @todo add editor functionality
   * @since 1.1.0
   * @return void
   */
  function editorbox() {
    if ( ! ( current_user_can( 'manage_lazyest_files' ) || current_user_can( 'manage_options') ) )
      return;
    global $wp_roles;
    $blogusers = lg_get_users_of_blog(); 
    $mcnt = $acnt = $ecnt = $vcnt = 0;
    $admins = $authors = $editors = $viewers = array();
    $option = '<option value="%s">%s</option>';   
    foreach( $blogusers as $user ) {
    	$user_nicename = esc_attr( $user->user_nicename );
      $optionval = sprintf( $option, $user->ID, $user_nicename );      
      if ( $user->has_cap( 'manage_lazyest_files' ) ) { // user has admin capabilities
        if ( 6 > $mcnt ) 
          $admins[] = $user_nicename;
        if ( 6 == $mcnt )
          $admins[] = '&hellip;';
      } else {
        if ( $user->has_cap( 'create_lazyest_folder' ) ) { // user has editor capabilities
          $eoptions['has'][] = $optionval;
          if ( 6 > $ecnt )
            $editors[] = $user_nicename;
          if ( 6 == $ecnt )
            $editors[] = '&hellip;';
          $acnt++;
        } else {
          $eoptions['not'][] = $optionval;
          if ( $user->has_cap( 'edit_lazyest_fields' ) ) { // user has author capabilities 
            $aoptions['has'][] = $optionval;
            if ( 6 > $acnt )
              $authors[] = $user_nicename;
            if ( 6 == $acnt )
              $authors[] .= '&hellip;';
          } else {       
            $aoptions['not'][] = $optionval;
           }
        }
      }       
    }
    $addremove = __( 'Add / Remove', 'lazyest-gallery' );
    $add = __( 'Add &raquo;', 'lazyest-gallery' );
    $remove = __( '&laquo; Remove', 'lazyest-gallery' );
    $users = __( 'Users', 'lazyest-gallery' );
    $authorstyle = ( isset( $_REQUEST['edit'] ) && ( $_REQUEST['edit'] == 'authors') ) ? 'display:block;' : 'display:none;'; 
    $editorstyle = ( isset( $_REQUEST['edit'] ) && ( $_REQUEST['edit'] == 'editors') ) ? 'display:block;' : 'display:none;';     
    ?>
    <div class="postbox" id="editordiv">
      <h3 class="hndle"><span><?php echo $users; ?></span></h3>
      <div class="inside"> 
        <div id="lazyest-admins" class="misc-pub-section">
          <p><?php esc_html_e( 'Administrators'); ?>: <strong><?php echo implode( ', ', $admins ) ?></strong></p>
        </div>
        <div id="lazyest-editors" class="misc-pub-section misc-pub-section-last">
          <p><?php esc_html_e( 'Editors', 'lazyest-gallery' ); ?>: <span id="list-editors" class="users-list"><?php echo implode( ', ', $editors ); ?></span></p><p><a id="add-remove-editor" class="button-secondary" href="<?php echo add_query_arg( 'edit', 'editors') ?>"><?php echo $addremove; ?></a></p>           
          <div id="edit_editors" style="<?php echo $editorstyle; ?>">
            <div id="not-editor" class="has_role">
              <p><strong><?php echo $users ?></strong></p>
              <select class="multiple" id="not-editors" name="not-editors[]" multiple="multiple" size="5">
                <?php if ( isset( $eoptions['not'] ) ) { echo implode( $eoptions['not'] ); }  ?>                             
              </select>
              <p class="authorbutton"><input class="button-secondary" id="add-editor" name="add-editor" type="submit" value="<?php echo $add ?>" /> <img alt="" id="editor-ajax-loading" src="<?php echo admin_url('images/wpspin_light.gif') ?>" class="ajax-loading" /></p>      
            </div>
            <div id="is-editor" class="has_role">
              <p><strong><?php esc_html_e( 'Editors', 'lazyest-gallery' ); ?></strong></p>
              <select class="multiple" id="is-editors" name="is-editors[]" multiple="multiple" size="5">
                <?php if ( isset( $eoptions['has'] ) ) { echo implode( $eoptions['has'] ); }  ?>                             
              </select>      
              <p class="authorbutton"><input class="button-secondary" id="remove-editor" name="remove-editor" type="submit" value="<?php echo $remove ?>" /></p>
            </div>
          <div class="clear"></div> 
          </div>
        </div>
        <div id="lazyest-authors" class="misc-pub-section misc-pub-section-last">
          <p><?php esc_html_e( 'Authors', 'lazyest-gallery' ); ?>: <span id="list-authors" class="users-list"><?php echo implode( ', ', $authors ); ?></span></p>
          <p><a id="add-remove-author" class="button-secondary" href="<?php echo add_query_arg( 'edit', 'authors') ?>"><?php esc_html_e( $addremove ); ?></a></p>           
          <div id="edit_authors" style="<?php echo $authorstyle; ?>">
            <div id="not-author" class="has_role">
              <p><strong><?php echo $users ?></strong></p>
              <select class="multiple" id="not-authors" name="not-authors[]" multiple="multiple" size="5">
                <?php if ( isset( $aoptions['not'] ) ) { echo implode( $aoptions['not'] ); }  ?>                             
              </select>
              <p class="authorbutton"><input class="button-secondary" id="add-author" name="add-author" type="submit" value="<?php esc_html_e( $add ) ?>" /> <img alt="" id="author-ajax-loading" src="<?php echo admin_url('images/wpspin_light.gif') ?>" class="ajax-loading" /></p>      
            </div>
            <div id="is-author" class="has_role">
              <p><strong><?php esc_html_e( 'Authors', 'lazyest-gallery' ); ?></strong></p>
              <select class="multiple" id="is-authors" name="is-authors[]" multiple="multiple" size="5">
                <?php if ( isset( $aoptions['has'] ) ) { echo implode( $aoptions['has'] ); }  ?>                             
              </select>      
              <p class="authorbutton"><input class="button-secondary" id="remove-author" name="remove-author" type="submit" value="<?php esc_html_e( $remove ) ?>" /></p>
            </div>
            <div class="clear"></div>   
          </div>                     
        </div>  
      </div>
    </div>
    <?php
  }
  
  /**
   * LazyestAdmin::viewerbox()
   * Show box to set minimum level to view the gallery
   * 
   * @since 1.1.0
   * @uses WP current_user_can()
   * @return
   */
  function viewerbox() {
    if ( ! ( current_user_can( 'manage_lazyest_files' ) || current_user_can( 'manage_options') ) )
      return;
    if ( '' == $this->get_option( 'viewer_level') )
      $this->update_option( 'viewer_level', 'everyone' );      
    ?>
    <div class="postbox" id="viewerdiv">
      <h3 class="hndle"><span><?php esc_html_e( 'Viewers', 'lazyest-gallery' ); ?></span></h3>
      <div class="inside" id="check_roles">        
        <div class="misc-pub-section misc-pub-section-last" id="roles_div">
        <p><strong><?php esc_html_e( 'Minimum level to view the gallery', 'lazyest-gallery' ); ?></strong><br /></p>
          <label><input type="radio" name="viewer_level" value="editor" <?php checked( 'editor', $this->get_option( 'viewer_level') ); ?> /> <?php esc_html_e( 'Editor' ) ?></label><br />
          <label><input type="radio" name="viewer_level" value="author" <?php checked( 'author', $this->get_option( 'viewer_level') ); ?> /> <?php esc_html_e( 'Author' ) ?></label><br />          
          <label><input type="radio" name="viewer_level" value="contributor" <?php checked( 'contributor', $this->get_option( 'viewer_level') ); ?> /> <?php esc_html_e( 'Contributor' ) ?></label><br />          
          <label><input type="radio" name="viewer_level" value="subscriber" <?php checked( 'subscriber', $this->get_option( 'viewer_level') ); ?> /> <?php esc_html_e( 'Subscriber' ) ?></label><br />
          <label title="<?php esc_html_e( 'Viewer does not have to log on to your blog.', 'lazyest-gallery' ) ?>"><input title="<?php esc_html_e( 'Viewer does not have to log on to your blog.', 'lazyest-gallery' ) ?>" type="radio" name="viewer_level" value="everyone" <?php checked( 'everyone', $this->get_option( 'viewer_level') ); ?> /> <?php esc_html_e( 'All visitors' ) ?></label><br /><br />
          <p><input type="submit" class="button-secondary" id="set_viewer_level" name="set_viewer_level" value="<?php esc_html_e( 'Update level', 'lazyest-gallery'); ?>" /> <img alt="" id="viewer-ajax-loading" src="<?php echo admin_url('images/wpspin_light.gif') ?>" class="ajax-loading" /></p> 
        </p>
        </div>
      </div>
    </div>
    <?php
  }
  
  /*
   * Section: other admin screens for lazyest-gallery
   */
  
  /**
   * LazyestAdmin::filemanager_page()
   * Create the Lazyest Gallery Management pages
   * 
   * @return
   */
  function filemanager_page() {
    if ( ! $this->user_can_browse() ) {      
      wp_die( esc_html__('You do not have permission to browse the gallery.', 'lazyest-gallery' ) );
    }
   	if ( isset($_GET['edit'] ) && ($_GET['edit'] == 'comments') ) {
      $this->commentor->edit_comments_form();
      return;
		}
    $folderdir = '';
    if ( isset( $_GET['folder'] ) ) $folderdir = utf8_decode( stripslashes( rawurldecode( $_GET['folder'] ) ) );
    if ( '' == $folderdir ) {
      if ( isset( $_GET['delete_folder'] ) ) {
        $this->delete_folder();
      }
      if( isset( $_POST['create_new_folder'] ) ) { 
        $foldername = $_POST['new_folder_name'];
        $this->new_gallery_folder( $foldername );       
      }
      if( isset( $_POST['add-author'] ) || isset( $_POST['add-editor'] ) ) {
        $this->add_users();
      }      
      if( isset( $_POST['remove-author'] ) || isset( $_POST['remove-editor'] ) ) {
        $this->remove_users();
      }
      if( isset( $_POST['set_viewer_level'] ) ) {
        $this->set_viewer_level();
      }
      $this->manage();
      return;
    }  
    include_once( $this->plugin_dir . '/inc/manager.php' ); 
    $folder = new LazyestAdminFolder( $folderdir );    
    if ( $folder->valid() && $folder->user_can( 'viewer' ) ) {      
      $folder->manage();   
    } else {
    	/* translators: %1s: <strong>, %2s: folder %3s: </strong> */
      $this->message = sprintf( esc_html__( 'Lazyest Gallery cannot open folder %1s%2s%3s', 'lazyest-gallery' ), '<strong>', esc_html( $_GET['folder'] ), '</strong>' ) ;
      $this->success = false;
      $_SERVER['REQUEST_URI'] = remove_query_arg( 'folder', $_SERVER['REQUEST_URI'] );
      $this->manage(); 
    }
    unset( $folder );
  }  
  
  /**
   * LazyestAdmin::allcomments_page()
   * Display the main comments screen
   * 
   * @since 1.0
   * @return void
   */
  function allcomments_page() {
    $this->commentor->edit_comments_form( 'all' );
  }
   
  /**
   * LazyestAdmin::themes_page()
   * calls the manage lazyest themes page
   * @since 1.1.0
   * @return void
   */
  function themes_page() {  
    require_once ( $this->plugin_dir . '/inc/themes.php' );
    $themes = new LazyestThemes();
    $themes->themes_page();  
    unset( $themes );
  }
  
  /**
   * LazyestAdmin::wizard_form()
   * Display the form to input windows xp wizard fields
   * 
   * @deprecated as of 1.2
   * @return void
   */
  function wizard_form() {
    if ( ! current_user_can( 'manage_options' ) ) {
      $this->message = esc_html__('Only Blog administrators can set external upload options', 'lazyest-gallery' );
      $this->success = false;
      $this->settings_page();
    }
  	if ( isset( $_POST['update_wizard_options'])){
  		$user_ok = false;
  		$passwd_ok = false;
      $message = '';
  
  		if ( isset( $_POST['wizard_username'] ) && ( 0 != strlen( $_POST['wizard_username'] ) ) ) {
  			$this->update_option('wizard_user', $_POST['wizard_username'] );
  			$user_ok = true;
  		} else {
  		  $message = esc_html__( 'You have to provide a username! ', 'lazyest-gallery' );
  		}
  
  		if ( isset( $_POST['wizard_password'] ) && ( 0 != strlen( $_POST['wizard_password'] ) ) ){
  			$this->update_option( 'wizard_password', base64_encode( $_POST['wizard_password'] ) );
  			$passwd_ok = true;
  		} else {
  		  $message .=  esc_html__( ' You have to provide a password! ', 'lazyest-gallery' );		
  		}
  
      $success = $passwd_ok && $user_ok;
      if ( $success ) {
        $this->update_option( 'enable_mwp_support', TRUE );
      }
      $this->message = ( $success ) ? esc_html__( 'Wizard Settings successfully edited', 'lazyest-gallery' ) : $message;
      $this->success = $success;      
		}
    ?>
  	<div class="wrap">  
  	<fieldset class="options">
  			<h2><?php esc_html_e( 'Microsoft Publisher Wizard Options', 'lazyest-gallery') ?></h2>
        <?php $this->options_message(); ?>
  			<?php if ( ( 'test' == $this->get_option( 'wizard_user' ) ) || ( 'secret' == $this->get_option( 'wizard_password' ) ) ) { ?>
  			<div id="message" class='error fade'> 
  				<b><?php esc_html_e( 'It is highly recomended to change your Username and Password! ', 'lazyest-gallery'); ?></b>
  				<p><?php esc_html_e( 'Default Username and Passwords are:', 'lazyest-gallery'); ?></p>
  					<ul>
  						<li><?php esc_html_e( 'Username:', 'lazyest-gallery'); ?> <code>test</code></li>
  						<li><?php esc_html_e( 'Password:', 'lazyest-gallery'); ?> <code>secret</code></li>
  					</ul>
  			</div>
  		<?php } ?>
  
  		<form method="post" enctype="multipart/form-data" action="" >
  			<table summary="wizard">
  				<tr>
  					<th><label for="wizard_username"><?php esc_html_e( 'Username:', 'lazyest-gallery'); ?></label></th><td><input type="text" name="wizard_username" id="wizard_username" value="<?php echo $this->get_option('wizard_user'); ?>"  size="25" class="code" /></td>
  				</tr>
  				<tr>
  					<th><label for="wizard_password"><?php esc_html_e( 'Password:', 'lazyest-gallery'); ?></label></th><td><input type="password" name="wizard_password" id="wizard_password" value="<?php echo $this->get_option('wizard_password'); ?>"  size="25" class="code" /></td>
  				</tr>
  				<tr>
  					<td style="vertical-align:middle"><?php esc_html_e( 'Download Registry File:', 'lazyest-gallery'); ?> &raquo;</td>
  					<td style="text-align:center;">
  						<a href="<?php echo $this->plugin_url; ?>/lazyest-wizard.php?step=reg">
  							<img src="<?php echo $this->plugin_url; ?>/images/reg.jpg" alt="Windows Registry File" />
  						</a>
  					</td>
  				</tr>
  			</table>
  			<input class="button" type="submit" name="update_wizard_options" value="<?php	esc_html_e( 'Update options', 'lazyest-gallery')	?>" />
  		</form>
  	</fieldset>
  </div>
  <?php    
  }
  
  /* Settings page functions */
  
  /**
   * LazyestAdmin::_preserve()
   * 
   * @since 1.1.0
   * @internal
   * @param array $options
   * @param string $option
   * @return void
   */
  function _preserve( &$options, $option = '' ) {
    if ( '' == $option ) return;
    $options[$option] = $this->get_option( $option );
  }
  
  /**
   * LazyestAdmin::update()
   * Check and Update settings
   * 
   * @param mixed $options
   * @return
   */
  function update( $options ) {
		if ( isset ( $options['delete'] ) && $options['delete'] == 'true' ) {
			delete_option ( 'lazyest-gallery' );
		} else if ( isset ( $options['default'] ) && $options['default'] == 'true' ) {
			return $this->defaults ();
		} else {
		  // preserve options not displayed on the admin screen
      $this->_preserve( $options, 'wizard_user');
      $this->_preserve( $options, 'wizard_password');
      $this->_preserve( $options, 'image_indexing');
      $this->_preserve( $options, 'gallery_secure');
      $this->_preserve( $options, 'style_css');
      $this->_preserve( $options, 'theme_javascript');
      $this->_preserve( $options, 'viewer_level');
      
		  // add trailing slash for directories
      if ( isset( $options['gallery_folder'] ) ) {
		    $options['gallery_folder'] =  rtrim( trim( $options['gallery_folder'] ), '/\\' ) . DIRECTORY_SEPARATOR;
		  }
     
      $options = $this->check_safety( $options );
      if ( isset( $options['gallery_prev'] ) ) {
		    $options['gallery_prev'] = trailingslashit( $options['gallery_prev'] );
		  }      
      if ( isset( $options['thumb_folder'] ) ) {
        $options['thumb_folder'] = trailingslashit( $options['thumb_folder'] );
      } 
      if ( isset( $options['slide_folder'] ) ) {
        $options['slide_folder'] = trailingslashit( $options['slide_folder'] );
      }
      
      // clear cache when thumb or slide dimensions have been changed
      if ( isset( $options['thumbheight'] ) && ( $options['thumbheight'] != $this->get_option( 'thumbheight' ) ) )
        $this->clear_cache( 'thumbs' );
      if ( isset( $options['thumbwidth'] ) && ( $options['thumbwidth'] != $this->get_option( 'thumbwidth' ) ) )
        $this->clear_cache( 'thumbs' );
      if ( isset( $options['pictheight'] ) && ( $options['pictheight'] != $this->get_option( 'pictheight' ) ) )        
        $this->clear_cache( 'slides' );
      if ( isset( $options['pictwidth'] ) && ( $options['pictwidth'] != $this->get_option( 'pictwidth' ) ) )        
        $this->clear_cache( 'slides' );
      
      // clear cache when cache has been disabled       
      if ( ! isset( $options['enable_cache'] ) && ( 'TRUE' == $this->get_option( 'enable_cache') ) )  {      
        $this->clear_cache( 'thumbs' );
			}
      if ( ! isset( $options[ 'enable_slides_cache' ] ) && ( 'TRUE' == $this->get_option( 'enable_slides_cache') ) )
        $this->clear_cache( 'slides' );       
      
			if ( ! isset( $options['enable_cache'] ) )			
        unset( $options['async_cache'] );
      
      // clear cache when cropping has been enabled or disabled
      if ( isset( $options['use_cropping'] ) && ( 'TRUE' != $this->get_option( 'use_cropping' ) ) )
        $this->clear_cache( 'thumbs' );
      if ( ! isset( $options['use_cropping'] ) && ( 'TRUE' == $this->get_option( 'use_cropping' ) ) )
        $this->clear_cache( 'thumbs' );
      
      // always exclude thumbs and slides folders
      if( isset( $options['excluded_folders_string'] ) ) {
		    $excludefolder = 	explode( ',', $options['excluded_folders_string'] );
		    if( isset( $options['thumb_folder'] ) ) {
          $thumbfolder = untrailingslashit( $options['thumb_folder'] );
		    }
        $thumbfolder_found = false;
    		for ( $i=0; $i < sizeof( $excludefolder ); $i++ ) {
    			$excludefolder[$i] = untrailingslashit( $excludefolder[$i] );
			    if ( $excludefolder[$i] == $thumbfolder ) {
		        $thumbfolder_found = true;
			    }				      
		    }
		    if( ! $thumbfolder_found ) {
          $excludefolder[]= $thumbfolder;
        }
		    $options['excluded_folders'] = $excludefolder;
        unset( $options['excluded_folders_string'] );
      } 
              
			unset ( $options['delete'] , $options['default'], $options['update_options'] );
			return apply_filters( 'lazyest_update_options', $options );
		}
	}
  
  /**
   * LazyestAdmin::settings_page()
   * Lazyest Gallery Settings page
   * 
   * @return void
   */   
  function settings_page() { 
    require_once( path_join( $this->plugin_dir, 'inc/settings.php' ) );
    $settings_page = new LazyestSettings();
    $settings_page->display();
  }
  
  /**
   * Section misc lazyest-gallery admin functions
   */
  
  /**
   * LazyestAdmin::save_changed_folders()
   * Sort subfolders after user submit
   * 
   * @return void
   */
  function save_changed_folders() {
    $folders = $this->folders( 'root', 'hidden' );
    if ( isset( $_REQUEST['lg_paged'] ) ) {
      $perpage  = 20;            
      $total_pages = ceil( count( $folders ) / $perpage ); 
      $current = isset( $_REQUEST['lg_paged'] ) ? absint( $_REQUEST['lg_paged'] ) : 0;	
    	$current = min( max( 1, $current ), $total_pages );
      $start = ( $current - 1 ) * $perpage + 1;
      $end = min( count( $folders ), $current * $perpage);  
    } else {
      $start = 1;
      $end = count( $folders );
    } 
    for ($i = $start -1; $i < $end; $i++) {      
      $folder = $folders[$i]; 
  		$folder_id =  $folder->form_name();                               
      $folder->order = isset( $_POST['index'][$folder_id] ) ? $_POST['index'][$folder_id] : 0;      
      $success = $folder->change();
      if ( ! $success ) {
        break;
      }
  	}
    if ( isset( $_POST['viewer_level'] ) )
      $this->update_option( 'viewer_level', $_POST['viewer_level'] );
    $this->message = ( $success ) ? esc_html__( 'Changes saved to the Gallery. Continue editing below', 'lazyest-gallery' ) : esc_html__( 'Lazyest Gallery could not save your folders.', 'lazyest-gallery' ); 
    $this->success = $success; 	      
  }
  
  /**
   * LazyestAdmin::delete_folder()
   * Delete a gallery folder and all its subdirectories 
   * 
   * @return void
   */
  function delete_folder() {
    $nonce=$_REQUEST['_wpnonce'];    
    if ( ! wp_verify_nonce( $nonce, 'lg_delete_folder' ) ) wp_die( esc_html__( 'You are not allowed to delete Lazyest Gallery folders', 'lazyest-gallery' ) );
    $path = $_GET['delete_folder'];
    $folder = new LazyestFolder( $path );
    $success = $folder->valid();
    if ( $success ) {
      $success = $this->remove_directory( $this->root . $path );
    }
    unset( $folder ); 
    $this->message = ( $success ) ? esc_html__( 'Folder deleted successfully', 'lazyest-gallery' ) : esc_html__( 'Lazyest Gallery could not delete the folder', 'lazyest-gallery' ); 
    $this->success = $success; 	      
  }
    
  /**
   * LazyestAdmin::remove_directory()
   * Removes a directory in the gallery file system
   * 
   * @param string $path
   * @return bool
   */
  function remove_directory( $path ) {
    if ( ! file_exists( $path ) ) {
      return true; // don't attempt to remove a non-existant directory
    }
  	if( $this->clear_directory( $path ) ) {
  		if( rmdir( $path ) ){
  			return true;
  		// directory removed
  		} else {
  			return false;
  		// directory couldn't removed
  		}
  	} else {
  		return false;
  	// no empty directory
  	}
  }
  
  /**
   * LazyestAdmin::clear_directory()
   * 
   * @param mixed $path
   * @return
   */
  function clear_directory( $path ) {
    if ( ! file_exists( $path ) ) {
      return true; // don't attempt to remove a non-existant directory
    }  
  	if( $dir_handle = @opendir( $path ) ) {
  		while( false !== ( $file = readdir( $dir_handle ) ) ) {
  			if( is_dir( $path . $file ) && $file != "." && $file != ".." ) {				
  				$filevar = substr( $path, strlen( $this->root ) );
  				if ( $this->is_folder( $filevar ) && isset( $this->commentor ) ) { 
  					$this->commentor->remove_comments( $filevar );
  				}
  				$this->clear_directory( trailingslashit( $path .  $file ) );
  				$this->remove_directory( trailingslashit( $path .  $file ) );
  			} else {
  				if($file == "." || $file == "..") {
  					continue;
  				} else {
  					unlink( $path . $file );
  				}
  			}
  		}
  		@closedir( $dir_handle );
  		return true;
  	// all files deleted
  	} else {
  		return false;
  	}
  }
  
  /**
   * LazyestAdmin::rename_file()
   * 
   * @param string $oldname
   * @param string $newname
   * @return bool
   */
  function rename_file( $oldname, $newname ) {
  	if ( @rename( $oldname, $newname ) ) {
  		return true;
  	} else {
  		return false;
  	}
  }
  
  /**
   * LazyestAdmin::make_directory()
   * Make a new directory and copy permissions from parent directory
   * 
   * @param string $path
   * @return bool
   */
  function make_directory( $path ) {
  	return wp_mkdir_p( $path );
  }
  
   
  /**
   * LazyestAdmin::admin_clear_cache()
   * Clear cache after user submit
   * 
   * @return void
   */
  function admin_clear_cache() {
    $cache_cleared = $this->clear_cache( 'all' );
    $this->message = ( $cache_cleared ) ? esc_html__( 'Cache cleared succesfully', 'lazyest-gallery') :  esc_html__( 'Lazyest Gallery cannot clear the cache: maybe it does not exist or has bad permissions', 'lazyest-gallery' );
    $this->success = $cache_cleared;  
  }
  
  /**
   * LazyestAdmin::admin_replete_cache()
   * Rebuilds the cache after user submit
   * Deprecated replaced by ajax function
   * 
   * @deprecated 1.1 
   * @return void
   */
  function admin_replete_cache() {
    _deprecated_function(__FUNCTION__, '1.1' );
  }
  
  /**
   * LazyestAdmin::clear_cache()
   * Removes thumbs and slides and their directories
   * 
   * @param string $what thumbs or slides cache or both
   * @return
   */
  function clear_cache( $what='all' ) {
    $thumbs = $this->get_option( 'thumb_folder' );
    $slides = $this->get_option( 'slide_folder' );
    // Cache cannot be cleared when thumbs or slides directory has not been set. We don't want to delete image folders
    if ( ( false === $thumbs ) || ( false === $slides ) || ( '' == $thumbs ) || ( '' == $slides ) )
      return false;    
  	$folders = $this->folders( 'subfolders', 'hidden');
    $success = true; 
  	if ( 0 != count( $folders ) ) { 
  	 	for ( $i = 0; $i != count( $folders ); $i++ ) {
        $folder =  $folders[$i];
  	 	  $path = $this->root . $folder->curdir;
        if ( ( 'all' == $what ) || ( 'thumbs' == $what ) ) {
          if ( file_exists( $path . $thumbs ) ) {
            if ( $this->remove_directory( $path . $thumbs ) == false )
              $success =  false;
          }
        }      
        if ( (  'all' == $what ) || ( 'slides' == $what ) ) {
          if ( file_exists( $path . $slides) ) {
            if ( $this->remove_directory( $path . $slides ) == false ) 
              $success = false;
          }
        }  	 	 
   	  }
    }  
    return $success;
  }
  
  /**
   * LazyestAdmin::replete_cache()
   * Deprecated replaced by ajax function
   * 
   * @deprecated 1.1 
   * @return bool
   */
  function replete_cache() {    
    _deprecated_function(__FUNCTION__, '1.1' );
    return $false;
  }
    
  /**
   * LazyestAdmin::check_author_roles()
   * @deprecated 1.1
   * @return void
   */
  function check_author_roles() {
    _deprecated_function(__FUNCTION__, '1.1' );
    return false;
  }
  
  /**
   * LazyestAdmin::rebuild_cache()
   * Creates thumbs and slides per folder
   * If thumb/slide exists, don't rebuild  
   * 
   * @param int $i key of folder in _build_folders_array() 
   * @return int next key
   */
  function rebuild_cache( $fcount, $icount = 0 ) {
    $cache_thumbs = ( 'TRUE' == $this->get_option( 'enable_cache' ) );
    $cache_slides = ( 'TRUE' == $this->get_option( 'enable_slides_cache' ) );
    $folder_array = get_transient( 'lg_rebuild_cache_folders' );
    if ( false === $folder_array ) {    
      $folder_array = $this->_build_folders_array();
      set_transient(  'lg_rebuild_cache_folders', $folder_array, 300 );  
    }
    $nfcount = ( $fcount == 0 ) ? count( $folder_array ) : $fcount;
    if ( $nfcount < count( $folder_array ) + 1 ) {
      $file = substr( $folder_array[$nfcount-1], strlen( $this->root ) );
      $folder = $folder = new LazyestFolder( $file );
      if ( $folder->valid() ) {
      	$folder->load();      	
      	$i = 0;
      	while ( $icount < count( $folder->list ) && $i < 10 ) {
      		$image = $folder->list[$icount];
      		if ( $cache_thumbs ) {      			
	      		$thumb = new LazyestThumb( $folder );
	      		$thumb->image = $image->image;
						$thumb->cache();
						unset( $thumb ); 	
      		}
      		if ( $cache_slides ) {
      			$slide = new LazyestSlide( $folder );
      			$slide->image = $image->image;
      			$slide->cache();
      			unset( $slide );
      		}
      		$i++;
      		$icount++;
      	}
      	if ( $icount == count( $folder->list ) ) {
      		$icount = 0;
      		$nfcount = $nfcount - 1;
      	} 
      		
      }
    } else {
    	$nfcount = 0;
    }
    if ( $nfcount == 0 ) 
      delete_transient( 'lg_rebuild_cache_folders' );
    return array( 'folder' => $nfcount, 'image' => $icount );
  }
  
  function truncate_table() {    
    global $wpdb;
    $nonce = $_REQUEST['_wpnonce'];
    $result = wp_verify_nonce( $nonce, 'settings' );
    if ( $result ) {
      $query = "TRUNCATE TABLE $this->table";
      $result = $wpdb->query( $query );  
      $result = ( false !== $result );    
    } 
    return $result;    
  }
  
  /**
   * LazyestAdmin::rebuild_database()
   * Rebuild the lazyestfiles table per folder
   * 
   * @since 1.1.0
   * @uses $wpdb, get_transient, set_transient, delete_transient
   * @param int $i key of folder in _build_folders_array()
   * @return int next key 
   */
  function rebuild_database( $i ) {
    global $wpdb;
    $folder_array = get_transient( 'lg_rebuild_database_folders' );
    if ( false === $folder_array ) {    
      $folder_array = $this->_build_folders_array();
      set_transient(  'lg_rebuild_database_folders', $folder_array, 300 );  
    }
    $j = ( $i == 0 ) ? count( $folder_array ) : $i;
    $insert = '';
    if ( $j < count( $folder_array ) ) {      
      $file = substr( $folder_array[$j-1], strlen( $this->root ) );
      $folder = $folder = new LazyestFolder( $file );
      $folder->open();      
      $imgID = $folder->id;
      $file = rawurlencode( $folder->curdir ); 
      $into = "INSERT INTO $this->table ( img_ID, file ) VALUES \n";
      $insert = $into . "( $imgID, \"$file\" ),";  
      $folder->load();
      $lines = 0;
      if ( 0 < count( $folder->list ) ) {
        $lines = 1;
        foreach( $folder->list as $image ) {
          $imgID = $image->id;
          $file = rawurlencode( $folder->curdir . $image->image );      
          $insert .= "\n( $imgID, \"$file\" ),";  
          $lines++;
          if ( $lines == 64 ) {
            $insert = trim( $insert, ',' ) . ';';
            $wpdb->query( $insert );
            $insert = $into;
            $lines = 0;
          }
        }         
      }
      if ( $lines = 0 ) {
        $insert = '';
      }
      if ( '' != $insert ) {
        $insert = trim( $insert, ',' ) . ';';
        $wpdb->query( $insert );  
      } 
    }
    if ( $j == 1 ) 
      delete_transient( 'lg_rebuild_cache_folders' );
    return $j - 1;
  }
  
  /**
   * LazyestAdmin::options_message()
   * display a box with a message on the admin page
   * 
   * @return void
   */
  function options_message() {
    if ( ! isset( $this->message ) )
      return;
    $class = $this->success ? 'updated' : 'error';
    echo sprintf( "<div id=\"message\" class=\"%s\"><p>%s</p></div>\n", $class, $this->message );  	
    unset( $this->message );
  }
    
  /**
   * LazyestAdmin::create_gallery_folder()
   * Creates a new directory to hold the Gallery
   * 
   * @param string $foldername
   * @return void
   */
  function create_gallery_folder( $foldername ) {   
    $newpath = path_join( ABSPATH , $foldername );
    $directory_made = $this->make_directory( $newpath );
    $this->message = ( $directory_made ) ? esc_html__('Folder created successfully', 'lazyest-gallery') : esc_html__( 'Lazyest Gallery cannot create folder: maybe it already exists or have bad permissions', 'lazyest-gallery' );
    $this->success = $directory_made; 
  }
  
  /**
   * LazyestAdmin::new_gallery_folder()
   * Creates a new folder in the Gallery
   * 
   * @param string $foldername
   * @return void
   */
  function new_gallery_folder( $foldername = '' ) {
    $currentfolder = '';
    if ( isset( $_POST['folder'] ) ) {
      $currentfolder = trailingslashit( urldecode( $_POST['folder'] ) );
    }       
    $newname = substr( $foldername, strlen( $currentfolder ) );
    if ( '' != $newname ) {
      $sanitized_name = preg_replace('/[^0-9a-z\.\_\-\(\)\$ ]/i','', $newname);
			if ( $sanitized_name == $newname ) {
				$new_folder_path = $this->root . $foldername;
        if ( file_exists( $new_folder_path ) ) {
          $success = false;
          $message = sprintf( esc_html__( 'Folder %s already exists', 'lazyest-gallery' ), $newname );
        } else {         
          $success = $this->make_directory( $new_folder_path ); 
    			$message = ( $success ) ? sprintf( esc_html__( 'Folder %s created successfully', 'lazyest-gallery' ), $newname ) : sprintf( esc_html__( 'Lazyest Gallery cannot create folder %s. Please check your server permissions', 'lazyest-gallery' ), $newname ); 
        }				 
			} else {
        $success = false;   
				                                 /* translators: 1: <strong>, 2: folder name, 3: </strong> */  
				$message =  sprintf( esc_html__( 'Lazyest Gallery cannot use %1s%2s%3s as foldername. Please do not use characters like \\ / : * ? & " < > | %% ', 'lazyest-gallery' ),
					'<strong>',
					esc_html( $newname ),
					'</strong>' 
				);
			}
		} else {
		  $success = false;
			$message = esc_html__( 'Lazyest Gallery cannot create a folder with no name.', 'lazyest-gallery');
		}
    if ( defined( 'DOING_AJAX' ) ) {
      return ( $success ) ? $success : $message;
    } else {
      $this->message = $message;
      $this->success = $success;
    }           
  }
  
  /**
   * LazyestAdmin::insert_shortcode()
   * Inserts the Gallery code in a WordPress page
   * 
   * @param mixed $page_id
   * @return void
   */
  function insert_shortcode( $page_id ) {
    global $lg_gallery;
    $apage = get_page( $page_id );
    $apage->post_content .= '[lg_gallery]';
    wp_update_post( $apage );
    $lg_gallery->update_option( 'gallery_id', $page_id );
    $gallery_prev = ( strlen( $permalink ) != 0 ) ? get_option( 'home' ) . '/' . get_page_uri( $page_ID ) . '/' : get_option( 'home' ) . "?page_id=" . $page_ID;
    $lg_gallery->update_option( 'gallery_prev', $gallery_prev );
  }
  
  /**
   * LazyestAdmin::some_lightbox_plugin()
   * Check if a Lightbox plugin is installed
   * 
   * @return bool
   */
  function some_lightbox_plugin() {
  	return (
      ( get_option( 'lightboxplus_options' ) != false ) ||
  		( class_exists( 'jQueryLightbox' ) ) ||
  		( get_option( 'lightbox_2_automate' ) != false ) ||
  		( get_option( 'lightbox_conditionals' ) != false ) ||
  		( get_option( 'shadowbox' ) != false )
  	);
  }
  
  /**
   * LazyestAdmin::some_thickbox_plugin()
   * Check if a Thickbox plugin is installed
   * 
   * @return bool
   */
  function some_thickbox_plugin() {
  	return (
  		function_exists('ThickBox_init') ||
  		function_exists('add_thickbox_js') ||
      ( get_option('thickbox_variant') != false ) ||
  		( get_option( 'shadowbox' ) != false )
  		);
  }
  
  /**
   * LazyestAdmin::add_user()
   * Adds a lazyest- role to a single user
   * 
   * @since 1.1.0
   * @param int $user_id
   * @param string $type user role to add
   * @return void
   */
  function add_user( $user_id, $type='none' ) {
    $nonce = $_REQUEST['_wpnonce']; 
    $settings = isset( $_REQUEST['lg_settings'] ) ? $_REQUEST['lg_settings'] : 0;
    $from_settings = wp_verify_nonce( $settings, 'settings' );
    $from_admin = wp_verify_nonce( $nonce, 'lg_manage_gallery' );
    if ( $from_admin || $from_settings ) {
      if ( 'none' == $type ) return;
        $user = new WP_User( $user_id );
        $user->add_role( "lazyest_$type" );           
        unset( $user );  
    } else {
      wp_die( esc_html__( 'You are not allowed to add users to Lazyest Gallery', 'lazyest-gallery' ) );
    }
        
  }
    
  /**
   * LazyestAdmin::remove_user()
   * 
   * @since 1.1.0
   * @param int $user_id
   * @param string $type user role to remove
   * @return void
   */
  function remove_user( $user_id, $type='none' ) {
    $nonce = $_REQUEST['_wpnonce']; 
    $lg_settings = isset( $_REQUEST['lg_settings'] ) ? $_REQUEST['lg_settings'] : 0;
    $from_settings = wp_verify_nonce( $lg_settings, 'settings' );
    $from_admin = wp_verify_nonce( $nonce, 'lg_manage_gallery' );    
    if ( $from_admin || $from_settings ) {
      if ( 'none' == $type ) return;
      $user = new WP_User( $user_id );
      $user->remove_role( "lazyest_$type" );
      switch ( $type ) {
        case 'author' : 
          $user->remove_cap( 'edit_lazyest_fields' );        
          $user->remove_cap( 'lazyest_author' );
        case 'editor' :
          $user->remove_cap( 'upload_lazyest_files' );
          $user->remove_cap( 'create_lazyest_folder');
          $user->remove_cap( 'lazyest_editor');
          break;
        case 'manager' :  
          $user->remove_cap( 'manage_lazyest_files' );
          $user->remove_cap( 'lazyest_manager' );
        default: 
          break; 
      }
      unset( $user );    
    } else {      
      wp_die( esc_html__( 'You are not allowed to remove users from Lazyest Gallery', 'lazyest-gallery' ) );
    }
  }
  
  /**
   * LazyestAdmin::add_user()
   * Add author(s) or editor(s) to the gallery 
   * 
   * $since 1.1.0
   * @return void
   */
  function add_users() { 
    if ( isset( $_POST['add-author'] ) ) {
      $users = $_POST['not-authors'];
      foreach( $users as $user_id ) {
        $this->add_user( $user_id, 'author' );          
      }
      $_REQUEST['edit'] = 'authors';
    } 
    if ( isset( $_POST['add-editor'] ) ) {
      $users = $_POST['not-editors'];
      foreach( $users as $user_id ) {
        $this->add_user( $user_id, 'editor' );
      }      
      $_REQUEST['edit'] = 'editors';
    }
  }
  
  /**
   * LazyestAdmin::remove_users()
   * Remove author(s) or editor(s) from the gallery
   * 
   * @since 1.1.0
   * @return void
   */
  function remove_users() { 
    if ( isset( $_POST['remove-author'] ) ) {
      $users = $_POST['is-authors'];
      foreach( $users as $user_id ) {
        $this->remove_user( $user_id, 'author' );  
      }
      $_REQUEST['edit'] = 'authors';
    } 
    if ( isset( $_POST['remove-editor'] ) ) {
      $users = $_POST['is-editors'];
      foreach( $users as $user_id ) {
        $this->remove_user( $user_id, 'editor' );
      }      
      $_REQUEST['edit'] = 'editors';
    }  
  }
  
  /**
   * LazyestAdmin::set_viewer_level()
   * Sets the minimum user level to view the Gallery 
   * 
   * @since 1.1.0
   * @return void
   */
  function set_viewer_level() {
    $nonce = $_REQUEST['_wpnonce'];
    if ( ! wp_verify_nonce( $nonce, 'lg_manage_gallery' ) ) 
      wp_die( esc_html__( 'You are not allowed to change viewer levels in Lazyest Gallery', 'lazyest-gallery' ) );
    if ( isset( $_POST['viewer_level'] ) ) {
      $this->update_option( 'viewer_level', $_POST['viewer_level'] );
    }
    $this->message = 'Viewer level updated successfully';
    $this->success = true;
    return true;
  }
  
  /**
   * LazyestAdmin::upload_tabs()
   * Adds an extra tab to the Wordpress Media insert dialog 
   * 
   * @since 1.1.0
   * @param array $tabs
   * @return array
   */
  function upload_tabs( $tabs ) {
  	$tab = array( 'lazyestgallery' => __( 'Lazyest Gallery', 'lazyest-gallery' ) ); 
    return array_merge( $tabs, $tab );
  }
  
  /**
   * LazyestAdmin::upload_lazyestgallery()
   * Create the tab in the iframe
   * 
   * @since 1.1.0
   * @return void 
   */
  function upload_lazyestgallery() {
    wp_iframe( array( &$this, 'media_upload_lazyestgallery' ) );
  }
  
  /**
   * LazyestAdmin::media_upload_lazyestgallery()
   * Handles the user submit
   * Display the upload tab for lazyest gallery
   * 
   * @since 1.1.0
   * @return void
   */
  function media_upload_lazyestgallery() {    
    require_once ( $this->plugin_dir . '/inc/uploadtab.php' );
    $uploadtab = new LazyestUploadTab();
    if ( isset( $_POST['folder_slide'] ) ) {    
      $result = $uploadtab->folder_to_editor( 'slideshow' );
      unset( $uploadtab );
      return $result;
    }
    if ( isset( $_POST['folder_short'] ) ) {    
      $result = $uploadtab->folder_to_editor( 'shortcode');
      unset( $uploadtab );
      return $result;
    }  
    if ( isset( $_POST['image_short'] ) ) {
      $result = $uploadtab->image_to_editor( 'shortcode' );
      unset( $uploadtab );
      return $result;
    }
    $uploadtab->display();
    unset( $uploadtab );
  }
  
  /**
   * LazyestAdmin::editable_roles()
   * 
   * @param array $all_roles
   * @since 1.1.9
   * @return array roles withouit lazyest gallery roles
   */
  function editable_roles( $all_roles ) {  	  	
  	foreach( $all_roles as $key => $role ) {
  		if ( isset( $role['capabilities']['lazyest_author'] ) ) {
  			unset( $all_roles[$key] );
  		}
  	}
  	return $all_roles;
  }
  
} // LazyestAdmin
?>
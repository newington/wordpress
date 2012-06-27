<?php
/**
 * LazyestCommentor
 * 
 * @package Lazyest Gallery  
 * @subpackage Comments
 * @author Marcel Brinkkemper
 * @copyright 2010-2012 Brimosoft
 * @since 0.16.0
 * @access public
 */
class LazyestCommentor {
  
  var $lazyest = '"lazyest"';
  
  /**
   * Holds the gallery item commented on
   * @var string
   */
  var $comments_from;  
  
  /**
   * LazyestCommentor::__construct()
   * object constructor   
   * 
   * @return void
   */
  function __construct() { 
    global $wpdb, $lg_gallery;
    add_action( 'comment_post', array( &$this, 'comment_post' ), 10, 2);
    add_action( 'init', array( &$this, 'redirect_comment' ) );
    add_filter( 'comments_array', array( &$this, 'comments_array' ) );
    add_filter( 'get_comments_number', array( &$this, 'get_comments_number' ), 20, 1 );
    add_filter( 'get_comment_link', array( &$this, 'get_comment_link' ), 10, 3 );
    add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
    if ( ! is_admin() ) { 
      add_filter( 'comments_template', array( &$this, 'comments_template' ) );
      add_filter( 'get_comments_pagenum_link', array( &$this, 'comments_pagenum_link') );
    }
  }
  
  
  /* wordpress filters, actions and basic comment fucntions */
  
  /**
   * LazyestCommentor::comment_post()
   * 
   * @param mixed $comment_ID
   * @param mixed $status
   * @return
   */
  function comment_post( $comment_ID, $status ) {
    global $wpdb, $lg_gallery;
    $status = (int) $status;
    if ( isset( $_POST['lg_comment_image_ID'] ) ) {
      $commentID = (int) $comment_ID;
      $imgID = intval( $_POST['lg_comment_image_ID'] );
      $insert = $wpdb->prepare( "INSERT INTO $wpdb->commentmeta ( comment_id, meta_key, meta_value ) VALUES ( $commentID, 'lazyest', $imgID );" );      
      $results = $wpdb->query( $insert );            
      $file = $_POST['lg_filevar'];     
      $select = $wpdb->prepare( "SELECT file FROM $lg_gallery->table WHERE img_ID = $imgID" );
      $result = $wpdb->get_row( $select, ARRAY_A );
      if ( ! isset( $result ) || ( 0 == count( $result ) ) ) { // be sure to insert the link to find the image on refresh
        $insert =  "INSERT INTO $lg_gallery->table (img_ID, file) VALUES ( $imgID, '$file' )";      
        $wpdb->query( $insert );
      }        
    }
  }
  
  /**
   * LazyestCommentor::redirect_comment()
   * 
   * @return
   */
  function redirect_comment() { 
    if ( is_admin() ) return;
    global $lg_gallery, $lg_comment; 
    if ( isset( $lg_comment ) ) {      
       $lg_gallery->comment = $lg_comment;
    } else {  
     if ( isset( $_GET['lg_comment'] ) ) {
       $lg_gallery->comment = $_GET['lg_comment'];
     }
    }   
    if ( '' !=  ( $lg_gallery->comment ) ) {
      $comment_ID = $lg_gallery->comment;
      $filevar = urldecode( $this->get_file_by_comment_id( $comment_ID ) );
      $redirect = $lg_gallery->address;
      if ( $lg_gallery->is_folder( $filevar ) ) {
        $folder = new LazyestFrontendFolder( $filevar );
        if ( $folder->valid() ) {
          $redirect = $folder->uri();
        }
        unset( $folder );
      }
      if ( $lg_gallery->is_image( $filevar ) ) {
        $folder = new LazyestFrontendFolder( dirname( $filevar ) );
        if ( $folder->valid() ) {
          $slide = $folder->single_image( basename( $filevar), 'slides' );
          $redirect = $slide->uri();
          unset( $slide );
        }
      }     
      wp_redirect( $redirect );
      exit();
    } 
  }

  
  /**
   * LazyestCommentor::get_comments_array()
   * 
   * Get the comments for the gallery, a folder or an image
   * Adds filevar varaiable to comments
   * 
   * @param string $filevar the galler file e.g. myfolder/animage.jpg
   * @return array of comments
   * @since 1.0.36
   */
  function get_comments_array( $filevar = '' ) {
    global $wpdb, $lg_gallery;  

  	$allcomments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1';", $lg_gallery->get_option( 'gallery_id') ) );
			 
  	if ( $lg_gallery->is_image( $filevar ) || $lg_gallery->is_folder( $filevar ) ) { // get the comments for the folder or the file 
      $img_comments = array();
      if ( $lg_gallery->is_image( $filevar ) ) {
        $folder = new LazyestFolder( dirname( $filevar ) );
        if ( $folder->valid() ) {
          $folder->load();
          $image = $folder->single_image( basename( $filevar ) ); 
          $imgID = (int) $image->id;
        }
        unset( $folder );
      } elseif ( $lg_gallery->is_folder($filevar) ) {
        $folder = new LazyestFolder( $filevar );
        if ( $folder->valid() ) {
          $folder->open();        
          $imgID = (int) $folder->id;
        }
        unset( $folder );
      }
      $results = $wpdb->get_results( $wpdb->prepare( "SELECT comment_id FROM $wpdb->commentmeta WHERE meta_key = 'lazyest' AND meta_value = $imgID;" ), ARRAY_A );         
      if ( 0 != count( $results ) ) {
        foreach ( $allcomments as $comment ) {
          if ( in_array( array( 'comment_id' => $comment->comment_ID ), $results ) ) {
          	$comment->filevar = rawurlencode( $filevar );
            $img_comments[] = $comment;
          }
        }
      }
      return $img_comments; // comments on this image
    } else { // the comments for the gallery root
      $gal_comments = array();
      
      $results = $wpdb->get_results( $wpdb->prepare( "SELECT comment_id FROM  $wpdb->commentmeta WHERE meta_key = 'lazyest';" ), ARRAY_A );
      if ( 0 != count( $results ) ) {
        foreach ( $allcomments as $comment ) {
          if ( ! in_array( array( 'comment_id' => $comment->comment_ID ), $results ) ) {
          	$comment->filevar = '/';
            $gal_comments[] = $comment;
          }
        }
        return $gal_comments; // comments on gallery
      } else {       
        return $allcomments;
      }
    }    
  }
  
  /**
   * LazyestCommentor::get_approved_comments()
   * Adds filevar varaiable to comments
   * 
   * @since 1.1.0
   * @return array all comments on gallery 
   */
  function get_approved_comments() {
  	global $lg_gallery, $wpdb;
    $page_id = (int) $lg_gallery->get_option( 'gallery_id' );
    $comments = get_approved_comments( $page_id );
    $files = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $lg_gallery->table" ) );
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT comment_id, meta_value FROM  $wpdb->commentmeta WHERE meta_key = 'lazyest';" ) );
    foreach( $comments as $comment ) {
    	$img_id = 0;
    	foreach ( $results as $result ) {
    		if ( $comment->comment_ID == $result->comment_id ) {
    			$img_id = $result->meta_value;
    			break;
    		}
    	}
    	if ( 0 == $img_id ) {
    		$comment->filevar = ''; 
    	} else {
    		foreach ( $files as $file ) {
    			if ( $file->img_ID == $img_id ) {
    				$comment->filevar = $file->file;
						break; 
    			}
    		}
    	}
    }
    return ( $comments );
  }
  
  
  /**
   * LazyestCommentor::comments_array()
   * 
   * Filters the comments for the gallery, a folder or an image
   * 
   * @param mixed $comments array of comments
   * @return array of comments
   */
  function comments_array( $comments ) {
    global $lg_gallery;  
    if ( ! is_admin() && $lg_gallery->is_gallery() ) {
      if ( ! isset( $lg_gallery->file ) ) $lg_gallery->valid();
      $filevar = $lg_gallery->file;
      return $this->get_comments_array( $filevar );
    } else {
      return $comments; // comments outside of gallery or admin
    }
  }
  
  /**
   * LazyestCommentor::get_comments_number()
   * 
   * @param mixed $count
   * @return
   */
  function get_comments_number( $count ) {
    global $wpdb, $lg_gallery;
      	
    if ( is_admin() || is_search() || ! $lg_gallery->is_gallery() )     	
			return $count;
    $lg_gallery->valid();
    $filevar = $lg_gallery->file;
    $allcomments = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1';", $lg_gallery->get_option( 'gallery_id') ) );   
    if ( $lg_gallery->is_image( $filevar ) || $lg_gallery->is_folder( $filevar ) ) {if ( $lg_gallery->is_image( $filevar ) ) {
      $folder = new LazyestFolder( dirname( $filevar ) );
      if ( $folder->valid() ) {
        $folder->load();
        $image = $folder->single_image( basename( $filevar ) ); 
        $imgID = (int) $image->id;
      }
      unset( $folder );
      } elseif ( $lg_gallery->is_folder($filevar) ) {
        $folder = new LazyestFolder(  $filevar );
        if ( $folder->valid() ) {
          $folder->open();
          $imgID = (int) $folder->id;
        }
        unset( $folder );
      }
      $select = "SELECT comment_id FROM $wpdb->commentmeta WHERE meta_value = $imgID AND meta_key = 'lazyest';";
      $query = $wpdb->prepare( $select );
      $results = $wpdb->get_col( $query );
      $img_comments = array();
      if ( 0 != count( $results ) ) {
        foreach ( $allcomments as $comment_id ) {
          if ( in_array($comment_id, $results ) ) {
            $img_comments[] = $comment_id;
          }
        }
      }
      return count( $img_comments );

    } else {
      $select = "SELECT comment_id FROM $wpdb->commentmeta WHERE meta_key = 'lazyest';";
      $query = $wpdb->prepare( $select );
      $results = $wpdb->get_col( $query );
      $gal_comments = array();
      if ( 0 != count( $results ) ) {
        foreach ( $allcomments as $comment_id ) {
          if ( ! in_array( $comment_id, $results ) ) {
            $gal_comments[] = $comment_id;
          }
      	}
       	return count( $gal_comments );
      } else {
        return $count - count( $gal_comments );
      }
    }
    
  }
  
   /**
   * LazyestCommentor::get_comment_link()
   * 
   * @param string $link
   * @param string $linkref
   * @param mixed $comment
   * @param mixed $args
   * @return string The permalink to the given comment.
   */
  function get_comment_link( $link, $comment = null, $args ) {
    global $lg_gallery, $wpdb, $wp_rewrite;
    	   	
    if ( ! isset( $comment ) ) {
    	return $link;	
    }	
    
  	$comment = get_comment( $comment );

  	if ( $comment->comment_post_ID != $lg_gallery->get_option( 'gallery_id' ) ) {
  		return $link;
  	}  	
    // get the permalink to the folder or image for this comment    	
    $file = $this->get_file_by_comment_id( $comment->comment_ID );
    if ( false === $file ) {
    	$file = '';
    }
			          
    $gallery_uri = $lg_gallery->get_option( 'gallery_prev' );
    $structure = get_option( 'permalink_structure' );    
    if ( $wp_rewrite->using_permalinks() && ( 0 == strpos( $structure, 'index.php' ) ) && ( 'TRUE' == $lg_gallery->get_option( 'use_permalinks' ) ) ) {          
    	$gallery_uri = trailingslashit( $gallery_uri ) . $file;
    } else {                
    	$gallery_uri = add_query_arg( 'file', $file, $gallery_uri );
    }   
		if ( !is_array($args) ) {
			$page = $args;
			$args = array();
			$args['page'] = $page;
		}
	
		$defaults = array( 'type' => 'all', 'page' => '', 'per_page' => '', 'max_depth' => '' );
		$args = wp_parse_args( $args, $defaults );

		if ( '' === $args['per_page'] && get_option('page_comments') )
			$args['per_page'] = get_option('comments_per_page');
	
		if ( empty($args['per_page']) ) {
			$args['per_page'] = 0;
			$args['page'] = 0;
		}
	
		if ( $args['per_page'] ) {
			$args['page'] = ( !empty($in_comment_loop) ) ? get_query_var('cpage') : $this->get_page_of_comment( $comment->comment_ID, $args );
	
			if ( $wp_rewrite->using_permalinks() )
				$link = user_trailingslashit( trailingslashit( $gallery_uri ) . 'comment-page-' . $args['page'], 'comment' );
			else
				$link = add_query_arg( 'cpage', $args['page'], $gallery_uri );
		} else {
			$link = $gallery_uri;
		}
		        
    $link = $link . '#comment-' . $comment->comment_ID;						          
    return $link;
  }
  
	/**
	 * LazyestCommentor::get_page_of_comment()
	 * 
	 * Calculate what page number a comment will appear on for comment paging.
	 *
	 * @since 1.0.36
	 * @uses get_comment() Gets the full comment of the $comment_ID parameter.
	 * @uses get_option() Get various settings to control function and defaults.
	 * @uses get_page_of_comment() Used to loop up to top level comment.
	 *
	 * @param int $comment_ID Comment ID.
	 * @param array $args Optional args.
	 * @return int|null Comment page number or null on error.

	 */
	function get_page_of_comment( $comment_ID, $args = array() ) {
		global $wpdb, $lg_gallery;
		if ( ! $comment = get_comment( $comment_ID ) )
			return;
	
		$defaults = array( 'type' => 'all', 'page' => '', 'per_page' => '', 'max_depth' => '' );
		$args = wp_parse_args( $args, $defaults );
	
		if ( '' === $args['per_page'] && get_option('page_comments') )
			$args['per_page'] = get_query_var('comments_per_page');
		if ( empty($args['per_page']) ) {
			$args['per_page'] = 0;
			$args['page'] = 0;
		}
		if ( $args['per_page'] < 1 )
			return 1;
	
		if ( '' === $args['max_depth'] ) {
			if ( get_option('thread_comments') )
				$args['max_depth'] = get_option('thread_comments_depth');
			else
				$args['max_depth'] = -1;
		}
	
		// Find this comment's top level parent if threading is enabled
		if ( $args['max_depth'] > 1 && 0 != $comment->comment_parent )
			return $this->get_page_of_comment( $comment->comment_parent, $args );
	
		$allowedtypes = array(
			'comment' => '',
			'pingback' => 'pingback',
			'trackback' => 'trackback',
		);
	
		$comtypewhere = ( 'all' != $args['type'] && isset($allowedtypes[$args['type']]) ) ? " AND comment_type = '" . $allowedtypes[$args['type']] . "'" : '';
	
		// Count comments older than this one	
		$img_ID = $this->get_id_by_comment_id( $comment->comment_ID );
		$comments_array = $this->get_comments( $img_ID );
		if ( 0 < count( $comments_array) ) {
			foreach ( $comments_array as $key => $acomment ) {
				if ( strtotime( $acomment->comment_date_gmt ) >= strtotime( $comment->comment_date_gmt ) ) {		
					unset( $comments_array[$key] );	
					continue;
				}
				if ( 0 < $acomment->comment_parent ) {
					unset( $comments_array[$key] );
					continue;
				}
			}
		}		
		$oldercoms = count( $comments_array );
		
		
		// No older comments? Then it's page #1.
		if ( 0 == $oldercoms )
			return 1;
	
		// Divide comments older than this one by comments per page to get this comment's page number
		return ceil( ( $oldercoms + 1 ) / $args['per_page'] );
	}
  
  /**
   * LazyestCommentor::comments_template()
   * Assigns the Lazyest Gallery comment template for the gallery page
   * 
   * @param string $include
   * @return string
   */
  function comments_template( $include ) {
    global $lg_gallery;
    if ( $lg_gallery->is_gallery() ) {
      $folder = new LazyestFolder( $lg_gallery->currentdir );     
      $cansave = ( '' != $lg_gallery->currentdir ) ? ( $folder->valid() && $folder->can_save() ) : true;
      if ( $cansave ) {
        $include = $lg_gallery->plugin_dir . '/inc/comments_template.php';
      }
      unset($folder);
    }   
    return $include;
  }
  
  /**
   * LazyestCommentor::comments_pagenum_link()
   * 
   * @param mixed $result
   * @return
   */
  function comments_pagenum_link( $result ) {
    global $lg_gallery;
    if ( $lg_gallery->is_gallery() ) {
      $folder = new LazyestFolder( $lg_gallery->currentdir );
      $cansave = ( '' != $lg_gallery->currentdir ) ? ( $folder->valid() && $folder->can_save() ) : true;
      if ( $cansave ) {
        $lgresult = $lg_gallery->uri() . $lg_gallery->file;            
        $comment_pos = strpos( $result, 'comment-page-' );
        if ( $comment_pos !== false ) {
          $lgresult .= substr( $result, $comment_pos );
        } else {
          $lgresult .= '#comments';
        }
        return $lgresult;
      }
      unset($folder);      
    }
    return $result;
  }
    
    
  /**
   * LazyestCommentor::get_file_by_comment_id()
   * 
   * @param mixed $comment_ID
   * @return
   */
  function get_file_by_comment_id( $comment_ID ) {
   global $wpdb, $lg_gallery;
    $commentID = (int) $comment_ID;
    $select = "SELECT * FROM $wpdb->commentmeta WHERE comment_id = $commentID AND meta_key = 'lazyest';";
    $query = $wpdb->prepare( $select );
    $result = $wpdb->get_row( $query, ARRAY_A );
    $file = '';
    if ( 0 < count( $result ) ) {
      $imgID = (int) $result['meta_value'];
      $file = $lg_gallery->get_file_by_id( $imgID );
      if ( false !== $file ) {
        if ( isset( $file[0]) ) return lg_nice_link($file[0]);
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  
  /**
   * LazyestCommentor::get_id_by_comment_id()
   * 
   * @since 1.0.3
   * @param int $comment_ID
   * @return int image ID
   */
  function get_id_by_comment_id( $comment_ID ) {
   global $wpdb, $lg_gallery;
    $commentID = (int) $comment_ID;
    $select = "SELECT * FROM $wpdb->commentmeta WHERE comment_id = $commentID AND meta_key = 'lazyest'";
    $query = $wpdb->prepare( $select );
    $result = $wpdb->get_row( $query, ARRAY_A );
    if ( 0 < count( $result ) ) {
      $imgID = (int) $result['meta_value'];
      return $imgID;
    } else {
      return 0;
    }
  }
  
  
  /**
   * LazyestCommentor::count_comments()
   * 
   * @param mixed $img_ID
   * @return
   */
  function count_comments( $img_ID ) {
    global $wpdb, $lg_gallery;      
    $page_id = $lg_gallery->get_option( 'gallery_id' );
    $gallery_page = get_post( $page_id );
    $allcount = (int) $gallery_page->comment_count;
    $imgID = (int) $img_ID;
    if ( $imgID > 0 ) {
      $select = "SELECT COUNT(*) AS cnt FROM $wpdb->commentmeta WHERE meta_value = $imgID AND meta_key = 'lazyest';";
      $query = $wpdb->prepare( $select );
      $results = $wpdb->get_results( $query );
      if ( ! empty( $results ) )
        return intval( $results[0]->cnt );
      return count( $results );
    }
		if ( $imgID == 0 ) { // count root comments
      $results = $wpdb->get_results( $wpdb->prepare( "SELECT COUNT(comment_id) as cnt FROM $wpdb->commentmeta WHERE meta_key = 'lazyest';" ) );
      return ( false === $results ) ? $allcount : $allcount - intval( $results[0]->cnt );  
    }
    if ( $imgID < 0 ) {
    	return $allcount;
    }
  }
  
  
  /**
   * LazyestCommentor::get_comments()
   * 
   * @param mixed $img_ID
   * @return
   */
  function get_comments( $img_ID )
  {
    global $lg_gallery, $wpdb;
    $imgID = (int) $img_ID;
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->commentmeta WHERE meta_value =  $imgID AND meta_key = 'lazyest';" ), ARRAY_A );		
		$filevar = $lg_gallery->get_file_by_id( $img_ID );
		$filevar = ( $filevar === false ) ? '/' : $filevar[0];
    $comments = array();
    if ( 0 != count( $results ) ) {
      foreach ( $results as $result ) {
        $comment_ID = (int) $result['comment_id'];
        $comment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->comments WHERE comment_ID = %d ORDER BY comment_date;", $comment_ID ) );
        if ( $comment ) {         
        	$comment->filevar = $filevar;
          $comments[] = $comment;
				}
      }
    }
    return $comments;
  }
  
  
  /**
   * LazyestCommentor::get_root_comments()
   * 
   * @return
   */
  function get_root_comments() {    
    return $this->get_comments_array();  
  }
  
  /**
   * LazyestCommentor::remove_comments()
   * 
   * @param mixed $filevar
   * @return
   */
  function remove_comments( $filevar ) {
    global $lg_gallery;
    $result = true;
    if ( $lg_gallery->is_image( $filevar ) ) {
      $folder = new LazyestFolder( dirname( $filevar ) );
      if ( $folder->valid() ) {
        $folder->load();
        $image = $folder->single_image( basename( $filevar ) ); 
        $img_ID = (int) $image->id;
      }
      unset( $folder );
    } elseif ( $lg_gallery->is_folder( $filevar ) ) {
      $folder = new LazyestFolder( dirname( $filevar ) );
      if ( $folder->valid() ) {
        $folder->open();
        $folder->load();
        foreach( $folder->list as $image ) {
          $this->remove_comments( $filevar . $image->image );
        }
        $imgID = (int) $folder->id;
      }
      unset( $folder );
    }
    if ( isset( $img_ID ) ) {
      $comments = $this->get_comments( $img_ID );
      if ( 0 < count( $comments ) ) {
	      foreach ( $comments as $comment ) {
	        $comment_ID = $comment->comment_ID;
	        if ( ! wp_delete_comment( $comment_ID ) )
	          $result = false;
	      }      
			}
    }
    return $result;
  } 
  
  /**
   * LazyestCommentor::edit_comments_form()
   * 
   * @return
   */
  function edit_comments_form( $all='gallery' ) {  
    global $lg_gallery;
  	
		$this->comments_from = $all;
		wp_enqueue_script( 'admin-forms' );
		wp_enqueue_script( 'admin-comments' );
    if ( isset( $_REQUEST['move_comments'] ) ){
      $lg_gallery->move_comments();
      $result = get_transient( 'lg_not_inserted' );
      if ( false === $result ) {
        $lg_gallery->success = true;
        $lg_gallery->message = __( 'Successfully updated your comments', 'lazyest-gallery' );
      }
    }
    $file = '';
    if ( $all == 'gallery' ) {      
      $file= stripslashes( rawurldecode( $_GET['file'] ) );
      $title = __('Gallery', 'lazyest-gallery' );
  		if ( $lg_gallery->is_folder( $file ) ) {
  			$this->comments_from = 'folder';               
        $folder = new LazyestFolder( $file );
        $folder->open();      
        $title = __( 'Folder ', 'lazyest-gallery' ) . '&#8220;' . $folder->title() . '&#8221;';
        unset( $folder );
  		} elseif ( $lg_gallery->is_image( $file ) ) {
  			$this->comments_from = 'image';		        
        $folder = new LazyestFolder( dirname( $file ) );
        $image = $folder->single_image( basename( $file ) );
        $title = __( 'Image ', 'lazyest-gallery' ) . '&#8220;' . $image->title() . '&#8221;';
        unset( $folder, $image );
  		} 
    } else {
      $title = __('Gallery, Folders and Images', 'lazyest-gallery' );
    }
  	
  		?>
  		<div class="wrap">
        <?php screen_icon( 'komments' ); ?>
        <h2><?php esc_html_e( 'Comments on', 'lazyest-gallery' ) ?> <?php echo esc_html( $title ) ?></h2>
        <?php $lg_gallery->options_message(); ?>
        <div id="ajax-div"></div>
        <div id="poststuff" class="metabox-holder has-right-sidebar">
          <form id="lazyest-comments" action="admin.php?page=lazyest-comments" method="post">
          <?php $this->edit_comments( $file ); ?>
    			</form>
        </div>
  		</div>
  	<?php
  } 

  /**
   * LazyestCommentor::edit_comments()
   * 
   * @param mixed $filevar
   * @return void
   */
  function edit_comments( $filevar ) {
  	global $lg_gallery;	
	switch ( $this->comments_from ) {
  	case 'folder' :
  		$folder = new LazyestFolder( $filevar );
      $folder->open();
  		$comments = $this->get_comments( $folder->id );
  		$caption = $folder->caption;
  		break;
  	case 'image' :
      $folder = new LazyestFolder( dirname( $filevar ) );
      $folder->load( 'images' );
  		$image = $folder->single_image( basename( $filevar ) );
  		$comments = $this->get_comments( $image->id );
  		$caption = $image->caption;
  		break;
    case 'gallery' :   
  		$comments = $this->get_root_comments();
  		$page_id = $lg_gallery->get_option( 'gallery_id' );
  		$gallery_page = get_post( $page_id );
  		$caption = $gallery_page->post_title;
      break;
    case 'all' :
    default :
      $page_id = (int) $lg_gallery->get_option( 'gallery_id' );
      $comments = $this->get_approved_comments( $page_id );
  		$gallery_page = get_post( $page_id );
  		$caption = $gallery_page->post_title;
      break;  
  	}   
		$do_pagination = false; 	  	
  	if ( $comments ) {
  		update_comment_cache( $comments ); 		
      $comments = array_reverse( $comments ); 
      $comments_table = new LazyestCommentsTable( $comments );     
      $perpage  = 20;            
      $total_pages = ceil( count( $comments ) / $perpage );
      $query_var = 'lg_paged';
      if ( isset ( $paged ) ) {
        $current = $paged;
      } else {      
        $current = isset( $_REQUEST[$query_var] ) ? absint( $_REQUEST[$query_var] ) : 0;	
    	$current = min( max( 1, $current ), $total_pages );
      }
      $start = ( $current - 1 ) * $perpage + 1;
      $end = min( count( $comments ), $current * $perpage);
      $do_pagination = 1 < $total_pages;
    
      if ( $do_pagination ) {
        $pagination = $lg_gallery->pagination( 'comments', $comments );
      ?>      
      <div class="tablenav"><?php echo $pagination  ?></div>
      <?php } ?>      
  		<br class="clear" />  		
      <?php $comments_table->display(); ?>
  		<?php 		 		
  	} 
    if ( $do_pagination ) {
    ?> 
    <div class="tablenav"><?php echo $pagination ?></div>
    <?php
    }
    if ( isset( $folder ) ) {
      unset ( $folder ) ;      
    }
    unset( $comments_table );
  } 
  
  /**
   * LazyestCommentor::admin_notices()
   * Display a notice aon all admin screens when comments have not been moved to wp_commentmeta
   * 
   * @since 1.1.0
   * @return void
   */
  function admin_notices() {
    global $lg_gallery;
    $not_inserted = get_transient( 'lg_not_inserted' );
    if ( false !== $not_inserted ) {
      $moveurl = admin_url( 'admin.php?page=lazyest-comments&amp;move_comments=now' );
			/* translators: 1: <a href="">, 2: </a>  */
      $lg_gallery->message = sprintf( esc_html__( 'Lazyest Gallery could not update your comments. %1sPlease try again%2s', 'lazyest-gallery' ), 
				sprintf( '<a id="movecomments" href="%s">', $moveurl),
				'</a>' 
			);
      $lg_gallery->success = false;
      $lg_gallery->options_message();
    }    
  }
    
} // LazyestCommentor


/* functions for the comment template */

function lazyest_commentmetadata( $comment ) {
	
	$theme = get_current_theme();
	if ( $theme != 'Twenty Eleven' ) {
		?>
		<div class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
			<?php
				/* translators: 1: date, 2: time */
				printf( __( '%1$s at %2$s', 'lazyest-gallery' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'lazyest-gallery' ), ' ' );
			?>
		</div><!-- .comment-meta .commentmetadata -->
		<?php
	}	
}

function lazyest_author_vcard( $comment ) {
	$theme = get_current_theme();
	if ( $theme == 'Twenty Eleven' ) {
		?>
		<footer class="comment-meta">
		<div class="comment-author vcard">
			<?php
				$avatar_size = 68;
				if ( '0' != $comment->comment_parent )
					$avatar_size = 39;

				echo get_avatar( $comment, $avatar_size );

				/* translators: 1: comment author, 2: date and time */
				printf( __( '%1$s on %2$s <span class="says">said:</span>', 'twentyeleven' ),
					sprintf( '<span class="fn">%s</span>', get_comment_author_link() ),
					sprintf( '<a href="%1$s"><time pubdate datetime="%2$s">%3$s</time></a>',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						/* translators: 1: date, 2: time */
						sprintf( __( '%1$s at %2$s', 'twentyeleven' ), get_comment_date(), get_comment_time() )
					)
				);
			?>
			<?php edit_comment_link( __( 'Edit', 'twentyeleven' ), '<span class="edit-link">', '</span>' ); ?>
		</div><!-- .comment-author .vcard -->
		</footer>
		<?php
	} else {
		$avatar_size = apply_filters( 'lazyest_avatar_size', 40 );
		?>
		<div class="comment-author vcard">
				<?php echo get_avatar( $comment, $avatar_size ); ?>
				<?php printf( __( '%s <span class="says">says:</span>', 'lazyest-gallery' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
		</div><!-- .comment-author .vcard -->
		<?php		
	}
}


function lg_comment_id_fields() {
  global $lg_gallery;
  if ( ! isset( $lg_gallery ) ) return false;
  if ( ! isset( $lg_gallery->file ) ) 
    $lg_gallery->valid();  
  if ( $lg_gallery->is_image( $lg_gallery->file ) ) {
	$the_folder = new LazyestFolder( dirname( $lg_gallery->file ) );    
	$the_image = $the_folder->single_image( basename( $lg_gallery->file ) );
    $the_id =  $the_image->id;
    $the_uri = $the_image->uri();
    $the_file = lg_nice_link( $the_folder->curdir . $the_image->image );
    unset( $the_image, $the_folder );
    } elseif ( $lg_gallery->is_folder( $lg_gallery->file ) ) {
	$the_folder = new LazyestFolder( $lg_gallery->file ) ;
    $the_folder->open();
    $the_id= $the_folder->id;
    $the_uri = $the_folder->uri();
    $the_file = lg_nice_link( $the_folder->curdir );
    unset( $the_folder );
  } else {
    return;
  } 
  ?>
  <input type="hidden" name="lg_comment_image_ID" value="<?php echo $the_id; ?>"/>
  <input type="hidden" name="redirect_to" value="<?php echo $the_uri; ?>"/>
  <input type="hidden" name="lg_filevar" value="<?php echo $the_file; ?>"/>  
  <?php
}
?>
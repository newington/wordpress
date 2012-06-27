<?php
/**
 * LazyestTable
 * Base table class for Lazyest Gallery
 * 
 * @package Lazyest Gallery  
 * @author M A C BRINKKEMPER
 * @copyright 2010-2012 Brimosoft
 * @version 1.1.0
 * @access public
 */
class LazyestTable {
 
  var $sortit;
	var $table;
  var $body;
  var $head;
  var $trbody;
  var $trhead;
  var $start;
  var $end;
  var $can_save;
  
  /**
   * LazyestTable::__construct()
   * 
   * @param mixed $items
   * @return
   */
  function __construct( $items ) {  
    global $lg_gallery;
    $this->table = $this->body = $this->head = $this->trbody = $this->trhead = array( 'class' => '', 'id' => '', 'style' => '' );
    $this->start = 0;
    $this->end = 0;
    $this->items = $items;    
    $this->can_save = true;
    $this->sortit = false;
  }
  
  /**
   * LazyestTable::display()
   * @return
   */
  function display() {    
    $class = ( '' != $this->table['class'] ) ? sprintf( ' class="%s"', $this->table['class'] ) : '';
    $id = ( '' != $this->table['id'] ) ? sprintf( ' id="%s"', $this->table['id'] ) : '';
    $style = ( '' != $this->table['style'] ) ? sprintf( ' style="%s"', $this->table['style'] ) : '';
    if ( 0 != count( $this->items ) ) {
    echo sprintf( '<table%s%s%s>', $class, $id, $style ) . "\n";
      $this->group( 'head' );
      $this->group( 'foot' );    
      $this->group( 'body' );
      echo '</table>' . "\n";      
    }
  }
    
  /**
   * LazyestTable::group()
   * 
   * @param string $type
   * @return
   */
  function group( $type = 'body' ) {
    $items = $this->columns();
    if ( 'body' == $type ) {
      if ( ! isset( $this->items ) ) $this->items = array();
    }
    $group = ( 'foot' == $type ) ? $this->head : $this->$type;
    $class = ( '' != $group['class'] ) ? sprintf( ' class="%s"', $group['class'] ) : '';
    $id = ( '' != $group['id'] ) ? sprintf( ' id="%s"', $group['id'] ) : '';
    $style = ( '' != $group['style'] ) ? sprintf( ' style="%s"', $group['style'] ) : '';
    $start = ( $type != 'body' ) ? 1 : $this->start;
    $end = ( $type != 'body' ) ? 1 : ( ( 0 < $this->end ) ? $this->end : count( $this->items ) );
    echo sprintf( '<t%s%s%s>', $type, $class, $id ) . "\n"; 
    for ( $i = $start-1; $i < $end; $i++ ) {
      $this->row( $type, $i );
    }
    echo sprintf( '</t%s>' . "\n", $type);
  }  
  
  /**
   * LazyestTable::row()
   * 
   * @param mixed $type
   * @param mixed $i
   * @return
   */
  function row( $type, $i ) { 
    $type = ( 'foot' == $type ) ? 'head' : $type;
    $trtype = 'tr' . $type;
    $row = $this->$trtype;
    $class = ( '' != $row['class'] ) ? sprintf( ' class="%s"', $row['class'] ) : '';
    $style = ( '' != $row['style'] ) ? sprintf( ' style="%s"', $row['style'] ) : '';
    echo sprintf( '<tr%s%s>', $class, $style );
    $cols = $this->columns();
    foreach( $cols as $key => $value ) {    
      $this->cell( $type, $key, $value, $i );
    }    
    echo '</tr>' . "\n";
  }
  
  /**
   * LazyestTable::cell()
   * 
   * @param mixed $type
   * @param mixed $key
   * @param mixed $value
   * @param mixed $i
   * @return
   */
  function cell( $type, $key, $value, $i ) { 
    switch ( $type ) {
    case 'foot':
    case 'head': 
      echo $this->head_cell( $key, $value ) . "\n";
      break;
    case 'body':
      echo $this->body_cell( $key, $value, $i ) . "\n";
    }   
  }
  
  /**
   * LazyestTable::head_cell()
   * 
   * @param mixed $key
   * @param mixed $value
   * @return
   */
  function head_cell( $key, $value ) {
    die( 'function LazyestTable::head_cell() must be over-ridden in a child-class.' );
  }
  
  /**
   * LazyestTable::body_cell()
   * 
   * @param mixed $key
   * @param mixed $value
   * @param mixed $i
   * @return
   */
  function body_cell( $key, $value, $i ) {   
    die( 'function LazyestTable::head_cell() must be over-ridden in a child-class.' );
  }
  
  /**
   * LazyestTable::columns()
   * 
   * @return
   */
  function columns() {
    die( 'function LazyestTable::head_cell() must be over-ridden in a child-class.' );
  }
  
  /**
   * LazyestTable::page()
   * 
   * @param mixed $query_var
   * @return
   */
  function page( $query_var ) {
	 	global $$query_var, $lg_gallery; 
    if ( ! $this->sortit ) {
      $perpage  = 20;            
      $total_pages = ceil( count( $this->items ) / $perpage ); 
      if ( isset ( $$query_var ) ) {
        $current = $$query_var;
      } else {      
        $current = isset( $_REQUEST[$query_var] ) ? absint( $_REQUEST[$query_var] ) : 0;	
    	$current = min( max( 1, $current ), $total_pages );
      }
      $this->start = ( $current - 1 ) * $perpage + 1;
      $this->end = min( count( $this->items ), $current * $perpage);  
    } else {
      $this->start = 1;
      $this->end = count( $this->items );
    }     
  }
  
} // LazyestTable


/**
 * LazyestFolderTable
 * 
 * @package  Lazyest Gallery 
 * @author M A C BRINKKEMPER
 * @copyright 2010 Brimosoft
 * @version 1.1.0
 * @access public
 */
class LazyestFolderTable extends LazyestTable {
  
  /**
   * LazyestFolderTable::__construct()
   * 
   * @param mixed $items
   * @return
   */
  function __construct( $items ) {
    global $lg_gallery, $paged;
    LazyestTable::__construct( $items );
    $this->can_save = true;
    if ( 0 < count( $items ) ) {
      foreach( $items as $folder ) {
        if ( ! $folder->can_save() || ! $folder->user_can( 'author' ) ) {
          $this->can_save = false;
          break;
        }          
      }
    }
    $this->sortit = ( $lg_gallery->sortit['folders'] && $this->can_save );
    $class = 'widefat';
    if ( $this->sortit )
      $class .= ' sortable';
      
    $this->table['class'] = $class;
    $this->table['id'] = 'sort_gallery';
    $this->table['style'] = 'clear:none;';
             
    $this->page( 'lg_paged' ); 
  }
  
  /**
   * LazyestFolderTable::columns()
   * 
   * @return
   */
  function columns() {
    return array( 
      'draghandle' => '',
      'name' => esc_html__( 'Name', 'lazyest-gallery' ),
      'caption' => esc_html__( 'Caption', 'lazyest-gallery' ),
      'images' => esc_html__( 'Images', 'lazyest-gallery' ),
      'hidden' => esc_html__( 'Visible', 'lazyest-gallery' ),
      'comments' => esc_html__( 'Comments', 'lazyest-gallery' ),
      'date' => esc_html__( 'Date', 'lazyest-gallery' )
    );
  }
  
  /**
   * LazyestFolderTable::head_cell()
   * 
   * @param mixed $key
   * @param mixed $value
   * @return
   */
  function head_cell( $key, $value ) {
    global $lg_gallery;  
    $title = $this->sortit ? __( 'Click to Sort', 'lazyest-gallery') : '';
    switch ( $key ) {
      case 'draghandle' : 
        $cell = $this->sortit ? '<th class="sorttable_nosort" style="cursor:default" scope="col"></th>' : '';
        break;
      case 'name' :
      case 'caption' :
      	$cell = sprintf( '<th scope="col" style="cursor:default" class="manage-column sorttable_alpha" title="%s">%s</th>', $title, $value );
      	break;
      case 'date' :
        $cell = sprintf( '<th scope="col" style="cursor:default" class="manage-column" title="%s">%s</th>', $title, $value ); 
        break;
      case 'hidden' :
        $cell = sprintf( '<th scope="col" class="sorttable_nosort manage-column">%s</th>', $value );
        break;
      case 'comments' :
        $cell = ( 'TRUE' == $lg_gallery->get_option( 'allow_comments' ) ) ? 
          sprintf( '<th scope="col" class="num" title="%s"><div class="vers"><img alt="%s" src="images/comment-grey-bubble.png"/></div></th>', $title, $value ) : '';
        break;
      case 'images' : 
        $cell = sprintf( '<th scope="col" class="num" title="%s">%s</th>', $title, $value );
    }
    return $cell;
  }
  
  /**
   * LazyestFolderTable::body_cell()
   * 
   * @param mixed $key
   * @param mixed $value
   * @param mixed $i
   * @return
   */
  function body_cell( $key, $value, $i ) {
    global $lg_gallery;
    $folder = $this->items[$i];
    $title = $this->sortit ? __('Click and Hold to Drag', 'lazyest-gallery' ) :  '';
    switch ( $key ) {
      case 'draghandle' : 
        $cell = $this->sortit ? sprintf( '<td class="dragHandle" title="%s"></td>', $title ) :  '';
        break;
      case 'name' :     
      $edit_url = ( $folder->user_can( 'viewer' ) ) ? admin_url( sprintf( 'admin.php?page=lazyest-filemanager&amp;folder=%s', lg_nice_link( $folder->curdir ) ) ) : '#';
        $cell = sprintf( '<td class="lg_foldercell" sorttable_customkey="%s">', strtolower( esc_attr( $folder->dirname() ) ) );
        $cell .= sprintf( '<a title="%s &quot;%s&quot;" class="row-title" href="%s">%s</a>', 
          __( 'Edit', 'lazyest-galley' ),
          esc_attr( $folder->dirname() ),
          $edit_url,
          htmlentities( $folder->dirname() )
        );
        $cell .= sprintf( '<input type="hidden" class="_index" id="index[%s]" name="index[%s]" value="%d" />',
          $folder->form_name(),
          $folder->form_name(),
          $i + 1
        );
        $cell .= sprintf( '<div class="lg_admin_list_folders" title="%s" id="lg_lf_%s"></div>',
          urlencode( $folder->curdir ), 
          $folder->id 
        );
        $cell .= '</td>';
        break;
      case 'caption' :
        $cell = sprintf( '<td sorttable_customkey="%s">%s</td>', 
          esc_attr( strtolower( $folder->title() ) ),
          strip_tags( lg_html( $folder->caption() ) ) );
        break;
      case 'date' :
        $cell = sprintf( '<td class="date column-date" sorttable_customkey="%s">%s</td>',
          $folder->datetime,
          date( get_option( 'date_format' ), $folder->datetime )
        );
        break;
      case 'hidden' :               
        $cell = sprintf( '<td class="check-column"><p title="%s" class="visibility f%s"></p></span></td>',
          ucfirst(  $folder->visibility ), 
          $folder->visibility );
        break;
      case 'comments' :        
          $cell = ( 'TRUE' == $lg_gallery->get_option( 'allow_comments' ) ) ? 
            sprintf( '<td class="num"><div class="post-com-count-wrapper"><a class="post-com-count" href="%s"><span class="comment-count">%s</span></a></div></td>', 
              admin_url( sprintf( 'admin.php?page=lazyest-filemanager&amp;edit=comments&amp;file=%s', lg_nice_link( $folder->curdir ) ) ),
              $lg_gallery->commentor->count_comments( $folder->id )
            ) : '';
        break;
      case 'images' :
        $cell = sprintf( '<td class="num">%d<br /><span title="%s" class="lg_folder_subcount" id="lg_sc_%s"></span></td>',
          $folder->count(),
          urlencode( $folder->curdir ),
          $folder->id
        ); 
        break;
          
    }
    return $cell;
  }
    
} // LazyestFolderTable

/**
 * LazyestImageTable
 * 
 * @package   
 * @author Lazyest Gallery
 * @copyright M A C BRINKKEMPER
 * @version 2010
 * @access public
 */
class LazyestImageTable extends LazyestTable {
  
  var $imagefields;
  var $extrafields;
  
  /**
   * LazyestImageTable::__construct()
   * 
   * @param mixed $items
   * @return
   */
  function __construct( $items ) {
    global $lg_gallery;
    LazyestTable::__construct( $items );
    $this->can_save = true;
    if ( 0 < count( $items ) ) {
      $image = $items[0];
      $this->can_save = $image->folder->can_save();
    }    
    $this->sortit = ( $lg_gallery->sortit['images'] && $this->can_save );
    $class = 'widefat';
    if ( $this->sortit )
      $class .= ' sortable';
      
    $this->table['class'] = $class;
    $this->table['id'] = 'sort_images';
    $this->table['style'] = 'clear:none;';   
    $this->body['id'] = 'image_list';     
           
    $this->page( 'lg_pagei' ); 
    
    $this->extrafields = false;
    $this->imagefields = $lg_gallery->get_fields( 'image' );
    if ( false !== $this->imagefields) {
      foreach( $this->imagefields as $field ) {
        if ( $field['edit'] ) {
          $this->extrafields = true;
          break;
        }
      }
    }
  }
  
  function display() {
  	if ( 0 != count( $this->items ) ) {  		
  		LazyestTable::display();	
  	} else {
  		?>
      	<table id="sort_images" class="widefat" style="clear:none;">
      	<tbody id="image_list"><tr><td><h2><?php esc_html_e( 'This folder is empty', 'lazyest-gallery' ); ?></h2></td></tr></tbody>
				</table>
    	<?php
  	}
  }
  
  /**
   * LazyestImageTable::columns()
   * 
   * @return
   */
  function columns() {
    return array(
      'draghandle' => '',
      'image' => esc_html__( 'Image', 'lazyest-gallery' ),
      'file' => esc_html__( 'File', 'lazyest-gallery' ),
      'content' => esc_html__( 'Content', 'lazyest-gallery' ),
      'comments' => esc_html__( 'Comments', 'lazyest-gallery' ),
      'date' => esc_html__( 'Date', 'lazyest-gallery' )
    );
  }
   
  /**
   * LazyestImageTable::head_cell()
   * 
   * @param mixed $key
   * @param mixed $value
   * @return
   */
  function head_cell( $key, $value ) {
    global $lg_gallery;  
    $title = $this->sortit ? esc_attr__( 'Click to Sort', 'lazyest-gallery') : '';
    switch ( $key ) {
      case 'draghandle' : 
        $cell = $this->sortit ? '<th class="sorttable_nosort" style="cursor:default" scope="col"></th>' : '';
        break;
      case 'image' :
      case 'file' :
      case 'date' :
      case 'content' :
        $cell = sprintf( '<th scope="col" style="cursor:default" class="manage-column sorttable_alpha" title="%s">%s</th>', $title, $value ); 
        break;
      case 'comments' :
        $cell = ( 'TRUE' == $lg_gallery->get_option( 'allow_comments' ) ) ? 
          sprintf( '<th scope="col" class="num" title="%s"><div class="vers"><img alt="%s" src="images/comment-grey-bubble.png"/></div></th>', $title, $value ) : '';
        break;
    }
    return $cell;
  }
    
  /**
   * LazyestImageTable::body_cell()
   * 
   * @param mixed $key
   * @param mixed $value
   * @param mixed $i
   * @return
   */
  function body_cell( $key, $value, $i ) {
    global $lg_gallery;
    $image = $this->items[$i];
    $folder = $image->folder;
    $onclick = $image->on_click();    
    $form_name = $image->form_name();    
    $caption = lg_esc_caption( $image->caption );
    $description = lg_esc_description( $image->description );    
    $tabstop = $i+2;
    switch ( $key ) {
      case 'draghandle' : 
        $title = $this->sortit ? esc_attr__('Click and Hold to Drag', 'lazyest-gallery' ) :  '';
        $cell = $this->sortit ? sprintf( '<td class="dragHandle" title="%s"></td>', $title ) : '';
        break;
      case 'image' :
	      $class= 'thumb';
		    if ( 'TRUE' != $lg_gallery->get_option( 'enable_cache' )  || 
					( ( 'TRUE' == $lg_gallery->get_option( 'async_cache' ) ) 
						&& ! file_exists( $image->loc() ) ) ) {
					$class .= ' lg_ajax';	
				}	
        $cell = '<td class="column-icon media-icon" width="80">';
        $cell .= sprintf( '<a target="_blank" href="%s" class="alignleft %s">',
          $onclick['href'],
          $onclick['class'] 
        );
        $cell .= sprintf( '<img width="60" height="60" class="attachment-80x60 %s" src="%s" alt="%s"  title="%s" /></a></td>',
        	$class,
          $image->src(),
          $image->alt(),
          $image->title() 
        );
        break;
      case 'file' :           
        $delete_warning = sprintf( __( "'You are about to delete \'%s\' \\n \'Cancel\' to stop \'OK\' to delete'", 'lazyest-gallery' ),
          htmlentities( $image->image, ENT_QUOTES)
        ); 
        $cell = sprintf( '<td class="media column-media" sorttable_customkey="%s">', $form_name );
        $cell .= sprintf( '<p style="cursor:default"><strong>%s</strong></p>', htmlentities($image->image) );
        $cell .= sprintf( '<input type="hidden" class="_index" id="index_%s" name="index_%s" value="%d" />', 
          $form_name,
          $form_name,
          $i + 1 );
        $cell .= '<div class="row-actions" style="cursor:default"><p>';
        $cell .= $this->can_save ? sprintf( '<span class="delete"><a class="submitdelete" onclick="if(confirm(%s)){return true;}return false;" href="%s" >%s</a> |</span>', 
          $delete_warning,
          admin_url( sprintf( 'admin.php?page=lazyest-filemanager&amp;folder=%s&amp;file_to_delete=%s' ,
            lg_nice_link( $folder->curdir ),
            lg_nice_link( $image->image )
          ) ),
          __( 'Delete Permanently', 'lazyest-gallery' ) ) : '';
        $cell .= sprintf( '<span class="view"> <a title="%s %s" target="_blank" href="%s" class="%s">%s</a> |</span>',
          __('View', 'lazyest-gallery'),
          htmlentities("\"$image->image\"", ENT_QUOTES),          
          $onclick['href'],
          $onclick['class'],
          __('View ', 'lazyest-gallery')
        );
        if ( $this->can_save ) {
          $cell .= '<br />';
          if ( $this->sortit ) {
            $cell .= sprintf( '<span style="display:none;" class="view hide-if-no-js"> <a class="to_top" id="to_top_%s" href="#" title="%s">%s &uarr;</a> |</span>',
              $form_name,   
              __( 'Move image to top of the list', 'lazyest-gallery' ),
              __( 'to Top', 'lazyest-gallery' )
            );
            $cell .= sprintf( '<span style="display:none;" class="view hide-if-no-js"> <a class="to_bottom" id="to_bottom_%s" href="#" title="%s">%s &darr;</a></span>',
              $form_name, 
              __( 'Move image to bottom of the list', 'lazyest-gallery' ),
              __( ' to Bottom', 'lazyest-gallery' )
            );
          }
          $cell .= sprintf( '| <span style="display:none;" class="view hide-if-no-folders"> <a rel="%s" class="move_to" id="mt%s" href="#" title="%s">%s&hellip;</a> |</span>',
            urlencode( $image->folder->curdir . $image->image ),
            $image->id,
            __( 'Right click to select folder', 'lazyest-gallery' ),
            __( 'Move to', 'lazyest-gallery')            
          );
          $cell .= sprintf( '<span style="display:none;" class="view hide-if-no-folders"> <a rel="%s" class="copy_to" id="ct%s" href="#" title="%s">%s&hellip;</a> |</span>',
            urlencode( $image->folder->curdir . $image->image ),
            $image->id,
            __( 'Right click to select folder', 'lazyest-gallery' ),
            __( 'Copy to', 'lazyest-gallery')            
          );
        }
        $cell .= '</p></div>';
        $extra_html = '';
        $cell .= apply_filters( 'lazyest_gallery_edit_image', $extra_html, $image );
        $cell .= '</td>';
        break;    
      case 'content' :
        $imagefields = $lg_gallery->get_fields( 'image' );
        $readonly = ! $this->can_save ? 'readonly="readonly"' : '';
        $cell = sprintf( '<td class="column-caption" sorttable_customkey="%s"><a name="%s"></a>',
          esc_attr( $caption ),
          $form_name,
          $readonly
        );
        $cell .= sprintf( '<strong>%s</strong><br /><input class="caption-text" type="text" tabindex="%s" name="%s" value="%s" size="50" %s />',
          __( 'Caption', 'lazyest-gallery' ),
          $tabstop,
          $form_name,
          $caption,
          $readonly
        );
        $cell .= sprintf( '<div id="descdiv_%s" class="descdiv hide_in_list">', $form_name );
        $cell .= sprintf( '<strong>%s</strong><br />', __( 'Description', 'lazyest-gallery' )  );
        $cell .= sprintf( '<textarea class="desc codepress" name="desc_%s" id="desc_%s" %s tabindex="%d" cols="50" >%s</textarea>', 
          $form_name,
          $form_name,
          $readonly,
          $tabstop + 1,
          $description          
        );
        if ( $this->extrafields ) {
          $cell .= sprintf( '<div id="xtradiv_%s" class="xtradiv hide_in_list">', $form_name );
          foreach( $this->imagefields as $field ) {
            if ( $field['edit'] ) {
              $value = htmlspecialchars( stripslashes( $image->extra_fields[$field['name']] ), ENT_QUOTES );
              $cell .= sprintf( '<strong>%s</strong><br />', $field['display'] );
              $cell .= sprintf( '<input id="%s_%s" name="%s_%s" type="text" style="width:100%s" value="%s" />',
                $form_name,
                $field['name'],
                $form_name,
                $field['name'],
                '%',
                $value
              );
            }
          }
          $cell .= '</div>';
        }
        $cell .= '</div></td>';
        break;
      case 'date' :
        $cell = sprintf( '<td sorttable_customkey="%s">%s</td>', 
          $image->datetime, 
          date( get_option( 'date_format' ), $image->datetime )
        ); 
        break;
      case 'comments' :
        if ( 'TRUE' == $lg_gallery->get_option( 'allow_comments' ) ) {          
          $cell = '<td class="num"><div class="post-com-count-wrapper">';
          $cell .= sprintf( '<a class="post-com-count" href="%s"><span class="comment-count">%s</span></a>',
            admin_url( sprintf('admin.php?page=lazyest-filemanager&amp;edit=comments&amp;file=%s', 
              lg_nice_link( path_join( $image->folder->curdir, $image->image ) ) ) ),
            $lg_gallery->commentor->count_comments( $image->id )
          ); 
          $cell .= '</div></td>';
        } else {
          $cell = '';
        }          
        break;
    }
    return $cell;
  }
  
} // LazyestImageTable

/**
 * LazyestCommentsTable
 * We don't use the WordPress comments table
 * 
 * @package Lazyest Gallery
 * @author M A C BRINKKEMPER
 * @copyright 2010
 * @version 1.1.0
 * @access public
 */
class LazyestCommentsTable extends LazyestTable {
  
  function __construct( $items ) {
    LazyestTable::__construct( $items );
    $this->can_save = true;
    $this->table['class'] = 'widefat fixed comments';
    $this->table['id'] = 'comments-table';
    $this->body['id'] = 'the-comment-list';
    $this->body['class'] = 'list:comment';             
    $this->page( 'lg_paged' ); 
    $this->trbody['class'] = 'approved';
  }
  
  function columns() {
    return array(
      'author' => esc_html__( 'Author', 'lazyest-gallery' ),
      'comment' => esc_html__( 'Comment', 'lazyest-gallery' ),
      'response' => esc_html__( 'In Response To', 'lazyest-gallery' )
    );
  }
  
  function head_cell( $key, $value ) {
    global $lg_gallery;  
    $class = array();
    $class[] = 'manage-column';
    switch ( $key ) {   
      case 'author' : 
        $class[] = 'column-author';
        break;
      case 'comment' :
        $class[] = 'column-comment';
        break;
      case 'response' :
        $class[] = 'column-response';
        break;
    }    
    $cell = sprintf( '<th id="%s" scope="col" style="cursor:default" class="%s" >%s</th>', 
      $key,
      implode( ' ', $class ), 
      $value 
    );
    return $cell;
  }
  
  function body_cell( $key, $value, $i ) {
    global $comment, $lg_gallery;
    // set global $comment so we can use WordPress functions
    $comment = $this->items[$i];              
    $comment_ID = $comment->comment_ID;  
    // in this table we only shows approved comments  
    $comment_status = 'approve';       
    $pending_comments = 0;                  
    switch ( $key ) {      
      case 'author' :
        $author_url = get_comment_author_url();
        $cell = '<td class="author column-author">';
        $cell .= sprintf( '<strong> %s %s </strong><br />',
          get_avatar( $comment, 32 ),
          get_comment_author( $comment )          
        );
        if ( current_user_can( 'moderate_comments' ) ) {
	        if ( ! empty($comment->comment_author_email) ) {
	          $cell .= get_comment_author_email_link() . '<br />';
					}
	        $cell .= sprintf( '<a href="edit-comments.php?s=%s&amp;mode=detail">%s</a>',
	          get_comment_author_IP( $comment ),
	          get_comment_author_IP( $comment )
	        ); 
				}
        $cell .= '</td>';
        break;
        
      case 'comment' :        
        $approve_nonce = esc_html( '_wpnonce=' . wp_create_nonce( "approve-comment_$comment_ID" ) );      
    		$del_nonce = esc_html( '_wpnonce=' . wp_create_nonce( "delete-comment_$comment_ID" ) );
        $postID = $lg_gallery->get_option( 'gallery_id' );
        $cell = '<td class="comment column-comment">';
				$filevar = isset( $comment->filevar ) ? $comment->filevar : '';
				$uri =  trailingslashit( $lg_gallery->get_option( 'gallery_prev' ) );
				if ( 'TRUE' != $lg_gallery->get_option( 'use_permalinks' ) ) {
					$comment_url = add_query_arg( 'file', $filevar, $uri );
				} else {
					$comment_url = trailingslashit( $uri . $filevar );
				}
				$comment_url .= '#comment-' . $comment_ID;
				$cell .= '<div class="submitted-on">';
        $cell .= sprintf( __( 'Submitted on <a href="%1$s">%2$s - %3$s</a>' ),
          esc_url( $comment_url ), 
          get_comment_date( get_option( 'date_format' ) ), 
          get_comment_date( get_option( 'time_format' ) ) 
        ); 
        $cell .= sprintf( '<p>%s</p>', apply_filters( 'comment_text', get_comment_text( $comment ), $comment ) );
        if ( current_user_can( 'moderate_comments' ) ) {
	        $cell .= '<div class="row-actions">';
	        $cell .= sprintf('<span class="unapprove"><a href="%s" class="delete:the-comment-list:comment-%s:e7e7d3:action=dim-comment&amp;new=unapproved vim-u vim-destructive" title="%s">%s</a> | </span>',
	          esc_url( "comment.php?action=unapprovecomment&p=$postID&c=$comment_ID&$approve_nonce" ),
	          $comment_ID,
	          esc_attr__( 'Unapprove this comment' ),
	          __( 'Unapprove' )
	        );  
	        $cell .=  sprintf( '<span class="edit"><a href="comment.php?action=editcomment&amp;c=%s" title="%s">%s</a> | </span>',
	          $comment_ID,      
	          esc_attr__( 'Edit comment' ),
	          __( 'Edit' )
	        );
	        $cell .= sprintf( '<span class="spam"><a href="%s" class="delete:the-comment-list:comment-%s::spam=1 vim-s vim-destructive" title="%s">%s</a> | </span>',
	          esc_url( "comment.php?action=spamcomment&p=$postID&c=$comment_ID&$del_nonce" ),
	          $comment_ID,
	          esc_attr__( 'Mark this comment as spam' ),
	          _x( 'Spam', 'verb' )
	          );
	        $cell .= sprintf( '<span class="trash"><a href="%s" class="delete:the-comment-list:%s::trash=1 delete vim-d vim-destructive" title="%s">%s</a></span>',
	          esc_url( "comment.php?action=trashcomment&p=$postID&c=$comment_ID&$del_nonce" ),
	          $comment_ID,
	          esc_attr__( 'Move this comment to the trash' ),
	          _x('Trash', 'verb')          
        );  
        }
        $cell .= '</td>';
        break;
        
      case 'response' :
        $cell= '<td class="response column-response">';   
		    $edit_title =  esc_html__( 'Gallery', 'lazyest-gallery' );
		    $edit_url = admin_url( 'admin.php?page=lazyest-filemanager&folder=' );	
				$filevar = isset( $comment->filevar ) ? stripslashes( rawurldecode( $comment->filevar ) ) : $lg_gallery->commentor->get_file_by_comment_id( $comment_ID );
		    $preview = $lg_gallery->get_option( 'gallery_prev' );
		    $class = 'alignright';
		    $img_src = trailingslashit( $lg_gallery->plugin_url ) . 'images/folders.png';
		    $img_alt = __( 'Icon', 'lazyest-gallery' );
		    $img_title = __( 'Click to View', 'lazyest-gallery' );
		    $img_id = 0;
      	$img_class = 'lg';
		    if ( '' !== $filevar ) {
		      $edit_url .= ( $lg_gallery->is_folder( $filevar ) ) ? lg_nice_link( $filevar ) : lg_nice_link( dirname( $filevar ) ); 
		      if ( $lg_gallery->is_folder( $filevar ) ) {
		        $folder = new LazyestFolder( $filevar );
		        $folder->open();         
		        $edit_title = $folder->title(); 
		        $preview = $folder->uri();
		        $img_src = trailingslashit( $lg_gallery->plugin_url ) . 'images/folder-icon.png';
		        $img_id = $folder->id;
		      }
		      if ( $lg_gallery->is_image( $filevar ) ) {
		        $folder = new LazyestFolder( dirname( $filevar ) );
		        $image = $folder->single_image( basename( $filevar), 'thumbs' );
		        $edit_url .= '#' . $image->form_name();
		        $edit_title = $image->title();
		        $onclick = $image->on_click();
		        $preview = $onclick['href'];
		        $class .= ' ' . $onclick['class'];
		        $img_src = $image->src();	        
		        if ( ( 'TRUE' == $lg_gallery->get_option( 'async_cache' ) && ! file_exists( $image->loc() ) ) ) {
							$img_class = 'lg_ajax';	
						}
		        $img_alt = $image->alt();
		        $img_id = $image->id;
		      }
		    } 
		    $cell .= '<div class="response-links"><span class="post-com-count-wrapper">';
		    $cell .= sprintf( '<a href="%s">%s</a><br />', $edit_url, $edit_title );
		    $cell .= sprintf( '<a href="admin.php?page=lazyest-filemanager&edit=comments&file=%s" title="%s" class="post-com-count"><span class="comment-count">%s</span></a>',
		      $filevar,
		      esc_attr( __( '0 pending' ) ),
		      $lg_gallery->commentor->count_comments( $img_id )
		    );
		    $cell .= '</div>';
		    $cell .= sprintf( '<a target="_blank" href="%s" class="alignright"><img width="32" height="32" src="%s" alt="%s"  title="%s" class="%s" /></a>', 
		      $preview,
		      $img_src,
		      $img_alt,
		      $img_title,
		      $img_class
		    );
        $cell .= "</td>\n";
        break;      
    } 
    return $cell;
  }
  
} // LazyestCommentsTable

?>
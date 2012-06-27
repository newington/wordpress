<?php
class LazyestFolder {
  
  /**
   * Holds the directory relative to the gallery root
   * @var string
   */
  var $curdir;
  
  /**
   * Holds the raw caption
   * @var string
   */  
   var $caption;
   
  /**
   * Holds the raw description
   * @var string
   */  
	var $description;
  
  /**
   * Holds the sorting rank
   * @var string
   */
	var $order;
  
	/**
   * Holds the visibility ( hidden or visible )
   * @var string
   */
   var $visibility;
  	
	/**
   * Holds the folder id
   * @var int
   */
  var $id;
  
  /**
   * Holds the folder date
   * @var int
   * @since 1.0.0
   */
  var $datetime;
  
  /**
   * Holds the images in this folder
   * @var array
   */
  var $list;
  
  /**
   * Array to hold user defined fields
   * @since 1.1.0
   * @var array
   */
  var $extra_fields = array();
  
  /**
   * Holds the editor user ID
   * @since 1.1.0
   * @var int
   */
  var $editor;
  
  /**
   * Holds the list of author IDs
   * @var array
   * @since 1.1.0
   */
  var $authors;
   
  /**
  * Holds the minimum viewer level
  * @var string
  * @since 1.1.0
  */
  var $viewer_level; 
  
  /**
   * LazyestFolder::__construct()
   * 
   * @param string $path
   * @return bool
   */
  function __construct( $path ) { 
    global $lg_gallery;    
    $this->curdir = trailingslashit( ltrim( $path, '\\/'  ) ); 
    if ( ! $this->valid() ) {
      return false;
    }
    $fields = $lg_gallery->get_fields( 'folder' );
    if ( false !== $fields ) {
      foreach( $fields as $field ) {
        $this->extra_fields[$field['name']] = '';
      }
    }
    return true;   
  }
  
  
  /**
   * LazyestFolder::valid()
   * 
   * @return
   */
  function valid() {
    global $lg_gallery;
    if ( ( '' == $this->curdir ) || ! is_dir( $lg_gallery->root . $this->curdir ) ) {
      return false;
    }     
    if ( ! is_readable( $lg_gallery->root . $this->curdir ) ) {
      return false;
    }
    $dirs = explode( '/', $this->curdir );
    $dir = $dirs[ count( $dirs ) - 2 ];     $result = true;
    $excluded = $lg_gallery->get_option( 'excluded_folders' );
    if ( $excluded ) {
			if ( is_array( $excluded ) ) {
				$result = !in_array( $dir, $excluded );
			}	else if ( is_string( $excluded ) ) {
				$result = ( strcasecmp( $dir, $excluded ) == 0);
			}	    	
    }	   
    return ( '' == $dir ) ? true : $result && ( $dir != '.' ) && ( $dir != '..' ) && ( '.' != $dir[0] );
  }
  
  /**
   * LazyestFolder::can_save()
   * Check user capabilities and file system 
   * 
   * @return
   */
  function can_save() {  
    global $lg_gallery; 
    return is_writeable( $lg_gallery->root . $this->curdir );
  }
  
  
  /**
   * LazyestFolder::uri()
   * 
   * @return
   */
  function uri( $widget = 'none' ) {
    global $lg_gallery; 
    if ( !isset( $lg_gallery ) || !$this->user_can( 'viewer' ) ) {
      return false;
    }        
    $gallery_uri = $lg_gallery->uri( $widget ); 
    $root = '';
    if ( ( isset( $lg_gallery->virtual_root ) ) && ! $lg_gallery->is_gallery() && ( 'TRUE' == $lg_gallery->get_option( 'link_to_gallery' ) ) )
    	$root = trailingslashit( $lg_gallery->virtual_root );
		$file = $root . $this->curdir;	 
    $uri = add_query_arg( 'file', lg_nice_link( $file ), $gallery_uri );
    $structure = get_option( 'permalink_structure' );    
    if ( (  0 < strlen( $structure ) ) && ( 0 == strpos( $structure,'index.php' ) ) ) {
      if ( ( 'TRUE' == $lg_gallery->get_option( 'use_permalinks' ) )  && ( $lg_gallery->is_gallery() || ( 'widget' == $widget ) || is_home() || is_admin() || ( 'TRUE' == $lg_gallery->get_option( 'link_to_gallery' ) ) ) )  {       
        $uri = trailingslashit( $gallery_uri ) . lg_nice_link( $file );
      }  
    }     
    return $uri; 
  }
  
  /**
   * LazyestFolder::dirname()
   * 
   * @return
   */
  function dirname() {
    $dirs = explode( '/', $this->curdir );
    return $dirs[count( $dirs )-2];
  }
  
  /**
   * LazyestFolder::realdir()
   * Returns the directory relative to the 'real' gallery root as given in Lazyest Gallery settings 
   * 
   * @since 1.0.3
   * @return string
   */
  function realdir() {
    global $lg_gallery;
    return ! isset( $lg_gallery->virtual_root ) ? $this->curdir : $lg_gallery->virtual_root . $this->curdir;
  }
  
  function caption() {
    global $lg_gallery;   
    $caption = ( '' != $this->caption ) ? $this->caption : str_replace( '_', ' ', htmlentities( $this->dirname() ) );
    $caption =  ( 'TRUE' == $lg_gallery->get_option( 'use_folder_captions' ) ) ? $caption : htmlentities( $this->dirname() );
    return apply_filters( 'lazyest_folder_caption', $caption );
  }
  
  function description() {
    return apply_filters( 'lazyest_folder_description', lg_html( $this->description ) );
  }
  

  /**
   * LazyestFolder::title()
   * Browser title
   * 
   * @return
   */
  function title() {
    global $lg_gallery;   
    
    $caption =  $this->caption();
    $title =  strip_tags( lg_html( $caption ) );
    return apply_filters( 'lazyest_folder_title', $title );
  }
  
  function form_name() {   
    return sanitize_title( untrailingslashit( $this->curdir ) );
  }
  
  function html_id() {
    return $this->form_name();
  }
    
  function is_folder_icon( $filevar ) {
  	global $lg_gallery;
  	if ( 'icon' != $lg_gallery->get_option( 'folder_image' ) ) {
  		return false;
  	}
  	$path_parts = pathinfo( $filevar );
  	$filename = $path_parts['filename'];  
    $dirs = explode( '/', untrailingslashit( $this->curdir ) );
    $dir = $dirs[ count( $dirs )-1 ];   
  	return ( $filename == $dir );  	
  }
  
  
  function count( $sub = 'root' ) {
    global $lg_gallery;
    $location = $lg_gallery->root . $this->curdir;
	 	$numfiles = 0;
    $subfiles = 0;
		if ( $dir_content = @opendir( $location ) ) {  
			while ( false !== ( $dir_file = readdir( $dir_content ) ) ) {
        if ( ( 'subfolders' == $sub ) && $lg_gallery->valid_dir( $location . $dir_file ) ) {
          $subfolder = new LazyestFolder( $this->curdir . $dir_file );
          if ( false != $subfolder ) {
            $subfiles += $subfolder->count( $sub );            
          }
          unset( $subfolder );
        }  			 
				if  ( is_readable( $location . $dir_file ) &&	( 0 < preg_match( "/^.*\.(jpg|gif|png|jpeg)$/i", $dir_file ) ) ) {
					if ( ! $this->is_folder_icon( $dir_file ) ) {
						$numfiles++;
					}
				}
			}
      @closedir( $dir_content );
		} else {
	    return false;
		}
  	return ( 'subfolders' == $sub ) ? $numfiles + $subfiles : $numfiles;
  }
  
  function _compare_i( $i1, $i2 ) {    
    global $lg_gallery;
    $how = $lg_gallery->get_option( 'sort_alphabetically' );
    $comp1 = $comp2 = '0'; // prevent notices when option 'sort_alphabetically' has not been set.
    $id = '';
    switch ( $how ) {
      case 'TRUE' :
      case 'DTRUE' : 
        $comp1 = $i1->image;
        $comp2 = $i2->image;
        $id = 'image';
        break;
      case 'CAPTION' :
      case 'DCAPTION' : 
        $comp1 = $i1->caption();
        $comp2 = $i2->caption();
        $id = 'caption';
        break;
      case 'FALSE' :
      case 'DFALSE' : 
        $comp1 = $i1->datetime;
        $comp2 = $i2->datetime;
        break;
      case 'MANUAL' :  
        $comp1 = $i1->index;
        $comp2 = $i2->index;
        break;  
    }           
    if ( $id == 'image' || $id == 'caption' ) {
      $comp1 = strtolower( $comp1 );      
      $comp2 = strtolower( $comp2 );
    }    
    if ( $comp1 == $comp2 ) {
      return 0;
    }    
    $result = ( $comp1 < $comp2 ) ? -1 : 1;
    return ( 'D' == $how[0] ) ? -$result : $result;
  }
  
  function sort() {
    if ( isset( $this->list ) ) {
      return ( usort( $this->list, array( &$this, '_compare_i' ) ) );
    } else {
      return false;
    }
  }
  
  
  /**
   * LazyestFolder::open()
   * Setting class members by reading captions.xml file if found
   * 
   * @return bool success or failure
   */
  function open() {
    global $lg_gallery;
  	$this->caption = '';  
  	$this->description = '';
  	$this->level = '1';
  	$this->order = '';
  	$this->visibility = 'visible';	
  	$this->id = '0';    
    $this->datetime =  @filemtime( $lg_gallery->root . $this->curdir );
  	if ( false === $this->datetime ) {
  		$this->datetime = time();
  	} 
    $this->editor = -1;
    $this->authors = array();
    $this->viewer_level = $lg_gallery->get_option( 'viewer_level' ); // inherit viewer level from gallery
    $roles = array(); // for compatibility reasons
  	if ( file_exists( $lg_gallery->root . $this->curdir . 'captions.xml' ) ) {
  		$objXML = new LazyestXMLParser();
  		$arrXML = $objXML->parse( $lg_gallery->root . $this->curdir . 'captions.xml' );
  		if ( isset( $arrXML ) && ( null != $arrXML ) ) {
  			foreach ( $arrXML[0]['children'] as $child ) {			 
				  switch ( $child['name'] ) {
  					case 'FOLDER' :              
				      if ( isset( $child['tagdata'] ) )  {
  						  $this->caption = stripslashes( html_entity_decode( utf8_decode( $child['tagdata'] ) ) );
              }
  						break;      
  					case 'FDESCRIPTION' :              
				      if ( isset( $child['tagdata'] ) )  {
  						  $this->description = stripslashes( html_entity_decode( utf8_decode( $child['tagdata'] ) ) );
              }
  						break;
  					case 'LEVEL' : // for pre 0.12 compatibility. will not be written back              
				      if ( isset( $child['tagdata'] ) )  {
  							$level = $child['tagdata'];
 							  if ( $level == 1 ) 
  							 $this->viewer_level = 'subscriber';
 								if ( $level > 1 ) 
                  $this->viewer_level = 'author';
 								if ( $level > 2 ) 
                  $this->viewer_level = 'editor';
 								if ( $level > 7 ) 
                  $this->roles[] = 'administrator';
  						} else {
  							$this->roles[] = 'everyone';
  						}
  					 break;
            case 'VIEWER_LEVEL' :
              if ( isset( $child['tagdata'] ) ) {
                $this->viewer_level = $child['tagdata'];
              }
              break;
  					case 'ORDER' :            
				      if ( isset( $child['tagdata'] ) )  {
  						  $this->order = $child['tagdata'];
              }
  						break;
  					case 'VISIBILITY' :              
				      if ( isset( $child['tagdata'] ) )  {
  						  $this->visibility = $child['tagdata'];
              }
  						break;		
  				  case 'ID' :              
				      if ( isset( $child['tagdata'] ) )  {
  				  	  $this->id = $child['tagdata'];
              }
  						break;
  					case 'ROLE' : // for pre 1.1 compatibility. will not be written back             
				      if ( isset( $child['tagdata'] ) )  {
				  	    $roles[] = $child['tagdata'];
              }
  						break;
            case 'FOLDERDATE' :
				      if ( isset( $child['tagdata'] ) )  {
  				  	 $this->datetime = (int)$child['tagdata'];
              }
  						break; 
            case 'EDITOR' :         
				      if ( isset( $child['tagdata'] ) )  {
  				  	  $this->editor = $child['tagdata'];
              }
              break;
            case 'AUTHOR' :                      
				      if ( isset( $child['tagdata'] ) )  {
  				  	  $this->authors[] = $child['tagdata'];
              }
              break;
            default:
              $key = strtolower( $child['name'] );                    
              // add value to extra field
              if ( isset( $child['tagdata'] ) ) 
								$this->extra_fields[$key] = stripslashes( html_entity_decode( utf8_decode( $child['tagdata'] ) ) );
              if ( ! $lg_gallery->has_field( $key ) ) 
								$lg_gallery->add_field( $key, 'folder' ); // register field in the gallery
              break;  		
  				}
        }    			
    		if ( '0' == $this->id ) { // id is mandatory
    			$k = $lg_gallery->get_option( 'image_indexing' );
    			$this->id = $k++;
    			$lg_gallery->update_option( 'image_indexing' , $k );
          $this->change();
    		}
        if ( '' == $this->editor ) { // if author is not set, inherit from one level up.         
          $up_dirs = explode( '/', untrailingslashit( $this->curdir ) );
          $path = '';
          if ( 1 < count( $up_dirs ) ) {
            for ( $i = 0; $i < count( $up_dirs ) - 1; $i++ ) {
              $path .= trailingslashit( $up_dirs[$i] );
            }
            $upfolder = new LazyestFolder( $path );
            $upfolder->open(); // this is a recusursive check if level up editor = ''
            $this->editor = $upfolder->editor;
            unset( $upfolder );
          }      
        }
        if ( 0 != count( $roles ) ) { // if roles have been read, convert to viewer_level
          if ( in_array( 'everyone', $roles ) ) 
            $this->viewer_level = 'everyone';
          elseif ( in_array( 'subscriber', $roles ) )
            $this->viewer_level = 'subscriber';
          elseif ( in_array( 'author', $roles ) )
            $this->viewer_level = 'author';
          elseif ( in_array( 'editor', $roles ) )
            $this->viewer_level = 'editor';
          elseif ( in_array( 'administrator', $roles ) )
            $this->viewer_level = 'administrator';
          $this->change();            
        }
    	}
    	unset($arrXML);
    	unset($objXML);
      return true;
  	} else {
			$k = $lg_gallery->get_option( 'image_indexing' );
    	$this->id = $k++;
    	$lg_gallery->update_option( 'image_indexing' , $k );
      $this->change();    
  	}
  }
  
  
  function write_xml( $handle ) {
    if ( ! $this->can_save() || ! isset( $handle ) ) {
      return false;
    }    
  	fwrite( $handle, "\t<folder><![CDATA[" . utf8_encode( htmlentities( $this->caption ) ) . "]]></folder>\n" );  
  	fwrite( $handle, "\t<fdescription><![CDATA[" . utf8_encode( htmlentities( $this->description ) ) . "]]></fdescription>\n" );		
  	fwrite( $handle, "\t<order>" . $this->order . "</order>\n" );
  	fwrite( $handle, "\t<visibility>" . $this->visibility . "</visibility>\n" );
  	fwrite( $handle, "\t<id>" . $this->id . "</id>\n" );
    fwrite( $handle, "\t<folderdate>" . $this->datetime . "</folderdate>\n" );
    fwrite( $handle, "\t<editor>" . $this->editor . "</editor>\n" );
    if ( 0 < count( $this->authors ) ) {
      foreach ( $this->authors as $author ) {
        fwrite( $handle, "\t<author>" . $author . "</author>\n" );
      }
    }
    fwrite( $handle, "\t<viewer_level>" . $this->viewer_level . "</viewer_level>\n");
    
    if ( ! isset( $this->extra_fields ) ) 
      return;
      
    if ( 0 < count( $this->extra_fields ) ) {       
      foreach( $this->extra_fields as $key => $field ) {
        fwrite( $handle, "\t<$key><![CDATA[" . utf8_encode( htmlentities( $field ) ) . "]]></$key>\n" );  
      }
    }
  }
  
  
  function _read_files() {  
    global $lg_gallery;
    $location = $lg_gallery->root . $this->curdir;
	 	$ilist = array();
		if ( $dir_content = @opendir( $location ) ) {  
			while ( false !== ( $dir_file = readdir( $dir_content ) ) ) {
        if ( $lg_gallery->is_image( $this->curdir . $dir_file ) ) {
          $filename = basename( $dir_file );
          $ilist[] = $filename;   
          do_action( 'lazyest_read_file', $this->curdir, $filename );         
        }        			 
			}
      @closedir( $dir_content );
		} else {	  
	    return false;
		}  
    return $ilist;    
  }
  
  function _new_item( $what = 'images' ) {
    switch ( $what ) {
	    case 'images' : 
        $image = new LazyestImage( $this );        
        break;
      case 'thumbs' :
        $image = new LazyestThumb( $this );
        break;
      case 'slides' :
        $image = new LazyestSlide( $this );
        break; 
	  }
    return $image;       
  }
  
  function _empty_list() {
    for ( $i = 0; $i < count( $this->list ); $i++ ) {
      $image = $this->list[$i];
      unset( $image );
    }
    unset( $this->list );
  }
  
  
  /**
   * LazyestFolder::load()
   * 
   * @param string $what What to load, can be either 'images', 'thumbs' or 'slides'
   * Reads all images in the folder and reads image data from captions.xml file.
   * Writes captions.xml with new images info, and deletes not found images info. 
   * @return bool success or failure
   */
  function load( $what = 'images' ) {
    global $lg_gallery;
    
    if ( isset( $this->list ) ) {
      $this->_empty_list();
    }
    $readfiles = $this->_read_files();
    if ( $readfiles === false ) {
      return false;
    } 
    $changed = false; 
  	if ( file_exists( $lg_gallery->root . $this->curdir . 'captions.xml' ) ) {	
  		$objXML = new LazyestXMLParser();
  		$arrXML = $objXML->parse( $lg_gallery->root . $this->curdir . 'captions.xml' );
  		$i = 0;
  		if ( isset( $arrXML ) && ( $arrXML != null ) ) {
  			foreach ( $arrXML[0]['children'] as $child ) {
  				if ( 'PHOTO' == $child['name'] ) {
  				  $image = $this->_new_item( $what );
            $image->datetime = 0; 
            $image->id = '';
            if ( array_key_exists( 'ID', $child['attrs'] ) ) {			  
  					  $image->image = $child['attrs']['ID']; // for compatibility pre 0.12 - tricky: in the xml file, ID is used for the image name                            
            }            
  					foreach( $child['children'] as $grandchild ) {  					  
  						switch ( $grandchild['name'] ) { 						  
  							case 'FILENAME' :
                  if ( isset( $grandchild['tagdata'] ) ) {
  								  $image->image = stripslashes( html_entity_decode( utf8_decode(  $grandchild['tagdata'] ) ) );
                  }              
  								break;
  							case 'CAPTION' :
                  if ( isset( $grandchild['tagdata'] ) ) {
  								  $image->caption = stripslashes( html_entity_decode( utf8_decode( $grandchild['tagdata'] ) ) );
                  }                
  								break;                
  							case 'DESCRIPTION' :                
                  if ( isset( $grandchild['tagdata'] ) ) {
  								  $image->description = stripslashes( html_entity_decode( utf8_decode( $grandchild['tagdata'] ) ) );
                  }
  								break;
  							case 'IMAGE' :                
                  if ( isset( $grandchild['tagdata'] ) ) {
  								  $image->id = $grandchild['tagdata']; // tricky: in the xml file, IMAGE is used for the id
                  }
  								break;
  							case 'INDEX' :                  
                  if ( isset( $grandchild['tagdata'] ) ) {  								  
  									$image->index = $grandchild['tagdata'];
                  } else {
  									$image->index = strval( $i );
  								}
                  break;                  
                case 'IMAGEDATE' :  
                  $datetime = 0;            
                  if ( isset( $grandchild['tagdata'] ) ) {
                    $datetime = (int)$grandchild['tagdata'];
                  }                 
                  if ( $datetime != 0 ) {
                    $image->datetime = $datetime;
                  } else {
                    $changed = true;
                  }                     
                  break;
                default:
                  $key = strtolower( $grandchild['name'] );
                  if ( array_key_exists( $key, $image->extra_fields ) ) {                    
                    if ( isset( $grandchild['tagdata'] ) ) 
											$image->extra_fields[$key] = stripslashes( html_entity_decode( utf8_decode( $grandchild['tagdata'] ) ) );
                    if ( ! $lg_gallery->has_field( $key ) ) 
											$lg_gallery->add_field( $key, 'image' );
                  }
                  break;
  						}
            }
            if ( 0 == $image->datetime ) {               
              $image->datetime  =  @filemtime( $lg_gallery->root . $this->curdir . $image->image );
              if ( false === $image->datetime ) {
	              $image->datetime = time();
              }
              $changed = true; 
            }           
            if ( '' == $image->id ) {                    
              $k = intval( $lg_gallery->get_option( 'image_indexing' ) );
              $image->id = $k++;
              $lg_gallery->update_option( 'image_indexing' , strval( $k ) );
              $changed = true;                     
            }  
            $readfilter = apply_filters( 'lazyest_image_xml_read', array( 'image' => $image, 'changed' => $changed ) );
            $image = $readfilter['image'];
            $changed = $readfilter['changed'];
            $found = false;            
            foreach( $readfiles as $key => $readfile ) {
            	if ( $image->image == $readfile ) {
            		unset( $readfiles[$key] );
            		$found = true;
    					$i++;         
              $this->list[] = $image;
            		break;
            	}
            }
            if ( ! $found ) {
            	unset( $image );
            	$changed = true;
            }                       				
				  }
  			}	
   		}
      if (  0 < count( $readfiles ) ) { // files have been added after last write
        $changed = true;
        foreach( $readfiles as $filename ) {
          $image = $this->_new_item( $what );
          $image->image = $filename;
          $image->datetime  =  @filemtime( $lg_gallery->root . $this->curdir . $image->image );
          if ( false === $image->datetime ) {
            $image->datetime = time();
          }
          $image->index = strval( $i++ );                            
          $k = intval( $lg_gallery->get_option( 'image_indexing' ) );
          $image->id = $k++;
          $lg_gallery->update_option( 'image_indexing' , strval( $k ) );
          $image = apply_filters( 'lazyest_image_found', $image );
          $this->list[] = $image;
        }
      }
      unset( $readfiles );
  		unset( $arrXML );
  		unset( $objXML );
  	} else {
      if (  0 < count( $readfiles ) ) { // files have been found but no captions.xml
        $changed = true;
        foreach( $readfiles as $filename ) {
          $image = $this->_new_item( $what );
          $image->image = $filename;
					$image->datetime  =  @filemtime( $lg_gallery->root . $this->curdir . $image->image );
          if ( false === $image->datetime ) {
            $image->datetime = time();
          }
          $image->index = 0;                             
          $k = intval( $lg_gallery->get_option( 'image_indexing' ) );
          $image->id = $k++;
          $lg_gallery->update_option( 'image_indexing' , strval( $k ) );
          $image = apply_filters( 'lazyest_image_found', $image );
          $this->list[] = $image;
        }
      }
  	}
		
		if  ( false !== has_filter( 'lazyest_sort_images' ) ) 
  		$this->list = apply_filters( 'lazyest_sort_images', $this->list );
 	  else 
    	$this->sort();
    	
    if ( $changed ) {  
      $this->save();
    }
    return true;	     
  }
  
  
  function save() { 
    global $lg_gallery;
  	$xml_file = $lg_gallery->root . $this->curdir . 'captions.xml';
  	$handle = @fopen( $xml_file, 'wb' );
  	if ( false !== $handle ) { 	 
  		fwrite( $handle, "<?xml version='1.0' encoding='UTF-8' ?>\n" );
  		fwrite( $handle, "<data>\n" );
  		$this->write_xml( $handle );      
      if ( isset( $this->list ) ) {
        for ( $i = 0; $i < count( $this->list ); $i++ ) {
          $image = $this->list[$i];
          $image->write_xml( $handle );
        }      
      }
  		fwrite( $handle, "</data>" );
  		fclose( $handle );
  		$stat = stat(  $lg_gallery->root . $this->curdir );
  		$perms = $stat['mode'] & 0000666;
  		@chmod( $xml_file, $perms );
  	} else {
  	  return false; 
  	}
    return true;
  }
  
  
  function change() { 
    global $lg_gallery;
    if ( ! $this->can_save() ) return false;
  	$xml_file = $lg_gallery->root . $this->curdir . 'captions.xml';
  	$old_file = file_exists( $xml_file );
  	if ( $old_file ) {
  	 $this->load();
  	}
    $result = $this->save();
    return $result;
  }
  
  function _compare_f( $f1, $f2 ) {    
    global $lg_gallery;
    $how = $lg_gallery->get_option( 'sort_folders' );
    $comp1 = $comp2 = '0'; // prevent notices when option 'sort_folders' has not been set.
    $id = '';
    switch ( $how ) {
      case 'TRUE' :
      case 'DTRUE' : 
        $comp1 = $f1->curdir;
        $comp2 = $f2->curdir;
        $id = 'curdir';
        break;
      case 'CAPTION' :
      case 'DCAPTION' : 
        $comp1 = $f1->caption();
        $comp2 = $f2->caption();
        $id = 'caption';
        break;
      case 'FALSE' :
      case 'DFALSE' : 
        $comp1 = $f1->datetime;
        $comp2 = $f2->datetime;
        break;
      case 'MANUAL' :  
        $comp1 = $f1->order;
        $comp2 = $f2->order;
        break;  
    }       
    if ( $id == 'curdir' || $id == 'caption' ) {
      $comp1 = strtolower( $comp1 );      
      $comp2 = strtolower( $comp2 );
    }    
    if ( $comp1 == $comp2 ) {
      return 0;
    }    
    $result = ( $comp1 < $comp2 ) ? -1 : 1;
    return ( 'D' == $how[0] ) ? -$result : $result;
  }  
  
  /**
   * LazyestFolder::subfolders()
   * 
   * @param string $show
   * @return
   */
  function subfolders( $show='hidden' ) {
    global $lg_gallery;
    
    $location = $lg_gallery->root . $this->curdir;
    
	 	$flist = array();
		if ( $dir_content = @opendir( $location ) ) {  
			while ( false !== ( $dir_file = readdir( $dir_content ) ) ) {
        if ( $lg_gallery->valid_dir( $location . $dir_file ) ) {
          $subfolder = new LazyestFolder( $this->curdir . $dir_file );
          if ( false != $subfolder ) {
            $subfolder->open();      
            if ( ( ( $show == 'hidden') && ( $subfolder->visibility == 'hidden' ) ) || ( $subfolder->visibility != 'hidden' ) ) {
              $flist[] = $subfolder;
            }            
          }
        }  			 
			}
      @closedir( $dir_content );
		} else {
	    return false;
		}
		
		if ( false !== has_filter( 'lazyest_sort_folders' ) )
			apply_filters( 'lazyest_sort_folders', $flist );
		else
    	$flist = ( usort( $flist, array( &$this, '_compare_f' ) ) ) ? $flist : false;
    	
 		return $flist;
  }
  
  /**
   * LazyestFolder::list_folders()
   * 
   * @param string $show
   * @param string $text
   * @param string $link
   * @return void
   */
  function list_folders( $show='visible', $text='title', $link='frontend' ){
    global $lg_gallery;
    if ( $folders = $this->subfolders( $show ) ) {
      if ( 0 < count( $folders ) ) {
        foreach( $folders as $folder ) {          
          $folder->open();
          echo "\n<ul>\n";       
          $title = ( 'title' == $text ) ? $folder->caption() : htmlentities( $folder->dirname() );
          $href = ( 'frontend' == $link ) || ( 'widget' == $link ) ? $folder->uri( $link ) : 'admin.php?page=lazyest-filemanager&amp;folder=' . lg_nice_link( $folder->curdir );             
          echo "<li><a href=\"$href\">$title</a> ";
          if ( 'admin' == $link ) {
            echo sprintf( __( '(%s Images)',  'lazyest-gallery' ), $folder->count() );
          }
          $folder->list_folders( $show, $text, $link );
          echo "</li>\n";
          echo "</ul>\n";   
        }
      }
    } 
  } 
  
  
  /**
   * LazyestFolder::user_can_access()
   * 
   * @deprecated 1.1
   * @return bool
   */
  function user_can_access() {
    _deprecated_function(__FUNCTION__, '1.1', "LazyestFolder::user_can()" );    
  	return ( $this->user_can( 'viewer' ) ); 
  }
  
  /**
   * LazyestFolder::user_can()
   * Check if the current user has certain capabilities in this folder 
   * 
   * @param string $capability
   * @return
   */
  function user_can( $capability = '' ) {
    global $lg_gallery, $current_user;
    
    if ( current_user_can( 'manage_options' ) )
      return true;  // administrator should always have access
    get_currentuserinfo();
    if ( ! isset( $this->visibility ) )
    	$this->open();
    // check for private folder
		if ( ( 'private' == $this->visibility ) && ( $this->editor != $current_user->ID ) )
			return false;
		
		// check for acces by role	      
    switch ( $capability ) {
      case 'viewer':
        $roles = array();
        $roles[] = $this->viewer_level;      
        $up_dirs = explode( '/', untrailingslashit( $this->curdir ) );
        $path = '';        
        if ( 1 < count( $up_dirs ) ) {
          for ( $i = 0; $i < count( $up_dirs ) - 1; $i++ ) {
            $path .= trailingslashit( $up_dirs[$i] );
            $upfolder = new LazyestFolder( $path );
            $upfolder->open();
            $roles[] = $upfolder->viewer_level;
            unset( $upfolder );
          }  
        }
        $user_can = true;  
        foreach( $roles as $role ) {
          if ( ( 'everyone' == $role ) || ( '' == $role ) ) {
            $user_can = $user_can ? true : false;
          } else {            
            $cap = $lg_gallery->level_cap( $role );
            $user_can = $user_can ? current_user_can( $cap ) : false; 
          }
        }        
        return $user_can;
        break; 
      case 'editor':
        if ( -1 < $this->editor ) {
          return current_user_can( 'manage_lazyest_files' ) || ( $this->editor == $current_user->ID );
        } else {
          return current_user_can( 'manage_lazyest_files' ) || current_user_can( 'create_lazyest_folder');
        }
        break;
      case 'author':
        if ( 0 < count( $this->authors ) ) {
          return current_user_can( 'manage_lazyest_files' ) || ( in_array( $current_user->ID , $this->authors ) );
        } else {
          return current_user_can( 'manage_lazyest_files' ) || current_user_can( 'edit_lazyest_fields');
        } 
        break;
    }
  }
  
  /**
   * LazyestFolder::_subimages()
   * 
   * @param mixed $folders
   * @param mixed $folder
   * @return
   */
  function _subimages( $folders, $folder ) {
    $subfolders = $folder->subfolders();  
    if ( 0 < count( $subfolders ) ) {
      foreach ( $subfolders as $subfolder ) {          
        if ( ( 'visible' == $subfolder->visibility ) && $subfolder->user_can( 'viewer' ) ) {
          $folders[] = $subfolder;
          $folders = $this->_subimages( $folders, $subfolder );
        }
      }
    }
    return $folders;
  }
  
  /**
   * LazyestFolder::random_image()
   * 
   * @param string $sub
   * @param integer $count
   * @return
   */
  function random_image( $sub = 'root', $count = 1, $what = 'thumbs' ) {
    if ( ! $this->user_can(  'viewer' ) )
      return false;
    $this->load( $what );  
    $random_list = isset ( $this->list ) ? $this->list : array(); 
    $folders = $this->subfolders('visible');    
    if ( 'subfolders' == $sub ) {
      $folders = $this->_subimages( $folders, $this );
      if ( 0 < count( $folders ) ) {     
        foreach ( $folders as $folder ) {
          if ( $folder->user_can(  'viewer' ) ) {
            $folder->load( 'thumbs' );
            if ( 0 < count( $folder->list ) ) {
              foreach ( $folder->list as $image ) {
                $random_list[] = $image;
              }
            }
          }
          unset( $folder->list );
        }
      }
    }  
    $images_list = array();
    if ( 0 < count( $random_list ) ) {
      $count = ( $count > count( $random_list ) ) ? count( $random_list ) : $count;
      $counted = 0;
      if ( 1 == $count ) {
        $key = array_rand( $random_list );
        $images_list[] = $random_list[$key]; 
      } else {         
 	      while ( ( $counted < $count ) && ( 0 < count( $random_list ) ) ) { 
 	        $key = array_rand( $random_list ); 
          $image = $random_list[$key];
          $inlist = false;
          $i = 0;
          while ( $i < count( $images_list) ) {
            $listimage = $images_list[$i];
            if ( $listimage->id == $image->id ) {
              $inlist = true;
              break;
            }
            $i++;
          }
          if ( ! $inlist )  
            $images_list[] = $image;  
 	        $counted++;          
 	        unset( $random_list[$key] ); 
        }
      } 
		}    
    return $images_list;
  }
  
  /**
   * LazyestFolder::single_image()
   * 
   * @param mixed $filename
   * @return
   */
  function single_image( $filename, $what = 'images' ) {
  	global $lg_gallery;
    if ( ! file_exists( $lg_gallery->root . $this->curdir . $filename ) )
    	return false;
    $this->load( $what );    
    $single_image = false;
    if ( 0 < count( $this->list ) ) {
      foreach ( $this->list as $image ) {
        if ( $image->valid() && ( $image->image == $filename ) ) {
          $single_image = $image;
          break;
        }
      }
    }  
    return $single_image;
  }
  
  /**
   * LazyestFolder::icon()
   * returns the folder icon as array icon=>img src class=>img class
   * @since 1.0.0
   * @uses untrailingslashit()
   * @return array
   */
  function icon() {
  	global $lg_gallery;
  	
  	$folder_icon = array();    
    
    if ( ! $this->user_can(  'viewer' ) ) {
      $folder_icon['icon'] = $lg_gallery->plugin_url . '/images/folder-na.png';
      $folder_icon['class'] = 'category_icon';
      return $folder_icon;
    }
    
  	$dir_parts = explode( '/', untrailingslashit( $this->curdir ) );
  	$fil = $dir_parts[ count( $dir_parts ) -1 ];
  		
  	$default_icon = $lg_gallery->plugin_url . '/images/folder-icon.png';
  	
  	$the_icon = $default_icon;
  	$folder_icon['class'] = 'icon';
    $exts = array( '.gif', '.jpg', '.jpeg', '.png' );
    $the_ext = '';  
  	switch( $lg_gallery->get_option( 'folder_image' ) ) {
  		case 'icon':		
  			$folder_icon['icon'] = $the_icon;	
        $stub = $lg_gallery->root . $this->curdir . $fil;
        $stuba = $lg_gallery->address . $this->curdir . $fil;
        foreach( $exts as $ext ) {  
          if ( file_exists( $stub . $ext ) ) {
            $the_ext = $ext;
          }
          if ( file_exists( $stub . strtoupper( $ext ) ) )
            $the_ext = strtoupper( $ext );
        }
  			if ( '' != $the_ext ) {
  			  $thumb = new LazyestThumb( $this );
          $thumb->image = $fil . $the_ext; 
          $folder_icon['icon'] = $thumb->src();
  				$folder_icon['class'] = 'category_icon';
    		}	
  		break;
  	
  		case 'random_image':         
  			$folder_icon['icon'] = $default_icon;
  			$folder_icon['class'] = 'random_image';
        $sub = ( 'TRUE' == $lg_gallery->get_option( 'random_subfolder' ) ) ? 'subfolders' : 'root';
        $images = $this->random_image( $sub ); 
        if ( 0 < count( $images ) ) {          
          $thumb = $images[0];
          $folder_icon['icon'] = $thumb->src();
        }
  			break;  
  	}
		$folder_icon['class'] .= ' thumb';
		if ( isset( $thumb) &&
			( ( 'TRUE' != $lg_gallery->get_option( 'enable_cache' ) )  || 
				( 'TRUE' == $lg_gallery->get_option( 'async_cache' ) ) ) 
					&& ! file_exists( $thumb->loc() ) ) {
			$folder_icon['class'] .= ' lg_ajax';	
		}	
		unset( $thumb );
  	return apply_filters( 'lazyest_folder_icon', $folder_icon, $this->curdir );
  }
    
  /**
   * LazyestFolder::set_extra_field()
   * 
   * set the value for an extra field
   * this should be a string
   * 
   * @param string $index
   * @param string $value
   * @return void
   * @since 1.1.0
   */
  function set_extra_field( $index, $value='' ) {
    $this->extra_fields[$index] = $value;
  }
  
  /**
   * LazyestFolder::get_extra_field()
   * Returns an extra field by index name
   * 
   * @param string $index
   * @return string
   * @since 1.1.0
   */
  function get_extra_field( $index ) {
    $value = false;
    if ( isset($this->extra_fields[$index] ) ) {
      $value = $this->extra_fields[$index];
    }
    return $value;
  }
  
  /**
   * LazyestFolder::icon_div()
   * the <div> element in thumbnail view containing the folder icon
   * 
   * @return string
   * @since 1.1.0
   */
  function icon_div() {
    global $lg_gallery;
    $icon_div = '';
    $show_image = 'none' != ( $lg_gallery->get_option( 'folder_image' ) ) && ( 'hidden' != $this->visibility );
    
    if ( $show_image ) {        
      $folder_icon = $this->icon();
      
      $style ='';
      if ( 'TRUE' ==  $lg_gallery->get_option( 'table_layout') ) {
        $style = sprintf( ' style="min-height:%dpx;"',
          ( 'empty_folder' == $folder_icon['class'] ) ? '1' : (int)$lg_gallery->get_option( 'thumbheight' ) 
        );
      }
      
			$folderlink = apply_filters( 'lazyest_folder_link', $this->uri() );           
      $icon_div = sprintf ( '<div class="lg_folder_thumb_image"%s>%s<a class="fldrimg" href="%s" title="%s"><img src="%s" alt="%s" class="%s" /></a>%s<br /></div>',        
        $style,
        apply_filters( 'lazyest_before_folder_link', '' ),
        $folderlink,
        esc_attr( $this->title() ),
        $folder_icon['icon'],
        esc_attr( $this->title() ),
        $folder_icon['class'],
        apply_filters( 'lazyest_after_folder_link', '' )
      );
      
    }
    return $icon_div;
  }  
  
  /**
   * LazyestFolder::caption_div()
   * displays the <div> element in thumbnail view containing the folder caption
   * 
   * @return void
   */
  function caption_div() {
    global $lg_gallery;    
    $caption_div = sprintf( '<div class="lg_thumb_caption">%s<a class="fldrlink" href="%s"><span class="fldrcaption">%s</span></a>',    
      apply_filters( 'lazyest_before_folder_caption', '' ),
      $this->uri(),
      lg_html( $this->caption() ) 
    );                
    $subfoldernum = false;
    $include = false;
    $foldernum = true;
		$class = 'folder-count'; 
		$option = $lg_gallery->get_option( 'count_subfolders' );         
    switch ( $option ) {
      case 'separate' :
        $subfoldernum = true;
				$class = 'lg_foldernum';          
        break;
      case 'include' :
				$subfoldernm = false;				
				$class = 'lg_folder_allcount';
				break;  
      case 'nothing' :
        $foldernum = false;
        break;                
    } 
    if ( $foldernum ) { 
      $caption_div .= sprintf( '<br /><span class="%s" id="lg_tc_%s" title="%s">%s %s</span><br />',
      	$class,
        $this->id,
        urlencode( $this->curdir ), 
        $this->count(),
        esc_html( $lg_gallery->get_option( 'listed_as' ) )
      );
      if ( $subfoldernum ) {
        $caption_div .= sprintf( '<span class="lg_folder_subcount" id="lg_sc_%s" title="%s"></span>',
          $this->id,
          urlencode( $this->curdir )
        );              
      }
    }
    $caption_div .= sprintf( '%s</div>',
      apply_filters( 'lazyest_after_folder_caption', '' )
    );
    return $caption_div;   
  }
  
  /**
   * LazyestFolder::description_div()
   * returns <div> element in thumbnail view containing the folder description
   * 
   * @return void
   */
  function description_div() {
    global $lg_gallery;
    $description_div = '';
    if ( 'TRUE' == $lg_gallery->get_option( 'thumb_description' ) ) {
      $description_div = sprintf( '<div class="thumb_description">%s%s%s</div>',
        apply_filters( 'lazyest_before_folder_description', '', $this ),
        ( '' != $this->description() ) ? '<p>' . $this->description() . '</p>' : '',
        apply_filters( 'lazyest_after_folder_description', '', $this ) 
      );
    }
    return $description_div;   
  }
  
  /**
   * LazyestFolder::_cache_enabled()
   * Checks if either thumbs or slides cache is enabled
   * 
   * @internal
   * @since 1.1.0
   * @return bool
   * @uses WP get_currentuserinfo()
   * @uses WP current_user_can()
   */
  function cache_enabled() {
    global $lg_gallery;
    return ( 'TRUE' == $lg_gallery->get_option( 'enable_cache' ) ) || ( 'TRUE' == $lg_gallery->get_option( 'enable_slides_cache' ) );
  }
    
} // LazyestFolder

/**
 * LazyestXMLParser xml parser class
 * 
 * @access public
 */
class LazyestXMLParser {

	var $arrOutput = array();
	var $resParser;
	var $strXmlData;

	/**
	 * LazyestXMLParser::parse()
	 * 
	 * @param mixed $strInputXML
	 * @return
	 */
	function parse($strInputXML) {
		$this->resParser = xml_parser_create ();
		xml_parser_set_option($this->resParser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_set_object($this->resParser,$this);
		xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");

		xml_set_character_data_handler($this->resParser, "tagData");

		$f = fopen( $strInputXML, 'r' );
		$readok = true;
		
		while( ( $data = fread( $f, 4096 ) ) && $readok ) {
			$this->strXmlData = xml_parse($this->resParser,$data );
			if(! $this->strXmlData) {
				printf("XML error: %s at line %d in file %s <br />" ,
					xml_error_string(xml_get_error_code($this->resParser)),
					xml_get_current_line_number($this->resParser),
					$strInputXML );
				$readok = false;
			}
		}
		xml_parser_free($this->resParser);
		
		if ( $readok ) {
			return $this->arrOutput;
		} else {
			return null;
		}
	}
	/**
	 * LazyestXMLParser::tagOpen()
	 * 
	 * @param mixed $parser
	 * @param mixed $name
	 * @param mixed $attrs
	 * @return
	 */
	function tagOpen($parser, $name, $attrs) {
	
		$tag=array("name"=>$name,"attrs"=>$attrs);
		array_push($this->arrOutput,$tag);
	}

	/**
	 * LazyestXMLParser::tagData()
	 * 
	 * @param mixed $parser
	 * @param mixed $tagData
	 * @return
	 */
	function tagData($parser, $tagData) {
		if(trim($tagData)) {
			if(isset($this->arrOutput[count($this->arrOutput)-1]['tagdata'])) {
				$this->arrOutput[count($this->arrOutput)-1]['tagdata'] .= $tagData;
			}
			else {
				$this->arrOutput[count($this->arrOutput)-1]['tagdata'] = $tagData;
			}
		}
	}

	/**
	 * LazyestXMLParser::tagClosed()
	 * 
	 * @param mixed $parser
	 * @param mixed $name
	 * @return
	 */
	function tagClosed($parser, $name) {
		$this->arrOutput[count($this->arrOutput)-2]['children'][] = $this->arrOutput[count($this->arrOutput)-1];
		array_pop($this->arrOutput);
	}
} // LazyestXMLParser
?>
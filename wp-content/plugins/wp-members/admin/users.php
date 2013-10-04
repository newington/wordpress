<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the bulk user management page.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2013  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2013
 */


/**
 * User management panel
 *
 * Creates the bulk user management panel for the
 * Users > WP-Members page.
 *
 * @since 2.4
 */
function wpmem_admin_users()
{	
	// define variables
	$col_phone = ''; $col_country = ''; $user_action_msg = '';
	
	// check to see if we need phone and country columns
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ )
	{ 
		if( $wpmem_fields[$row][2] == 'country' && $wpmem_fields[$row][4] == 'y' ) { $col_country = true; }
		if( $wpmem_fields[$row][2] == 'phone1' && $wpmem_fields[$row][4] == 'y' ) { $col_phone = true; }
	}
	
	// should run other checks for expiration, activation, etc...
	
	// here is where we handle actions on the table...
	
	if( $_POST ) { 

		$action = ( isset( $_POST['action'] ) ) ? $_POST['action'] : false;
		$users  = ( isset( $_POST['users'] ) ) ? $_POST['users'] : false;

		switch( $action ) {
		
		case "activate":
			// find out if we need to set passwords
			$wpmem_fields = get_option( 'wpmembers_fields' );
			$chk_pass = false;
			for ( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
				if( $wpmem_fields[$row][2] == 'password' ) { $chk_pass = true; }
			}
			$x = 0;
			foreach( $users as $user ) {
				// check to see if the user is already activated, if not, activate
				if( ! get_user_meta( $user, 'active', true ) ) {
					wpmem_a_activate_user( $user, $chk_pass );
					$x++;
				}
			}
			$user_action_msg = sprintf( __( '%d users were activated.', 'wp-members' ), $x );
			break;
			
		case "export":
			update_option( 'wpmembers_export', $users );
			$user_action_msg = sprintf( __( 'Users ready to export, %s click here %s to generate and download a CSV.', 'wp-members' ),  '<a href="' . //WP_PLUGIN_URL . '/wp-members/wp-members-export.php" target="_blank">', '</a>' );
			WPMEM_DIR . '/admin/export.php" target="_blank">', '</a>' );
			break;
		
		}
		
	} ?>

	<div class="wrap">

		<div id="icon-users" class="icon32"><br /></div>
		<h2><?php _e( 'WP-Members Users', 'wp-members' ); ?>  <a href="user-new.php" class="button add-new-h2"><?php _e( 'Add New', 'wp-members' ); ?></a></h2>
		
	<?php if( $user_action_msg ) { ?>

		<div id="message" class="updated fade"><p><strong><?php echo $user_action_msg; ?></strong></p></div>

	<?php } ?>

		<form id="posts-filter" action="<?php echo $_SERVER['REQUEST_URI']?>" method="post">
	
		<div class="filter">
			<ul class="subsubsub">
			
			<?php
			
			// For now, I don't see a good way of working this for localization without a 
			// huge amount of additional programming (like a multi-dimensional array)
			$show = $lcas = $curr = '';
			$tmp  = array( "All", "Not Active", "Pending", "Trial", "Subscription", "Expired", "Not Exported" );
			$show = ( isset( $_GET['show'] ) ) ? $_GET['show'] : false; 
			
			for( $row = 0; $row < count( $tmp ); $row++ )
			{
				
				$link = "users.php?page=wpmem-users";
				if( $row != 0 ) {
				
					$lcas = strtolower( $tmp[$row] );
					$lcas = str_replace( " ", "", $lcas );
					$link.= "&#038;show=";
					$link.= $lcas;
					
					$curr = "";
						$curr = ( $show == $lcas ) ? ' class="current"' : '';
					
				} else {
				
					$curr = ( ! $show ) ? ' class="current"' : ''; 
					
				}
				
				$end = "";
				if( $row != 5 ) { $end = " |"; }

				$echolink = true;
				if( $lcas == "notactive" && WPMEM_MOD_REG != 1 ) { $echolink = false; }
				if( $lcas == "trial"     && WPMEM_USE_TRL != 1 ) { $echolink = false; }
				
				if( ( $lcas == "subscription" || $lcas == "expired" || $lacas == "pending" ) && WPMEM_USE_EXP != 1 ) { $echolink = false; }
				
				if( $echolink ) { echo "<li><a href=\"$link\"$curr>$tmp[$row] <span class=\"count\"></span></a>$end</li>"; }
			}

			?>
			</ul>
		</div>


	<?php
	
		// done with the action items, now build the page
		$users_per_page = 10;

		// workout the different queries, etc...
		if( ! $show ) {

			$result = count_users();


			$paged = ( isset( $_REQUEST['paged'] ) ) ? $_REQUEST['paged'] : 1;

			$arr = array(
				'show' => 'Total Users',
				'show_num' => $users_per_page,
				'total_users' =>  $result['total_users'],
				'pages' => ( ceil( ( $result['total_users'] ) / $users_per_page ) ),
				'paged' => $paged,
				'link' => "users.php?page=wpmem-users"
				);


			$offset = ( $paged == 1 ) ? 0 : ( ( $arr['paged'] * $users_per_page ) - $users_per_page );
			
			$args = array(
				'offset' => $offset,
				'number' => $users_per_page,
				'fields' => 'all'
				);
		
		} elseif( $show == 'notactive' ) {



		} elseif( $show == 'notexported' ) {

		}

			
		$users = get_users( $args ); // get_users_of_blog(); ?>		


		<?php wpmem_a_build_user_action( true, $arr ); ?>

		<table class="widefat fixed" cellspacing="0">
			<thead>
			<?php //$colspan = wpmem_a_build_user_tbl_head( $col_phone, $col_country ); ?>
			<?php $colspan = wpmem_a_build_user_tbl_head( array( 'phone'=>$col_phone, 'country'=>$col_country ) ); ?>
			</thead>

			<tfoot>
			<?php //$colspan = wpmem_a_build_user_tbl_head( $col_phone, $col_country ); ?>
			<?php $colspan = wpmem_a_build_user_tbl_head( array( 'phone'=>$col_phone, 'country'=>$col_country ) ); ?>
			</tfoot>

			<tbody id="users" class="list:user user-list">

			<?php	
			if( WPMEM_DEBUG == true ) { echo "<pre>\n"; print_r( $users ); echo "</pre>\n"; }
			$x=0; $class = '';
			foreach( $users as $user )
			{
				// are we filtering results? (active, trials, etc...)
				
				$chk_show = false; 
				switch( $show ) {
				case "notactive":
					$chk_show = ( get_user_meta( $user->ID, 'active', 'true' ) != 1 ) ? true : false;
					break;
				case "pending":
					$chk_exp_type = get_user_meta( $user->ID, 'exp_type', 'true' );
					$chk_show = ( $chk_exp_type == 'pending' ) ? true : false;
					break;
				case "trial":
					$chk_exp_type = get_user_meta( $user->ID, 'exp_type', 'true' );
					$chk_show = ( $chk_exp_type == 'trial' ) ? true : false;
					break;
				case "subscription":
					$chk_exp_type = get_user_meta( $user->ID, 'exp_type', 'true' );
					$chk_show = ( $chk_exp_type == 'subscription' ) ? true : false;
					break;
				case "expired":
					$chk_show = ( wpmem_chk_exp( $user->ID ) ) ? true : false; 
					break;
				case "notexported":
					$chk_show = ( get_user_meta( $user->ID, 'exported', 'true' ) != 1 ) ? true : false;
					break;
				}

				if( !$show || $chk_show == true ) {
					
					$class = ( $class == 'alternate' ) ? '' : 'alternate';

					echo "<tr id=\"{$user->ID}\" class=\"$class\">\n";
					echo "	<th scope='row' class='check-column'><input type='checkbox' name='users[]' id=\"user_{$user->ID}\" class='administrator' value=\"{$user->ID}\" /></th>\n";
					echo "	<td class=\"username column-username\" nowrap>\n";
					echo "		<strong><a href=\"user-edit.php?user_id={$user->ID}&#038;" . esc_attr( stripslashes( $_SERVER['REQUEST_URI'] ) ) . "\">" . $user->user_login . "</a></strong><br />\n";
					echo "	</td>\n";
					echo "	<td class=\"name column-name\" nowrap>" . htmlspecialchars( get_user_meta( $user->ID, 'first_name', 'true' ) ) . "&nbsp" . htmlspecialchars( get_user_meta( $user->ID, 'last_name', 'true' ) ) . "</td>\n";
					echo "	<td class=\"email column-email\" nowrap><a href='mailto:" . $user->user_email . "' title='E-mail: " . $user->user_email . "'>" . $user->user_email . "</a></td>\n";
					
					if( $col_phone == true ) {
						echo "	<td class=\"email column-email\" nowrap>" . htmlspecialchars( get_user_meta( $user->ID, 'phone1', 'true' ) ) . "</td>\n";
					}
					
					if( $col_country == true ) {
						echo "	<td class=\"email column-email\" nowrap>" . htmlspecialchars( get_user_meta( $user->ID, 'country', 'true' ) ) . "</td>\n";
					}
					
					if( WPMEM_MOD_REG == 1 ) { 
						echo "	<td class=\"role column-role\" nowrap>";
						if( get_user_meta( $user->ID, 'active', 'true' ) != 1 ) { _e( 'No', 'wp-members' ); }
						echo "</td>\n";
					}
					
					if( WPMEM_USE_EXP == 1 ) {
						if( WPMEM_USE_TRL == 1 ) {
							echo "	<td class=\"email column-email\" nowrap>"; echo ucfirst( get_user_meta( $user->ID, 'exp_type', true ) ); echo "</td>\n";
						}
						echo "	<td class=\"email column-email\" nowrap>"; echo get_user_meta( $user->ID, 'expires', true ); echo "</td>\n";
					}
					echo "</tr>\n"; $x++;
				}
			} 
			
			if( $x == 0 ) { echo "<tr><td colspan=\"$colspan\">"; _e( 'No users matched your criteria', 'wp-members' ); echo "</td></tr>"; } ?>

		</table>

		</form>
		
		<br />
		<p>This button exports the full user list. To export based on other criteria, use the form above.</p>
		<form method="link" action="<?php echo WPMEM_DIR . 'admin/export-full.php'; ?>">
			<input type="submit" class="button-secondary" value="Export All Users">
		</form>
	</div>
<?php
}


/**
 * builds the user action dropdown
 *
 * @param bool  $top
 * @param array $arr
 *
 * @since 2.4
 */
function wpmem_a_build_user_action( $top, $arr )
{ ?>
	<div class="tablenav<?php if( $top ){ echo ' top'; }?>">
		<div class="alignleft actions">
			<select name="action<?php if( !$top ) { echo '2'; } ?>">
				<option value="" selected="selected"><?php _e('Bulk Actions', 'wp-members'); ?></option>
			<?php if (WPMEM_MOD_REG == 1) { ?>
				<option value="activate"><?php _e('Activate', 'wp-members'); ?></option>
			<?php } ?>
				<option value="export"><?php _e('Export', 'wp-members'); ?></option>
			</select>
			<input type="submit" value="<?php _e('Apply', 'wp-members'); ?>" name="doaction" id="doaction" class="button-secondary action" />
		</div>
		<?php if( $arr['show'] == 'Total Users' ) { ?>
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo $arr['show'] . ': ' . $arr['total_users']; ?></span>
		<?php if( $arr['total_users'] > $arr['show_num'] ) { ?>
			<span class="pagination-links">
				<a class="first-page<?php if( $arr['paged'] == 1 ){ echo ' disabled'; } ?>" title="Go to the first page" href="<?php echo $arr['link']; ?>">&laquo;</a>
				<a class="prev-page<?php if( $arr['paged'] == 1 ){ echo ' disabled'; } ?>" title="Go to the previous page" href="<?php echo $arr['link']; ?>&#038;paged=<?php echo ( $arr['paged'] - 1 ); ?>">&lsaquo;</a>
				
				<span class="paging-input"><input class="current-page" title="Current page" type="text" name="paged" value="<?php echo $arr['paged']; ?>" size="1" /> of <span class="total-pages"><?php echo $arr['pages']; ?></span></span>
				
				<a class="next-page<?php if( $arr['paged'] == $arr['pages'] ){ echo ' disabled'; } ?>" title="Go to the next page" href="<?php echo $arr['link']; ?>&#038;paged=<?php echo ( $arr['paged'] + 1 ); ?>">&rsaquo;</a>
				<a class="last-page<?php if( $arr['paged'] == $arr['pages'] ){ echo ' disabled'; } ?>" title="Go to the last page" href="<?php echo $arr['link']; ?>&#038;paged=<?php echo $arr['pages']; ?>">&raquo;</a>
			</span>
		<?php } ?>
		</div>
		<?php } ?>
		<br class="clear" />
	</div>
<?php 	
}


/**
 * builds the user management table heading
 *
 * @since 2.4
 *
 * @param array $args
 */
function wpmem_a_build_user_tbl_head( $args )
{
	$arr = array( 'Username', 'Name', 'E-mail', 'Phone', 'Country', 'Activated?', 'Subscription', 'Expires' ); ?>

	<tr class="thead">
		<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
	<?php $c = 1; 
	foreach( $arr as $val ) { 

		$showcol = false;
		switch ($val) {
		case "Phone":
			$showcol = ( $args['phone'] == true ) ? true : false; 
			break;
		case "Country":
			$showcol = ( $args['country'] == true ) ? true : false; 
			break;
		case "Activated?":
			$showcol = ( WPMEM_MOD_REG == 1 ) ? true : false; 
			break;
		case "Subscription":
			$showcol = ( WPMEM_USE_EXP == 1 && WPMEM_USE_TRL == true ) ? true : false;
			break;
		case "Expires":
			$showcol = ( WPMEM_USE_EXP == 1 ) ? true : false;
			break;
		default:
			$showcol = true;
			break;
		} 		
		
		echo ( $showcol == true ) ? '<th scope="col" class="manage-column" style="">' .  $val . '</th>' : '';

		$c++; 
	} ?>
	</tr><?php 
	return $c;
}


/**
 * WP-Members NEW users.php functions
 *
 * The following code will replace the above users.php.
 * For now, we will run both together and deprecate the old
 * code in a future version.
 */


/**
 * WP-Members Admin Functions
 *
 * Functions to manage the Users > All Users page.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2013  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2013
 */


/**
 * Actions and filters
 */
add_action( 'admin_footer-users.php', 'wpmem_bulk_user_action' );
add_action( 'load-users.php', 'wpmem_users_page_load' );
add_action( 'admin_notices', 'wpmem_users_admin_notices' );
add_filter( 'views_users', 'wpmem_users_views' );
add_filter( 'manage_users_columns', 'wpmem_add_user_column' );
add_action( 'manage_users_custom_column',  'wpmem_add_user_column_content', 10, 3 );
if( WPMEM_MOD_REG == 1 ) {
	add_filter( 'user_row_actions', 'wpmem_insert_activate_link', 10, 2 );
}


/**
 * Function to add activate/export to the bulk dropdown list
 *
 * @since 2.8.2
 */
function wpmem_bulk_user_action()
{ ?>
    <script type="text/javascript">
      jQuery(document).ready(function() {
	<?php if( WPMEM_MOD_REG == 1 ) { ?>
        jQuery('<option>').val('activate').text('<?php _e('Activate')?>').appendTo("select[name='action']");
	<?php } ?>
		jQuery('<option>').val('export').text('<?php _e('Export')?>').appendTo("select[name='action']");
	<?php if( WPMEM_MOD_REG == 1 ) { ?>
        jQuery('<option>').val('activate').text('<?php _e('Activate')?>').appendTo("select[name='action2']");
	<?php } ?>
		jQuery('<option>').val('export').text('<?php _e('Export')?>').appendTo("select[name='action2']");
      });
    </script>
    <?php
}


/**
 * Function to add activate link to the user row action
 *
 * @since 2.8.2
 *
 * @param  array $actions
 * @param  $user_object
 * @return array $actions
 */
function wpmem_insert_activate_link( $actions, $user_object ) {
    if( current_user_can( 'edit_users', $user_object->ID ) ) {
	
		$var = get_user_meta( $user_object->ID, 'active', true );
		
		if( $var != 1 ) {
			$url = "users.php?action=activate-single&amp;user=$user_object->ID";
			$url = wp_nonce_url( $url, 'activate-user' );
			$actions['activate'] = '<a href="' . $url . '">Activate</a>';
		}
	}
    return $actions;
}


/**
 * Function to handle bulk actions at page load
 *
 * @since 2.8.2
 *
 * @uses WP_Users_List_Table
 */
function wpmem_users_page_load()
{
	$wp_list_table = _get_list_table( 'WP_Users_List_Table' );
	$action = $wp_list_table->current_action();
	
	if( $action == 'activate' || 'activate-single' ) {
		// find out if we need to set passwords
		$chk_pass = false;
		$wpmem_fields = get_option( 'wpmembers_fields' );
		for ( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
			if( $wpmem_fields[$row][2] == 'password' ) { $chk_pass = true; }
		}
	}

	switch( $action ) {
		
	case 'activate':
		
		/** validate nonce */
		check_admin_referer( 'bulk-users' );
		
		/** get the users */
		$users = $_REQUEST['users'];
		
		/** update the users */
		$x = 0;
		foreach( $users as $user ) {
			// check to see if the user is already activated, if not, activate
			if( ! get_user_meta( $user, 'active', true ) ) {
				wpmem_a_activate_user( $user, $chk_pass );
				$x++;
			}
		}
		
		/** set the return message */
		$sendback = add_query_arg( array('activated' => $x . ' users activated' ), $sendback );
		
		break;
		
	case 'activate-single':
		
		/** validate nonce */
		check_admin_referer( 'activate-user' );
		
		/** get the users */
		$users = $_REQUEST['user'];

		/** check to see if the user is already activated, if not, activate */
		if( ! get_user_meta( $users, 'active', true ) ) {
			
			wpmem_a_activate_user( $users, $chk_pass );
			
			/** get the user data */
			$user_info = get_userdata( $users );

			/** set the return message */
			$sendback = add_query_arg( array('activated' => "$user_info->user_login activated" ), $sendback );
		
		} else {

			/** get the return message */
			$sendback = add_query_arg( array('activated' => "That user is already active" ), $sendback );
		
		}
		
		break;
		
	case 'show':
		
		add_action( 'pre_user_query', 'wpmem_a_pre_user_query' );
		return;
		break;
		
	case 'export':

		$users  = ( isset( $_REQUEST['users'] ) ) ? $_REQUEST['users'] : false;
		update_option( 'wpmembers_export', $users );
		
		global $user_action_msg;
		$user_action_msg = sprintf( __( 'Users ready to export, %s click here %s to generate and download a CSV.', 'wp-members' ),  '<a href="' . WPMEM_DIR . '/admin/export.php" target="_blank">', '</a>' );
		
		return;
		break;
		
	default:
		return;
		break;

	}

	/** if we did not return already, we need to wp_redirect */
	wp_redirect( $sendback );
	exit();

}


/**
 * Function to echo admin update message
 *
 * @since 2.8.2
 */
function wpmem_users_admin_notices()
{    
	global $pagenow, $user_action_msg;
	if( $pagenow == 'users.php' && isset( $_REQUEST['activated'] ) ) {
		$message = $_REQUEST['activated'];
		echo "<div class=\"updated\"><p>{$message}</p></div>";
	}

	if( $user_action_msg ) {
		echo "<div class=\"updated\"><p>{$user_action_msg}</p></div>";
	}
}


/**
 * Function to add user views to the top list
 *
 * @since 2.8.2
 *
 * @param  array $views
 * @return array $views
 */
function wpmem_users_views( $views )
{
	$arr = array();	
	if( defined( 'WPMEM_USE_EXP' ) && WPMEM_USE_EXP == 1 ) { $arr[] = 'Pending'; }
	if( defined( 'WPMEM_USE_TRL' ) && WPMEM_USE_TRL == 1 ) { $arr[] = 'Trial'; }
	if( defined( 'WPMEM_USE_EXP' ) && WPMEM_USE_EXP == 1 ) { $arr[] = 'Subscription'; $arr[] = 'Expired'; }
	if( defined( 'WPMEM_MOD_REG' ) && WPMEM_MOD_REG == 1 ) { $arr[] = 'Not Active'; }
	$arr[] = 'Not Exported';
	$show = ( isset( $_GET['show'] ) ) ? $_GET['show'] : false;
	
	for( $row = 0; $row < count( $arr ); $row++ )
	{
		$link = "users.php?action=show&amp;show=";
		$lcas = str_replace( " ", "", strtolower( $arr[$row] ) );
		$link.= $lcas;
		$curr = ( $show == $lcas ) ? ' class="current"' : '';
		
		$echolink = true;
		if( $lcas == "notactive" && WPMEM_MOD_REG != 1 ) { $echolink = false; }
		
		if( $echolink ) { $views[$lcas] = "<a href=\"$link\" $curr>$arr[$row] <span class=\"count\"></span></a>"; }
	}

	/** @todo if $show, then run function search query for the users */

	return $views;
}


/**
 * Function to add custom user columns to the user table
 *
 * @since 2.8.2
 *
 * @param  array $columns
 * @return array $columns
 */
function wpmem_add_user_column( $columns ) 
{
	global $wpmem_user_columns;
	$wpmem_user_columns = get_option( 'wpmembers_utfields' );
	
	if( $wpmem_user_columns ) {
		foreach( $wpmem_user_columns as $key => $val ) {

			if( $key == 'active' ) {
			
				if( WPMEM_MOD_REG == 1 ) {
					$columns[$key] = $val;
				}
			
			} else {
				$columns[$key] = $val;
			}
		}
	}
	
	return $columns;
} 


/**
 * Function to add the user content to the custom column
 *
 * @since 2.8.2
 * 
 * @param $value
 * @param $column_name
 * @param $user_id
 * @return The user value for the custom column
 */
function wpmem_add_user_column_content( $value, $column_name, $user_id ) {

	// is the column a WP-Members column?
	global $wpmem_user_columns;
	$is_wpmem = ( is_array( $wpmem_user_columns ) && array_key_exists( $column_name, $wpmem_user_columns ) ) ? true : false;
	
	if( $is_wpmem ) {
	
		switch( $column_name ) {
		
		case 'active':
			if( WPMEM_MOD_REG == 1 ) {
			/**
			 * If the column is "active", then return the value or empty.
			 * Returning in here keeps us from displaying another value.
			 */
				return ( get_user_meta( $user_id , 'active', 'true' ) != 1 ) ? __( 'No', 'wp-members' ) : '';
			} else {
				return;
			}
			break;

		case 'user_url':
		case 'user_registered':
			/**
			 * Unlike other fields, website/url is not a meta field
			 */
			$user_info = get_userdata( $user_id );
			return $user_info->$column_name;
			break;
			
		default:
			return get_user_meta( $user_id, $column_name, true );
			break;
		}
	
	}
	
	return $value;
}


/**
 * Activates a user
 *
 * If registration is moderated, sets the activated flag 
 * in the usermeta. Flag prevents login when WPMEM_MOD_REG
 * is true (1). Function is fired from bulk user edit or
 * user profile update.
 *
 * @since 2.4
 *
 * @uses do_action Calls 'wpmem_user_activated' action
 *
 * @param int  $user_id
 * @param bool $chk_pass
 * @uses $wpdb WordPress Database object
 */
function wpmem_a_activate_user( $user_id, $chk_pass = false )
{
	// define new_pass
	$new_pass = '';
	
	// If passwords are user defined skip this
	if( ! $chk_pass ) {
		// generates a password to send the user
		$new_pass = wp_generate_password();
		$new_hash = wp_hash_password( $new_pass );
		
		// update the user with the new password
		global $wpdb;
		$wpdb->update( $wpdb->users, array( 'user_pass' => $new_hash ), array( 'ID' => $user_id ), array( '%s' ), array( '%d' ) );
	}
	
	// if subscriptions can expire, set the user's expiration date
	if( WPMEM_USE_EXP == 1 ) { wpmem_set_exp( $user_id ); }

	// generate and send user approved email to user
	require_once( WPMEM_PATH . 'wp-members-email.php' );
	wpmem_inc_regemail( $user_id, $new_pass, 2 );
	
	// set the active flag in usermeta
	update_user_meta( $user_id, 'active', 1 );
	
	do_action( 'wpmem_user_activated', $user_id );
	
	return;
}


/**
 * Deactivates a user
 *
 * Reverses the active flag from the activation process
 * preventing login when registration is moderated.
 *
 * @since 2.7.1
 *
 * @param int $user_id
 */
function wpmem_a_deactivate_user( $user_id ) {
	update_user_meta( $user_id, 'active', 0 );
}


/**
 * Adjusts user query based on custom views
 *
 * @since 2.8.3
 *
 * @param $user_search
 */
function wpmem_a_pre_user_query( $user_search ) 
{
	global $wpdb;
	$show = $_GET['show'];	
	switch ( $show ) {
	
		case 'notactive':        
		case 'notexported':
			$key = ( $show == 'notactive' ) ? 'active' : 'exported';
			$replace_query = "WHERE 1=1 AND {$wpdb->users}.ID NOT IN (
			 SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta 
				WHERE {$wpdb->usermeta}.meta_key = \"$key\"
				AND {$wpdb->usermeta}.meta_value = '1' )";
			break;
			
		case 'trial':
		case 'subscription':			
			$replace_query = "WHERE 1=1 AND {$wpdb->users}.ID IN (
			 SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta 
				WHERE {$wpdb->usermeta}.meta_key = 'exp_type'
				AND {$wpdb->usermeta}.meta_value = \"$show\" )";
			break;
			
		case 'pending': 		
			$replace_query = "WHERE 1=1 AND {$wpdb->users}.ID IN (
			 SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta 
				WHERE {$wpdb->usermeta}.meta_key = 'exp_type'
				AND {$wpdb->usermeta}.meta_value = \"$show\" )";
			break;

			
		case 'expired':
			//$chk_show = ( wpmem_chk_exp( $user->ID ) ) ? true : false; // if( wpmem_chk_exp( $user->ID ) ) { $chk_show = true; }
			
			$replace_query = "WHERE 1=1 AND {$wpdb->users}.ID IN (
			 SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta 
				WHERE {$wpdb->usermeta}.meta_key = 'expires'
				AND STR_TO_DATE( {$wpdb->usermeta}.meta_value, '%d,%m,%Y' ) < CURDATE() )";
			
			
			break;
	}
	
	$user_search->query_where = str_replace( 'WHERE 1=1', $replace_query,	$user_search->query_where );
}
?>
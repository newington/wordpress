<?php
/**
 * Functions and methods to remove all settings for the Active Directory
 * 		Authentication Integration plugin for WordPress
 * @package WordPress
 * @subpackage ADAuthInt
 * @version 0.6
 */
if( !class_exists( 'ADAuthInt_Plugin' ) )
	require_once( '../class-active-directory-authentication-integration.php' );
?>
<div class="wrap">
	<h3>Delete AD Authentication Integration Options</h3>
<?php
if( current_user_can( 'manage-options' ) ) {
	if( isset( $_GET['options-action'] ) && $_GET['options-action'] == __('Yes, I want to delete all options', ADAUTHINT_TEXT_DOMAIN ) ) {
		if( !isset( $ADAuthInt_Plugin_Obj ) ) {
			$ADAuthInt_Plugin_Obj = new ADAuthInt_Plugin;
		}
		foreach( $ADAuthInt_Plugin_Obj->subtitles as $optgroupname=>$title ) {
			if( ADAI_IS_NETWORK_ACTIVE ) {
				$exists = get_site_option( $optgroupname );
				if( $exists )
					$deleted = delete_site_option( $optgroupname );
			}
			else {
				$exists = get_option( $optgroupname );
				if( $exists )
					$deleted = delete_option( $optgroupname );
			}
			echo ( $deleted && $exists ) ?
				'<p>The ' . $title . ' set of options has been removed from the database successfully.</p>' : ( ( !$exists ) ?
					'<p>The ' . $title . ' set of options did not exist in the database, so it did not need to be removed.</p>' :
					'<p>There was an unspecified error removing the ' . $title . ' set of options from the database. Please try again.</p>' );
		}
?>
	<h3>Note:</h3>
    <p>If you visit the settings page for this plug-in, all options will now be restored to the original default settings.</p>
<?php
	} else {
?>
	<p>Are you sure you want to delete all of the settings for this plug-in? There is no way to restore these settings once you have done so.</p>
    <form action="" method="get">
    	<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>"/>
        <p class="submit">
	        <input type="submit" class="button-primary" value="<?php _e( 'Yes, I want to delete all options', ADAUTHINT_TEXT_DOMAIN ) ?>" name="options-action"/>
        </p>
        <p class="submit">
	        <input type="submit" class="button-primary" value="<?php _e( 'No, please take me to the options page', ADAUTHINT_TEXT_DOMAIN ) ?>"/>
        </p>
    </form>
<?php
	}
} else {
	echo '<p>You do not have the proper permissions to perform this action.</p>';
}
?>
</div>
<?php

/**
 * BuddyPress - Create Blog
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

get_header( 'buddypress' ); ?>

<!-- #page-header -->
<div id="page-header" class="clearfix">
<div class="container">
<div id="page-header-content" class="clearfix">
<h1><?php _e( 'Create a Site', 'buddypress' ); ?> &nbsp;<a class="button" href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_blogs_root_slug() ); ?>"><?php _e( 'Site Directory', 'buddypress' ); ?></a></h1>
</div>
</div>
</div>
<!-- /#page-header -->

	<?php do_action( 'bp_before_directory_blogs_content' ); ?>

<!-- #site-container -->
<div id="site-container" class="clearfix">

<!-- #primary -->
<div id="primary" class="sidebar-off clearfix"> 

<!-- #content -->
<div id="content" role="main">
		
		<?php do_action( 'bp_before_create_blog_content_template' ); ?>

		<?php do_action( 'template_notices' ); ?>

		<?php do_action( 'bp_before_create_blog_content' ); ?>

		<?php if ( bp_blog_signup_enabled() ) : ?>

			<?php bp_show_blog_signup_form(); ?>

		<?php else: ?>

			<div id="message" class="info">
				<p><?php _e( 'Site registration is currently disabled', 'buddypress' ); ?></p>
			</div>

		<?php endif; ?>

		<?php do_action( 'bp_after_create_blog_content' ); ?>
		
		<?php do_action( 'bp_after_create_blog_content_template' ); ?>

</div>
<!-- /#content -->

<?php do_action( 'bp_after_directory_blogs_content' ); ?>

<?php //get_sidebar( 'buddypress' ); ?>

</div>
<!-- /#primary -->

</div>
<!-- /#site-container -->

<?php get_footer( 'buddypress' ); ?>


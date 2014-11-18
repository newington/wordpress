<?php get_header( 'buddypress' ); ?>

<?php 
$st_page_sidebar_pos = '';	
$st_page_sidebar_pos = get_post_meta( get_the_ID(), '_st_page_sidebar', true ); 
if (empty( $st_page_sidebar_pos )) {
$st_page_sidebar_pos = 'right';	
}
?>

<!-- #page-header -->
<div id="page-header" class="clearfix">
<div class="container">
<div id="page-header-content" class="clearfix">

<h1><?php _e( 'Groups Directory', 'buddypress' ); ?></h1>

</div>
</div>
</div>
<!-- /#page-header -->

<!-- #site-container -->
<div id="site-container" class="clearfix">

<!-- #primary -->
<div id="primary" class="sidebar-<?php echo $st_page_sidebar_pos; ?> clearfix"> 

<!-- #content -->
<div id="content" role="main">

			<?php if ( bp_has_groups() ) : while ( bp_groups() ) : bp_the_group(); ?>

			<?php do_action( 'bp_before_group_plugin_template' ); ?>

			<div id="item-header">
				<?php locate_template( array( 'groups/single/group-header.php' ), true ); ?>
			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
					<ul>
						<?php bp_get_options_nav(); ?>

						<?php do_action( 'bp_group_plugin_options_nav' ); ?>
					</ul>
				</div>
			</div><!-- #item-nav -->

			<div id="item-body">

				<?php do_action( 'bp_before_group_body' ); ?>

				<?php do_action( 'bp_template_content' ); ?>

				<?php do_action( 'bp_after_group_body' ); ?>
			</div><!-- #item-body -->

			<?php do_action( 'bp_after_group_plugin_template' ); ?>

			<?php endwhile; endif; ?>

</div>
<!-- /#content -->

<?php if ($st_page_sidebar_pos != 'off') {
get_sidebar( 'buddypress' );
} ?>

</div>
<!-- /#primary -->

</div>
<!-- /#site-container -->

<?php get_footer( 'buddypress' ); ?>
<?php get_header( 'buddypress' ); ?>

<!-- #page-header -->
<div id="page-header" class="clearfix">
<div class="container">
<div id="page-header-content" class="clearfix">

<h1><?php _e( 'Members', 'buddypress' ); ?></h1>

</div>
</div>
</div>
<!-- /#page-header -->

<!-- #site-container -->
<div id="site-container" class="clearfix">

<!-- #primary -->
<div id="primary" class="sidebar-off clearfix"> 

<!-- #content -->
<section id="content" role="main">

<?php do_action( 'template_notices' ); ?>

<div class="activity no-ajax" role="main">
	<?php if ( bp_has_activities( 'display_comments=threaded&show_hidden=true&include=' . bp_current_action() ) ) : ?>

		<ul id="activity-stream" class="activity-list item-list">
		<?php while ( bp_activities() ) : bp_the_activity(); ?>

			<?php locate_template( array( 'activity/entry.php' ), true ); ?>

		<?php endwhile; ?>
		</ul>

	<?php endif; ?>
</div>

</section>
<!-- /#content -->

</div>
<!-- /#primary -->

</div>
<!-- /#site-container -->

<?php get_footer( 'buddypress' ); ?>
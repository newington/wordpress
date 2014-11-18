<?php

/**
 * Template Name: bbPress - Topic Tags
 *
 * @package bbPress
 * @subpackage Theme
 */

get_header(); ?>

<?php $st_page_sidebar_pos = get_post_meta( $post->ID, '_st_page_sidebar', true ); ?>

<!-- #page-header -->
<div id="page-header" class="clearfix">
<div class="container">
<div id="page-header-content" class="clearfix">
<h1><?php the_title(); ?></h1>
<?php if (get_post_meta( $post->ID, '_st_page_tagline', true )) { ?>
<p><?php echo get_post_meta( $post->ID, '_st_page_tagline', true ); ?></p>
<?php } ?>
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
  
  <?php if (!get_post_meta( $post->ID, '_st_page_breadcrumbs', true )) { ?>
<!-- #breadcrumbs -->
<div id="page-subnav" class="clearfix">
<div class="container">
<?php 
	$st_bbpress_breadcrumbs_args = array(
			// Modify default BBPress Breadcrumbs
			'before'          => '<nav class="bbp-breadcrumb">',
			'after'           => '</nav>',
			'sep'             => __( '&frasl;', 'bbpress' ),
	);
	bbp_breadcrumb($st_bbpress_breadcrumbs_args); ?>
</div>
</div>
<!-- /#breadcrumbs -->
<?php } ?>

	<?php do_action( 'bbp_before_main_content' ); ?>

	<?php do_action( 'bbp_template_notices' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<div id="bbp-topic-tags" class="bbp-topic-tags">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-content">

				<?php get_the_content() ? the_content() : _e( '<p>This is a collection of tags that are currently popular on our forums.</p>', 'bbpress' ); ?>

				<div id="bbpress-forums">

					<?php bbp_breadcrumb(); ?>

					<div id="bbp-topic-hot-tags">

						<?php wp_tag_cloud( array( 'smallest' => 9, 'largest' => 38, 'number' => 80, 'taxonomy' => bbp_get_topic_tag_tax_id() ) ); ?>

					</div>
				</div>
			</div>
		</div><!-- #bbp-topic-tags -->

	<?php endwhile; ?>

	<?php do_action( 'bbp_after_main_content' ); ?>

  </div>
  <!-- #content -->

  <?php if ($st_page_sidebar_pos != 'off') {
  get_sidebar();
  } ?>

</div>
<!-- /#primary -->

</div>
<!-- /#site-container -->

<?php get_footer(); ?>

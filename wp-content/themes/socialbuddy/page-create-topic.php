<?php

/**
 * Template Name: bbPress - Create Topic
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

		<div id="bbp-new-topic" class="bbp-new-topic">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-content">

				<?php the_content(); ?>

				<?php bbp_get_template_part( 'form', 'topic' ); ?>

			</div>
		</div><!-- #bbp-new-topic -->

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

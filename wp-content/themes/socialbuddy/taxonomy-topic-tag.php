<?php

/**
 * Topic Tag
 *
 * @package bbPress
 * @subpackage Theme
 */

get_header(); ?>

<?php 
// Get position of sidebar
$st_forum_sidebar_position = of_get_option('st_forum_sidebar');
?>

<?php get_template_part( 'page-header', 'forums' ); 	?>

<!-- #site-container -->
<div id="site-container" class="clearfix">

<!-- #primary -->
<div id="primary" class="sidebar-<?php echo $st_forum_sidebar_position; ?> clearfix"> 

  <!-- #content -->
  <div id="content" role="main">
  
  <?php get_template_part( 'page-subheader', 'forums' ); 	?>

	<?php do_action( 'bbp_before_main_content' ); ?>

	<?php do_action( 'bbp_template_notices' ); ?>

	<div id="topic-tag" class="bbp-topic-tag">
		<div class="entry-content">

			<?php bbp_get_template_part( 'content', 'archive-topic' ); ?>

		</div>
	</div><!-- #topic-tag -->

	<?php do_action( 'bbp_after_main_content' ); ?>

</div>
<!-- /#content -->

<?php if ($st_forum_sidebar_position != 'off') {
  get_sidebar('bbpress');
  } ?>

</div>
<!-- /#primary -->

</div>
<!-- /#site-container -->

<?php get_footer(); ?>
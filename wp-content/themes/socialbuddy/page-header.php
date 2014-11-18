<!-- #page-header -->
<div id="page-header" class="clearfix">
<div class="container">
<div id="page-header-content" class="clearfix">
<?php if ( is_search() ) { ?>

<h1><?php printf( __( 'Search Results: %s', 'framework' ), get_search_query() ); ?></h1>

<?php } elseif ( is_author() ) { ?>

<h1><?php printf( __( 'Author: %s', 'framework' ), get_the_author() ); ?></h1>

<?php } elseif ( is_archive() ) { ?>

<h1><?php echo of_get_option('st_blog_title') ?></h1><span>/</span><h2><?php 
if ( is_day() ) {
								echo get_the_date();

							} elseif ( is_month() ) {
								echo get_the_date( 'F Y' );

							} elseif ( is_year() ) {
								echo get_the_date( 'Y' );

							} else {
								_e( 'Archives', 'framework' );

							}
							?>
</h2>

<?php } elseif ( is_category() ) { ?>

<h1><?php echo of_get_option('st_blog_title') ?></h1><span>/</span><h2><?php single_cat_title(); ?></h2>

<?php } elseif ( is_tag() ) { ?>

<h1><?php echo of_get_option('st_blog_title') ?></h1><span>/</span><h2><?php single_tag_title(); ?></h2>

<?php } elseif ( is_home() ||  is_single() )  { ?>

<h1><?php echo of_get_option('st_blog_title') ?></h1>

<?php } else { ?>

<h1><?php the_title(); ?></h1>

<?php }  ?>
</div>
</div>
</div>
<!-- /#page-header -->
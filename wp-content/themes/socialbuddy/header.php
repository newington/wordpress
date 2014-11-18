<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php wp_title('-', true, 'right'); ?><?php bloginfo('name'); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php get_template_part('header', 'CSS'); ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php if ( has_nav_menu( 'primary-nav' ) ) { ?>
<!-- #primary-nav-mobile -->
<nav id="primary-nav-mobile">
<a class="menu-toggle" href="#"></a>
<?php wp_nav_menu( array('theme_location' => 'primary-nav', 'container' => false, 'menu_class' => 'clearfix', 'menu_id' => 'mobile-menu', )); ?>
</nav>
<!-- /#primary-nav-mobile -->
<?php } ?>


<!-- #header -->
<header id="header" class="clearfix">
<div class="container">

<!-- #logo -->
  <div id="logo">
    <?php if (is_front_page()) { ?><h1><?php } ?>
      <a title="<?php bloginfo( 'name' ); ?>" href="<?php echo home_url(); ?>">
      <?php if (of_get_option('st_logo')) { ?>
      <img alt="<?php bloginfo( 'name' ); ?>" src="<?php echo of_get_option('st_logo'); ?>">
      <?php } else { ?>
      <?php bloginfo( 'name' ); ?>
      <?php } ?>
      </a>
     <?php if (is_front_page()) { ?></h1><?php } ?>
  </div>
<!-- /#logo -->


<!-- #primary-nav -->
<nav id="primary-nav" role="navigation" class="clearfix">
  <?php if ( has_nav_menu( 'primary-nav' ) ) { ?>
    <?php wp_nav_menu( array('theme_location' => 'primary-nav', 'container' => false, 'menu_class' => 'nav sf-menu clearfix' )); ?>
  <?php } ?>
</nav>
<!-- #primary-nav -->


</div>
</header>
<!-- /#header -->
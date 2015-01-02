<!DOCTYPE html><!-- HTML 5 -->
<html <?php language_attributes(); ?>>

<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	
	<title><?php wp_title('|', true, 'right'); ?></title>

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php themezee_wrapper_before(); // hook before #wrapper ?>
<div id="wrapper">

	<?php themezee_header_before(); // hook before #header ?>
	<div id="header">

		<div id="head">
			<div id="logo">
				<?php 
				$options = get_option('themezee_options');
				if ( isset($options['themeZee_general_logo']) and $options['themeZee_general_logo'] <> "" ) { ?>
					<a href="<?php echo home_url(); ?>"><img src="<?php echo esc_url($options['themeZee_general_logo']); ?>" alt="Logo" /></a>
				<?php } else { ?>
					<a href="<?php echo home_url(); ?>/"><h1><?php bloginfo('name'); ?></h1></a>
				<?php } ?>
			</div>
			<div id="topnavi">
				<?php 
				// Get Navigation out of Theme Options
					wp_nav_menu(array('theme_location' => 'top_navi', 'container' => false, 'menu_id' => 'topnav', 'echo' => true, 'fallback_cb' => 'themezee_default_menu', 'before' => '', 'after' => '', 'link_before' => '', 'link_after' => '', 'depth' => 0));
				?>
			</div>
		</div>
	</div>
	<?php themezee_header_after(); // hook after #header ?>
	
	<?php if( get_header_image() != '' ) : ?>
		<div id="custom_header">
			<img src="<?php echo get_header_image(); ?>" />
		</div>
	<?php endif; ?>
	
	<div id="navi">
		<?php 
		// Get Navigation out of Theme Options
			wp_nav_menu(array('theme_location' => 'main_navi', 'container' => false, 'menu_id' => 'nav', 'echo' => true, 'fallback_cb' => 'themezee_default_menu', 'before' => '', 'after' => '', 'link_before' => '', 'link_after' => '', 'depth' => 0));
		?>
	</div>
	<div class="clear"></div>
	
	<div id="wrap">
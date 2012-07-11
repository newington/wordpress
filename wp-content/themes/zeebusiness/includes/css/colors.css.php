<?php 
add_action('wp_head', 'themezee_css_colors');
function themezee_css_colors() {
	
	$options = get_option('themezee_options');
	
	if ( isset($options['themeZee_color_activate']) and $options['themeZee_color_activate'] == 'true' ) {
		
		echo '<style type="text/css">';
		echo '
			a, a:link, a:visited,
			#sidebar ul li h2,
			#logo h1,
			#navi ul li a:hover,
			.postmeta a:link, .postmeta a:visited,
			.postinfo a:link, .postinfo a:visited,
			#comments a:link, #comments a:visited, #respond a:link, #respond a:visited,
			.bottombar ul li h2
			{
				color: #'.esc_attr($options['themeZee_colors_full']).';
			}
			.post-title,
			.post-title a:link, .post-title a:visited
			{
				color: #'.esc_attr($options['themeZee_colors_full']).';
			}
			#sidebar ul li ul, #sidebar ul li div,
			#topnavi ul li a:hover,
			#topnavi ul li ul,
			#navi,
			.more-link,
			.arh,
			#slide_panel,
			#comments h3, #respond h3,
			.bypostauthor .fn,
			.wp-pagenavi .current,
			.bottombar ul li ul, .bottombar ul li div
			{
				background: #'.esc_attr($options['themeZee_colors_full']).';
			}

			#wrapper {
				border-top: 7px solid #'.esc_attr($options['themeZee_colors_full']).';
				border-bottom: 7px solid #'.esc_attr($options['themeZee_colors_full']).';
			}
			.sticky {
				border-left: 4px solid #'.esc_attr($options['themeZee_colors_full']).';
			}
			.postmeta {
				border-top: 1px dotted #'.esc_attr($options['themeZee_colors_full']).';
				border-bottom: 1px dotted #'.esc_attr($options['themeZee_colors_full']).';
			}
			.commentlist li {
				border-top: 1px dotted #'.esc_attr($options['themeZee_colors_full']).';
			}
			#comments .children li {
				border-left: 2px solid #'.esc_attr($options['themeZee_colors_full']).';
			}
		';
		echo '</style>';
	}
}
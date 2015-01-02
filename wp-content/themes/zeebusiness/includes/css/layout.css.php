<?php 
add_action('wp_head', 'themezee_css_layout');
function themezee_css_layout() {
	
	echo '<style type="text/css">';
	$options = get_option('themezee_options');
	
	// Add Custom CSS
	if ( $options['themeZee_general_css'] <> '' ) {
		echo $options['themeZee_general_css'];
	}
	
	echo '</style>';
}
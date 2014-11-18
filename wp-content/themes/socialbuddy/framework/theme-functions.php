<?php

/* Functions specific to the included option settings */

/**
* Output Custom CSS from theme options
*/

function st_head_css() {
		$custom_css = of_get_option('st_custom_css');
		$output = '';
		
		if ($custom_css <> '') {
			$output .= $custom_css . "\n";
		}
		
		// Output styles
		if ($output <> '') {
			$output = "<!-- Custom Styling -->\n<style type=\"text/css\">\n" . $output . "</style>\n";
			echo $output;
		}
	
}

add_action('wp_head', 'st_head_css');

/**
* Add Favicon
*/
function st_favicon() {
	if (of_get_option('st_custom_favicon')) {
	echo '<link rel="shortcut icon" href="'. of_get_option('st_custom_favicon') .'"/>'."\n";
	}
}

add_action('wp_head', 'st_favicon');


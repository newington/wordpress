<?php
/*
Plugin Name: Padlet Shortcode
Description: Enables shortcode to embed Padlet walls. Usage: <code>[padlet key="8ytxjz" height="480" width="100%" ]</code>. This code is available in the SHARE section of the wall side bar.
Version: 1.3
License: GPL
Author: Nitesh Goel / Padlet
Author URI: http://padlet.com
*/

function createWallwisherEmbedFrame($atts, $content = null) {
	extract(shortcode_atts(array(
		'key'   => '',
		'width'   => '100%',
		'height' => '480'
	), $atts));

	if(!$key) {
		$code = "<div style='padding:5px;border: 1px solid red'>Something is wrong with your Padlet shortcode.</div>";
	}
	else {
		$code = "<iframe src='http://padlet.com/embed/$key' frameborder=0 width='100%' height='480px' style='padding:0;margin:0;border:none'></iframe><div style='border-top:2px solid #a7d23a;padding:8px;margin:0;font-size:12px;text-align:right'><a href='http://wallwisher.com' style='color:#41555f;text-decoration:none'>Created with Padlet<img valign='middle' style='margin:0 0 0 10px;padding:0;border:none;width:16px;height:16px' src='http://padlet.com/favicon.ico'></a></div>";
	}

	/**
	* Return embed in JS and iframe
	*/
	return $code;
}

add_shortcode('wallwisher', 'createWallwisherEmbedFrame');
add_shortcode('padlet', 'createWallwisherEmbedFrame');
?>
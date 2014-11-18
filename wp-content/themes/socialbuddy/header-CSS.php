<style type="text/css">
/* Background */
html {
	<?php
	$st_background_color = of_get_option('st_background_color');
	if ($st_background_color['color']) { ?>
	background-color:<?php echo $st_background_color['color'] ?>;
	<?php } ?>
	<?php if ($st_background_color['image']) { ?>
	background-image: url("<?php echo $st_background_color['image'] ?>");
	background-attachment:<?php echo $st_background_color['attachment'] ?>;
	background-position:<?php echo $st_background_color['position'] ?>;
	background-repeat:<?php echo $st_background_color['repeat'] ?>;
	<?php } ?>	
}
/* Link Color */
a, 
a:visited, 
a:hover {
color:<?php echo of_get_option('st_link_color'); ?>;
}
/* Link Color Buttons */
#groups-list-options a, 
#members-list-options a,
.blog-pagination a, 
.blog-pagination span,
.paging-navigation .nav-previous a, 
.paging-navigation .nav-next a,
#page-header .button,
#page-header input[type="submit"] {
	background-color:<?php echo of_get_option('st_link_color'); ?>;
}

/* Theme Color */
button,
a.button,
input[type=submit],
input[type=button],
input[type=reset],
ul.button-nav li a,
div.generic-button a,
#callout .btn,
#hpslider h2,
.flex-control-paging li a.flex-active {
background:<?php echo of_get_option('st_theme_color'); ?>;	
}









.entry-content blockquote:before,
.activity-list .activity-content blockquote:before {
	color:<?php echo of_get_option('st_theme_color'); ?>;	
}


/* Header Colors */
#header,
#hpslider,
#page-header {
	<?php
	$st_header_color = of_get_option('st_header_color');
	if ($st_header_color['color']) { ?>
	background-color:<?php echo $st_header_color['color'] ?>;
	<?php } ?>
	<?php if ($st_header_color['image']) { ?>
	background-image: url("<?php echo $st_header_color['image'] ?>");
	background-attachment:<?php echo $st_header_color['attachment'] ?>;
	background-position:<?php echo $st_header_color['position'] ?>;
	background-repeat:<?php echo $st_header_color['repeat'] ?>;
	<?php } ?>	
}
#hpslider p {
	background:<?php echo $st_header_color['color'] ?>;
}
<?php 
function colourBrightness($hex, $percent) {
	// Work out if hash given
	$hash = '';
	if (stristr($hex,'#')) {
		$hex = str_replace('#','',$hex);
		$hash = '#';
	}
	/// HEX TO RGB
	$rgb = array(hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2)));
	//// CALCULATE
	for ($i=0; $i<3; $i++) {
		// See if brighter or darker
		if ($percent > 0) {
			// Lighter
			$rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1-$percent));
		} else {
			// Darker
			$positivePercent = $percent - ($percent*2);
			$rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1-$positivePercent));
		}
		// In case rounding up causes us to go to 256
		if ($rgb[$i] > 255) {
			$rgb[$i] = 255;
		}
	}
	//// RBG to Hex
	$hex = '';
	for($i=0; $i < 3; $i++) {
		// Convert the decimal digit to hex
		$hexDigit = dechex($rgb[$i]);
		// Add a leading zero if necessary
		if(strlen($hexDigit) == 1) {
		$hexDigit = "0" . $hexDigit;
		}
		// Append to the hex string
		$hex .= $hexDigit;
	}
	return $hash.$hex;
}
$header_colour = $st_header_color['color'];
$brightness = -0.7;
$page_header_color = colourBrightness($header_colour,$brightness);
$nav_drop_color = colourBrightness($header_colour,-0.8);
?>
#page-header-content {
	background:<?php echo $page_header_color; ?>;
}
#primary-nav ul ul {
    background: <?php echo $nav_drop_color; ?>;
}
#primary-nav ul ul:before {
	border-bottom: 10px solid <?php echo $nav_drop_color; ?>;
}
</style>

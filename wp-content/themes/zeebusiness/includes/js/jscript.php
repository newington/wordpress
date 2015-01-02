<?php 
	
add_action('wp_head', 'themezee_include_jscript');
function themezee_include_jscript() {

	// Select Post Slider Modus
	$options = get_option('themezee_options');
	if(isset($options['themeZee_show_slider']) and $options['themeZee_show_slider'] == 'true') {
		switch($options['themeZee_slider_mode']) {
			case 0:
				$return = "<script type=\"text/javascript\">
				//<![CDATA[
					// Horizontal Slider
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx: 'scrollHorz',
							speed: 1000,
							timeout: 8000,
							next:   '#slide_next', 
							prev:   '#slide_prev'
						});
					});
				//]]>
				</script>";

			break;
			case 1:
				$return = "<script type=\"text/javascript\">
				//<![CDATA[
					// Dropdown Slider
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx:     'scrollVert',
							speed: 1000,
							timeout: 8000,
							next:   '#slide_next', 
							prev:   '#slide_prev'
						});
					});
				//]]>
				</script>";

			break;
			case 2:
				$return = "<script type=\"text/javascript\">
				//<![CDATA[
					// Fade Slider
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx: 'fade',
							speed: 'slow',
							timeout: 8000,
							next:   '#slide_next', 
							prev:   '#slide_prev'
						});
					});
				//]]>
				</script>";

			break;
			default:
				$return = "<script type=\"text/javascript\">
				//<![CDATA[
					// Horizontal Slider
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx: 'scrollHorz',
							speed: 1000,
							timeout: 8000,
							next:   '#slide_next', 
							prev:   '#slide_prev'
						});
					});
				//]]>
				</script>";
			break;
		}
		
		/* Slide Menu
			Slide Effeckts
				show - show(500) 
				slide - slideDown(500)
				fade - show().css({opacity:0}).animate({opacity:1},500)
				diagonal - animate({width:'show',height:'show'},500)
				left - animate({width:'show'},500)
				slidefade - animate({height:'show',opacity:1})
		*/
		$return .= "<script type=\"text/javascript\">
				//<![CDATA[
					jQuery(document).ready(function($) {
						$('#nav ul').css({display: 'none'}); // Opera Fix
						$('#nav li').hover(function(){
							$(this).find('ul:first').css({visibility: 'visible',display: 'none'}).slideDown(350);
						},function(){
							$(this).find('ul:first').css({visibility: 'hidden'});
						});
						
						$('#topnav ul').css({display: 'none'}); // Opera Fix
						$('#topnav li').hover(function(){
							$(this).find('ul:first').css({visibility: 'visible',display: 'none'}).slideDown(350);
						},function(){
							$(this).find('ul:first').css({visibility: 'hidden'});
						});
					});
				//]]>
				</script>";
				
		/* Frontpage Slider */
		$return .= "<script type=\"text/javascript\">
				//<![CDATA[
					// Front Page Slider
					jQuery(document).ready(function($) {
						$('#frontpage_slideshow')
							.cycle({
							fx: 'curtainX',
							speed: 500,
							timeout: 8000,
							pager: '#frontpage_pager'
						});
					});
				//]]>
				</script>";
		echo $return;
	}
}
?>
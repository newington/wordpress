<?php

add_filter( 'twentyeleven_color_schemes', 'twentyeleven_color_schemes_orange' );
add_action( 'twentyeleven_enqueue_color_scheme', 'twentyeleven_enqueue_color_scheme_orange' );
add_action( 'wp_footer', 'add_googleanalytics' );

function twentyeleven_color_schemes_orange( $color_schemes ) {
	$color_schemes['orange'] = array(
		'value' => 'orange',
		'label' => __( 'Orange', 'twentyeleven' ),
		'thumbnail' => get_stylesheet_directory_uri() . '/orange.png',
		'default_link_color' => '#FFA500',
	);
	return $color_schemes;
}

function twentyeleven_enqueue_color_scheme_orange( $color_scheme ) {
	if ( 'orange' == $color_scheme )
		wp_enqueue_style( 'orange', get_stylesheet_directory_uri() . '/orange.css', array(), null );
}

function add_googleanalytics() { ?>
	<script type="text/javascript">

	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-27301259-2']);
	  _gaq.push(['_trackPageview']);

	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();

	</script>
<?php } ?>
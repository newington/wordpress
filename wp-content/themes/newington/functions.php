<?php

add_filter( 'twentyeleven_color_schemes', 'twentyeleven_color_schemes_orange' );
add_action( 'twentyeleven_enqueue_color_scheme', 'twentyeleven_enqueue_color_scheme_orange' );

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
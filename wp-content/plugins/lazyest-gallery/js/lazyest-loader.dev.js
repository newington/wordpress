/*
  images loader for lazyest-gallery
  copyright 2010 Marcel Brinkkemper
*/

var lazyest_loading = true;

function lg_js_loadNext() {
  if (jQuery('img.lg_ajax').length) {
    var loadImg = jQuery('.lg_ajax:first');
    if ( loadImg ) {
      loadImg.removeClass('lg_ajax');
      var loadSrc = lazyestimg.ajaxurl + loadImg.attr('src').split('?')[1];
      loadImg.attr('src', loadSrc );
    }
  } else {	
    if ( lazyest_loading ) {
    lazyest_loading = false;
      if(typeof(lazyest_slideshow) !== 'undefined') {
        if ( jQuery('.lg_slideshow').length ) {  
          lg_js_slideshow();
        }
      }
    }
  }
}

function lg_js_loadFirst() {
	if ( jQuery('img.lg_ajax').length) {
		jQuery('img.lg_ajax').each( function(){
			jQuery(this).load(function(){
				lg_js_loadNext();
			});
		});
	} 	
}

jQuery(window).ready(function() {
  lg_js_loadFirst();  
}) ;

jQuery(window).load(function() {
  lg_js_loadNext();
});
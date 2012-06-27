var $ = jQuery.noConflict();

var lg_prevWidth;
var lg_prevHeight;

function lg_popUp( element ) {  
  lg_prevWidth = $(element).width();
  lg_prevHeight = $(element).height();     
  if ( ! $(element).hasClass('popup') ) { 
    $(element).css({'z-index':'100'});
    var t = setTimeout(function() {
      $(element).css({'position':'absolute','top':-(lg_prevHeight+5)});
      $(element).stop().animate({'font-size':'90%', 'line-height':'1em'}, 200, function() {
        $(element).addClass('popup');  
      });          
    }, 200 );
    $(this).data('timeout', t);      
  }  
}

function lg_popDown( element ) {  
  if( $(element).hasClass('popup')) { 
    clearTimeout($(this).data('timeout'));
    $(element).stop().animate({'font-size':'0', 'line-height':'0' }, 200, function() {
      $(element).removeClass('popup');
      $(element).css(({'top':'0','position':'relative', 'z-index':'0'}));  
    });               
  } else {
    clearTimeout($(this).data('timeout'));
  }
}

$(document).ready(function() {  
  $('.lg_thumb').hover(      
    function() {
      lg_popUp($(this));
    },
    function () {
      lg_popDown($(this));
    }
  );
});
jQuery( document ).ready( function( $ ) {

	/* 
	 * Play Vimeo
	 ****************************************************
	 */

	$('a.vimeoframe').click(function(){ // the trigger action 
	  
	  var vimeokey = $(this).data('vimeo');
	  
	  // get the padding-bottom value
	  var vimeoratio = $(this).css( "padding-bottom" );
	  
	  // apply it to the parent element
	  $(this).parent("div.vimeo-item").css( "padding-bottom", vimeoratio );
	  
	  $(this).replaceWith('<iframe src="https://player.vimeo.com/video/' + vimeokey + '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;autoplay=1" width="240" height="180" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen class="list-item-inside dblock"></iframe>');
	  
	  return false;
	  
	});

}); // document ready
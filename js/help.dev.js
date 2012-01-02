jQuery( document ).ready( function( $ ){
	
	$('div#qa-action-links').each( function(){
		$(this).append(' | <a href="#" class="reply">reply</a>' );
	});
	
	$('.reply').live( 'click', function( event ) {
		
		event.preventDefault();
		
		$(this).parent().parent().children('#comments').children('#respond').toggle('slow');

		if ( $(this).text() == 'reply' )
			$(this).text( 'cancel reply' );
		else 
			$(this).text( 'reply' );

		return false;
		
	});
	
	//non-js
	$('div#respond, #reply-title').hide();
	
});
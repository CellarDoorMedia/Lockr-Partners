jQuery( function ( $ ) {
	
	$( '.key-label input' ).bind( 'input keydown change', function() {
		var keyLabel = $( this ).val();
		keyLabel = keyLabel.toLowerCase().replace( /\W+/g,'_' );
		
		if( !$( '.machine-name' ).hasClass('disabled') ){
			$( '.machine-name input' ).val( keyLabel );
			$( '.machine-name-label a' ).text( keyLabel );
		}
	});
	$( '.machine-name input' ).bind( 'change' ), function() {
		var keyLabel = $( this ).val();
		keyLabel = keyLabel.toLowerCase().replace( /\W+/g,'_' );
		$( this ).val( keyLabel );
	}
	$( '.show-key-name' ).click( function() {
		$( '.machine-name' ).removeClass( 'hidden' );
		return false;
	});
});
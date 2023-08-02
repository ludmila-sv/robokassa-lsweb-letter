/* eslint-disable prettier/prettier */
( function ( $ ) {

	$( '#robo-letter-place, #robo-letter-translit' ).on( 'change', function() {

		const place = $( '#robo-letter-place' ).val();
		const translit = $( '#robo-letter-translit' ).val();
		const div = $( '.robo-letter' );

		$.ajax( {
			url: '/wp-admin/admin-ajax.php',
			type: 'POST',
			dataType: 'html',
			data: {
				action: 'letterrobokassa',
				place,
				translit,
				nonce: robolsweb_admin_letter.nonce,
			},
			cache: false,
			beforeSend() {
				div.addClass('loading');
				div.html( '' );
			},
			success( data ) {
				div.html( data );		
			},
			error( data ) {
				alert( data );
			},
			complete() {
				div.removeClass('loading');
			},
		} ); // end ajax
	} );

} )( jQuery );

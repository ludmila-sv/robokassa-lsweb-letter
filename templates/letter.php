<?php
namespace Lsweb\Robokassa\Letter;

/*
* Letter page.
*/
?>

<div class="robo-page">
	<h1><?php esc_html_e( 'Generate Letter', 'robokassa-lsweb-letter' ); ?></h1>
	<div class="robo-letter-select">
		<label for="robo-letter-place"><?php esc_html_e( 'Choose place', 'robokassa-lsweb-letter' ); ?></label>
		<select name="place" id="robo-letter-place">
			<option value=""><?php esc_html_e( 'Choose place', 'robokassa-lsweb-letter' ); ?></option>
			<?php
			foreach ( $places as $place ) {
				echo '<option value="' . esc_html( $place ) . '">' . esc_html( $place ) . '</option>';
			}
			?>
		</select>
	</div>
	<div class="robo-letter-translit">
		<input id="robo-letter-translit" type="checkbox" name="robo_letter_translit" value="" />
		<label for="robo-letter-translit" class="robo-letter-translit__label"><?php esc_html_e( 'Convert cyrilic letters into latin ones', 'robokassa-lsweb-letter' ); ?></label>
	</dev>
	<div class="robo-letter-message"><?php esc_html_e( 'Notes included to the letter are those paid but not sent.', 'robokassa-lsweb-letter' ); ?></div>
	<div class="robo-letter"></div>
</div>
<script>
	jQuery( document ).ready( function ( $ ) {
		$( '#robo-letter-translit' ).on( 'click', function () {
			let val = $( this ).val() == 1 ? 0 : 1;
			$( this ).attr( 'value', val );
			$( this ).prop( 'checked', val );
		} );
	} )
</script>

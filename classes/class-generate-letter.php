<?php
/**
 * Ajax handling for letter
 */
namespace Lsweb\Robokassa\Letter;

use Lsweb\Robokassa\Master\Hooked;

/**
 * Class to render letter
 */
class Generate_Letter implements Hooked {

	/**
	 * Init hooks.
	 */
	public function init_hooks() {

		add_action( 'wp_ajax_letterrobokassa', array( $this, 'create_letter' ) );
		add_action( 'wp_ajax_nopriv_letterrobokassa', array( $this, 'create_letter' ) );

	}

	/**
	 * Dependency injection.
	 *
	 * @var Translit
	 */
	private $translit;

	public function __construct( Translit $translit ) {
		$this->translit = $translit;
	}

	public function get_places() {
		global $wpdb;
		$table = $wpdb->prefix . RM_TABLE;

		$q = 'SELECT title FROM ' . $table . ' ORDER BY id DESC';
		$n = $wpdb->query( $q ); // phpcs:ignore

		$places = array();

		if ( $n ) {
			for ( $i = 0; $i < $n; $i++ ) {
				$row = $wpdb->get_row( $q, ARRAY_A, $i ); // phpcs:ignore
				$places[] = $row['title'];
			}
		}
		$places = array_unique( $places );
		return $places;
	}

	public function create_letter() {
		$place    = ( isset( $_POST['place'] ) && wp_verify_nonce( $_POST['nonce'], RL_NONCE ) ) ? sanitize_text_field( wp_unslash( $_POST['place'] ) ) : '';
		$translit = ( isset( $_POST['translit'] ) && wp_verify_nonce( $_POST['nonce'], RL_NONCE ) ) ? sanitize_text_field( wp_unslash( $_POST['translit'] ) ) : 0;

		try {

			$notes = $this->get_notes( $place, $translit );
			echo wp_kses_post( $notes );
		} catch ( \Exception $e ) {
			echo esc_html( $e->getMessage() );
		}

		wp_die();
	}

	private function get_notes( $place = '', $translit = 0 ) {

		global $wpdb;
		$table = $wpdb->prefix . RM_TABLE;
		if ( empty( $place ) ) {
			$q = 'SELECT content FROM ' . $table . ' WHERE paid=1 AND sent=0 ORDER BY id DESC';
		} else {
			$q = 'SELECT content FROM ' . $table . ' WHERE title LIKE "%' . trim( $place ) . '%" AND paid=1 AND sent=0 ORDER BY id DESC';
		}
		$wpdb->hide_errors();
		$n = $wpdb->query( $q ); // phpcs:ignore

		$notes = '';

		if ( ! $n ) {
			throw new \Exception( __( 'Nothing found.', 'robokassa-lsweb-letter' ) );
		} else {
			for ( $i = 0; $i < $n; $i++ ) {
				$row  = $wpdb->get_row( $q, ARRAY_A, $i ); // phpcs:ignore
				$note = preg_replace( '/(\s–\s\<b.*?\<\/b\>)/', '', htmlspecialchars_decode( $row['content'] ) );
				$note = str_replace( 'Молиться за ', '', $note );

				$notes .= $note;
			}
		}

		$notes_ordered = $this->order_notes( $notes, $translit );

		return $notes_ordered;
	}

	private function order_notes( $notes = '', $translit = 0 ) {
		$notes_sorted      = '';
		$notes_sorted_dead = '';

		$live_liturgy = array();
		$live_40      = array();
		$live_prayer  = array();
		$live_relics  = array();

		$dead_liturgy  = array();
		$dead_40       = array();
		$dead_memorial = array();
		$dead_relics   = array();

		$notes = str_replace( "\n", '', $notes );
		$notes = str_replace( "\n\r", '', $notes );

		$liturgy  = $translit ? 'Liturgy:' : 'Литургия:';
		$prayer40 = $translit ? '40-days commemoration:' : 'Сорокоуст:';
		$moleben  = $translit ? 'Moleben:' : 'Молебен:';
		$relics   = $translit ? 'Prayer before relics:' : 'Молитва у мощей:';
		$memorial = $translit ? 'Memorial service:' : 'Панихида:';
		$live     = $translit ? 'For the health:' : 'О здравии:';
		$dead     = $translit ? 'For the repose:' : 'Об упокоени:';

		preg_match_all( '/<p[^>]*>(.*?)<\/p>/si', $notes, $notes_arr );

		foreach ( $notes_arr[1] as $note ) {
			$matches = array();

			preg_match( '/<b[^>]*>(.*)<\/b>/', $note, $matches );
			if ( $translit ) {
				$name = '<p>' . $this->translit->cyr_to_lat( $matches[1] ) . '</p>';
			} else {
				$name = '<p>' . $matches[1] . '</p>';
			}

			if ( strpos( $note, 'о здравии' ) ) {
				if ( strpos( $note, 'литургия' ) ) {
					$live_liturgy[] = $name;
				}
				if ( strpos( $note, 'сорокоуст' ) ) {
					$live_40[] = $name;
				}
				if ( strpos( $note, 'молебен' ) ) {
					$live_prayer[] = $name;
				}
				if ( strpos( $note, 'мощей' ) ) {
					$live_relics[] = $name;
				}
			} else {
				if ( strpos( $note, 'литургия' ) ) {
					$dead_liturgy[] = $name;
				}
				if ( strpos( $note, 'сорокоуст' ) ) {
					$dead_40[] = $name;
				}
				if ( strpos( $note, 'панихида' ) ) {
					$dead_memorial[] = $name;
				}
				if ( strpos( $note, 'мощей' ) ) {
					$dead_relics[] = $name;
				}
			}
		}

		if ( ! empty( $live_liturgy ) ) {
			$notes_sorted .= '<h4>' . $liturgy . '</h4>' . implode( '', $live_liturgy );
		}
		if ( ! empty( $live_40 ) ) {
			$notes_sorted .= '<h4>' . $prayer40 . '</h4>' . implode( '', $live_40 );
		}
		if ( ! empty( $live_prayer ) ) {
			$notes_sorted .= '<h4>' . $moleben . '</h4>' . implode( '', $live_prayer );
		}
		if ( ! empty( $live_relics ) ) {
			$notes_sorted .= '<h4>' . $relics . '</h4>' . implode( '', $live_relics );
		}
		if ( ! empty( $notes_sorted ) ) {
			$notes_sorted = '<h3>' . $live . '</h3>' . $notes_sorted;
		}

		if ( ! empty( $dead_liturgy ) ) {
			$notes_sorted_dead .= '<h4>' . $liturgy . '</h4>' . implode( '', $dead_liturgy );
		}
		if ( ! empty( $dead_40 ) ) {
			$notes_sorted_dead .= '<h4>' . $prayer40 . '</h4>' . implode( '', $dead_40 );
		}
		if ( ! empty( $dead_memorial ) ) {
			$notes_sorted_dead .= '<h4>' . $memorial . '</h4>' . implode( '', $dead_memorial );
		}
		if ( ! empty( $dead_relics ) ) {
			$notes_sorted_dead .= '<h4>' . $relics . '</h4>' . implode( '', $dead_relics );
		}
		if ( ! empty( $notes_sorted_dead ) ) {
			$notes_sorted_dead = '<h3>' . $dead . '</h3>' . $notes_sorted_dead;
		}
		$notes_sorted = $notes_sorted . $notes_sorted_dead;

		if ( ! empty( $notes_sorted ) ) {
			return $notes_sorted;
		} else {
			return $notes;
		}
	}

}

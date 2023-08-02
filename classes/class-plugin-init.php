<?php
namespace Lsweb\Robokassa\Letter;

use Lsweb\Robokassa\Master\Hooked;

class Plugin_Init implements Hooked {

	private static $nonce = RL_NONCE;

	public function init_hooks() {
		load_plugin_textdomain( 'robokassa-lsweb-letter', false, RL_PLUGIN_DIR_NAME . '/languages' );

		if ( function_exists( 'Lsweb\Robokassa\autoloader' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
			add_action( 'admin_menu', array( $this, 'menu' ), 20 );
			flush_rewrite_rules();
		}
	}

	/**
	 * Dependency injection.
	 *
	 * @var Generate_Letter
	 */
	private $generate_letter;

	public function __construct( Generate_Letter $generate_letter ) {
		$this->generate_letter = $generate_letter;
	}

	public function menu() {
		add_submenu_page( 'robokassa-lsweb', __( 'Generate Letter', 'robokassa-lsweb-letter' ), __( 'Letter', 'robokassa-lsweb-letter' ), 'manage_options', 'robokassa-letter', array( $this, 'robokassa_letter' ), 30 );
	}

	public function robokassa_letter() {
		$places = $this->generate_letter->get_places();
		require_once plugin_dir_path( __DIR__ ) . '/templates/letter.php';
	}

	public static function admin_styles() {
		wp_enqueue_style( 'robo-letter-style', RL_PLUGIN_DIR . '/assets/css/styles.css', array(), RL_VERSION );

		wp_enqueue_script( 'robo-lsweb-admin-letter', RL_PLUGIN_DIR . '/assets/js/letter.js', array( 'jquery' ), RL_VERSION, true );
		wp_localize_script(
			'robo-lsweb-admin-letter',
			'robolsweb_admin_letter',
			array(
				'ajax_url' => wp_unslash( admin_url( 'admin-ajax.php' ) ),
				'nonce'    => wp_create_nonce( self::$nonce ),
				'error'    => __( 'Error! Try again later.', 'robokassa-lsweb' ),
			)
		);
	}

}

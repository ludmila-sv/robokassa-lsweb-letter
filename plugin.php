<?php
/*
 * Plugin Name: Robokassa Letter
 * Description: Generates letter based on paid orders.
 * Author: lsweb.ru
 * Version: 2.0
 * Author URI: https://lsweb.ru/
 * Text Domain: robokassa-lsweb-letter
 * Domain Path: /languages
*/

namespace Lsweb\Robokassa\Letter;

use Lsweb\Robokassa\Letter\Plugin_Init;

defined( 'ABSPATH' ) || die();

define( 'RL_PLUGIN_DIR_NAME', dirname( plugin_basename( __FILE__ ) ) );

define( 'RL_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
define( 'RL_VERSION', '2.0' ); // see above;
define( 'RL_NONCE', 'nonce_robokassa_lsweb_letter' );

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\deactivate' );

add_action( 'plugins_loaded', __NAMESPACE__ . '\check_master_active' );
add_filter( 'robo_child_plugins_to_be_loaded', __NAMESPACE__ . '\enqueue_to_be_loaded' );

/**
 * Activates plugin.
 */
function activate() {
	check_master_active();
}

/**
 * Deactivates plugin. Removes settings, etc. if the option 'full_uninstall' is on.
 */
function deactivate() {
	remove_submenu_page( 'robokassa-lsweb', 'robokassa-letter' );
	flush_rewrite_rules();
}

/**
 * Show notice if master plugin is not active, otherwise activates the child plugin.
 *
 * @return void
 */
function check_master_active() {

	if ( ! function_exists( 'Lsweb\Robokassa\autoloader' ) ) {
		load_plugin_textdomain( 'robokassa-lsweb-letter', false, RL_PLUGIN_DIR_NAME . '/languages' );
		add_action(
			'admin_notices',
			function() {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php esc_html_e( 'Please install and activate Robokassa Master Plugin first!', 'robokassa-lsweb-letter' ); ?></p>
				</div>
				<?php
			}
		);
	}

}

/**
 * Links sub plugin's class to be loaded with master plugin.
 *
 * @param array $sub_plugins
 *
 * @return array
 */
function enqueue_to_be_loaded( $sub_plugins = array() ) {
	$sub_plugins['letter_init']     = __NAMESPACE__ . '\Plugin_Init';
	$sub_plugins['letter_generate'] = __NAMESPACE__ . '\Generate_Letter';
	$sub_plugins['letter_translit'] = __NAMESPACE__ . '\Translit';

	return $sub_plugins;
}


<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Admin area. This file also App all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpmudev.com
 * @since             1.0.0
 * @package           CF_Images
 *
 * @wordpress-plugin
 * Plugin Name:       Cloudflare Images
 * Plugin URI:        https://wpmudev.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress Admin area.
 * Version:           1.0.0
 * Author:            Anton Vanyukov
 * Author URI:        https://vanyukov.su
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf-images
 * Domain Path:       /languages
 */

namespace CF_Images;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CF_IMAGES_VERSION', '1.0.0' );
define( 'CF_IMAGES_ACCOUNT_ID', '457c5eaeaae69e45675a80889d2eb6ec' );
define( 'CF_IMAGES_KEY_TOKEN', 'KIWSJZB_6CMgrjv4n3jX4pnZ3B-IkHsH4U3kQ7OU' );

require_once 'App/Activator.php';
register_activation_hook( __FILE__, array( 'CF_Images\App\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CF_Images\App\Activator', 'deactivate' ) );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cf_images() {

	require_once 'App/Core.php';
	new App\Core();

}
run_cf_images();

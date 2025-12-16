<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @package           International_Telephone_Input_With_Flags_And_Dial_Codes
 * @author            Sajjad Hossain Sagor <sagorh672@gmail.com>
 *
 * Plugin Name:       International Telephone Input With Flags And Dial Codes
 * Plugin URI:        https://wordpress.org/plugins/international-telephone-input-with-flags-and-dial-codes/
 * Description:       Plugin turns the standard telephone input into an International Telephone Input with a national flag drop down list respective Country dial codes.
 * Version:           2.0.6
 * Requires at least: 5.6
 * Requires PHP:      8.1
 * Author:            Sajjad Hossain Sagor
 * Author URI:        https://sajjadhsagor.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       international-telephone-input-with-flags-and-dial-codes
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_VERSION', '2.0.6' );

/**
 * Define Plugin Folders Path
 */
define( 'INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

define( 'INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-international-telephone-input-with-flags-and-dial-codes-activator.php
 *
 * @since    2.0.0
 */
function on_activate_international_telephone_input_with_flags_and_dial_codes() {
	require_once INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_PATH . 'includes/class-international-telephone-input-with-flags-and-dial-codes-activator.php';

	International_Telephone_Input_With_Flags_And_Dial_Codes_Activator::on_activate();
}

register_activation_hook( __FILE__, 'on_activate_international_telephone_input_with_flags_and_dial_codes' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-international-telephone-input-with-flags-and-dial-codes-deactivator.php
 *
 * @since    2.0.0
 */
function on_deactivate_international_telephone_input_with_flags_and_dial_codes() {
	require_once INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_PATH . 'includes/class-international-telephone-input-with-flags-and-dial-codes-deactivator.php';

	International_Telephone_Input_With_Flags_And_Dial_Codes_Deactivator::on_deactivate();
}

register_deactivation_hook( __FILE__, 'on_deactivate_international_telephone_input_with_flags_and_dial_codes' );

/**
 * The core plugin class that is used to define admin-specific and public-facing hooks.
 *
 * @since    2.0.0
 */
require INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_PATH . 'includes/class-international-telephone-input-with-flags-and-dial-codes.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.0
 */
function run_international_telephone_input_with_flags_and_dial_codes() {
	$plugin = new International_Telephone_Input_With_Flags_And_Dial_Codes();

	$plugin->run();
}

run_international_telephone_input_with_flags_and_dial_codes();

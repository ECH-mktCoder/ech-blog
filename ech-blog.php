<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://#
 * @since             1.0.0
 * @package           Ech_Blog
 *
 * @wordpress-plugin
 * Plugin Name:       ECH Blog
 * Plugin URI:        https://#
 * Description:       A Wordpress plugin to display ECH articles for any ECH company's brand websites
 * Version:           1.0.0
 * Author:            Toby Wong
 * Author URI:        https://#
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ech-blog
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ECH_BLOG_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ech-blog-activator.php
 */
function activate_ech_blog() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ech-blog-activator.php';
	Ech_Blog_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ech-blog-deactivator.php
 */
function deactivate_ech_blog() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ech-blog-deactivator.php';
	Ech_Blog_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ech_blog' );
register_deactivation_hook( __FILE__, 'deactivate_ech_blog' );




/****************************************
 * Create an option "run_init_createVP" once plugin is activated
 ****************************************/
function activate_initialize_createVP() {
	require_once plugin_dir_path( __FILE__ ) . 'public/class-ech-blog-virtual-pages.php';
	Ech_Blog_Virtual_Pages::initialize_createVP();
}
register_activation_hook( __FILE__, 'activate_initialize_createVP' );



/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ech-blog.php';




/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ech_blog() {
	$plugin = new Ech_Blog();
	$plugin->run();
}
run_ech_blog();

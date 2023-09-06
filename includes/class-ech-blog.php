<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://#
 * @since      1.0.0
 *
 * @package    Ech_Blog
 * @subpackage Ech_Blog/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ech_Blog
 * @subpackage Ech_Blog/includes
 * @author     Toby Wong <tobywong@prohaba.com>
 */
class Ech_Blog {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ech_Blog_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ECH_BLOG_VERSION' ) ) {
			$this->version = ECH_BLOG_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ech-blog';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ech_Blog_Loader. Orchestrates the hooks of the plugin.
	 * - Ech_Blog_i18n. Defines internationalization functionality.
	 * - Ech_Blog_Admin. Defines all hooks for the admin area.
	 * - Ech_Blog_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ech-blog-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ech-blog-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ech-blog-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ech-blog-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ech-blog-virtual-pages.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ech-blog-filters.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ech-blog-cates-tags.php';

		$this->loader = new Ech_Blog_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ech_Blog_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ech_Blog_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ech_Blog_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// ^^^ Add admin menu items
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ech_blog_admin_menu' );

		// ^^^ Register our plugin settings
		$this->loader->add_action('admin_init', $plugin_admin, 'reg_ech_blog_settings');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ech_Blog_Public( $this->get_plugin_name(), $this->get_version() );
		$virtual_page_public = new Ech_Blog_Virtual_Pages();

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );


		// ^^^ Register AJAX functions
		$this->loader->add_action( 'wp_ajax_nopriv_ECHB_load_more_posts', $plugin_public, 'ECHB_load_more_posts' );
		$this->loader->add_action( 'wp_ajax_ECHB_load_more_posts', $plugin_public, 'ECHB_load_more_posts' );

		$this->loader->add_action( 'wp_ajax_nopriv_ECHB_filter_blog_list', $plugin_public, 'ECHB_filter_blog_list' );
		$this->loader->add_action( 'wp_ajax_ECHB_filter_blog_list', $plugin_public, 'ECHB_filter_blog_list' );
		

		// ^^^ Add shortcodes
		$this->loader->add_shortcode( 'ech_blog', $plugin_public, 'ech_blog_func');
		$this->loader->add_shortcode( 'ech_blog_single_post_output', $virtual_page_public, 'ech_blog_single_post_output');
		$this->loader->add_shortcode( 'ech_blog_cate_tag_list_output', $virtual_page_public, 'ech_blog_cate_tag_list_output');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ech_Blog_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}

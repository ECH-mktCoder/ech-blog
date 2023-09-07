<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://#
 * @since      1.0.0
 *
 * @package    Ech_Blog
 * @subpackage Ech_Blog/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ech_Blog
 * @subpackage Ech_Blog/admin
 * @author     Toby Wong <tobywong@prohaba.com>
 */
class Ech_Blog_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		
		// Apply below files only in this plugin admin page
		if( isset($_GET['page']) && $_GET['page'] == 'reg_ech_blog_settings') {			
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ech-blog-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// Apply below files only in this plugin admin page
		if( isset($_GET['page']) && $_GET['page'] == 'reg_ech_blog_settings') {			
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ech-blog-admin.js', array( 'jquery' ), $this->version, false );
		}
	}


	/**
	 *  ^^^ Add ECH Blog admin menu
	 */
	public function ech_blog_admin_menu() {
		add_menu_page( 'ECH Blog Settings', 'ECH Blog', 'manage_options', 'reg_ech_blog_settings', array($this, 'ech_blog_admin_page'), 'dashicons-buddicons-activity', 110 );
	}
	// return view
	public function ech_blog_admin_page() {
		require_once ('partials/ech-blog-admin-display.php');
	}


	/**
	 * ^^^ Register custom fields for plugin settings
	 *
	 * @since    1.0.0
	 */
	public function reg_ech_blog_settings() {
		// Register all settings for general setting page
		register_setting( 'ech_blog_settings', 'ech_blog_apply_api_env');
		register_setting( 'ech_blog_settings', 'ech_blog_ppp');
		register_setting( 'ech_blog_settings', 'ech_blog_default_post_featured_img');
		register_setting( 'ech_blog_settings', 'ech_blog_channel_id');
		register_setting( 'ech_blog_settings', 'ech_blog_brand_id');
		register_setting( 'ech_blog_settings', 'ech_blog_category_filter');
		register_setting( 'ech_blog_settings', 'ech_blog_enable_breadcrumb');
		register_setting( 'ech_blog_settings', 'ech_blog_enable_single_post_brand_section');
	}



	/***********************************************************
	 * Get API Environment states
	 ***********************************************************/
	public function ADMIN_ECHPL_get_env_status() {
		$getApiEnv = get_option( 'ech_blog_apply_api_env' );
		if ( $getApiEnv == "0") {
			return 'DEV';
		} else {
			return 'LIVE';
		}
	}


	/***********************************************************
	 * Check API Environment and return the API domain
	 ***********************************************************/
	public function ADMIN_ECHB_getAPIDomain() {
		$getAPIEnv = get_option('ech_blog_apply_api_env'); 
		if ($getAPIEnv == 1) {
			$domain = "https://globalcms-api.umhgp.com/";
		} else {
			$domain = "https://globalcms-api-uat.umhgp.com";
		}

		return $domain;
	}


	/****************************************
	 * Get Blog JSON Using API
	 ****************************************/
	public function ADMIN_ECHB_curl_blog_json($api_link) {
		$ch = curl_init();

		$api_headers = array(
			'accept: application/json',
			'version: v1',
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $api_headers);
		curl_setopt($ch, CURLOPT_URL, $api_link);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);

		return $result;
	}

}

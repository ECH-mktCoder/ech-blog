<?php

/**
 * Fired during plugin activation
 *
 * @link       https://#
 * @since      1.0.0
 *
 * @package    Ech_Blog
 * @subpackage Ech_Blog/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ech_Blog
 * @subpackage Ech_Blog/includes
 * @author     Toby Wong <tobywong@prohaba.com>
 */
class Ech_Blog_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// connect to LIVE API when first activate
		$getApiEnv = get_option( 'ech_blog_apply_api_env' );
		if(empty($getApiEnv) || !$getApiEnv ) {
			add_option( 'ech_blog_apply_api_env', 1 );
		}

		// set post per page to 12
        $getPPP = get_option( 'ech_blog_ppp' );
        if(empty($getPPP) || !$getPPP ) {
            add_option( 'ech_blog_ppp', 12 );
        }

		// set channel id to 9
		$getChannelID = get_option( 'ech_blog_channel_id' );
		if(empty($getChannelID) || !$getChannelID ) {
            add_option( 'ech_blog_channel_id', 9 );
        }

		// set brand id to 0
		$getBrandID = get_option( 'ech_blog_brand_id' );
		if(empty($getBrandID) || !$getBrandID ) {
            add_option( 'ech_blog_brand_id', 0 );
        }

		// set default post featured image
        $getFeaturedImg = get_option( 'ech_blog_default_post_featured_img' );
        if(empty($getFeaturedImg) || !$getFeaturedImg ) {
            add_option( 'ech_blog_default_post_featured_img', plugin_dir_url( dirname( __FILE__ ) ).'assets/img/ec_logo.svg' );
        }

		// set breadcrumb status to 0 (disable)
		$getBreadcrumbStatus = get_option( 'ech_blog_enable_breadcrumb' );
		if(empty($getBreadcrumbStatus) || !$getBreadcrumbStatus ) {
            add_option( 'ech_blog_enable_breadcrumb', 0 );
        }

		// set single post brand section to 0 (disable)
		$getSPBrandSectionStatus = get_option( 'ech_blog_enable_single_post_brand_section' );
		if(empty($getSPBrandSectionStatus) || !$getSPBrandSectionStatus ) {
            add_option( 'ech_blog_enable_single_post_brand_section', 0 );
        }
		
	} // activate



	


}

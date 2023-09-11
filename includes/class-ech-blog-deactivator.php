<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://#
 * @since      1.0.0
 *
 * @package    Ech_Blog
 * @subpackage Ech_Blog/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Ech_Blog
 * @subpackage Ech_Blog/includes
 * @author     Toby Wong <tobywong@prohaba.com>
 */
class Ech_Blog_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		
		$pageSinglePost = get_page_by_path( 'health-blog/health-blog-content' );
    	wp_delete_post($pageSinglePost->ID);

		$pageBlogCateTagList = get_page_by_path( 'health-blog/health-blog-category-tag-list' );
    	wp_delete_post($pageBlogCateTagList->ID);
		
	}

}

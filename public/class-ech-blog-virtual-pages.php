<?php

class Ech_Blog_Virtual_Pages
{

  /************************************************************************
   * To avoid the error "generated X characters of unexpected output" ocurred during plugin activation, 
   * initialize_createVP function is called in define_public_hooks, add_action('init')
   * (folder: includes/class-ech-blog.php)
   * initialize_createVP() fires after WordPress has finished loading, but before any headers are sent.
   ************************************************************************/
  public static function initialize_createVP()
  {
    // add an option to make use ECHB_setupVP is triggered once per VP. Delete this option once all VP are created.
    add_option('run_init_createVP', 1);
  }


  public function ECHB_createVP()
  {
    if (get_option('run_init_createVP') == 1) {
      $this->ECHB_setupVP('Health Blog Content', 'health-blog-content', '[ech_blog_single_post_output]');
      $this->ECHB_setupVP('Health Blog Category Tag List', 'health-blog-category-tag-list', '[ech_blog_cate_tag_list_output]');

      // Delete this option once all VP are created.
      delete_option('run_init_createVP');
    }
  }

  private static function ECHB_setupVP($pageTitle, $pageSlug, $pageShortcode)
  {
    // Get parent page and get its id
    $get_parent_page = get_page_by_path('health-blog');

    $v_page = array(
      'post_type' => 'page',
      'post_title' => $pageTitle,
      'post_name' => $pageSlug,
      'post_content' => $pageShortcode,  // shortcode from this plugin
      'post_status' => 'publish',
      'post_author' => get_current_user_id(),
      'post_parent' => $get_parent_page->ID
    );

    wp_insert_post($v_page, true);
  } // ECHB_setupVP




  /******************************** VP SHORTCODE ********************************/

  public static function ech_blog_single_post_output()
  {
    if (!get_option('run_init_createVP')) {
      include('partials/ech-blog-single-post-view.php');
    }
  }  //--end ech_blog_single_post_output()


  public static function ech_blog_cate_tag_list_output()
  {
    if (!get_option('run_init_createVP')) {
      include('partials/ech-blog-category-tag-list-view.php');
    }
  } //ech_blog_cate_tag_list_output

  /******************************** (end) VP SHORTCODE ********************************/
} // class
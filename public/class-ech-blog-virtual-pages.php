<?php

class Ech_Blog_Virtual_Pages {

    public static function ech_blog_single_post_output() {
      include('partials/ech-blog-single-post-view.php');
    }  //--end ech_blog_single_post_output()


    public static function ech_blog_cate_tag_list_output() {
		  include('partials/ech-blog-category-tag-list-view.php');
    } //ech_blog_cate_tag_list_output

} // class
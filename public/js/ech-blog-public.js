(function( $ ) {
	'use strict';
	$(function () {

		$('.ech_blog_big_wrap .ech_blog_filter_container .D_categories_filter_container .D_cate_filter').on('click', function(){
		  $('.ech_blog_big_wrap .ech_blog_filter_container .D_categories_filter_container .active').removeClass('active');
		  $(this).addClass('active');
		  
		  getFilteredBlogPosts();
		});
	  
		$('.ech_blog_filter_container .filter_search_btn').click(function(){
		  getFilteredBlogPosts();
		});
	}); // ready

})( jQuery );


  
  
  
  /*************************************************
   * Load More Post JS
   *************************************************/
  function load_more_posts(topage) {
	jQuery(".ech_blog_container .loading_div").css("display", "block");
	jQuery(".all_posts_container").html("");
  
  
	var ppp = jQuery(".all_posts_container").data("ppp");
	var brand_id = jQuery(".all_posts_container").data("brand-id");
	var toPage = topage;
  
	var filterCate = jQuery(".all_posts_container").data("category");
	var filterTag = jQuery(".all_posts_container").data("tag");
	var filterTitle = jQuery(".all_posts_container").data("title");
  
	var ajaxurl = jQuery(".ech_blog_pagination").data("ajaxurl");
  
	
	jQuery.ajax({
	  url: ajaxurl,
	  type: "post",
	  data: {
		ppp: ppp,
		toPage: toPage,
		filterCate: filterCate,
		filterTitle: filterTitle,
		filterTag: filterTag,
		brand_id: brand_id,
		action: "ECHB_load_more_posts",
	  },
	  success: function (res) {
		var jsonObj = JSON.parse(res);
  
		jQuery(".all_posts_container").html(jsonObj.html);
		jQuery(".ech_blog_container .loading_div").css("display", "none");
		jQuery('html, body').animate({ scrollTop: jQuery('.echb_page_anchor').offset().top }, 0);
		
  
	  },
	  error: function (res) {
		console.error(res);
	  },
	});
  }
  
  
  
  
  /*************************************************
   * Get filtered blog posts
   *************************************************/
  function getFilteredBlogPosts() {
	// get filter value
	var filter_cate = '';
	var filter_title = jQuery("#title_filter").val();
	var filter_cate = jQuery("#categories_filter").val();
	var ppp = jQuery(".all_posts_container").data("ppp");
	var brand_id = jQuery(".all_posts_container").data("brand-id");
  
	if(jQuery(window).width() > 1024) {
	  filter_cate = jQuery(".D_cate_filter.active").data('catefilterid');
	} else {
	  filter_cate = jQuery("#categories_filter_M").val();
	}
  
	// clean DOM 
	jQuery(".ech_blog_container .loading_div").css("display", "block");
	jQuery(".all_posts_container").html("");
  
	// ajax
	var ajaxurl = jQuery(".ech_blog_pagination").data("ajaxurl");
  
  
	jQuery.ajax({
	  url: ajaxurl,
	  type: "post",
	  data: {
		ppp: ppp,
		brand_id: brand_id,
		filterTitle: filter_title,
		filterCate: filter_cate,
		action: "ECHB_filter_blog_list",
	  },
	  success: function (res) {
		var jsonObj = JSON.parse(res);
		//console.log('api: ' + jsonObj.api);
		//console.log('html: ' + jsonObj.html);
		jQuery(".all_posts_container").html(jsonObj.html);
		jQuery(".ech_blog_container .loading_div").css("display", "none");
  
		jQuery(".all_posts_container").data("title", filter_title);
		jQuery(".all_posts_container").attr("data-title", filter_title);
  
		jQuery(".all_posts_container").data("category", filter_cate);
		jQuery(".all_posts_container").attr("data-category", filter_cate);
  
		
		if(jsonObj.max_page > 1) {
		  jQuery(".ech_blog_pagination").attr("data-max-page", jsonObj.max_page);       
		  jQuery(".ech_blog_pagination").data("max-page", jsonObj.max_page);       
		  paginationGenerate(1);
		  
		  jQuery(".ech_blog_pagination").css("display", "block");
  
		} else {
		  jQuery(".ech_blog_pagination").css("display", "none");
		}
		
  
	  },
	  error: function (res) {
		console.error(res);
	  },
	});
  }
<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://#
 * @since      1.0.0
 *
 * @package    Ech_Blog
 * @subpackage Ech_Blog/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ech_Blog
 * @subpackage Ech_Blog/public
 * @author     Toby Wong <tobywong@prohaba.com>
 */
class Ech_Blog_Public
{

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


	private $filters;
	private $cates_tags_list;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->filters = new Ech_Blog_Filters();
		$this->cates_tags_list = new Ech_Blog_Cates_Tags();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		global $TRP_LANGUAGE;

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ech-blog-public.css', array(), $this->version, 'all');

		if (strpos($_SERVER['REQUEST_URI'], "health-blog-content") !== false) {
			wp_enqueue_style($this->plugin_name . '_single_post', plugin_dir_url(__FILE__) . 'css/ech-blog-single-post.css', array(), $this->version, 'all');

			if ($TRP_LANGUAGE == 'zh_HK' || $TRP_LANGUAGE == 'zh_CN') {
				wp_enqueue_style($this->plugin_name . '_single_post_ZH', plugin_dir_url(__FILE__) . 'css/ech-blog-single-post-zh.css', array(), $this->version, 'all');
			}
		}


		if (strpos($_SERVER['REQUEST_URI'], "health-blog-category-tag-list") !== false) {
			wp_enqueue_style($this->plugin_name . '_cates_tags_list', plugin_dir_url(__FILE__) . 'css/ech-blog-cates-tags-list.css', array(), $this->version, 'all');
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		if (strpos($_SERVER['REQUEST_URI'], "health-blog") !== false) {
			if (count(explode('/', $_SERVER['REQUEST_URI'])) == 3) {
				wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ech-blog-public.js', array('jquery'), $this->version, false);
				wp_enqueue_script($this->plugin_name . '_pagination', plugin_dir_url(__FILE__) . 'js/ech-blog-pagination.js', array('jquery'), $this->version, false);
			}
		}
	}



	public function ech_blog_func($atts)
	{

		/*** shortcode atts ***/
		$paraArr = shortcode_atts(array(
			'ppp' => null,
			'show_cate' => null
		), $atts);

		if ($paraArr['ppp'] == null) {
			$ppp = get_option('ech_blog_ppp');
		} else {
			$ppp = (int)$paraArr['ppp'];
		}

		if ($paraArr['show_cate'] == null) {
			$show_cate = get_option('ech_blog_category_filter');
		} else {
			$show_cate = (int)$paraArr['show_cate'];
		}
		/*** (end) shortcode atts ***/

		$channel_id = get_option('ech_blog_channel_id');
		$brand_id = get_option('ech_blog_brand_id');


		// check if specific category filters are set. If yes, get the first category articles
		if ( trim(get_option('ech_blog_category_filter')) == '' ) {
			$api_args = array(
				'page_size' => $ppp,
				'channel_id' => $channel_id,
				'brand_id' => $brand_id
			);
		} else {
			$filterCateArr = explode(',', get_option('ech_blog_category_filter'));
			$api_args = array(
				'page_size' => $ppp,
				'channel_id' => $channel_id,
				'brand_id' => $brand_id,
				'cate_id' => $filterCateArr[0]
			);
		}
		

		$api_link = $this->ECHB_gen_blog_link_api_link($api_args);

		$output = '';

		$output .= '<div class="ech_blog_big_wrap">';
		$output .= '<div class="echb_page_anchor"></div>'; // anchor

		/***** Filters *****/

		$output .= '<div class="ech_blog_filter_container">';

		$output .= $this->filters->ECHB_get_categories_list($show_cate);
		$output .= $this->filters->ECHB_get_filter_title();

		$output .= '<div class="filter_search_btn">' . $this->ECHB_echolang(['Search', '搜尋', '搜寻']) . '</div>';
		$output .= '</div>'; //ech_blog_filter_container
		/***** (end)Filters *****/


		/*********** POST LIST ************/
		$output .= '<div class="ech_blog_container" >';
		$get_blog_json = $this->ECHB_curl_blog_json($api_link);
		$json_arr = json_decode($get_blog_json, true);

		/*** loading div ***/
		$output .= '<div class="loading_div"><p>' . $this->ECHB_echolang(['Loading...', '載入中...', '载入中...']) . '</p></div>';
		/*** (end) loading div ***/

		$output .= '<div class="all_posts_container" data-ppp="' . $ppp . '" data-channel="' . $channel_id . '" data-brand-id="' . $brand_id . '" data-category="" data-title="" data-tag="" >';
		foreach ($json_arr['result'] as $post) {
			$output .= $this->ECHB_load_post_card_template($post, $brand_id);
		}
		$output .= '</div>'; //all_posts_container


		/*** pagination ***/
		$total_posts = $json_arr['count'];
		$max_page = ceil($total_posts / $ppp);


		$output .= '<div class="ech_blog_pagination" data-current-page="1" data-max-page="' . $max_page . '" data-topage="" data-brand-id="' . $brand_id . '" data-ajaxurl="' . get_admin_url(null, 'admin-ajax.php') . '"></div>';

		$output .= '</div>'; //ech_blog_container

		/*********** (END) POST LIST ************/

		$output .= '</div>'; //ech_blog_big_wrap


		return $output;
	} // ech_blog_func() 







	/****************************************
	 * Load Single Post Template
	 * $scAttr_brand_id = brand id requested in shortcode attributes 
	 ****************************************/
	public function ECHB_load_post_card_template($post, $scAttr_brand_id)
	{
		$html = '';


		$thumbnail_arr_en = json_decode($post['en_blog_img'], true);
		$thumbnail_arr_zh = json_decode($post['tc_blog_img'], true);
		$thumbnail_arr_sc = json_decode($post['cn_blog_img'], true);


		// check ZH thumbnail if it is empty, empty EN and SC thumbnails will use ZH thumbnails becoz of ECHB_echolang()
		if ($thumbnail_arr_zh[0] == "") {
			$thumbnail_arr_zh[0] = get_option('ech_blog_default_post_featured_img');
		}


		$publish_date = $post['product_time'];

		/***** CATEGORY *****/
		$cateArrEn = array();
		$cateArrZH = array();
		$cateArrSC = array();
		foreach ($post['category'] as $label) {
			array_push($cateArrEn, array('type' => 'category', 'tag_id' => $label['article_category_id'], 'tag_name' => $label['en_name']));
			array_push($cateArrZH, array('type' => 'category', 'tag_id' => $label['article_category_id'], 'tag_name' => $label['tc_name']));
			array_push($cateArrSC, array('type' => 'category', 'tag_id' => $label['article_category_id'], 'tag_name' => $label['cn_name']));
		}
		/***** (END) CATEGORY *****/


		/***** TAG *****/
		$tagsArrEN = array();
		$tagsArrZH = array();
		$tagsArrSC = array();
		foreach ($post['label'] as $label) {
			array_push($tagsArrEN, array('type' => 'tag', 'tag_id' => $label['label_id'], 'tag_name' => $label['en_name']));
			array_push($tagsArrZH, array('type' => 'tag', 'tag_id' => $label['label_id'], 'tag_name' => $label['tc_name']));
			array_push($tagsArrSC, array('type' => 'tag', 'tag_id' => $label['label_id'], 'tag_name' => $label['cn_name']));
		}
		/***** (END)TAG *****/


		$html .= '<div class="single_blog_post_card">';

		$html .= '<div class="post_thumb"><a href="' . site_url() . '/health-blog/health-blog-content/?article_id=' . $post['forever_article_id'] . '&post_version=' . $post['forever_version'] . '&scAttr_brand_id=' . $scAttr_brand_id . '"><img src="' . $this->ECHB_echolang([$thumbnail_arr_en[0], $thumbnail_arr_zh[0], $thumbnail_arr_sc[0]]) . '" /></a></div>';
		$html .= '<div class="post_title"><a href="' . site_url() . '/health-blog/health-blog-content/?article_id=' . $post['forever_article_id'] . '&post_version=' . $post['forever_version'] . '&scAttr_brand_id=' . $scAttr_brand_id . '">' . $this->ECHB_echolang([$post['en_title'], $post['tc_title'], $post['cn_title']]) . '</a></div>';
		$html .= '<div class="post_date">' . date('d/m/Y', $publish_date) . '</div>';

		$html .= '<div class="post_cate"> <strong>' . $this->ECHB_echolang(['Categories', '類別', '类别']) . ': </strong> ' . $this->ECHB_echolang([$this->ECHB_apply_comma_from_array($cateArrEn, $scAttr_brand_id), $this->ECHB_apply_comma_from_array($cateArrZH, $scAttr_brand_id), $this->ECHB_apply_comma_from_array($cateArrSC, $scAttr_brand_id)]) . '</div>';

		$html .= '<div class="post_tag"> <strong>' . $this->ECHB_echolang(['Tags', '標籤', '标签']) . ': </strong> ' . $this->ECHB_echolang([$this->ECHB_apply_comma_from_array($tagsArrEN, $scAttr_brand_id), $this->ECHB_apply_comma_from_array($tagsArrZH, $scAttr_brand_id), $this->ECHB_apply_comma_from_array($tagsArrSC, $scAttr_brand_id)]) . '</div>';


		$html .= '</div>'; //single_blog_post_card

		return $html;
	}




	/****************************************
	 * Load more posts
	 ****************************************/
	public function ECHB_load_more_posts()
	{
		$ppp = $_POST['ppp'];
		$toPage = $_POST['toPage'];
		$brand_id = $_POST['brand_id'];
		$filterTitle = $_POST['filterTitle'];
		$filterCate = $_POST['filterCate'];
		$filterTag = $_POST['filterTag'];


		$api_args = array(
			'page_size' => $ppp,
			'page' => $toPage,
			'brand_id' => $brand_id,
			'title' => $filterTitle,
			'cate_id' => $filterCate,
			'tag_id' => $filterTag
		);
		$api_link = $this->ECHB_gen_blog_link_api_link($api_args);

		$get_blog_json = $this->ECHB_curl_blog_json($api_link);
		$json_arr = json_decode($get_blog_json, true);

		$html = '';
		$max_page = '';

		if (isset($json_arr['result']) && $json_arr['count'] != 0) {
			$total_posts = $json_arr['count'];
			$max_page = round($total_posts / $ppp, 0);

			foreach ($json_arr['result'] as $post) {
				$html .= $this->ECHB_load_post_card_template($post, $brand_id);
			}
		} else {
			$html .= $this->ECHB_echolang(['No posts ...', '沒有文章', '没有文章']);
		}

		echo json_encode(array('html' => $html, 'max_page' => $max_page), JSON_UNESCAPED_SLASHES);

		wp_die();
	}



	/**************************** API ****************************/


	/***********************************************************
	 * Check API Environment and return the API domain
	 ***********************************************************/
	public function ECHB_getAPIDomain()
	{
		$getAPIEnv = get_option('ech_blog_apply_api_env');
		if ($getAPIEnv == 1) {
			$domain = "https://globalcms-api.umhgp.com/";
		} else {
			$domain = "https://globalcms-api-uat.umhgp.com";
		}

		return $domain;
	}

	/****************************************
	 * Filter and merge value and return a full API Blog List link. 
	 * Array key: page, page_size, channel_id, get_type, title, content, label_name, publisher_name
	 ****************************************/
	public function ECHB_gen_blog_link_api_link(array $args)
	{
		$full_api = $this->ECHB_getAPIDomain() . '/v1/api/blog_article_list?';

		if (!empty($args['page'])) {
			$full_api .= 'page=' . $args['page'];
		} else {
			$full_api .= 'page=1';
		}


		if (!empty($args['page_size'])) {
			$full_api .= '&';
			$full_api .= 'page_size=' . $args['page_size'];
		} else {
			$full_api .= '&';
			$full_api .= 'page_size=9';
		}


		if (!empty($args['channel_id'])) {
			$full_api .= '&';
			$full_api .= 'channel_id=' . $args['channel_id'];
		} else {
			$full_api .= '&';
			$full_api .= 'channel_id=9';
		}


		if (!empty($args['get_type'])) {
			$full_api .= '&';
			$full_api .= 'get_type=' . $args['get_type'];
		} else {
			$full_api .= '&';
			$full_api .= 'get_type=1';
		}


		if (!empty($args['title'])) {
			$full_api .= '&';
			$full_api .= 'title=' . $args['title'];
		}

		if (!empty($args['content'])) {
			$full_api .= '&';
			$full_api .= 'content=' . $args['content'];
		}

		if (!empty($args['cate_id'])) {
			$full_api .= '&';
			$full_api .= 'article_category_id=' . $args['cate_id'];
		}

		if (!empty($args['tag_id'])) {
			$full_api .= '&';
			$full_api .= 'label_id=' . $args['tag_id'];
		}


		if (!empty($args['publisher_name'])) {
			$full_api .= '&';
			$full_api .= 'publisher_name=' . $args['publisher_name'];
		}

		if (!empty($args['brand_id'])) {
			$full_api .= '&';
			$full_api .= 'brand_id=' . $args['brand_id'];
		}


		return $full_api;
	}

	/****************************************
	 * Filter and merge value and return a full API Post Content link. 
	 * Array key: article_id, channel_id
	 ****************************************/
	public function ECHB_gen_post_api_link(array $args)
	{
		$full_api = $this->ECHB_getAPIDomain() . '/v1/api/article_detail/?blog=1';

		if (!empty($args['article_id'])) {
			$full_api .= '&';
			$full_api .= 'article_id=' . $args['article_id'];
		}

		if (!empty($args['version'])) {
			$full_api .= '&';
			$full_api .= 'version=' . $args['version'];
		}

		if (!empty($args['channel_id'])) {
			$full_api .= '&';
			$full_api .= 'channel_id=' . $args['channel_id'];
		} else {
			$full_api .= '&';
			$full_api .= 'channel_id=9';
		}

		return $full_api;
	}

	public function ECHB_test(array $args)
	{
		//$test = 'test only - ' . $args['article_id'] . ' | ' . Ech_Blog_Public::ECHB_getAPIDomain();
		$test = 'test only - ' . $args['article_id'] . ' | ' . $this->ECHB_getAPIDomain();
		return $test;
	}
	/**************************** (end)API ****************************/



	/****************************************
	 * Filter blog posts
	 * filter: category, title
	 ****************************************/
	public function ECHB_filter_blog_list()
	{
		$ppp = $_POST['ppp'];
		$brand_id = $_POST['brand_id'];
		$filterCate = $_POST['filterCate'];
		$filterTitle = $_POST['filterTitle'];

		$api_args = array(
			'page_size' => $ppp,
			'brand_id' => $brand_id,
			'cate_id' => $filterCate,
			'title' => $filterTitle
		);
		$api_link = $this->ECHB_gen_blog_link_api_link($api_args);


		$get_blog_json = $this->ECHB_curl_blog_json($api_link);
		$json_arr = json_decode($get_blog_json, true);
		$html = '';


		$max_page = '';
		if (isset($json_arr['result']) && $json_arr['count'] != 0) {
			$total_posts = $json_arr['count'];
			$max_page = round($total_posts / $ppp, 0);
			foreach ($json_arr['result'] as $post) {
				$html .= $this->ECHB_load_post_card_template($post, $brand_id);
			}
		} else {
			$html .= $this->ECHB_echolang(['No posts ...', '沒有文章', '没有文章']);
		}

		echo json_encode(array('html' => $html, 'max_page' => $max_page, 'api' => $api_link), JSON_UNESCAPED_SLASHES);

		wp_die();
	}




	/****************************************
	 * Get Blog JSON Using API
	 ****************************************/
	public function ECHB_curl_blog_json($api_link)
	{
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


	/****************************************
	 * DISPLAY SPECIFIC LANGUAGE
	 ****************************************/
	public function ECHB_echolang($stringArr)
	{
		global $TRP_LANGUAGE;

		switch ($TRP_LANGUAGE) {
			case 'zh_HK':
				$langString = $stringArr[1];
				break;
			case 'zh_CN':
				$langString = $stringArr[2];
				break;
			default:
				$langString = $stringArr[0];
		}

		if (empty($langString) || $langString == '' || $langString == null) {
			$langString = $stringArr[1]; //zh_HK
		}

		return $langString;
	}
	/********** (END)DISPLAY SPECIFIC LANGUAGE **********/




	public function ECHB_displayPostContent($contentLangArr)
	{

		global $TRP_LANGUAGE;

		switch ($TRP_LANGUAGE) {
			case 'zh_HK':
				$contentArr = $contentLangArr[1];
				break;
			case 'zh_CN':
				$contentArr = $contentLangArr[2];
				break;
			default:
				$contentArr = $contentLangArr[0];
		}



		$html = '';
		foreach ($contentArr as $key => $data) {
			// If empty value in EN and SC, display ZH value
			if ($data['title'] == '') {
				$data['title'] = $contentLangArr[1][$key]['title'];
			}
			if ($data['content'] == '') {
				$data['content'] = $contentLangArr[1][$key]['content'];
			}
			if ($data['desktop_img'] == '') {
				$data['desktop_img'] = $contentLangArr[1][$key]['desktop_img'];
			}
			if ($data['mobile_img'] == '') {
				$data['mobile_img'] = $contentLangArr[1][$key]['mobile_img'];
			}

			// display content
			$html .= '<div class="ECHB_p_section">';
			$html .= '<h2>' . $data['title'] . '</h2>';
			$html .= '<p>' . $data['content'] . '</p>';
			if ($data['desktop_img'] != '') {
				$html .= '<img src="' . $data['desktop_img'] . '" class="hidden_b_w1024" />';
			}

			if ($data['mobile_img'] != '') {
				$html .= '<img src="' . $data['mobile_img'] . '" class="show_b_w1024" />';
			}
			$html .= '</div>'; //ECHB_p_section
		}

		return $html;
	}


	/****************************************
	 * Sort Content Paragraphs. 
	 * This function is used to get the corresponding ZH values if empty values in EN and SC
	 ****************************************/
	public function ECHB_sortContentArr($contentArr)
	{
		// Sort paragraphs by the value 'sort'. Sort empty 'sort' value at the end of the array
		usort($contentArr, function ($a, $b) {
			if ($a['sort'] == "") return 1;
			if ($b['sort'] == "") return -1;
			return $a['sort'] - $b['sort'];
		});

		// find FOOTER array and temporary remove it from content array. 
		foreach ($contentArr as $k => $pArr) {
			if ($pArr['part'] == 'FOOTER') {
				$footerArr = $pArr;
				unset($contentArr[$k]);
			}
		}

		// re-index the content array
		$contentArr = array_values($contentArr);

		// if FOOTER array exist, add it back at the end of content array
		if (isset($footerArr)) {
			array_push($contentArr, $footerArr);
		}


		return $contentArr;
	}


	/****************************************
	 * Blog List - categories / tags comma separated list from array
	 * This function is used to create a comma sparated list from an array. It is used on API Blog list categories / tags display
	 ****************************************/
	public function ECHB_apply_comma_from_array($langArr, $scAttr_brand_id)
	{
		$prefix = $commaList = '';
		$type = '';

		$scAttr_brand_id = rtrim($scAttr_brand_id, "/");

		foreach ($langArr as $itemArr) {
			if ($itemArr['type'] == 'tag') {
				$type = 'tag_id=';
				$commaList .= $prefix . '<span class="echb_tag"><a href="' . site_url() . '/health-blog/health-blog-category-tag-list/?brand_id=' . $scAttr_brand_id . '&' . $type . $itemArr['tag_id'] . '">#' . $itemArr['tag_name'] . '</a></span>';
			} else {
				$type = 'cate_id=';
				$commaList .= $prefix . '<a href="' . site_url() . '/health-blog/health-blog-category-tag-list/?brand_id=' . $scAttr_brand_id . '&' . $type . $itemArr['tag_id'] . '">' . $itemArr['tag_name'] . '</a>';
			}
			
			$prefix = ", ";
		}

		return $commaList;
	}
}

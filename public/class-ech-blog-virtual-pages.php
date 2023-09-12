<?php

class Ech_Blog_Virtual_Pages extends Ech_Blog_Public {


    public function __construct() {
        add_action('template_redirect', 'ECHB_redirection');
    }
    /************************************************************************
     * To avoid the error "generated X characters of unexpected output" ocurred during plugin activation, 
     * initialize_createVP function is called in define_public_hooks, add_action('init')
     * (folder: includes/class-ech-blog.php)
     * initialize_createVP() fires after WordPress has finished loading, but before any headers are sent.
     ************************************************************************/
    public static function initialize_createVP() {
        // add an option to make use ECHB_setupVP is triggered once per VP. Delete this option once all VP are created.
        add_option('run_init_createVP', 1);
    }


    public function ECHB_createVP() {
        if (get_option('run_init_createVP') == 1) {
        $this->ECHB_setupVP('Health Blog Content', 'health-blog-content', '[ech_blog_single_post_output]');
        $this->ECHB_setupVP('Health Blog Category Tag List', 'health-blog-category-tag-list', '[ech_blog_cate_tag_list_output]');

        // Delete this option once all VP are created.
        delete_option('run_init_createVP');
        }
    }

    private static function ECHB_setupVP($pageTitle, $pageSlug, $pageShortcode){
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

    public function ech_blog_single_post_output($atts) {
        if (!get_option('run_init_createVP')) {
            global $wp;

            $atts = shortcode_atts(array(
                'article_id'    => isset($_GET['article_id']) ? sanitize_key($_GET['article_id']) : '',
                'post_version'  => isset($_GET['post_version']) ? sanitize_key($_GET['post_version']) : '',
                'brand_id'      => isset($_GET['scAttr_brand_id']) ? sanitize_key($_GET['scAttr_brand_id']) : '',
            ), $atts);


            $articleID  = $atts['article_id'];
            $postVersion  = $atts['post_version'];
            $brandID = $atts['brand_id'];

            if (!isset($articleID) || !isset($postVersion ) || empty($articleID) || empty($postVersion)) {
                echo '<script>window.location.replace("/health-blog");</script>';
            }


            $args = array(
                'article_id' => $articleID,
                'version' => $postVersion
            );

            $api_link = parent::ECHB_gen_post_api_link($args);
            $get_post_json = parent::ECHB_curl_blog_json($api_link);
            $json_arr = json_decode($get_post_json, true);
            
            if (!isset($json_arr['result_code']) || $json_arr['result_code'] != 0) {
                echo '<script>window.location.replace("/health-blog");</script>';
            }

            /*********************************************************
             * Meta Data Conditions on unrelated brand articles
             *  - disable Google indexing
             *  - empty canonical url
             *********************************************************/
            $get_post_brandID = $json_arr['result']['brand'][0]['forever_brand_id'];

            if ($get_post_brandID != $_GET['scAttr_brand_id'] || empty($_GET['scAttr_brand_id'])) {

                /* Disable Google indexing */
                add_filter('rank_math/frontend/robots', function ($robots) {
                    $robots["follow"] = 'nofollow';
                    $robots["index"] = 'noindex';
                    return $robots;
                });

                /* Empty canonical url */
                add_filter('rank_math/frontend/canonical', function ($canonical) {
                    $canonical = "";
                    return $canonical;
                });
            } // end if
            /***** (END) Disable Google indexing on unrelated brand articles *****/

            $post = $json_arr['result'];

            $contentMainImg_en = json_decode($post['en_blog_img'], true);
            $contentMainImg_zh = json_decode($post['tc_blog_img'], true);
            $contentMainImg_sc = json_decode($post['cn_blog_img'], true);


            $contentEN = json_decode($post['en_blog_content'], true);
            $contentZH = json_decode($post['tc_blog_content'], true);
            $contentSC = json_decode($post['cn_blog_content'], true);



            // Sort paragraphs by value 'sort' using function in the plugin
            $contentZH = parent::ECHB_sortContentArr($contentZH);
            $contentEN = parent::ECHB_sortContentArr($contentEN);
            $contentSC = parent::ECHB_sortContentArr($contentSC);

            $html = '';

            $getSPBrandSectionStatus = get_option('ech_blog_enable_single_post_brand_section');
            $getBreadcrumbStatus = get_option('ech_blog_enable_breadcrumb');
            $post_title = parent::ECHB_echolang([$post['en_title'], $post['tc_title'], $post['cn_title']]); 


            $html .= '<div class="all_single_post_wrap">';
                if( $getBreadcrumbStatus == 1 ) {
                $html .= '<div class="sp_breadcrumb">';
                    $html .= '<div><a href="'.site_url().'">' . parent::ECHB_echolang(['Home', '主頁', '主页']) . '</a> > <a href="'.site_url() . '/health-blog/'.'">'.parent::ECHB_echolang(['Health Blog', '健康資訊', '健康资讯']).'</a> > ' . $post_title . '</div>';
                $html .= '</div>'; // sp_breadcrumb
                }

                $html .= '<div class="ECHB_back_to_blog_list">';
                $html .= '<a href="'.site_url().'/health-blog/"> < '.parent::ECHB_echolang(['Back to health blog', '返回健康專欄', '返回健康专栏']).'</a>';
                $html .= '</div>';

                $html .= '<div class="single_post_container">';
                $temp_class = ($getSPBrandSectionStatus == 0) ? 'post_container full_width':'post_container';
                $html .= '<div class="'. $temp_class . '">';
                    $html .= '<div class="post_title">';
                    $html .= '<h1>'.$post_title.'</h1>';
                    $html .= '</div>'; // .post_title

                    $html .= '<div class="post_info">';
                    $html .= '<div class="post_date">'.date('d/m/Y', $post['product_time']).'</div>';
                    /***** TAG *****/
                    $tagsArrEN = array();
                    $tagsArrZH = array();
                    $tagsArrSC = array();
                    foreach ($post['label'] as $label) {
                        array_push($tagsArrEN, array('type' => 'tag', 'tag_id' => $label['label_id'], 'tag_name' => $label['label_en_name']));
                        array_push($tagsArrZH, array('type' => 'tag', 'tag_id' => $label['label_id'], 'tag_name' => $label['label_tc_name']));
                        array_push($tagsArrSC, array('type' => 'tag', 'tag_id' => $label['label_id'], 'tag_name' => $label['label_cn_name']));
                    }
                    
                    $html .= '<div class="post_tag">'. parent::ECHB_echolang(['Topics', '標籤', '标签']) .':'. parent::ECHB_echolang([parent::ECHB_apply_comma_from_array($tagsArrEN, $brandID), parent::ECHB_apply_comma_from_array($tagsArrZH, $brandID), parent::ECHB_apply_comma_from_array($tagsArrSC, $brandID)]) . '</div>';
                    /***** (END)TAG *****/



                    /***** SHARE TEXT *****/
                    $shareTxt = parent::ECHB_echolang([$post['en_share'], $post['tc_share'], $post['cn_share']]);
                    if ($shareTxt == '' || $shareTxt == null) {
                        $shareTxt = $post_title;
                    }
                    $shareTxt = str_replace(' ', '_', $shareTxt);
                    $shareTxt = preg_replace('~[^\p{L}\p{N}\_]+~u', '', $shareTxt);
                    
                    $html .= '<div class="post_share">';
                        $html .= '<a href="https://www.facebook.com/sharer/sharer.php?u='. home_url(add_query_arg($_GET, $wp->request)) . '" target="_blank"><img src="'.plugin_dir_url(dirname(__FILE__, 1)) .'assets/img/author-fb.svg" alt="" class="post_fb"></a>';
                        $html .= '<a href="https://www.linkedin.com/shareArticle?mini=true&url='.home_url(add_query_arg($_GET, $wp->request)) .'" target="_blank"><img src="'.plugin_dir_url(dirname(__FILE__, 1)) .'assets/img/author-linkedin.svg" alt="" class="post_llinkedin"></a>';
                        $html .= '<a href="https://api.whatsapp.com/send?text='. $shareTxt .'%20-%20'. home_url(add_query_arg($_GET, $wp->request)) .'" data-action="share/whatsapp/share" target="_blank"><img src="'. plugin_dir_url(dirname(__FILE__, 1)) .'assets/img/author_wtsapp.svg" alt="" class="post_wtsapp"></a>';
                        $html .= '';
                    $html .= '</div>'; // .post_share
                    /***** (END)SHARE TEXT *****/
                    $html .= '</div>'; // .post_info

                    $html .= '<div class="post_content">';
                        $html .= '<div class="content_main_img">';
                            $html .= '<img src="'.parent::ECHB_echolang([$contentMainImg_en[1], $contentMainImg_zh[1], $contentMainImg_sc[1]]).'" alt="" class="hidden_b_w1024">';
                            $html .= '<img src="'. parent::ECHB_echolang([$contentMainImg_en[3], $contentMainImg_zh[3], $contentMainImg_sc[3]]).'" alt="" class="show_b_w1024">';
                        $html .= '</div>'; // .content_main_img

                        $html .= '<div class="content">';
                        $html .= parent::ECHB_displayPostContent([$contentEN, $contentZH, $contentSC]);
                        $html .= '</div>';

                        $html .= '<div class="post_source">';
                        if ($post['blog_published_sources'] == 1) {
                            foreach($post['doctors'] as $dr) {
                                // Dr Name
                                $dr_nameEN = $dr['en_salutation'] . ' ' . $dr['en_name'];
                                $dr_nameZH = $dr['tc_name'] . $dr['tc_salutation'];
                                $dr_nameSC = $dr['cn_name'] . $dr['cn_salutation'];

                                // specialist fields
                                $spArrEN = array();
                                $spArrZH = array();
                                $spArrSC = array();

                                foreach ($dr['specialty_list'] as $spList) {
                                    array_push($spArrEN, $spList['en_name']);
                                    array_push($spArrZH, $spList['tc_name']);
                                    array_push($spArrSC, $spList['cn_name']);
                                }
                                $html .= '<div class="dr_source">';
                                    $html .= '<div class="dr_profile"><img src="'. $dr['avatar'] .'" alt=""></div>';
                                    $html .= '<div class="dr_info">';
                                        $html .= '<div class="dr_name">' . parent::ECHB_echolang([$dr_nameEN, $dr_nameZH, $dr_nameSC]). '</div>';
                                        $html .= '<div class="dr_field">'. parent::ECHB_echolang([implode(', ', $spArrEN), implode(', ', $spArrZH), implode(', ', $spArrSC)]).' </div> ';
                                        
                                        if ($post['tc_blog_url'] != '') {
                                            
                                            $html .= '<div class="dr_booking"><a href="'. parent::ECHB_echolang([$post['en_blog_url'], $post['tc_blog_url'], $post['cn_blog_url']]) .'" target="_blank">'. parent::ECHB_echolang(['Book Appointment', '預約醫生', '预约医生']) .'</a></div>';
                                            
                                        }
                                    $html .= '</div>'; // .dr_info

                                $html .= '</div>'; // .dr_source
                            } // foreach $post['doctors'] as $dr
                            

                        } else {
                            $html .= '<div class="media_source">'. parent::ECHB_echolang(['Source', '來源', '来源']) .': <a href="'. parent::ECHB_echolang([$post['en_blog_url'], $post['tc_blog_url'], $post['cn_blog_url']]) .'" target="_blank">'. parent::ECHB_echolang([$post['en_issuer'], $post['tc_issuer'], $post['cn_issuer']]) .'</a></div>';
                            
                        } // if $post['blog_published_sources'] == 1
                        $html .= '</div>'; // .post_source                    
                    $html .= '</div>'; // .post_content
                $html .= '</div>'; // .post_container

                
                if ($getSPBrandSectionStatus == 1) {
                    $html .= '<div class="brand_container">';
                        $html .= '<div class="inner_brand_container">';
                            if (!empty($post['brand'])) {

                                $html .= '<p>'. parent::ECHB_echolang(['Related Brands', '相關品牌', '相关品牌']) . '</p>';
                                foreach ($post['brand'] as $brand) {
                                    $brandImgEN = json_decode($brand['en_picture'], true);
                                    $brandImgZH = json_decode($brand['tc_picture'], true);
                                    $brandImgSC = json_decode($brand['cn_picture'], true);

                                    $html .= '<div class="single_brand_container" data-brandid="'. $brand['forever_brand_id'] .'">';
                                    if ($brandImgZH[0] != '') {
                                        $html .= '<div class="brand_img">';
                                            $html .= '<img src="'. parent::ECHB_echolang([$brandImgEN[0], $brandImgZH[0], $brandImgSC[0]]) .'" alt="'. parent::ECHB_echolang([$brand['en_name'], $brand['tc_name'], $brand['cn_name']]) .'">';
                                        $html .= '</div>';
                                    }
                                    $html .= '<div class="brand_name">'. parent::ECHB_echolang([$brand['en_name'], $brand['tc_name'], $brand['cn_name']]) .'</div>';

                                    if ($brand['brand_website_url'] != null || $brand['brand_website_url'] != '') {
                                        $html .= '<div class="brand_learn_more"><a href="'. $brand['brand_website_url'] .'" target="_blank">'. parent::ECHB_echolang(['Learn More', '了解更多', '了解更多']) .'</a></div>';
                                    }
                                    $html .= '</div>'; // .single_brand_container
                                } // foreach $post['brand'] as $brand
                            } // if !empty($post['brand'])
                            
                        $html .= '</div>'; // .inner_brand_container
                    $html .= '</div>'; // .brand_container
                } // if $getSPBrandSectionStatus == 1
                


                $html .= '</div>'; // .single_post_container

            $html .= '</div>'; // .all_single_post_wrap

            

            return $html;
        } // if run_init_createVP
    }  //--end ech_blog_single_post_output()





    public function ech_blog_cate_tag_list_output($atts) {
        /* if (!get_option('run_init_createVP')) {
        include('partials/ech-blog-category-tag-list-view.php');
        } */
        if (!get_option('run_init_createVP')) {

            $atts = shortcode_atts(array(
                'cate_id'    => isset($_GET['cate_id']) ? sanitize_key($_GET['cate_id']) : '',
                'tag_id'  => isset($_GET['tag_id']) ? sanitize_key($_GET['tag_id']) : '',
                'brand_id'      => isset($_GET['brand_id']) ? sanitize_key($_GET['brand_id']) : '',
            ), $atts);


            $cateID  = $atts['cate_id'];
            $tagID  = $atts['tag_id'];
            $brandID = $atts['brand_id'];

            if( (!isset($cateID) && !isset($tagID)) || (empty($cateID) && empty($tagID)) ) {
                echo '<script>window.location.replace("/health-blog");</script>';
            }

            $ppp = get_option( 'ech_blog_ppp' );
            $channelID = get_option( 'ech_blog_channel_id' );
            $plugin_catesTagsFunc = new Ech_Blog_Cates_Tags();

            $args = array(
                'page_size' => $ppp,
                'cate_id' => $cateID,
                'tag_id' => $tagID,
                'brand_id' => $brandID
            );
            $api_link = parent::ECHB_gen_blog_link_api_link($args);
            $get_cate_tag_json = parent::ECHB_curl_blog_json($api_link);
            $json_arr = json_decode($get_cate_tag_json, true);
            /**
             * Redirect to blog list page if cate_id is invalid
             */
            if (!isset($json_arr['count']) || $json_arr['count'] == 0) {
                echo '<script>window.location.replace("/health-blog");</script>';  
            }

            $title_type = '';
            $title_name = '';
            if(isset($cateID) && !empty($cateID) ) {
                // Get Category Names
                $getCateName_json = $plugin_catesTagsFunc->ECHB_get_category_name($cateID);
                $cateNameArr = json_decode($getCateName_json, true);
            
                $title_type = parent::ECHB_echolang(['Category', '類別', '类别']);
                $title_name = parent::ECHB_echolang([ $cateNameArr['en'], $cateNameArr['zh'], $cateNameArr['sc']]);
            }
            if(isset($tagID) && !empty($tagID) ) {
                // Get Tag Name
                $getTagName_json = $plugin_catesTagsFunc->ECHB_get_tag_name($tagID);
                $tagNameArr = json_decode($getTagName_json, true);
            
                $title_type = parent::ECHB_echolang(['Tag', '標籤', '标签']);
                $title_name = parent::ECHB_echolang([ $tagNameArr['en'], $tagNameArr['zh'], $tagNameArr['sc']]);
            }

            $html = '';
            $html .= '<div class="ech_blog_cate_tags_all_wrap">';

            $getBreadcrumbStatus = get_option( 'ech_blog_enable_breadcrumb' );
            if ($getBreadcrumbStatus == 1) {
                $html .= '<div class="sp_breadcrumb">';
                    $html .= '<div><a href="'. site_url() .'">'. parent::ECHB_echolang(['Home', '主頁', '主页']) .'</a> > <a href="'. site_url() . '/health-blog/">'. parent::ECHB_echolang(['Health Blog', '健康資訊', '健康资讯']) .'</a> > '.$title_type.': '.$title_name .' </div>';
                $html .= '</div>';
            }

            $html .= '<div class="echb_page_anchor"></div>';
            $html .= '<div class="ECHB_back_to_blog_list"><a href="'.site_url().'/health-blog/"> '. parent::ECHB_echolang(['Back to health blog', '返回健康專欄', '返回健康专栏']) .'</a></div>';
            $html .= '<div class="ECHB_search_title">';
                $html .= '<p><span>'.$title_type.': </span>'.$title_name.' </p>';
            $html .= '</div>'; // .ECHB_search_title

            $html .= '<div class="ech_blog_container">';
                $html .= '<div class="loading_div"><p>'. parent::ECHB_echolang(['Loading...','載入中...','载入中...']).'</p></div>';
                $html .= '<div class="all_posts_container" data-ppp="'.$ppp.'" data-channel="'.$channelID.'" data-category="'.$cateID.'" data-title="" data-tag="'.$tagID.'" data-brand-id="'.$brandID.'">';
                    foreach ($json_arr['result'] as $post) {
                        $html .= parent::ECHB_load_post_card_template($post, $brandID);
                    }                    
                $html .= '</div>'; // .all_posts_container

                /*** pagination ***/
                $total_posts = $json_arr['count'];
                $max_page = ceil($total_posts/$ppp);
                $html .= '<div class="ech_blog_pagination" data-current-page="1" data-max-page="'.$max_page.'" data-topage="" data-brand-id="'.$brandID.'" data-ajaxurl="'.get_admin_url(null, 'admin-ajax.php').'"></div>';

            $html .= '</div>'; // .ech_blog_container
            $html .= '</div>'; // .ech_blog_cate_tags_all_wrap
            

            return $html;
        } // if !get_option('run_init_createVP')

    } //ech_blog_cate_tag_list_output

    /******************************** (end) VP SHORTCODE ********************************/



   


} // class
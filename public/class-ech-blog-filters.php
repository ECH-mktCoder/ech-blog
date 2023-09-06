<?php 



class Ech_Blog_Filters extends Ech_Blog_Public {


    public function __construct() { }
    
    public function ECHB_get_filter_tags() {
        $html = '';
        $html .= '<div class="categories_filter">';
        $html .= '<select name="tag_filter" id="tag_filter" class="tag_filter">';
            //$html .= '<option value="">'.parent::ECHB_echolang(['All Tags','全部標簽','全部标签']).'</option>';
        $html .= '</select>'; 
        $html .= '</div>'; //categories_filter
    
        return $html;
    }



    public function ECHB_get_filter_title() {
        $html = '';
        $html .= '<div class="title_filter">';
        $html .= '<input type="text" name="title_filter" class="title_filter" id="title_filter" placeholder="'. parent::ECHB_echolang(['Filter by title','搜尋標題','搜寻标题']).'" />';
        $html .= '</div>'; //tags_filter
    
        return $html;
    }




    public function ECHB_get_categories_list($cateID) {
        $cateIDArr = array();
        if($cateID != '') {
            $cateIDArr = explode(",", $cateID);
        }

        $full_api = parent::ECHB_getAPIDomain() . '/v1/api/article_categories_list?get_type=1&page=1&page_size=50&channel_id=9';
        $get_cateList_json = parent::ECHB_curl_blog_json($full_api);
        $json_arr = json_decode($get_cateList_json, true);

        $html = '';
        // Desktop    
        $html .= '<div class="D_categories_filter_container">';

        if(count($cateIDArr) == 0 ) {
            $html .= '<div data-catefilterid="" class="D_cate_filter active">'.parent::ECHB_echolang(['All Categories','全部類別','全部类别']).'</div>';
        }

        if (empty($cateIDArr)) {
            foreach($json_arr['result'] as $category) {
                $html .= '<div data-catefilterid="'.$category['article_category_id'].'" class="D_cate_filter">';
                $html .= parent::ECHB_echolang([$category['en_name'], $category['tc_name'], $category['cn_name'] ]);
                $html .= '</div>'; // D_cate_filter
            }
        } else {
            foreach($cateIDArr as $key=>$show_cate) {
                foreach($json_arr['result'] as $category) {
                    if(in_array($show_cate, $category)) {
                        if($key === 0) {
                            $html .= '<div data-catefilterid="'.$category['article_category_id'].'" class="D_cate_filter active">';
                        } else {
                            $html .= '<div data-catefilterid="'.$category['article_category_id'].'" class="D_cate_filter">';
                        }
                        $html .= parent::ECHB_echolang([$category['en_name'], $category['tc_name'], $category['cn_name'] ]);
                        $html .= '</div>'; // D_cate_filter
                    }
                }
            }
        }
        
        $html .= '</div>'; //D_categories_filter_container

        
        // Mobile 
        $html .= '<div class="M_categories_filter_container">';
        $html .= '<select name="categories_filter_M" id="categories_filter_M" class="categories_filter_M">';

            if(count($cateIDArr) == 0 ) {
                $html .= '<option value="">'.parent::ECHB_echolang(['All Categories','全部類別','全部类别']).'</option>';
            }

            if (empty($cateIDArr)) {
                foreach($json_arr['result'] as $category) {
                    $html .= '<option value="'.$category['article_category_id'].'">'.parent::ECHB_echolang([$category['en_name'], $category['tc_name'], $category['cn_name'] ]).'</option>';
                }
            } else {
                foreach($cateIDArr as $key=>$show_cate) {
                    foreach($json_arr['result'] as $key=>$category) {
                        if(in_array($show_cate, $category)) {
                            //$html .= '<option value="'.$category['article_category_id'].'">'.parent::ECHB_echolang([$category['en_name'], $category['tc_name'], $category['cn_name'] ]).'</option>';
                            if($key === 0) {
                                $html .= '<option value="'.$category['article_category_id'].'" selected>';
                            } else {
                                $html .= '<option value="'.$category['article_category_id'].'">';
                            }
                            $html .= parent::ECHB_echolang([$category['en_name'], $category['tc_name'], $category['cn_name'] ]);
                            $html .= '</option>';
                        }
                    }
                }
            }

            
            
        $html .= '</select>'; 
        $html .= '</div>'; //M_categories_filter_container

        return $html;

    }

} // class
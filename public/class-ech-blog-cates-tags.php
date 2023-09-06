<?php 

class Ech_Blog_Cates_Tags extends Ech_Blog_Public {
    
    public function __construct() { }

    public function ECHB_get_category_name($cate_id) {
        $full_api = parent::ECHB_getAPIDomain() . '/v1/api/article_categories_list?get_type=1&page=1&page_size=50&channel_id=9';
        $get_cateList_json = parent::ECHB_curl_blog_json($full_api);
        $json_arr = json_decode($get_cateList_json, true);
    
        // search cate id and get its array key
        $key = array_search($cate_id, array_column($json_arr['result'], 'article_category_id'));
        
        $en_name = $json_arr['result'][$key]['en_name'];
        $zh_name = $json_arr['result'][$key]['tc_name'];
        $sc_name = $json_arr['result'][$key]['cn_name'];
        
        return json_encode(array('en'=>$en_name, 'zh' => $zh_name, 'sc'=>$sc_name), JSON_UNESCAPED_SLASHES);
    } // ECHB_get_category_name



    function ECHB_get_tag_name($tag_id) {
        $full_api = parent::ECHB_getAPIDomain() . '/v1/api/labels_list?get_type=1&page=1&page_size=9000&channel_id=9';
    
        $get_tag_json = parent::ECHB_curl_blog_json($full_api);
        $json_arr = json_decode($get_tag_json, true);
    
        // search tag id and get its array key
        $key = array_search($tag_id, array_column($json_arr['result'], 'label_id'));
    
        $en_name = $json_arr['result'][$key]['en_name'];
        $zh_name = $json_arr['result'][$key]['tc_name'];
        $sc_name = $json_arr['result'][$key]['cn_name'];
    
        return json_encode(array('en'=>$en_name, 'zh' => $zh_name, 'sc'=>$sc_name), JSON_UNESCAPED_SLASHES);
    } // ECHB_get_tag_name

} // class
<?php 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Redirect to blog list page if no article_id is passed
 */
if( (!isset($_GET['cate_id']) && !isset($_GET['tag_id'])) || (empty($_GET['cate_id']) && empty($_GET['tag_id'])) ) {
    echo '<script>window.location.replace("/health-blog");</script>';
    exit;
}

global $wp;

$plugin_info = new Ech_Blog();
$plugin_public = new Ech_Blog_Public($plugin_info->get_plugin_name(), $plugin_info->get_version());
$plugin_catesTagsFunc = new Ech_Blog_Cates_Tags();


$ppp = get_option( 'ech_blog_ppp' );

if(isset($_GET['cate_id'])) {
    $cate_id  = $_GET['cate_id'];
}
if(isset($_GET['tag_id'])) {
    $tag_id  = $_GET['tag_id'];
}

if(isset($_GET['brand_id'])) {
    $brand_id  = $_GET['brand_id'];
}


$args = array(
    'page_size' => $ppp,
    'cate_id' => $cate_id,
    'tag_id' => $tag_id,
    'brand_id' => $brand_id
);


$api_link = $plugin_public->ECHB_gen_blog_link_api_link($args);

$get_cate_tag_json = $plugin_public->ECHB_curl_blog_json($api_link);
$json_arr = json_decode($get_cate_tag_json, true);


/**
 * Redirect to blog list page if cate_id is invalid
 */
 if (!isset($json_arr['count']) || $json_arr['count'] == 0) {
    echo '<script>window.location.replace("/health-blog");</script>';  
    exit;
}


$title_type = '';
$title_name = '';
//$brand_id = 0;
if(isset($_GET['cate_id']) && !empty($_GET['cate_id']) ) {
    // Get Category Names
    $getCateName_json = $plugin_catesTagsFunc->ECHB_get_category_name($cate_id);
    $cateNameArr = json_decode($getCateName_json, true);

    $title_type = $plugin_public->ECHB_echolang(['Category', '類別', '类别']);
    $title_name = $plugin_public->ECHB_echolang([ $cateNameArr['en'], $cateNameArr['zh'], $cateNameArr['sc']]);
}

if(isset($_GET['tag_id']) && !empty($_GET['tag_id']) ) {
    // Get Tag Name
    $getTagName_json = $plugin_catesTagsFunc->ECHB_get_tag_name($tag_id);
    $tagNameArr = json_decode($getTagName_json, true);

    $title_type = $plugin_public->ECHB_echolang(['Tag', '標籤', '标签']);
    $title_name = $plugin_public->ECHB_echolang([ $tagNameArr['en'], $tagNameArr['zh'], $tagNameArr['sc']]);
}


?>


<div class="ech_blog_cate_tags_all_wrap">

    <?php $getBreadcrumbStatus = get_option( 'ech_blog_enable_breadcrumb' ); ?>
    <?php if ($getBreadcrumbStatus == 1): ?>
        <div class="sp_breadcrumb">
            <div><a href="<?= site_url() ?>"><?= $plugin_public->ECHB_echolang(['Home', '主頁', '主页']) ?></a> > <a href="<?= site_url() . '/health-blog/' ?>"><?= $plugin_public->ECHB_echolang(['Health Blog', '健康資訊', '健康资讯']) ?></a> > <?=$title_type.': '.$title_name ?> </div>
        </div> <!-- sp_breadcrumb -->
    <?php endif; ?>

    <div class="echb_page_anchor"></div>

    <div class="ECHB_back_to_blog_list"><a href="<?=site_url()?>/health-blog/"> <?= $plugin_public->ECHB_echolang(['Back to health blog', '返回健康專欄', '返回健康专栏']) ?></a></div>
    <div class="ECHB_search_title">
        <p><span><?=$title_type?>: </span><?=$title_name?> </p>
    </div>

    <div class="ech_blog_container">
        <div class="loading_div"><p><?=$plugin_public->ECHB_echolang(['Loading...','載入中...','载入中...'])?></p></div>

        <div class="all_posts_container" data-ppp="<?=$ppp?>" data-channel="<?=$channel_id?>" data-category="<?=$cate_id?>" data-title="" data-tag="<?=$tag_id?>" data-brand-id="<?=$brand_id?>">
            <?php foreach($json_arr['result'] as $post): ?>
            <?= $plugin_public->ECHB_load_post_card_template($post, $brand_id)?>
            <?php endforeach; ?>
        </div> <!-- all_posts_container -->

        <?php 
            /*** pagination ***/
            $total_posts = $json_arr['count'];
            $max_page = ceil($total_posts/$ppp);
        ?>
        <div class="ech_blog_pagination" data-current-page="1" data-max-page="<?=$max_page?>" data-topage="" data-brand-id="<?=$brand_id?>" data-ajaxurl="<?=get_admin_url(null, 'admin-ajax.php')?>"></div>

    </div> <!-- ech_blog_container -->


    

</div> <!-- ech_blog_cate_tags_all_wrap -->
<?php


if (!isset($_GET['article_id']) || !isset($_GET['post_version']) || empty($_GET['article_id']) || empty($_GET['post_version'])) {
    echo '<script>window.location.replace("/health-blog");</script>';
}


global $wp;

$plugin_info = new Ech_Blog();
$plugin_public = new Ech_Blog_Public($plugin_info->get_plugin_name(), $plugin_info->get_version());


$articleID  = $_GET['article_id'];
$postVersion  = $_GET['post_version'];
$scAttr_brand_id  = $_GET['scAttr_brand_id'];

$args = array(
    'article_id' => $articleID,
    'version' => $postVersion
);

$api_link = $plugin_public->ECHB_gen_post_api_link($args);

$get_post_json = $plugin_public->ECHB_curl_blog_json($api_link);
$json_arr = json_decode($get_post_json, true);



/**
 * Redirect to blog list page if article_id is invalid
 */

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
$contentEN = $plugin_public->ECHB_sortContentArr($contentEN);
$contentZH = $plugin_public->ECHB_sortContentArr($contentZH);
$contentSC = $plugin_public->ECHB_sortContentArr($contentSC);

?>

<?php $getSPBrandSectionStatus = get_option('ech_blog_enable_single_post_brand_section'); ?>


<div class="all_single_post_wrap">
    <?php $post_title = $plugin_public->ECHB_echolang([$post['en_title'], $post['tc_title'], $post['cn_title']]);  ?>

    <?php $getBreadcrumbStatus = get_option('ech_blog_enable_breadcrumb'); ?>
    <?php if ($getBreadcrumbStatus == 1) : ?>
        <div class="sp_breadcrumb">
            <div><a href="<?= site_url() ?>"><?= $plugin_public->ECHB_echolang(['Home', '主頁', '主页']) ?></a> > <a href="<?= site_url() . '/health-blog/' ?>"><?= $plugin_public->ECHB_echolang(['Health Blog', '健康資訊', '健康资讯']) ?></a> > <?= $post_title ?> </div>
        </div> <!-- sp_breadcrumb -->
    <?php endif; ?>

    <div class="ECHB_back_to_blog_list"><a href="<?= site_url() ?>/health-blog/">
            < <?= $plugin_public->ECHB_echolang(['Back to health blog', '返回健康專欄', '返回健康专栏']) ?></a>
    </div>

    <div class="single_post_container">
        <div class="post_container <?= ($getSPBrandSectionStatus == 0) ? ' full_width' : '' ?>">
            <div class="post_title">
                <h1><?= $post_title ?></h1>
            </div>

            <div class="post_info">
                <div class="post_date"><?= date('d/m/Y', $post['product_time']) ?></div>
                <?php
                /***** TAG *****/
                $tagsArrEN = array();
                $tagsArrZH = array();
                $tagsArrSC = array();
                foreach ($post['label'] as $label) {
                    array_push($tagsArrEN, array('type' => 'tag', 'tag_id' => $label['label_id'], 'tag_name' => $label['label_en_name']));
                    array_push($tagsArrZH, array('type' => 'tag', 'tag_id' => $label['label_id'], 'tag_name' => $label['label_tc_name']));
                    array_push($tagsArrSC, array('type' => 'tag', 'tag_id' => $label['label_id'], 'tag_name' => $label['label_cn_name']));
                }
                /***** (END)TAG *****/
                ?>

                <?php
                /***** SHARE TEXT *****/
                $shareTxt = $plugin_public->ECHB_echolang([$post['en_share'], $post['tc_share'], $post['cn_share']]);
                if ($shareTxt == '' || $shareTxt == null) {
                    $shareTxt = $post_title;
                }
                $shareTxt = str_replace(' ', '_', $shareTxt);
                $shareTxt = preg_replace('~[^\p{L}\p{N}\_]+~u', '', $shareTxt);
                /***** (END)SHARE TEXT *****/
                ?>
                <div class="post_tag"><?= $plugin_public->ECHB_echolang(['Topics', '標籤', '标签']) ?>: <?= $plugin_public->ECHB_echolang([$plugin_public->ECHB_apply_comma_from_array($tagsArrEN, $scAttr_brand_id), $plugin_public->ECHB_apply_comma_from_array($tagsArrZH, $scAttr_brand_id), $plugin_public->ECHB_apply_comma_from_array($tagsArrSC, $scAttr_brand_id)]); ?></div>
                <div class="post_share">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= home_url(add_query_arg($_GET, $wp->request)) ?>" target="_blank"><img src="<?= plugin_dir_url(dirname(__DIR__, 1)) ?>assets/img/author-fb.svg" alt="" class="post_fb"></a>

                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= home_url(add_query_arg($_GET, $wp->request)) ?>" target="_blank"><img src="<?= plugin_dir_url(dirname(__DIR__, 1)) ?>assets/img/author-linkedin.svg" alt="" class="post_llinkedin"></a>

                    <a href="https://api.whatsapp.com/send?text=<?= $shareTxt ?>%20-%20<?= home_url(add_query_arg($_GET, $wp->request)) ?>" data-action="share/whatsapp/share" target="_blank"><img src="<?= plugin_dir_url(dirname(__DIR__, 1)) ?>assets/img/author_wtsapp.svg" alt="" class="post_wtsapp"></a>

                </div>
            </div>

            <div class="post_content">

                <div class="content_main_img">
                    <img src="<?= $plugin_public->ECHB_echolang([$contentMainImg_en[1], $contentMainImg_zh[1], $contentMainImg_sc[1]]); ?>" alt="" class="hidden_b_w1024">
                    <img src="<?= $plugin_public->ECHB_echolang([$contentMainImg_en[3], $contentMainImg_zh[3], $contentMainImg_sc[3]]); ?>" alt="" class="show_b_w1024">
                </div> <!-- content_main_img -->


                <div class="content">
                    <?= $plugin_public->ECHB_displayPostContent([$contentEN, $contentZH, $contentSC]); ?>
                </div>


                <div class="post_source">
                    <?php if ($post['blog_published_sources'] == 1) : // dr source 
                    ?>

                        <?php foreach ($post['doctors'] as $dr) : ?>
                            <?php
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
                            ?>
                            <div class="dr_source">
                                <div class="dr_profile"><img src="<?= $dr['avatar'] ?>" alt=""></div>
                                <div class="dr_info">
                                    <div class="dr_name"><?= $plugin_public->ECHB_echolang([$dr_nameEN, $dr_nameZH, $dr_nameSC]) ?></div>
                                    <div class="dr_field"><?= $plugin_public->ECHB_echolang([implode(', ', $spArrEN), implode(', ', $spArrZH), implode(', ', $spArrSC)]); ?> </div> <!-- dr_field -->
                                    <?php if ($post['tc_blog_url'] != '') : ?>
                                        <div class="dr_booking"><a href="<?= $plugin_public->ECHB_echolang([$post['en_blog_url'], $post['tc_blog_url'], $post['cn_blog_url']]) ?>" target="_blank"><?= $plugin_public->ECHB_echolang(['Book Appointment', '預約醫生', '预约医生']) ?></a></div>
                                    <?php endif; ?>
                                </div>
                            </div> <!-- dr_source -->
                        <?php endforeach; ?>

                    <?php else : ?>
                        <div class="media_source"><?= $plugin_public->ECHB_echolang(['Source', '來源', '来源']) ?>: <a href="<?= $plugin_public->ECHB_echolang([$post['en_blog_url'], $post['tc_blog_url'], $post['cn_blog_url']]) ?>" target="_blank"><?= $plugin_public->ECHB_echolang([$post['en_issuer'], $post['tc_issuer'], $post['cn_issuer']]) ?></a></div>
                    <?php endif; ?>
                </div>
            </div>

        </div> <!-- post_container -->

        <?php if ($getSPBrandSectionStatus == 1) : ?>
            <div class="brand_container">
                <div class="inner_brand_container">
                    <?php if (!empty($post['brand'])) : ?>
                        <p><?= $plugin_public->ECHB_echolang(['Related Brands', '相關品牌', '相关品牌']) ?></p>
                        <?php foreach ($post['brand'] as $brand) : ?>
                            <div class="single_brand_container" data-brandid="<?= $brand['forever_brand_id'] ?>">
                                <?php
                                $brandImgEN = json_decode($brand['en_picture'], true);
                                $brandImgZH = json_decode($brand['tc_picture'], true);
                                $brandImgSC = json_decode($brand['cn_picture'], true);

                                if ($brandImgZH[0] != '') :
                                ?>
                                    <div class="brand_img">
                                        <img src="<?= $plugin_public->ECHB_echolang([$brandImgEN[0], $brandImgZH[0], $brandImgSC[0]]) ?>" alt="<?= $plugin_public->ECHB_echolang([$brand['en_name'], $brand['tc_name'], $brand['cn_name']]) ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="brand_name"><?= $plugin_public->ECHB_echolang([$brand['en_name'], $brand['tc_name'], $brand['cn_name']]) ?></div>

                                <?php if ($brand['brand_website_url'] != null || $brand['brand_website_url'] != '') : ?>
                                    <div class="brand_learn_more"><a href="<?= $brand['brand_website_url'] ?>" target="_blank"><?= $plugin_public->ECHB_echolang(['Learn More', '了解更多', '了解更多']) ?></a></div>
                                <?php endif; ?>
                            </div> <!-- single_brand_container -->
                        <?php endforeach; ?>
                    <?php endif; // if ( ! empty($post['brand']) ) 
                    ?>
                </div>
            </div> <!-- brand_container -->
        <?php endif; //if $getSPBrandSectionStatus 
        ?>
    </div> <!-- single_post_container -->


    <?php if (!empty($post['similarity_article'])) : ?>
        <div class="related_article_wrap">
            <h3><?= $plugin_public->ECHB_echolang(['Related Articles', '相關文章', '相关文章']); ?></h3>
            <div class="related_articles_container">
                <?php foreach ($post['similarity_article'] as $related) : ?>
                    <?= $plugin_public->ECHB_load_post_card_template($related, $scAttr_brand_id); ?>
                <?php endforeach; ?>
            </div> <!-- related_articles_container-->
        </div>
    <?php endif; ?>


</div> <!-- all_single_post_wrap -->
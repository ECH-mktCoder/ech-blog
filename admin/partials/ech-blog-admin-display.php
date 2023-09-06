<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://#
 * @since      1.0.0
 *
 * @package    Ech_Blog
 * @subpackage Ech_Blog/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php 
    $plugin_info = new Ech_Blog();
    $ADMIN_ECHB_func = new Ech_Blog_Admin($plugin_info->get_plugin_name(), $plugin_info->get_version()); 
?>

<div class="echPlg_wrap">
    <h1>ECH Blog General Settings</h1>

    <div class="plg_intro">
        <p> More shortcode attributes and guidelines, visit <a href="#" target="_blank">Github</a>. </p>
        <div class="shtcode_container">
            <pre id="sample_shortcode">[ech_blog]</pre>
            <div id="copyMsg"></div>
            <button id="copyShortcode">Copy Shortcode</button>
        </div>


        <div class="api_info_container">
            <p>Filter category IDs ( <?=$ADMIN_ECHB_func->ADMIN_ECHPL_get_env_status()?> )</p>
            <?php 
                $cateID_full_api = $ADMIN_ECHB_func->ADMIN_ECHB_getAPIDomain() . '/v1/api/article_categories_list?get_type=1&page=1&page_size=50&channel_id=9';
                $cateID_get_json = $ADMIN_ECHB_func->ADMIN_ECHB_curl_blog_json($cateID_full_api);
                $cateID_json_arr = json_decode($cateID_get_json, true);
                $cateArr = $cateID_json_arr['result'];
            ?>
            <div class="info_list">
                <?php foreach($cateArr as $cate):?>
                    <div>
                        <?=$cate['tc_name'] . ' | ' . $cate['en_name'] . ' : ' . $cate['article_category_id']?>
                    </div>
                <?php endforeach; ?>
            </div>

           
            
            <p>Brand IDs ( <?=$ADMIN_ECHB_func->ADMIN_ECHPL_get_env_status()?> )</p>
            <div>no api to get brand id yet</div>


        </div> <!-- api_info_container -->
    </div>

    <div class="form_container">
        <form method="post" id="ech_blog_settings_form">
        <?php 
            settings_fields( 'ech_blog_settings' );
            do_settings_sections( 'ech_blog_settings' );
        ?>

            <h2>General</h2>
            <div class="form_row">
                <?php $getApiEnv = get_option( 'ech_blog_apply_api_env' ); ?>
                <label>Connect to Global CMS API environment : </label>
                <select name="ech_blog_apply_api_env" id="">
                    <option value="0" <?= ($getApiEnv == 0) ? 'selected' : '' ?>>Dev/UAT</option>
                    <option value="1" <?= ($getApiEnv == 1) ? 'selected' : '' ?>>Live</option>
                </select>
            </div>
            
            <div class="form_row">
                <?php $getApiDomainLIVE = get_option( 'ech_blog_api_domain_live' ); ?>
                <label><strong>LIVE</strong> API Domain : </label>
                <input type="url" name="ech_blog_api_domain_live" id="ech_blog_api_domain_live" value="<?=$getApiDomainLIVE?>">
            </div>

            <div class="form_row">
                <?php $getApiDomainDEV = get_option( 'ech_blog_api_domain_dev' ); ?>
                <label><strong>DEV</strong> API Domain : </label>
                <input type="url" name="ech_blog_api_domain_dev" id="ech_blog_api_domain_dev" value="<?=$getApiDomainDEV?>">
            </div>

            <div class="form_row">
                <?php $getPPP = get_option( 'ech_blog_ppp' ); ?>
                <label>Post per page : </label>
                <input type="text" name="ech_blog_ppp" id="ech_blog_ppp" pattern="[0-9]{1,}" value="<?=$getPPP?>">
            </div>

            <div class="form_row">
                <?php $getChannelID = get_option( 'ech_blog_channel_id' ); ?>
                <label>Channel ID : </label>
                <input type="text" name="ech_blog_channel_id" id="ech_blog_channel_id" pattern="[0-9]{1,}" value="<?=$getChannelID?>">
            </div>

            <div class="form_row">
                <?php $getBrandID = get_option( 'ech_blog_brand_id' ); ?>
                <label>Brand ID : </label>
                <input type="text" name="ech_blog_brand_id" id="ech_blog_brand_id" pattern="[0-9]{1,}" value="<?=$getBrandID?>">
            </div>

            <div class="form_row">
                <?php $getFilteredCate = get_option( 'ech_blog_category_filter' ); ?>
                <label>Filtered Categories (use comma to separate them) : </label>
                <input type="text" name="ech_blog_category_filter" id="ech_blog_category_filter" pattern="[0-9,]{1,}" value="<?=$getFilteredCate?>">
            </div>

            

            <div class="form_row">
                <?php $getFeaturedImg = get_option( 'ech_blog_default_post_featured_img' ); ?>
                <label>Default post featured image : </label>
                <input type="text" name="ech_blog_default_post_featured_img" id="ech_blog_default_post_featured_img" value="<?=$getFeaturedImg?>">
            </div>


            <div class="form_row">
                <?php $getBreadcrumbStatus = get_option( 'ech_blog_enable_breadcrumb' ); ?>
                <label>Display breadcrumb on "Single Post Content" & "Cate/Tag List" page : </label>
                <select name="ech_blog_enable_breadcrumb" id="">
                    <option value="0" <?= ($getBreadcrumbStatus == 0) ? 'selected' : '' ?>>Disable</option>
                    <option value="1" <?= ($getBreadcrumbStatus == 1) ? 'selected' : '' ?>>Enable</option>
                </select>
            </div>


            <div class="form_row">
                <?php $getSPBrandSectionStatus = get_option( 'ech_blog_enable_single_post_brand_section' ); ?>
                <label>Display brand section on "Single Post Content" page : </label>
                <select name="ech_blog_enable_single_post_brand_section" id="">
                    <option value="0" <?= ($getSPBrandSectionStatus == 0) ? 'selected' : '' ?>>Disable</option>
                    <option value="1" <?= ($getSPBrandSectionStatus == 1) ? 'selected' : '' ?>>Enable</option>
                </select>
            </div>


            <div class="form_row">
                <button type="submit"> Save </button>
            </div>
        </form>
        <div class="statusMsg"></div>


    </div> <!-- form_container -->
</div>


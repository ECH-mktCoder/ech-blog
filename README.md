# ech-blog
A Wordpress plugin to display ECH articles for any ECH company's brand websites. It is integrated with the global ECH articles CMS. 

This plugin integrates with the TranslatePress plugin, Astra theme and Elementor page builder. Please make sure you already have these installed in the Wordpress site.

## Installation
Before installing the plugin, create a new page with a slug `health-blog`
Install and activate the plugin

## Usage 
To display the blog, enter shortcode
```
[ech_blog]
```

## Shortcode Attributes

Please note that shortcode attributes will override the setting in plugin admin dashboard. 

Attribute | Description
----------|-------------
ppp (INT) | post per page. Default vaule is `12`
channel_id (INT) | select article channels between ECH app and website. Default value is `9` (website)
brand_id (INT) | enter brand id to display specific brand articles. Default value is `0` which is display all brand articles. 
show_cate (STR) | enter category id to display specific filter categories. Use comma to separate them. (eg. `show_cate = "123,456"`). Default value is an empty string which is display all the filter categories. 





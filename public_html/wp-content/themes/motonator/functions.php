<?php
add_theme_support('post-thumbnails');

function remove_default_post_screen_metaboxes() {
	remove_meta_box('categorydiv','post','side'); //カテゴリー
	remove_meta_box('tagsdiv-post_tag','post','side'); //タグ
}
add_action('admin_menu','remove_default_post_screen_metaboxes');

// 人気記事出力用
function getPostViews($postID){
	$count_key = 'post_views_count';
	$count = get_post_meta($postID, $count_key, true);
	if($count==''){
			delete_post_meta($postID, $count_key);
			add_post_meta($postID, $count_key, '0');
			return "0 View";
	}
	return $count.' Views';
}

function setPostViews($postID) {
	$count_key = 'post_views_count';
	$count = get_post_meta($postID, $count_key, true);
	if($count==''){
			$count = 0;
			delete_post_meta($postID, $count_key);
			add_post_meta($postID, $count_key, '0');
	}else{
			$count++;
			update_post_meta($postID, $count_key, $count);
	}
}
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

function my_posts_per_page($query) {
	if(is_search()&&$query->is_main_query()){
		$query->set('posts_per_page', 10);
	}
}
add_action( 'pre_get_posts', 'my_posts_per_page' );

function my_post_search($search) {
	if(is_search()) {
		$search .= " AND post_type = 'post'";
	}
	return $search;
}
add_filter('posts_search', 'my_post_search');

// サイトURLのショートコード（home_urlで使用）
function shortcode_home_url() {
    return home_url();
}
add_shortcode('home_url', 'shortcode_home_url');

// テンプレートディレクトリのショートコード（temp_urlで使用）
function shortcode_template_url() {
    return get_template_directory_uri();
}
add_shortcode('temp_url', 'shortcode_template_url');
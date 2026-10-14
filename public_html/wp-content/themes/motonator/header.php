<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
	<link rel="stylesheet" href="<?=get_template_directory_uri()?>/style.css?v=<?=filemtime(get_template_directory() . '/style.css')?>">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="<?=get_template_directory_uri()?>/js/masonry.pkgd.min.js"></script>
	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<script>
	  (adsbygoogle = window.adsbygoogle || []).push({
		google_ad_client: "ca-pub-4449490024488807",
		enable_page_level_ads: true
	  });
	</script>
	<title><?=wp_title()?></title>
	<?php
	$site_name = get_bloginfo('name');
	$og_title = $site_name . ' - ' . get_bloginfo('description');
	$og_desc = 'ネットスラングや流行語、ゲーム・アニメ・マンガの名言など、気になるキーワードの元ネタを調べられるWEBサイトです。';
	$og_url = home_url('/');
	$og_type = 'website';
	$og_image = get_template_directory_uri() . '/img/ogp-default.png';
	if(is_singular('post')){
		$og_post = get_queried_object();
		$og_title = $og_post->post_title . 'の元ネタ | ' . $site_name;
		$og_source = function_exists('get_field') ? get_field('mean', $og_post->ID) : '';
		if(!$og_source){ $og_source = $og_post->post_content; }
		$og_text = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($og_source)));
		if($og_text){ $og_desc = mb_strimwidth($og_text, 0, 120, '…'); }
		$og_url = get_permalink($og_post->ID);
		$og_type = 'article';
		if(has_post_thumbnail($og_post->ID)){ $og_image = get_the_post_thumbnail_url($og_post->ID, 'large'); }
	} elseif(is_page()){
		$og_post = get_queried_object();
		$og_title = $og_post->post_title . ' | ' . $site_name;
		$og_url = get_permalink($og_post->ID);
	}
	?>
	<meta name="description" content="<?=esc_attr($og_desc)?>">
	<link rel="canonical" href="<?=esc_url($og_url)?>">
	<link rel="icon" href="<?=home_url('/favicon.ico')?>" sizes="48x48">
	<link rel="icon" type="image/png" sizes="192x192" href="<?=get_template_directory_uri()?>/img/icon-192.png">
	<link rel="apple-touch-icon" href="<?=get_template_directory_uri()?>/img/apple-touch-icon.png">
	<meta name="theme-color" content="#f7931e">
	<meta property="og:site_name" content="<?=esc_attr($site_name)?>">
	<meta property="og:type" content="<?=esc_attr($og_type)?>">
	<meta property="og:title" content="<?=esc_attr($og_title)?>">
	<meta property="og:description" content="<?=esc_attr($og_desc)?>">
	<meta property="og:url" content="<?=esc_url($og_url)?>">
	<meta property="og:image" content="<?=esc_url($og_image)?>">
	<meta property="og:locale" content="ja_JP">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?=esc_attr($og_title)?>">
	<meta name="twitter:description" content="<?=esc_attr($og_desc)?>">
	<meta name="twitter:image" content="<?=esc_url($og_image)?>">
</head>
<body <?php body_class(); ?>>
<?php if(!is_home()): ?>
	<header class="common_header">
		<h2><?=get_bloginfo()?> - <br><?=get_bloginfo('description')?></h2>
		<div class="logo"><a href="<?=home_url()?>"><img src="<?=get_template_directory_uri()?>/img/logo.png" alt=""></a></div>
		<ul class="g_nav">
			<li><a href="<?=home_url()?>">Home</a></li>
			<li><a href="<?=home_url()?>/?s=">記事一覧</a></li>
			<li><a href="<?=home_url()?>/about">このサイトについて</a></li>
			<li><a href="<?=home_url()?>/contact">お問い合わせ</a></li>
		</ul>
	</header>
<?php endif; ?>
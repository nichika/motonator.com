<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
	<link rel="stylesheet" href="<?=get_template_directory_uri()?>/style.css">
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
<?php get_header(); ?>
	<?php
	$ad_number_list = [
		// 8, 16
		// 上記コメントを外す ※Google審査用
	];
	?>
	<header class="home_header">
		<h1><?=get_bloginfo()?> - <br><?=get_bloginfo('description')?></h1>
		<div class="logo"><img src="<?=get_template_directory_uri()?>/img/logo.png" alt=""></div>
		<div class="search">
			<div class="text">私の名前はモトネーター。あなたの知りたい「言葉の元ネタ」を調べます。</div>
			<?php get_search_form(); ?>
		</div>
	</header>
	<div class="about">
		<div class="inner">
			<h2><?=get_bloginfo()?>とは？</h2>
			<p>ネットスラングや流行語など気になるキーワードの元ネタを調べられるWEBサイトです。</p>
			<a href="<?=home_url()?>/about">詳しく見る</a>
		</div>
	</div>
	<section class="home_feed">
		<ul class="feed_grid">
			<?php
			// 24 is divisible by 2, 3, 4 and 6, so the grid has no ragged last row at any width.
			$feed_query = new WP_Query(['posts_per_page' => 24, 'post_type' => 'post']);
			$feed_index = 0;
			?>
			<?php while($feed_query->have_posts()): $feed_query->the_post(); $feed_index++; ?>
				<?php
				$feed_link = esc_url(get_permalink());
				$feed_tags = [];
				foreach(['genre', 'work', 'etc'] as $feed_tax){
					$feed_terms = get_the_terms(get_the_ID(), $feed_tax);
					if($feed_terms && !is_wp_error($feed_terms)){
						foreach($feed_terms as $feed_term){ $feed_tags[] = $feed_term; }
					}
				}
				$feed_tags = array_slice($feed_tags, 0, 3);
				?>
				<li class="card">
					<a class="card_thumb" href="<?=$feed_link?>" tabindex="-1" aria-hidden="true">
						<?php if(has_post_thumbnail()): ?>
							<?php the_post_thumbnail('medium_large', ['alt' => '', 'loading' => $feed_index <= 4 ? 'eager' : 'lazy', 'sizes' => '(min-width: 1700px) 16vw, (min-width: 1280px) 24vw, (min-width: 900px) 32vw, (min-width: 640px) 48vw, 96px']); ?>
						<?php else: ?>
							<img src="<?=get_template_directory_uri()?>/img/no_image.svg" alt="" width="320" height="320" loading="lazy">
						<?php endif; ?>
					</a>
					<div class="card_body">
						<a class="card_title" href="<?=$feed_link?>"><b>「<?=esc_html(get_the_title())?>」</b>の元ネタ</a>
						<?php if($feed_tags): ?>
							<ul class="card_tags">
								<?php foreach($feed_tags as $feed_tag): ?>
									<li><a href="<?=esc_url(get_term_link($feed_tag))?>"><?=esc_html($feed_tag->name)?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</li>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</ul>
	</section>
<?php get_footer(); ?>

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
	<ul class="thumbnail_list">
		<?php
		$args = [
			'posts_per_page' => 10,
			'post_type' => 'post',
			'meta_key'  => '_thumbnail_id'
		];
		$thumbnail_query = new WP_Query($args);
		?>
		<?php while($thumbnail_query->have_posts()): ?>
			<?php
			$thumbnail_query->the_post();
			?>
			<?php /*
		    <li style="background-image: url(<?=get_the_post_thumbnail_url()?>)"></li>
		    // 上記コメントを外す ※Google審査用
		    */ ?>
		<?php endwhile; ?>
		<?php wp_reset_postdata(); ?>
	</ul>
	<div class="about">
		<div class="inner">
			<h2><?=get_bloginfo()?>とは？</h2>
			<p>ネットスラングや流行語など気になるキーワードの元ネタを調べられるWEBサイトです。</p>
			<a href="<?=home_url()?>/about">詳しく見る</a>
		</div>
	</div>
	<div class="latest_post">
		<h2>新着記事</h2>
		<ul class="post_list">
			<?php
			$args = [
				'posts_per_page' => 20,
				'post_type' => 'post',
			];
			$post_query = new WP_Query($args);
			$post_number = 0;
			?>
			<?php while($post_query->have_posts()): ?>
				<?php
				$post_query->the_post();
				$post_number++;
				?>
				<li>
					<?php if(has_post_thumbnail()): ?>
						<div class="thumbnail">
							<a href="<?=esc_url(get_permalink())?>">
								<?php the_post_thumbnail('medium'); ?>
							</a>
						</div>
					<?php else: ?>
						<div class="thumbnail no_image">
							<a href="<?=esc_url(get_permalink())?>">
								<img src="<?=get_template_directory_uri()?>/img/no_image.svg" alt="" width="320" height="320" loading="lazy">
							</a>
						</div>
					<?php endif; ?>
					<div class="title">
						<a href="<?=esc_url(get_permalink())?>">
							<b>「<?=get_the_title()?>」</b>の元ネタ
						</a>
					</div>
					<div class="tag">
						<?php
						$genre_tag_list = get_the_terms($post->ID, 'genre');
						$work_tag_list = get_the_terms($post->ID, 'work');
						$etc_tag_list = get_the_terms($post->ID, 'etc');
						?>
						<ul class="tag_list">
							<?php if($genre_tag_list): ?>
								<?php foreach($genre_tag_list as $genre_tag): ?>
									<li>
										<a href="<?=esc_url(get_term_link($genre_tag))?>">
											<?=$genre_tag->name?>
										</a>
									</li>
								<?php endforeach; ?>
							<?php endif;?>
							<?php if($work_tag_list): ?>
								<?php foreach($work_tag_list as $work_tag): ?>
									<li>
										<a href="<?=esc_url(get_term_link($work_tag))?>">
											<?=$work_tag->name?>
										</a>
									</li>
								<?php endforeach; ?>
							<?php endif;?>
							<?php if($etc_tag_list): ?>
								<?php foreach($etc_tag_list as $etc_tag): ?>
									<li>
										<a href="<?=esc_url(get_term_link($etc_tag))?>">
											<?=$etc_tag->name?>
										</a>
									</li>
								<?php endforeach; ?>
							<?php endif;?>
						</ul>
					</div>
				</li>
				<?php if(in_array($post_number, $ad_number_list)): ?>
					<li class="google_ad">
						Google Ad
					</li>
				<?php endif;?>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</ul>
	</div>
<?php get_footer(); ?>
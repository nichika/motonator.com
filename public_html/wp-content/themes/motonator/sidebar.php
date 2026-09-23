<div class="sub">
	<div class="search">
		<h2>記事の検索</h2>
		<?php get_search_form(); ?>
	</div>
	<div class="latest_post post">
		<h2>新着記事一覧</h2>
		<ul class="latest_post_list post_list">
			<?php
			$args = [
				'posts_per_page' => 10,
				'post_type' => 'post',
				'orderby' => 'date',
				'order' => 'DESC',
			];
			$latest_post_query = new WP_Query($args);
			?>
			<?php while($latest_post_query->have_posts()): ?>
				<?php
				$latest_post_query->the_post();
				?>
				<li>
					<a href="<?=$post->ID?>">
						<div class="thumbnail">
						<?php if(has_post_thumbnail()): ?>
							<?php the_post_thumbnail(array( 30, 30 )); ?>
						<?php else: ?>
							<img src="<?=get_template_directory_uri()?>/img/no_image.svg" alt="">
						<?php endif; ?>
						</div>
						<div class="title">
							<?=mb_strimwidth(get_the_title(), 0, 48, '…')?>
						</div>
					</a>
				</li>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</ul>
	</div>
	<div class="popular_post post">
		<h2>人気の記事一覧</h2>
		<ul class="popular_post_list post_list">
			<?php
			setPostViews(get_the_ID());
			$args = [
				'posts_per_page' => 10,
				'post_type' => 'post',
				'meta_key' => 'post_views_count',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
			];
			$popular_post_query = new WP_Query($args);
			?>
			<?php while($popular_post_query->have_posts()): ?>
				<?php
				$popular_post_query->the_post();
				?>
				<li>
					<a href="<?=$post->ID?>">
						<div class="thumbnail">
							<?php if(has_post_thumbnail()): ?>
								<?php the_post_thumbnail(array( 30, 30 )); ?>
							<?php else: ?>
								<img src="<?=get_template_directory_uri()?>/img/no_image.svg" alt="">
							<?php endif; ?>
						</div>
						<div class="title">
							<?=mb_strimwidth(get_the_title(), 0, 48, '…')?>
						</div>
					</a>
				</li>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</ul>
	</div>
	<?php /*
	<div class="test_googlead">
		Google Ad
	</div>
	<div class="test_googlead">
		Google Ad
	</div>
	コメントを外す ※Google審査用
	*/ ?>
</div>
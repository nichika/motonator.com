<?php
if(!function_exists('motonator_sidebar_item')){
	function motonator_sidebar_item(){
		$genres = get_the_terms(get_the_ID(), 'genre');
		?>
		<li>
			<a href="<?=esc_url(get_permalink())?>">
				<div class="thumbnail">
					<?php if(has_post_thumbnail()): ?>
						<?php the_post_thumbnail('thumbnail'); ?>
					<?php else: ?>
						<img src="<?=get_template_directory_uri()?>/img/no_image.svg" alt="" width="320" height="320" loading="lazy">
					<?php endif; ?>
				</div>
				<div class="info">
					<div class="title"><?=esc_html(mb_strimwidth(get_the_title(), 0, 60, '…'))?></div>
					<?php if($genres && !is_wp_error($genres)): ?>
						<span class="genre"><?=esc_html($genres[0]->name)?></span>
					<?php endif; ?>
				</div>
			</a>
		</li>
		<?php
	}
}
?>
<div class="sub">
	<div class="search">
		<h2>記事の検索</h2>
		<?php get_search_form(); ?>
	</div>
	<div class="latest_post post">
		<h2>新着記事</h2>
		<ul class="latest_post_list post_list">
			<?php
			$latest_post_query = new WP_Query([
				'posts_per_page' => 10,
				'post_type' => 'post',
				'orderby' => 'date',
				'order' => 'DESC',
			]);
			?>
			<?php while($latest_post_query->have_posts()): ?>
				<?php $latest_post_query->the_post(); ?>
				<?php motonator_sidebar_item(); ?>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</ul>
	</div>
	<div class="popular_post post">
		<h2>人気の記事</h2>
		<ul class="popular_post_list post_list">
			<?php
			setPostViews(get_the_ID());
			$popular_post_query = new WP_Query([
				'posts_per_page' => 10,
				'post_type' => 'post',
				'meta_key' => 'post_views_count',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
			]);
			?>
			<?php while($popular_post_query->have_posts()): ?>
				<?php $popular_post_query->the_post(); ?>
				<?php motonator_sidebar_item(); ?>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</ul>
	</div>
</div>

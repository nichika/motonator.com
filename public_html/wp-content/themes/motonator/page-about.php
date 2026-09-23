<?php get_header(); ?>
	<?php if(have_posts()): while(have_posts()): the_post(); ?>
	<div class="container">
		<h1><? the_title(); ?></h1>
		<div class="introduction">
			<img src="<?=get_template_directory_uri()?>/img/about_illust.png" alt="">
			<div class="text">
				<? the_content(); ?>
			</div>
		</div>
		<div class="share">
			<h3><?=get_bloginfo()?>を<br>みんなにシェアしよう！</h3>
			<ul>
				<li class="twitter">
					<?php
					$twitter_text = urlencode(get_bloginfo() . ' - ' . get_bloginfo('description'));
					$line_text = urlencode(get_bloginfo() . get_bloginfo('description') . get_home_url());
					?>
					<a href="http://twitter.com/share?url=<?=urlencode(get_home_url())?>&text=<?=$twitter_text?>&hashtags=Motonater" target="_blank" rel="nofollow">
						<b>Twitter</b><br>
						でツイートする
					</a>
				</li>
				<li class="facebook">
					<a href="https://www.facebook.com/sharer/sharer.php?u=<?=urlencode(get_home_url())?>&src=sdkpreparse" target="_blank" rel="nofollow">
						<b>Facebook</b><br>
						でいいね！する
					</a>
				</li>
				<li class="line">
					<a href="line://msg/text/?<?=$line_text?>" target="_blank" rel="nofollow">
						<b>LINE</b><br>
						でシェアする
					</a>
				</li>
			</ul>
		</div>
		<?php
		$args = [
			'posts_per_page' => 10,
			'post_type' => 'post',
			'orderby' => 'rand',
		];
		$random_post_query = new WP_Query($args);
		?>
		<?php if($random_post_query->have_posts()): ?>
			<div class="random">
				<h3>記事をランダムに表示中</h3>
				<ul class="random_post_list">
					<?php while($random_post_query->have_posts()): ?>
						<?php
						$random_post_query->the_post();
						?>
						<li>
							<div class="thumbnail">
								<a href="?p=<?=$post->ID?>">
									<?php // if(has_post_thumbnail()): ?>
									<?php if(false): // 上記コメントを外す ※Google審査用 ?>
										<?php the_post_thumbnail(array( 60, 60 )); ?>
									<?php else: ?>
										<img src="<?=get_template_directory_uri()?>/img/no_image.png" alt="">
									<?php endif; ?>
								</a>
							</div>
							<div class="title">
								<a href="?p=<?=$post->ID?>">
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
												<a href="<?=get_home_url()?>?s=<?=$genre_tag->slug?>">
													<?=$genre_tag->name?>
												</a>
											</li>
										<?php endforeach; ?>
									<?php endif;?>
									<?php if($work_tag_list): ?>
										<?php foreach($work_tag_list as $work_tag): ?>
											<li>
												<a href="<?=get_home_url()?>?s=<?=$work_tag->slug?>">
													<?=$work_tag->name?>
												</a>
											</li>
										<?php endforeach; ?>
									<?php endif;?>
									<?php if($etc_tag_list): ?>
										<?php foreach($etc_tag_list as $etc_tag): ?>
											<li>
												<a href="<?=get_home_url()?>?s=<?=$etc_tag->slug?>">
													<?=$etc_tag->name?>
												</a>
											</li>
										<?php endforeach; ?>
									<?php endif;?>
								</ul>
							</div>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
	<?php endwhile; endif; ?>
<?php get_footer(); ?>
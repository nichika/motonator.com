<?php get_header(); ?>
	<div class="container">
		<div class="main">
			<?php
			if(!empty($_GET['s'])){
				$search_title = 'フリーワード: ' . $_GET['s'];
			} elseif(!empty($_GET['genre'])) {
				$search_title = 'タグ：' . $_GET['genre'];
			} elseif(!empty($_GET['work'])) {
				$search_title = 'タグ：' . $_GET['work'];
			} elseif(!empty($_GET['etc'])) {
				$search_title = 'タグ：' . $_GET['etc'];
			} else {
				$search_title = 'すべての記事';
			}
			?>
			<div class="headline">
				<h1>検索結果</h1>
				<div class="ruby">
					<?=$search_title?>
				</div>
			</div>
			<?php if(have_posts()): ?>
				<div class="post">
					<ul class="post_list">
						<?php while(have_posts()): the_post(); ?>
							<li>
								<div class="thumbnail">
									<a href="?p=<?=$post->ID?>">
										<?php if(has_post_thumbnail()): ?>
											<?php the_post_thumbnail(array( 60, 60 )); ?>
										<?php else: ?>
											<img src="<?=get_template_directory_uri()?>/img/no_image.svg" alt="">
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
					<?php wp_pagenavi(); ?>
				</div>
			<?php else: ?>
				<div class="post no_result">
					記事が見つかりませんでした。
				</div>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
<?php get_footer(); ?>
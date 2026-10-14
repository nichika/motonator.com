<?php get_header(); ?>
	<?php if(have_posts()): while(have_posts()): the_post(); ?>
		<div class="container">
			<div class="main">
				<div class="headline">
					<h1><b><?=$post->post_title?></b>の元ネタって？</h1>
					<?php if(get_field('ruby')): ?>
						<div class="ruby">読み方：<?=get_field('ruby')?></div>
					<?php endif; ?>
					<div class="dates">
						公開日：<?=get_the_date('Y.m.d')?>
						<?php if(get_the_modified_date('Ymd') !== get_the_date('Ymd')): ?>
							　更新日：<?=get_the_modified_date('Y.m.d')?>
						<?php endif; ?>
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
				</div>
				<?php if(get_field('mean')): ?>
					<div class="mean">
						<h2><b><?=$post->post_title?></b>の意味</h2>
						<div class="content">
							<img src="<?=get_template_directory_uri()?>/img/circle_robot_illust.png" alt="">
							<?=get_field('mean')?>
						</div>
					</div>
				<?php endif; ?>
				<div class="detail">
					<h2><b><?=$post->post_title?></b>の元ネタ</h2>
					<div class="content">
						<?php if(has_post_thumbnail()): ?>
							<?php the_post_thumbnail(); ?>
						<?php endif; ?>
						<?php the_content(); ?>
					</div>
				</div>
				<div class="share">
					<h3>この元ネタをみんなにシェアしよう！</h3>
					<ul>
						<li class="twitter">
							<?php
							$this_page_url = get_home_url() . '/' . $post->ID;
							$twitter_text = urlencode('「' . $post->post_title . '」の元ネタ - ' . get_bloginfo());
							$line_text = urlencode('「' . $post->post_title . '」の元ネタ' . get_bloginfo() . $this_page_url);
							?>
							<a href="http://twitter.com/share?url=<?=urlencode($this_page_url)?>&text=<?=$twitter_text?>&hashtags=Motonater" target="_blank" rel="nofollow">
								<b>Twitter</b><br>
								でツイートする
							</a>
						</li>
						<li class="facebook">
							<a href="https://www.facebook.com/sharer/sharer.php?u=<?=urlencode($this_page_url)?>&src=sdkpreparse" target="_blank" rel="nofollow">
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
				// Same work / keyword articles first, ranked by how many terms they share.
				$kw_current_id = $post->ID;
				$kw_term_ids = [];
				foreach([$work_tag_list, $etc_tag_list] as $kw_list){
					if($kw_list && !is_wp_error($kw_list)){
						foreach($kw_list as $kw_t){ $kw_term_ids[] = $kw_t->term_id; }
					}
				}
				$kw_shown_ids = [];
				if($kw_term_ids){
					$kw_candidates = get_posts([
						'posts_per_page' => 40,
						'post_type' => 'post',
						'fields' => 'ids',
						'post__not_in' => [$kw_current_id],
						'tax_query' => [
							'relation' => 'OR',
							['taxonomy' => 'work', 'field' => 'term_id', 'terms' => $kw_term_ids],
							['taxonomy' => 'etc', 'field' => 'term_id', 'terms' => $kw_term_ids],
						],
					]);
					$kw_scored = [];
					foreach($kw_candidates as $kw_id){
						$kw_shared = array_intersect($kw_term_ids, wp_get_object_terms($kw_id, ['work', 'etc'], ['fields' => 'ids']));
						$kw_scored[$kw_id] = count($kw_shared);
					}
					arsort($kw_scored);
					$kw_shown_ids = array_slice(array_keys($kw_scored), 0, 6);
				}
				?>
				<?php if($kw_shown_ids): ?>
					<div class="relation">
						<h3>同じ作品・キーワードの記事</h3>
						<ul class="relation_post_list">
							<?php foreach($kw_shown_ids as $kw_id): ?>
								<li>
									<div class="thumbnail">
										<a href="<?=esc_url(get_permalink($kw_id))?>">
											<?php if(has_post_thumbnail($kw_id)): ?>
												<img src="<?=esc_url(get_the_post_thumbnail_url($kw_id, 'thumbnail'))?>" alt="<?=esc_attr(get_the_title($kw_id))?>">
											<?php else: ?>
												<img src="<?=get_template_directory_uri()?>/img/no_image.svg" alt="">
											<?php endif; ?>
										</a>
									</div>
									<div class="title">
										<a href="<?=esc_url(get_permalink($kw_id))?>">
											<b>「<?=esc_html(get_the_title($kw_id))?>」</b>の元ネタ
										</a>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
				<?php if($genre_tag_list): ?>
					<?php
					$args = [
						'posts_per_page' => 10,
						'post_type' => 'post',
						'tax_query' => [
							[
								'taxonomy' => 'genre',
								'field'    => 'slug',
								'terms'    => $genre_tag_list[0]->slug,
							],
						],
						'post__not_in' => array_merge([$post->ID], $kw_shown_ids),
						'orderby' => 'date',
						'order' => 'DESC',
					];
					$relation_post_query = new WP_Query($args);
					?>
					<?php if($relation_post_query->have_posts()): ?>
						<div class="relation">
							<h3><?=$genre_tag_list[0]->name?>に関連する記事</h3>
							<ul class="relation_post_list">
								<?php while($relation_post_query->have_posts()): ?>
									<?php
									$relation_post_query->the_post();
									?>
									<li>
										<div class="thumbnail">
											<a href="<?=$post->ID?>">
										<?php if(has_post_thumbnail()): ?>
											<img src="<?php echo get_the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>">
										<?php else: ?>
											<img src="<?=get_template_directory_uri()?>/img/no_image.svg" alt="">
										<?php endif; ?>
											</a>
										</div>
										<div class="title">
											<a href="<?=$post->ID?>">
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
				<?php endif; ?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	<?php endwhile; endif; ?>
<?php get_footer(); ?>
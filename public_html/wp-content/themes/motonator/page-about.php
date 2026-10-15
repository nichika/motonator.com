<?php
get_header();

// Operator profile. Edit these lines to add real background you are comfortable publishing.
$about_admin_name = 'モトネーター編集部';
$about_admin_lines = [
	'2017年にサイトを開設し、ネットスラングや流行語、ゲーム・アニメ・マンガの名言の「元ネタ」を調べて記録しています。',
	'「なんとなく使っている言葉」の由来を、出典を確かめながら、誰にでも読みやすい形で残すことを大切にしています。',
];

global $wpdb;
$about_post_count = (int) wp_count_posts('post')->publish;
$about_source_count = (int) $wpdb->get_var(
	"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' AND post_content LIKE '%class=\"sources\"%'"
);
$about_genres = get_terms(['taxonomy' => 'genre', 'hide_empty' => true]);
$about_genre_count = is_wp_error($about_genres) ? 0 : count($about_genres);
?>
	<?php if(have_posts()): while(have_posts()): the_post(); ?>
	<div class="container">
		<h1><? the_title(); ?></h1>
		<div class="introduction">
			<img src="<?=get_template_directory_uri()?>/img/about_illust.png" alt="">
			<div class="text">
				<? the_content(); ?>
			</div>
			<p class="lead">言葉の意味だけでなく、<strong>「どこから生まれ、どう広まったのか」</strong>を、出典を確かめながらやさしく解説しています。</p>
		</div>

		<ul class="about-stats">
			<li><b><?=number_format($about_post_count)?></b><span>公開中の記事</span></li>
			<li><b><?=number_format($about_source_count)?></b><span>出典つきの記事</span></li>
			<li><b><?=number_format($about_genre_count)?></b><span>ジャンル</span></li>
			<li><b>2017</b><span>から運営</span></li>
		</ul>

		<section class="about-section about-features">
			<h2>Motonatorの3つの特長</h2>
			<ul>
				<li>
					<img src="<?=get_template_directory_uri()?>/img/about_feature_search.svg" alt="">
					<h3>「元ネタ」にこだわる</h3>
					<p>意味の説明で終わらず、いつ・どこで生まれ、どう広まったのかを時系列で整理します。</p>
				</li>
				<li>
					<img src="<?=get_template_directory_uri()?>/img/about_feature_sources.svg" alt="">
					<h3>出典を明記する</h3>
					<p>確認に使った参考ページを、記事の最後に「参考・出典」として載せています。</p>
				</li>
				<li>
					<img src="<?=get_template_directory_uri()?>/img/about_feature_readable.svg" alt="">
					<h3>読みやすさを大切に</h3>
					<p>短い段落とやさしい言葉で、スマホでもさっと読めるように書いています。</p>
				</li>
			</ul>
		</section>

		<section class="about-section about-flow">
			<h2>記事ができるまで</h2>
			<ol>
				<li><b>調べる</b><span>公式サイト・出版社・辞書・新聞社・Wikipediaなどのページを実際に開いて確認します。</span></li>
				<li><b>書く</b><span>下調べや下書きにはAIも活用し、年・作品名・発言者などを一つずつ確かめながら文章にします。</span></li>
				<li><b>チェックする</b><span>誤字脱字や事実関係を、書いた工程とは別にもう一度確認します。確認できない由来は断定しません。</span></li>
				<li><b>公開する</b><span>運営者が内容を確認してから公開します。公開後に直した場合は「更新日」を変更します。</span></li>
			</ol>
		</section>

		<section class="about-section about-admin">
			<h2>運営者について</h2>
			<div class="card">
				<img src="<?=get_template_directory_uri()?>/img/circle_robot_illust.png" alt="">
				<div class="body">
					<p class="name"><?=esc_html($about_admin_name)?></p>
					<?php foreach($about_admin_lines as $about_line): ?>
						<p><?=esc_html($about_line)?></p>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<section class="about-section about-policy">
			<h2>編集方針</h2>
			<dl>
				<dt>正確さを最優先</dt>
				<dd>由来が確認できない話は、断定せず「諸説あります」「不明です」と書きます。</dd>
				<dt>AIの利用について</dt>
				<dd>記事の下調べ・下書き・チェックにAIを活用しています。公開前には運営者が内容を確認しています。</dd>
				<dt>画像について</dt>
				<dd>サイト内のイラストは、特定の作品やキャラクターを描かない、オリジナルのイメージ画像（AI生成）です。ゲームやアニメの画像・スクリーンショットは使用していません。</dd>
				<dt>権利について</dt>
				<dd>作品名・キャラクター名・セリフなどの権利は、各権利者に帰属します。解説のために必要最小限の言葉を引用しています。</dd>
				<dt>誤りを見つけたら</dt>
				<dd><a href="<?=home_url()?>/contact">お問い合わせ</a>からご連絡ください。確認のうえ、速やかに修正します。</dd>
			</dl>
		</section>

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
								<a href="<?=esc_url(get_permalink())?>">
									<?php if(has_post_thumbnail()): ?>
										<?php the_post_thumbnail('thumbnail'); ?>
									<?php else: ?>
										<img src="<?=get_template_directory_uri()?>/img/no_image.svg" alt="" width="320" height="320" loading="lazy">
									<?php endif; ?>
								</a>
							</div>
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

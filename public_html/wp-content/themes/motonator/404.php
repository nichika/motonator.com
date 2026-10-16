<?php get_header(); ?>
	<div class="container">
		<div class="main">
			<h1>ページが見つかりません</h1>
			<div class="content">
				<p>お探しのページは、移動・削除されたか、URLが間違っている可能性があります。</p>
				<p>知りたい言葉の元ネタを、下の検索ボックスから探してみてください。</p>
				<div class="notfound-search"><?php get_search_form(); ?></div>
				<h2>新着記事</h2>
				<ul>
					<?php
					$nf_query = new WP_Query(['posts_per_page' => 8, 'post_type' => 'post']);
					while($nf_query->have_posts()): $nf_query->the_post();
					?>
						<li><a href="<?=esc_url(get_permalink())?>">「<?=esc_html(get_the_title())?>」の元ネタ</a></li>
					<?php endwhile; wp_reset_postdata(); ?>
				</ul>
				<p><a href="<?=esc_url(home_url('/'))?>">トップページへ戻る</a></p>
			</div>
		</div>
		<?php get_sidebar(); ?>
	</div>
<?php get_footer(); ?>

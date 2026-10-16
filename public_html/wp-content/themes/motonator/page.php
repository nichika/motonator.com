<?php get_header(); ?>
	<?php if(have_posts()): while(have_posts()): the_post(); ?>
	<div class="container">
		<div class="main">
			<h1><? the_title(); ?></h1>
			<div class="content">
				<?php if(is_page('contact')): ?>
					<p class="contact-notice">ご送信の前に、<a href="<?=esc_url(home_url('/privacypolicy'))?>">プライバシーポリシー</a>をご確認ください。送信をもって、内容に同意されたものとみなします。</p>
				<?php endif; ?>
				<? the_content(); ?>
			</div>
		</div>
		<?php get_sidebar(); ?>
	</div>
	<?php endwhile; endif; ?>
<?php get_footer(); ?>

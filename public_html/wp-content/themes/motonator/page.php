<?php get_header(); ?>
	<?php if(have_posts()): while(have_posts()): the_post(); ?>
	<div class="container">
		<div class="main">
			<h1><? the_title(); ?></h1>
			<div class="content">
				<? the_content(); ?>
			</div>
		</div>
		<?php get_sidebar(); ?>
	</div>
	<?php endwhile; endif; ?>
<?php get_footer(); ?>

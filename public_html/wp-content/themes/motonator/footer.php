	<footer>
		©2017 Motonator　<a href="<?=home_url()?>/contact" style="color:#fff;">お問い合わせ</a>　<a href="<?=home_url()?>/privacypolicy" style="color:#fff;">プライバシーポリシー</a>
	</footer>
<?php if(is_home()): ?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="<?=get_template_directory_uri()?>/js/masonry.pkgd.min.js"></script>
	<script>
		jQuery(function($){
			function doMasonry(){
				$('.post_list').masonry({
					itemSelector: '.post_list > li',
					columnWidth: 320,
					isFitWidth: true
				});
			}
			if($(window).width()>=640){
				doMasonry();
			}
			$(window).resize(function(){
				if($(window).width()>=640){
					doMasonry();
				}
			});
		});
	</script>
<?php endif; ?>
</body>
</html>

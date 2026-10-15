<form role="search" method="get" action="<?=get_home_url()?>">
	<input name="s" type="search" placeholder="言葉を入力（例：タイパ）" aria-label="記事を検索" value="<?=esc_attr(get_search_query())?>"><button id="search_button" type="submit">検索</button>
</form>

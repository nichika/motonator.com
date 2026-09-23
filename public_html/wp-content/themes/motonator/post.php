<?php
// ページが存在しない場合はトップページにリダイレクト
wp_redirect( home_url(), '301' );
exit;
?>
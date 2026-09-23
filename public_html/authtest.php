<?php
// Temporary diagnostic file — checks whether the Authorization header
// (needed for WordPress Application Passwords) reaches PHP at all on
// this host. Delete after diagnosing.
header('Content-Type: text/plain; charset=utf-8');
echo "HTTP_AUTHORIZATION: " . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'NOT SET') . "\n";
echo "REDIRECT_HTTP_AUTHORIZATION: " . ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'NOT SET') . "\n";
foreach ($_SERVER as $k => $v) {
    if (stripos($k, 'auth') !== false) {
        echo "$k = $v\n";
    }
}

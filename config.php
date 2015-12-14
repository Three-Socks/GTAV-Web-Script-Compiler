<?php

if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
	$protocol = 'https://';
else
	$protocol = 'http://';

$installUrl = $protocol . '127.0.0.1/web_compiler';

$themeUrl = $protocol . '127.0.0.1/web_compiler/theme';

$sourceDir = dirname(__FILE__). '/';
$themeDir = dirname(__FILE__). '/theme/';

$secretKey1 = '^d~61=7E48e.2-v-';
$secretKey2 = 'qgT_%_2NW=P*:Pa-';

?>
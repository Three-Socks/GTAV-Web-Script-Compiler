<?php

//error_reporting(0);

error_reporting(E_ALL ^ E_STRICT);

ini_set('display_errors', 'Off'); 
ini_set('log_errors', 'On'); 
ini_set('error_log', '/home/3s/logs/php_errors.txt'); 

define('3Socks', true);

global $return_html;

function HTML_Home($repos)
{
	echo '
			<div class="row">
				<div class="page-header">
					<h1>Repositories</h1>
				</div>
				<div class="col-md-6">
					<div class="row">';
					
	foreach ($repos as $platform => $repo_platform)
	{
		echo '<h2>' . $platform . '</h2>';

		foreach ($repo_platform as $repo)
			echo '
						<h3><a href="https://bitbucket.org/ThreeSocks/', $repo['name'], '/">', $repo['display'], '</a></h3>';
	}

	echo '
					</div>
				</div>
				<div class="col-md-6" style="padding-left:35px;">
					<div class="row">
						<p></p>
					</div>
				</div>';

	echo '
			</div>';
}

if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
	$protocol = 'https://';
else
	$protocol = 'http://';

$installUrl = $protocol . '3socks.cf';

$themeUrl = $protocol . '3socks.cf/theme';

$sourceDir = '/home/3s/source/';
$themeDir = '/home/3s/public_html/theme/';

$script_output = '';

include $themeDir . 'theme.php';

$repos = array(
	/*'PC' => array(
		array('name' => 'gtav-stunt-helper', 'display' => 'GTAV Stunt Helper'),
	),*/
	'Console' => array(
		array('name' => 'gtav-menu-base', 'display' => 'GTAV Menu Base'),
		array('name' => 'gtav-modmanager', 'display' => 'GTAV ModManager'),
		array('name' => 'gtav-custom-camera', 'display' => 'GTAV Custom Camera'),
		array('name' => 'gtav-stuff', 'display' => 'GTAV Stuff'),
	),
);

HTML_Start_Display('Three-Socks - GTAV');

HTML_Home($repos);

HTML_End_Display();



?>
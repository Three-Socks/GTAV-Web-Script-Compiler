<?php

//error_reporting(0);

error_reporting(E_ALL ^ E_STRICT);

//ini_set('display_errors', 'Off'); 

define('3Socks', true);

require(dirname(__FILE__) . '/config.php');

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

HTML_Start_Display('GTAV');

HTML_Home($repos);

HTML_End_Display();



?>
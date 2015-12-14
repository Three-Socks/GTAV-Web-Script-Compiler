<?php

if (!defined('3Socks'))
	die();

function HTML_Start_Display($title, $title_more = '')
{
	global $installUrl, $themeUrl;

	echo '<!DOCTYPE html>
	<html lang="en">
		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="description" content="GTAV CSC/XSC">
			<meta name="author" content="">
			<link rel="icon" href="favicon.ico">

			<title>' . $title . '</title>

			<link rel="icon" type="img/ico" href="/favicon.ico">

			<!-- Bootstrap core CSS -->
			<link href="' . $themeUrl . '/bootstrap-dist/css_theme/bootstrap.css?2" rel="stylesheet">
			<link href="' . $themeUrl . '/bootstrap-dist/css/bootstrap-colorpicker.min.css" rel="stylesheet">

			<!-- Custom styles for this template -->
			<link href="' . $themeUrl . '/theme.css?2" rel="stylesheet">
			
			<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
			<!--[if lt IE 9]>
				<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
				<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
			<![endif]-->
		</head>

		<body role="document">
			<!-- Fixed navbar navbar-default/navbar-inverse -->
			<nav class="navbar navbar-default navbar-fixed-top">
				<div class="container">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="' . $installUrl . '/">' . $title . $title_more . '</a>
					</div>
					<div id="navbar" class="navbar-collapse collapse">
						<ul class="nav navbar-nav">
							<li><a href="' . $installUrl . '/index.php">Code</a></li>
							<li><a href="' . $installUrl . '/compiler/">Compiler</a></li>
							<li><a href="' . $installUrl . '/modmanager_maker/">ModManager Maker</a></li>
							<li><a href="' . $installUrl . '/hash/">Hasher</a></li>
							<li><a href="' . $installUrl . '/opcode_convert/">Opcode Convert</a></li>
						</ul>
					</div><!--/.nav-collapse -->
				</div>
			</nav>

			<div class="container" role="main">
';
}

function HTML_Script_Info_Section($HeaderValues)
{
	global $string_pages_count;
	global $code_pages_count;

	echo '
				<div class="page-header">
					<h1>Script Info</h1>
				</div>
				<div class="row">

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Script Name</dt>
							<dd>' . $HeaderValues['filename'] . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Magic</dt>
							<dd>' .  strtoupper(bin2hex($HeaderValues['magic'])) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Globals Version</dt>
							<dd>' . strtoupper(bin2hex($HeaderValues['globalsversion'])) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Filesize</dt>
							<dd>' . number_format($HeaderValues['filesize']) . ' <i>bytes</i></dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Code Pages</dt>
							<dd>' . $code_pages_count . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>String Pages</dt>
							<dd>' . $string_pages_count . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Native Pages</dt>
							<dd>1</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Code Size</dt>
							<dd>' . number_format($HeaderValues['codelength']) . ' <i>bytes</i></dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>String Size</dt>
							<dd>' . number_format($HeaderValues['stringssize']) . ' <i>bytes</i></dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Natives Count</dt>
							<dd>' . number_format($HeaderValues['nativescount']) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Parameter Count</dt>
							<dd>' . number_format($HeaderValues['parametercount']) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Globals Count</dt>
							<dd>' . number_format($HeaderValues['globalscount']) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Statics Count</dt>
							<dd>' . number_format($HeaderValues['staticscount']) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

				</div>';
}

function HTML_Upload_Section($html_return)
{
	global $maxDecompileSize;

	$save_code = isset($_POST['save_code']) ? $_POST['save_code'] : 1;
	
	echo '
			<div class="col-md-5">
				<div class="page-header">
					<h1>Decompile</h1>
				</div>
				<form action="" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<div style="float:left;width:49%;">
							<label for="upload_script">Script input</label>
							<input type="hidden" name="MAX_FILE_SIZE" value="' . $maxDecompileSize . '" />
							<input type="file" id="upload_script" name="upload_script">
							<p class="help-block">*.csc, *.xsc</p>
							<p class="help-block">Max script size: ' . round($maxDecompileSize / 1024, 1) . ' KB</p>
							<div class="checkbox">
								<label for="save_code">
								<input type="checkbox" id="save_code" name="save_code" value="1"', $save_code ? ' checked' : '', '>
										Save the decompiled code instead of outputting.
								</label>
							</div>
						</div>
						<div class="clearfix"></div>
						<br />
						<button type="submit" class="btn btn-default">Decompile</button>
						<br />
						<br />';
						
						if (isset($_FILES['upload_script']) && !empty($html_return))
							echo $html_return;
						
						echo '
					</div>
				</form>
			</div>';

/*			<div class="col-md-4">
				<div class="page-header">
					<h1>Statics Editor</h1>
				</div>
				<form action="" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label for="upload_script_statics">Script input</label>
						<input type="hidden" name="MAX_FILE_SIZE" value="' . $maxDecompileSize . '" />
						<input type="file" id="upload_script_statics" name="upload_script_statics">
						<p class="help-block">*.csc, *.xsc</p>
						<br />
						<button type="submit" class="btn btn-default">Edit</button>
						<br />
						<br />';
						
						if (isset($_POST['statics_edit_action']) && !empty($html_return))
							echo $html_return;
						
						echo '
					</div>
				</form>
			</div>
*/
	echo '
			<div class="col-md-6">
				<div class="page-header">
					<h1>Compile</h1>
				</div>
				<form action="" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<div style="float:left;width:49%;">
							<label for="upload_script_template">Script static template input</label>
							<input type="file" id="upload_script_template" name="upload_script_template">
							<p class="help-block">*.csc, *.xsc</p>
						</div>
						<div style="float:right;width:49%;">
							<label for="upload_code">Code input</label>
							<input type="file" id="upload_code" name="upload_code">
							<p class="help-block">*.csa, *.xsa</p>
						</div>
						<div class="clearfix"></div>
						<br />
						<button type="submit" class="btn btn-default">Compile</button>
						<br />
						<br />';
						
						if (isset($_FILES['upload_code']) && !empty($html_return))
							echo $html_return;
						
						echo '
					</div>
				</form>
			</div>
';

}

function HTML_Hash_Section($html_return, $button_ids)
{
	global $installUrl;

	$text = isset($_POST['string_to_hash']) ? trim($_POST['string_to_hash']) : '';
	
	echo '
			<div class="row">
				<form action="" class="form-inline" method="post">
					<div class="col-lg-6">
						<div class="page-header">
							<h1>Joaat Hasher</h1>
						</div>
						<div class="form-group">
							<label for="string_to_hash">String</label>
							<input size="36" type="text" class="form-control" id="string_to_hash" name="string_to_hash" value="', htmlspecialchars($text, ENT_QUOTES, 'UTF-8'), '">
							<button type="submit" class="btn btn-default">Hash</button>
						</div>
						<br />
						<br />';

						if (empty($_POST['cheat_combo_ids']) && !empty($html_return))
							echo $html_return;

						echo '
					</div>
					<div class="col-lg-6">
						<div class="page-header">
							<h1>Cheat Combo Hasher</h1>
						</div>
						<div id="cheat_combo_buttons" class="text-center btn-toolbar" style="float: none;">
							<div class="btn-group" style="float: none;">
								<button button_id="1" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash/buttons/button_1.png" alt="" style="width:32px;" />
								</button>
								<button button_id="2" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash//buttons/button_2.png" alt="" style="width:32px;" />
								</button>
								<button button_id="3" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash//buttons/button_3.png" alt="" style="width:32px;" />
								</button>
								<button button_id="4" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash//buttons/button_4.png" alt="" style="width:32px;" />
								</button>
							</div>
							<div class="btn-group" style="float: none;">
								<button button_id="5" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash/buttons/button_5.png" alt="" style="width:32px;" />
								</button>
								<button button_id="6" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash/buttons/button_6.png" alt="" style="width:32px;" />
								</button>
								<button button_id="7" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash/buttons/button_7.png" alt="" style="width:32px;" />
								</button>
								<button button_id="8" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash/buttons/button_8.png" alt="" style="width:32px;" />
								</button>
							</div>
							<div class="btn-group" style="float: none;margin-top:5px;">
								<button button_id="9" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash/buttons/button_9.png" alt="" style="width:32px;" />
								</button>
								<button button_id="10" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash/buttons/button_10.png" alt="" style="width:32px;" />
								</button>
								<button button_id="11" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash/buttons/button_11.png" alt="" style="width:32px;" />
								</button>
								<button button_id="12" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash/buttons/button_12.png" alt="" style="width:32px;" />
								</button>
							</div>
						</div>

						<div class="text-center" style="margin-top: 20px;">
							<input id="cheat_combo_ids" type="hidden" name="cheat_combo_ids" value="', !empty($_POST['cheat_combo_ids']) ? htmlspecialchars($_POST['cheat_combo_ids'], ENT_QUOTES, 'UTF-8') : '0', '">
							<button id="cheat_combo_clear" type="button" class="btn btn-default">Clear</button>
							<button type="submit" class="btn btn-default">Submit</button>
						</div>
						
						<div id="cheat_combo" class="btn-toolbar" style="', !empty($button_ids) ? '' : 'display:none;', 'margin-top:20px;">';
						if (!empty($_POST['cheat_combo_ids']) && !empty($html_return))
							echo '<div id="html_return">' . $html_return . '</div>';
						
						echo '
						<br />
						<p>Cheat Combo</p>
							<div class="btn-group">';

	if (!empty($button_ids))
	{
		foreach($button_ids as $button)
			echo '
								<a button_id="' . $button . '" href="#" class="btn btn-default">
									<img src="' . $installUrl . '/hash/buttons/button_' . $button . '.png" alt="" style="width:16px;" />
								</a>';
			
	}
							
	echo '
							</div>
						</div>
						
					</div>
				</form>
			</div>';

}

function HTML_ModManger_Maker_Section($modmanager_scripts, $html_return)
{
	$script_exist = isset($_POST['script_exist']) ? 1 : 0;
	$platform = !empty($_POST['platform']) ? $_POST['platform'] : '';
	$custom_bool_off = !empty($_POST['custom_bool_off']) ? filter_var($_POST['custom_bool_off'], FILTER_SANITIZE_STRING) : '';
	$custom_bool_on = !empty($_POST['custom_bool_on']) ? filter_var($_POST['custom_bool_on'], FILTER_SANITIZE_STRING) : 'Enabled';
	$custom_font = !empty($_POST['custom_font']) ? (int) $_POST['custom_font'] : 0;
	$menu_header = !empty($_POST['menu_header']) ? colourDataValidate($_POST['menu_header']) : '#000000';
	$menu_window = !empty($_POST['menu_window']) ? colourDataValidate($_POST['menu_window']) : '#000000';
	$menu_highlight_window = !empty($_POST['menu_highlight_window']) ? colourDataValidate($_POST['menu_highlight_window']) : '#f0f0f0';
	$menu_header_text = !empty($_POST['menu_header_text']) ? colourDataValidate($_POST['menu_header_text']) : '#f0f0f0';
	$menu_highlighted_text = !empty($_POST['menu_highlighted_text']) ? colourDataValidate($_POST['menu_highlighted_text']) : '#000000';
	$menu_text = !empty($_POST['menu_text']) ? colourDataValidate($_POST['menu_text']) : '#f0f0f0';
	$menu_pos = !empty($_POST['menu_pos']) ? (int) $_POST['menu_pos'] : 0;
	$menu_sfx = !empty($_POST['menu_sfx']) ? (int) $_POST['menu_sfx'] : 1;

	echo '
			<div class="row">
				<div class="page-header">
					<h1>ModManager Maker</h1>
				</div>
				<form action="" id="modmanager_form" class="form-inline" method="post">
				<div class="col-md-6">';
					/*<div class="row" style="margin-bottom: 10px;">
						<div class="col-xs-6">
							<button type="submit" class="btn btn-default">Submit</button>
						</div>
						
						<div class="col-xs-6">
							<input id="modmanager_add" class="btn btn-default" type="button" value="Add">
							<input id="modmanager_delete" class="btn btn-default" type="button" value="Delete">
						</div>
					</div>*/
					echo '<div class="row">
						<div class="col-xs-6">
							(Close on load) &nbsp; Display Name
						</div>
						<div class="col-xs-6">
							Script
						</div>
					</div>';

	foreach ($modmanager_scripts as $key => $modmanager)
	{
		$modmanager_item_menu = isset($modmanager['menu']) && $modmanager['menu'] == 1 ? 1 : 0;

		echo '
					<div class="row" id="modmanager_item_div_' . $key . '">
					<div class="col-xs-6" style="margin: 8px 0;">
						<div class="input-group">
							<span class="input-group-addon">
								<input id="modmanager_item_menu_' . $key . '_h" name="modmanager_item_menu_' . $key . '" type="hidden">
								<input id="modmanager_item_menu_' . $key . '" name="modmanager_item_menu_' . $key . '" type="checkbox"', $modmanager_item_menu ? ' checked': '', '>
							</span>
							<input type="text" class="form-control" size="35" name="modmanager_item[]" id="modmanager_item_' . $key . '" value="' . $modmanager['name'] . '" maxlength="60">
						</div>
					</div>

					<div class="col-xs-6" style="margin: 8px 0;">				
						<div class="input-group">
							<input type="text" class="form-control" name="modmanager_item_script[]" id="modmanager_item_script_' . $key . '" value="' . $modmanager['script'] . '" maxlength="60" spellcheck="false">
						</div>
					</div>
					</div>';
	}

	echo '
					<div id="modmanager_items"></div>

					<div class="row" style="margin-top: 10px;">
						<div class="col-xs-6">
							<input type="hidden" name="modmanager_maker">
							<button type="submit" class="btn btn-default">Submit</button>
							<!--<input id="modmanager_save" class="btn btn-default" type="button" value="Save">-->
						</div>
						
						<div class="col-xs-6">
							<input id="modmanager_add_2" class="btn btn-default" type="button" value="Add">
							<input id="modmanager_delete_2" class="btn btn-default" type="button" value="Delete">
						</div>
					</div>
					</div>
					<div class="col-md-6" style="padding-left:35px;">
						<div class="row">
						<p>ModManager Maker for GTAV (PS3/360). Enter the display name and script to load into the text boxes and press submit to compile into a script.</p>
						<p>Select the checkbox next to the display name to make ModManager close when the script is loaded.</p>
						<p>To open ModManager: L3+R3 (PS3) / LS+RS (360) .</p>
						<h1>Options</h1>
							<div class="checkbox">
								<label for="script_exist">
								<input type="checkbox" id="script_exist" name="script_exist" value="1"', $script_exist ? ' checked' : '', '>
										&nbsp; Remove script exist checks.
								</label>
							</div>

							<br />
							<br />

							<div class="form-group">
								<label for="custom_bool_off">Disabled string</label><br />
								<input type="text" class="form-control" id="custom_bool_off" name="custom_bool_off" value="', $custom_bool_off, '">
							</div>

							<br />
							<br />

							<div class="form-group">
								<label for="custom_bool_on">Enabled string</label><br />
								<input type="text" class="form-control" id="custom_bool_on" name="custom_bool_on" value="', $custom_bool_on, '">
							</div>

							<br />
							<br />

							<div class="form-group">
								<label for="custom_font">Font (0-7)</label><br />
								<input type="number" min="0" max="7" class="form-control" id="custom_font" name="custom_font" value="', $custom_font, '">
							</div>

							<br />
							<br />

							<div class="input-group modmanager_menu_header">
								<label for="menu_header">Menu Header Window</label><br />
								<input type="text" id="menu_header" name="menu_header" value="', $menu_header, '" class="form-control" />
								<span class="input-group-addon"><i></i></span>
							</div>

							<br />
							<br />

							<div class="input-group modmanager_menu_window">
								<label for="menu_window">Menu Items Window</label><br />
								<input type="text" id="menu_window" name="menu_window" value="', $menu_window, '" class="form-control" />
								<span class="input-group-addon"><i></i></span>
							</div>

							<br />
							<br />

							<div class="input-group modmanager_menu_highlight_window">
								<label for="menu_highlight_window">Menu Highlight Bar</label><br />
								<input type="text" id="menu_highlight_window" name="menu_highlight_window" value="', $menu_highlight_window, '" class="form-control" />
								<span class="input-group-addon"><i></i></span>
							</div>

							<br />
							<br />

							<div class="input-group modmanager_menu_header_text">
								<label for="menu_header_text">Menu Header Text</label><br />
								<input type="text" id="menu_header_text" name="menu_header_text" value="', $menu_header_text, '" class="form-control" />
								<span class="input-group-addon"><i></i></span>
							</div>

							<br />
							<br />

							<div class="input-group modmanager_menu_highlighted_text">
								<label for="menu_highlighted_text">Menu Highlighted Text</label><br />
								<input type="text" id="menu_highlighted_text" name="menu_highlighted_text" value="', $menu_highlighted_text, '" class="form-control" />
								<span class="input-group-addon"><i></i></span>
							</div>

							<br />
							<br />

							<div class="input-group modmanager_menu_text">
								<label for="menu_text">Menu Items Text</label><br />
								<input type="text" id="menu_text" name="menu_text" value="', $menu_text, '" class="form-control" />
								<span class="input-group-addon"><i></i></span>
							</div>

							<br />
							<br />

							<div class="form-group">
								<label for="menu_pos">Menu Position</label><br />
								<select class="form-control" id="menu_pos" name="menu_pos">
									<option value="0"', $menu_pos == '0' ? ' selected' : '', '>Left</option>
									<option value="1"', $menu_pos == '1' ? ' selected' : '', '>Right</option>
								</select>										 
							</div>

							<br />
							<br />

							<div class="form-group">
								<label for="menu_sfx">Menu Sound Effects</label><br />
								<select class="form-control" id="menu_sfx" name="menu_sfx">
									<option value="0"', $menu_sfx == '0' ? ' selected' : '', '>Off</option>
									<option value="1"', $menu_sfx == '1' ? ' selected' : '', '>On</option>
								</select>										 
							</div>

							<br />
							<br />

							<div class="form-group">
								<label for="platform">Platform</label><br />
								<select class="form-control" id="platform" name="platform">
									<option value="PS3"', $platform == 'PS3' ? ' selected' : '', '>PS3 (CSC)</option>
									<option value="360"', $platform == '360' ? ' selected' : '', '>360 (XSC)</option>
								</select>										 
							</div>';

	if (isset($_POST['modmanager_maker']) && !empty($html_return))
		echo $html_return;

	echo '
					</div>
					</div>

				</form>';


				
	echo '
			</div>';
}

function HTML_Convert_Section($html_return)
{
	$convert_format = isset($_POST['convert_format']) ? $_POST['convert_format'] : 'zorg';
	$download_code = isset($_POST['download_code']) ? 1 : 0;

	echo '
			<div class="col-md-5">
				<div class="page-header">
					<h1>Convert</h1>
				</div>
				<form action="" method="post" enctype="multipart/form-data">
					<div class="form-group">
							<label for="upload_code">Code input</label>
							<input type="file" id="upload_code" name="upload_code">
							<p class="help-block">*.csa, *.xsa</p>
							<div class="radio">
								<label>
									<input type="radio" name="convert_format" id="optionsRadios2" value="zorg"', $convert_format == "zorg" ? ' checked' : '', '>
									Convert from zorgs format to web.
								</label>
							</div>
							<div class="radio">
								<label>
									<input type="radio" name="convert_format" id="optionsRadios1" value="web"', $convert_format == "web" ? ' checked' : '', '>
									Convert from web format to zorgs.
								</label>
							</div>
							<div class="checkbox">
								<label for="download_code">
								<input type="checkbox" id="download_code" name="download_code" value="1"', $download_code ? ' checked' : '', '>
										Download code.
								</label>
							</div>
						<br />
						<button type="submit" class="btn btn-default">Convert</button>
						<br />
						<br />
					</div>
				</form>
			</div>';

			if (isset($_FILES['upload_code']) && !empty($html_return))
			{
				echo '
						<div class="col-md-5">
							<div class="page-header">
								<h1>Output</h1>
							</div>';

				echo '
								<form>
									<div class="form-group">
										<textarea class="form-control" id="script_code" class="form-control" rows="3" onfocus="var inp=this;setTimeout(function(){inp.select();},10);">' . $html_return . '</textarea>
										<br />
										<p><button id="select-all-button" type="button" class="btn btn-primary" onfocus="var inp=document.getElementById(\'script_code\');setTimeout(function(){inp.select();},10);">Select All</button></p>
									</div>
								</form>
							</div>';
			}
}

function HTML_Code_Section($HeaderValues)
{
	echo '
				<div class="page-header">
					<h1>Code - ' . number_format($HeaderValues['codelength']) . ' <i>bytes</i></h1>
				</div>';
}

function HTML_Code_download($code_filename, $time)
{
	echo  '<a class="btn btn-primary btn-lg active" role="button" href="' . $code_filename . '">Download Code</a>
					<p class="text-muted">Decompiled in ' . $time . ' seconds</p>';
}

function HTML_Code_textarea($decompiled_output, $time)
{
	echo '
				<div class="row">
					<form>
						<div class="form-group">
							<textarea class="form-control" id="script_code" class="form-control" rows="3" onfocus="var inp=this;setTimeout(function(){inp.select();},10);">' .$decompiled_output . '</textarea>
							<br />
							<p><button id="select-all-button" type="button" class="btn btn-primary" onfocus="var inp=document.getElementById(\'script_code\');setTimeout(function(){inp.select();},10);">Select All</button></p>
						</div>
					</form>
				<p class="text-muted">Decompiled in ' . $time . ' seconds</p>
				</div>';

}

function HTML_Errors($script_errors)
{
	echo '
				<textarea class="form-control" rows="20" spellcheck="false">

				--Errors found in script--
				
				';
	foreach($script_errors as $script_error)
		echo $script_error . "\n";

	echo "</textarea>\n" . "If this is a stock Rockstar script, then something went wrong. If this is a custom XSC file, then it probably means some sneaky shit was pulled to prevent decompiling... but it was still decompiled!";
}

function HTML_progress_bar()
{
	echo '
	<div class="progress">
		<div id="progress_bar" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
	</div>';

}

function HTML_Native_Section($script_sections, $HeaderValues, $raw_natives_name, $raw_hashes_name)
{
	$natives_count = number_format($HeaderValues['nativescount']);
	$text_natives_array = Get_Text_Natives($raw_natives_name, $raw_hashes_name, $script_sections);
	
	echo '
	<div class="col-md-4">
		<div class="page-header">
			<h1>Natives - ' . $natives_count . '</h1>
		</div>
		<textarea class="form-control" rows="20" cols="40" spellcheck="false">';
	
	foreach ($text_natives_array as $native)
		echo $native . "\n";
	
	echo '</textarea>
	</div>';

}

function HTML_String_Section($script_sections, $HeaderValues)
{
	$string_sect = $script_sections['string_sect'];
	
	$stringssize = number_format($HeaderValues['stringssize']);//Could be used for display, but we show # of strings instead
	$string_sect_length = strlen($string_sect);//Actually used for code
	
	$string_sect = str_replace("00000000000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("00000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("00000000000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000000000", "00", $string_sect);
	$string_sect = str_replace("00000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000", "00", $string_sect);
	$string_sect = str_replace("00000000", "00", $string_sect);
	$string_sect = str_replace("000000", "00", $string_sect);
	$string_sect = str_replace("0000", "00", $string_sect); //Above code just gets rid of blanks
	
	$number_of_strings_total = number_format(substr_count($string_sect, "00")); //Could be used to display approx # of strings

	echo '
	<div class="col-md-4">
		<div class="page-header">
			<h1>Strings -  ' . $stringssize . ' <i>bytes</i></h1>
		</div>
		<textarea class="form-control" rows="20" cols="40" spellcheck="false">';

	if ($string_sect == "No Strings Found In This Script"){ //This checks if string sect is null and just displays null text - yes, some scripts dont have string sections :?
		echo $string_sect;
		goto finishHTML;
	}
	
	$buffer = 0;
	
	while($buffer <= $string_sect_length){
	
		$byte_not_null = true;
		$i = 0;
		
		while($byte_not_null == true){
			$byte = substr($string_sect, $buffer, 2);
			if($byte == '00'){
				$byte_not_null = false;
				$string = Hex_to_Text(implode("", $bytes));
				echo "$string";
				echo "\n";
				unset($bytes);
				$string = null;
				$byte = null;
			}else{
				if($buffer >= $string_sect_length){
					goto breakloop;
				}
				$bytes[$i] = $byte;
				$byte = null;
				$i++;
			}
			$buffer = $buffer + '2';
		}
	}
	breakloop:
	
	$string_sect = null;
	
	//Finish HTML
	finishHTML:
	echo '</textarea>
	</div>';

}

function HTML_Statics_Section($script_sections, $HeaderValues)
{
	echo '
		<div class="col-md-4">
			<div class="page-header">
				<h1>Statics - ' . $HeaderValues['staticscount'] . '</h1>
			</div>
			<div style="height: 600px; overflow: auto;">
			<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th>#</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>';

	foreach ($script_sections['statics_sect'] as $index => $static_hex)
		echo '
							<tr>
								<th scope="row">' . $index . '</th>
								<td>' . hexdec($static_hex) . '</td>
							</tr>';

	echo '

						</tbody>
			</table>
			</div>
		</div>';
}

function HTML_Statics_Edit($statics_sect, $HeaderValues, $script_filename, $script_filename_ext)
{
	echo '
		<div class="col-md-4">
			<div class="page-header">
				<h1>Statics</h1>
			</div>
			<form action="" method="post">
			<button type="submit" class="btn btn-default">Update</button>
			<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th>#</th>
								<th class="text-center">Value</th>
							</tr>
						</thead>
						<tbody id="statics">';

	foreach ($statics_sect as $index => $static_hex)
		echo '
							<tr>
								<th scope="row">' . $index . '</th>
								<td align="center">
									<div style="width: 80px;">
										<input id="statics_input_' . $index . '" name="statics_input_' . $index . '" type="number" class="form-control" value="' . hexdec($static_hex) . '">
									</div>
								</td>
							</tr>';

	echo '

						</tbody>
			</table>
			<div style="float:right;">
				<input id="statics_add" class="btn btn-default" type="button" value="Add">
				<input id="statics_delete" class="btn btn-default" type="button" value="Delete">
			</div>
			<br />
			<input type="hidden" name="statics_edit_action" value="1">
			<input type="hidden" name="script_filename" value="' . $script_filename . '">
			<input type="hidden" name="script_filename_ext" value="' . $script_filename_ext . '">
			<button type="submit" class="btn btn-default">Update</button>
			</form>
		</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script type="text/javascript">
			$( document ).ready(function() {
				var statics_count = ' . count($statics_sect) . ';
				$( "#statics_add" ).click(function() {
					$( "#statics" ).append( \'<tr><th scope="row">\' + statics_count + \'</th><td align="center"><div style="width: 80px;"><input id="statics_input_\' + statics_count + \'" name="statics_input_\' + statics_count + \'" type="number" class="form-control" value="0"></div></td></tr>\');
					statics_count++;
				});
				$( "#statics_delete" ).click(function() {
					if (statics_count != 0)
					{
						$("#statics tr:last").remove();
						statics_count--;
					}
				});
			});
		</script>';
}

function HTML_End_Display($script_code = "")
{
	global $themeUrl;

	echo '
    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="' . $themeUrl . '/bootstrap-dist/js/bootstrap.min.js"></script>';
		if (!empty($script_code))
			echo $script_code;
		
		echo '
  </body>
</html>';
}
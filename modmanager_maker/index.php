<?php

//error_reporting(0);

error_reporting(E_ALL ^ E_STRICT);

//ini_set('display_errors', 'Off'); 

require('../config.php');

define('3Socks', true);

function colourDataValidate($data)
{
  $data = str_replace("#", "", $data);
	if (ctype_xdigit($data))
		return "#" . $data;
	else
		return "#000000";
}

function hex2rgb($hex)
{
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   //return implode(",", $rgb); // returns the rgb values separated by commas
   return $rgb; // returns an array with the rgb values
}

global $return_html;

$script_output = '';

include $themeDir . 'theme.php';

$modmanager_scripts = array(
	array(
		'name' => 'Three-Socks Trainer',
		'script' => '3s_trainer',
	),
	array(
		'name' => 'Console Trainer V',
		'script' => 'console_trainer_v',
	),
	array(
		'name' => 'Slinky\'s Animation Menu',
		'script' => 'slinky_anim',
	),
	array(
		'name' => 'ap ii intense Menu',
		'script' => 'rock_menu',
	),
	array(
		'name' => 'VCA',
		'script' => 'ecb_menu',
	),
	array(
		'name' => 'Custom Camera',
		'script' => 'custom_cam',
	),
	array(
		'name' => 'North Yankton',
		'script' => 'north_yankton',
		'menu' => 1,
	),
	array(
		'name' => 'XYZH Coords',
		'script' => 'xyzh_coords',
	),
);


if (isset($_POST['modmanager_maker']))
{
	if (!empty($_POST['modmanager_item']) && !empty($_POST['modmanager_item_script']))
	{
		$script_exist = isset($_POST['script_exist']) ? 1 : 0;
		$platform = !empty($_POST['platform']) ? $_POST['platform'] : '';
		$custom_bool_off = !empty($_POST['custom_bool_off']) ? filter_var($_POST['custom_bool_off'], FILTER_SANITIZE_STRING) : '';
		$custom_bool_on = !empty($_POST['custom_bool_on']) ? filter_var($_POST['custom_bool_on'], FILTER_SANITIZE_STRING) : 'Enabled';
		$custom_font = !empty($_POST['custom_font']) ? (int) $_POST['custom_font'] : 0;
		$menu_header = !empty($_POST['menu_header']) ? hex2rgb(colourDataValidate($_POST['menu_header'])) : hex2rgb('#000000');
		$menu_window = !empty($_POST['menu_window']) ? hex2rgb(colourDataValidate($_POST['menu_window'])) : hex2rgb('#000000');
		$menu_highlight_window = !empty($_POST['menu_highlight_window']) ? hex2rgb(colourDataValidate($_POST['menu_highlight_window'])) : hex2rgb('#f0f0f0');
		$menu_header_text = !empty($_POST['menu_header_text']) ? hex2rgb(colourDataValidate($_POST['menu_header_text'])) : hex2rgb('#f0f0f0');
		$menu_highlighted_text = !empty($_POST['menu_highlighted_text']) ? hex2rgb(colourDataValidate($_POST['menu_highlighted_text'])) : hex2rgb('#000000');
		$menu_text = !empty($_POST['menu_text']) ? hex2rgb(colourDataValidate($_POST['menu_text'])) : hex2rgb('#f0f0f0');
		$menu_pos = isset($_POST['menu_pos']) ? (int) $_POST['menu_pos'] : 0;
		$menu_sfx = isset($_POST['menu_sfx']) ? (int) $_POST['menu_sfx'] : 1;

		$custom_modmanager_scripts = array();
		foreach ($_POST['modmanager_item'] as $key => $post_val)
		{
			if (!empty($_POST['modmanager_item_script'][$key]) && !empty($post_val))
			{
				$modmanager_item = str_replace(array("\"", ":"), "", $post_val);
				$modmanager_item_script = str_replace(array("\"", ":"), "", $_POST['modmanager_item_script'][$key]);
				$modmanager_item = (strlen($modmanager_item) > 63) ? substr($modmanager_item,0, 63) : $modmanager_item;
				$modmanager_item_script = (strlen($modmanager_item_script) > 63) ? substr($modmanager_item_script,0, 63) : $modmanager_item_script;

				$menu_key = 'modmanager_item_menu_' . $key;
				$modmanager_item_menu = !empty($_POST[$menu_key]) ? 1 : 0;
				
				$custom_modmanager_scripts[] = 
					array(
						'name' => $modmanager_item,
						'script' => $modmanager_item_script,
						'menu' => $modmanager_item_menu,
					);
			}
		}

		if (!empty($custom_modmanager_scripts))
		{
			if (count($custom_modmanager_scripts) > 50)
				array_splice($custom_modmanager_scripts, count($custom_modmanager_scripts) - 50);
		
			include $sourceDir . 'compiler/compiler.php';

			$statics_sect = array();

			$xsc_template_hex = Get_XSC_Hex($sourceDir . 'modmanager/modmanager.csc');

			$header = GetHeader($xsc_template_hex);  //Get Header
			$HeaderValues = GetHeaderValues($header, $xsc_template_hex);
			$statics_sect = Read_Statics_Section($HeaderValues, $xsc_template_hex);

			if (count($statics_sect) <= 1)
			{
				for ($i = 0; $i < 15; $i++)
					$statics_sect[$i] = '00000000';
			}

			$raw_code = file_get_contents($sourceDir . 'modmanager/modmanager.csa');

			$script_code = "";

			foreach ($custom_modmanager_scripts as $key => $modmanager)
			{
				if (!$script_exist)
					$script_code .= "
PushString \"" . $modmanager['script'] . "\"
CallNative does_script_exist 1 1
JumpFalse @Label_script_config_exist_" . $key;

				$script_code .= "
PushString \"" . $modmanager['name'] . "\"
PushString \"" . $modmanager['script'] . "\"
PushS 1024";

				if (isset($modmanager['menu']) && $modmanager['menu'] == 1)
					$script_code .= "
Call @modmanager_addItem_script_menu";
				else
					$script_code .= "
Call @modmanager_addItem_script";

				if (!$script_exist)
					$script_code .= "
:Label_script_config_exist_" . $key;
			}

			// Add Scripts
			$raw_code = str_replace(":Label_modmanager_script_config_set\nPushString \"ModManager\"\nCall @menu_set_title\n", ":Label_modmanager_script_config_set\nPushString \"ModManager\"\nCall @menu_set_title\n" . $script_code, $raw_code);

			// Custom bool off/on
			$raw_code = str_replace("PushString \"\"\nPushString \"Enabled\"\nCall @menu_set_bool_strings", "PushString \"" . $custom_bool_off . "\"\nPushString \"" . $custom_bool_on . "\"\nCall @menu_set_bool_strings", $raw_code);

			// Custom Font
			$raw_code = str_replace(":Label_modmanager_set_font\npush_0\nCall @menu_set_font", "Push " . $custom_font . "\nCall @menu_set_font", $raw_code);

			// Menu Header
			$raw_code = str_replace(":Label_modmanager_menu_header\npush_0\npush_0\npush_0\nCall @menu_set_header_scroll_window_colour", "Push " . $menu_header[0] . "\nPush " . $menu_header[1] . "\nPush " . $menu_header[2] . "\nCall @menu_set_header_scroll_window_colour", $raw_code);

			// Menu Window
			$raw_code = str_replace(":Label_modmanager_menu_window\npush_0\npush_0\npush_0\nCall @menu_set_items_window_colour", "Push " . $menu_window[0] . "\nPush " . $menu_window[1] . "\nPush " . $menu_window[2] . "\nCall @menu_set_items_window_colour", $raw_code);

			// Menu Highlight Window
			$raw_code = str_replace(":Label_modmanager_menu_highlight_window\nPush1 240\nPush1 240\nPush1 240\nCall @menu_set_highlight_bar_colour", "Push " . $menu_highlight_window[0] . "\nPush " . $menu_highlight_window[1] . "\nPush " . $menu_highlight_window[2] . "\nCall @menu_set_highlight_bar_colour", $raw_code);

			// Menu Header Text
			$raw_code = str_replace(":Label_modmanager_menu_header_text\nPush1 240\nPush1 240\nPush1 240\nCall @menu_set_header_scroll_text_colour", "Push " . $menu_header_text[0] . "\nPush " . $menu_header_text[1] . "\nPush " . $menu_header_text[2] . "\nCall @menu_set_header_scroll_text_colour", $raw_code);

			// Menu Highlighted Text
			$raw_code = str_replace(":Label_modmanager_menu_highlighted_text\npush_0\npush_0\npush_0\nCall @menu_set_highlighted_text_colour", "Push " . $menu_highlighted_text[0] . "\nPush " . $menu_highlighted_text[1] . "\nPush " . $menu_highlighted_text[2] . "\nCall @menu_set_highlighted_text_colour", $raw_code);

			// Menu Text
			$raw_code = str_replace(":Label_modmanager_menu_text\nPush1 240\nPush1 240\nPush1 240\nCall @menu_set_non_highlighted_text_colour", "Push " . $menu_text[0] . "\nPush " . $menu_text[1] . "\nPush " . $menu_text[2] . "\nCall @menu_set_non_highlighted_text_colour", $raw_code);

			// Menu Pos
			$raw_code = str_replace(":Label_modmanager_menu_pos\npush_0\nCall @menu_set_menu_align", "Push " . $menu_pos . "\nCall @menu_set_menu_align", $raw_code);
			
			// Menu SFX
			$raw_code = str_replace(":Label_modmanager_menu_sfx\npush_1\nCall @menu_set_sfx", "Push " . $menu_sfx . "\nCall @menu_set_sfx", $raw_code);

			/*var_dump('$script_exist - ' . $script_exist);
			var_dump('$platform - ' . $platform);
			var_dump('$custom_bool_off - ' . $custom_bool_off);
			var_dump('$custom_bool_on - ' . $custom_bool_on);
			var_dump('$custom_font - ' . $custom_font);
			var_dump('$menu_header - ' . $menu_header);
			var_dump('$menu_window - ' . $menu_window);
			var_dump('$menu_highlight_window - ' . $menu_highlight_window);
			var_dump('$menu_header_text - ' . $menu_header_text);
			var_dump('$menu_highlighted_text - ' . $menu_highlighted_text);
			var_dump('$menu_text - ' . $menu_text);*/
			//var_dump($menu_sfx);
			//var_dump($raw_code);
			//die();

			//split by lines
			$raw_code = str_replace("\r\n", "\n", $raw_code);
			$raw_code = bin2hex($raw_code);
			$raw_code = str_replace("c2a0", "20", $raw_code);
			$raw_code = hex2bin($raw_code);
			$lines = explode("\n", $raw_code);
			$code_lines = array();

			//throw each line into array newlines
			//remove blank lines and comment lines
			foreach($lines as $line)
			{
				if($line == "" || trim($line) == "" || $line == "\r" || $line == "\n" || $line == "\r\n" || substr_count($line, "/") > 0)
					continue;

				$code_lines[] = $line;//$code_lines is lines of code
			}

			$xsc_final_filename = 'modmanager';
			
			if ($platform == "360")
				$ext = "xsa";
			else
				$ext = "csa";
			
			parse_code($code_lines, $statics_sect, $xsc_final_filename, $ext);
		}
	}
}

	HTML_Start_Display('GTAV ModManager Maker 1.1', ' <span style="font-size:12px;">for GTAV</span>');

	HTML_ModManger_Maker_Section($modmanager_scripts, $return_html);

	HTML_End_Display('
    <script src="' . $themeUrl . '/bootstrap-dist/js/bootstrap-colorpicker.min.js"></script>
		<script type="text/javascript">
			$( document ).ready(function() {
				var modmanager_item_count = ' . count($modmanager_scripts) . ';
				$( "#modmanager_add, #modmanager_add_2" ).click(function() {
					if (modmanager_item_count < 50)
					{
						$( "#modmanager_items" ).append( \'<div class="row" id="modmanager_item_div_\' + modmanager_item_count + \'"><div class="col-xs-6" style="margin: 8px 0;"><div class="input-group"> <span class="input-group-addon"> <input id="modmanager_item_menu_\' + modmanager_item_count + \'_h" name="modmanager_item_menu_\' + modmanager_item_count + \'" type="hidden"> <input id="modmanager_item_menu_\' + modmanager_item_count + \'" name="modmanager_item_menu_\' + modmanager_item_count + \'" type="checkbox" > </span> <input type="text" class="form-control" size="35" name="modmanager_item[]" id="modmanager_item_\' + modmanager_item_count + \'"  maxlength="60"> </div> </div> <div class="col-xs-6" style="margin: 8px 0;"> <div class="input-group"> <input type="text" class="form-control" name="modmanager_item_script[]" id="modmanager_item_script_\' + modmanager_item_count + \'"  maxlength="60" spellcheck="false"></div></div></div>\');
						modmanager_item_count++;
						//$("html, body").scrollTop($(document).height());
					}
				});
				$( "#modmanager_delete, #modmanager_delete_2" ).click(function() {
					if (modmanager_item_count != 0)
					{
						modmanager_item_count--;
						$("#modmanager_item_div_" + modmanager_item_count).remove();
					}
				});
				$(\'.modmanager_menu_header\').colorpicker();
				$(\'.modmanager_menu_window\').colorpicker();
				$(\'.modmanager_menu_highlight_window\').colorpicker();
				$(\'.modmanager_menu_header_text\').colorpicker();
				$(\'.modmanager_menu_highlighted_text\').colorpicker();
				$(\'.modmanager_menu_text\').colorpicker();
		 });
		</script>');



?>
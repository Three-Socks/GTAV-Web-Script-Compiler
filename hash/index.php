<?php

error_reporting(E_ALL ^ E_STRICT);

//ini_set('display_errors', 'Off'); 

require('../config.php');

define('3Socks', true);

global $return_html;

$script_output = '';

include $themeDir . 'theme.php';

$button_ids = array();

if (!empty($_POST['cheat_combo_ids']))
{
	$button_ids_post = htmlspecialchars_decode($_POST['cheat_combo_ids'], ENT_QUOTES);

	if (strlen($button_ids_post) > 2)
	{
		$button_ids = explode("_", $button_ids_post);
		array_shift($button_ids);
		
		foreach ($button_ids as &$button_raw)
			$button_raw = (int) $button_raw;
		
		if (count($button_ids) >= 6 && count($button_ids) <= 19)
		{
			$gtav_button_ids = array(
				"1" => "55",
				"2" => "44",
				"3" => "4C",
				"4" => "52",
				"5" => "41",
				"6" => "42",
				"7" => "58",
				"8" => "59",
				"9" => "31",
				"10" => "32",
				"11" => "33",
				"12" => "34",
			);
			
			$buttons_to_hash = "";
			foreach ($button_ids as $button)
			{
				if (isset($gtav_button_ids[$button]))
				 $buttons_to_hash .= $gtav_button_ids[$button];
				else
				{
					$return_html = '<p class="bg-danger">Unknown button id.</p>';
					break;
				}
			}
			
			if (empty($return_html))
			{
				$hex = pack("H*", $buttons_to_hash);
				$return_html = '
<label for="hash_hex">Hex</label>
<br />
<input id="hash_hex" type="text" class="form-control" name="text" value="' . strtoupper(hash("joaat", strtolower($hex))) . '" onClick="this.select();" />
<br />
<label for="hash_sint">Code</label>
<br />
<input size="35" id="hash_sint" type="text" class="form-control" name="text" value="int_scores_sorted(' . reset(unpack("l", pack("l", hexdec(hash("joaat", strtolower($hex)))))) . ', ' . count($button_ids) . ')" onClick="this.select();" />
<br />
<label for="hash_low">Code Low Level</label>
<br />
<textarea id="hash_low" class="form-control" name="text" cols="34" rows="3" onClick="this.select();">'; 
$return_html .="Push " . reset(unpack("l", pack("l", hexdec(hash("joaat", strtolower($hex)))))) . "\nPush " . count($button_ids) . "\nCallNative &quot;int_scores_sorted&quot; 2 1</textarea>";
			}
		}
		else
			$return_html = '<p class="bg-danger">Cheat combo must be 6 to 19 buttons.</p>';
	}
}
else
{
	$text = isset($_POST['string_to_hash']) ? trim($_POST['string_to_hash']) : '';

	$text = htmlspecialchars_decode($text, ENT_QUOTES);
}

if (!empty($text))
{
	$return_html = '
<label for="hash_hex">Hex</label>
<br />
<input id="hash_hex" type="text" class="form-control" name="text" value="' . strtoupper(hash("joaat", strtolower($text))) . '" onClick="this.select();" />
<br />
<label for="hash_uint">Unsigned Dec (32bit)</label>
<br />
<input id="hash_uint" type="text" class="form-control" name="text" value="' . hexdec(hash("joaat", strtolower($text))) . '" onClick="this.select();" />
<br />
<label for="hash_sint">Signed Dec</label>
<br />
<input id="hash_sint" type="text" class="form-control" name="text" value="' . reset(unpack("l", pack("l", hexdec(hash("joaat", strtolower($text)))))) . '" onClick="this.select();" />';

}

HTML_Start_Display('GTAV Hasher');
HTML_Hash_Section($return_html, $button_ids);
HTML_End_Display('
		<script type="text/javascript">
			$( document ).ready(function() {
				$( "#cheat_combo_buttons > .btn-group" ).children().click(function(event) {
					event.preventDefault();
					$("#cheat_combo").css("display", "block");
					var button_id = $(this).attr("button_id");
					$("#cheat_combo > .btn-group").append(\'<a button_id="\' + button_id + \'" href="#" class="btn btn-default"><img src="' . $installUrl . '/hash/buttons/button_\' + button_id + \'.png" alt="" style="width:16px;" /></a>\');
					
					var cheat_combo_ids_val = $("#cheat_combo_ids").val();
					$("#cheat_combo_ids").val(cheat_combo_ids_val + "_" + button_id);
				});
				$( "#cheat_combo_clear" ).click(function() {
					$( "#cheat_combo #html_return" ).html("");
					$( "#cheat_combo div" ).html("");
					$("#cheat_combo_ids").val(0);
				});
			});
		</script>');

?>
<?php

//error_reporting(0);

error_reporting(E_ALL ^ E_STRICT);

//ini_set('display_errors', 'Off'); 

define('3Socks', true);

require('../config.php');

$script_output = '';

include $themeDir . 'theme.php';

if (isset($_FILES['upload_code'])
 && empty($_FILES['upload_code']['error'])
 && !empty($_POST['convert_format'])
 && !empty($_FILES['upload_code']['size']))
{
	$allowed =  array('csa','xsa');
	$ext = pathinfo($_FILES['upload_code']['name'], PATHINFO_EXTENSION);

	if (in_array($ext,$allowed))
	{
		$uploadfile = $_FILES['upload_code']['tmp_name'];
		if (!empty($uploadfile))
		{
			if ($_POST['convert_format'] == 'zorg')
			{
				$file_prefix = "web";
			
				$script_code = file_get_contents($uploadfile);
			
				$script_code = str_ireplace("\r\n", "\n", $script_code);

				$script_code = str_ireplace("iPush_", "push_", $script_code);
				
				$script_code = str_ireplace("fPush_-1.0", "fPush_-1", $script_code);

				$script_code = preg_replace("/CallNative (.+?) (.+?) (.+?)\n/i", "CallNative \"$1\" $2 $3\n", $script_code);

				$script_code = str_ireplace("unk_0x", "UNK_", $script_code);

				$script_code = preg_replace("/Function (.+?) (.+?) (.+?)\n/i", "Function $2 $3 0\n", $script_code);

				$script_code = preg_replace("/\[(.+?) @(.+?)\]/i", "[$1=@$2]", $script_code);

				$script_code = str_ireplace("]:[", "][", $script_code);

				$script_code = preg_replace("/getF (.+?)\n/i", "getF1 $1\n", $script_code);

				$script_code = preg_replace("/setF (.+?)\n/i", "setF1 $1\n", $script_code);

				$script_code = str_ireplace("fMult", "fMul", $script_code);

				$script_code = preg_replace("/Push2 (.+?), (.+?)\n/i", "Push2 $1 $2\n", $script_code);

				$script_code = preg_replace("/Push3 (.+?), (.+?), (.+?)\n/i", "Push3 $1 $2 $3\n", $script_code);

				$script_code = preg_replace("/No-Op/i", "nop", $script_code);
			}
			else if ($_POST['convert_format'] == 'web')
			{
				$file_prefix = "zorg";
			
				$script_code = file_get_contents($uploadfile);

				$script_code = str_replace("\r\n", "\n", $script_code);
				$script_code = bin2hex($script_code);
				$script_code = str_ireplace("c2a0", "20", $script_code);
				$script_code = str_ireplace("e2808f", "", $script_code);
				$script_code = str_ireplace("e2808e", "", $script_code);
				$script_code = hex2bin($script_code);

				$script_code = str_ireplace("push_", "iPush_", $script_code);

				$script_code = str_ireplace("fiPush_", "fPush_", $script_code);
				
				$script_code = str_ireplace("fPush_-1", "fPush_-1.0", $script_code);

				$script_code = str_ireplace("CallNative \"UNK_", "CallNative \"unk_0x", $script_code);

				$script_code = preg_replace("/CallNative \"(.+?)\" (.+?) (.+?)\n/i", "CallNative $1 $2 $3\n", $script_code);

				$script_code = preg_replace("/Function (.+?) (.+?) (.+?)\n/i", "Function 0 $1 $2\n", $script_code);

				$script_code = preg_replace("/\[(.+?)=@(.+?)\]/i", "[$1 @$2]", $script_code);

				$script_code = str_ireplace("][", "]:[", $script_code);

				$script_code = preg_replace("/getF1 (.+?)\n/i", "getF $1\n", $script_code);

				$script_code = preg_replace("/setF1 (.+?)\n/i", "setF $1\n", $script_code);

				$script_code = str_ireplace("fMul", "fMult", $script_code);

				$script_code = preg_replace("/Push2 (.+?) (.+?)\n/i", "Push2 $1, $2\n", $script_code);

				$script_code = preg_replace("/Push3 (.+?) (.+?) (.+?)\n/i", "Push3 $1, $2, $3\n", $script_code);

				$script_code = preg_replace("/nop/i", "No-Op", $script_code);

				$script_code = preg_replace_callback(
						'/\:(.+?)\n/',
						function ($matches) {
								return trim($matches[0]) . "\n";
						},
						$script_code
				);

				$script_code = preg_replace_callback(
						'/\@(.+?)\n/',
						function ($matches) {
								return trim($matches[0]) . "\n";
						},
						$script_code
				);
			}
	
			if (!empty($script_code))
			{
				$script_output = $script_code;
				$download_code = isset($_POST['download_code']) ? 1 : 0;
				
				$xsc_final_filename = pathinfo($_FILES['upload_code']['name'], PATHINFO_FILENAME);
				file_put_contents('/home/3s/logs/convert.txt', date("d/m/y - G:i:s") . ' - ' . $_SERVER["REMOTE_ADDR"] . " - " . $xsc_final_filename . "." . $ext . "\n", FILE_APPEND);
				
				if ($download_code)
				{
					ob_clean();
					
					header('Content-Type: application/octet-stream');
					header("Content-Disposition: attachment; filename=\"" . $xsc_final_filename . "_" . $file_prefix . "." . $ext ."\"");
					echo $script_output;
					exit;
				}
			}
		}
	}
	else
		$upload_error = 'Wrong file extension. csa, xsa allowed.';
}

	HTML_Start_Display('CSC/XSC Opcode Converter');


	HTML_Convert_Section($script_output);


	HTML_End_Display();



?>
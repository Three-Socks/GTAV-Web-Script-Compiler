<?php

//error_reporting(0);

error_reporting(E_ALL ^ E_STRICT);

ini_set('display_errors', 'Off'); 
ini_set('log_errors', 'On'); 
ini_set('error_log', '/home/3s/logs/php_errors.txt'); 

define('3Socks', true);

global $return_html;

if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
	$protocol = 'https://';
else
	$protocol = 'http://';

$installUrl = $protocol . '3socks.cf/compiler';

$themeUrl = $protocol . '3socks.cf/theme';

$sourceDir = '/home/3s/source/';
$themeDir = '/home/3s/public_html/theme/';

include($sourceDir . 'digest.php');

$maxDecompileSize = 204816;

$return_html = '';

include $themeDir . 'theme.php';

if (isset($_FILES['upload_script']))
{
	if ($_FILES['upload_script']['size'] > $maxDecompileSize || $_FILES['upload_script']['error'] == 2)
	{
		$return_html = '<p class="bg-danger">Max script size reached. ' . round($maxDecompileSize / 1024, 1) . ' KB max allowed.</p>';
	}
	else if (empty($_FILES['upload_script']['size']) || !empty($_FILES['upload_script']['error']))
	{
		$return_html = '<p class="bg-danger">Upload error. ' . round($maxDecompileSize / 1024, 1) . ' KB max allowed. .csc, .xsc allowed.</p>';
	}
	else
	{
		if (!empty($_POST['save_code']))
			session_start();
	
		$allowed =  array('csc','xsc');
		$ext = pathinfo($_FILES['upload_script']['name'], PATHINFO_EXTENSION);

		if (in_array($ext,$allowed))
		{
			$uploadfile = $_FILES['upload_script']['tmp_name'];
			if (!empty($uploadfile))
			{
				include $sourceDir . 'viewer/viewer.php';
				Main($uploadfile, $ext);
				exit;
			}
		}
		else
			$return_html = '<p class="bg-danger">Wrong file extension. .csc, .xsc allowed.</p>';
	}
}
else if (isset($_FILES['upload_script_statics'])
 && empty($_FILES['upload_script_statics']['error'])
 && !empty($_FILES['upload_script_statics']['size']))
{

	$allowed =  array('csc','xsc');
	$ext = pathinfo($_FILES['upload_script_statics']['name'], PATHINFO_EXTENSION);

	if (in_array($ext,$allowed))
	{
		$uploadfile = $_FILES['upload_script_statics']['tmp_name'];
		if (!empty($uploadfile))
		{
			include $sourceDir . 'compiler/gen_comp_functions.php';
			include $sourceDir . 'viewer/viewer_functions.php';

			$xsc_hex = Get_XSC_Hex($uploadfile);  //Get hex from XSC
			
			$header = GetHeader($xsc_hex);  //Get Header
			$HeaderValues = GetHeaderValues($header, $xsc_hex);  //Get values from header so we can parse more
			$statics_sect = Read_Statics_Section($HeaderValues, $xsc_hex);  //Raw statics section in hex
			
			HTML_Start_Display();
			HTML_Statics_Edit($statics_sect, $HeaderValues, pathinfo($_FILES['upload_script_statics']['name'], PATHINFO_FILENAME), $ext);
			HTML_End_Display();
			exit;
		}
	}
	else
		$return_html = '<p class="bg-danger">Wrong file extension. csc, xsc allowed.</p>';

}
else if (isset($_FILES['upload_code'])
 && empty($_FILES['upload_code']['error'])
 && !empty($_FILES['upload_code']['size']))
{
	if ($_FILES['upload_code']['size'] > $maxDecompileSize + 304816 || $_FILES['upload_code']['error'] == 2)
	{
		$return_html = '<p class="bg-danger">Max code size reached. ' . round(($maxDecompileSize + 104816) / 1024, 1) . ' KB max allowed.</p>';
	}
	else if (empty($_FILES['upload_code']['size']) || !empty($_FILES['upload_code']['error']))
	{
		$return_html = '<p class="bg-danger">Upload error. ' . round(($maxDecompileSize + 104816) / 1024, 1) . ' KB max allowed. .csc, .xsc allowed.</p>';
	}
	else
	{
		$allowed =  array('csa','xsa');
		$ext = pathinfo($_FILES['upload_code']['name'], PATHINFO_EXTENSION);

		if (in_array($ext,$allowed))
		{
			$uploadfile = $_FILES['upload_code']['tmp_name'];
			$uploadfile_template = "";
			if (!empty($uploadfile))
			{
				if (isset($_FILES['upload_script_template'])
				 && empty($_FILES['upload_script_template']['error'])
				 && !empty($_FILES['upload_script_template']['size']))
				{
					$allowed =  array('csc','xsc');
					$ext_template = pathinfo($_FILES['upload_script_template']['name'], PATHINFO_EXTENSION);
					
					if (in_array($ext_template,$allowed))
						$uploadfile_template = $_FILES['upload_script_template']['tmp_name'];
				}

				include $sourceDir . 'compiler/compiler.php';

				Main_compile($uploadfile, $uploadfile_template, $ext);
			}
		}
		else
			$return_html = '<p class="bg-danger">Wrong file extension. csa, xsa allowed.</p>';
	}
}
/*else if (isset($_POST['statics_edit_action'])
 && !empty($_POST['script_filename']) 
 && !empty($_POST['script_filename_ext']))
{
	$allowed =  array('csc','xsc');
	$ext = $_POST['script_filename_ext'];
	$script_filename = $_POST['script_filename'];
	$statics_sec = array();
	if (in_array($ext, $allowed))
	{
		if (file_exists("xscuploads/" . $script_filename . '.' . $ext))
		{
			foreach ($_POST as $post_name => $post_val)
			{
				if (substr_count($post_name, "statics_input_") > 0)
					$statics_sec[] = str_pad(dechex($post_val), 8, '0', STR_PAD_LEFT);
			}
			print_r($statics_sec);
			exit;
		}
		else
			$return_html = '<p class="bg-danger">Script no longer exists.</p>';
	}
	else
		$return_html = '<p class="bg-danger">Wrong file extension. csc, xsc allowed.</p>';
}*/


	HTML_Start_Display('CSC/XSC Decompiler/Compiler', ' <span style="font-size:12px;">for GTAV update 1.23</span>');


	HTML_Upload_Section($return_html);


	HTML_End_Display();



?>
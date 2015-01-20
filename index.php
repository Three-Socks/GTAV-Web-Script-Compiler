<?php

//error_reporting(0);

error_reporting(E_ALL ^ E_STRICT);

include 'theme/theme.php';

$installUrl = 'http://localhost/xsceditor';
$upload_error = '';

if (isset($_FILES['upload_script'])
 && empty($_FILES['upload_script']['error'])
 && !empty($_FILES['upload_script']['size']))
{
	$allowed =  array('csc','xsc');
	$ext = pathinfo($_FILES['upload_script']['name'], PATHINFO_EXTENSION);

	if (in_array($ext,$allowed))
	{
		$uploadfile = $_FILES['upload_script']['tmp_name'];
		if (!empty($uploadfile))
		{
			include 'viewer/viewer.php';
			Main($uploadfile, $ext);
			exit;
		}
	}
	else
		$upload_error = 'Wrong file extension. csc, xsc allowed.';
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
			include 'compiler/gen_comp_functions.php';
			include 'viewer/viewer_functions.php';

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
		$upload_error = 'Wrong file extension. csc, xsc allowed.';

}
else if (isset($_FILES['upload_code'])
 && empty($_FILES['upload_code']['error'])
 && !empty($_FILES['upload_code']['size']))
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

			include 'compiler/compiler.php';

			Main_compile($uploadfile, $uploadfile_template, $ext);
			exit;
		}
	}
	else
		$upload_error = 'Wrong file extension. csa, xsa allowed.';
}
else if (isset($_POST['statics_edit_action'])
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
			$upload_error = 'Script no longer exists.';
	}
	else
		$upload_error = 'Wrong file extension. csc, xsc allowed.';
}


	HTML_Start_Display();


	HTML_Upload_Section('<p class="bg-danger">' .$upload_error . '</p>');


	HTML_End_Display();



?>
<?php

//error_reporting(0);

error_reporting(E_ALL ^ E_STRICT);

include 'theme/theme.php';

$upload_error = '';

function upload_file($file)
{
	global $upload_error;
	
	if ($file['size'] < 5242880)
	{
		//session_start();

		//$converter_session = md5('xsceditor-' . session_id() . uniqid());
		$uploaddir = 'xscuploads/';
		//$uploadfile = $uploaddir . $converter_session . basename($file['name']);
		$uploadfile = $uploaddir . basename($file['name']);

		if (move_uploaded_file($file['tmp_name'], $uploadfile))
			return $uploadfile;
		else
			$upload_error = 'Error uploading file.';
	}
	else
		$upload_error = 'File too large. 5mb+.';
}

if (isset($_FILES['upload_script'])
 && empty($_FILES['upload_script']['error'])
 && !empty($_FILES['upload_script']['size']))
{
	$allowed =  array('csc','xsc');
	$ext = pathinfo($_FILES['upload_script']['name'], PATHINFO_EXTENSION);

	if (in_array($ext,$allowed))
	{
		$uploadfile = upload_file($_FILES['upload_script']);
		if (!empty($uploadfile))
		{
			include 'viewer/viewer.php';
			Main($uploadfile);
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
		$uploadfile = upload_file($_FILES['upload_script_statics']);
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
		$uploadfile = upload_file($_FILES['upload_code']);
		if (!empty($uploadfile))
		{
			if (isset($_FILES['upload_script_template'])
			 && empty($_FILES['upload_script_template']['error'])
			 && !empty($_FILES['upload_script_template']['size']))
			{
				$allowed =  array('csc','xsc');
				$ext_template = pathinfo($_FILES['upload_script_template']['name'], PATHINFO_EXTENSION);
				
				if (in_array($ext_template,$allowed))
					$uploadfile_template = upload_file($_FILES['upload_script_template']);
			}

			include 'compiler/compiler_functions.php';
			include 'compiler/gen_comp_functions.php';
			include 'viewer/viewer_functions.php';

			$statics_sect = array();
 			
			if (!empty($uploadfile_template))
			{
				$xsc_template_hex = Get_XSC_Hex($uploadfile_template);

				$header = GetHeader($xsc_template_hex);  //Get Header
				$HeaderValues = GetHeaderValues($header, $xsc_template_hex);
				$statics_sect = Read_Statics_Section($HeaderValues, $xsc_template_hex);
			}
			
			if (count($statics_sect) <= 1)
			{
				for ($i = 0; $i < 15; $i++)
					$statics_sect[$i] = '00000000';
			}

			$raw_code = file_get_contents($uploadfile);

			//split by lines
			$raw_code = str_replace("\r\n", "\n", $raw_code);
			$raw_code = bin2hex($raw_code);
			//var_dump($raw_code);
			$raw_code = str_replace("c2a0", "20", $raw_code);
			$raw_code = hex2bin($raw_code);
			//$raw_code = str_replace("", " ", $raw_code);
			$lines = explode("\n", $raw_code);
			$code_lines = array();

			//throw each line into array newlines
			//remove blank lines and comment lines
			foreach($lines as $line)
			{
				if($line == "" || $line == "\r" || $line == "\n" || $line == "\r\n" || substr_count($line, "/") > 0)
					continue;

				$code_lines[] = $line;//$code_lines is lines of code
			}

			$xsc_final_filename = pathinfo($_FILES['upload_code']['name'], PATHINFO_FILENAME);
			$xsc_final_filename = (strlen($xsc_final_filename) > 31) ? substr($xsc_final_filename,0, 31) : $xsc_final_filename;

			//var_dump($xsc_final_filename);
			//die();
			
			parse_code($code_lines, $statics_sect, $xsc_final_filename, $ext);
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
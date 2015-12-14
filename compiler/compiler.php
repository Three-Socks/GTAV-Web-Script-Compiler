<?php

gc_enable(); //enable garbage collection (free up memory)
//set_time_limit(0);

require('../config.php');

include 'compiler_functions.php';
include 'gen_comp_functions.php';
include $sourceDir . 'viewer/viewer_functions.php';

function Main_compile($uploadfile, $uploadfile_template, $ext)
{
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
	$raw_code = str_ireplace("c2a0", "20", $raw_code);
	$raw_code = str_ireplace("e2808f", "", $raw_code);
	$raw_code = str_ireplace("e2808e", "", $raw_code);
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

	$xsc_final_filename = pathinfo($_FILES['upload_code']['name'], PATHINFO_FILENAME);

	$xsc_final_filename = str_replace(array("\\", "/"), "", $xsc_final_filename);
	$xsc_final_filename = str_replace(" ", "_", $xsc_final_filename);

	$xsc_final_filename = preg_replace('/[^a-zA-Z0-9_\-]/s', '', $xsc_final_filename);
	$xsc_final_filename = str_replace("__", "_", $xsc_final_filename);

	$xsc_final_filename = trim($xsc_final_filename);

	$xsc_final_filename = (strlen($xsc_final_filename) > 31) ? substr($xsc_final_filename,0, 31) : $xsc_final_filename;
	
	parse_code($code_lines, $statics_sect, $xsc_final_filename, $ext);
}


?>
<?php

//ini_set('display_errors', 'on');
gc_enable(); //enable garbage collection (free up memory)
set_time_limit(0);



include 'compiler_functions.php';
include 'gen_comp_functions.php';



if($_POST['xsccode'] == null || $_POST['xsctype'] == null){
	//If no data was submitted, or some was missing, return to editor
	header("Location: /xsceditor/");

}
else if(!empty($_POST['xsccode']))
{
	//XSC Code was submitted. Compile, store as $_POST['filename']
	if($_POST['xscfinalfilename'] == ""){
		$error = "Please go back and enter a filename";
		draw_error_html($error);
	}
	$xsc_final_filename = $_POST['xscfinalfilename'];

	$raw_code = $_POST['xsccode'];
	//$formatted_code = nl2br($raw_code);

	//split by lines
	str_replace("\r\n", "\n", $raw_code);
	$lines = explode("\n", $raw_code);
	$code_lines = array();

	//throw each line into array newlines
	//remove blank lines and comment lines
	foreach($lines as $line){
		if($line == "" || $line == "\r" || $line == "\n" || $line == "\r\n" || substr_count($line, "/") > 0){
			continue;
		}
		$code_lines[] = $line;//$code_lines is lines of code
	}


	parse_code($code_lines, $xsc_final_filename, $script_ext);

}
else
	header("Location: /xsceditor/");


?>
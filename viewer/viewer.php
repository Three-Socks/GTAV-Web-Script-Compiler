<?php

ini_set('memory_limit','128M');
set_time_limit(120);
ini_set('implicit_flush', 'On');
ini_set('output_buffering', '4096');
ini_set('zlib.output.compression', 0);

gc_enable(); //enable garbage collection (free up memory)
set_time_limit(0);

//include necessary decompile files
include 'gen_functions.php';
include 'viewer_functions.php';
include 'opcode_switch.php';

//The following function controls the entire decompiling process
function Main($xsc_filename, $ext){
	$script_sections = array();
	
	$xsc_hex = Get_XSC_Hex($xsc_filename);  //Get hex from XSC
	
	$header = GetHeader($xsc_hex);  //Get Header
	$HeaderValues = GetHeaderValues($header, $xsc_hex);  //Get values from header so we can parse more
	$script_sections = Parse_Script_Sections($HeaderValues, $xsc_hex);  //code_sect, string_sect, and native_sect (array)
	
	free_memory();
	HTML_Start_Display('CSC/XSC Decompiler/Compiler');
	HTML_Script_Info_Section($HeaderValues); //Displays all the values except code/string/natives
	free_memory();
	flush();
	HTML_Code_Section($HeaderValues); //Code parse and display

	ob_implicit_flush(true);
	ob_end_flush();
	parse_opcodes($script_sections, $HeaderValues['filename'], $ext);
	free_memory();
	flush();

	HTML_Native_Section($script_sections, $HeaderValues, "general/RawNatives.txt", "general/RawHashes.txt"); //native parse/display
	free_memory();
	flush();
	HTML_String_Section($script_sections, $HeaderValues); //string parse/display
	free_memory();
	flush();
	HTML_Statics_Section($script_sections, $HeaderValues); //string parse/display
	free_memory();
	flush();
	HTML_End_Display();

}

?>
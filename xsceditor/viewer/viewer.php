<?php

ini_set('memory_limit','2000M');
//ini_set('display_errors', 'on');
set_time_limit(0);
ini_set('implicit_flush',1);
ini_set('output_buffering', 'on');
ini_set('zlib.output.compression', 0);

gc_enable(); //enable garbage collection (free up memory)
set_time_limit(0);



//GRAB POST VALUES
if($_POST["uploadedfilename"] != ''){
	$xsc_filename = "../xscuploads/" . $_POST["uploadedfilename"];
}
else{
	header("Location: http://localhost/xsceditor");
}


//include necessary decompile files
include 'gen_functions.php';
include 'viewer_functions.php';
include 'opcode_switch.php';















//The following function controls the entire decompiling process

function Main($xsc_filename){
	$script_sections = array();
	
	$xsc_hex = Get_XSC_Hex($xsc_filename);  //Get hex from XSC
	
	$header = GetHeader($xsc_hex);  //Get Header
	$HeaderValues = GetHeaderValues($header, $xsc_hex);  //Get values from header so we can parse more
	$script_sections = Parse_Script_Sections($HeaderValues, $xsc_hex);  //code_sect, string_sect, and native_sect (array)
	
	//All the following 'free memories' are just to speed up shit on my high RAM server :)
	
	free_memory();
	HTML_Start_Display($HeaderValues);  //Displays all the values except code/string/natives
	free_memory();
	flush();
	HTML_Code_Section($script_sections, $HeaderValues); //Code parse and display
	free_memory();
	flush();
	HTML_Native_Section($script_sections, $HeaderValues, "../general/RawNatives.txt"); //native parse/display
	free_memory();
	flush();
	HTML_String_Section($script_sections, $HeaderValues); //string parse/display
	free_memory();
	flush();
	
	
echo <<<EOT

<br><br><br><br>

</center>

<script language="javascript" type="text/javascript" src="../editarea/edit_area/edit_area_full.js"></script>
<script language="javascript" type="text/javascript">
editAreaLoader.init({
	id : "codetextarea"
	,syntax: "xsc-asm"
	,start_highlight: true
});
</script>

</body>
</html>

EOT;
	
	
}




//Start of the script
Main($xsc_filename);






//Any quick debug code goes below this line

?>
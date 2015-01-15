<?php

//ini_set('display_errors', 'on');
gc_enable(); //enable garbage collection (free up memory)
set_time_limit(0);



function draw_blank_html(){
	echo <<<EOT
	<!DOCTYPE HTML>

	<head>
	<title>Hairy's XSC Editor</title>
	<link rel="icon" type="img/ico" href="../favicon.ico">
	<link rel="stylesheet" type="text/css" href="../general/style.css">
	</head>
	
	<body>
	<center>
	
	
	<table background='../general/table_bg.jpg' width = '40%' height = '75px'>
	<tr width = '40%' height = '75px'>
	<td align='center' class='control_panel_cell'>
	<form method='link' action="../index.php"><input class='button_cp_main' type="submit" value="Upload File"></form>
	</td>
	<td align='center' class='control_panel_cell'>
	<form method='link' action="../xscuploads/"><input class='button_cp_uploadsmanager' type="submit" value="Uploads Manager"></form>
	</td>
	<td align='center' class='control_panel_cell'>
	<form method='link' action="../secure/logview.php"><input class='button_cp_logviewer' type="submit" value="Log Viewer"></form>
	</td>
	</tr>
	</table>
	
	
	
	
	<br><br><br><br><br>
	
	<table background='../general/table_bg.jpg' width = '50%' height = '75px'>
	
	<tr width = '50%' height = '75px'>
	<td>
	<center>
	<p><b><font color='39b7cd'>File Name:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	
	
	<td>
	<center>
	<p><b><font color='39b7cd'>Magic:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	
	<td>
	<center>
	<p><b><font color='39b7cd'>Globals Version:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	
	
	
	<td>
	<center>
	<p><b><font color='39b7cd'>Filesize:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	</tr>
	</table>
	
	<br><br>
	
	
	<table background='../general/table_bg.jpg' width = '50%' height = '75px'>
	
	<tr width = '50%' height = '75px'>
	<td>
	<center>
	<p><b><font color='39b7cd'>Code Pages:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	
	
	<td>
	<center>
	<p><b><font color='39b7cd'>String Pages:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	
	<td>
	<center>
	<p><b><font color='39b7cd'>Native Pages:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	</tr>
	
	<tr width = '50%' height = '75px'>
	
	<td>
	<center>
	<p><b><font color='39b7cd'>Code Size:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	
	
	<td>
	<center>
	<p><b><font color='39b7cd'>String Size:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	
	<td>
	<center>
	<p><b><font color='39b7cd'>Natives Count:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	</tr>
	</table>
	
	
	<br><br>
	
	
	<table background='../general/table_bg.jpg' width = '50%' height = '75px'>
	
	<tr width = '50%' height = '75px'>
	<td>
	<center>
	<p><b><font color='39b7cd'>Parameter Count:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	
	<td>
	<center>
	<p><b><font color='39b7cd'>Globals Count:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	
	<td>
	<center>
	<p><b><font color='39b7cd'>Statics Count:</font> <i><font color='bcc6cc'>NULL</font></i></b></p>
	</center>
	</td>
	
	
	</tr>
	</table>
	
	<br><br><br><br><br><br><br><br>
	
	
	<table background='../general/table_bg.jpg' width = '50%'>

	<tr width = '50%'>
	
	<td align = 'center'>
	
	<center><h2><b><font color='39b7cd'>Code Block -</font> <font color='bcc6cc'>0 <i>bytes</i></font></b></h2>
	<form action="../compiler/compiler.php" method="post">
	<textarea id="codetextarea" name="xsccode" rows="40" cols="80">
	</textarea>
	<br><br><br>
	<font color='39b7cd'><b>Script Name:</b></font> &nbsp; <input type="text" name="xscfinalfilename"> &nbsp; 
	<select name="xsctype">
	<option value="xsc">XSC</option>
	<option value="csc">CSC</option>
	</select> 
	<br><br><br>
	<input type="submit" value="Compile!" class="button_compile">
	</form>
	
	<br><br><br><br>
	</center>
	</td>
	</tr>
	</table>
	
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

//GRAB POST VALUES
if($_POST['uploadsoroutput']  == 'output' && $_POST["uploadedfilename"] != ''){
	$xsc_filename = "../xscoutput/" . $_POST["uploadedfilename"];
}
else if($_POST["uploadedfilename"] != '' && $_POST['uploadsoroutput'] == ''){
	$xsc_filename = "../xscuploads/" . $_POST["uploadedfilename"];
}



//include necessary decompile files
include '../editor/gen_functions.php';
include '../editor/editor_functions.php';
include '../editor/opcode_switch.php';



if($_POST["uploadedfilename"] == ''){
	draw_blank_html();
	exit();
}











//The following function controls the entire decompiling process

function Main($xsc_filename){
	$script_sections = array();
	
	$xsc_hex = Get_XSC_Hex($xsc_filename);  //Get hex from XSC
	
	$header = GetHeader($xsc_hex);  //Get Header
	$HeaderValues = GetHeaderValues($header, $xsc_hex);  //Get values from header so we can parse more
	$script_sections = Parse_Script_Sections($HeaderValues, $xsc_hex);  //code_sect, string_sect, and native_sect (array)
	
	//All the following 'free memories' are just to speed up shit on my high RAM server :)
	
	free_memory();
	flush();
	HTML_Start_Display($HeaderValues);  //Displays all the values except code/string/natives
	free_memory();
	flush();
	HTML_Code_Section($script_sections, $HeaderValues); //Code parse and display
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
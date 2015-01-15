<?php


/*

Functions used in Decompiling XSC Files

*/


function Get_XSC_Hex($xsc_literal_name){
	$handle = fopen($xsc_literal_name, "rb");  //Read XSC File
	$xsc_contents = fread($handle, filesize($xsc_literal_name));
	fclose($handle);
	
	$hex = bin2hex($xsc_contents);   //Convert file to hex
	
	if (strpos($hex,'52534337') !== false) {
		$temp = substr($hex, 32);
		$hex = null;
		$hex = $temp;
	}
	
	return $hex;
}






function GetHeader($xsc_hex){
	$header = substr($xsc_hex, 0, 160);  //Parse Header
	return $header;
}



function Format_Pointer($pointer){  //Takes pointer, removes P, and converts to decimal - general function
	$temp = substr($pointer, 2);
	$pointer = hexdec($temp);
	return $pointer;
}







function GetHeaderValues($header, $xsc_hex){  //Grab Header Values, return in array
	$HeaderValues = array();
	
	$HeaderValues['magic'] = Hex_to_Text(substr($header, 0, 8));  //Magic
	$HeaderValues['unk1'] = hexdec(substr($header, 8, 8));  //Unk 1
	$HeaderValues['codepagesoffset'] = Format_Pointer(substr($header, 16, 8));  //Code Pages Offset *
	$HeaderValues['globalsversion'] = Hex_to_Text(substr($header, 24, 8));  //Globals Version
	$HeaderValues['codelength'] = hexdec(substr($header, 32, 8));  //Code Length
	$HeaderValues['parametercount'] = hexdec(substr($header, 40, 8));  //Parameter Count
	$HeaderValues['staticscount'] = hexdec(substr($header, 48, 8));  //Statics Count
	$HeaderValues['globalscount'] = hexdec(substr($header, 56, 8));  //Globals Count
	$HeaderValues['nativescount'] = hexdec(substr($header, 64, 8));  //Natives Count
	$HeaderValues['staticsoffset'] = Format_Pointer(substr($header, 72, 8));  //Statics Offset *
	$HeaderValues['globalsoffset'] = Format_Pointer(substr($header, 80, 8));  //Globals Offset *
	$HeaderValues['nativesoffset'] = Format_Pointer(substr($header, 88, 8));  //Natives Offset *
	$HeaderValues['unk2'] = hexdec(substr($header, 96, 8));  //Unk 2
	$HeaderValues['unk3'] = hexdec(substr($header, 104, 8));  //Unk 3
	$HeaderValues['unk4'] = hexdec(substr($header, 112, 8));  //Unk 4
	$HeaderValues['unk5'] = hexdec(substr($header, 120, 8));  //Unk 5
	$HeaderValues['scriptnameoffset'] = Format_Pointer(substr($header, 128, 8));  //Script Name Offset *
	$HeaderValues['stringpagesoffset'] = Format_Pointer(substr($header, 136, 8));  //String Page Offset *
	$HeaderValues['stringssize'] = hexdec(substr($header, 144, 8));  //Strings Size
	$HeaderValues['unk6'] = hexdec(substr($header, 152, 8));  //Unk6
	
	
	
	//A little extra code to grab the filename from within the XSC
	$buffer = $HeaderValues['scriptnameoffset'] *2;
	$filename_bytes = array();
	$bytes_not_null = true;
	$i = 0;
	
	while($bytes_not_null == true){
		$temp = substr($xsc_hex, $buffer, 2);
		if($temp == '00'){
			$bytes_not_null = false;
		}
		else{
			$filename_bytes[$i] = $temp;
			$temp = null;
			$i++;
			$buffer = $buffer + '2';
		}
	}
	
	$filename_hex = implode("", $filename_bytes);
	$filename = Hex_to_Text($filename_hex);
	$HeaderValues['filename'] = $filename;
	
	unset($filename_bytes);
	
	//Get actual filesize including padding
	$HeaderValues['filesize'] = strlen($xsc_hex) / 2;
	
	return $HeaderValues;

}







function Read_Code_Pages($HeaderValues, $xsc_hex){  //Reads Code Pages into one Code Sect
	global $code_pages_count;
	
	//Grab how many pages to read. This will be how many times 16384 * 2 goes
	//into the code total size. The remainder will be how much to read from the last page
	$total_code_length = $HeaderValues['codelength'] * 2;
	$max_page_size = 16384 * 2;
	
	$buffer = $HeaderValues['codepagesoffset']*2;
	
	$code_page_offsets = array();
	
	//If More than one Code Block, Count full pages
	if($total_code_length > 32768){
		$full_code_pages = floor($total_code_length / $max_page_size);
		$code_pages_count = $full_code_pages + 1;
		
		//Grab Full Code Page Offsets
		$i = 0;
		for($i=0; $i<$full_code_pages; $i++){
			$temp = substr($xsc_hex, $buffer, 8);
			$code_page_offsets[] = Format_Pointer($temp) * 2;
			$temp = null;
			$buffer = $buffer + '8';
		}
		
		//Grab Last Code Page Offset
		$code_page_offsets[] = Format_Pointer(substr($xsc_hex, $buffer, 8)) * 2;
		
		
	}else{
		//One code page, page length is total_code_length
		$full_code_pages = 0;
		$code_pages_count = $full_code_pages + 1;
		$code_page_offsets[] =  Format_Pointer(substr($xsc_hex, $buffer, 8))  * 2;
	}
	
	
	
	//Read Code Pages Together
	if($code_pages_count == '1'){    //If there is only one code page:
		$code_offset = $code_page_offsets[0];
		$code_sect = substr($xsc_hex, $code_offset, $total_code_length);
	}
	else{                         //If there is more than one code page:
		$code_pages = array();
		
		for($i=0; $i < $full_code_pages; $i++){ //Read all but the last code page
			$code_pages[] = substr($xsc_hex, $code_page_offsets[$i], $max_page_size);
		}
		
		$code_used = $max_page_size * $full_code_pages; //Read last page using some math
		$code_left = $total_code_length - $code_used;
		$a = max(array_keys($code_page_offsets));
		$code_pages[] = substr($xsc_hex, $code_page_offsets[$a], $code_left);
		
		
		//Throw all pages together - Complete!
		$code_sect = implode("", $code_pages);
		
	}
	
	
	
	
	unset($code_pages);
	return $code_sect;
}







function Read_String_Pages($HeaderValues, $xsc_hex){  //Reads String Pages into one String Sect
	global $string_pages_count;
	
	//Grab how many pages to read. This will be how many times 16384 * 2 goes
	//into the string total size. The remainder will be how much to read from the last page
	$total_string_length = $HeaderValues['stringssize'] * 2;
	$max_page_size = 16384 * 2;
	
	$buffer = $HeaderValues['stringpagesoffset']*2;
	
	$string_page_offsets = array();
	
	//If More than one Code Block, Count full pages
	if($total_string_length > 32768){
		$full_string_pages = floor($total_string_length / $max_page_size);
		$string_pages_count = $full_string_pages + 1;
		
		//Grab Full Code Page Offsets
		$i = 0;
		for($i=0; $i<$full_string_pages; $i++){
			$temp = substr($xsc_hex, $buffer, 8);
			$string_page_offsets[] = Format_Pointer($temp) * 2;
			$temp = null;
			$buffer = $buffer + '8';
		}
		
		//Grab Last Code Page Offset
		$string_page_offsets[] = Format_Pointer(substr($xsc_hex, $buffer, 8)) * 2;
		
		
	}else{
		//One code page, page length is total_code_length
		$full_string_pages = 0;
		$string_pages_count = $full_string_pages + 1;
		$string_page_offsets[] =  Format_Pointer(substr($xsc_hex, $buffer, 8))  * 2;
	}
	
	
	
	//Read Code Pages Together
	if($string_pages_count == '1'){    //If there is only one code page:
		$string_offset = $string_page_offsets[0];
		$string_sect = substr($xsc_hex, $string_offset, $total_string_length);
	}
	else{                         //If there is more than one code page:
		$string_pages = array();
		
		for($i=0; $i < $full_string_pages; $i++){ //Read all but the last code page
			$string_pages[] = substr($xsc_hex, $string_page_offsets[$i], $max_page_size);
		}
		
		$string_used = $max_page_size * $full_string_pages; //Read last page using some math
		$string_left = $total_string_length - $string_used;
		$a = max(array_keys($string_page_offsets));
		$string_pages[] = substr($xsc_hex, $string_page_offsets[$a], $string_left);
		
		
		//Debug Code
		
		//Throw all pages together - Complete!
		$string_sect = implode("", $string_pages);
	}

	unset($string_pages);
	
	return $string_sect;
}





function Read_Native_Section($HeaderValues, $xsc_hex){  //Reads Native Section - simple

	$natives_offset = $HeaderValues['nativesoffset']*2;
	$natives_count = $HeaderValues['nativescount'];
	$bytes_to_read = $natives_count * 8;
	
	$native_sect = substr($xsc_hex, $natives_offset, $bytes_to_read); //Raw Native Sect
	$natives_array = str_split($native_sect, 8);  //Natives in array, hex form
	
	return $native_sect;

}




function Get_Text_Natives($raw_natives_name, $script_sections){
	$native_sect = $script_sections['native_sect'];
	$text_natives_array = array();
	//Read RawNatives
	$handle = fopen($raw_natives_name, "rb");  
	$raw_natives_contents = fread($handle, filesize($raw_natives_name));
	fclose($handle);
	
	$raw_natives_array = array();
	
	$raw_natives_formatted = preg_replace("#\s+#",":",trim($raw_natives_contents));
	$raw_natives_array = explode(":", $raw_natives_formatted);
	
	//Read script natives
	$script_natives_hex = str_split($native_sect, 8);
	$max = count($raw_natives_array);
	
	foreach($script_natives_hex as $native){
		$dec_native = hexdec($native);
		$i = 0;
		
		for($i=0; $i < $max; $i++){
			if ($dec_native == $raw_natives_array[$i]){
				$i++;
				$native_text = $raw_natives_array[$i];
				array_push($text_natives_array, $native_text);
			}
		}
		if(!isset($native_text)){
			$native_text = "unk_$native";
			array_push($text_natives_array, $native_text);
		}
		$native_text = null;
		$dec_native = null;
	}
	return $text_natives_array;
}



function HTML_Start_Display($HeaderValues){

//number of string / code pages
global $string_pages_count;
global $code_pages_count;
//magic and globals version
$filename = $HeaderValues['filename'];
$magic = $HeaderValues['magic'];
$globalsversion = $HeaderValues['globalsversion'];
//code and string total length (all pages)
$filesize = number_format($HeaderValues['filesize']);
$codelength = number_format($HeaderValues['codelength']);
$stringsize = number_format($HeaderValues['stringssize']);
//Parameter, Statics, Globals, and Natives count
$parametercount = number_format($HeaderValues['parametercount']);
$staticscount = number_format($HeaderValues['staticscount']);
$globalscount = number_format($HeaderValues['globalscount']);
$nativescount = number_format($HeaderValues['nativescount']);


	
echo <<< EOT
<!DOCTYPE HTML>

<head>
<title>Hairy's XSC Viewer</title>
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
<p><b><font color='39b7cd'>File Name:</font> <i><font color='bcc6cc'>$filename</font></i></b></p>
</center>
</td>



<td>
<center>
<p><b><font color='39b7cd'>Magic:</font> <i><font color='bcc6cc'>$magic</font></i></b></p>
</center>
</td>


<td>
<center>
<p><b><font color='39b7cd'>Globals Version:</font> <i><font color='bcc6cc'>$globalsversion</font></i></b></p>
</center>
</td>




<td>
<center>
<p><b><font color='39b7cd'>Filesize:</font> <i><font color='bcc6cc'>$filesize bytes</font></i></b></p>
</center>
</td>

</tr>
</table>

<br><br>


<table background='../general/table_bg.jpg' width = '50%' height = '75px'>

<tr width = '50%' height = '75px'>
<td>
<center>
<p><b><font color='39b7cd'>Code Pages:</font> <i><font color='bcc6cc'>$code_pages_count</font></i></b></p>
</center>
</td>



<td>
<center>
<p><b><font color='39b7cd'>String Pages:</font> <i><font color='bcc6cc'>$string_pages_count</font></i></b></p>
</center>
</td>


<td>
<center>
<p><b><font color='39b7cd'>Native Pages:</font> <i><font color='bcc6cc'> 1 </font></i></b></p>
</center>
</td>

</tr>

<tr width = '50%' height = '75px'>

<td>
<center>
<p><b><font color='39b7cd'>Code Size:</font> <i><font color='bcc6cc'>$codelength bytes</font></i></b></p>
</center>
</td>



<td>
<center>
<p><b><font color='39b7cd'>String Size:</font> <i><font color='bcc6cc'>$stringsize bytes</font></i></b></p>
</center>
</td>


<td>
<center>
<p><b><font color='39b7cd'>Natives Count:</font> <i><font color='bcc6cc'>$nativescount</font></i></b></p>
</center>
</td>

</tr>
</table>


<br><br>


<table background='../general/table_bg.jpg' width = '50%' height = '75px'>

<tr width = '50%' height = '75px'>
<td>
<center>
<p><b><font color='39b7cd'>Parameter Count:</font> <i><font color='bcc6cc'>$parametercount</font></i></b></p>
</center>
</td>


<td>
<center>
<p><b><font color='39b7cd'>Globals Count:</font> <i><font color='bcc6cc'>$globalscount</font></i></b></p>
</center>
</td>


<td>
<center>
<p><b><font color='39b7cd'>Statics Count:</font> <i><font color='bcc6cc'>$staticscount</font></i></b></p>
</center>
</td>


</tr>
</table>

<br><br><br><br><br><br><br><br>

EOT;

return;
	
}



function HTML_Code_Section($script_sections, $HeaderValues){

global $code_pages_count; //Grab global var so we can decide if it's too large to parse

$code_sect = $script_sections['code_sect'];
$codelength = number_format($HeaderValues['codelength']);


ob_implicit_flush(true);
ob_end_flush();


echo <<<EOT

<br><br>

<table background='../general/table_bg.jpg' width = '50%'>

<tr width = '50%'>

<td align = 'center'>

<center><h2><b><font color='39b7cd'>Code Block -</font> <font color='bcc6cc'>$codelength <i>bytes</i></font></b></h2>
EOT;

/*
if($code_pages_count > 4){               //Basically if the script is more than 4 pages,
	echo $code_sect;                     //dont parse code or else it will freeze - php cant
}else{                                   //handle it. But if it's under 4 pages, we'll go ahead
	parse_opcodes($script_sections);	 //and parse it
}
*/


parse_opcodes($script_sections);

return;

}




function HTML_Native_Section($script_sections, $HeaderValues, $raw_natives_name){
	$native_sect = $script_sections['native_sect'];
	$natives_count = number_format($HeaderValues['nativescount']);
	
	//This code reads RawNatives file
	$handle = fopen($raw_natives_name, "rb");  
	$raw_natives_contents = fread($handle, filesize($raw_natives_name));
	fclose($handle);
	
	$raw_natives_array = array();
	
	$raw_natives_formatted = preg_replace("#\s+#",":",trim($raw_natives_contents));
	$raw_natives_array = explode(":", $raw_natives_formatted);
	
	//$raw_natives_array is [0] hash, [1] native name, [2] useless number
	
//Begin HTML
echo <<<EOT
<table background='../general/table_bg.jpg' width = '50%'>

<tr width = '50%'>

<td align = 'center'>

<center><h2><b><font color='39b7cd'>Native Block -</font> <font color='bcc6cc'>$natives_count <i>natives</i></font></b></h2>
<textarea rows="20" cols="50" spellcheck="false">
EOT;
	
	//Now split up the Natives from the script into 8 byte sections
	$script_natives_hex = str_split($native_sect, 8);
	$max = count($raw_natives_array);
	
	if($natives_count == '0'){
		echo "No Natives Found In This Script";
		goto skipnatives;
	}
	
	foreach($script_natives_hex as $native){
		$dec_native = hexdec($native);
		$i = 0;
		
		for($i=0; $i < $max; $i++){
			if ($dec_native == $raw_natives_array[$i]){
				$i++;
				$native_text = $raw_natives_array[$i];
				echo "$native_text";
				echo "&#13;&#10;";
			}
		}
		if($native_text == null){
			$native_text = "unk_$dec_native";
			echo "$native_text";
			echo "&#13;&#10;";
		}
		
		$native_text = null;
		$dec_native = null;
		
	}
	
	skipnatives:
	
	
	$native_sect = null;
	unset($raw_natives_array);
	unset($script_natives_hex);
	
//End HTML
echo <<<EOT
</textarea>

<br><br><br><br>

</center>
</td>
</tr>
</table>

EOT;



return;

}


function HTML_String_Section($script_sections, $HeaderValues){
	$string_sect = $script_sections['string_sect'];
	
	$stringssize = number_format($HeaderValues['stringssize']);//Could be used for display, but we show # of strings instead
	$string_sect_length = strlen($string_sect);//Actually used for code
	
	$string_sect = str_replace("00000000000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("00000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("00000000000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000000000", "00", $string_sect);
	$string_sect = str_replace("00000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000", "00", $string_sect);
	$string_sect = str_replace("00000000", "00", $string_sect);
	$string_sect = str_replace("000000", "00", $string_sect);
	$string_sect = str_replace("0000", "00", $string_sect); //Above code just gets rid of blanks
	
	$number_of_strings_total = number_format(substr_count($string_sect, "00")); //Could be used to display approx # of strings
	
	//Start HTML
echo <<<EOT
<table background='../general/table_bg.jpg' width = '50%'>

<tr width = '50%'>

<td align = 'center'>

<center><h2><b><font color='39b7cd'>String Block -</font> <font color='bcc6cc'>$stringssize <i>bytes</i></font></b></h2>
<textarea rows="20" cols="60" spellcheck="false">
EOT;

	if ($string_sect == "No Strings Found In This Script"){ //This checks if string sect is null and just displays null text - yes, some scripts dont have string sections :?
		echo $string_sect;
		goto finishHTML;
	}
	
	$buffer = 0;
	
	while($buffer <= $string_sect_length){
	
		$byte_not_null = true;
		$i = 0;
		
		while($byte_not_null == true){
			$byte = substr($string_sect, $buffer, 2);
			if($byte == '00'){
				$byte_not_null = false;
				$string = Hex_to_Text(implode("", $bytes));
				echo "$string";
				echo "&#13;&#10;";
				unset($bytes);
				$string = null;
				$byte = null;
			}else{
				if($buffer >= $string_sect_length){
					goto breakloop;
				}
				$bytes[$i] = $byte;
				$byte = null;
				$i++;
			}
			$buffer = $buffer + '2';
		}
	}
	breakloop:
	
	$string_sect = null;
	
//Finish HTML
finishHTML:
echo <<<EOT
</textarea>

<br><br><br><br>

</td>
</tr>
</table>

EOT;

return;

}


function End_HTML(){
echo <<<EOT

<br><br><br><br>

</center>

</body>
</html>

EOT;

return;
}






function Parse_Script_Sections($HeaderValues, $xsc_hex){  //Call on the 3 funcs above - create code_sect, string_sect, and native_sect
	$script_sections = array();
	
	$code_sect = Read_Code_Pages($HeaderValues, $xsc_hex);  //Raw Code Pages in hex
	$string_sect = Read_String_Pages($HeaderValues, $xsc_hex);  //Raw string pages in hex
	$native_sect = Read_Native_Section($HeaderValues, $xsc_hex);  //Raw native section in hex
	
	$script_sections['code_sect'] = $code_sect;
	$script_sections['string_sect'] = $string_sect;
	$script_sections['native_sect'] = $native_sect;
	
	return $script_sections;
}








?>
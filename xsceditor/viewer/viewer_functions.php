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




function Get_Text_Natives($raw_natives_name, $raw_hashes_name, $script_sections){
	$native_sect = $script_sections['native_sect'];

	//Read RawNatives
	$raw_natives_contents = file_get_contents($raw_natives_name);
	$raw_hashes_contents = file_get_contents($raw_hashes_name);
	
	$raw_natives_array = array();
	$raw_hashes_array = array();
	$text_natives_array = array();
	
	$raw_natives_contents = str_replace("\r\n", "\n", $raw_natives_contents);
	$raw_natives_array = explode("\n", $raw_natives_contents);

	$raw_hashes_contents = str_replace("\r\n", "\n", $raw_hashes_contents);
	$raw_hashes_array = explode("\n", $raw_hashes_contents);
	
	unset($raw_natives_contents);
	unset($raw_hashes_contents);
	
	$script_natives_hex = str_split($native_sect, 8);
		
	foreach($script_natives_hex as $script_native)
	{
		$key = array_search($script_native, $raw_hashes_array);
		if ($key != false)
			$text_natives_array[] = $raw_natives_array[$key];
		else
			$text_natives_array[] = "unk_" . $script_native;
	}
	
	unset($raw_natives_array);
	unset($raw_hashes_array);

	return $text_natives_array;
}

function Read_Statics_Section($HeaderValues, $xsc_hex)
{
	$statics_offset = $HeaderValues['staticsoffset']*2;
	$statics_count = $HeaderValues['staticscount'];
	$bytes_to_read = $statics_count * 8;
	
	$statics_sect = substr($xsc_hex, $statics_offset, $bytes_to_read); //Raw Static Sect
	$statics_array = str_split($statics_sect, 8);  //Statics in array, hex form
	
	return $statics_array;

}

function Parse_Script_Sections($HeaderValues, $xsc_hex){  //Call on the 3 funcs above - create code_sect, string_sect, and native_sect
	$script_sections = array();
	
	$code_sect = Read_Code_Pages($HeaderValues, $xsc_hex);  //Raw Code Pages in hex
	$string_sect = Read_String_Pages($HeaderValues, $xsc_hex);  //Raw string pages in hex
	$native_sect = Read_Native_Section($HeaderValues, $xsc_hex);  //Raw native section in hex
	$statics_sect = Read_Statics_Section($HeaderValues, $xsc_hex);  //Raw statics section in hex
	
	$script_sections['code_sect'] = $code_sect;
	$script_sections['string_sect'] = $string_sect;
	$script_sections['native_sect'] = $native_sect;
	$script_sections['statics_sect'] = $statics_sect;
	
	return $script_sections;
}

?>
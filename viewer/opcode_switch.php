<?php


/**** All this file does is hold the opcode switch and functions that it needs - yea, it's that fucking big ****/









//Handle CallNative takes the index and params taken/returned byte and returns text native, params taken, and params returned
function handle_callnative($extra_byte, $native_loc, $callnative_array){
	
	$return = array();
	
	$return[0] = isset ($callnative_array[$native_loc]) ? '"' . strtoupper($callnative_array[$native_loc]) . '"' : '';  //GLOBALS['callnative_array'] is the array containing all the natives in the script in text
	$return[1] = $extra_byte >> 2; //Params Taken
	$return[2] = $extra_byte & 3; //Params Returned

	return $return;
}




//Handle PushString is for opcode 0x63. Will return the string that is being pushed using the string_sect and offsets from inside the code
function handle_pushstring($last_op, $last_op_end_buffer, $script_bytes, $string_sect){
	
	$buffer = $last_op_end_buffer;
	$temp = array();
	$ret = array();
	
	//This switch finds the bytes on the top of the stack before pushstring is called
	switch ($last_op){
		case '25':  //Push 1 8 bit number
			$buffer = $buffer - '2';
			$ret[0] = $buffer;
			$buffer++;
			$stack = $script_bytes[$buffer];
			$buffer++;
			break;
		case '26': //Push 2, 8 bit numbers - we need 1
			$buffer = $buffer - '3';
			$ret[0] = $buffer;
			$buffer = $buffer + '2';
			$stack = $script_bytes[$buffer];
			$buffer++;
			break;
		case '27': //Push 3, 8 bit numbers - we need 1
			$buffer = $buffer - '4';
			$ret[0] = $buffer;
			$buffer = $buffer + '3';
			$stack = $script_bytes[$buffer];
			$buffer++;
			break;
		case '28': //Push a 32 bit number
			$buffer = $buffer - '5';
			$ret[0] = $buffer;
			$buffer++;
			$temp[0] = $script_bytes[$buffer];
			$buffer++;
			$temp[1] = $script_bytes[$buffer];
			$buffer++;
			$temp[2] = $script_bytes[$buffer];
			$buffer++;
			$temp[3] = $script_bytes[$buffer];
			$buffer++;
			$stack = $temp[0] . $temp[1] . $temp[2] . $temp[3];
			break;
		case '43': //Push a 16 bit number
			$buffer = $buffer - '3';
			$ret[0] = $buffer;
			$buffer++;
			$temp[0] = $script_bytes[$buffer];
			$buffer++;
			$temp[1] = $script_bytes[$buffer];
			$buffer++;
			$stack = $temp[0] . $temp[1];
			break;
		case '61': //Push a 24 bit number
			$buffer = $buffer - '4';
			$ret[0] = $buffer;
			$buffer++;
			$temp[0] = $script_bytes[$buffer];
			$buffer++;
			$temp[1] = $script_bytes[$buffer];
			$buffer++;
			$temp[2] = $script_bytes[$buffer];
			$buffer++;
			$stack = $temp[0] . $temp[1] . $temp[2];
			break;
		case '6e'://Push_0
			$ret[0] = $buffer - '1';
			$stack = '0';
			break;
		case '6f'://Push_1
			$ret[0] = $buffer - '1';
			$stack = '1';
			break;
		case '70'://Push_2
			$ret[0] = $buffer - '1';
			$stack = '2';
			break;
		case '71'://Push_3
			$ret[0] = $buffer - '1';
			$stack = '3';
			break;
		case '72'://Push_4
			$ret[0] = $buffer - '1';
			$stack = '4';
			break;
		case '73'://Push_5
			$ret[0] = $buffer - '1';
			$stack = '5';
			break;
		case '74'://Push_6
			$ret[0] = $buffer - '1';
			$stack = '6';
			break;
		case '75'://Push_7
			$ret[0] = $buffer - '1';
			$stack = '7';
			break;
		default: //Error
			$stack = 'error';
	}
	$ret[3] = $buffer;
	
	
	//Null everything except $stack and $string_sect
	unset($temp);
	unset($script_bytes);
	$buffer = null;
	$last_op = null;
	$last_op_end_buffer = null;
	
	//If stack had unknown number, just display error instead of freezing trying to do whats below
	if($stack == 'error'){
		//this means there was not a push op in front of pushstring...
	}
	
	//This last part starts at the buffer, reads bytes until you hit a null, then implodes, convert to ascii, and return
	$buffer = hexdec($stack) *2;
	$ret[1] = $buffer;
	
	for($i=0; $i<100; $i++){
		$bytes[$i] = substr($string_sect, $buffer, 2);
		if($bytes[$i] != '00'){
			$buffer = $buffer + '2';
			continue;
		}
		unset($bytes[$i]);
		$string = implode("", $bytes);
		$ret_string = '"' . Hex_to_Text($string) . '"';
		break;
	}
	$ret[2] = $ret_string;
		
	return $ret; //$ret[0] is offset of push opcode, $ret[1] is offset pushed, $ret[2] is corresponding native
}

function parse_opcodes($script_sections, $filename, $ext){ 
	$time_start = microtime(true);

	$code_sect = $script_sections['code_sect'];
	$string_sect = $script_sections['string_sect'];
	$native_sect = $script_sections['native_sect'];
	$callnative_array = array();
	$callnative_array = Get_Text_Natives("general/RawNatives.txt", "general/RawHashes.txt", $script_sections);
	
	$script_bytes = new SplFixedArray(strlen($code_sect)/2);
	$script_bytes = str_split($code_sect, 2);
	$script_bytes_total = count($script_bytes);
	
	
	//Setup Progress Bar related shit
	HTML_progress_bar();
	
	$decompiled_output = '';
	$passes = 0;
	
	//First pass will use same opcode switch, but its to find jumps and calls and place labels
	
	$jump_call_offsets = array(); //Stores offsets called/jumped to
	$ops_to_ignore_offsets = array();
	$ops_to_ignore_pushed_off = array();
	$pushstring_assoc = array();
	$offsets_after_push = array();
	$script_errors = array();
	$buffer = 0;
	$label_index = 0;
	
	while ($buffer < $script_bytes_total){
		
		
		//Load next op
		$opcode = $script_bytes[$buffer];
		
		//Clear Values
		unset($temp);
		$offset = null;
		$temp = array();
		
		
		/*Find opcode match and store jump/call offset in array $jump_call_offsets:
		Jumps - offset is buffer after last byte + offset pushed
		Calls - Calls are offsets from the start of the script*/
		if($opcode == "55" || $opcode == "56" || $opcode == "57" || $opcode == "58" || $opcode == "59" || $opcode == "5a" || $opcode == "5b" || $opcode == "5c"){
			//All types of Jumps (2)
			$buffer++;
			$temp[0] = $script_bytes[$buffer];
			$buffer++;
			$temp[1] = $script_bytes[$buffer];
			$buffer++;
			$offset = Uint16toInt16(hexdec($temp[0] . $temp[1])) + $buffer;
			if(!isset($jump_call_offsets[$offset])){
				$jump_call_offsets[$offset] = $label_index;
				$label_index++;
			}
			continue;
		}
		else if($opcode == "5d"){ //Call (3)
			$buffer++;
			$temp[0] = $script_bytes[$buffer];
			$buffer++;
			$temp[1] = $script_bytes[$buffer];
			$buffer++;
			$temp[2] = $script_bytes[$buffer];
			$buffer++;
			$offset = hexdec($temp[0] . $temp[1] . $temp[2]);
			if(!isset($jump_call_offsets[$offset])){
				$jump_call_offsets[$offset] = $label_index;
				$label_index++;
			}
			continue;
		}
		else if($opcode == "62"){ //Switch (byte after * 6)
			$buffer++;//op
			$switch_size = hexdec($script_bytes[$buffer]);
			$buffer++;//size
			for($i=0; $i < $switch_size; $i++){
				$absolute_offset = null;
				$offset = null;
				unset($temp);
				$temp = array();
				$buffer++;//cases
				$buffer++;
				$buffer++;
				$buffer++;
				$temp[0] = $script_bytes[$buffer]; //jump bytes(2)
				$buffer++;
				$temp[1] = $script_bytes[$buffer];
				$buffer++;
				$offset = Uint16toInt16(hexdec($temp[0] . $temp[1])) + $buffer;
				if(!isset($jump_call_offsets[$offset])){
				$jump_call_offsets[$offset] = $label_index;
				$label_index++;
				}
			}
			continue;
		}
		else if($opcode == "63"){ //PushString. Send last op and last op end buffer to handle pushstring
			$buffer++;
			$push_s_arr = array();
			$push_s_arr = handle_pushstring($last_op, $last_op_end_buffer, $script_bytes, $string_sect);
			$ops_to_ignore_offsets[] = isset($push_s_arr[0]) ? $push_s_arr[0] : 0;
			$ops_to_ignore_pushed_off[] = isset($push_s_arr[1]) ? $push_s_arr[1] : 0;
			$pushstring_assoc[] = isset($push_s_arr[2]) ? $push_s_arr[2] : 0;
			$offsets_after_push[] = isset($push_s_arr[3]) ? $push_s_arr[3] : 0;
			unset($push_s_arr);
			continue;
		}
		else{ //Not a special opcode, continue reading script
		
			switch ($opcode) {
				case "00":
					$buffer++;
					break;
				case "01":
					$buffer++;
					break;
				case "02":
					$buffer++;
					break;
				case "03":
					$buffer++;
					break;
				case "04":
					$buffer++;
					break;
				case "05":
					$buffer++;
					break;
				case "06":
					$buffer++;
					break;
				case "07":
					$buffer++;
					break;
				case "08":
					$buffer++;
					break;
				case "09":
					$buffer++;
					break;
				case "0a":
					$buffer++;
					break;
				case "0b":
					$buffer++;
					break;
				case "0c":
					$buffer++;
					break;
				case "0d":
					$buffer++;
					break;
				case "0e":
					$buffer++;
					break;
				case "0f":
					$buffer++;
					break;
				case "10":
					$buffer++;
					break;
				case "11":
					$buffer++;
					break;
				case "12":
					$buffer++;
					break;
				case "13":
					$buffer++;
					break;
				case "14":
					$buffer++;
					break;
				case "15":
					$buffer++;
					break;
				case "16":
					$buffer++;
					break;
				case "17":
					$buffer++;
					break;
				case "18":
					$buffer++;
					break;
				case "19":
					$buffer++;
					break;
				case "1a":
					$buffer++;
					break;
				case "1b":
					$buffer++;
					break;
				case "1c":
					$buffer++;
					break;
				case "1d":
					$buffer++;
					break;
				case "1e":
					$buffer++;
					break;
				case "1f":
					$buffer++;
					break;
				case "20":
					$buffer++;
					break;
				case "21":
					$buffer++;
					break;
				case "22":
					$buffer++;
					break;
				case "23":
					$buffer++;
					break;
				case "24":
					$buffer++;
					break;
				case "25":
					$buffer++;
					$buffer++;
					break;
				case "26":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "27":
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "28":
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "29":
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "2a":
					$buffer++;
					break;
				case "2b":
					$buffer++;
					break;
				case "2c":
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "2d":
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "2e":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "2f":
					$buffer++;
					break;
				case "30":
					$buffer++;
					break;
				case "31":
					$buffer++;
					break;
				case "32":
					$buffer++;
					break;
				case "33":
					$buffer++;
					break;
				case "34":
					$buffer++;
					$buffer++;
					break;
				case "35":
					$buffer++;
					$buffer++;
					break;
				case "36":
					$buffer++;
					$buffer++;
					break;
				case "37":
					$buffer++;
					$buffer++;
					break;
				case "38":
					$buffer++;
					$buffer++;
					break;
				case "39":
					$buffer++;
					$buffer++;
					break;
				case "3a":
					$buffer++;
					$buffer++;
					break;
				case "3b":
					$buffer++;
					$buffer++;
					break;
				case "3c":
					$buffer++;
					$buffer++;
					break;
				case "3d":
					$buffer++;
					$buffer++;
					break;
				case "3e":
					$buffer++;
					$buffer++;
					break;
				case "3f":
					$buffer++;
					break;
				case "40":
					$buffer++;
					$buffer++;
					break;
				case "41":
					$buffer++;
					$buffer++;
					break;
				case "42":
					$buffer++;
					$buffer++;
					break;
				case "43":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "44":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "45":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "46":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "47":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "48":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "49":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "4a":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "4b":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "4c":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "4d":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "4e":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "4f":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "50":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "51":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "52":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "53":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "54":
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "55"://Jumps
					echo "passed jump in else loop?";
					break;
				case "56":
					echo "passed jump in else loop?";
					break;
				case "57":
					echo "passed jump in else loop?";
					break;
				case "58":
					echo "passed jump in else loop?";
					break;
				case "59":
					echo "passed jump in else loop?";
					break;
				case "5a":
					echo "passed jump in else loop?";
					break;
				case "5b":
					echo "passed jump in else loop?";
					break;
				case "5c":
					echo "passed jump in else loop?";
					break;
				case "5d"://Call
					echo "passed call in else loop?";
					break;
				case "5e":
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "5f":
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "60":
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "61":
					$buffer++;
					$buffer++;
					$buffer++;
					$buffer++;
					break;
				case "62"://Switch
					echo "passed switch in else loop?";
					break;
				case "63"://PushString
					echo "passed pushstring in else loop?";
					break;
				case "64":
					$buffer++;
					break;
				case "65":
					$buffer++;
					$buffer++;
					break;
				case "66":
					$buffer++;
					$buffer++;
					break;
				case "67":
					$buffer++;
					$buffer++;
					break;
				case "68":
					$buffer++;
					$buffer++;
					break;
				case "69":
					$buffer++;
					break;
				case "6a":
					$buffer++;
					break;
				case "6b":
					$buffer++;
					break;
				case "6c":
					$buffer++;
					break;
				case "6d":
					$buffer++;
					break;
				case "6e":
					$buffer++;
					break;
				case "6f":
					$buffer++;
					break;
				case "70":
					$buffer++;
					break;
				case "71":
					$buffer++;
					break;
				case "72":
					$buffer++;
					break;
				case "73":
					$buffer++;
					break;
				case "74":
					$buffer++;
					break;
				case "75":
					$buffer++;
					break;
				case "76":
					$buffer++;
					break;
				case "77":
					$buffer++;
					break;
				case "78":
					$buffer++;
					break;
				case "79":
					$buffer++;
					break;
				case "7a":
					$buffer++;
					break;
				case "7b":
					$buffer++;
					break;
				case "7c":
					$buffer++;
					break;
				case "7d":
					$buffer++;
					break;
				case "7e":
					$buffer++;
					break;
				default:
					$script_errors[] = "Error - opcode: " . $opcode . ", buffer: " . $buffer . "\n";
					$buffer++;
					break;
			}//End Switch
		}//End Else
		
		if(isset($error)){
			echo $error;
			$error = null;
		}
		
		//PushString Code
		$last_op = null;
		$last_op_end_buffer = null;
		$last_op = $opcode;
		$last_op_end_buffer = $buffer;
		
		$opcode = null;
	}//End While
	$buffer = null;
	
	array_unique($jump_call_offsets);
	//array_values($jump_call_offsets);
	//sort($jump_call_offsets);
	
	$max_jumpcalls = count($jump_call_offsets);
	
	
	
	free_memory();
	//sleep(2);
	
	//Second pass where shit is put into high-level format
	$buffer = 0;
	$bytes_linked = 0;
	
	$c = 0;
	$t = 0;
	$b = 0;
	$buffers_passed = array();
	$opcodes_used = array();
	$opcodes_offsets = array();

	while ($buffer < $script_bytes_total){  //Basically keep going until you run out of bytes in the code sect
		
		$buffers_passed[$b] = $buffer;
		$b++;
		
		if (isset($jump_call_offsets[$buffer]))
			$decompiled_output .= "\n:Label_" . $jump_call_offsets[$buffer] . "\n";
		
		//For PushString, if the current offset is a push before a pushstring, advance buffer past it
		$ops_to_ignore_offsets_count = count($ops_to_ignore_offsets);
		
		// !! was count($ops_to_ignore_offsets_count)
		for($h=0; $h<$ops_to_ignore_offsets_count; $h++){
			if($buffer == $ops_to_ignore_offsets[$h]){//If buffer is a buffer to skip
				
				//echo "buffer equaled offset to ignore";
				
				$op_to_ignore = $script_bytes[$buffer];//Grab opcode at buffer
				
				
				switch($op_to_ignore){//Skip opcode and associated bytes
					case "25"://Push1
						$buffer = $buffer + '2';
						break;
					case "26":
						$buffer = $buffer + '3';
						break;
					case "27":
						$buffer = $buffer + '4';
						break;
					case "28":
						$buffer = $buffer = '5';
						break;
					case "43":
						$buffer = $buffer + '3';
						break;
					case "61":
						$buffer = $buffer + '4';
						break;
					case "6e":
						$buffer++;
						break;
					case "6f":
						$buffer++;
						break;
					case "70":
						$buffer++;
						break;
					case "71":
						$buffer++;
						break;
					case "72":
						$buffer++;
						break;
					case "73":
						$buffer++;
						break;
					case "74":
						$buffer++;
						break;
					case "75":
						$buffer++;
						break;
				}
				if(isset($error)){
					//echo "$error";
					//echo "Op to Ignore: $op_to_ignore";
				}
				$error = null;
				$op_to_ignore = null;
				$h = 0;
				break;
			}//continue reading script
		}
	
		$opcode = $script_bytes[$buffer];  //Read next byte from buffer, which should be an opcode if switch is right
		$linked = array();                 //linked[0] - linked[4] holds bytes to link together after opcode. Concatenated then thrown in $bytes_linked
		
		switch ($opcode) {      //Create opcode switch. This file must stay secure after this is written
			case "00":
				$opcode_show = "nop";
				$buffer++;
				break;
			case "01":
				$opcode_show = "Add";
				$buffer++;
				break;
			case "02":
				$opcode_show = "Sub";
				$buffer++;
				break;
			case "03":
				$opcode_show = "Mult";
				$buffer++;
				break;
			case "04":
				$opcode_show = "Div";
				$buffer++;
				break;
			case "05":
				$opcode_show = "Mod";
				$buffer++;
				break;
			case "06":
				$opcode_show = "Not";
				$buffer++;
				break;
			case "07":
				$opcode_show = "Neg";
				$buffer++;
				break;
			case "08":
				$opcode_show = "CmpEQ";
				$buffer++;
				break;
			case "09":
				$opcode_show = "CmpNE";
				$buffer++;
				break;
			case "0a":
				$opcode_show = "CmpGT";
				$buffer++;
				break;
			case "0b":
				$opcode_show = "CmpGE";
				$buffer++;
				break;
			case "0c":
				$opcode_show = "CmpLT";
				$buffer++;
				break;
			case "0d":
				$opcode_show = "CmpLE";
				$buffer++;
				break;
			case "0e":
				$opcode_show = "fAdd";
				$buffer++;
				break;
			case "0f":
				$opcode_show = "fSub";
				$buffer++;
				break;
			case "10":
				$opcode_show = "fMul";
				$buffer++;
				break;
			case "11":
				$opcode_show = "fDiv";
				$buffer++;
				break;
			case "12":
				$opcode_show = "fMod";
				$buffer++;
				break;
			case "13":
				$opcode_show = "fNeg";
				$buffer++;
				break;
			case "14":
				$opcode_show = "FCmpEQ";
				$buffer++;
				break;
			case "15":
				$opcode_show = "FCmpNE";
				$buffer++;
				break;
			case "16":
				$opcode_show = "FCmpGT";
				$buffer++;
				break;
			case "17":
				$opcode_show = "FCmpGE";
				$buffer++;
				break;
			case "18":
				$opcode_show = "FCmpLT";
				$buffer++;
				break;
			case "19":
				$opcode_show = "FCmpLE";
				$buffer++;
				break;
			case "1a":
				$opcode_show = "vAdd";
				$buffer++;
				break;
			case "1b":
				$opcode_show = "vSub";
				$buffer++;
				break;
			case "1c":
				$opcode_show = "vMul";
				$buffer++;
				break;
			case "1d":
				$opcode_show = "vDiv";
				$buffer++;
				break;
			case "1e":
				$opcode_show = "vNeg";
				$buffer++;
				break;
			case "1f":
				$opcode_show = "And";
				$buffer++;
				break;
			case "20":
				$opcode_show = "Or";
				$buffer++;
				break;
			case "21":
				$opcode_show = "Xor";
				$buffer++;
				break;
			case "22":
				$opcode_show = "ItoF";
				$buffer++;
				break;
			case "23":
				$opcode_show = "FtoI";
				$buffer++;
				break;
			case "24":
				$opcode_show = "Dup2";
				$buffer++;
				break;
			case "25":
				$opcode_show = "Push1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "26":
				$opcode_show = "Push2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0]) . " " . hexdec($linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "27":
				$opcode_show = "Push3";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$linked[2] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0]) . " " . hexdec($linked[1]) . " " . hexdec($linked[2]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "28":
				$opcode_show = "Push";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$linked[2] = $script_bytes[$buffer];
				$buffer++;
				$linked[3] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = Hex_to_Dec($linked[0] .$linked[1] . $linked[2] . $linked[3]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "29":
				$opcode_show = "fPush";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$linked[2] = $script_bytes[$buffer];
				$buffer++;
				$linked[3] = $script_bytes[$buffer];
				$buffer++;
				$temp = $linked[0] . $linked[1] . $linked[2] . $linked[3];
				$temp2 = unpack('f', pack('i', hexdec($temp)));
				$bytes_linked = $temp2[1];
				$temp2 = null;
				$temp = null;
				break;
			case "2a":
				$opcode_show = "Dup";
				$buffer++;
				break;
			case "2b":
				$opcode_show = "Drop";
				$buffer++;
				break;
			case "2c":
				$opcode_show = "CallNative";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$linked[2] = $script_bytes[$buffer];
				$buffer++;
				$extra_byte = hexdec($linked[0]);
				$native_loc = hexdec($linked[1] . $linked[2]);
				$return = array(); //$return[0] = native, $return[1] = taken, $return[2] = returned
				$return = handle_callnative($extra_byte, $native_loc, $callnative_array);
				$bytes_linked = $return[0] . " " . $return[1] . " " . $return[2];    //Extra code for CallNative
				unset($return);
				$extra_byte = null;
				$native_loc = null;
				break;
			case "2d":
				$opcode_show = "Function";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$temp = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $temp . $script_bytes[$buffer];
				$buffer++;
				$linked[2] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0]) . " " . hexdec($linked[1]) . " " . hexdec($linked[2]) . "\n";
				$temp = null;
				break;
			case "2e":
				$opcode_show = "Return";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0]) . " " . hexdec($linked[1]);
				break;
			case "2f":
				$opcode_show = "pGet";
				$buffer++;
				break;
			case "30":
				$opcode_show = "pSet";
				$buffer++;
				break;
			case "31":
				$opcode_show = "pPeekSet";
				$buffer++;
				break;
			case "32":
				$opcode_show = "ToStack";
				$buffer++;
				break;
			case "33":
				$opcode_show = "FromStack";
				$buffer++;
				break;
			case "34":
				$opcode_show = "ArrayGetP1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "35":
				$opcode_show = "ArrayGet1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "36":
				$opcode_show = "ArraySet1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "37":
				$opcode_show = "pFrame1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "38":
				$opcode_show = "getF1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "39":
				$opcode_show = "setF1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "3a":
				$opcode_show = "pStatic1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "3b":
				$opcode_show = "StaticGet1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "3c":
				$opcode_show = "StaticSet1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "3d":
				$opcode_show = "Add1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "3e":
				$opcode_show = "Mult1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "3f":
				$opcode_show = "GetStackImmediateP";
				$buffer++;
				break;
			case "40":
				$opcode_show = "GetImmediateP1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "41":
				$opcode_show = "GetImmediate1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "42":
				$opcode_show = "SetImmediate1";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				$buffer++;
				break;
			case "43":
				$opcode_show = "PushS";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = Hex_to_Dec($linked[0] . $linked[1]);
				break;
			case "44":
				$opcode_show = "Add2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "45":
				$opcode_show = "Mult2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "46":
				$opcode_show = "GetImmediateP2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "47":
				$opcode_show = "GetImmediate2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "48":
				$opcode_show = "SetImmediate2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "49":
				$opcode_show = "ArrayGetP2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "4a":
				$opcode_show = "ArrayGet2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "4b":
				$opcode_show = "ArraySet2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "4c":
				$opcode_show = "pFrame2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "4d":
				$opcode_show = "getF2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "4e":
				$opcode_show = "SetF2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "4f":
				$opcode_show = "pStatic2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "50":
				$opcode_show = "StaticGet2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "51":
				$opcode_show = "StaticSet2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "52":
				$opcode_show = "pGlobal2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "53":
				$opcode_show = "globalGet2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "54":
				$opcode_show = "globalSet2";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1]);
				if($bytes_linked == "0" || $bytes_linked == ""){
					$bytes_linked = '0';
				}
				break;
			case "55":
				$opcode_show = "Jump";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$temp = Uint16toInt16(hexdec($linked[0] . $linked[1])) + $buffer;
         		//for($i=0; $i<$max_jumpcalls; $i++){
            		//if($temp == $jump_call_offsets[$i]){
								if (isset($jump_call_offsets[$temp])){
              			$bytes_linked = "@Label_" . $jump_call_offsets[$temp];
                      	break;
            		}
                //}
				if(!isset($bytes_linked)){
					$bytes_linked = "@off_$temp";
				}
				$temp = null;
				break;
			case "56":
				$opcode_show = "JumpFalse";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$temp = Uint16toInt16(hexdec($linked[0] . $linked[1])) + $buffer;
         		//for($i=0; $i<$max_jumpcalls; $i++){
            		//if($temp == $jump_call_offsets[$i]){
								if (isset($jump_call_offsets[$temp])){
              			$bytes_linked = "@Label_" . $jump_call_offsets[$temp];
                      	break;
            		}
                //}
				if(!isset($bytes_linked)){
					$bytes_linked = "@off_$temp";
				}
				$temp = null;
          		break;
			case "57":
				$opcode_show = "JumpNE";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$temp = Uint16toInt16(hexdec($linked[0] . $linked[1])) + $buffer;
         		//for($i=0; $i<$max_jumpcalls; $i++){
            		//if($temp == $jump_call_offsets[$i]){
								if (isset($jump_call_offsets[$temp])){
              			$bytes_linked = "@Label_" . $jump_call_offsets[$temp];
                      	break;
            		}
                //}
				if(!isset($bytes_linked)){
					$bytes_linked = "@off_$temp";
				}
				$temp = null;
				break;
			case "58":
				$opcode_show = "JumpEQ";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$temp = Uint16toInt16(hexdec($linked[0] . $linked[1])) + $buffer;
         		//for($i=0; $i<$max_jumpcalls; $i++){
            		//if($temp == $jump_call_offsets[$i]){
								if (isset($jump_call_offsets[$temp])){
              			$bytes_linked = "@Label_" . $jump_call_offsets[$temp];
                      	break;
            		}
                //}
				if(!isset($bytes_linked)){
					$bytes_linked = "@off_$temp";
				}
				$temp = null;
				break;
			case "59":
				$opcode_show = "JumpLE";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$temp = Uint16toInt16(hexdec($linked[0] . $linked[1])) + $buffer;
         		//for($i=0; $i<$max_jumpcalls; $i++){
            		//if($temp == $jump_call_offsets[$i]){
								if (isset($jump_call_offsets[$temp])){
              			$bytes_linked = "@Label_" . $jump_call_offsets[$temp];
                      	break;
            		}
                //}
				if(!isset($bytes_linked)){
					$bytes_linked = "@off_$temp";
				}
				$temp = null;
				break;
			case "5a":
				$opcode_show = "JumpLT";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$temp = Uint16toInt16(hexdec($linked[0] . $linked[1])) + $buffer;
         		//for($i=0; $i<$max_jumpcalls; $i++){
            		//if($temp == $jump_call_offsets[$i]){
								if (isset($jump_call_offsets[$temp])){
              			$bytes_linked = "@Label_" . $jump_call_offsets[$temp];
                      	break;
            		}
                //}
				if(!isset($bytes_linked)){
					$bytes_linked = "@off_$temp";
				}
				$temp = null;
				break;
			case "5b":
				$opcode_show = "JumpGE";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$temp = Uint16toInt16(hexdec($linked[0] . $linked[1])) + $buffer;
         		//for($i=0; $i<$max_jumpcalls; $i++){
            		//if($temp == $jump_call_offsets[$i]){
								if (isset($jump_call_offsets[$temp])){
              			$bytes_linked = "@Label_" . $jump_call_offsets[$temp];
                      	break;
            		}
                //}
				if(!isset($bytes_linked)){
					$bytes_linked = "@off_$temp";
				}
				$temp = null;
				break;
			case "5c":
				$opcode_show = "JumpGT";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$temp = Uint16toInt16(hexdec($linked[0] . $linked[1])) + $buffer;
         		//for($i=0; $i<$max_jumpcalls; $i++){
            		//if($temp == $jump_call_offsets[$i]){
								if (isset($jump_call_offsets[$temp])){
              			$bytes_linked = "@Label_" . $jump_call_offsets[$temp];
                      	break;
            		}
                //}
				if(!isset($bytes_linked)){
					$bytes_linked = "@off_$temp";
				}
				$temp = null;
				break;
			case "5d":
				$opcode_show = "Call";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$linked[2] = $script_bytes[$buffer];
				$buffer++;
				$temp = hexdec($linked[0] . $linked[1] . $linked[2]);
         		//for($i=0; $i<$max_jumpcalls; $i++){
            		//if($temp == $jump_call_offsets[$i]){
								if (isset($jump_call_offsets[$temp])){
              			$bytes_linked = "@Label_" . $jump_call_offsets[$temp];
                      	break;
            		}
                //}
				if(!isset($bytes_linked)){
					$bytes_linked = "@off_$temp";
				}
				$temp = null;
				break;
			case "5e":
				$opcode_show = "pGlobal3";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$linked[2] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1] . $linked[2]);
				break;
			case "5f":
				$opcode_show = "globalGet3";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$linked[2] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1] . $linked[2]);
				break;
			case "60":
				$opcode_show = "globalSet3";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$linked[2] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = hexdec($linked[0] . $linked[1] . $linked[2]);
				break;
			case "61":
				$opcode_show = "pushI24";
				$buffer++;
				$linked[0] = $script_bytes[$buffer];
				$buffer++;
				$linked[1] = $script_bytes[$buffer];
				$buffer++;
				$linked[2] = $script_bytes[$buffer];
				$buffer++;
				$bytes_linked = Hex_to_Dec($linked[0] . $linked[1] . $linked[2]);
				break;
			case "62":
				$opcode_show = "Switch";                             //Extra code for a Switch Case
				$buffer++;
				$cases_to_read = hexdec($script_bytes[$buffer]);
				$buffer++;
				$temp = array();
				$cases = array();
				$jumps = array();
				$labels = array();
				$c = 0;
				$j = 0;
				$l = 0;
				for($i=0; $i < $cases_to_read; $i++){
					$temp[0] = $script_bytes[$buffer]; //case
					$buffer++;
					$temp[1] = $script_bytes[$buffer];
					$buffer++;
					$temp[2] = $script_bytes[$buffer];
					$buffer++;
					$temp[3] = $script_bytes[$buffer];
					$buffer++;
					$cases[$c] = $temp[0] . $temp[1] . $temp[2] . $temp[3];
					$temp[4] = $script_bytes[$buffer]; //jump
					$buffer++;
					$temp[5] = $script_bytes[$buffer];
					$jumps[$j] = $temp[4] . $temp[5];
					$buffer++;
					//Now figure out label
					$temp1 = Uint16toInt16(hexdec($jumps[$j])) + $buffer;
					
					if (isset($jump_call_offsets[$temp1]))
						$labels[$l] = "@Label_" . $jump_call_offsets[$temp1];
					else
						$labels[$l] = "@off_$temp1";

					$l++;
					//continue to next loop
					$c++;
					$j++;
					unset($temp);
					$temp1 = null;
					$p = null;
				}
				$i=0;
				$final = array();
				while($i < $cases_to_read){
					$final[$i] = "[" . hexdec($cases[$i]) . "=" . $labels[$i] . "]";
					$i++;
				}
				$bytes_linked = implode("", $final);
				unset($cases);
				unset($jumps);
				$final = null;
				$cases_to_read = null;
				break;
			case "63"://PushString is handled above
				$opcode_show = "PushString";
				$key = array_search($buffer, $offsets_after_push);
				$bytes_linked = $pushstring_assoc[$key];
				$key = null;
				$buffer++;
				break;
			case "64":
				$opcode_show = "GetHash";
				$buffer++;
				break;
			case "65":
				$opcode_show = "StrCopy";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				$buffer++;
				break;
			case "66":
				$opcode_show = "ItoS";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				$buffer++;
				break;
			case "67":
				$opcode_show = "StrAdd";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				$buffer++;
				break;
			case "68":
				$opcode_show = "StrAddi";
				$buffer++;
				$bytes_linked = hexdec($script_bytes[$buffer]);
				$buffer++;
				break;
			case "69":
				$opcode_show = "SnCopy";
				$buffer++;
				break;
			case "6a":
				$opcode_show = "Catch";
				$buffer++;
				break;
			case "6b":
				$opcode_show = "Throw";
				$buffer++;
				break;
			case "6c":
				$opcode_show = "pCall";
				$buffer++;
				break;
			case "6d":
				$opcode_show = "push_-1";
				$buffer++;
				break;
			case "6e":
				$opcode_show = "push_0";
				$buffer++;
				break;
			case "6f":
				$opcode_show = "push_1";
				$buffer++;
				break;
			case "70":
				$opcode_show = "push_2";
				$buffer++;
				break;
			case "71":
				$opcode_show = "push_3";
				$buffer++;
				break;
			case "72":
				$opcode_show = "push_4";
				$buffer++;
				break;
			case "73":
				$opcode_show = "push_5";
				$buffer++;
				break;
			case "74":
				$opcode_show = "push_6";
				$buffer++;
				break;
			case "75":
				$opcode_show = "push_7";
				$buffer++;
				break;
			case "76":
				$opcode_show = "fPush_-1";
				$buffer++;
				break;
			case "77":
				$opcode_show = "fPush_0.0";
				$buffer++;
				break;
			case "78":
				$opcode_show = "fPush_1.0";
				$buffer++;
				break;
			case "79":
				$opcode_show = "fPush_2.0";
				$buffer++;
				break;
			case "7a":
				$opcode_show = "fPush_3.0";
				$buffer++;
				break;
			case "7b":
				$opcode_show = "fPush_4.0";
				$buffer++;
				break;
			case "7c":
				$opcode_show = "fPush_5.0";
				$buffer++;
				break;
			case "7d":
				$opcode_show = "fPush_6.0";
				$buffer++;
				break;
			case "7e":
				$opcode_show = "fPush_7.0";
				$buffer++;
				break;
			default:
				$opcode_show = "unk_op";
				$bytes_linked = $opcode;
				$buffer++;
				break;
		}
		
		//Once switch is complete, echo opcode name, space, bytes linked (if there are any), then newline
		$decompiled_output .= $opcode_show;
		if($bytes_linked != null){
			$decompiled_output .= " " . $bytes_linked;
		}
		$decompiled_output .= "\n";
		
		
		//Reset values after loop. This prevents values from concatenating onto each other - stupid php
		unset($linked);
		$bytes_linked = null;
		$opcode_show = null;
		$opcode = null;
		$temp = null;
		
		//Update Progress Bar
		$passes++;
		if($passes%100 == 0){
			$perc = ceil($buffer/$script_bytes_total * 100);
			$percent = ceil($buffer/$script_bytes_total * 100) . "%";
			if($perc < 100){
				echo <<<EOT
				<script type='text/javascript'>
				document.getElementById('progress_bar').style.width = '$perc'+'%';
				document.getElementById('progress_bar').setAttribute('aria-valuenow', '$perc')
				document.getElementById('progress_bar').innerHTML = '$percent Complete';
				</script>
EOT;
			}else{
				echo <<<EOT
				<script type='text/javascript'>
				document.getElementById('progress_bar').style.width = '100'+'%';
				document.getElementById('progress_bar').setAttribute('aria-valuenow', '100')
				document.getElementById('progress_bar').innerHTML = 'Done!';
				</script>
EOT;
			}
			
			flush();
		}

		
		
	} //Repeat while loop until buffer has reached end of code_sect
	
	
	echo <<<EOT
				<script type='text/javascript'>
				document.getElementById('progress_bar').style.width = '100'+'%';
				document.getElementById('progress_bar').setAttribute('aria-valuenow', '100')
				document.getElementById('progress_bar').innerHTML = 'Done!';
				</script>
EOT;
	
	
	/* DEBUG CODE */
	/*echo "371: " . array_search("371", $buffers_passed) . "\n";
	echo "436: " . array_search("436", $buffers_passed) . "\n";
	echo "804: " . array_search("804", $buffers_passed) . "\n";//did buffer pass offset
	echo "2078: " . array_search("2078", $buffers_passed) . "\n";
	
	echo "\n";
	
	echo "371: " . array_search("371", $jump_call_offsets) . "\n";//is offset in jump_calls
	echo "436: " . array_search("436", $jump_call_offsets) . "\n";
	echo "804: " . array_search("804", $jump_call_offsets) . "\n";
	echo "2078: " . array_search("2078", $jump_call_offsets) . "\n";
	
	echo "\n";
	
	echo "Jump Call Offsets 0: $jump_call_offsets[3]\n";
	echo "Jump Call Offsets 1: $jump_call_offsets[4]\n";
	echo "Jump Call Offsets 2: $jump_call_offsets[5]\n";
	echo "Jump Call Offsets 3: $jump_call_offsets[6]\n";
	echo "Jump Call Offsets 4: $jump_call_offsets[7]\n";
	echo "Jump Call Offsets 5: $jump_call_offsets[8]\n";
	echo "Jump Call Offsets 6: $jump_call_offsets[9]\n";
	
	echo "Jump Call Offsets Count: " . count($jump_call_offsets) . "\n";*/
	/* END DEBUG CODE */

	//Free up memory - helps a lot
	unset($script_bytes);
	$string_sect = null;
	$code_sect = null;
	$native_sect = null;
	free_memory();

	$time_end = microtime(true);
	$time = $time_end - $time_start;

	$filename = str_replace(array("\\", "/"), "", $filename);
	$filename = str_replace(" ", "_", $filename);

	$filename = preg_replace('/[^a-zA-Z0-9_\-]/s', '', $filename);
	$filename = str_replace("__", "_", $filename);

	$filename = trim($filename);

	if (!empty($_POST['save_code']))
	{
		if ($ext == "csc")
			$file_ext = ".csa";
		else
			$file_ext = ".xsa";

		require('../config.php');

		$code_file_session = sha1($secretKey1 . $filename . $file_ext . session_id());

		file_put_contents($sourceDir . 'code/' . $code_file_session, $decompiled_output);

		//file_put_contents($sourceDir . 'logs/decompile.txt', date("d/m/y - G:i:s") . ' - ' . $_SERVER["REMOTE_ADDR"] . " - " . $filename . "." . $ext . " (" . $code_file_session . ") - " . $time . "\n", FILE_APPEND);
		
		$secure_session = sha1($secretKey2 . $filename . $file_ext . session_id());

		HTML_Code_download($installUrl . "/code/download.php?f=" . $filename . $file_ext . "&s=" . $secure_session, $time);
	}
	else
	{
		//file_put_contents($sourceDir . 'logs/decompile.txt', date("d/m/y - G:i:s") . ' - ' . $_SERVER["REMOTE_ADDR"] . " - " . $filename . "." . $ext . " - " . $time . "\n", FILE_APPEND);
		HTML_Code_textarea($decompiled_output, $time);
	}

	if (!empty($script_errors))
		HTML_Errors($script_errors);

	return;
}


?>
<?php

/*       This file is the main XSC Compiling Script        */


function parse_code($code_lines, $static_sect, $xsc_final_filename, $script_ext)
{
	global $return_html;
	
	$bytes = array(); //store final product in here to return
	
	$return_html = "";
	
	$string_sect = array();
	$string_storage = array();
	$string_offset_storage = array();
	$s = 0;
	
	$native_sect = array();
	$native_storage = array();
	$native_offset_storage = array();
	$n = 0;
	
	
	//Label_decs stores all :Label's to compare @Labels to after everything else is done
	$label_decs = array();
	//Label_decs_offsets stores the corresponding offsets of :Label's
	$label_decs_offsets = array();
	$d = 0;
	
	
	//Read until you go thru all the submitted code
	$i = 0;
	$code_lines_count = count($code_lines);
	while($i < $code_lines_count){
		
		$line = $code_lines[$i];
		
		//if(substr_count($line, ":") > 0){//Label Declaration - leave alone for now
		if (preg_match("/^\:(.+?)$/", $line)) {
			//Store label as @Label in array
			$label_decs[$d] = str_replace(":", "@", preg_replace('/\s+/', '',$line));
			//Store offset as bytes array max key + 1 because it points to next op
			$label_decs_offsets[$d] = max(array_keys($bytes)) + 1;
			$d++;
			$i++;
			continue;
		}
		
		/*
			Calls/Jumps:
			Read Line. Echo @Label and reserve extra array keys as needed for the offset
			later. This is so the offsets stay the same and :Labels will show up at the
			correct places
			
			Label Declarations:
			For :Labels, add the label name and offset to an array. After all the other bytes
			are set, and offsets are final, take each @Label and match it to a :Label in the
			label_decs array. The offset in the same key # of the other array is the offset to
			replace.
		*/
		
		
		
		if(substr_count($line, "\xa0") == '0' && substr_count($line, " ") == '0'){//Line has single op with no param
			$line_parts = array();
			$line_parts[0] = str_replace(' ', '',preg_replace('/\s+/', '',$line));
		}else{//Line has op with at least one param
			$format_line = str_replace("\xa0"," ",$line);
			$line_parts = array();
			if(substr_count($format_line, '"') > 0){//PushString or CallNative
				$line_parts = str_getcsv($format_line, ' ');
			}else{//No need to preserve space inside quotes...
				$line_parts = explode(" ", $format_line);
			}
		}
		
		
		$opcode = $line_parts[0];
		
		
		$temp = array();
	
		switch(strtolower($opcode)){
			case "nop"://1
				$bytes[] = "00";
				break;
			case "add"://1
				$bytes[] = "01";
				break;
			case "sub"://1
				$bytes[] = "02";
				break;
			case "mult"://1
				$bytes[] = "03";
				break;
			case "div"://1
				$bytes[] = "04";
				break;
			case "mod"://1
				$bytes[] = "05";
				break;
			case "not"://1
				$bytes[] = "06";
				break;
			case "neg"://1
				$bytes[] = "07";
				break;
			case "cmpeq"://1
				$bytes[] = "08";
				break;
			case "cmpne"://1
				$bytes[] = "09";
				break;
			case "cmpgt"://1
				$bytes[] = "0a";
				break;
			case "cmpge"://1
				$bytes[] = "0b";
				break;
			case "cmplt"://1
				$bytes[] = "0c";
				break;
			case "cmple":
				$bytes[] = "0d";
				break;
			case "fadd"://1
				$bytes[] = "0e";
				break;
			case "fsub"://1
				$bytes[] = "0f";
				break;
			case "fmul"://1
				$bytes[] = "10";
				break;
			case "fdiv"://1
				$bytes[] = "11";
				break;
			case "fmod"://1
				$bytes[] = "12";
				break;
			case "fneg"://1
				$bytes[] = "13";
				break;
			case "fcmpeq"://1
				$bytes[] = "14";
				break;
			case "fcmpne"://1
				$bytes[] = "15";
				break;
			case "fcmpgt"://1
				$bytes[] = "16";
				break;
			case "fcmpge"://1
				$bytes[] = "17";
				break;
			case "fcmplt"://1
				$bytes[] = "18";
				break;
			case "fcmple"://1
				$bytes[] = "19";
				break;
			case "vadd"://1
				$bytes[] = "1a";
				break;
			case "vsub"://1
				$bytes[] = "1b";
				break;
			case "vmul"://1
				$bytes[] = "1c";
				break;
			case "vdiv"://1
				$bytes[] = "1d";
				break;
			case "vneg"://1
				$bytes[] = "1e";
				break;
			case "and"://1
				$bytes[] = "1f";
				break;
			case "or"://1
				$bytes[] = "20";
				break;
			case "xor"://1
				$bytes[] = "21";
				break;
			case "itof"://1
				$bytes[] = "22";
				break;
			case "ftoi"://1
				$bytes[] = "23";
				break;
			case "dup2"://1
				$bytes[] = "24";
				break;
			case "push1"://2
				$bytes[] = "25";
				$temp[0] = dechex($line_parts[1]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				break;
			case "push2"://3
				$bytes[] = "26";
				$temp[0] = dechex($line_parts[1]);
				$temp[1] = dechex($line_parts[2]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				$bytes[] = str_pad($temp[1], 2, '0', STR_PAD_LEFT);
				break;
			case "push3"://4
				$bytes[] = "27";
				$temp[0] = dechex($line_parts[1]);
				$temp[1] = dechex($line_parts[2]);
				$temp[2] = dechex($line_parts[3]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				$bytes[] = str_pad($temp[1], 2, '0', STR_PAD_LEFT);
				$bytes[] = str_pad($temp[2], 2, '0', STR_PAD_LEFT);
				break;
			case "push"://5
				$bytes[] = "28";
				$temp[0] = Dec_to_Hex($line_parts[1]);
				//split into array to place 2 at a time
				$arr = array();
				$arr = str_split(str_pad($temp[0], 8, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				$bytes[] = $arr[2];
				$bytes[] = $arr[3];
				unset($arr);
				break;
			case "fpush"://5
				$bytes[] = "29";
				//Convert to 32 bit hex. Every 2 chars into array key
				$temp[0] = isset($line_parts[1]) ? Float2Hex($line_parts[1]) : Float2Hex(0.0);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 8, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				$bytes[] = $arr[2];
				$bytes[] = $arr[3];
				unset($arr);
				break;
			case "dup"://1
				$bytes[] = "2a";
				break;
			case "drop"://1
				$bytes[] = "2b";
				break;
			case "callnative"://4 (special)
				$bytes[] = "2c";
				//Get Native Hex Hash
				if(substr_count(strtoupper($line_parts[1]), "UNK_") > 0){//if it's a UNK_
					$hash = str_ireplace(array('"', "unk_0x", "UNK_"), "", $line_parts[1]);
				}else{//else if it's a normal native name
					$hash = hash('joaat', strtolower(str_replace('"', '', $line_parts[1])));//Native
				}
				
				//Check to see if hash is already in native sect
				if(array_search($hash, $native_sect) !== false){
					//Native is already in native sect
					$nat_loc = array_search($hash, $native_sect);
				}else{
					//Add native to native sect and return location
					$native_sect[] = $hash;
					$nat_loc = array_search($hash, $native_sect);
				}
				
				//Push Params taken/returned
				$bytes[] = str_pad(dechex(($line_parts[2] << 2)|$line_parts[3]), 2, '0', STR_PAD_LEFT);
				//Push native array key. Extend to 4
				$arr = array();
				$arr = str_split(str_pad(dechex($nat_loc), 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				//unset vars
				$hash = null;
				$nat_loc = null;
				unset($arr);
				break;
			case "function"://5
				$bytes[] = "2d";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$temp[1] = str_pad(dechex($line_parts[2]), 4, '0', STR_PAD_LEFT);
				$temp[2] = str_pad(dechex($line_parts[3]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				$bytes[] = substr($temp[1], 0, 2);
				$bytes[] = substr($temp[1], 2, 2);
				$bytes[] = $temp[2];
				break;
			case "return"://3
				$bytes[] = "2e";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$temp[1] = str_pad(dechex($line_parts[2]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				$bytes[] = $temp[1];
				break;
			case "pget"://1
				$bytes[] = "2f";
				break;
			case "pset"://1
				$bytes[] = "30";
				break;
			case "ppeekset"://1
				$bytes[] = "31";
				break;
			case "tostack"://1
				$bytes[] = "32";
				break;
			case "fromstack"://1
				$bytes[] = "33";
				break;
			case "arraygetp1"://2
				$bytes[] = "34";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "arrayget1"://2
				$bytes[] = "35";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "arrayset1"://2
				$bytes[] = "36";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "pframe1"://2
				$bytes[] = "37";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "getf1"://2
				$bytes[] = "38";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "setf1"://2
				$bytes[] = "39";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "pstatic1"://2
				$bytes[] = "3a";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "staticget1"://2
				$bytes[] = "3b";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "staticset1"://2
				$bytes[] = "3c";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "add1"://2
				$bytes[] = "3d";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "mult1"://2
				$bytes[] = "3e";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "getstackimmediatep"://1
				$bytes[] = "3f";
				break;
			case "getimmediatep1"://2
				$bytes[] = "40";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "getimmediate1"://2
				$bytes[] = "41";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "setimmediate1"://2
				$bytes[] = "42";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "pushs"://3
				$bytes[] = "43";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "add2"://3
				$bytes[] = "44";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "mult2"://3
				$bytes[] = "45";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "getimmediatep2"://3
				$bytes[] = "46";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "getimmediate2"://3
				$bytes[] = "47";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "setimmediate2"://3
				$bytes[] = "48";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "arraygetp2"://3
				$bytes[] = "49";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "arrayget2"://3
				$bytes[] = "4a";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "arrayset2"://3
				$bytes[] = "4b";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "pframe2"://3
				$bytes[] = "4c";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "getf2"://3
				$bytes[] = "4d";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "setf2"://3
				$bytes[] = "4e";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "pstatic2"://3
				$bytes[] = "4f";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "staticget2"://3
				$bytes[] = "50";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "staticset2"://3
				$bytes[] = "51";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "pglobal2"://3
				$bytes[] = "52";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "globalget2"://3
				$bytes[] = "53";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "globalset2"://3
				$bytes[] = "54";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "jump"://2 special
				$bytes[] = "55";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "jumpfalse"://2 special
				$bytes[] = "56";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "jumpne"://2 special
				$bytes[] = "57";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "jumpeq"://2 special
				$bytes[] = "58";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "jumple"://2 special
				$bytes[] = "59";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "jumplt"://2 special
				$bytes[] = "5a";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "jumpge"://2 special
				$bytes[] = "5b";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "jumpgt"://2 special
				$bytes[] = "5c";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "call"://3 special
				$bytes[] = "5d";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				$bytes[] = "";
				break;
			case "pglobal3"://4
				$bytes[] = "5e";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 6, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				$bytes[] = $arr[2];
				unset($arr);
				break;
			case "globalget3"://4
				$bytes[] = "5f";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 6, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				$bytes[] = $arr[2];
				unset($arr);
				break;
			case "globalset3"://4
				$bytes[] = "60";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 6, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				$bytes[] = $arr[2];
				unset($arr);
				break;
			case "pushi24"://4
				$bytes[] = "61";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 6, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				$bytes[] = $arr[2];
				unset($arr);
				break;
			case "switch"://* (special)
				$bytes[] = "62";
				$begin_bracket_count = dechex(substr_count($line_parts[1], "]"));
				$end_bracket_count = dechex(substr_count($line_parts[1], "]"));
				if($begin_bracket_count != $end_bracket_count){
					//Compile error!!
				}
				$switch_bytes = array();
				$switch_bytes = Parse_Switch($line_parts[1]);
				foreach($switch_bytes as $thing){
					$bytes[] = $thing;
				}
				break;
			case "pushstring"://1 (special)
				//Get string hex
				$hex = String_to_Hex($line_parts[1]);
				//echo "String: $line_parts[1], ";
				//Get string start offset
				if(array_search($hex, $string_storage) !== false){
					//If string is already in string section...
					$temp = array_search($hex, $string_storage);
					$offset = $string_offset_storage[$temp];
					//echo "Offset: $offset<br />";
					unset($temp);
				}else{//Get string offset. Store offset and string in storage arrays
					//String is unique. Get offset and add to string sect
					$offset = count($string_sect);
					$string_offset_storage[$s] = $offset;
					$string_storage[$s] = $hex;
					$s++;
					//echo "Offset: $offset<br />";
					
					//Store in string sect byte by byte
					if($hex == ""){
						//If string is empty (happens sometimes)
						$string_sect[] = "00";
					}else{
						$arr = array();
						$arr = str_split($hex, 2);
						foreach($arr as $part){
							$string_sect[] = $part;
						}
						$string_sect[] = "00";
					}
				}
				
				
				//Create Push before PushString
				$to_push = array();
				$to_push = Create_Push_Before_PushString($offset);
				
				//Echo Push
				foreach($to_push as $eko){
					$bytes[] = $eko;
				}
				$bytes[] = "63";
				unset($arr);
				$hex = null;
				$offset = null;
				unset($to_push);
				break;
			case "gethash"://1
				$bytes[] = "64";
				break;
			case "strcopy"://2
				$bytes[] = "65";
				$temp[0] = dechex($line_parts[1]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				break;
			case "itos"://2
				$bytes[] = "66";
				$temp[0] = dechex($line_parts[1]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				break;
			case "stradd"://2
				$bytes[] = "67";
				$temp[0] = dechex($line_parts[1]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				break;
			case "straddi"://2
				$bytes[] = "68";
				$temp[0] = dechex($line_parts[1]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				break;
			case "sncopy"://1
				$bytes[] = "69";
				break;
			case "catch"://1
				$bytes[] = "6a";
				break;
			case "throw"://1
				$bytes[] = "6b";
				break;
			case "pcall"://1
				$bytes[] = "6c";
				break;
			case "push_-1"://1
				$bytes[] = "6d";
				break;
			case "push_0"://1
				$bytes[] = "6e";
				break;
			case "push_1"://1
				$bytes[] = "6f";
				break;
			case "push_2"://1
				$bytes[] = "70";
				break;
			case "push_3"://1
				$bytes[] = "71";
				break;
			case "push_4"://1
				$bytes[] = "72";
				break;
			case "push_5"://1
				$bytes[] = "73";
				break;
			case "push_6"://1
				$bytes[] = "74";
				break;
			case "push_7"://1
				$bytes[] = "75";
				break;
			case "fpush_-1"://1
				$bytes[] = "76";
				break;
			case "fpush_0.0"://1
				$bytes[] = "77";
				break;
			case "fpush_1.0"://1
				$bytes[] = "78";
				break;
			case "fpush_2.0"://1
				$bytes[] = "79";
				break;
			case "fpush_3.0"://1
				$bytes[] = "7a";
				break;
			case "fpush_4.0"://1
				$bytes[] = "7b";
				break;
			case "fpush_5.0"://1
				$bytes[] = "7c";
				break;
			case "fpush_6.0"://1
				$bytes[] = "7d";
				break;
			case "fpush_7.0"://1
				$bytes[] = "7e";
				break;
			case "unk_op"://1
				$bytes[] = str_pad(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $line_parts[1]), 2, '0', STR_PAD_LEFT);
				break;
			default:
				$return_html .= "<p class=\"bg-danger\">Compile error! Unk opcode: \"$opcode\"<br />" . $code_lines[$i-1] . "<br />" . $code_lines[$i] . " &nbsp;<-----<br />" . $code_lines[$i+1] . "</p>";
		}
		//Still do CallNative, PushString, and Switch
		
		$error = null;
		$line = null;
		$format_line = null;
		unset($line_parts);
		unset($temp);
		$opcode = null;
		
		$i++;
	}//End While
	
	
	//Now go thru and replace @Labels with correct offsets
	//$label_decs
	//$label_decs_offsets
	//str_pad( , 6, '0', STR_PAD_LEFT);
	$m = 0;
	$bytes_count = count($bytes);
	while($m < $bytes_count){
		if(substr_count($bytes[$m], "@") > 0){
			//Array key contains @Label
			$key = array_search(preg_replace('/\s+/', '',$bytes[$m]), $label_decs);
			$one_up = $m + 1;
			$two_up = $m + 2;
			if($key === false){//If it jumps to a non-existent label...
				if($bytes[$one_up] == "" && $bytes[$two_up] == ""){
					//Call to non-existing label. just put FF. extend to 6 bytes
					$return_html .= "<p class=\"bg-danger\">Compile error! >Hit a Call FF. Label: " . $bytes[$m] . "</p>";
					$bytes[$m] = "FF";
					$bytes[$one_up] = "FF";
					$bytes[$two_up] = "FF";
				}
				else if($bytes[$one_up] == "" && $bytes[$two_up] != ""){
					//Jump to non-existing label. just put FF. extend to 6 bytes
					$return_html .= "<p class=\"bg-danger\">Compile error! >Hit a Jump FF. Label: " . $bytes[$m] . "</p>";
					$bytes[$m] = "FF";
					$bytes[$one_up] = "FF";
				}
			}
			else{//raw offset to replace @Label with
				$offset = $label_decs_offsets[$key];
				//find out how far to extend offset
				if($bytes[$one_up] == "" && $bytes[$two_up] == ""){
					//Call. Extend offset to 6
					$hex_offset = str_pad(dechex($offset), 6, '0', STR_PAD_LEFT);
					$temp = array();
					$temp = str_split($hex_offset, 2);
					$bytes[$m] = $temp[0];
					$bytes[$one_up] = $temp[1];
					$bytes[$two_up] = $temp[2];
					unset($temp);
					$hex_offset = null;
				}
				else if($bytes[$one_up] == "" && $bytes[$two_up] != ""){
					//Jump. Extend offset to 4
					$new_offset = $offset - ($m + 2);
					//echo "<i>Offset: $offset, New Offset: $new_offset, M: $m --- ";
					$hex_offset = str_pad(dechex(Int16toUint16($new_offset)), 4, '0', STR_PAD_LEFT);
					//echo "Hex Offset: $hex_offset</i><br />";
					$temp = array();
					$temp = str_split($hex_offset, 2);
					$bytes[$m] = $temp[0];
					$bytes[$one_up] = $temp[1];
					unset($temp);
					$hex_offset = null;
					$new_offset = null;
				}
			}
			$one_up = null;
			$two_up = null;
			$offset = null;
			$key == null;
		}
		$m++;
	}
	
	
	//Create Code Sect
	$code_sect = implode("", $bytes);
	$code_sect_length = strlen($code_sect) / 2;
	//Extend code length to multiple of 16
	while((strlen($code_sect) / 2) % 16 != 0){
		$code_sect = $code_sect . "00";
	}
	
	//Create String Sect
	$string_sect = implode("", $string_sect);
	$string_sect_length = strlen($string_sect) / 2;
	//Extend string length to multiple of 16
	while((strlen($string_sect) / 2) % 16 != 0){
		$string_sect = $string_sect . "00";
	}
	
	
	//debug
	//foreach($native_sect as $native){
	//	echo "Native: $native <br />";
	//}
	
	//Create Native Sect
	$native_sect = implode("", $native_sect);
	$native_sect_length = strlen($native_sect) / 2;
	$native_count = strlen($native_sect) / 8;
	while((strlen($native_sect) / 2) % 16 != 0){
		$native_sect = $native_sect . "00";
	}

	//Create Static Sect
	//$static_sect = array('00000006', '0000000A', '00000032', '00000032', '00000032', '00000032', '00000017');
	$static_sect = implode("", $static_sect);
	$static_sect_length = strlen($static_sect) / 2;
	$static_count = strlen($static_sect) / 8;
	while((strlen($static_sect) / 2) % 16 != 0){
		$static_sect = $static_sect . "00";
	}
	
	//Create Header. Header is 80 bytes.
	$HeaderValues = array();
	$HeaderValues['magic'] = "B43A4500";
	$HeaderValues['unk1'] = "";//Pointer to 01 at end of file
	$HeaderValues['codepagesoffset'] = "";//Pointer to code pages at end of file
	$HeaderValues['globalsversion'] = "FF448AC7";
	$HeaderValues['codelength'] = str_pad(dechex($code_sect_length), 8, '0', STR_PAD_LEFT);
	$HeaderValues['parametercount'] = "00000000";
	$HeaderValues['staticscount'] = str_pad(dechex($static_count), 8, '0', STR_PAD_LEFT);
	$HeaderValues['globalscount'] = "00000000";
	$HeaderValues['nativecount'] = str_pad(dechex($native_count), 8, '0', STR_PAD_LEFT);
	$HeaderValues['staticsoffset'] = "";
	$HeaderValues['globalsoffset'] = "00000000";
	$HeaderValues['nativesoffset'] = "";//Pointer to native sect start
	$HeaderValues['unk2'] = "00000000";
	$HeaderValues['unk3'] = "00000000";
	$HeaderValues['unk4'] = hash('joaat', $xsc_final_filename);//Jenkins hash of script
	$HeaderValues['unk5'] = "00000001";
	$HeaderValues['scriptnameoffset'] = "";//Pointer to filename at end of file
	$HeaderValues['stringpagesoffset'] = "";//Pointer to string pages at end of file
	$HeaderValues['stringssize'] = str_pad(dechex($string_sect_length), 8, '0', STR_PAD_LEFT);
	$HeaderValues['unk6'] = "00000000";
	
	//Create last section (code/string block pointers, and filename)
	$final_section = array();
	
	//make a var containing the length of the file so far
	$code_sect_so_far = strlen($code_sect) /2;
	$string_sect_so_far = strlen($string_sect) /2;
	$native_sect_so_far = strlen($native_sect) /2;
	$filelength_so_far = $code_sect_so_far + $string_sect_so_far + $native_sect_so_far + 80;
	//also create native location offset
	$native_sect_offset = $code_sect_so_far + $string_sect_so_far + 80;

	//add some padding
	$final_section['padding'] = "00000000000000000000000000000000";

	$final_section['statics'] = $static_sect;
	$static_sect_so_far = strlen($static_sect) / 2;
	$static_sect_offset = $filelength_so_far + 16;
	$filelength_so_far = $filelength_so_far + $static_sect_so_far;
	
	//location of filename
	$filename_pointer_loc = $filelength_so_far + 16;
	//filename
	$filename = bin2hex($xsc_final_filename);//bin2hex POST_FILENAME
	//extend filename so there's always at least 4 nulls after it
	if(strlen($filename)/2 > 12){
		while((strlen($filename) / 2) < 32){
			$filename = $filename . "00";
		}
		$filelength_so_far = $filelength_so_far + 16;
	}else{
		while((strlen($filename)/2) < 16){
			$filename = $filename . "00";
		}
	}
	
	$final_section['filename'] = $filename;
	
	//location of code block pointer
	$codeblocks_pointer_loc = $filelength_so_far + 32;
	//code block pointer

	//var_dump(dechex($codeblocks));
	//die();
	$codeblocks = "50000050";
	while((strlen($codeblocks) / 2) < 16){
		$codeblocks = $codeblocks . "00";
	}
	//var_dump(dechex($code_sect_so_far+80));
	//die();
	//$codePages_len = $code_sect_length + 16384 - 1 >> 14;
	//$codePages = array();

	//for ($i = 0; $i < $codePages_len; $i++)
		//$codePages[$i] = (reader.ReadInt() & 16777215) + XSCGlobals.BaseOffset;

	//var_dump($codePages);
	//die();
	$final_section['codeblocks'] = $codeblocks;
	
	//location of string block pointer
	$stringblocks_pointer_loc = $filelength_so_far + 48;
	//string block pointer
	if(strlen($string_sect) < 4){
		//String Block pointer is null
		$stringblocks = "00000000";
	}else{//Create String Block Pointer
		$stringblocks = dechex((strlen($code_sect) / 2) + 80);//String starts right after code
		$stringblocks = str_pad($stringblocks, 8, '0', STR_PAD_LEFT);
		$stringblocks = substr($stringblocks, 2);
		$stringblocks = "50" . $stringblocks;
	}
	while((strlen($stringblocks) / 2) < 16){
		$stringblocks = $stringblocks . "00";
	}
	$final_section['stringblocks'] = $stringblocks;
	
	//location of extra 01 at end
	$oneatend_pointer_loc = $filelength_so_far + 64;
	//create extra 01 at end
	$final_section['oneatend'] = "00000000010000000000000000000000";

	$final_sect = implode("", $final_section);
	
	//Fill in remaining Header Values
	$HeaderValues['unk1'] = create_pointer_from_offset(dechex($oneatend_pointer_loc));
	$HeaderValues['codepagesoffset'] = create_pointer_from_offset(dechex($codeblocks_pointer_loc));
	$HeaderValues['nativesoffset'] = create_pointer_from_offset(dechex($native_sect_offset));
	$HeaderValues['staticsoffset'] = create_pointer_from_offset(dechex($static_sect_offset));
	$HeaderValues['scriptnameoffset'] = create_pointer_from_offset(dechex($filename_pointer_loc));
	$HeaderValues['stringpagesoffset'] = create_pointer_from_offset(dechex($stringblocks_pointer_loc));
	
	//Start throwing together parts
	$xsc_parts = array();
	$xsc_parts['header'] = implode("", $HeaderValues);
	$xsc_parts['code'] = $code_sect;
	$xsc_parts['strings'] = $string_sect;
	$xsc_parts['natives'] = $native_sect;
	$xsc_parts['final'] = $final_sect;
	
	$hex = implode("", $xsc_parts);
	
	//NOW DEAL WITH RSC7 HEADER SHIT
	$rscheader = array();
	$rscheader['magic'] = "52534337";
	$rscheader['version'] = "00000009";

	$hex_length = strlen($hex) / 2;

	$file_basesize = $script_ext == "csa" ? 4096 : 8192;
	$rounded_length = (int) ceil($hex_length / $file_basesize) * $file_basesize;

	$hex .= str_repeat("00", $rounded_length - $hex_length);
	
	$hex_length = strlen($hex) / 2;

	$systemflag = dechex(GetFlagFromSize($hex_length, $file_basesize));
	
	$rscheader['systemflag'] = str_pad($systemflag, 8, '0', STR_PAD_LEFT);
	
	$file_ext = $script_ext == "csa" ? ".csc" : ".xsc";
	
	$rscheader['graphicsflag'] = "90000000";
	$libv_header = implode("", $rscheader);
	
	$hex = $libv_header . $hex;

	$label_decs_u = array_unique($label_decs);
	$label_decs_same = array_diff($label_decs, array_diff($label_decs_u, array_diff_assoc($label_decs, $label_decs_u)));

	if (!empty($label_decs_same))
	{
		$return_html .= '<br /><p class="text-danger">Duplicate labels found</p>';
		
		foreach ($label_decs_same as $label)
		{
			$return_html .= '<p class="text-danger">' . $label . '</p>';
		}
	}
		
	if (empty($return_html))
	{
		file_put_contents('/home/3s/logs/compile.txt', date("d/m/y - G:i:s") . ' - ' . $_SERVER["REMOTE_ADDR"] . " - " . $xsc_final_filename . $file_ext . "\n", FILE_APPEND);
		ob_clean();
		
		header('Content-Type: application/octet-stream');
		header("Content-Disposition: attachment; filename=\"" . $xsc_final_filename . $file_ext ."\"");
		echo pack('H*', $hex);
		exit;
	}
}



?>
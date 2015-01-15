<?php

/*       This file is the main XSC Compiling Script        */






function draw_error_html($error){
	//Draw error html and kill script execution
	
	echo <<<EOT
	<html>
	
	<head>
	<title>XSC Compiler</title>
	<link rel="icon" type="img/ico" href="../favicon.ico">
	<link rel="stylesheet" type="text/css" href="../general/style.css">
	</head>
	
	<body>
	<center>
	
	
	<table background='../general/table_bg.jpg' width = '40%' height = '75px'>
	<tr width = '40%' height = '75px'>
	<td align='center' class='control_panel_cell'>
	<form method='link' action="index.php"><input class='button_cp_main' type="submit" value="Upload File"></form>
	</td>
	<td align='center' class='control_panel_cell'>
	<form method='link' action="xscuploads/"><input class='button_cp_uploadsmanager' type="submit" value="Uploads Manager"></form>
	</td>
	<td align='center' class='control_panel_cell'>
	<form method='link' action="secure/logview.php"><input class='button_cp_logviewer' type="submit" value="Log Viewer"></form>
	</td>
	</tr>
	</table>
	
	<br><br><br><br><br><br><br><br><br><br><br><br><br>
	
	<table background='../general/table_bg.jpg' width = '50%' height = '75px'>
	
	<tr width = '50%' height = '75px'>
	<td>
	<center>
	
	<br><br><br><br>
	<p><h2><b><font color='ff0000'>Error: $error</font></b></h2></p>
	
	<br>
	
	<p>
	<input action="action" type="button" value="Back" class="button_compile_error" onclick="history.go(-1);" />
	</p>
	
	<br><br><br><br>
	
	</center>
	</td>
	</tr>
	
	</table>
	
	
	</center>
	</body>
	
	</html>	
	
EOT;

exit();

}


function draw_success_html($xsc_final_filename, $libv_header){
	//Draw success html and provide file download link
	
	$file_link = "../xscoutput/" . $xsc_final_filename . ".xsc";
	
	echo <<<EOT
	<html>
	
	<head>
	<title>XSC Compiler</title>
	<link rel="icon" type="img/ico" href="../favicon.ico">
	<link rel="stylesheet" type="text/css" href="../general/style.css">
	</head>
	
	<body>
	<center>
	
	
	<table background='../general/table_bg.jpg' width = '40%' height = '75px'>
	<tr width = '40%' height = '75px'>
	<td align='center' class='control_panel_cell'>
	<form method='link' action="index.php"><input class='button_cp_main' type="submit" value="Upload File"></form>
	</td>
	<td align='center' class='control_panel_cell'>
	<form method='link' action="xscuploads/"><input class='button_cp_uploadsmanager' type="submit" value="Uploads Manager"></form>
	</td>
	<td align='center' class='control_panel_cell'>
	<form method='link' action="secure/logview.php"><input class='button_cp_logviewer' type="submit" value="Log Viewer"></form>
	</td>
	</tr>
	</table>
	
	<br><br><br><br><br><br><br><br><br><br><br><br><br>
	
	<table background='../general/table_bg.jpg' width = '50%' height = '75px'>
	
	<tr width = '50%' height = '75px'>
	<td>
	<center>
	
	<br><br><br><br>
	<p><h2><b><font color='00ff00'>Your XSC has been compiled successfully!</font></b></h2></p>
	
	<br><br>
	
	<p>
	<form method="get" action="$file_link">
	<button type="submit" class="button_compile_success">Download!</button>
	</form>
	</p>
	
	<br><br><br>
	
	<p>
	<font color="add8e6"><b><h3>Your RSC7 Header is: $libv_header</h3></b></font>
	</p>
	<br><br><br><br>
	
	</center>
	</td>
	</tr>
	
	</table>
	
	
	</center>
	</body>
	
	</html>	
	
EOT;



}



function parse_code($code_lines, $xsc_final_filename){
	
	$bytes = array(); //store final product in here to return
	
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
	while($i < count($code_lines)){
		
		$line = $code_lines[$i];
		
		if(substr_count($line, ":") > 0){//Label Declaration - leave alone for now
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
	
		switch($opcode){
			case "nop"://1
				$bytes[] = "00";
				break;
			case "Add"://1
				$bytes[] = "01";
				break;
			case "Sub"://1
				$bytes[] = "02";
				break;
			case "Mult"://1
				$bytes[] = "03";
				break;
			case "Div"://1
				$bytes[] = "04";
				break;
			case "Mod"://1
				$bytes[] = "05";
				break;
			case "Not"://1
				$bytes[] = "06";
				break;
			case "Neg"://1
				$bytes[] = "07";
				break;
			case "CmpEQ"://1
				$bytes[] = "08";
				break;
			case "CmpNE"://1
				$bytes[] = "09";
				break;
			case "CmpGT"://1
				$bytes[] = "0a";
				break;
			case "CmpGE"://1
				$bytes[] = "0b";
				break;
			case "CmpLT"://1
				$bytes[] = "0c";
				break;
			case "CmpLE":
				$bytes[] = "0d";
				break;
			case "fAdd"://1
				$bytes[] = "0e";
				break;
			case "fSub"://1
				$bytes[] = "0f";
				break;
			case "fMul"://1
				$bytes[] = "10";
				break;
			case "fDiv"://1
				$bytes[] = "11";
				break;
			case "fMod"://1
				$bytes[] = "12";
				break;
			case "fNeg"://1
				$bytes[] = "13";
				break;
			case "FCmpEQ"://1
				$bytes[] = "14";
				break;
			case "FCmpNE"://1
				$bytes[] = "15";
				break;
			case "FCmpGT"://1
				$bytes[] = "16";
				break;
			case "FCmpGE"://1
				$bytes[] = "17";
				break;
			case "FCmpLT"://1
				$bytes[] = "18";
				break;
			case "FCmpLE"://1
				$bytes[] = "19";
				break;
			case "vAdd"://1
				$bytes[] = "1a";
				break;
			case "vSub"://1
				$bytes[] = "1b";
				break;
			case "vMul"://1
				$bytes[] = "1c";
				break;
			case "vDiv"://1
				$bytes[] = "1d";
				break;
			case "vNeg"://1
				$bytes[] = "1e";
				break;
			case "And"://1
				$bytes[] = "1f";
				break;
			case "Or"://1
				$bytes[] = "20";
				break;
			case "Xor"://1
				$bytes[] = "21";
				break;
			case "ItoF"://1
				$bytes[] = "22";
				break;
			case "FtoI"://1
				$bytes[] = "23";
				break;
			case "Dup2"://1
				$bytes[] = "24";
				break;
			case "Push1"://2
				$bytes[] = "25";
				$temp[0] = dechex($line_parts[1]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				break;
			case "Push2"://3
				$bytes[] = "26";
				$temp[0] = dechex($line_parts[1]);
				$temp[1] = dechex($line_parts[2]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				$bytes[] = str_pad($temp[1], 2, '0', STR_PAD_LEFT);
				break;
			case "Push3"://4
				$bytes[] = "27";
				$temp[0] = dechex($line_parts[1]);
				$temp[1] = dechex($line_parts[2]);
				$temp[2] = dechex($line_parts[3]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				$bytes[] = str_pad($temp[1], 2, '0', STR_PAD_LEFT);
				$bytes[] = str_pad($temp[2], 2, '0', STR_PAD_LEFT);
				break;
			case "Push"://5
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
			case "fPush"://5
				$bytes[] = "29";
				//Convert to 32 bit hex. Every 2 chars into array key
				$temp[0] = Float2Hex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 8, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				$bytes[] = $arr[2];
				$bytes[] = $arr[3];
				unset($arr);
				break;
			case "Dup"://1
				$bytes[] = "2a";
				break;
			case "Drop"://1
				$bytes[] = "2b";
				break;
			case "CallNative"://4 (special)
				$bytes[] = "2c";
				//Get Native Hex Hash
				if(substr_count($line_parts[1], "UNK_") > 0){//if it's a UNK_
					$hash = str_replace('"', '', str_replace("UNK_", "", $line_parts[1]));
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
			case "Function"://5
				$bytes[] = "2d";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$temp[1] = str_pad(dechex($line_parts[2]), 4, '0', STR_PAD_LEFT);
				$temp[2] = str_pad(dechex($line_parts[3]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				$bytes[] = substr($temp[1], 0, 2);
				$bytes[] = substr($temp[1], 2, 2);
				$bytes[] = $temp[2];
				break;
			case "Return"://3
				$bytes[] = "2e";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$temp[1] = str_pad(dechex($line_parts[2]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				$bytes[] = $temp[1];
				break;
			case "pGet"://1
				$bytes[] = "2f";
				break;
			case "pSet"://1
				$bytes[] = "30";
				break;
			case "pPeekSet"://1
				$bytes[] = "31";
				break;
			case "ToStack"://1
				$bytes[] = "32";
				break;
			case "FromStack"://1
				$bytes[] = "33";
				break;
			case "ArrayGetP1"://2
				$bytes[] = "34";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "ArrayGet1"://2
				$bytes[] = "35";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "ArraySet1"://2
				$bytes[] = "36";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "pFrame1"://2
				$bytes[] = "37";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "getF1"://2
				$bytes[] = "38";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "setF1"://2
				$bytes[] = "39";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "pStatic1"://2
				$bytes[] = "3a";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "StaticGet1"://2
				$bytes[] = "3b";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "StaticSet1"://2
				$bytes[] = "3c";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "Add1"://2
				$bytes[] = "3d";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "Mult1"://2
				$bytes[] = "3e";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "GetStackImmediateP"://1
				$bytes[] = "3f";
				break;
			case "GetImmediateP1"://2
				$bytes[] = "40";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "GetImmediate1"://2
				$bytes[] = "41";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "SetImmediate1"://2
				$bytes[] = "42";
				$temp[0] = str_pad(dechex($line_parts[1]), 2, '0', STR_PAD_LEFT);
				$bytes[] = $temp[0];
				break;
			case "PushS"://3
				$bytes[] = "43";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "Add2"://3
				$bytes[] = "44";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "Mult2"://3
				$bytes[] = "45";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "GetStackImmediateP2"://3
				$bytes[] = "46";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "GetImmediate2"://3
				$bytes[] = "47";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "SetImmediate2"://3
				$bytes[] = "48";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "ArrayGetP2"://3
				$bytes[] = "49";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "ArrayGet2"://3
				$bytes[] = "4a";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "ArraySet2"://3
				$bytes[] = "4b";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "pFrame2"://3
				$bytes[] = "4c";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "getF2"://3
				$bytes[] = "4d";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "SetF2"://3
				$bytes[] = "4e";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "pStatic2"://3
				$bytes[] = "4f";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "StaticGet2"://3
				$bytes[] = "50";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "StaticSet2"://3
				$bytes[] = "51";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "pGlobal2"://3
				$bytes[] = "52";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "globalGet2"://3
				$bytes[] = "53";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "globalSet2"://3
				$bytes[] = "54";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 4, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				unset($arr);
				break;
			case "Jump"://2 special
				$bytes[] = "55";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "JumpFalse"://2 special
				$bytes[] = "56";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "JumpNE"://2 special
				$bytes[] = "57";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "JumpEQ"://2 special
				$bytes[] = "58";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "JumpLE"://2 special
				$bytes[] = "59";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "JumpLT"://2 special
				$bytes[] = "5a";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "JumpGE"://2 special
				$bytes[] = "5b";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "JumpGT"://2 special
				$bytes[] = "5c";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				break;
			case "Call"://3 special
				$bytes[] = "5d";
				$bytes[] = $line_parts[1];
				$bytes[] = "";
				$bytes[] = "";
				break;
			case "pGlobal3"://4
				$bytes[] = "5e";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 6, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				$bytes[] = $arr[2];
				unset($arr);
				break;
			case "globalGet3"://4
				$bytes[] = "5f";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 6, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				$bytes[] = $arr[2];
				unset($arr);
				break;
			case "globalSet3"://4
				$bytes[] = "60";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 6, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				$bytes[] = $arr[2];
				unset($arr);
				break;
			case "pushI24"://4
				$bytes[] = "61";
				$temp[0] = dechex($line_parts[1]);
				$arr = array();
				$arr = str_split(str_pad($temp[0], 6, '0', STR_PAD_LEFT), 2);
				$bytes[] = $arr[0];
				$bytes[] = $arr[1];
				$bytes[] = $arr[2];
				unset($arr);
				break;
			case "Switch"://* (special)
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
			case "PushString"://1 (special)
				//Get string hex
				$hex = String_to_Hex($line_parts[1]);
				echo "String: $line_parts[1], ";
				//Get string start offset
				if(array_search($hex, $string_storage) !== false){
					//If string is already in string section...
					$temp = array_search($hex, $string_storage);
					$offset = $string_offset_storage[$temp];
					echo "Offset: $offset<br />";
					unset($temp);
				}else{//Get string offset. Store offset and string in storage arrays
					//String is unique. Get offset and add to string sect
					$offset = count($string_sect);
					$string_offset_storage[$s] = $offset;
					$string_storage[$s] = $hex;
					$s++;
					echo "Offset: $offset<br />";
					
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
			case "GetHash"://1
				$bytes[] = "64";
				break;
			case "StrCopy"://2
				$bytes[] = "65";
				$temp[0] = dechex($line_parts[1]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				break;
			case "ItoS"://2
				$bytes[] = "66";
				$temp[0] = dechex($line_parts[1]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				break;
			case "StrAdd"://2
				$bytes[] = "67";
				$temp[0] = dechex($line_parts[1]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				break;
			case "StrAddi"://2
				$bytes[] = "68";
				$temp[0] = dechex($line_parts[1]);
				$bytes[] = str_pad($temp[0], 2, '0', STR_PAD_LEFT);
				break;
			case "SnCopy"://1
				$bytes[] = "69";
				break;
			case "Catch"://1
				$bytes[] = "6a";
				break;
			case "Throw"://1
				$bytes[] = "6b";
				break;
			case "pCall"://1
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
			case "fPush_-1"://1
				$bytes[] = "76";
				break;
			case "fPush_0.0"://1
				$bytes[] = "77";
				break;
			case "fPush_1.0"://1
				$bytes[] = "78";
				break;
			case "fPush_2.0"://1
				$bytes[] = "79";
				break;
			case "fPush_3.0"://1
				$bytes[] = "7a";
				break;
			case "fPush_4.0"://1
				$bytes[] = "7b";
				break;
			case "fPush_5.0"://1
				$bytes[] = "7c";
				break;
			case "fPush_6.0"://1
				$bytes[] = "7d";
				break;
			case "fPush_7.0"://1
				$bytes[] = "7e";
				break;
			case "unk_op"://1
				$bytes[] = str_pad(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $line_parts[1]), 2, '0', STR_PAD_LEFT);
				break;
			default:
				$error = "Error! Op is $opcode??";
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
	while($m < count($bytes)){
		if(substr_count($bytes[$m], "@") > 0){
			//Array key contains @Label
			$key = array_search(preg_replace('/\s+/', '',$bytes[$m]), $label_decs);
			$one_up = $m + 1;
			$two_up = $m + 2;
			if($key === false){//If it jumps to a non-existent label...
				if($bytes[$one_up] == "" && $bytes[$two_up] == ""){
					//Call to non-existing label. just put FF. extend to 6 bytes
					echo "<b>Hit a Call FF. Label: $bytes[$m]</b><br />";
					$bytes[$m] = "FF";
					$bytes[$one_up] = "FF";
					$bytes[$two_up] = "FF";
				}
				else if($bytes[$one_up] == "" && $bytes[$two_up] != ""){
					//Jump to non-existing label. just put FF. extend to 6 bytes
					echo "<b>Hit a Jump FF. Label: $bytes[$m]</b><br />";
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
	foreach($native_sect as $native){
		echo "Native: $native <br />";
	}
	
	//Create Native Sect
	$native_sect = implode("", $native_sect);
	$native_sect_length = strlen($native_sect) / 2;
	$native_count = strlen($native_sect) / 8;
	while((strlen($native_sect) / 2) % 16 != 0){
		$native_sect = $native_sect . "00";
	}
	
	
	
	//Create Header. Header is 80 bytes.
	$HeaderValues = array();
	$HeaderValues['magic'] = "34274500";
	$HeaderValues['unk1'] = "";//Pointer to 01 at end of file
	$HeaderValues['codepagesoffset'] = "";//Pointer to code pages at end of file
	$HeaderValues['globalsversion'] = "fdf69e36";
	$HeaderValues['codelength'] = str_pad(dechex($code_sect_length), 8, '0', STR_PAD_LEFT);
	$HeaderValues['parametercount'] = "00000000";
	$HeaderValues['staticscount'] = "00000000";
	$HeaderValues['globalscount'] = "00000000";
	$HeaderValues['nativecount'] = str_pad(dechex($native_count), 8, '0', STR_PAD_LEFT);
	$HeaderValues['staticsoffset'] = "00000000";
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
	$codeblocks = "50000050";
	while((strlen($codeblocks) / 2) < 16){
		$codeblocks = $codeblocks . "00";
	}
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
	
	//Make sure output is a multiple of 4,096 or 8,192 or 12,288, or 16,384
	$hex_length = strlen($hex) / 2;
	
	if($_POST['xsctype'] == "xsc"){
		//XSC RSC7 Headers
		
		if($hex_length <= 8000){
			//Extend hex to 8,192 bytes
			while(strlen($hex)/2 < 8192){
				$hex = $hex . "00";
				$rscheader['systemflag'] = "00020000";
			}
		}
		else if($hex_length > 8000 && $hex_length <= 16000){
			//Extend hex to 16,384 bytes
			while(strlen($hex)/2 < 16384){
				$hex = $hex . "00";
				$rscheader['systemflag'] = "00000800";
			}
		}
		else if($hex_length > 16000 && $hex_length <= 32000){
			//Extend hex to 32,768 bytes
			while(strlen($hex)/2 < 32768){
				$hex = $hex . "00";
				$rscheader['systemflag'] = "00000080";
			}
		}
		$file_ext = ".xsc";
	}
	else if($_POST['xsctype'] == "csc"){
	
		if($hex_length <= 8000){
			//Extend hex to 8,192 bytes
			while(strlen($hex)/2 < 8192){
				$hex = $hex . "00";
				$rscheader['systemflag'] = "00020000";
			}
		}
		else if($hex_length > 8000 && $hex_length <= 16000){
			//Extend hex to 16,384 bytes
			while(strlen($hex)/2 < 16384){
				$hex = $hex . "00";
				$rscheader['systemflag'] = "00000080";
			}
		}
		else if($hex_length > 16000 && $hex_length <= 32000){
			//Extend hex to 32,768 bytes
			while(strlen($hex)/2 < 16384){
				$hex = $hex . "00";
				$rscheader['systemflag'] = "00000080";
			}
		}
		$file_ext = ".csc";
	}
	else{
		echo "Invalid XSC Type !?!?";
		exit();
	}
	
	
	//Create RSC7 Header String Separate from file. Display on next page
	$rscheader['graphicsflag'] = "90000000";
	$libv_header = implode("", $rscheader);
	
	$storage_loc = "..//xscoutput/" . $xsc_final_filename . $file_ext;
	file_put_contents($storage_loc, pack('H*', $hex));
	
	
	draw_success_html($xsc_final_filename, $libv_header);
	
	
	//DEBUG CODE
	/*foreach($HeaderValues as $HeaderValue){
		echo "HeaderValue: $HeaderValue <br />";
	}
	
	echo "<br><br><br>";
	
	
	foreach($final_section as $value){
		echo "Final Section Value: $value <br />";
	}
	
	echo "<br><br><br>";*/
	
}



?>
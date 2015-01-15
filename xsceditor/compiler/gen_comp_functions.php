<?php


function Float2Hex($float){//single precision float to 32 bit hex
$float32 = pack("f", $float);
$binarydata32 =unpack('H*',$float32);

$reversed = implode("", $binarydata32);

$arr = str_split($reversed, 2);

$final = array();

$final[0] = $arr[3];
$final[1] = $arr[2];
$final[2] = $arr[1];
$final[3] = $arr[0];

$return = implode("", $final);
return $return;
}




function Dec_to_Hex($decimal){//signed decimal to hex

	/*dec = dechex($decimal);
	
	if($decimal < 0){
		$ret = substr($dec, 8);
	}
	else{
		$ret = $dec;
	}
	
	return $ret;*/
	return dechex($decimal);

}


function String_to_Hex($string){//Convert String to Hex
	//remove carriage returns
	$str = str_replace('"', '', $string);
	$str = str_replace("\r", "", $str);
	$str = str_replace("\n", "", $str);
	$str = str_replace("\r\n", "", $str);
	
	$hex = bin2hex($str);
	
	return $hex;

}


function Create_Push_Before_PushString($offset){
	
	$ret = array();
	
	if($offset <= 7){//If you can use push_0 - push_7
		switch($offset){
			case "0":
				$push_op = "6e";
				break;
			case "1":
				$push_op = "6f";
				break;
			case "2":
				$push_op = "70";
				break;
			case "3":
				$push_op = "71";
				break;
			case "4":
				$push_op = "72";
				break;
			case "5":
				$push_op = "73";
				break;
			case "6":
				$push_op = "74";
				break;
			case "7":
				$push_op = "75";
				break;
		}
		//Create return for Push_0 - Push_7
		$ret[] = $push_op;
	}
	else{//else, use Push1, PushS, or Push(32)
		if($offset <= 255){
			$push_op = "25";
			$bytes_pushed = str_pad(dechex($offset), 2, '0', STR_PAD_LEFT);
		}
		else if($offset > 255 && $offset <= 65535){
			$push_op = "43";
			$bytes_pushed = str_pad(dechex($offset), 4, '0', STR_PAD_LEFT);
		}
		else if($offset > 65535){
			$push_op = "28";
			$bytes_pushed = str_pad(Dec_to_Hex($offset), 8, '0', STR_PAD_LEFT);
		}
		//Create return array for Push1, PushS, and Push(32)
		$arr = array();
		$arr = str_split($bytes_pushed, 2);
		$ret[] = $push_op;
		foreach($arr as $thing){
			$ret[] = $thing;
		}
	}
	return $ret;
}







function Parse_Switch($switch){
	
	$parts = array();
	$parts = explode("]", $switch);
	array_pop($parts );
	array_filter($parts);
	
	$parts2 = array();
	foreach($parts as $part){
		$parts2[] = str_replace("[", "", $part);
	}
	
	$dec = array();
	foreach($parts2 as $part2){
		$temp = array();
		$temp = explode("=", $part2);
		$dec[] = $temp[0];
		$dec[] = $temp[1];
		unset($temp);
	}
	
	//Last value is always fucked up for some reason so just remove it
	array_filter($dec);
	
	$hex = array();
	
	//Find cases and jumps / convert to hex
	foreach($dec as $temp){
		if(strlen($temp) == 0){
			continue;
		}
		else if(substr_count($temp, "@") > 0){//Jump
			$hex[] = $temp;
		}
		else{//Case
			$hex[] = str_pad(dechex($temp), 8, '0', STR_PAD_LEFT);
		}
	}
	
	
	//create return array. 2 bytes per array key
	$ret = array();
	//Case/Jump pairs count
	$ret[] = str_pad(dechex(substr_count($switch, "]")), 2, '0', STR_PAD_LEFT);
	
	//Add all cases/jumps
	foreach($hex as $thing){
		if(substr_count($thing, "@") > 0){
			$ret[] = $thing;//Label
			$ret[] = "";
		}
		else{
			$temp = array();
			$temp = str_split($thing, 2);
			$ret[] = $temp[0];//2 bytes of case
			$ret[] = $temp[1];//2 bytes of case
			$ret[] = $temp[2];//2 bytes of case
			$ret[] = $temp[3];//2 bytes of case
		}
	}
	
return $ret;

}



function create_pointer_from_offset($pointer){
	
	$pointer = str_pad($pointer, 8, '0', STR_PAD_LEFT);
	$pointer = substr($pointer, 2);
	$pointer = "50" . $pointer;
	
	return $pointer;

}


function Signed_Dec_to_Hex($number)
{
    $hexvalues = array('0','1','2','3','4','5','6','7',
               '8','9','A','B','C','D','E','F');
    $hexval = '';
     while($number != '0')
     {
        $hexval = $hexvalues[bcmod($number,'16')].$hexval;
        $number = bcdiv($number,'16',0);
    }
    return $hexval;
}


function Int16toUint16($value)
{
 if ($value < 0)
 {
  $value += 65536;
 }
 return $value;
}

?>
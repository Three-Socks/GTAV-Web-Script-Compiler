<?php





/*

General Functions used in both Decompiling and Compiling

*/






function Hex_to_Dec($hexvalue){
	$return = null;
	$return = reset(unpack("l", pack("l", hexdec($hexvalue))));
	return $return;
}

function Hex_to_Text($hexvalue){
	$return = null;
	for($i=0;$i<strlen($hexvalue);$i+=2) $return .= chr(hexdec(substr($hexvalue,$i,2)));
	return $return;
}


function free_memory(){
	gc_collect_cycles();
}



function Uint16toInt16($value)
{
 if ($value > 32767)
 {
  $value -= 65536;
 }
 return $value;
}









?>
<?php

$natives = file_get_contents("RawNatives.txt");

$natives = str_replace("\r\n", "\n", $natives);

$natives = explode("\n", $natives);

$hash_array = array();
$dup_natives = array();

foreach ($natives as $native)
{
	$hash = hash("joaat", strtolower($native));
	
	if (isset($hash_array[$hash]))
		$dup_natives[] = $native;
		
	$hash_array[$hash] = $hash;
}

if (!empty($dup_natives))
{
	echo 'Error! Remove duplicate natives<br /><br />';

	foreach ($dup_natives as $dup)
		echo "Duplicate native: " . $dup . "<br />";
		
}
else
{
	file_put_contents("RawHashes.txt", implode("\n", $hash_array));
	echo 'Created hash file RawHashes.txt';
}

?>
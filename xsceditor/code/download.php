<?php

$file = isset($_GET['f']) ? $_GET['f'] : 0;

if (!empty($file))
{
	$file = trim(str_replace(array("\\", "/"), "", $file));
	
	if (file_exists($file))
	{
		header('Content-Type: application/octet-stream');
		header("Content-Disposition: attachment; filename=\"" . $file ."\"");
		readfile($file);
	}
}

?>
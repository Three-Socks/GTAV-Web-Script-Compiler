<?php

$file = isset($_GET['f']) ? $_GET['f'] : 0;
$get_sec_session = isset($_GET['s']) ? $_GET['s'] : 0;

if (!empty($file) && !empty($_GET['s']))
{
	session_start();
	$secure_session = sha1('qgT_%_2NW=P*:Pa-' . $file . session_id());

	if ($secure_session == $get_sec_session)
	{
		$file = trim(str_replace(array("\\", "/"), "", $file));

		$code_file_session = sha1('^d~61=7E48e.2-v-' . $file . session_id());

		$file_path = "/home/3s/source/code-store-5657/" . $code_file_session;
		
		if (file_exists($file_path))
		{
			header('Content-Type: application/octet-stream');
			header("Content-Disposition: attachment; filename=\"" . $file ."\"");
			readfile($file_path);
		}
		else
			file_put_contents('/home/3s/logs/download.txt', date("d/m/y - G:i:s") . ' - File does not exist - ' . $_SERVER["REMOTE_ADDR"] . " - " . $file . " (" . $file_path . ") - " . $get_sec_session . " (correct " . $secure_session . ")\n", FILE_APPEND);

	}
	else
		file_put_contents('/home/3s/logs/download.txt', date("d/m/y - G:i:s") . ' - Wrong get_sec_session - ' . $_SERVER["REMOTE_ADDR"] . " - " . $file . " - " . $get_sec_session . " (correct " . $secure_session . ")\n", FILE_APPEND);
}
else
	file_put_contents('/home/3s/logs/download.txt', date("d/m/y - G:i:s") . ' - Empty params - ' . $_SERVER["REMOTE_ADDR"] . " - " . $file . " - " . $get_sec_session . "\n", FILE_APPEND);


?>
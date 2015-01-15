<?php


//ini_set('display_errors', 'on');





/* Start of actual script */



if($_POST['file_to_delete'] != ''){
	$to_delete = $_SERVER['DOCUMENT_ROOT'] . "/xsceditor/xscuploads" . $_POST['file_to_delete'];
	unlink($to_delete);
	echo "$to_delete deleted";
}

$dir = $_SERVER['DOCUMENT_ROOT'] . "/xsceditor/xscuploads";

$files = scandir($dir);

echo <<<EOT

	<html>
	
	<head>
	<title>XSC Uploads Manager</title>
	<link rel="stylesheet" type="text/css" href="../general/style.css">
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,700italic,400,600,700' rel='stylesheet' type='text/css'>
	</head>
	
	
	<body background='../general/background.jpg'>
	
	<center>
	<nav class="menu slide-menu-left">
    <ul>
        <li><a href="../index.php">File Upload</a></li>
        <li><a href="../xscuploads/upload_manager.php">Manage Uploads</a></li>
        <li><a href="../secure/logview.php">View Logs</a></li>
		<li><button class="close-menu">&larr; Close</button></li>
    </ul>
	</nav>
	<button class="nav-toggler toggle-slide-left">Open Menu</button>
	<br><br>
	
	<h1><i>XSC Upload Manager</i></h1>
	
	<table align='center' width='50%' bgcolor='white'>
	
	<tr width=50% bgcolor='blue'>
	<center>
	<th><font color='white'>File</font></th>
	<th><font color='white'>Action</font></th>
	</center>
	</tr>

EOT;


foreach($files as $file){

	if($file == 'index.php' or $file == '' or $file == ' ' or $file == '.' or $file == '..'){
	continue;
	}
	
	echo "<tr width='50%'>";
	echo "<center>";
	
	echo "<td><center><b>" . $file . "</b></center></td>";
	
	echo <<<EOT
	
	<td>
	<center>
	
	<br>
	
	<form action="../viewer/viewer.php" method="post">
	<input type="hidden" name="uploadedfilename" value="$file">
	<input class="button_open_in_viewer" type="submit" value="Open in Viewer">
	</form>
	<form action="../editor/editor.php" method="post">
	<input type="hidden" name="uploadedfilename" value="$file">
	<input class="button_open_in_editor" type="submit" value="Open in Editor">
	</form>
	<form action="" method="post">
	<input type="hidden" name="file_to_delete" value="$file">
	<input class="button_delete" type="submit" value="Delete">
	</form>
	
	
	</center>
	</td>
	</center>
	</tr>
	
EOT;

}

if($files[0] == '.' && $files[1] == '..' && $files[2] == ''){
	echo <<<EOT
	
	<tr width='50%'>
	<center>
	
	<td><center> Nothing to see here! </center></td>
	
	<td>
	<center>
	
	</center>
	</td>
	</center>
	</tr>
	
EOT;
}

echo <<<EOT

</table>
<script src="../js/classie.js"></script>
<script src="../js/nav.js"></script>

</body>
</html>

EOT;













?>
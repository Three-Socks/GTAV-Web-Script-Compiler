<?php

//ini_set('display_errors', 'on');


///******Begin actual script code*****///

//This page is what appears when you type in http://hairyxsc.tk
//This page will let you upload a XSC file to be viewed on the next page
//If RSC7 header is present (16 byte LibertyV header) we will just ignore it in the viewer
//Either way, after all above steps have been completed, redirect to the xscviewer opening the XSC


//Check for submitted data first

if ($_POST["which_button_was_selected"] == ''){ //If no data was submitted, display form
	
	echo <<<EOT

	<html>
	<head>
	<title>File Upload Form</title>
	<link rel="icon" type="img/ico" href="favicon.ico">
	<link rel="stylesheet" type="text/css" href="general/style.css">
	</head>
	<body background='general/background.jpg'>
	<center>
	
	
	<br><br><br><br><br><br><br><br><br><br>
	
	<table width="50%" height="40%" background='general/table_bg.jpg'>
	
	<tr width="50%">
	<td width="25%">
	<center>
	<h2><b><font color='39b7cd'>Upload XSC File</font></b></h2>
	</center>
	</td>
	<td width="25%">
	<center>
	<h2><b><font color='39b7cd'>Or...</font></b></h2>
	</center>
	</td>
	</tr>
	
	<tr width="50%">
	<td width="25%">
	<center>
	<form action="" method="post" enctype="multipart/form-data" align='center'><br>
	<input type="file" name="uploadedxsc">
	<input type="hidden" name="which_button_was_selected" value="upload">
	<br><br><br>
	<input class="button_upload" type="submit" value="Upload!">
	</form>
	</center>
	</td>
	
	<td width="25%">
	<center>
	<form method="link" action="xscuploads/">
	<input class="button_select_from_uploads" type="submit" value="Select from Uploads">
	</form>
	
	<form method="link" action="editor/editor.php">
	<input class="button_create_new" type="submit" value="Create New">
	</form>
	</center>
	</td>
	
	</tr>
	</table>
	
	
	
	
	
	</center>
	</body>
	</html>
EOT;

}
else if ($_POST["which_button_was_selected"] == "upload"){  //File was uploaded
	
	
	echo <<<EOT
	
	<html>
	
	<head>
	<title>Upload a XSC</title>
	<link rel="icon" type="img/ico" href="favicon.ico">
	<link rel="stylesheet" type="text/css" href="general/style.css">
	</head>
	
	<body background='general/background.jpg'>
	
	
	<center>
	<br><br><br><br><br><br><br><br>
	
	<table width="20%" height="40%" background='general/table_bg.jpg'>
	<tr width="25%">
	<td width="25%">
	<center>
	
EOT;
	
	//This will control what extensions, etc are allowed
	$allowedExts = array("xsc", "csc");
	$temp = explode(".", $_FILES["uploadedxsc"]["name"]);
	$extension = end($temp);
	
	if (($_FILES["uploadedxsc"]["type"] == "application/octet-stream")
	&& ($_FILES["uploadedxsc"]["size"] < 1200000000)
	&& in_array($extension, $allowedExts)) {
	if ($_FILES["uploadedxsc"]["error"] > 0) {
		echo "Error: " . $_FILES["uploadedxsc"]["error"] . "<br>";
	} else {
		uploadokay: //Upload Okay marker - just for Zero lol
		echo "<p>";
		echo "<b><font color='39b7cd'>File:</font></b><i> <font color='bcc6cc'>" . $_FILES["uploadedxsc"]["name"] . "</font></i><br>";
		echo "<b><font color='39b7cd'>Size:</font></b><i> <font color='bcc6cc'>" . ($_FILES["uploadedxsc"]["size"] / 1024) . " kB</font></i><br>";
		if($_FILES["uploadedxsc"]["size"] > 40000){
			echo "<br /><font color='ff69b4'>Warning: File is large so decompiling may take a while. Your browser may go unresponsive during this process...</font> <br>";
		}
		echo "</p>";
		
		//Move temp file to permanent location
		move_uploaded_file($_FILES["uploadedxsc"]["tmp_name"], "xscuploads/" . $_FILES["uploadedxsc"]["name"]);
		$uploadedfilename = $_FILES["uploadedxsc"]["name"];
		
		echo <<<EOT
		
		<br />
		
		<form action="viewer/viewer.php" method="post"  enctype="multipart/form-data">
		<input type="hidden" name="uploadedfilename" value="$uploadedfilename">
		<input class="button_open_viewer" type="submit" value="Open XSC Viewer">
		</form>
		
		<br />
		
		<form action="editor/editor.php" method="post"  enctype="multipart/form-data">
		<input type="hidden" name="uploadedfilename" value="$uploadedfilename">
		<input class="button_open_editor" type="submit" value="Open XSC Editor">
		</form>
		
		</center>
		</td>
		</tr>
		</table>
		
		</center>
		</body>
		</html>
		
EOT;
		
	}
	} else {   //If this point is reached, the file is invalid for some reason
		
		if($_FILES["uploadedxsc"]["type"] != "application/octet-stream"){
			$reason = "Wrong filetype. File upload blocked for security reasons.";
		}
		else if($_FILES["uploadedxsc"]["size"] == ""){
			$reason = "No file selected. Please select a file to upload or choose 'Create New'";
		}
		else if($_FILES["uploadedxsc"]["size"] >= 1200000000){
			$reason = "File is larger than the allowed 800 MB. Upload cancelled.";
		}
		else{ $reason = "You're gay"; }
		
		echo "<br><br><br><font color='39b7cd'>Error:</font> <font color='bcc6cc'>$reason</font>";
		
		echo <<<EOT
		<br><br><br>
		<form><input class="button_choose_another" type="button" value="Back" onClick="history.go(-1);return true;"></form>
		
		</center>
		</body>
		</html>
EOT;
	}

}
	
else if ($_POST["which_button_was_selected"] == 'createnew'){
	
	//Go to xsc compiler
	echo "Create new chosen";
	
}
else{ echo "Error - createnew/upload blank!"; }
















?>
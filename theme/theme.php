<?php

function HTML_Start_Display()
{
	global $installUrl;

	echo '<!DOCTYPE html>
	<html lang="en">
		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="description" content="">
			<meta name="author" content="">
			<link rel="icon" href="favicon.ico">

			<title>CSC/XSC Compiler</title>

			<link rel="icon" type="img/ico" href="' . $installUrl . '/favicon.ico">

			<!-- Bootstrap core CSS -->
			<link href="' . $installUrl . '/theme/bootstrap-dist/css/bootstrap.min.css" rel="stylesheet">
			<!-- Bootstrap theme -->
			<link href="' . $installUrl . '/theme/bootstrap-dist/css/bootstrap-theme.min.css" rel="stylesheet">

			<!-- Custom styles for this template -->
			<link href="' . $installUrl . '/theme/theme.css" rel="stylesheet">
			
			<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
			<!--[if lt IE 9]>
				<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
				<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
			<![endif]-->
		</head>

		<body role="document">
			<!-- Fixed navbar -->
			<nav class="navbar navbar-inverse navbar-fixed-top">
				<div class="container">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="' . $installUrl . '/">CSC/XSC Decompiler/Compiler</a>
					</div>
					<div id="navbar" class="navbar-collapse collapse">
						<ul class="nav navbar-nav">
							<li class="active"><a href="' . $installUrl . '/">Home</a></li>
						</ul>
					</div><!--/.nav-collapse -->
				</div>
			</nav>

			<div class="container" role="main">
';

}

function HTML_Script_Info_Section($HeaderValues)
{
	global $string_pages_count;
	global $code_pages_count;

	echo '
				<div class="page-header">
					<h1>Script Info</h1>
				</div>
				<div class="row">

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Script Name</dt>
							<dd>' . $HeaderValues['filename'] . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Magic</dt>
							<dd>' .  bin2hex($HeaderValues['magic']) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Globals Version</dt>
							<dd>' . strtoupper(bin2hex($HeaderValues['globalsversion'])) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Filesize</dt>
							<dd>' . number_format($HeaderValues['filesize']) . ' <i>bytes</i></dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Code Pages</dt>
							<dd>' . $code_pages_count . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>String Pages</dt>
							<dd>' . $string_pages_count . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Native Pages</dt>
							<dd>1</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Code Size</dt>
							<dd>' . number_format($HeaderValues['codelength']) . ' <i>bytes</i></dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>String Size</dt>
							<dd>' . number_format($HeaderValues['stringssize']) . ' <i>bytes</i></dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Natives Count</dt>
							<dd>' . number_format($HeaderValues['nativescount']) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Parameter Count</dt>
							<dd>' . number_format($HeaderValues['parametercount']) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Globals Count</dt>
							<dd>' . number_format($HeaderValues['globalscount']) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

					<div class="col-sm-3">
						<dl class="dl-horizontal">
							<dt>Statics Count</dt>
							<dd>' . number_format($HeaderValues['staticscount']) . '</dd>
						</dl>
					</div><!-- /.col-sm-3 -->

				</div>';
}

function HTML_Upload_Section($html_return)
{
	echo '
			<div class="col-md-5">
				<div class="page-header">
					<h1>Decompile</h1>
				</div>
				<form action="" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<div style="float:left;width:49%;">
							<label for="upload_script">Script input</label>
							<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
							<input type="file" id="upload_script" name="upload_script">
							<p class="help-block">*.csc, *xsc</p>
							<div class="checkbox">
								<label for="save_code">
								<input type="checkbox" id="save_code" name="save_code" value="1">
										Save the decompiled code instead of outputting.
								</label>
							</div>
						</div>
						<div class="clearfix"></div>
						<br />
						<button type="submit" class="btn btn-default">Decompile</button>
						<br />
						<br />';
						
						if (isset($_FILES['upload_script']) && !empty($html_return))
							echo $html_return;
						
						echo '
					</div>
				</form>
			</div>';

/*			<div class="col-md-4">
				<div class="page-header">
					<h1>Statics Editor</h1>
				</div>
				<form action="" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label for="upload_script_statics">Script input</label>
						<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
						<input type="file" id="upload_script_statics" name="upload_script_statics">
						<p class="help-block">*.csc, *xsc</p>
						<br />
						<button type="submit" class="btn btn-default">Edit</button>
						<br />
						<br />';
						
						if (isset($_POST['statics_edit_action']) && !empty($html_return))
							echo $html_return;
						
						echo '
					</div>
				</form>
			</div>
*/
	echo '
			<div class="col-md-6">
				<div class="page-header">
					<h1>Compile</h1>
				</div>
				<form action="" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<div style="float:left;width:49%;">
							<label for="upload_script_template">Script static template input</label>
							<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
							<input type="file" id="upload_script_template" name="upload_script_template">
							<p class="help-block">*.csc, *xsc</p>
						</div>
						<div style="float:right;width:49%;">
							<label for="upload_code">Code input</label>
							<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
							<input type="file" id="upload_code" name="upload_code">
							<p class="help-block">*.csa, *xsa</p>
						</div>
						<div class="clearfix"></div>
						<br />
						<button type="submit" class="btn btn-default">Compile</button>
						<br />
						<br />';
						
						if (isset($_FILES['upload_code']) && !empty($html_return))
							echo $html_return;
						
						echo '
					</div>
				</form>
			</div>
';

}

function HTML_Code_Section($HeaderValues)
{
	echo '
				<div class="page-header">
					<h1>Code - ' . number_format($HeaderValues['codelength']) . ' <i>bytes</i></h1>
				</div>';
}

function HTML_Code_download($code_filename, $time)
{
	echo  '<a class="btn btn-primary btn-lg active" role="button" href="' . $code_filename . '">Download Code</a>
					<p class="text-muted">Decompiled in ' . $time . ' seconds</p>';
}

function HTML_Code_textarea($decompiled_output, $time)
{
	echo '
				<div class="row">
					<form>
						<div class="form-group">
							<textarea id="script_code" class="form-control" rows="3" onfocus="var inp=this;setTimeout(function(){inp.select();},10);">' .$decompiled_output . '</textarea>
							<br />
							<p><button id="select-all-button" type="button" class="btn btn-primary" onfocus="var inp=document.getElementById(\'script_code\');setTimeout(function(){inp.select();},10);">Select All</button></p>
						</div>
					</form>
				<p class="text-muted">Decompiled in ' . $time . ' seconds</p>
				</div>';

}

function HTML_Errors($script_errors)
{
	echo '
				<textarea class="form-control" rows="20" spellcheck="false">

				--Errors found in script--
				
				';
	foreach($script_errors as $script_error)
		echo $script_error . "\n";

	echo "</textarea>\n" . "If this is a stock Rockstar script, then something went wrong. If this is a custom XSC file, then it probably means some sneaky shit was pulled to prevent decompiling... but it was still decompiled!";
}

function HTML_progress_bar()
{
	echo '
	<div class="progress">
		<div id="progress_bar" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
	</div>';

}

function HTML_Native_Section($script_sections, $HeaderValues, $raw_natives_name, $raw_hashes_name)
{
	$natives_count = number_format($HeaderValues['nativescount']);
	$text_natives_array = Get_Text_Natives($raw_natives_name, $raw_hashes_name, $script_sections);
	
	echo '
	<div class="col-md-4">
		<div class="page-header">
			<h1>Natives - ' . $natives_count . '</h1>
		</div>
		<textarea rows="20" cols="40" spellcheck="false">';
	
	foreach ($text_natives_array as $native)
		echo $native . "\n";
	
	echo '</textarea>
	</div>';

}

function HTML_String_Section($script_sections, $HeaderValues)
{
	$string_sect = $script_sections['string_sect'];
	
	$stringssize = number_format($HeaderValues['stringssize']);//Could be used for display, but we show # of strings instead
	$string_sect_length = strlen($string_sect);//Actually used for code
	
	$string_sect = str_replace("00000000000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("00000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000000000000000", "00", $string_sect);
	$string_sect = str_replace("00000000000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000000000", "00", $string_sect);
	$string_sect = str_replace("00000000000000", "00", $string_sect);
	$string_sect = str_replace("000000000000", "00", $string_sect);
	$string_sect = str_replace("0000000000", "00", $string_sect);
	$string_sect = str_replace("00000000", "00", $string_sect);
	$string_sect = str_replace("000000", "00", $string_sect);
	$string_sect = str_replace("0000", "00", $string_sect); //Above code just gets rid of blanks
	
	$number_of_strings_total = number_format(substr_count($string_sect, "00")); //Could be used to display approx # of strings

	echo '
	<div class="col-md-4">
		<div class="page-header">
			<h1>Strings -  ' . $stringssize . ' <i>bytes</i></h1>
		</div>
		<textarea rows="20" cols="40" spellcheck="false">';

	if ($string_sect == "No Strings Found In This Script"){ //This checks if string sect is null and just displays null text - yes, some scripts dont have string sections :?
		echo $string_sect;
		goto finishHTML;
	}
	
	$buffer = 0;
	
	while($buffer <= $string_sect_length){
	
		$byte_not_null = true;
		$i = 0;
		
		while($byte_not_null == true){
			$byte = substr($string_sect, $buffer, 2);
			if($byte == '00'){
				$byte_not_null = false;
				$string = Hex_to_Text(implode("", $bytes));
				echo "$string";
				echo "\n";
				unset($bytes);
				$string = null;
				$byte = null;
			}else{
				if($buffer >= $string_sect_length){
					goto breakloop;
				}
				$bytes[$i] = $byte;
				$byte = null;
				$i++;
			}
			$buffer = $buffer + '2';
		}
	}
	breakloop:
	
	$string_sect = null;
	
	//Finish HTML
	finishHTML:
	echo '</textarea>
	</div>';

}

function HTML_Statics_Section($script_sections, $HeaderValues)
{
	echo '
		<div class="col-md-4">
			<div class="page-header">
				<h1>Statics - ' . $HeaderValues['staticscount'] . '</h1>
			</div>
			<div style="height: 600px; overflow: auto;">
			<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th>#</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>';

	foreach ($script_sections['statics_sect'] as $index => $static_hex)
		echo '
							<tr>
								<th scope="row">' . $index . '</th>
								<td>' . hexdec($static_hex) . '</td>
							</tr>';

	echo '

						</tbody>
			</table>
			</div>
		</div>';
}

function HTML_Statics_Edit($statics_sect, $HeaderValues, $script_filename, $script_filename_ext)
{
	echo '
		<div class="col-md-4">
			<div class="page-header">
				<h1>Statics</h1>
			</div>
			<form action="" method="post">
			<button type="submit" class="btn btn-default">Update</button>
			<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th>#</th>
								<th class="text-center">Value</th>
							</tr>
						</thead>
						<tbody id="statics">';

	foreach ($statics_sect as $index => $static_hex)
		echo '
							<tr>
								<th scope="row">' . $index . '</th>
								<td align="center">
									<div style="width: 80px;">
										<input id="statics_input_' . $index . '" name="statics_input_' . $index . '" type="number" class="form-control" value="' . hexdec($static_hex) . '">
									</div>
								</td>
							</tr>';

	echo '

						</tbody>
			</table>
			<div style="float:right;">
				<input id="statics_add" class="btn btn-default" type="button" value="Add">
				<input id="statics_delete" class="btn btn-default" type="button" value="Delete">
			</div>
			<br />
			<input type="hidden" name="statics_edit_action" value="1">
			<input type="hidden" name="script_filename" value="' . $script_filename . '">
			<input type="hidden" name="script_filename_ext" value="' . $script_filename_ext . '">
			<button type="submit" class="btn btn-default">Update</button>
			</form>
		</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script type="text/javascript">
			$( document ).ready(function() {
				var statics_count = ' . count($statics_sect) . ';
				$( "#statics_add" ).click(function() {
					$( "#statics" ).append( \'<tr><th scope="row">\' + statics_count + \'</th><td align="center"><div style="width: 80px;"><input id="statics_input_\' + statics_count + \'" name="statics_input_\' + statics_count + \'" type="number" class="form-control" value="0"></div></td></tr>\');
					statics_count++;
				});
				$( "#statics_delete" ).click(function() {
					if (statics_count != 0)
					{
						$("#statics tr:last").remove();
						statics_count--;
					}
				});
			});
		</script>';
}

function HTML_End_Display()
{
	global $installUrl;

	echo '
    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="' . $installUrl . '/theme/bootstrap-dist/js/bootstrap.min.js"></script>
  </body>
</html>';
}
<?php
include($_SERVER['DOCUMENT_ROOT']."/classes/upload/upload_class.php"); //classes is the map where the class file is stored (one above the root)
$folder = $_SERVER['DOCUMENT_ROOT']."/files/";
error_reporting(E_ALL);
function select_files($dir) {
	// removed in ver 1.01 the globals 
	$teller = 0;
	if ($handle = opendir($dir)) {
		$mydir = "<p>These are the files in the directory:</p>\n";
		$mydir .= "<form name=\"form1\" method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">\n";
		$mydir .= "  <select name=\"file_in_folder\">\n";
		$mydir .= "    <option value=\"\" selected>...\n";
		while (false !== ($file = readdir($handle))) {
			$files[] = $file;
		}
		closedir($handle); 
		sort($files);
		foreach ($files as $val) {
			if (is_file($dir.$val)) { // show only real files (ver. 1.01)
				$mydir .= "    <option value=\"".$val."\">";
				$mydir .= (strlen($val) > 30) ? substr($val, 0, 30)."...\n" : $val."\n";
				$teller++;	
			}
		}
		$mydir .= "  </select>";
		$mydir .= "<input type=\"submit\" name=\"download\" value=\"Download\">";
		$mydir .= "</form>\n";
	}
	if ($teller == 0) {
		echo "No files!";
	} else { 
		echo $mydir;
	}
}
if (isset($_POST['download'])) {
	$fullPath = $folder.$_POST['file_in_folder'];
	if ($fd = fopen ($fullPath, "rb")) {
		$fsize = filesize($fullPath);
		$path_parts = pathinfo($fullPath); 
		$ext = strtolower($path_parts["extension"]); 
		switch ($ext) {
			case "png":
			case "bmp":
			case "gif":
			header("Content-type: image/".$ext.""); 
			header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\"");
			break;
			case "pdf":
			header("Content-type: application/pdf");
			header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); 
			break;
			case "zip":
			header("Content-type: application/zip"); 
			header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
			break;
			default;
			header("Content-type: application/octet-stream");
			header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
		}
		header("Content-length: $fsize");
		header("Cache-control: private"); 
		while(!feof($fd)) {
			$buffer = fread($fd, 2048);
			echo $buffer;
		}
	}
	fclose ($fd);
	exit;
}
function del_file($file) {
	$delete = @unlink($file); 
	clearstatcache();
	if (@file_exists($file)) { 
		$filesys = eregi_replace("/","\\",$file); 
		$delete = @system("del $filesys");
		clearstatcache();
		if (@file_exists($file)) { 
			$delete = @chmod ($file, 0775); 
			$delete = @unlink($file); 
			$delete = @system("del $filesys");
		}
	}
}
function get_oldest_file($directory) {
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if (is_file($directory.$file)) { // add only files to the array (ver. 1.01)
				$files[] = $file;
			}
		}
		if (count($files) <= 12) {
			return;
		} else {
			foreach ($files as $val) {
				if (is_file($directory.$val)) {
					$file_date[$val] = filemtime($directory.$val);
				}
			}
		}
	}
	closedir($handle);
	asort($file_date, SORT_NUMERIC);
	reset($file_date);
	$oldest = key($file_date);
	return $oldest;
}

$max_size = 1024*80; // the max. size for uploading
$my_upload = new file_upload;

$my_upload->upload_dir = $_SERVER['DOCUMENT_ROOT']."/files/"; // "files" is the folder for the uploaded files (you have to create this folder)
$my_upload->extensions = array(".png", ".zip", ".pdf", ".gif", ".bmp"); // specify the allowed extensions here

		
if(isset($_POST['Submit'])) {
	$my_upload->the_temp_file = $_FILES['upload']['tmp_name'];
	$my_upload->the_file = $_FILES['upload']['name'];
	$my_upload->http_error = $_FILES['upload']['error'];
	$my_upload->replace = (isset($_POST['replace'])) ? $_POST['replace'] : "n";
	$my_upload->do_filename_check = (isset($_POST['check'])) ? $_POST['check'] : "n"; 
	if ($my_upload->upload()) {
		$latest = get_oldest_file($folder);
		del_file($folder.$latest);
	}
}
		
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Demo: File upload/download and open directory</title>

<style type="text/css">
<!--
body {
	font-family: Arial, Helvetica, sans-serif;
	text-align:center;
}
p {
	font-size: 14px;
	line-height: 20px;
}
label {
	font: 14px/20px Arial, Helvetica, sans-serif;
	margin-top: 5px 0 0;
	float:left;
	display:block;
	width:120px;
}
#main {
	width:350px;
	margin:0 auto;
	padding:10px;
	text-align:left;
	border: 1px solid #000000;
}
input {
	margin-left:5px;
}
-->
</style>
</head>

<body>
<div id="main">
  <h2 style="text-align:center;margin-top:10px;">Demo page:</h2>
  <p align="center">(File upload/download and open directory)</p>
  <p>Max. filesize: <b><?php echo $max_size/1024; ?> KB</b><br>
  <?php 
  $ext = "Allowed extensions are: <b>";
  foreach ($my_upload->extensions as $val) {
	  $ext .=  ltrim($val, ".").", ";
  } 
  echo rtrim($ext, ", ")."</b>";
  ?>
  </p>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" name="form1">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_size; ?>">
	<center><input type="file" name="upload" id="upload" size="25"></center>
	<label for="replace">Replace?</label>
	<input type="checkbox" name="replace" id="replace" value="y"><br clear="all">
	<label for="check">Validate filename ?</label>
	<input name="check" type="checkbox" id="check" value="y" checked>
	<br clear="all">
	<center><input type="submit" name="Submit" id="Submit" value="Submit"></center>
  </form>
  <p style="margin-top:20px;color:#FF0000;"><?php echo $my_upload->show_error_string(); ?></p>
  <?php echo select_files($folder); ?>
  <p style="margin-top:40px;">Find more information about the directory and download function on: <a href="http://www.finalwebsites.com/snippets.php" target="_blank">www.finalwebsites.com</a></p>
</div>  
</body>
</html>
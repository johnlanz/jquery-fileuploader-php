<?php
include ($_SERVER['DOCUMENT_ROOT']."/classes/upload/upload_class.php"); //classes is the map where the class file is stored (one above the root)

$max_size = 1024*250; // the max. size for uploading
	
$my_upload = new file_upload;

$my_upload->upload_dir = $_SERVER['DOCUMENT_ROOT']."/files/new/"; // "files" is the folder for the uploaded files (you have to create this folder)
$my_upload->extensions = array(".png", ".zip", ".pdf"); // specify the allowed extensions here
// $my_upload->extensions = "de"; // use this to switch the messages into an other language (translate first!!!)
$my_upload->max_length_filename = 50; // change this value to fit your field length in your database (standard 100)
$my_upload->rename_file = true;
		
if(isset($_POST['Submit'])) {
	$my_upload->the_temp_file = $_FILES['upload']['tmp_name'];
	$my_upload->the_file = $_FILES['upload']['name'];
	$my_upload->http_error = $_FILES['upload']['error'];
	$my_upload->replace = (isset($_POST['replace'])) ? $_POST['replace'] : "n"; // because only a checked checkboxes is true
	$my_upload->do_filename_check = (isset($_POST['check'])) ? $_POST['check'] : "n"; // use this boolean to check for a valid filename
	$new_name = (isset($_POST['name'])) ? $_POST['name'] : "";
	if ($my_upload->upload($new_name)) { // new name is an additional filename information, use this to rename the uploaded file
		$full_path = $my_upload->upload_dir.$my_upload->file_copy;
		$info = $my_upload->get_uploaded_file_info($full_path);
		// ... or do something like insert the filename to the database
	}
}
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Upload example</title>
<style type="text/css">
<!--
label {
	float:left;
	display:block;
	width:120px;
}
input {
	float:left;
}
-->
</style>
</head>

<body>
<h3>File upload script:</h3>
<p>Max. filesize = <?php echo $max_size; ?> bytes.</p>
<form name="form1" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_size; ?>"><br>
  <label for="upload">Select a file...</label><input type="file" name="upload" size="30"><br clear="all">
  <label for="name">New name?</label><input type="text" name="name" size="20"> 
  (without extension!) <br clear="all">
  <label for="replace">Replace ?</label><input type="checkbox" name="replace" value="y"><br clear="all">
  <label for="check">Validate filename ?</label><input name="check" type="checkbox" value="y" checked><br clear="all">
  <input style="margin-left:120px;" type="submit" name="Submit" value="Submit">
</form>
<br clear="all">
<p><?php echo $my_upload->show_error_string(); ?></p>
<?php if (isset($info)) echo "<blockquote>".nl2br($info)."</blockquote>"; ?> 
</body>
</html>
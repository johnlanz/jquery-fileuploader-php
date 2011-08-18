<?php
include ($_SERVER['DOCUMENT_ROOT']."/classes/upload/upload_class.php"); //classes is the map where the class file is stored (one above the root)

$max_size = 1024*250; // the max. size for uploading
	
$my_upload = new file_upload;

$my_upload->upload_dir = $_SERVER['DOCUMENT_ROOT']."/files/new/"; // "files" is the folder for the uploaded files (you have to create this folder)
$my_upload->extensions = array(".png", ".zip", ".pdf", ".jpg"); // specify the allowed extensions here
// $my_upload->extensions = "de"; // use this to switch the messages into an other language (translate first!!!)
$my_upload->max_length_filename = 50; // change this value to fit your field length in your database (standard 100)
$my_upload->rename_file = true;


// You need to modify the settings below...
$conn = mysql_connect("localhost", "user", "pw") or die(mysql_error());
mysql_select_db("database", $conn) or die(mysql_error());

// the code to create the test table
mysql_query("
	CREATE TABLE IF NOT EXISTS file_table (
	id INT NOT NULL AUTO_INCREMENT,
	file_name VARCHAR( 100 ) NOT NULL,
	PRIMARY KEY (id))") or die(mysql_error());
		
if(isset($_POST['Submit'])) {
	$my_upload->the_temp_file = $_FILES['upload']['tmp_name'];
	$my_upload->the_file = $_FILES['upload']['name'];
	$my_upload->http_error = $_FILES['upload']['error'];
	$my_upload->replace = "y";
	$my_upload->do_filename_check = "n"; // use this boolean to check for a valid filename
	if ($my_upload->upload()) { // new name is an additional filename information, use this to rename the uploaded file
		mysql_query(sprintf("INSERT INTO file_table SET file_name = '%s'", $my_upload->file_copy));
	}
}
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Upload (database) example</title>
</head>

<body>
<h3>File upload script:</h3>
<p>This example is supposed to upload a file and store the name inside a database<br>
(you need to create a database to use this example). </p>
<p>Max. filesize = <?php echo $max_size; ?> bytes.</p>
<form name="form1" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_size; ?>">
  <?php echo $my_upload->create_file_field("upload", "Select a file...", 25, false); ?>
  <input type="submit" name="Submit" value="Submit">
</form>
<br clear="all">
<p><?php echo $my_upload->show_error_string(); ?></p>

<h3>The next file is an example how to show a file while using the method "create_file_field"</h3>
<p>Note the title from the file field if the file does not exist. There is no other function inside this exaple the showing the field.</p>
<form>
<?php echo $my_upload->create_file_field("file_in_db", "The file inside the upload class .zip file", 25, true, "Replace old file?", $_SERVER['DOCUMENT_ROOT']."/classes/upload/", "example_for_db.jpg", true, 30, "Delete image"); ?>
</form>
</body>
</html>
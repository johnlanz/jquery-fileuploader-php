<?php
include ($_SERVER['DOCUMENT_ROOT']."/classes/upload/upload_class.php"); //classes is the map where the class file is stored (one above the root)
//error_reporting(E_ALL);
$max_size = 1024*100; // the max. size for uploading

class muli_files extends file_upload {
	
	var $number_of_files = 0;
	var $names_array;
	var $tmp_names_array;
	var $error_array;
	var $wrong_extensions = 0;
	var $bad_filenames = 0;
	
	function extra_text($msg_num) {
		switch ($this->language) {
			case "de":
			// add you translations here
			break;
			default:
			$extra_msg[1] = "Error for: <b>".$this->the_file."</b>";
			$extra_msg[2] = "You have tried to upload ".$this->wrong_extensions." files with a bad extension, the following extensions are allowed: <b>".$this->ext_string."</b>";
			$extra_msg[3] = "Select at least on file.";
			$extra_msg[4] = "Select the file(s) for upload.";
			$extra_msg[5] = "You have tried to upload <b>".$this->bad_filenames." files</b> with invalid characters inside the filename.";
		}
		return $extra_msg[$msg_num];
	}
	// this method checkes the number of files for upload
	// this example works with one or more files
	function count_files() {
		foreach ($this->names_array as $test) {
			if ($test != "") {
				$this->number_of_files++;
			}
		}
		if ($this->number_of_files > 0) {
			return true;
		} else {
			return false;
		} 
	}
	function upload_multi_files () {
		$this->message = "";
		if ($this->count_files()) {
			foreach ($this->names_array as $key => $value) { 
				if ($value != "") {
					$this->the_file = $value;
					$new_name = $this->set_file_name();
					if ($this->check_file_name($new_name)) {
						if ($this->validateExtension()) {
							$this->file_copy = $new_name;
							$this->the_temp_file = $this->tmp_names_array[$key];
							if (is_uploaded_file($this->the_temp_file)) {
								if ($this->move_upload($this->the_temp_file, $this->file_copy)) {
									$this->message[] = $this->error_text($this->error_array[$key]);
									if ($this->rename_file) $this->message[] = $this->error_text(16);
									sleep(1); // wait a seconds to get an new timestamp (if rename is set)
								}
							} else {
								$this->message[] = $this->extra_text(1);
								$this->message[] = $this->error_text($this->error_array[$key]);
							}
						} else {
							$this->wrong_extensions++;
						}
					} else {
						$this->bad_filenames++;
					}
				} 
			}
			if ($this->bad_filenames > 0) $this->message[] = $this->extra_text(5);
			if ($this->wrong_extensions > 0) {
				$this->show_extensions();
				$this->message[] = $this->extra_text(2);
			}
		} else {
			$this->message[] = $this->extra_text(3);
		}
	}
}

$multi_upload = new muli_files;

$multi_upload->upload_dir = $_SERVER['DOCUMENT_ROOT']."/files/"; // "files" is the folder for the uploaded files (you have to create this folder)
$multi_upload->extensions = array(".png", ".zip"); // specify the allowed extensions here
$multi_upload->message[] = $multi_upload->extra_text(4); // a different standard message for multiple files
//$multi_upload->rename_file = true; // set to "true" if you want to rename all files with a timestamp value
$multi_upload->do_filename_check = "y"; // check filename ...
		
if(isset($_POST['Submit'])) {
	$multi_upload->tmp_names_array = $_FILES['upload']['tmp_name'];
	$multi_upload->names_array = $_FILES['upload']['name'];
	$multi_upload->error_array = $_FILES['upload']['error'];
	$multi_upload->replace = (isset($_POST['replace'])) ? $_POST['replace'] : "n"; // because only a checked checkboxes is true
	$multi_upload->upload_multi_files();
}
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>(multiple) upload example</title>

<style type="text/css">
<!--
label {
	width: 80px;
}
input {
	margin-bottom:3px;
	margin-left:5px;
}
-->
</style>
</head>

<body>
<h3>(multiple) File upload script:</h3>
<p>Max. filesize = <?php echo $max_size; ?> bytes. (each) </p>
<form name="form1" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_size; ?>">
  <label for="upload[]">File 1:</label>
  <input type="file" name="upload[]" size="30"><br>
  <label for="upload[]">File 2:</label>
  <input type="file" name="upload[]" size="30"><br>
  <label for="upload[]">File 3:</label>
  <input type="file" name="upload[]" size="30"><br>
  <!-- Add here more file fields if you need. -->
  Replace files? 
  <input type="checkbox" name="replace" value="y">
  <input type="submit" name="Submit" value="Submit">
</form>
<p><?php echo $multi_upload->show_error_string(); ?></p>
</body>
</html>
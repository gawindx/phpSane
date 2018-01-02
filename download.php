<?php
include("incl/functions.php");
include("incl/config.php");

if($do_file_download) {
	//create zip file
	$selected_file_paths = array();
	if (count($_REQUEST['selected_files']) > 1){
		foreach($_REQUEST['selected_files'] as $selected_file) {
			$selected_file_path = $save_dir . basename($selected_file);
			if(is_readable($selected_file_path)) {
				array_push($selected_file_paths, $selected_file_path);
			}
		}
		$zipfile_path =  $temp_dir . 'scanned_' . time() . '.zip';
		if(sizeof($selected_file_paths) > 0) {
			create_zip($selected_file_paths, $zipfile_path, true);
			if(is_readable($zipfile_path)) {
				//output path to created file
				echo "$zipfile_path";
			}
		}
	}else{
		$selected_file = $_REQUEST['selected_files'][0];
		$selected_file_path = $save_dir . basename($selected_file);
		if(sizeof($selected_file_path) > 0) {
			if(is_readable($selected_file_path)) {
				echo "$selected_file_path";
			}
		}
	}
}


/* creates a compressed zip file */
function create_zip($files = array(),$destination = '',$overwrite = false) {
	//if the zip file already exists and overwrite is false, return false
	if(file_exists($destination) && !$overwrite) { return false; }

	//vars
	$valid_files = array();
	//if files were passed in...
	if(is_array($files)) {
		//cycle through each file
		foreach($files as $file) {
			//make sure the file exists
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}
	}
	//if we have good files...
	if(count($valid_files)) {
		//create the archive
		$zip = new ZipArchive();
		if($overwrite) {
			if($zip->open($destination, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE ) !== true) {
				return false;
			}
		}else{
			if($zip->open($destination, ZIPARCHIVE::CREATE ) !== true) {
				return false;
			}
		}

		//add the files
		foreach($valid_files as $file) {
			$zip->addFile($file, basename($file));
		}

		//close the zip
		$zip->close();

		//check to make sure the file exists
		return file_exists($destination);
	}else{
		return false;
	}
}
?>

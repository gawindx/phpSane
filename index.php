<?php
/* phpSANE
// Version: 0.99.0-a2
// Gawindx <decauxnico@gmail.com>
//
// 0.x Branch developer's :
// John Walsh <john.walsh@mini-net.co.uk>
// Wojciech Bronisz <wojtek@bronisz.eu>
// David Fröhlich <?>
// moraumereu <?>
*/
$phpsane_version = '0.99.0-a2';

include('scripts/class.php');

/* Debug function
/ Send debug data to log file
/ necessary to declare the function before any other function
*/

function debug2log($dbg_msg) {
	global $Config;

	if($Config['path']['log_debug'])
	{
		$dbg_bt = debug_backtrace();
		$caller = array_shift($dbg_bt);
		$php_source = basename($caller['file']);
		$php_line = $caller['line'];
		$dbg_msg = '['.$php_source.':'.$php_line.']: '.$dbg_msg.".\n";
		$log_file = fopen($Config['path']['log_fname'], 'a');
		fwrite($log_file, $dbg_msg);
		fclose($log_file);
	}
}

include('scripts/functions.php');
include('scripts/language.php');
include('scripts/config.php');

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	header('Content-Type: text/plain');

 	// Don t forget the encoding
  	$method = $_POST['method'];

  	switch($method) {
		case 'get_all_devices':
        	$data = get_all_devices();
        	echo print_r($data, true);
        	break;
    	case 'get_preview':
			debug2log('received preview request with post data:'.print_r($_POST, true));
			$data = get_preview($_POST['preview_args']);
			echo print_r($data, true);
        	break;
    	case 'get_scan':
        	$data = get_scan($_POST['scan_args']);
			echo print_r($data, true);
        	break;
		case 'get_files':
			$data = get_files();
			echo print_r($data, true);
        	break;
		case 'get_params':
			$data = get_params();
			echo print_r($data, true);
			break;
		case 'get_languages':
			$data = get_languages();
			echo print_r($data, true);
			break;
	}
} else {
	include('scripts/frontpage.php');
}

?>
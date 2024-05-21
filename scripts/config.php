<?php
// Avoid direct access ; redirect to '/'
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    if (ob_get_contents()) ob_clean(); // ob_get_contents() even works without active output buffering
    header('Location: /');
    die;
}

$Config = array();
 
// CONFIG --------------------------------------------------------------
$Config['path'] = array();

$Config['path']['cache_dir'] 	= './cache/';									// Cache Directory (parent's dir for all
																				// others directories)
$Config['path']['log_dir'] 		= $Config['path']['cache_dir'].'log/';			// Log Directory
$Config['path']['config_file'] 	= $Config['path']['log_dir'].'config.cfg';		// Configuration file
$Config['path']['log_fname'] 	= $Config['path']['log_dir'].'log.txt';			// Debug File
$Config['path']['save_dir'] 	= $Config['path']['cache_dir'].'output/';		// Destination Directory for scanned files
$Config['path']['scanner_dir'] 	= $Config['path']['cache_dir'].'scanners/';		// Destination directory for storing and 
																				// reading scanner configuration files
$Config['path']['thumb_dir']	= $Config['path']['cache_dir'].'thumbnails/';	// Destination Directory for Thumbnails files
$Config['path']['temp_dir'] 	= $Config['path']['cache_dir'].'tmp/';			// Temporary Directory for storing preview files
$Config['path']['log_debug']	= true;											//

// system config
// =============

$Config['exe_path'] = array();

$Config['exe_path']['SCANIMAGE']	= '/usr/bin/scanimage';	//scanimage binary (sane)
$Config['exe_path']['GOCR']			= '/usr/bin/gocr';		//optional ocr binary
$Config['exe_path']['PDFUNITE']		= '/usr/bin/pdfunite';	//optional PDF merge binary
$Config['exe_path']['PNMTOJPEG']	= '/usr/bin/pnmtojpeg';	//netpbm pnm to jpeg conversion binary
$Config['exe_path']['PNMTOTIFF']	= '/usr/bin/pnmtotiff';	//netpbm pnm to tiff conversion binary
$Config['exe_path']['PNMTOBMP']		= '/usr/bin/ppmtobmp';	//netpbm ppm to bmp conversion binary
$Config['exe_path']['PNMTOPNG']		= '/usr/bin/pnmtopng';	//netpbm pnm to png conversion binary
$Config['exe_path']['CONVERT']		= '/usr/bin/convert';	//ImageMagick convert binary
$Config['exe_path']['IDENTIFY']		= '/usr/bin/identify';	//ImageMagick binary used to test for PDF support

if(php_uname('s') == 'FreeBSD') {
	// FreeBSD
	$Config['exe_path']['SCANIMAGE']	= '/usr/local/bin/scanimage';
	$Config['exe_path']['GOCR']			= '/usr/local/bin/gocr';
	$Config['exe_path']['PDFUNITE']		= '/usr/local/bin/pdfunite';
	$Config['exe_path']['PNMTOJPEG']	= '/usr/bin/pnmtojpeg';
	$Config['exe_path']['PNMTOTIFF']	= '/usr/local/bin/pnmtotiff';
	$Config['exe_path']['PNMTOBMP']		= '/usr/local/bin/ppmtobmp';
	$Config['exe_path']['PNMTOPNG']		= '/usr/local/bin/pnmtopng';
	$Config['exe_path']['CONVERT']		= '/usr/local/bin/convert';
	$Config['exe_path']['IDENTIFY']		= '/usr/local/bin/identify';
} else if(stripos(exec('uname -a'), 'synology') !== FALSE) {
	// Synology Disk Station
	$Config['exe_path']['SCANIMAGE']	= '/opt/bin/scanimage';
	$Config['exe_path']['GOCR']			= '/opt/bin/gocr';
	$Config['exe_path']['PDFUNITE']		= '/usr/local/bin/pdfunite';
	$Config['exe_path']['PNMTOJPEG']	= '/usr/local/netpbm/bin/pnmtojpeg';
	$Config['exe_path']['PNMTOTIFF']	= '/usr/local/netpbm/bin/pnmtotiff';
	$Config['exe_path']['PNMTOBMP']		= '/usr/local/netpbm/bin/ppmtobmp';
	$Config['exe_path']['PNMTOPNG']		= '/usr/local/netpbm/bin/pnmtopng';
	$Config['exe_path']['CONVERT']		= '/opt/bin/convert';
	$Config['exe_path']['IDENTIFY']		= '/opt/bin/identify';
}

// thumbanil size
$Config['thumbnail']['width'] = '200';
$Config['thumbnail']['height'] = '275';

$Do_Format = array(
	'pnm'	=> true,
	'txt' => true,
	'pdf' => true,
	'books' => true,
	'jpg'	=> true,
	'tif'	=> true,
	'bmp'	=> true,
	'png'	=> true,
);

$query_ocr = 'ls ' . $Config['exe_path']['GOCR'];
$query_convert = 'ls '. $Config['exe_path']['CONVERT'];
$query_identity = 'ls '. $Config['exe_path']['IDENTIFY'];
$query_pdf = $Config['exe_path']['IDENTIFY'] . ' -list Format | grep -i pdf';
$query_pdfbook = 'ls ' . $Config['exe_path']['PDFUNITE'];
$query_jpg = 'ls ' . $Config['exe_path']['PNMTOJPEG'];
$query_tiff = 'ls ' . $Config['exe_path']['PNMTOTIFF'];
$query_bmp = 'ls ' . $Config['exe_path']['PNMTOBMP'];
$query_png = 'ls ' . $Config['exe_path']['PNMTOPNG'];
$query_im_version = $Config['exe_path']['CONVERT'] . ' -version';

$Config['IM_legacy_version'] = true;
if (0 !== preg_match('/(version|v)?\s*((?:[0-9]+\.?\-?)+)/i', `$query_im_version`, $matches)) {
	preg_match_all('/(?:[0-9])+/', $matches[0], $IM_version);
	if ((intval($IM_version[0][0])) >= 7) {
		$Config['IM_legacy_version'] = false;
	}else{
		$Config['IM_legacy_version'] = true;
	}
}

$Config['Producer'] = 'phpSane';
$Config['Author'] = 'phpSane';

if(!`$query_ocr`) {
	$Do_Format['txt'] = false; //disable OCR when not available
	debug2log('OCR/txt Disabled');
}else{
	debug2log('OCR/txt Enabled');
}

if(!(`$query_convert` && `$query_identity` && `$query_pdf`)) {
	$Do_Format['pdf'] = false; //disable PDF when not available
	debug2log('PDF Disabled');
}else{
	debug2log('PDF Enabled');
}
if(!`$query_pdfbook`) {
	$Do_Format['do_append_pdf'] = false; //disable PDF books when merge tool is not available
	debug2log('PDF Books Disabled');
}else{
	debug2log('PDF Books Enabled');
}
if(!`$query_jpg`) {
	$Do_Format['jpg'] = false; //disable JPEG Format
	debug2log('JPEG Disabled');
}else{
	debug2log('JPEG Enabled');
}
if(!`$query_tiff`) {
	$Do_Format['tif'] = false; //disable TIFF Format
	debug2log('TIFF Disabled');
}else{
	debug2log('TIFF Enabled');
}

if(!`$query_bmp`) {
	$Do_Format['bmp'] = false; //disable BMP Format
	debug2log('BMP Disabled');
}else{
	debug2log('BMP Enabled');
}
if(!`$query_png`) {
	$Do_Format['png'] = false; //disable PNG Format
	debug2log('PNG Disabled');
}else{
	debug2log('PNG Enabled');
}

$Config['do_format'] = $Do_Format;
unset($Do_Format);
unset($query_ocr, $query_convert, $query_identity, $query_pdf,
	$query_pdfbook, $query_jpg, $query_tiff, $query_bmp, $query_png);

$Config['language'] = 'fr';

// TODO :
// Add possibility to load user config file and override default config
// include()

debug2log('System variable Initialized!');

// first verify if configs dir then create it if not exists
try {
	if (!is_dir($Config['path']['cache_dir'])) {
		mkdir($Config['path']['cache_dir']);
	}

	if (!is_dir($Config['path']['log_dir'])) {
		mkdir($Config['path']['log_dir']);
	}

	if (!is_dir($Config['path']['save_dir'])) {
		mkdir($Config['path']['save_dir']);
	}
	if (!is_dir($Config['path']['scanner_dir'])) {
		mkdir($Config['path']['scanner_dir']);
	}

	if (!is_dir($Config['path']['temp_dir'])) {
		mkdir($Config['path']['temp_dir']);
	}
	
	if (!is_dir($Config['path']['thumb_dir'])) {
		mkdir($Config['path']['thumb_dir']);
	}
} catch (Exception $e) {
	debug2log('Exception on create directory : ' . $e->getMessage() . '\n');
}


// Create empty log file if not exist
// log new instance if already exist

try {
	if ($log_debug) {
		if (!file_exists($Config['path']['log_fname'])) {
			$log_file = fopen($Config['path']['log_fname'], "w");
			fclose($log_file);
		}else{
			$log_file = fopen($Config['path']['log_fname'], "a");
			fwrite($log_file, "!!!New PhpSane Instance!!!\n");
			fclose($log_file);
		}
	}
} catch (Exception $e) {
    debug2log('Exception reçue : ',  $e->getMessage(), "\n");
}

debug2log('Log Debug Enabled!');

// set up a string to be prepended to the scanimage command, so that
// scanimage looks for devices at the ip making the request
// $SCAN_NET_SETUP = 'export SANE_NET_HOSTS='.$_SERVER['REMOTE_ADDR'].' && ';
$SCAN_NET_SETUP = '';
$SCANIMAGE_PATH = $SCAN_NET_SETUP . $Config['exe_path']['SCANIMAGE'] ;

$SaneCmd = new ScanImg($SCANIMAGE_PATH);

debug2log('Initialisation Succeeded');

?>

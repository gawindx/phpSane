<?php
// Avoid direct access ; redirect to '/'
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    if (ob_get_contents()) ob_clean(); // ob_get_contents() even works without active output buffering
    header('Location: /');
    die;
}

$Languages['en'] = array(
	'lang_name'				=> 'English',
	'device' 				=> 'Device',
	'scan'					=> 'Scan',
	'preview'				=> 'Preview',		
	'scan_area'				=> 'Scan area',
	'left'					=> 'Left',
	'top'					=> 'Top',
	'width'					=> 'Width',
	'height'				=> 'Height',
	'mm'					=> 'mm',
	'page_size'				=> 'Page size',
	'scan_options'			=> 'Scan options',
	'file_format'			=> 'File format',
	'file_name'				=> 'File name',
	'mode'					=> 'Mode',
	'brightness'			=> 'Brightness',
	'contrast'				=> 'Contrast',
	'resolution'			=> 'Resolution',
	'dpi'					=> 'DPI',
	'jpeg_image'			=> 'JPEG image',
	'pnm_image'				=> 'PNM image',
	'tiff_image'			=> 'TIFF image',
	'png_image'				=> 'PNG image',
	'bmp_image'				=> 'BMP image',
	'pdf_document'			=> 'PDF Document',
	'txt_document'			=> 'TXT Document',
	'source'				=> 'Source',
	'settings'				=> 'Settings',
	'language'				=> 'Language',
	'producer'				=> 'Producer',
	'author'				=> 'Author',
	/*
	"%",					// 7
	"Unknown",				// 8
	"preparing download.. please wait",		// 11
	"Are you sure you want to delete the selected files? This action cannot be undone!",	// 12
	"Error occurred while downloading files.",	// 13

	"24 bit color",			// 15
	"Grayscale",			// 16
	"Monochrome",			// 17

	array("en", "en_US", "en_US.UTF-8", "English_UnitedStates", "English"),		// 19
	"English",				// 20
	"Scanner device",		// 21

	
	"preview",				// 24
	"Reset",				// 25
	"Clean",				// 26
	"All",					// 27
	"None",					// 28
	"Documents",			// 29
	"Images",				// 30
	"Other",				// 31
	"ERROR",				// 32
	"No scanners were identified.<br>If you were expecting something different, check that the scanner is plugged in, turned on and detected by the sane-find-scanner tool (if appropriate).<br>Please read the documentation which came with this software (README, FAQ, manpages).",// 33
	"try again",			// 34
	"Right-click to save",	// 35
	"CLOSE",				// 36
	"Help",					// 37
	"Extra",				// 38
	"no scan command",		// 39
	"Page size",			// 40

	"Accept",				// 42

	"Scan another page?",	// 50
	"Type",					// 52
	"Size",					// 53
	"Date modified",		// 54
	"file",					// 55
	"Select file",			// 56
	"Custom size",			// 57
	"Download",				// 58
	"Delete",				// 59
	"scan_",				// 60
	"Source",				// 61
	"Flatbad",				// 62
	"ADF",				// 63*/
);

?>

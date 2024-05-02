<?php
// Avoid direct access ; redirect to '/'
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    if (ob_get_contents()) ob_clean(); // ob_get_contents() even works without active output buffering
    header('Location: /');
    die;
}

$Languages['fr'] = array(
	'lang_name'				=> 'Français',
	'device' 				=> 'Scanner',
	'scan'					=> 'Numériser',
	'preview'				=> 'Prévisualiser',		
	'scan_area'				=> 'Surface de numérisation',
	'left'					=> 'Gauche',
	'top'					=> 'Haut',
	'width'					=> 'Largeur',
	'height'				=> 'Hauteur',
	'mm'					=> 'mm',
	'page_size'				=> 'Taille de la Page',
	'scan_options'			=> 'Options de numérisation',
	'file_format'			=> 'Format de fichier',
	'file_name'				=> 'Nom de fichier',
	'mode'					=> 'Mode',
	'brightness'			=> 'Luminosité',
	'contrast'				=> 'Contraste',
	'resolution'			=> 'Résolution',
	'dpi'					=> 'DPI',
	'jpeg_image'			=> 'Image JPEG',
	'pnm_image'				=> 'Image PNM',
	'tiff_image'			=> 'Image TIFF',
	'png_image'				=> 'Image PNG',
	'bmp_image'				=> 'Image BMP',
	'pdf_document'			=> 'Document PDF',
	'txt_document'			=> 'Document TXT',
	'source'				=> 'Source',
	'settings'				=> 'Réglages',
	'language'				=> 'Langue',
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

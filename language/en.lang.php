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
);

?>

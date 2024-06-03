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
	'producer'				=> 'Producteur',
	'author'				=> 'Auteur',
);
?>

<?php

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="author" content="root">
<meta name="robots" content="noindex">
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>Save</title>
</head>
<body>';


include("incl/security.php");
include("incl/language.php");

$file_save = $_GET['file_save'];
$file_save_image = $_GET['file_save_image'];
$lang_id = $_GET['lang_id'];


if ($file_save_image) {
	echo "
	<p class='align_center'>
		<img src='".$file_save."' border='2'>";
} else {
	// my_pre my_mono
	echo "\t<p class='my_pre'>\n";
	include($file_save);
	echo "\n\t<hr>\n";
}

echo "
		<br><a href=\"$file_save\" target=\"_blank\">".$file_save."</a>
	</p>

	<p class='align_center'>
		".$lang[$lang_id][35]."
	</p>

	<p class='align_center'>
		<input type='button' name='close' value='".$lang[$lang_id][36]."' onClick='javascript:window.close();'>
	</p>

</body>
</html>\n";

?>

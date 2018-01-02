<?php
// CONFIG --------------------------------------------------------------

$temp_dir    = "./tmp/";                   //  temporary directory for storing preview files
$save_dir    = "./output/";                //  destination directory for scanned files
$scanner_dir = "./scanners/";              //  destination directory for storing and reading scanner configuration files
$save_type   = "link";                     //  link / popup

// set up a string to be prepended to the scanimage command, so that
// scanimage looks for devices at the ip making the request
// $SCAN_NET_SETUP = 'export SANE_NET_HOSTS='.$_SERVER['REMOTE_ADDR'].' && ';
$SCAN_NET_SETUP = '';


// user config
// ===========

// default language
// 0 = german
// 1 = english
// 2 = polish
// 3 = finnish
// 4 = russian
// 5 = ukrainian
// 6 = french
// 7 = dutch
$lang_id = 1;


// set your scanner maximum page size, and a low dpi for previews
$PREVIEW_WIDTH_MM   = 215;
$PREVIEW_HEIGHT_MM  = 297;
$PREVIEW_DPI        = 75;

// set the maximum scan size
$MAX_SCAN_WIDTH_MM  = 215;
$MAX_SCAN_HEIGHT_MM = 296.926;

// scale factor to map preview image -> scanner co-ords
$PREVIEW_SCALE = 2;

// set the preview image on-screen size
$PREVIEW_WIDTH_PX   = $PREVIEW_WIDTH_MM * $PREVIEW_SCALE;
$PREVIEW_HEIGHT_PX  = $PREVIEW_HEIGHT_MM * $PREVIEW_SCALE;

// set the list of page sizes to select from
// ref: page sizes in mm (http://en.wikipedia.org/wiki/Paper_size)
// NB. only pages within your scanner size will be included
$PAGE_SIZE_LIST = array();
add_page_size('A0', 841, 1189);
add_page_size('A1', 594, 841);
add_page_size('A2', 420, 594);
add_page_size('A3', 297, 420);
add_page_size('A4', 210, 297);
add_page_size('A5', 148, 210);
add_page_size('A6', 105, 148);
//add_page_size('A7', 74, 105);
//add_page_size('A8', 52, 74);
//add_page_size('A9', 37, 52);
//add_page_size('A10', 26, 37);
//add_page_size('US Letter', 216, 279);
//add_page_size('US Legal', 216, 356);
//add_page_size('US Ledger', 432, 279);
//add_page_size('US Tabloid', 279, 432);
$DEFAULT_PAGE_SIZE = 'A4';

// enable features
$do_test_mode     = false; //fake scanner
$do_btn_clean     = false; //for debugging
$do_btn_reset     = false; //for debugging
$do_btn_help      = true;
$do_brightness    = true;
$do_contrast      = true;
$do_usr_opt       = false;
$do_file_name     = false;
$do_append_pdf    = true;
$do_append_txt    = true;
$do_lang_toggle   = true;
$do_file_selectbox= true;  //multi file selection box
$do_file_delete   = true;  //delete selected files
$do_file_download = true;  //download selected files
$do_file_timezone = false;
$do_file_highlight_new = true;


$do_format_pnm = true;
$do_format_jpg = true;
$do_format_tif = true;
$do_format_bmp = true;
$do_format_png = true;
$do_format_txt = true;
$do_format_pdf = true;


// END CONFIG ----------------------------------------------------------

// system config
// =============
$SCANIMAGE = "/usr/bin/scanimage"; //scanimage binary (sane)
$GOCR      = "/opt/bin/gocr";      //optional ocr binary
$PDFUNITE  = "/usr/local/bin/pdfunite"; //optional PDF merge binary
$PNMTOJPEG = "/usr/bin/pnmtojpeg"; //netpbm pnm to jpeg conversion binary
$PNMTOTIFF = "/usr/bin/pnmtotiff"; //netpbm pnm to tiff conversion binary
$PNMTOBMP  = "/usr/bin/ppmtobmp";  //netpbm ppm to bmp conversion binary
$PNMTOPNG  = "/usr/bin/pnmtopng";  //netpbm pnm to png conversion binary
$CONVERT   = "/usr/bin/convert";   //ImageMagick convert binary
$IDENTIFY  = "/usr/bin/identify";  //ImageMagick binary used to test for PDF support
if(php_uname('s') == 'FreeBSD') {
  //FreeBSD
  $SCANIMAGE = "/usr/local/bin/scanimage";
  $GOCR      = "/usr/local/bin/gocr";
  $PDFUNITE  = "/usr/local/bin/pdfunite";
  $PNMTOJPEG = "/usr/bin/pnmtojpeg";
  $PNMTOTIFF = "/usr/local/bin/pnmtotiff";
  $PNMTOBMP  = "/usr/local/bin/ppmtobmp";
  $PNMTOPNG  = "/usr/local/bin/pnmtopng";
  $CONVERT   = "/usr/local/bin/convert";
  $IDENTIFY  = "/usr/local/bin/identify";
}
else if(stripos(exec('uname -a'), 'synology') !== FALSE) {
  //Synology Disk Station
  $SCANIMAGE = "/opt/bin/scanimage";
  $GOCR      = "/opt/bin/gocr";
  $PDFUNITE  = "/usr/local/bin/pdfunite";
  $PNMTOJPEG = "/usr/local/netpbm/bin/pnmtojpeg";
  $PNMTOTIFF = "/usr/local/netpbm/bin/pnmtotiff";
  $PNMTOBMP  = "/usr/local/netpbm/bin/ppmtobmp";
  $PNMTOPNG  = "/usr/local/netpbm/bin/pnmtopng";
  $CONVERT   = "/opt/bin/convert";
  $IDENTIFY  = "/opt/bin/identify";
}
if(!`ls $GOCR`) $do_format_txt = false; //disable OCR when not available
if(!(`ls $CONVERT` && `ls $IDENTIFY` && `$IDENTIFY -list Format | grep -i pdf`)) $do_format_pdf = false; //disable PDF when not available
if(!`ls $PDFUNITE`) $do_append_pdf = false; //disable PDF books when merge tool is not available



$action_clear=0;
$action_clean_tmp=0;
$action_clean_output=0;
$action_deletefiles=0;
$action_preview=0;
$action_save=0;
$first=1;


// first visit and clean/clear options
if (isset($_POST['first'])) $first=$_POST['first'];
if ($first) { 
  $action_clear = 1;
  $action_clean_tmp = 1;
  $first = 0;
  if(isset($_COOKIE['language_id'])) {
    $lang_id = $_COOKIE['language_id'];
  }
}

if(isset($_POST['lang_id'])) $lang_id=$_POST['lang_id'];
if(isset($_POST['append_file'])) $append_file=$_POST['append_file'];


// check what button is clicked
if(isset($_POST['action_deletefiles'])) $action_deletefiles=1;
if(isset($_POST['action_preview'])) $action_preview=1;
if(isset($_POST['action_save'])) $action_save=1;
if(isset($_POST['action_reset'])) $action_clear=1;
if(isset($_POST['action_clean'])) { $action_clean_tmp=1; $action_clean_output=1; $action_clear=1; };


// default options (-1 for default)
$sid = time();
$preview_images = "./images/scan.jpg";
$page_size = 'A4';
$format = "jpg";
$mode = "Color";  // Lineart|Gray|Color
$resolution = 300;
$brightness = -1;
$contrast = -1;
$usr_opt = " --jpeg-quality 0";
$pos_x = 0;
$pos_y = 0;
$geometry_x = 0;
$geometry_y = 0;
$file_name_prefix = -1;
foreach ($PAGE_SIZE_LIST as $index => $page_values) {
  if ($page_values[0] == $DEFAULT_PAGE_SIZE)
  {
    $default_page_width_mm = $page_values[1];
    $default_page_height_mm = $page_values[2];
    
    $pos_x = $PREVIEW_WIDTH_MM - $default_page_width_mm;
    $pos_y = 0;
    $geometry_x = $PREVIEW_WIDTH_MM;
    $geometry_y = $default_page_height_mm;
  } 
}


// user options
if (!$action_clear) {
	if(isset($_POST['sid'])) $sid=$_POST['sid'];
	if(isset($_POST['preview_images'])) $preview_images=$_POST['preview_images'];
	if(isset($_POST['pos_x'])) $pos_x=$_POST['pos_x'];
	if(isset($_POST['pos_y'])) $pos_y=$_POST['pos_y'];
	if(isset($_POST['geometry_x'])) $geometry_x=$_POST['geometry_x'];
	if(isset($_POST['geometry_y'])) $geometry_y=$_POST['geometry_y'];
	if(isset($_POST['format'])) $format=$_POST['format'];
	if(isset($_POST['mode'])) $mode=$_POST['mode'];
	if(isset($_POST['resolution'])) $resolution=$_POST['resolution'];
	if(isset($_POST['brightness'])) $brightness=$_POST['brightness'];
	if(isset($_POST['usr_opt'])) $usr_opt=$_POST['usr_opt'];
}


// verify usr_opt - keep only valid chars, otherwise replace with an 'X'
$my_usr_opt = '';
for ($i = 0; $i < strlen($usr_opt); $i++) {
	if (preg_match('([0-9]|[a-z]|[A-Z]|[\ \%\+\-_=])', $usr_opt[$i])) {
		$my_usr_opt .= $usr_opt[$i];
	} else {
		$my_usr_opt .= 'X';
	}
}
$usr_opt = $my_usr_opt;


// INTERNAL CONFIG -----------------------------------------------------

// scanner device detect
$scanner_ok = false;
if ($do_test_mode) {
	$sane_result = "device `plustek:libusb:004:002' is a Plustek OpticPro U24 flatbed scanner";
} else {
	$sane_cmd = $SCAN_NET_SETUP . $SCANIMAGE . " --list-devices | grep 'device' | grep -e '\(scanner\|hpaio\|multi-function\)'";
	$sane_result = exec($sane_cmd);
	$sane_result;
	unset($sane_cmd);
}

// get scanner name
$start = strpos($sane_result, "`") + 1;
$length = strpos($sane_result, "'") - $start;
$scanner = "\"".substr($sane_result, $start, $length)."\"";
unset($start);
unset($length);
if ((strlen($scanner) > 2) || $do_test_mode) {
	$scanner_ok = true;
}
$start = strpos($sane_result, "is a ") + 5;
$length = strlen($sane_result) - $start;
$scanner_name = str_replace("_", " ", substr($sane_result, $start, $length));
$scan_output = $scanner_name;
unset($start);
unset($length);
unset($sane_result);


if($scanner_ok) {
  $scanner_known = scanner_known($scanner_name);

  // allowed resolutions
  if($scanner_known) {
    // read scanner configuration from file
    $mode_list = get_scanner_mode_options($scanner_name);
    $mode_default = get_scanner_mode_default($scanner_name);
    $resolution_list = get_scanner_resolution_options($scanner_name);
    $resolution_max = (int)end($resolution_list);
    $resolution_min = (int)reset($resolution_list);
    $resolution_default = get_scanner_resolution_default($scanner_name);
    $brightness_supported = strtolower(get_scanner_brightness_supported($scanner_name)) === 'true';
    $brightness_default = (int)get_scanner_brightness_default($scanner_name);
    $brightness_minimum = (int)get_scanner_brightness_minimum($scanner_name);
    $brightness_maximum = (int)get_scanner_brightness_maximum($scanner_name);
    $contrast_supported = strtolower(get_scanner_contrast_supported($scanner_name)) === 'true';
    $contrast_default = (int)get_scanner_contrast_default($scanner_name);
    $contrast_minimum = (int)get_scanner_contrast_minimum($scanner_name);
    $contrast_maximum = (int)get_scanner_contrast_maximum($scanner_name);
  }
  else {
    // build configuration from scanimage output

    // scanimage call and gather output
    $sane_cmd = $SCANIMAGE . " -h -d$scanner";
    $sane_result = `$sane_cmd`;
    if ($do_test_mode) {
      $sane_result = "   --resolution 50..2450dpi [75]\n   --mode Lineart|Color|Gray [Color]\n   --contrast 0..100 [50]\n   --brightness -100..100 [0]\n";
    }
    $sane_result_arr = explode("\n", $sane_result);
    unset($sane_result);
    unset($sane_cmd);
    ////////

    
    // brightness
    $brightness_supported = false;
    $brightness_minimum = 0;
    $brightness_maximum = 0;
    $brightness_default = 0;
    $sane_result_brightness = preg_grep('/--brightness /', $sane_result_arr);
    if(count($sane_result_brightness) > 0) {
      $brightness_line = end($sane_result_brightness);
      if(strpos($brightness_line, 'inactive') === false) {
        $brightness_supported = true;
        $brightness_minmax = explode('..', preg_replace('/^.*--brightness ([-|0-9..]*)[ \t].*$/iU','$1', $brightness_line));
        $brightness_minimum = $brightness_minmax[0];
        $brightness_maximum = $brightness_minmax[1];
        unset($brightness_minmax);
        
        preg_match("/\[(.*?)\]/", $brightness_line, $brightness_default_array);
        $brightness_default = $brightness_default_array[1];
        unset($brightness_default_array);
      }
      unset($brightness_line);
    }
    unset($sane_result_brightness);
    ////////
    
    
    // contrast
    $contrast_supported = false;
    $contrast_minimum = 0;
    $contrast_maximum = 0;
    $contrast_default = 0;
    $sane_result_contrast = preg_grep('/--contrast /', $sane_result_arr);
    if(count($sane_result_contrast) > 0) {
      $contrast_line = end($sane_result_contrast);
      if(strpos($contrast_line, 'inactive') === false) {
        $contrast_supported = true;
        $contrast_minmax = explode('..', preg_replace('/^.*--contrast ([-|0-9..]*)[ \t].*$/iU','$1', $contrast_line));
        $contrast_minimum = $contrast_minmax[0];
        $contrast_maximum = $contrast_minmax[1];
        unset($contrast_minmax);

        preg_match("/\[(.*?)\]/", $contrast_line, $contrast_default_array);
        $contrast_default = $contrast_default_array[1];
        unset($contrast_default_array);
      }
      unset($contrast_line);
    }
    unset($sane_result_contrast);
    ////////
    
    
    // modes
    $sane_result_mode = preg_grep('/--mode /', $sane_result_arr);
    $sane_result_mode = end($sane_result_mode);
    $modes = preg_replace('/^.*--mode ([a-z|]*)[ \t].*$/iU','$1', $sane_result_mode);
    $mode_list = explode('|', $modes);
    
    preg_match("/\[(.*?)\]/", $sane_result_mode, $mode_default_array);
    $mode_default = $mode_default_array[1];
    unset($sane_result_mode);
    unset($mode_default_array);
    ////////

    
    // resolutions
    $sane_result_reso = preg_grep('/--resolution /', $sane_result_arr);
    $sane_result_reso = end($sane_result_reso);

    // get default resolution
    preg_match("/\[(.*?)\]/", $sane_result_reso, $resolution_default_array);
    $resolution_default = $resolution_default_array[1];
    
    $start = strpos($sane_result_reso, "n") + 2;
    $length = strpos($sane_result_reso, "dpi") - $start;
    $list = "" . substr($sane_result_reso, $start,$length) . "";
    unset($start);
    unset($length);
    unset($sane_result_reso);
    unset($sane_result_arr);

    // change "|" separated string $list into array of values or generate a range of values.
    $length = strpos($list, "..");
    if ($length === false) {
      $resolution_list = explode("|" , $list);
      $resolution_max = (int)end($resolution_list);
      $resolution_min = (int)reset($resolution_list);
    } else {
      $resolution_list = array();
      $resolution_min = (int)substr($list, 0, $length);
      $resolution_max = (int)substr($list, $length + 2);

      // lower resolutions
      $list = array(
        10, 20, 30, 40, 50, 60, 72, 75, 80, 90,
        100, 120, 133, 144, 150, 160, 175, 180,
        200, 216, 240, 266,
        300, 320, 350, 360,
        400, 480,
        600,
        720,
        800,
        900,
      );

      foreach ($list as $res) {
        if (($res >= $resolution_min) && ($res <= $resolution_max)) {
          $resolution_list[] = $res;
        }
      }

      // higher resolutions
      $res = 1000;
      while (($res >= $resolution_min) && ($res < $resolution_max)) {
        $resolution_list[] = $res;
        $res += 200;
      }

      $resolution_list[] = $resolution_max;
    }
    unset($length);
    ////////
    
    
    // save scanner configuration
    save_scanner_config($scanner_name, 
                        $mode_list, $mode_default,
                        $resolution_list, $resolution_default,
                        $brightness_supported, $brightness_default, $brightness_minimum, $brightness_maximum,
                        $contrast_supported, $contrast_default, $contrast_minimum, $contrast_maximum);
  }

  if($resolution == -1 || array_search($resolution_default, $resolution_list) === false) {
    $resolution = $resolution_default;
  }
  
  if($mode == -1 || (array_search(strtolower($mode),array_map('strtolower', $mode_list)) === false)) {
    $mode = $mode_default;
  }
  
  $do_brightness = $do_brightness && $brightness_supported; //disable brightness option when not available
  if($brightness == -1 || (($brightness < $brightness_minimum) || ($brightness > $brightness_maximum))) {
    $brightness = $brightness_default; //set to scanimage default when not set or out of range
  }
  unset($brightness_supported);
  
  $do_contrast = $do_contrast && $contrast_supported; //disable contrast option when not available
  if($contrast == -1 || (($contrast < $contrast_minimum) || ($contrast > $contrast_maximum))) {
    $contrast = $contrast_default; //set to scanimage default when not set or out of range
  }
  unset($contrast_supported);
}
?>

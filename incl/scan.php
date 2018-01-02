<?php
  $lang_error=$lang[$lang_id][32];
  $error_input=0;

  function scan_error(&$scan_output, &$error_input, $lang_error) {
    $scan_output="!!!!!!!! ".$lang_error." !!!!!!!!";
    $error_input=1;
  }

  ////////////////////////////////////////////////////////////////////////

  // build the scan command options

  $cmd_geometry_l="";
  if ($pos_x >= 0) {
    if($pos_x <= $MAX_SCAN_WIDTH_MM) {
      $cmd_geometry_l=" -l ".$pos_x."mm";
    }
    else {
      $cmd_geometry_l=" -l ".$MAX_SCAN_WIDTH_MM."mm";
    }
  }
  else {
    $lang[$lang_id][1]="<span class=\"input_error\">".$lang[$lang_id][1]."</span>";
    scan_error($scan_output, $error_input, $lang_error);
  }

  $cmd_geometry_t="";
  if ($pos_y >= 0) {
    if ($pos_y <= $MAX_SCAN_HEIGHT_MM) {
      $cmd_geometry_t=" -t ".$pos_y."mm";
    }
    else {
      $cmd_geometry_t=" -t ".$MAX_SCAN_HEIGHT_MM."mm";
    }
  }
  else {
    $lang[$lang_id][2]="<span class=\"input_error\">".$lang[$lang_id][2]."</span>";
    scan_error($scan_output, $error_input, $lang_error);
  }

  $cmd_geometry_x="";
  $width = $geometry_x;
  if (($width >= 0) && $width <= $PREVIEW_WIDTH_MM) {
    if($width <= $MAX_SCAN_WIDTH_MM) {
      $cmd_geometry_x=" -x ".$width."mm";
    }
    else {
      $cmd_geometry_x=" -x ".$MAX_SCAN_WIDTH_MM."mm";
    }
  }
  else {
    $lang[$lang_id][3]="<span class=\"input_error\">".$lang[$lang_id][3]."</span>";
    scan_error($scan_output, $error_input, $lang_error);
  }

  $cmd_geometry_y="";
  $height = $geometry_y;
  if (($height >= 0) && $height <=$PREVIEW_HEIGHT_MM) {
      if($height <= $MAX_SCAN_HEIGHT_MM) {
      $cmd_geometry_y=" -y ".$height."mm";
    }
    else {
      $cmd_geometry_y=" -y ".$MAX_SCAN_HEIGHT_MM."mm";
    }
  }
  else {
    $lang[$lang_id][4]="<span class=\"input_error\">".$lang[$lang_id][4]."</span>";
    scan_error($scan_output, $error_input, $lang_error);
  }

  $cmd_mode=" --mode \"".$mode."\"";

  $cmd_resolution="";
  if (($resolution >= $resolution_min) && ($resolution <= $resolution_max)) {
    $cmd_resolution=" --resolution ".$resolution."dpi";
  } else {
    $lang[$lang_id][18]="<span class=\"input_error\">".$lang[$lang_id][18]."</span>";
    scan_error($scan_output, $error_input, $lang_error);
  }

  $cmd_brightness="";
  if ($do_brightness && (strtolower($mode) != 'lineart')) {
    if (($brightness >= $contrast_minimum) && ($brightness <= $contrast_maximum)) {
      $cmd_brightness=" --brightness ".$brightness;
    } else {
      $lang[$lang_id][22]="<span class=\"input_error\">".$lang[$lang_id][22]."</span>";
      scan_error($scan_output, $error_input, $lang_error);
    }
  }

  $cmd_contrast="";
  if ($do_contrast) {
    if (($contrast >= $contrast_minimum) && ($contrast <= $contrast_maximum)) {
      $cmd_contrast=" --contrast ".$contrast;
    } else {
      $lang[$lang_id][23]="<span class=\"input_error\">".$lang[$lang_id][23]."</span>";
      scan_error($scan_output, $error_input, $lang_error);
    }
  }
  
  $cmd_usr_opt=" " . $usr_opt;

  ////////////////////////////////////////////////////////////////////////
  // build the device command

  $scan_yes='';
  $cmd_device = '';
  $file_save = '';
  $file_save_image = 0;
  $cmd_scan=$SCANIMAGE." -d ".$scanner.$cmd_geometry_l.$cmd_geometry_t.$cmd_geometry_x.$cmd_geometry_y.$cmd_mode.$cmd_resolution.$cmd_brightness.$cmd_contrast.$cmd_usr_opt;

  if ($error_input == 0)
  {
    if ($action_preview) {
      $preview_images = $temp_dir."preview_".$sid.".jpg";
      $cmd_device = $SCANIMAGE." -d ".$scanner." --resolution ".$PREVIEW_DPI."dpi -l 0mm -t 0mm -x ".$MAX_SCAN_WIDTH_MM."mm -y ".$MAX_SCAN_HEIGHT_MM."mm".$cmd_mode.$cmd_brightness.$cmd_contrast.$cmd_usr_opt." | ".$PNMTOJPEG." --quality=50 > ".$preview_images;
    }
    else if ($action_save) {
      $file_save = $save_dir.$_POST['file_name'].".".$format;
      if (file_exists($file_save)) {
        $file_save=$save_dir.$_POST['file_name']." ".date("Y-m-d H.i.s",time()).".".$format;
      }
      $file_save_image = 1;
      
      if ($format == "jpg") {
        $cmd_device = $cmd_scan." | {$PNMTOJPEG} --quality=100 > \"".$file_save."\"";
      }
      if ($format == "pnm") {
        $cmd_device = $cmd_scan." > \"".$file_save."\"";
      }
      if ($format == "tif") {
        $cmd_device = $cmd_scan." | {$PNMTOTIFF} > \"".$file_save."\"";
      }
      if ($format == "bmp") {
        $cmd_device = $cmd_scan." | {$PNMTOBMP} > \"".$file_save."\"";
      }
      if ($format == "png") {
        $cmd_device = $cmd_scan." | {$PNMTOPNG} > \"".$file_save."\"";
      }
      if ($format == "pdf") {
        //$cmd_device = $cmd_scan." | {$CONVERT} pnm:- -compress jpeg -quality 100 -density {$resolution} pdf:- > \"".$file_save."\"";
        /*
          Bugfix:
          convert: unable to read image data `-' @ error/pnm.c/ReadPNMImage/766.
          convert: no images defined `pdf:-' @ error/convert.c/ConvertImageCommand/3044.
        */
        $cmd_device = $cmd_scan." | {$CONVERT} - -compress jpeg -quality 100 -density {$resolution} pdf:- > \"".$file_save."\"";
      }
      if ($format == "txt") {
        $cmd_device = $cmd_scan." | ".$GOCR." - > \"".$file_save."\"";
      }
    }
  }
  
  if ($action_deletefiles && $do_file_delete) {
    if(isset($_POST['selected_files'])) {
      foreach($_POST['selected_files'] as $selected_file) {
        $file_path = $save_dir . $selected_file;
        if(is_readable($file_path)) {
          unlink($file_path);
        }
      }
    }
  }

  ////////////////////////////////////////////////////////////////////////
  // perform actions required
  if ($cmd_device !== '') {
    $scan_yes=`$cmd_device`;
  } else {
    $cmd_device = $lang[$lang_id][39];
  }

  //merge files
  if ($action_save && $append_file !== '') {
    $escaped_file_save = str_replace(" ", "\\ ", $file_save);
    $escaped_append_file = str_replace(" ", "\\ ", $append_file);
  
    if ($format == "pdf" && $do_append_pdf) {  
      //merge pdf files
      exec("$PDFUNITE $escaped_append_file $escaped_file_save {$escaped_append_file}_new");
      exec("rm -f $escaped_append_file $escaped_file_save");
      exec("mv {$escaped_append_file}_new $escaped_append_file");
    }
    else if ($format == "txt" && $do_append_txt) {  
      //merge txt files
      exec("cat $escaped_file_save >> $escaped_append_file");
      exec("rm -f $escaped_file_save");
    }
    $file_save = $append_file;
  }
  
  if (($file_save !== '')&&($save_type=='popup')) {
    echo "<script language=\"JavaScript\" type=\"text/javascript\">\n";
    echo "window.open(\"./save.php?file_save=".$file_save."&file_save_image=".$file_save_image."&lang_id=".$lang_id."\",\"_blank\", \"width=400,height=500,left=320,top=200,scrollbars=yes,location=no,status=no,menubar=no\");\n";
    echo "</script>\n";
  }

  //remove files from temp directory older then one day
  if ($action_clean_tmp) {
    $files = glob($temp_dir."*");
    foreach($files as $file) {
      if(is_file($file) && time() - filemtime($file) >= 24*60*60) {
        unlink($file);
      }
    }
  }

  if ($action_clean_output) {
    $cmd_clean='rm -f '.$save_dir.'*';
    exec($cmd_clean);
  }
?>

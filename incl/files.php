<table id='files_table_wrapper'>
  <tr>
    <td><div class='ruler'/></td>
    <td class='unit_spacer'></td>
  </tr>
  <tr>
    <td>
      <ul id="file_buttons">
      <?php
        if($do_file_selectbox && ($do_file_download || $do_file_delete)) {
          echo "
          <li>
            <table id='select_menu_button'>
              <tr>
                <td><input id='select_menu_checkbox' type='checkbox'/></td>
                <td><span id='select_menu_arrow'></span></td>
              </tr>
            </table>
            <ul id='select_menu_dropdown'>
              <li id='select_menu_all'>{$lang[$lang_id][27]}</li>
              <li id='select_menu_none'>{$lang[$lang_id][28]}</li>
              <li id='select_menu_documents'>{$lang[$lang_id][29]}</li>
              <li id='select_menu_images'>{$lang[$lang_id][30]}</li>
              <li id='select_menu_other'>{$lang[$lang_id][31]}</li>
            </ul>
          </li>";
        }
        if($do_file_delete) echo "<li><input id='file_delete' type='submit' value='{$lang[$lang_id][59]}' name='action_deletefiles'></li>";
        if($do_file_download) echo "<li><input id='file_download' type='button' value='{$lang[$lang_id][58]}' name='action_downloadfiles'></li><li><div id='file_download_spinner'>{$lang[$lang_id][11]}</div></li>";
      ?>
      </ul>
    </td>
    <td class='unit_spacer'></td>
  </tr>
  <tr> 
    <td>
      <table id="files_table">
        <thead>
          <tr>
          <?php
            if($do_file_download || $do_file_delete) { 
              echo "      <th></th>";
            }
            echo "
            <th class='file_name_column'>{$lang[$lang_id][51]}</th>
            <th>{$lang[$lang_id][52]}</th>
            <th>{$lang[$lang_id][53]}</th>
            <th>{$lang[$lang_id][54]}</th>";
          ?>
          </tr>
        </thead>
        <tbody>
        <?php
          //create list of file names
          $files = array();
          foreach (new DirectoryIterator($save_dir) as $fileinfo) {
            if(!is_dir($save_dir.$fileinfo)) {    
              $files[$fileinfo->getMTime()] = $fileinfo->getFilename();
            }
          }
          krsort($files);
          $dirArray = array_values($files);
          
          //loop through the array of files
          for($index=0; $index < count($dirArray); $index++) {
            $file_name = $dirArray[$index];
            $file_path = str_replace(" ", "%20", $save_dir.$dirArray[$index]);
            $file_size = size_readable(filesize($save_dir . $dirArray[$index]));
            $file_modtime = strftime('%c', filemtime($save_dir . $dirArray[$index]));
            $file_new = $index === 0 && $action_save && $scanner_ok && (time() - filemtime($save_dir . $dirArray[$index]) <= 60);
            if(!$do_file_timezone) {
              $file_modtime = str_replace(array(' CET', ' CEST'), '', $file_modtime);
            }
            
            //file type and category
            $file_extention = findexts($dirArray[$index]);
            $file_category = '';
            switch ($file_extention){
              case "bmp": $file_extention = $lang[$lang_id][47]; $file_category = "image"; break;
              case "jpg": case "jpeg": $file_extention = $lang[$lang_id][44]; $file_category = "image"; break;
              case "pdf": $file_extention = $lang[$lang_id][43]; $file_category = "document"; break;
              case "png": $file_extention = $lang[$lang_id][48]; $file_category = "image"; break;
              case "pnm": $file_extention = $lang[$lang_id][45]; $file_category = "image"; break;
              case "tif": case "tiff": $file_extention = $lang[$lang_id][46]; $file_category = "image"; break;
              case "txt": $file_extention = $lang[$lang_id][49]; $file_category = "document"; break;
              case "": $file_extention = ucwords($lang[$lang_id][8]); break;
              default: $file_extention = strtoupper($file_extention)." ".$lang[$lang_id][55]; $file_category = "other"; break;
            }
            
            //print file info
            echo "<tr";
            if($do_file_highlight_new && $file_new) {
              echo " class='file_row_new'";
            }
            echo ">";
            if($do_file_download || $do_file_delete) { 
              echo "
              <td><input class='selected_files' type='checkbox' name='selected_files[]' value='$file_name' title='{$lang[$lang_id][56]}'></td>";
            }
            echo "
              <td class='file_column_category'>$file_category</td>
              <td class='file_column_name'><a href='$file_path' target='_blank'>$file_name</a></td>
              <td class='file_column_ext'><a href='$file_path' target='_blank'>$file_extention</a></td>
              <td class='file_column_size'><a href='$file_path' target='_blank'>$file_size</a></td>
              <td class='file_column_modtime'><a href='$file_path' target='_blank'>$file_modtime</a></td>
            </tr>\n";
          }
        ?>
        </tbody>
      </table>
    </td>
    <td class='unit_spacer'></td>
  </tr>
</table>

<?php
  //finds extensions of files
  function findexts($filename) {
    $splitFileName = explode(".", $filename);
    return (count($splitFileName) > 1) ? end($splitFileName) : '';
  }
  
  /* Return human readable sizes
   *
   * @author      Aidan Lister <aidan@php.net>
   * @version     1.3.0
   * @link        http://aidanlister.com/2004/04/human-readable-file-sizes/
   * @param       int     $size        size in bytes
   * @param       string  $max         maximum unit
   * @param       string  $system      'si' for SI, 'bi' for binary prefixes
   * @param       string  $retstring   return string format
  */
  function size_readable($size, $max = null, $system = 'si', $retstring = '%01.2f %s')
  {
      // Pick units
      $systems['si']['prefix'] = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
      $systems['si']['size']   = 1000;
      $systems['bi']['prefix'] = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
      $systems['bi']['size']   = 1024;
      $sys = isset($systems[$system]) ? $systems[$system] : $systems['si'];
    
      // Max unit to display
      $depth = count($sys['prefix']) - 1;
      if ($max && false !== $d = array_search($max, $sys['prefix'])) {
          $depth = $d;
      }
    
      // Loop
      $i = 0;
      while ($size >= $sys['size'] && $i < $depth) {
          $size /= $sys['size'];
          $i++;
      }
    
      return sprintf($retstring, $size, $sys['prefix'][$i]);
  }
?>
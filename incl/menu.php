<?php
echo "<table id='tab_menu_settings'>
	<tr>
		<th colspan='3'>".$lang[$lang_id][21]."</th>
	</tr>
	<tr>
		<td id='scanner_name' colspan='3'>".$scan_output."</td>
	</tr>
	<tr>
		<th colspan='3'>".$lang[$lang_id][0]."</th>
	</tr>
  <tr>
    <td>".$lang[$lang_id][40]."</td>
		<td class='value_column'>
      <select id='pagesize' name='pagesize' size=1>
        <option value='0,0' data-image='images/pagesize_custom.png'>{$lang[$lang_id][57]}</option>";
foreach ($PAGE_SIZE_LIST as $index => $page_values) {
  $pagesize_string = ($page_values[1].",".$page_values[2]);
  echo "<option value='" . $pagesize_string . "'";
  if ((isset($_POST['pagesize']) && ($_POST['pagesize'] == $pagesize_string)) || (!isset($_POST['pagesize']) && ($page_values[0] == $DEFAULT_PAGE_SIZE))) echo " selected";
  echo " data-image='images/pagesize_" . strtolower($page_values[0]) . ".png'>{$page_values[0]}</option>";
}
  echo "
      </select>
		</td>
    <td class='unit_column'></td>
	</tr>
	<tr>
    <td>".$lang[$lang_id][1]."</td>
		<td class='value_column'><input type='text' name='pos_x' id='pos_x' value='".$pos_x."' size='4' maxlength='3'></td>
    <td class='unit_column'>{$lang[$lang_id][5]}</td>
	</tr>
	<tr>
    <td>".$lang[$lang_id][2]."</td>
	  <td class='value_column'><input type='text' name='pos_y' id='pos_y' value='".$pos_y."' size='4' maxlength='3'></td>
    <td class='unit_column'>{$lang[$lang_id][5]}</td>
	</tr>
	<tr>
		<td>".$lang[$lang_id][3]."</td>
    <td class='value_column'><input type='text' name='geometry_x' id='geometry_x' value='".$geometry_x."' size='4' maxlength='3'></td>
    <td class='unit_column'>{$lang[$lang_id][5]}</td>
	</tr>
	<tr>
		<td>".$lang[$lang_id][4]."</td>
    <td class='value_column'><input type='text' name='geometry_y' id='geometry_y' value='".$geometry_y."' size='4' maxlength='3'></td>
    <td class='unit_column'>{$lang[$lang_id][5]}</td>
	</tr>
	<tr>
		<th colspan='3'>".$lang[$lang_id][9]."</th>
	</tr>
  <tr>
    <td>".$lang[$lang_id][10]."</td>
		<td class='value_column'>
			<select name='format'>\n";
if($do_format_jpg) { echo "<option "; if($format=="jpg") echo "selected "; echo "value='jpg' data-image='images/filetype_jpg.png'>".$lang[$lang_id][44]."</option>\n"; }
if($do_format_pnm) { echo "<option "; if($format=="pnm") echo "selected "; echo "value='pnm' data-image='images/filetype_pnm.png'>".$lang[$lang_id][45]."</option>\n"; }
if($do_format_tif) { echo "<option "; if($format=="tif") echo "selected "; echo "value='tif' data-image='images/filetype_tif.png'>".$lang[$lang_id][46]."</option>\n"; }
if($do_format_png) { echo "<option "; if($format=="png") echo "selected "; echo "value='png' data-image='images/filetype_png.png'>".$lang[$lang_id][48]."</option>\n"; }
if($do_format_bmp) { echo "<option "; if($format=="bmp") echo "selected "; echo "value='bmp' data-image='images/filetype_bmp.png'>".$lang[$lang_id][47]."</option>\n"; }
if($do_format_pdf) { echo "<option "; if($format=="pdf") echo "selected "; echo "value='pdf' data-image='images/filetype_pdf.png'>".$lang[$lang_id][43]."</option>\n"; }
if($do_format_txt) { echo "<option "; if($format=="txt") echo "selected "; echo "value='txt' data-image='images/filetype_txt.png'>".$lang[$lang_id][49]."</option>\n"; }
echo "</select>
		</td>
    <td class='unit_column'></td>
  </tr>
	<tr>
    <td>".$lang[$lang_id][14]."</td>
    <td class='value_column'>		
      <select name='mode'>\n";
$mode_color_index = array_search('color', array_map('strtolower', $mode_list));;
if($mode_color_index !== false) {
  echo "<option"; if(strcasecmp($mode, 'color') == 0) echo " selected"; echo " value='{$mode_list[$mode_color_index]}' data-image='images/mode_color.png'>" . $lang[$lang_id][15]."</option>";
}
$mode_gray_index = array_search('gray', array_map('strtolower', $mode_list));
if($mode_gray_index !== false) {
  echo "<option"; if(strcasecmp($mode, 'gray') == 0) echo " selected"; echo " value='{$mode_list[$mode_gray_index]}' data-image='images/mode_gray.png'>" . $lang[$lang_id][16]."</option>";
}
$mode_lineart_index = array_search('lineart', array_map('strtolower', $mode_list));
if($mode_lineart_index !== false) {
  echo "<option"; if(strcasecmp($mode, 'lineart') == 0) echo " selected"; echo " value='{$mode_list[$mode_lineart_index]}' data-image='images/mode_lineart.png'>" . $lang[$lang_id][17]."</option>";
}
echo "
	    </select>
    </td>
    <td class='unit_column'></td>
  </tr>
	<tr>
    <td>".$lang[$lang_id][18]."</td>
		<td class='value_column'>\n".html_selectbox('resolution', $resolution_list, $resolution)."</td>
    <td class='unit_column'>{$lang[$lang_id][6]}</td>
  </tr>";

if ($do_brightness) {
	echo "
  <tr>
    <td>".$lang[$lang_id][22]."</td>
    <td class='value_column'>
      <div id='brightness_slider' class='noUiSlider'></div>
      <input id='brightness' type='text' value='".$brightness."' name='brightness' maxlength='4'>
    </td>
    <td class='unit_column'>{$lang[$lang_id][7]}</td>
  </tr>";
}

if ($do_contrast) {
	echo "
  <tr>
    <td>".$lang[$lang_id][23]."</td>
    <td class='value_column'>
      <div id='contrast_slider' class='noUiSlider'></div>
      <input id='contrast' type='text' value='".$contrast."' name='contrast' maxlength='3'>
    </td>
    <td class='unit_column'>{$lang[$lang_id][7]}</td>
  </tr>";
}

if ($do_usr_opt) {
echo "
	<tr>
		<td>".$lang[$lang_id][38]."</td>
    <td class='value_column'><input type='text' value='".$usr_opt."' name='usr_opt' size='40'></td>
    <td class='unit_column'></td>
	</tr>";
}

if($do_file_name) {
  $filename = ($file_name_prefix !== -1 ? $file_name_prefix : $lang[$lang_id][60]) . date("Y-m-d H.i.s", time());
  echo "
	<tr>
    <td>".$lang[$lang_id][41]."</td>
	  <td class='value_column'><input type='text' value='$filename' name='file_name' size='40'></td>
    <!--<td class='value_column'><input type='text' value='".$lang[$lang_id][60]." ".date("Y-m-d H.i.s",time())."' name='file_name' size='40'></td>-->
    <td class='unit_column'></td>
	</tr>";
}

if($do_btn_reset || $do_btn_clean) {
echo "
    <tr>
      <td colspan='2'>";
        if ($do_btn_reset) echo "<input type='submit' name='action_reset' value='".$lang[$lang_id][25]."'>";
        if ($do_btn_clean) echo "<input type='submit' name='action_clean' value='".$lang[$lang_id][26]."'>";
      echo "
      </td>
      <td class='unit_column'></td>
    </tr>";
}

echo "
    <tr>
      <td id='tab_menu_buttons' colspan='2'>
        <input id='tab_menu_buttons_preview' type='submit' name='action_preview' value='".$lang[$lang_id][24]."'><input id='tab_menu_buttons_accept' type='submit' name='action_save' value='".$lang[$lang_id][42]."'>
      </td>
      <td class='unit_column'></td>
    </tr>
  </table>\n";
  
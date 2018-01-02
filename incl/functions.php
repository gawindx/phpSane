<?php

// ---------------------------------------------------------------------
function add_page_size($page_name, $page_x, $page_y) {
	global $PREVIEW_WIDTH_MM, $PREVIEW_HEIGHT_MM;
	global $PAGE_SIZE_LIST;

	if (($page_x <= $PREVIEW_WIDTH_MM) && ($page_y <= $PREVIEW_HEIGHT_MM)) {
		$PAGE_SIZE_LIST[] = array(0 => $page_name, $page_x, $page_y);
	}
}
// ---------------------------------------------------------------------


// ---------------------------------------------------------------------
// Scanner configuration file functions
// ---------------------------------------------------------------------

function sanitize_path($url) {
	// everything to lower and no spaces begin or end
	$url = strtolower(trim($url));
 
	// adding - for spaces and union characters
	$find = array(' ', '&', '\r\n', '\n', '+',',');
	$url = str_replace ($find, '-', $url);
 
	//delete and replace rest of special chars
	$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
	$repl = array('', '-', '');
	return preg_replace ($find, $repl, $url);
}

function get_config_path($scanner_name) {
  global $scanner_dir;
  return $scanner_dir.(sanitize_path($scanner_name)).".ini";
}

function scanner_known($scanner_name) {
  return file_exists(get_config_path($scanner_name));
}

function get_config_option_array($scanner_name, $config_key, $index = 0) {
  $config_options = explode('|', get_config_option($scanner_name, $config_key));
  $config_option_string = $config_options[$index];
  return explode(';', $config_option_string);
}

function get_config_option($scanner_name, $config_key) {
  $config_option = '';
  if(scanner_known($scanner_name)) {
    $scanner_config = file(get_config_path($scanner_name));
    
    $config_option_line = preg_grep("/".$config_key.":/", $scanner_config);
    $config_option_line = trim(end($config_option_line));

    $start = strpos($config_option_line, ":") + 1;
    $length = strlen($config_option_line) - $start;
    $config_option = "" . substr($config_option_line, $start, $length) . "";
  }
  return $config_option;
}

function get_scanner_resolution_options($scanner_name) {
  return get_config_option_array($scanner_name, "resolution");
}
function get_scanner_resolution_default($scanner_name) {
  $mode_info = explode('|', get_config_option($scanner_name, "resolution"));
  return $mode_info[1];
}

function get_scanner_mode_options($scanner_name) {
  return get_config_option_array($scanner_name, "mode");
}
function get_scanner_mode_default($scanner_name) {
  $mode_info = explode('|', get_config_option($scanner_name, "mode"));
  return $mode_info[1];
}

function get_scanner_brightness_supported($scanner_name) {
  $brightness_info = explode('|', get_config_option($scanner_name, "brightness"));
  return $brightness_info[0];
}
function get_scanner_brightness_default($scanner_name) {
  $brightness_info = explode('|', get_config_option($scanner_name, "brightness"));
  return $brightness_info[1];
}
function get_scanner_brightness_minimum($scanner_name) {
  $brightness_info = explode('|', get_config_option($scanner_name, "brightness"));
  return $brightness_info[2];
}
function get_scanner_brightness_maximum($scanner_name) {
  $brightness_info = explode('|', get_config_option($scanner_name, "brightness"));
  return $brightness_info[3];
}

function get_scanner_contrast_supported($scanner_name) {
  $contrast_info = explode('|', get_config_option($scanner_name, "contrast"));
  return $contrast_info[0];
}
function get_scanner_contrast_default($scanner_name) {
  $contrast_info = explode('|', get_config_option($scanner_name, "contrast"));
  return $contrast_info[1];
}
function get_scanner_contrast_minimum($scanner_name) {
  $contrast_info = explode('|', get_config_option($scanner_name, "contrast"));
  return $contrast_info[2];
}
function get_scanner_contrast_maximum($scanner_name) {
  $contrast_info = explode('|', get_config_option($scanner_name, "contrast"));
  return $contrast_info[3];
}

function save_scanner_config($scanner_name, 
                             $mode_list, $mode_default,
                             $resolution_list, $resolution_default, 
                             $brightness_supported, $brightness_default, $brightness_minimum, $brightness_maximum,
                             $contrast_supported, $contrast_default, $contrast_minimum, $contrast_maximum) {
  $file_path = get_config_path($scanner_name);
  file_put_contents($file_path, "mode:".implode(';', $mode_list)."|{$mode_default}\r\n");
  file_put_contents($file_path, "resolution:".implode(';', $resolution_list)."|{$resolution_default}|\r\n", FILE_APPEND);
  file_put_contents($file_path, "brightness:".($brightness_supported ? 'true' : 'false')."|{$brightness_default}|{$brightness_minimum}|{$brightness_maximum}\r\n", FILE_APPEND);
  file_put_contents($file_path, "contrast:".($contrast_supported ? 'true' : 'false')."|{$contrast_default}|{$contrast_minimum}|{$contrast_maximum}\r\n", FILE_APPEND);
}

// ---------------------------------------------------------------------
// ---------------------------------------------------------------------


/**
 * generates html select dropdown list with options
 * if values is two dimensional then adds optgroup too
 *
 * @param 	string	$name			selectbox name and id
 * @param 	array		$values		options
 * @param 	mixed		$selected	selected option
 * @param 	array		$attributes additonal attributes
 *
 * @return 	string	html source with selectbox
 */

function html_selectbox($name, $values, $selected=NULL, $attributes=array()) {
	$attr_html = '';
	if(is_array($attributes) && !empty($attributes))
	{
		foreach ($attributes as $k=>$v)
		{
			$attr_html .= ' '.$k.'="'.$v.'"';
		}
	}

	$output = '<select name="'.$name.'" id="'.$name.'"'.$attr_html.'>'."\n";
	if(is_array($values) && !empty($values))
	{
		foreach($values as $key=>$value)
		{
			if(is_array($value))
			{
				$output .= '<optgroup label="'.$key.'">'."\n";
				foreach($value as $k=>$v)
				{
					$sel = $selected==$v ? ' selected="selected"' : '';
					$output .= '<option value="'.$k.'"'.$sel.'>'.$v.'</option>'."\n";
				}
				$output .= '</optgroup>'."\n";
			}
			else
			{
				$sel = $selected==$value ? ' selected' : '';
				$output .= '<option'.$sel.' value="'.$value.'">'.$value.'</option>'."\n";
        
			}
		}
	}
	$output .= "</select>\n";

	return $output;
}
// ---------------------------------------------------------------------

?>

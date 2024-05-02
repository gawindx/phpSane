<?php
// Avoid direct access ; redirect to '/'
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    if (ob_get_contents()) ob_clean(); // ob_get_contents() even works without active output buffering
    header('Location: /');
    die;
}

function get_config() {
	$response = array();
	$response['result'] = 'true';
}

/* 
 * discover and return an array with all device 
 * and all capacity of device
 * 
 * return: associative array 
 * 	device => associative array capacity => value
 *   
 * */
function get_all_devices() {
	global $SaneCmd;
	$response = array();
	$device_list = array();

	$response['result'] = 'false';
	$sane_arg = " --list-devices | grep 'device' | grep -e '\(scanner\|hpaio\|multi-function\)'";

	try {
		if (!($SaneCmd->run($sane_arg))) {
			debug2log('Search Devices Failed!');
			return null;
		}else{
			debug2log('Search Devices Success\nError Code:'.$SaneCmd->ScanImgResCode.'\nOutput :\n'.implode(',\n', $SaneCmd->ScanImgOutput));
			$sane_output = $SaneCmd->ScanImgOutput;
			debug2log('Last Executed Command:\n'.$SaneCmd->ScanImgLastCmd);
			debug2log(print_r($sane_output, true));
		}
		
		foreach($sane_output as $Device) {
			debug2log('Detect Config for '.$Device);
			$DeviceCap = new ScannerDevice($Device, false);
			debug2log('Device Capabilities fully acquired!');
			array_push($device_list, $DeviceCap->AsArray());
			unset($DeviceCap);
		}
		//debug2log("Full Device List :".print_r($device_list, true));
		$response['result'] = 'true';
		$response['devices'] = $device_list;
	} catch (Exception $e) {
		debug2log('Exception received : ',  $e->getMessage(), "\n");
		$response['devices'] = $device_list;
	} finally {
		return json_encode($response);
	}
}

function get_preview($preview_args) {
	global $Config;
	global $SaneCmd;
	global $PNMTOJPEG;
	$response = array();

	//debug2log('Get Preview requested!');
	$response['result'] = 'false';
	try {
		// generate output tmp file for preview
		$preview_jpg = $Config['path']['temp_dir'] .'preview_'.substr(md5(microtime()),rand(0,26),5).'.jpg';
		//debug2log('Save preview to: '.$preview_jpg.'!');
		$preview_args['output'] = '| '.$Config['exe_path']['PNMTOJPEG'].' --quality=50 > "'.$preview_jpg.'"';
		//debug2log('Get Preview args:'.print_r($preview_args, true));

		if($SaneCmd->prepareCmd($preview_args)) {
			if (!($SaneCmd->run())) {;
				$response['result'] = 'false';
			}else{
				$response['result'] = 'true';
				$response['preview_file'] = $preview_jpg;
			}
		}else{
			$response['result'] = 'false';
		}
	} catch (Exception $e) {
		//debug2log('Exception received : ',  $e->getMessage(), "\n");
		$response['result'] = 'false';
		$response['errorMessage'] = $e->getMessage();
	} finally {
		$response['sane_output'] = $SaneCmd->ScanImgOutput;
		$response['sane_result'] = $SaneCmd->ScanImgResCode;
		$response['sane_last_cmd'] = $SaneCmd->ScanImgLastCmd;
		$response['request_args'] = $preview_args;

		return json_encode($response);
	}
}

function get_scan($scan_args) {
	global $SaneCmd;
	global $Config;

	$response = array();
	debug2log('Get Scan requested!');
	$response['result'] = 'false';
	debug2log('Request Args : '.print_r($scan_args, true));

	try {
		$scan_args['exe_path'] = $Config['exe_path'];
		if ($scan_args['filename'] == '') {
			$scan_args['filename'] = $Config['path']['save_dir'].'Output_'.date("Y-m-d H.i.s",time()).'.'.$scan_args['file_format'];
		}else{
			$scan_args['filename'] = basename($scan_args['filename'], '.' . (pathinfo($scan_args['filename']))['extension']);
			$scan_args['filename'] = $Config['path']['save_dir'].$scan_args['filename'];
		}
		debug2log('Filename:'.$scan_args['filename']);
		$scan_args['tmp_file'] = $Config['path']['temp_dir'] .'out_'.substr(md5(microtime()),rand(0,26),5).'_';
		//debug2log('scan args completed');
		debug2log('Tmp File:'.$scan_args['tmp_file']);
		if($SaneCmd->prepareCmd($scan_args)) {
			if (!($SaneCmd->run())) {;
				$response['result'] = 'false';
				//debug2log('SaneCmd Failed');
			}else{
				$response['result'] = 'true';
				$response['filename'] = $scan_args['filename'];
				$response['file_datas'] = getFileInfos($response['filename']);
				//debug2log('SaneCmd Success');
				//debug2log('Output File is:'.$response['filename']);
			}
		}else{
			$response['result'] = 'false';
			//debug2log('SaneCmd Prepare CmdFailed');
		}
	} catch (Exception $e) {
		//debug2log('Exception received : ',  $e->getMessage(), "\n");
		$response['result'] = 'false';
		$response['errorMessage'] = $e->getMessage();
	} finally {
		$response['sane_output'] = $SaneCmd->ScanImgOutput;
		$response['sane_result'] = $SaneCmd->ScanImgResCode;
		$response['sane_last_cmd'] = $SaneCmd->ScanImgLastCmd;
		$response['request_args'] = $scan_args;
		//debug2log('GetScan Success');
		//debug2log('Response : '.print_r($response, true));

		return json_encode($response);
	}
}

function get_files() {
	global $Config;
	$response = array();
	$response['result'] = false;
	$response['files'] = array();
	$save_dir = $Config['path']['save_dir'];

	try {
		//create list of file names
		$files = array();
		foreach (new DirectoryIterator($Config['path']['save_dir']) as $fileinfo) {
			if(!is_dir($Config['path']['save_dir'].$fileinfo)) {
				$files[$fileinfo->getMTime()] = $fileinfo->getFilename();
			}
		}
		krsort($files);
		$dirArray = array_values($files);

		for($index=0; $index < count($dirArray); $index++) {
			array_push($response['files'], getFileInfos($dirArray[$index]));
		}
		$response['result'] = true;
	} catch(Exception $e) {
		debug2log('Exception on get_files :' . $e->getMessage());
		$response['result'] = false;
	}finally {
		return json_encode($response);
	}

	
	return json_encode($result);
}

function getFileInfos($filename) {
	global $Config;
	$file_result = array();

	try {
		debug2log('get file info : '.$filename);
		$file_result['file_name'] = basename($filename);
		debug2log('get file info after: '.$file_result['file_name']);
		$file_result['file_path'] = $Config['path']['save_dir'].$file_result['file_name'];
		debug2log('get file path : '.$file_result['file_path']);
		$file_result['file_size'] = size_readable(filesize($file_result['file_path']));
		$file_result['file_modtime'] = utf8_encode(strftime('%c', filemtime($file_result['file_path'])));
		$file_result['file_new'] = $index === 0 && (time() - filemtime($file_result['file_path']) <= 60);
		$file_result['thumbnail'] = getThumbnail($file_result['file_name']);
		
		//file type and category
		$file_category = '';
		$file_icon = '';
		$file_ext = findexts($filename);
		switch ($file_ext){
			case 'bmp': 
				$file_icon = 'images/filetype_bmp.png';
				$file_category = 'image';
				break;
			case 'jpg': case 'jpeg': 
				$file_icon = 'images/filetype_jpg.png';
				$file_category = 'image';
				break;
			case 'pdf':
				$file_icon = 'images/filetype_pdf.png';
				$file_category = 'document';
				break;
			case 'png':
				$file_icon = 'images/filetype_png.png';
				$file_category = 'image';
				break;
			case 'pnm':
				$file_icon = 'images/filetype_pnm.png';
				$file_category = 'image';
				break;
			case 'tif': case 'tiff':
				$file_icon = 'images/filetype_tif.png';
				$file_category = 'image';
				break;
			case 'txt':
				$file_icon = 'images/filetype_txt.png';
				$file_category = 'document';
				break;
			default:
				$file_icon = '';
				$file_category = 'other';
				break;
		}
		$file_result['file_icon'] = $file_icon;
		$file_result['file_ext'] = $file_ext;
	} catch (Exception $e) {
		$file_result = null;
	} finally {
		return $file_result;
	}
}

function get_params() {
	global $Config;
	$response = array();
	$response['result'] = false;
	
	try {
		$response['params'] = array_clone($Config);
		unset($response['params']['path']);
		unset($response['params']['exe_path']);
		$response['result'] = true;
	} catch(Exception $e) {
		debug2log('Exception on get_Params :' . $e->getMessage());
		$response['result'] = false;
	}finally{
		return json_encode($response);
	}
}

function get_languages(){
	global $Languages;
	$response = array();
	$response['result'] = false;
	
	try {
		$response['languages'] = array_clone($Languages);
		$response['result'] = true;
	} catch(Exception $e) {
		debug2log('Exception on get_languages :' . $e->getMessage());
		$response['result'] = false;
	}finally{
		return json_encode($response);
	}
}

function getThumbnail($filename) {
	global $Config;
	try {
		debug2log('get thumbnail for ' . $filename);
		$thumbname = 'thumb_'.$filename.'.jpg';
		if (!file_exists($Config['path']['thumb_dir'].$thumbname)) {
			debug2log('thumbnail not already exist');
			debug2log('create thumbnail in:' . $Config['path']['thumb_dir'].$thumbname);
			if(!createThumbnail($Config['path']['save_dir'].$filename, $thumbname)) {
				debug2log('create thumbnail failed');
				$thumbname = false;
			}else{
				debug2log('create thumbnail Success');
			};
		}
	} catch(Excpetion $e) {
		$thumbname = false;
	} finally {
		return $Config['path']['thumb_dir'] . $thumbname;
	}
}

function createThumbnail($filename, $thumbname) {
	global $Config;
	$thumb_w = $Config['thumbnail']['width'];
	$thumb_h = $Config['thumbnail']['height'];
	$output = array();
	$result_code;

	debug2log('Convert thumbnail for ' . $filename);
	debug2log('Output Path:' . $Config['path']['thumb_dir'] . $thumbname);
	debug2log('Output File: '. realpath($Config['path']['thumb_dir']) . '/' . $thumbname);
	$thumb_args = array(
		$Config['exe_path']['CONVERT'],
		$thumb_w,
		$thumb_h,
		realpath($filename),
		realpath($Config['path']['thumb_dir']) . '/' . $thumbname,
	);
	debug2log('Convert with args: ' . print_r($thumb_args, true));
	try{
		debug2log('Prepare Cmd');
	$thumb_cmd =$thumb_args[0] . ' -sample "' . $thumb_args[1] . 'x' . $thumb_args[2] . '" "'. $thumb_args[3] . '" "' . $thumb_args[4] . '"';
	debug2log('Prepare Cmd Ok');
	
	}catch(Exception $e) {
		debug2log('Exception on thumbnail :'.$e->getMessage());
		$thumb_cmd = 'empty';
	}
	
	debug2log('Convert command args: ' . $thumb_cmd);

	if ( exec($thumb_cmd, $output, $result_code) !== false ) {
		debug2log('Convert thumbnail Output:' . print_r($output, true));

		debug2log('Convert thumbnail RC:' . $result_code);
		$result = true;
	}else{
		debug2log('Convert exec Failed');
		$result = false;
	}
	return $result;
}

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

function array_clone($array) {
    return array_map(function($element) {
        return ((is_array($element))
            ? array_clone($element)
            : ((is_object($element))
                ? clone $element
                : $element
            )
        );
    }, $array);
}

/* Return human readable sizes
*
* @author	  Aidan Lister <aidan@php.net>
* @version	 1.3.0
* @link		http://aidanlister.com/2004/04/human-readable-file-sizes/
* @param		int		$size		size in bytes
* @param		string	$max		maximum unit
* @param		string	$system		'si' for SI, 'bi' for binary prefixes
* @param		string	$retstring	return string format
*/
function size_readable($size, $max = null, $system = 'si', $retstring = '%01.2f %s'){
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

function findexts($filename) {
	$splitFileName = explode(".", $filename);
	return (count($splitFileName) > 1) ? end($splitFileName) : '';
}
?>
<?php
    // Avoid direct access ; redirect to '/'
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        if (ob_get_contents()) ob_clean(); // ob_get_contents() even works without active output buffering
        header('Location: /');
        die;
    }

    class ScanImg {
        protected static $instance = NULL;
        public $ScanImgResCode = 0;
        public $ScanImgOutput = array();
        protected static $ScanImgPath = '';
        public $CmdLineArgs = '';
        public $ScanImgLastCmd = '';
        public $LastError = '';

        /**
        * Get the Class instance's or declare new one if not exists
        *
        * @return $instance	An instance Class
        */
        public static function get_instance() {
            if ( NULL === self::$instance ) {
                self::$instance = new self(self::$ScanImgPath);
            }
            return self::$instance;
        }

        function __construct($pathfile) {
            self::$ScanImgPath = $pathfile;

        }

        /**
        * Run scanimage with args passed to method
        *
        * $ScanImgArgs as string
        *
        * @return boolean
        */
        public function run($ScanImgArgs = '') {
            $result = false;
            try{
                // initialize public variable
                $this->ScanImgResCode = 0;
                $this->ScanImgLastCmd = '';
                $this->ScanImgOutput = '';

                //if $ScanImagArgs is '' use prepared command and empty it
                if (empty($ScanImgArgs) && !empty($this->CmdLineArgs)) {
                    $ScanImgArgs = $this->CmdLineArgs;
                    $this->CmdLineArgs = '';
                }

                // Be sure that Args do not start with space 
                $ScanImgArgs = trim($ScanImgArgs);
                $SanePath = self::$ScanImgPath." ".$ScanImgArgs;
                $this->ScanImgLastCmd = $SanePath;
                if ( exec($SanePath, $output, $result_code) !== false ) {
                    $this->ScanImgOutput = $output;
                    $this->ScanImgResCode = $result_code;
                    $result = true;
                }else{
                    $result = false;
                }
            } catch (Exception $e) {
                debug2log('Exception received : ',  $e->getMessage(), "\n");
                $result = false;
                $this->ScanImgResCode = 255;
                $this->LastError = $e->getMessage();
            } finally {
                return $result;
            }
        }

        public function prepareCmd($argumentObject) {
            try {
                $result = false;
                $args_ok = false;
                $CmdLine = '';
                $dev_arg = '';
                $geo_args = '';
                $mode_arg = '';
                $contrast_arg = '';
                $brightness_arg = '';
                $src_arg = '';
                $src_addon = '';
                $output_arg = '';

                $res_arg = ' --resolution 300dpi';

                foreach ($argumentObject as $key=>$value) {
                    switch ($key) {
                        case 'deviceUri':
                            $dev_arg = '-d "'.$value.'"';
                            $args_ok = true;
                            break;
                        case 'resolution':
                            $res_arg = ' --resolution '.$value.'dpi';
                            break;
                        case 'geometry':
                            $tlx = $value['tlx'];
                            $tly = $value['tly'];
                            $x = $value['x'];
                            $y = $value['y'];
                            $geo_args = ' -l '.$tlx.'mm -t '.$tly.'mm -x '.$x.'mm -y '.$y.'mm';
                            unset($tlx, $tly, $x, $y);
                            break;
                        case 'mode';
                            $mode_arg = ' --mode "'.$value.'"';
                            break;
                        case 'source':
                            $src_arg = ' --source "'.$value.'"';
                            break;
                        case 'output':
                            $output_arg = $value;
                            break;
                        case 'contrast':
                            if ($value != 'false') {
                                $contrast_arg = ' --contrast "'.$value.'"';
                            }
                            break;
                        case 'brighness':
                            if ($value != 'false') {
                                $brightness_arg = ' --brightness "'.$value.'"';
                            }
                            break;
                        case 'file_format':
                            $EP = $argumentObject['exe_path'];
                            $filename = $argumentObject['filename'];
                            $tmp_file = $argumentObject['tmp_file'];
                            $resolution = $argumentObject['resolution'];

                            switch ($value){
                                case 'pdf':
                                    // see for option https://imagemagick.org/script/defines.php
                                    if ($argumentObject['src_type'] =="adf") {
                                        $src_addon = " --format=tiff --batch='{$tmp_file}%d.tif' --batch-start=10";
                                        $output_arg = " && {$EP['CONVERT']} '{$tmp_file}*.tif' -compress jpeg -quality 90 -density {$resolution} -title '$filename' pdf:- > '$filename'";
                                    }else{
                                        $output_arg = $cmd_scan." | {$EP['CONVERT']} - -compress jpeg -quality 100 -density {$resolution} -title '$filename' pdf:- > '$filename'";
                                    }
                                    break;
                                case 'txt':
                                    if ($argumentObject['src_type'] =="adf") {
                                        $src_addon = " --batch='{$tmp_file}%d.pnm' --batch-start=10";
                                        $output_arg = " && {$EP['CONVERT']} '{$tmp_file}*.pnm' '{$tmp_file}single.pnm' | {$EP['GOCR']} '{$tmp_file}single.pnm' > '$filename'";
                                    }else{
                                        $output_arg = " | ".$EP['GOCR']." - > '$filename'";
                                    }
                                    break;
                                case 'png':
                                    $output_arg = " | {$EP['PNMTOPNG']} > '$filename'";
                                    break;
                                case 'bmp':
                                    $output_arg = " | {$EP['PNMTOBMP']} > '$filename'";
                                    break;
                                case 'tif':
                                    if ($argumentObject['src_type'] == 'adf') {
                                        $src_addon = " --format=tiff --batch='{$tmp_file}%d.tif' --batch-start=10";
                                        $output_arg = " && {$EP['CONVERT']} '{$tmp_file}*.tif' -compress jpeg -quality 90 -density {$resolution} '$filename'";
                                    }else{
                                        $output_arg = " | {$EP['PNMTOTIFF']} > '$filename'";
                                    }
                                    break;
                                case 'pnm':
                                    $output_arg = " > '$filename'";
                                    break;
                                case 'jpg':
                                default:
                                    $output_arg = " | {$EP['PNMTOJPEG']} --quality=100 -o '$filename'";
                                    break;
                            }
                        default:
                            break;
                    }
                }
                if ($args_ok) {
                    $CmdLine = $dev_arg.$res_arg.$geo_args.$contrast_arg.$brightness_arg.$mode_arg.$src_arg.$src_addon.$output_arg;
                    $this->CmdLineArgs = $CmdLine;
                    $result = $this->CmdLineArgs;
                    $result = true;
                }else{
                    $result = false;
                }
            } catch (Exception $e) {
                debug2log('Exception received : ',  $e->getMessage(), "\n");
                $this->LastError = $e->getMessage();
                $result = false;
            } finally {
                unset($CmdLine, $args_ok, $dev_arg, $res_arg, $geo_args, $mode_arg, 
                    $src_arg, $output_arg, $contrast_arg, $brightness, $src_addon);
                debug2log('Return result:'.$result);
                return $result;
            }
        }
    }

    class ScannerDevice {

        public $DeviceCap = array();
        public $DeviceLine = "";
        public $Debug = false;
        public $ScanCmd;
        public $Config = array();

        /**
         * [__construct description]
         *
         * @param   [type] $ScanImgLines  [$ScanImgLines description]
         * @param   [type] $test_mode     [$test_mode description]
         * @param   false                 [ description]
         *
         * @return  [type]                [return description]
         */
        public function __construct($ScanImgLines, $test_mode = false) {
            debug2log('Construct new device Object');
            global $Config;

            $this->Config['scanner_dir'] = $Config['path']['scanner_dir'];
            $this->ScanCmd = ScanImg::get_instance();
            $this->DeviceLine = $ScanImgLines;
            $this->Debug = $test_mode;
            $new_device = false;

            $this->DeviceCap['DeviceName'] = $this->getDeviceName();
            $this->DeviceCap['DeviceUri'] = $this->getDeviceUri();
            debug2log('Verify existing config for '.$this->DeviceCap['DeviceName']);
            if ($this->device_known()) {
                // read scanner configuration from file
                debug2log('known device, get all cap from config file');
                $this->get_device_config_from_file();
                $this->DeviceCap['status'] = 'loaded from file';
            }else{
                debug2log('Unknow device, get all cap from Scanimage');
                $new_device = true;
                if ((strlen($this->DeviceCap['DeviceUri']) > 2) || $this->Debug) {
                    // build configuration from scanimage output
                    // scanimage call and gather output
                    $this->DeviceCap['ScanImgParams'] = $this->getScanImageParams();
                    $this->DeviceCap['Brightness'] = $this->getBrightness();
                    $this->DeviceCap['Contrast'] = $this->getContrast();
                    $this->DeviceCap['Modes'] = $this->getModes();
                    $this->DeviceCap['Resolution'] = $this->getResolution();
                    $this->DeviceCap['Sources'] = $this->getSources();
                    $this->DeviceCap['Geometry'] = $this->getGeometry();
                    /*if(!$test_mode){
                        unset($this->DeviceCap['ScanImgParams']);
                    }*/
                }
            }
            // save here
            if ($new_device) {
                debug2log('Save config to file for '.$this->DeviceCap['DeviceName']);
                $this->save_device_to_file($this->DeviceCap);
            }
        }

        public function AsArray() {
            return $this->DeviceCap;
        }

        public function AsJson(){
            return json_encode($this->DeviceCap);
        }

        private function device_known() {
            $cfg_file_path = $this->get_config_path($this->DeviceCap['DeviceName']);
            return file_exists($cfg_file_path[0].$cfg_file_path[1]);
        }

        private function get_config_path($scanner_name) {
            debug2log('get config path for '.$this->DeviceCap['DeviceName']);
            $cfgname = sanitize_path($scanner_name).'.txt';
            debug2log('config name is '.$cfgname);
            debug2log('scanner dir is '.$this->Config['scanner_dir']);
            debug2log('scanner dir real is '.realpath($this->Config['scanner_dir']));
            
            return array(realpath($this->Config['scanner_dir']).'/', $cfgname);
        }

        private function get_device_config_from_file() {
            $device_name = $this->DeviceCap['DeviceName'];
            $bkp_uri = $this->DeviceCap['DeviceUri'];
            $cfg_file_path = $this->get_config_path($device_name);
            $scanner_config = file_get_contents($cfg_file_path[0].$cfg_file_path[1]);

            $device_cap_bkp = json_decode($scanner_config, true);
            $this->DeviceCap = array_merge($device_cap_bkp, $this->DeviceCap);
            $this->DeviceCap['DeviceUri'] = $bkp_uri;
            $this->DeviceCap['status'] = 'loaded from file';
        }

        private function save_device_to_file($device_cap) {
            debug2log('entering save scanner config function!');
            $tmp_device = array_merge(array(), $device_cap);
            $device_name = $tmp_device['DeviceName'];
            $cfg_file_path = $this->get_config_path($device_name);

            debug2log('config path is '.$cfg_file_path[0].$cfg_file_path[1]);
            //Cleanup unnecessary key/value
            unset($tmp_device['DeviceUri']);

            // Convert JSON data from an array to a string
            $jsonString = json_encode($tmp_device, JSON_PRETTY_PRINT);
            $file_path = $cfg_file_path[0].$cfg_file_path[1];
            debug2log('jsonString is '.$jsonString);
            try {
                // Write in the file
                debug2log('file_path is '.$file_path);
                //write json to file
                if (file_put_contents($file_path, $jsonString)) {
                    debug2log('device config saved');
                }else{
                    debug2log('device config not saved'); 
                }
                
            }catch(Exception $e){
                debug2log('Exception received : ',  $e->getMessage(), '\n');
            }

        }

        private function getDeviceName() {
            $start = strpos($this->DeviceLine, 'is a ') + 5;
            $length = strlen($this->DeviceLine) - $start;
            $scanner_name = str_replace('_', ' ', substr($this->DeviceLine, $start, $length));
            unset($start);
            unset($length);
            return $scanner_name;
        }

        public function getDeviceUri() {
            // get scanner name
            $start = strpos($this->DeviceLine, "`") + 1;
            $length = strpos($this->DeviceLine, "'") - $start;
            $scanner = ''.substr($this->DeviceLine, $start, $length).'';
            unset($start);
            unset($length);
            return $scanner;
        }

        public function getScanImageParams() {
            $this->ScanCmd->run(" -A -d".$this->DeviceCap["DeviceUri"]);
            return $this->ScanCmd->ScanImgOutput;
        }

        public function getBrightness() {
            $result = array();
            $result['min'] = 0;
            $result['max'] = 100;
            $result['default'] = 50;
    

            $result['enabled'] = 'false';
            $result_brightness = preg_grep('/--brightness /', $this->DeviceCap['ScanImgParams']);
            if(count($result_brightness) > 0) {
                $brightness_line = end($result_brightness);
                if(strpos($brightness_line, 'inactive') === false) {
                    $result['enabled'] = 'true';
                    $brightness_minmax = explode('..', preg_replace('/^.*--brightness ([-|0-9..]*)[ \t].*$/iU','$1', $brightness_line));
                    preg_match("/\[(.*?)\]/", $brightness_line, $brightness_default_array);
                    $result['min'] = $brightness_minmax[0];
                    $result['max'] = $brightness_minmax[1];
                    $result['default'] = $brightness_default_array[1];
                    
                    unset($brightness_minmax);
                    unset($brightness_default_array);
                }
                unset($brightness_line);
            }
            unset($result_brightness);
            return $result;
        }

        public function getContrast() {
            $result = array();
            $result['min'] = 0;
            $result['max'] = 100;
            $result['default'] = 50;

            $result['enabled'] = 'false';
            $result_contrast = preg_grep('/--contrast /', $this->DeviceCap['ScanImgParams']);
            if(count($result_contrast) > 0) {
                $contrast_line = end($result_contrast);
                if(strpos($contrast_line, 'inactive') === false) {
                    $result['enabled'] = 'true';
                    $contrast_minmax = explode('..', preg_replace('/^.*--contrast ([-|0-9..]*)[ \t].*$/iU','$1', $contrast_line));
                    preg_match("/\[(.*?)\]/", $contrast_line, $contrast_default_array);
                    $result['min'] = $contrast_minmax[0];
                    $result['max'] = $contrast_minmax[1];
                    $result['default'] = $contrast_default_array[1];
                    
                    unset($contrast_minmax);
                    unset($contrast_default_array);
                }
                unset($contrast_line);
            }
            unset($result_contrast);
            return $result;
        }

        public function getModes() {
            $result = array();

            $result_mode = preg_grep('/--mode /', $this->DeviceCap['ScanImgParams']);
            $result_mode = end($result_mode);

            $modes = preg_replace('/^.*--mode (.*) \[.*\]$/iU','$1', $result_mode);
            preg_match("/\s\[(.*)\]+$/", $result_mode, $mode_default_array);
            $result['list'] = explode('|', $modes);
            $result['default'] = $mode_default_array[1];
            unset($sane_result_mode);
            unset($mode_default_array);
            return $result;
        }

        public function getResolution() {
            $result = array();

            $result_reso = preg_grep('/--resolution /', $this->DeviceCap['ScanImgParams']);
            $result_reso = end($result_reso);

            preg_match("/\[(.*?)\]/", $result_reso, $resolution_default_array);
            $result['default'] = $resolution_default_array[1];
            $start = strpos($result_reso, 'n') + 2;
            $length = strpos($result_reso, 'dpi') - $start;
            $result['str'] = '' . substr($result_reso, $start, $length) . '';
            unset($start);
            unset($length);
            // change "|" separated string $list into array of values or generate a range of values.
            $length = strpos($result['str'], '..');
            if ($length === false) {
                $resolution_list = explode('|' , $result['str']);
                $result['max'] = (int)end($resolution_list);
                $result['min'] = (int)reset($resolution_list);
                $result['list'] = $resolution_list;
            } else {
                //generate list if unknown
                $result['list'] = array();
                $result['min'] = (int)substr($result['str'], 0, $length);
                $result['max'] = (int)substr($result['str'], $length + 2);

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
                        $result['list'][] = $res;
                    }
                }

                // higher resolutions
                $res = 1000;
                while (($res >= $resolution_min) && ($res < $resolution_max)) {
                    $result['list'][] = $res;
                    $res += 200;
                }

                $result['list'][] = $resolution_max;
            }
            return $result;
        }

        public function getSources() {
            $result = array();
            $result['enabled'] = 'false';

            $result_source = preg_grep('/--source /', $this->DeviceCap['ScanImgParams']);
            $result_source = end($result_source);
            $sources = preg_replace('/^.*--source (.*) \[.*\]$/iU','$1', $result_source);

            if(strpos($result_source, 'inactive') === false) {
                $result['enabled'] = 'true';
                $result['sources'] = array();

                $source_list = explode('|', $sources);
                preg_match("/\[(.*?)\]/", $result_source, $source_default_array);
                $source_default = $source_default_array[1];
                foreach($source_list as $DeviceSource) {
                    $result['sources'][$DeviceSource] = 'Flatbed';
                    if (preg_match('/flatbed/i', $DeviceSource)) {
                        $result['sources'][$DeviceSource] = 'Flatbed';
                    }else if (preg_match('/auto|feed|adf/i', $DeviceSource)) {
                        $result['sources'][$DeviceSource] = 'ADF';
                    }
                }
                unset($result_source);
                unset($source_default_array);
            }
            return $result;
        }

        function getGeometry() {
            $result = array();

            $result_tlx = preg_grep('/-l /', $this->DeviceCap['ScanImgParams']);
            $result_tlx = end($result_tlx);
            $result['tlx'] = preg_replace('/^.*-l.*\[(.*)\]$/', '$1', $result_tlx);
            $result_tly = preg_grep('/-t /', $this->DeviceCap['ScanImgParams']);
            $result_tly = end($result_tly);
            $result['tly'] = preg_replace('/^.*-t.*\[(.*)\]$/', '$1', $result_tly);

            $result_width = preg_grep('/-x /', $this->DeviceCap['ScanImgParams']);
            $result_width = end($result_width);
            $result['width'] = preg_replace('/^.*-x.*\[(.*)\]$/', '$1', $result_width);
            $result_height = preg_grep('/-y /', $this->DeviceCap['ScanImgParams']);
            $result_height = end($result_height);
            $result['height'] = preg_replace('/^.*-y.*\[(.*)\]$/', '$1', $result_height);
            unset($result_tlx);
            unset($result_tly);
            unset($result_width);
            unset($result_height);
            
            return $result;

        }
    }
?>
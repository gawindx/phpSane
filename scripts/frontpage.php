<?php
    // Avoid direct access ; redirect to '/'
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        if (ob_get_contents()) ob_clean(); // ob_get_contents() even works without active output buffering
        header('Location: /');
        die;
    }
?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=yes'>
    <meta name='description' content='Sane Web Client GUI.'>
	
	<meta name='author' content='root'>
	<meta name='robots' content='noindex'>
	<meta http-equiv='content-type' content='text/html; charset=UTF-8'>

	<title><?php echo($phpsane_version);?></title>
	<link rel='icon' href='favicon.ico' type='image/png' />
	<link rel='shortcut icon' href='favicon.ico' type='image/png' />
    
    <link rel='stylesheet' href='incl/bootstrap-5.0.2-dist/css/bootstrap.min.css' />
    <link rel='stylesheet' href='css/jquery.jcrop.css' type='text/css' />
	<link rel='stylesheet' href='css/jquery.msdropdown.min.css' type='text/css' />
    <link rel='stylesheet' href='css/nouislider.min.css' type='text/css' />
	<link rel='stylesheet' href='css/phpsane.css' type='text/css' />
    
    <script src='incl/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js'></script>
    <script src='js/jquery-3.7.1.min.js' type='text/javascript'></script>
    <script src='js/jquery.jcrop.min.js' type='text/javascript'></script>
    <script src='js/jquery.msdropdown.min.js' type='text/javascript'></script>
    <script src='js/nouislider.min.js' type='text/javascript'></script>

 </head>
<body>
    <header class='container-fluid'>
        <div class='row mb-3'>
            <div class='col-md-6 col-8'>
                <a href='index.php'>
                    <img src='images/logo.jpg' alt='phpSane' id='logo' class='logo'>
                </a>
            </div>
            <div class='col-md-6 col-4 text-right'>

                <div class='dropdown'>
                <button class='btn btn-primary dropdown-toggle' type='button' id='dropdownSettingsButton' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        <img src='images/btn_preview.png' alt='Lang'>
                        <span data-lang='settings'></span>
                </button>
                <div class='dropdown-menu dropdown-menu-end' aria-labelledby='dropdownSettingsButton' id='SettingsButtonMenu'>
                    <table class='table' id='tab_settings'>
                        <tr>
                            <th data-lang='language'></th>
                            <td class='value_column' colspan=2>
                                <select id='language_select' size=1 is='ms-dropdown'>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th data-lang='producer'></th>
                            <td class='value_column'>
                                <input type='text' name='producer' id='producer' value='phpSane' size='4'>
                            </td>
                        </tr>
                        <tr>
                            <th data-lang='author'></th>
                            <td class='value_column'>
                                <input type='text' name='author' id='author' value='phpSane' size='4'>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    <!--    <nav class='navbar navbar-expand-lg navbar-light bg-light d-md-none'>
            <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarNav' aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>
                <span class='navbar-toggler-icon'></span>
            </button>
            <div class='collapse navbar-collapse' id='navbarNav'>
                <ul class='navbar-nav'>
                    <li class='nav-item'>
                        <button class='nav-link btn btn-primary'>
                            <img src='images/btn_preview.png' alt='File Icon'> Fichier
                        </button>
                    </li>
                    <li class='nav-item'>
                        <button class='nav-link btn btn-primary'>
                            <img src='images/btn_preview.png' alt='Shield Icon'> Bouclier
                        </button>
                    </li>
                    <li class='nav-item ml-auto'>
                        <button class='nav-link btn btn-primary'>
                            <img src='images/btn_preview.png' alt='Gear Icon'> Paramètres
                        </button>
                    </li>
                </ul>
            </div>
        </nav>-->
    </header>

    <div class='container' id='phpsane_container'>
        <div class='row'>
            <div class='col-sm-12 col-md-7 col-lg-4 mt lscape-preview'>
                <div class='row'>
                    <div class='col-12 lscape-buttons'>
                        <div class='row' id='tab_menu_buttons'>
                            <div class='col-sm-6 col-sm-pull-6 lscape-button'>
                                <button class='btn btn-primary' id='menu_button_accept' onclick='getScan()'> 
                                    <img src='images/btn_accept.png' alt='Scan'>
                                    <span data-lang='scan'></span>
                                </button>
                            </div>
                            <div class='col-sm-6 col-sm-push-6 lscape-button'>
                                <button class='btn btn-primary lscape-button' id='menu_button_preview' onclick='getPreviewScan()'>
                                    <img src='images/btn_preview.png' alt='Preview'>
                                    <span data-lang='preview'></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class='col-12 lscape-preview-img'>
                        <div class='row mt-3'>
                            <div class='col-sm-12 d-flex justify-content-center'>
                                <img src='images/scan.jpg' alt='Preview' class='img-fluid' id='preview_image'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-sm-0 col-md-5 col-lg-4 d-none d-md-block d-lg-block mt-5 pt-2 lscape-d-none' id='settings_column'>
                <table class='table' id='tab_menu_settings'>
                    <tr>
                        <th data-lang='device'></th>
                    </tr>
                    <tr>
                        <td class='value_column' colspan=2>
                            <select id='scanner_select' size=1 is='ms-dropdown'>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th colspan='3' data-lang='scan_area'></th>
                    </tr>
                    <tr>
                        <td data-lang='page_size'></td>
                        <td class='value_column'>
                            <select id='pagesize_select' name='pagesize_select' size=1 is='ms-dropdown'>
                            </select>
                        </td>
                        <td class='unit_column'></td>
                    </tr>
                    <tr>
                        <td data-lang='left'></td>
                        <td class='value_column geo-input'>
                            <input type='text' name='pos_x' id='pos_x' value='0' size='4' maxlength='3'>
                        </td>
                        <td class='unit_column'>mm</td>
                    </tr>
                    <tr>
                        <td data-lang='top'></td>
                        <td class='value_column geo-input'>
                            <input type='text' name='pos_y' id='pos_y' value='0' size='4' maxlength='3'>
                        </td>
                        <td class='unit_column'>mm</td>
                    </tr>
                    <tr>
                        <td data-lang='width'></td>
                        <td class='value_column geo-input'>
                            <input type='text' name='geometry_x' id='geometry_x' value='210' size='4' maxlength='3'>
                        </td>
                        <td class='unit_column'>mm</td>
                    </tr>
                    <tr>
                        <td data-lang='height'></td>
                        <td class='value_column geo-input'>
                            <input type='text' name='geometry_y' id='geometry_y' value='297' size='4' maxlength='3'>
                        </td>
                        <td class='unit_column'>mm</td>
                    </tr>
                    <tr class='scan_options_row'>
                        <th colspan='3' data-lang='scan_options'></th>
                    </tr>
                    <tr id='format_row' class='scan_options_row d-none'>
                        <td data-lang='file_format'></td>
                        <td class='value_column'>
                            <select id='format_select' name='format_select' is='ms-dropdown'>
                            </select>
                        </td>
                        <td class='unit_column'></td>
                    </tr>
                    <tr id='fname_row' class='scan_options_row d-none'>
                        <td data-lang='file_name'></td>
                        <td class='value_column'>
                            <input type='text' name='scan_filename' id='scan_filename' size='40'>
                        </td>
                        <td class='unit_column'></td>
                    </tr>
                    <tr id='mode_row' class='scan_options_row d-none'>
                        <td data-lang='mode'></td>
                        <td class='value_column'>
                            <select name='mode_select' id='mode_select' is='ms-dropdown'>
                            </select>
                        </td>
                        <td class='unit_column'></td>
                    </tr>
                    <tr id='bright_row' class='scan_options_row d-none'>
                        <td data-lang='brightness'></td>
                        <td class='value_column'>
                            <div id='brightness_slider' class='noUiSlider'></div>
                            <input id='brightness' type='text' value='50' name='brightness' maxlength='3'>
                        </td>
                        <td class='unit_column'>%</td>
                    </tr>
                    <tr id='contrast_row' class='scan_options_row d-none'>
                        <td data-lang='contrast'></td>
                        <td class='value_column'>
                            <div id='contrast_slider' class='noUiSlider'></div>
                            <input id='contrast' type='text' value='50' name='contrast' maxlength='3'>
                        </td>
                        <td class='unit_column'>%</td>
                    </tr>
                    <tr id='reso_row' class='scan_options_row d-none'>
                        <td data-lang='resolution'></td>
                        <td class='value_column'>
                            <select name='resolution_select' id='resolution_select' is='ms-dropdown'>
                            </select>
                        </td>
                        <td class='unit_column' data-lang='dpi'></td>
                    </tr>
                    <tr id='source_row' class='scan_options_row d-none'>
                        <td data-lang='source'></td>
                        <td class='value_column'>
                            <select id='source_select' name='source_select' size=1 is='ms-dropdown'>
                            </select>
                        </td>
                        <td class='unit_column'></td>
                    </tr>
                </table>
            </div>
            <div class='col-sm-0 col-md-12 col-lg-4 d-none d-md-block mt-5 pt-2 lscape-d-none' id='file_column'>
                <div class='file-explorer table-responsive'>
                    <table class='table table-striped table-bordered'>
                        <thead>
                            <tr>
                                <th scope='col'> </th>
                                <th scope='col' data-lang='file_name'></th>
                                <th scope='col'> </th>
                            </tr>
                        </thead>
                        <tbody id='file_list'>
                        </tbody>
                    </table>
                </div>    
            </div>
        </div>
    </div>
    <script src='js/functions.js'></script>
</body>
</html>

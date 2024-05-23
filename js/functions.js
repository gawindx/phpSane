// general variable
var devices_list;
var jcrop_api;
var saved_scan;
var ui_params;
var languages;
var devices_ok = false;
var files_ok = false;
var params_ok = false;
var languages_ok = false;


//Todo :
// - push all ajax request in array and add
//   function to abort() all requests on specific event ?
// - Move all msdropdown event to native select event
// - Move all msdropdown creation to select peuplate and refresh msdropdown
// - Finally, move all msdropdown to bootstrap dropdown
// 

var request_arr;

// format's variable
const FormatObject = [ 
	{ type: 'jpg', description: 'jpeg_image', image: 'images/filetype_jpg.png' },
	{ type: 'pnm', description: 'pnm_image', image: 'images/filetype_pnm.png' },
	{ type: 'tif', description: 'tiff_image', image: 'images/filetype_tif.png' },
	{ type: 'png', description: 'png_image', image: 'images/filetype_png.png' },
	{ type: 'bmp', description: 'bmp_image', image: 'images/filetype_bmp.png' },
	{ type: 'pdf', description: 'pdf_document', image: 'images/filetype_pdf.png' },
	{ type: 'txt', description: 'txt_document', image: 'images/filetype_txt.png' }
	];
const flatbed_file_pattern = new RegExp('jpg|pnm|tif|png|bmp|pdf|txt');
const adf_file_pattern = new RegExp('tif|pdf|txt');

// Color mode variable's
const color_pattern = new RegExp('color|24 ?bit', 'i');
const black_pattern = new RegExp('black|mono|lineart', 'i');
const gray_pattern = new RegExp('gray', 'i'); 

// scan area and crop variables
var ScanArea = {};
var default_page_width_mm = 210;
var default_page_height_mm = 297;
var pos_x = 0;
var pos_y = 0;

// Define Physical Glasses Dimension Variable 
var glasses = {};
glasses.width_mm = 215;
glasses.height_mm = 297;

// Define preview geometry variable
var geometry = {};
geometry.scale = 2;
geometry.x = glasses.width_mm - (glasses.width_mm - default_page_width_mm);
geometry.y = glasses.height_mm;
geometry.pxmax_x = getSizePx(glasses.width_mm);
geometry.pxmax_y = getSizePx(geometry.y);

// Start and Initialize msDropDown
$(document).ready(function() {
	//enable msDropDown on select boxes
	try {
		$('body select').msDropdown({reverseMode:true});
	} catch(e) {
		alert(e.message);
	}
	devices_ok = false;
	files_ok = false;
	params_ok = false;
	languages_ok = false;
	// Init phpsane frontend
	get_all_devices();
	get_files();
	get_params();
	get_languages();
});

$(document).on('RequestOk', function(){
	if (devices_ok && files_ok && params_ok && languages_ok) {
		$(document).trigger('UIReady');
	} 
});

$(document).on('UIReady', function(){
	translate_ui();
	UpdateLanguage();
});

// handle error on ajax's request
function requestError(xhr,status,msg) {
	alert('xhr: ' + xhr +'\n' + 'status: ' + status +'\n' + 'msg: ' + msg);
}

// retrieve list of saved files
function get_files() {
	// Ajax request
	$.ajax({
		type: 'POST',
		url: 'index.php',
		data: { method: 'get_files' },
		contentType: 'application/x-www-form-urlencoded',
		dataType: 'json',
		success: function(response) {

			// verify response (status http 200 but error or no result on server side)
			if(response.result == 'false') {
				requestError(xhr, status, 'get_all_devices bad response');
			}

			saved_scan = response.files;
			UpdateFileList();
			files_ok = true;
			$(document).trigger('RequestOk');
		},
		error: requestError,
	});
}

// retrieve all params
function get_params() {
	// Ajax request
	$.ajax({
		type: 'POST',
		url: 'index.php',
		data: { method: 'get_params' },
		contentType: 'application/x-www-form-urlencoded',
		dataType: 'json',
		success: function(response) {

			// verify response (status http 200 but error or no result on server side)
			if(response.result == 'false') {
				requestError(xhr, status, 'get_all_devices bad response');
			}

			ui_params = response.params;
			UpdateFileList();
			params_ok = true;
			$(document).trigger('RequestOk');
		},
		error: requestError,
	});
}

// Update list of saved scan
function UpdateFileList() {

	if (typeof saved_scan == 'undefined') {
		return;
	}

	saved_scan.sort(function (a, b) {
			return a.file_modtime - b.file_modtime; 
		});

	$('#file_list').empty();

	$.each(saved_scan, function() {
		var row_id = this.file_name.toLowerCase().replace(/\s/g, '_').replace(/\./g,'_').replace(/-/g,'_');
		new_row  = '<tr id="' + row_id + '">';
		new_row += '<th scope="row">';
		new_row += '<input class="selected_files" type="checkbox" name="selected_files[]" ';
		new_row += 'value="' + this.file_name + '" title="Select file">';
		new_row += '</th>';
		new_row += '<td>';
		new_row += '<img src="' + this.file_icon + '" alt="PDF">';
		new_row += '<a href="' + this.file_path + '" target="_blank">' + this.file_name+ '</a>';
		new_row += '</td>';
		new_row += '<td></td>';
		new_row += '</tr>';
		$('#file_list').append(new_row);

		var thumb_html = '<img src="' + this.thumbnail + '" alt="' + this.file_name + '">';
		$('#' + row_id + '').popover({
			html: true,
			content: thumb_html,
			container: 'body',
			delay: { 'show': 500, 'hide': 100 },
			placement: 'left',
			fallbackPlacement: ['left', 'top', 'bottom', 'right'],
			trigger: 'hover',
		});
	});

}

// retrieve all params
function get_languages() {
	// Ajax request
	$.ajax({
		type: 'POST',
		url: 'index.php',
		data: { method: 'get_languages' },
		contentType: 'application/x-www-form-urlencoded',
		dataType: 'json',
		success: function(response) {

			// verify response (status http 200 but error or no result on server side)
			if(response.result == 'false') {
				requestError(xhr, status, 'get_all_devices bad response');
			}

			languages = response.languages;
			languages_ok = true;
			$(document).trigger('RequestOk');
		},
		error: requestError,
	});
}

//
function translate_ui() {
	var language_nodes = $('[data-lang]');
	$.each(language_nodes, function() {
		var translated_text = getTranslate(this.dataset['lang']);
		this.innerHTML = translated_text;
	});
}

//
function getTranslate(text) {
	var lang_id = ui_params.language;
	var trans_txt = languages[lang_id][text];
	if((typeof trans_txt == 'undefined') || (trans_txt == '')) {
		trans_txt = text;
	}
	return trans_txt;
}

// retrieve list of device from server
function get_all_devices() {

	// initialise variable
	var Scan_Select =  $('#scanner_select').msDropdown().data("dd");

	// Ajax request
	$.ajax({
		type: 'POST',
		url: 'index.php',
		data: { method: 'get_all_devices' },
		contentType: 'application/x-www-form-urlencoded',
		dataType: 'json',
		success: function(response) {

			// verify response (status http 200 but error or no result on server side)
			if(response.result == 'false') {
				requestError(xhr, status, 'get_all_devices bad response');
			}

			// test list of devices. Show error message with refresh button if no device. Init page if any.
			if((response.devices).length > 0) {
				var Scan_Select_count = Scan_Select.length;
				devices_list = response['devices'];

				// disable events
				Scan_Select.off('change');

				// initialize (remove all option)
				for (let i = 0; i < Scan_Select_count; i++) {
					Scan_Select.remove(0);
				}
	
				// add option for each device
				$.each(devices_list, function(  ) {
					var device_img;
					var device_value;
					var device_text;
					var prefix_value;
					var device_uri = this.DeviceUri;
				
					if(device_uri.startsWith('net')) {
						// Remote device
						prefix_value = 'rmt_';
					}else{
						// Local Device
						prefix_value = 'lcl_';
					}
					device_img = 'images/' + prefix_value + 'device.png';

					device_value = prefix_value + this.DeviceName.toLowerCase().replace(/\s/g, '_');
					device_text = this.DeviceName;
					Scan_Select.add({text:device_text, value:device_value, image:device_img});
				});

				// enable events
				Scan_Select.on('change', function() {
					UpdateScan_Selects(this.selectedIndex);
				});

				// select default value
				$('#scanner_select').selectedIndex = 0;
				Scan_Select.refresh();

				// fake event (force init of device option's section)
				$('#scanner_select').trigger('change');

				$('#preview_image').Jcrop({
					bgColor:	'black',
					onChange:   showCoords,
					onSelect:   showCoords,
					onRelease:  clearCoords,
					setSelect: [ 	(geometry.scale * pos_x) , 
									(geometry.scale * pos_y),
									(geometry.scale * (pos_x + geometry.x)),
									(geometry.scale * (pos_y + geometry.y))],
				}, function(){
					jcrop_api = this;
				});

				$('td.geo-input > input').on('change', function() {
					var pos_x = getSizePx(parseInt($('#pos_x').val()));
					var pos_y = getSizePx(parseInt($('#pos_y').val()));
					var geometry_x = getSizePx(parseInt($('#geometry_x').val()));
					var geometry_y = getSizePx(parseInt($('#geometry_y').val()));

					if ((pos_x + geometry_x) >= geometry.pxmax_x) {
						geometry_x = geometry.pxmax_x;
					}else{
						geometry_x = pos_x + geometry_x;
					}
					if ((pos_y + geometry_y) >= geometry.pxmax_y) {
						geometry_y = geometry.pxmax_y;
					}else{
						geometry_y = pos_y + geometry_y;
					}
					try{
						//jcrop_api.setSelect([pos_x, pos_y, geometry_x, geometry_y]);
						jcrop_api.destroy();
						$('#preview_image').Jcrop({
							bgColor:	'black',
							onChange:   showCoords,
							onSelect:   showCoords,
							onRelease:  clearCoords,
							setSelect: [pos_x, pos_y, geometry_x, geometry_y],
						}, function(){
							jcrop_api = this;
						});
					} catch(e) {
						console.log(e.message);
					}
				});
				devices_ok = true;
				$(document).trigger('RequestOk');
			}else{
				//show error page
			}
		},
		error: requestError,
	});
}

// initialise all select
function UpdateScan_Selects(Device_index) {
	// disable event on slider befure updating
	$("#brightness").off('change');
	$("#contrast").off('change');
	UpdateResolution_Select(Device_index);
	UpdateSource_Select(Device_index);
	UpdateMode_Select(Device_index);
	InitBrightnessSlider(Device_index);
	InitContrastSlider(Device_index);
	UpdateDeviceGeometry(Device_index);
	UpdatePageSize();
}

// initialise select for all supported resolution
function UpdateResolution_Select(Device_index) {
	//initialize variable
	var device_cap = devices_list[Device_index];
	var Res_Select =  $('#resolution_select').msDropdown().data('dd');
	var Res_Select_count = Res_Select.length;
	var default_res;

	Resolutions = device_cap.Resolution;
	Resolution_list = Resolutions.list;

	// initialize (remove all option)
	for (let i = 0; i < Res_Select_count; i++) {
		Res_Select.remove(0);
	}

	// add all option and select a default value near 300dpi
	$.each(Resolution_list, function() {
		// convert to number
		var Res_Int = parseInt(this);

		// compare with tolerance of 300 +/- 50dpi
		if ((Res_Int > 249) && (Res_Int < 351)){
			default_res = '' + this + '';;
		}
		
		// define to 300dpi if no corresponding value
		if (default_res == ''){
			default_res = '300';
		}
		Res_Select.add({text:this, value:this});
	});

	// select default value
	$('#resolution_select').val(default_res);
	Res_Select.refresh();

	// show option
	if($('#reso_row').hasClass('d-none')) {
		$('#reso_row').removeClass('d-none');
	}
}

// initialise select for supported source
function UpdateSource_Select(Device_index) {

	var device_cap = devices_list[Device_index];
	var Src_Select =  $('#source_select').msDropdown().data("dd");
	var Src_Select_count = Src_Select.length;
	
	// Disable events
	Src_Select.off('change');

	// initialize (remove all option)
	for (let i = 0; i < Src_Select_count; i++) {
		Src_Select.remove(0);
	}
	
	Sources = device_cap.Sources;
	if(Sources.enabled == 'false') {
		// hide option if no source
		if(!$('#source_row').hasClass('d-none')){
			$('#source_row').addClass('d-none');
		}
	}else{
		Sources_list = Sources.sources;
		// add all option
		$.each(Sources_list, function(Src_lbl, Src_type) {
			var Src_value = Src_lbl.toLowerCase().replace(/\s/g, '_');
			var Src_img = 'images/source_' + Src_type.toLowerCase() + '.png';
			Src_Select.add({text:Src_lbl, value:Src_value, image:Src_img, description: Src_type.toLowerCase()});
		});

		// enable events
		Src_Select.on('change', function() {
			UpdateFileType_Select();
		});

		// Select default value		
		$('#source_select').selectedIndex = 0;
		Src_Select.refresh();
		$('#source_select').trigger('change');

		// show option
		if($('#source_row').hasClass('d-none')) {
			$('#source_row').removeClass('d-none');
		}
	}
}

// initialise select for supported/enabled file type
function UpdateFileType_Select() {
	var do_format = ui_params.do_format;
	var Src_type = 'flatbed';
	var file_pattern = 'flatbed_file_pattern';
	var Frmt_Select =  $('#format_select').msDropdown().data("dd");
	var Frmt_Select_count = Frmt_Select.length;

	// initialize (remove all option)
	for (let i = 0; i < Frmt_Select_count; i++) {
		Frmt_Select.remove(0);
	}
	
	// hidden's row means source disabled (only one)
	if($('#source_row').hasClass('d-none')) {
		Src_type = 'flatbed';
	}else{
		Src_type = $('#source_select option:selected').data('description');
	}
	file_pattern = Src_type + '_file_pattern';
	
	// add option depending of source type
	$.each(FormatObject, function() {
		if((this.type).match(eval(file_pattern)) && (do_format[this.type])){
			Frmt_Select.add({text:getTranslate(this.description), value:this.type, image:this.image});
		}
	});

	// Select default value
	$('#format_select').val('pdf');
	Frmt_Select.refresh();

	// show option
	if($('#format_row').hasClass('d-none')) {
		$('#format_row').removeClass('d-none');
	}
}

// initialise select for supported modes
function UpdateMode_Select(Device_index) {
	var device_cap = devices_list[Device_index];
	var Mode_Select =  $('#mode_select').msDropdown().data("dd");
	var Mode_Select_count = Mode_Select.length;

	// initialize (remove all option)
	for (let i = 0; i < Mode_Select_count; i++) {
		Mode_Select.remove(0);
	}

	Modes = device_cap.Modes.list;
	Mode_default = device_cap.Modes.default;
	
	// add option for each mode
	$.each(Modes, function() {
		var mode_str = '' + this + '';
		var value_str = mode_str.toLowerCase().replace(/\s/g, '_');
		var image_path;
		
		// set image path based on mode type
		switch(true) {
			case (mode_str.match(color_pattern) !== null):
				image_path = 'images/mode_color.png';
				break;
			case (mode_str.match(black_pattern) !== null):
				image_path = 'images/mode_lineart.png';
				break;
			case (mode_str.match(gray_pattern) !== null):
				image_path = 'images/mode_gray.png';
				break;
		}

		// add option
		Mode_Select.add({text:mode_str, value:value_str, image:image_path});
	});

	// select default value
	$('#mode_select').val(Mode_default.toLowerCase().replace(/\s/g, '_'));
	Mode_Select.refresh();

	// show option
	if($('#mode_row').hasClass('d-none')) {
		$('#mode_row').removeClass('d-none');
	}
}

// initialize Brightness Slider
function InitBrightnessSlider(Device_index) {
	var device_cap = devices_list[Device_index];
	var device_brightness = device_cap.Brightness;

	// Verify if brightness is enabled and hide row if not
	if(device_brightness.enabled === 'false'){
		if(!$('#bright_row').hasClass('d-none')) {
			$('#bright_row').addClass('d-none');
		}
	}else{
		var BSlider = document.getElementById('brightness_slider');
		var brightness_min = parseInt(device_brightness.min);
		var brightness_max = parseInt(device_brightness.max);
		var brightness_default = parseInt(device_brightness.default);

		// be sure that slider step represents 1%
		const full_range = (0 - brightness_min) + brightness_max;
		var steps = full_range/100;
		
		// if slider is already initialized, create will fail
		// if not, it create the slider and in any case  
		// we running updateOptions to set min/max and value
		try{
			// try to create slider
			noUiSlider.create(BSlider, {
				range: {
					'min':brightness_min,
					'max':brightness_max,
				},
				start:brightness_default,
				step: steps,
				handles: 1,
			});
		} catch {
			// 
		}finally{
			// disable events
			$('#brightness').off('change');
			BSlider.noUiSlider.off('update');

			// at this point, slider is created
			BSlider.noUiSlider.updateOptions({
				range: {
					'min':brightness_min,
					'max':brightness_max,
				},
				start:brightness_default,
				step: steps,
			});
			BSlider.noUiSlider.set(brightness_default);

			// update input value on slide
			BSlider.noUiSlider.on('update', function(){
				var brightness_pos = parseInt(BSlider.noUiSlider.get());
				var brightnessValue = parseInt(((brightness_pos - brightness_min) / full_range) * 100);
				$('#brightness').val(brightnessValue);
			});
		}
		
		// update slider on input value change
		$('#brightness').on('change', function() {
			var brightnessValue = parseInt(this.value);
			brightnessValue = ((brightnessValue / 100) * full_range ) + brightness_min;
			if(!isNaN(brightnessValue)) {
				BSlider.noUiSlider.set(brightnessValue);
			} 
		});

		// show option
		if($('#bright_row').hasClass('d-none')) {
			$('#bright_row').removeClass('d-none');
		}
	}
}

// initialize Contrast Slider
function InitContrastSlider(Device_index) {
	var device_cap = devices_list[Device_index];
	var device_contrast = device_cap.Contrast;

	if(device_contrast.enabled === 'false'){
		if(!$('#contrast_row').hasClass('d-none')) {
			$('#contrast_row').addClass('d-none');
		}
	}else{
		var CSlider = document.getElementById('contrast_slider');
		var contrast_min = parseInt(device_contrast.min);
		var contrast_max = parseInt(device_contrast.max);
		var contrast_default = parseInt(device_contrast.default);
		
		// be sure that slider step represents 1%
		const full_range = (0 - contrast_min) + contrast_max;
		var steps = full_range/100;

		// if slider is already initialized, create will fail
		// if not, it create the slider and in any case  
		// we running updateOptions to set/reset min/max and value
		try{
			// try to create slider
			noUiSlider.create(CSlider, {
				range: {
					'min':contrast_min,
					'max':contrast_max,
				},
				start: contrast_default,
				step: steps,
				handles: 1,
			});
		} catch {
			//
		}finally{
			// disable events
			$('#contrast').off('change');
			CSlider.noUiSlider.off('update');

			// at this point, slider is created
			CSlider.noUiSlider.updateOptions({
				range: {
					'min':contrast_min,
					'max':contrast_max,
				},
				start:contrast_default,
				step: steps,
			});
			CSlider.noUiSlider.set(contrast_default);

			// update input value on slide
			CSlider.noUiSlider.on('update', function(){
				var contrast_pos = parseInt(CSlider.noUiSlider.get());
				var contrastValue = parseInt(((contrast_pos - contrast_min) / full_range) * 100);
				$('#contrast').val(contrastValue);
			});
		}

		// update slider on input value change
		$('#contrast').on('change', function() {
			var contrastValue = parseInt(this.value);
			contrastValue = ((contrastValue / 100) * full_range ) + contrast_min;
			if(!isNaN(contrastValue)) {
				CSlider.noUiSlider.set(contrastValue);
			}
		});

		// show option
		if($('#contrast_row').hasClass('d-none')) {
			$('#contrast_row').removeClass('d-none');
		}
	}
}

// initialize geometry for specific device
function UpdateDeviceGeometry(Device_index) {
	var device_cap = devices_list[Device_index];
	var Device_ScanArea = { height:Math.round(parseInt(device_cap.Geometry.height)),
		width:Math.round(parseInt(device_cap.Geometry.width))
	};

	glasses.width_mm = Device_ScanArea.width;
	glasses.height_mm = Device_ScanArea.height;
	geometry.pxmax_x = getSizePx(glasses.width_mm);
	geometry.pxmax_y = getSizePx(glasses.height_mm);

	geometry.scale = ($('#preview_image').height() / glasses.height_mm);
	geometry.x = glasses.width_mm - (glasses.width_mm - default_page_width_mm);
	geometry.y = glasses.height_mm;
	
	var img_width = '' + Math.round(glasses.width_mm * geometry.scale) + 'px';
	var img_height = '' + $('#preview_image').height() + 'px';
	$('#preview_image').css({width:img_width, height:img_height});
}

// initialize pagesize Select
function UpdatePageSize() {
	// Todo : add US size depending language and optional parameters
	var PSize_List = {
		'A0': {'h': 1189, 'w': 841},
		'A1': {'h': 841, 'w': 594},
		'A2': {'h': 594, 'w': 420},
		'A3': {'h': 420, 'w': 297},
		'A4': {'h': 297, 'w': 210},
		'A5': {'h': 210, 'w': 148},
		'A6': {'h': 148, 'w': 105},
		'A7': {'h': 105, 'w': 74},
		'A8': {'h': 74, 'w': 52},
		'A9': {'h': 52, 'w': 37},
		'A10': {'h': 37, 'w': 26},
		};
	var PSize_Select =  $('#pagesize_select').msDropdown().data("dd");
	var PSize_Select_count = PSize_Select.length;

	//Disable events
	PSize_Select.off('change');
	
	// initialize (remove all option)
	for (let i = 0; i < PSize_Select_count; i++) {
		PSize_Select.remove(0);
	}
	// add 'custom' entry	
	PSize_Select.add({text:'Custom Size', value:'0,0', image:'images/pagesize_custom.png'});
	
	// add option for each mode
	$.each(PSize_List, function(Size, h_w) {
		if ((h_w.w <= glasses.width_mm) && (h_w.h <= glasses.height_mm)) {
			var size_str = '' + Size + '';
			var value_str = '' + h_w.w + ',' + h_w.h + '';
			var image_path = 'images/pagesize_' + size_str.toLowerCase() + '.png';
			
			// add option
			PSize_Select.add({text:size_str, value:value_str, image:image_path});
		}
	});

	// enable change event
	//PSize_Select.on('change',PSize_Change);
	PSize_Select.on('change', (function() {
		PSizeChange(this);
	}));

	// Select default value		
	PSize_Select.set('selectedIndex', 1);
	PSize_Select.refresh();
	$('#pagesize_select').trigger('change');
}

// initialize language select
function UpdateLanguage() {
	var Lang_Select =  $('#language_select').msDropdown().data("dd");
	var Lang_Select_count = Lang_Select.length;

	//Disable events
	Lang_Select.off('change');
	
	// initialize (remove all option)
	for (let i = 0; i < Lang_Select_count; i++) {
		Lang_Select.remove(0);
	}
	
	// add option for each mode
	$.each(languages, function(lang_id, lang_data) {
			var lang_str = '' + lang_data.lang_name + '';
			var value_str = '' + lang_id + '';
			var image_path = 'images/lang_' + lang_id + '.gif';
			
			// add option
			Lang_Select.add({text:lang_str, value:value_str, image:image_path});
	});

	// enable change event
	Lang_Select.on('change', (function() {
		LanguageChange(this.value);
	}));

	// Select default value		
	Lang_Select.set('selectedIndex', 1);
	Lang_Select.refresh();
	$('#language_select').trigger('change');
}

// Change Page Size
function PSizeChange(element) {
	pos_x = 0, pos_y = 0;
	PreviewArea = (function (SArea) {
		var Area = (SArea.value).split(',');
		return {height:parseInt(Area[1]), width:parseInt(Area[0])}
	})(element);
	geometry.x = PreviewArea.width, geometry.y = PreviewArea.height;
	
	$('#pos_x').val(pos_x);
	$('#pos_y').val(pos_y);
	$('#geometry_x').val(geometry.x);
	$('#geometry_y').val(geometry.y);

	try{
		if(typeof jcrop_api != 'undefined') {
			jcrop_api.setSelect([getSizePx(pos_x), getSizePx(pos_y), getSizePx((pos_x + geometry.x)), getSizePx((pos_y + geometry.y))]);
		}	
	} catch(e) {
		console.log(e.message);
	}
}

// change language
function LanguageChange(lang_id) {
	ui_params.language = lang_id;
	translate_ui();
}

// jcrop onChange and onSelect event handler
function showCoords(c) {
	pos_x = getSizeMm(c.x);
	pos_y = getSizeMm(c.y);
	if($('#pos_x').val() !== pos_x) $('#pos_x').val(pos_x);
	if($('#pos_y').val() !== pos_y) $('#pos_y').val(pos_y);

	geometry.x = getSizeMm(c.w);
	geometry.y = getSizeMm(c.h);
	var bidule = $('#geometry_x').val();
	if($('#geometry_x').val() !== geometry.x) $('#geometry_x').val(geometry.x);
	if($('#geometry_y').val() !== geometry.y) $('#geometry_y').val(geometry.y);

	var pagesize_string = geometry.x + ',' + geometry.y;
	var PSize_sel = $('#pagesize_select').msDropdown().data('dd');
	var current_pagesize = PSize_sel.get('value');

	if (current_pagesize !== pagesize_string) {
		var value_to_select = '0,0';

		$('#pagesize_select > option').each(function() {
			if(this.value === pagesize_string) value_to_select = this.value;
		});

		if(current_pagesize !== value_to_select) {
			PSize_sel.off('change');
			PSize_sel.set('value', value_to_select);
			PSize_sel.on('change', (function() {
				PSizeChange(this);
			}));
			//$('#pagesize_select').trigger('change');
		}
	}
};

// jcrop onRelease event handler
function clearCoords(){
	$('#pos_x').val(0);
	$('#pos_y').val(0);
	$('#geometry_x').val('');
	$('#geometry_y').val('');
};

// jcrop convert px to mm
function getSizeMm(sizePx) {
	return (parseInt(sizePx) / geometry.scale).toFixed();
}

// jcrop convert mm to px
function getSizePx(sizeMm) {
	return Math.floor(parseInt(sizeMm) * geometry.scale);
	//return (parseInt(sizeMm) * geometry.scale).toFixed();
}

// get the preview image
function getPreviewScan() {
	// initialise variables
	var Device_index = ($('#scanner_select').msDropdown().data('dd')).get('selectedIndex');
	var device_cap = devices_list[Device_index];

	// create data object with necessary argument
	var dataObject = {};
	dataObject.deviceUri = '' + device_cap.DeviceUri + '';
	dataObject.resolution = '' + device_cap.Resolution.min + '';
	dataObject.geometry = { tlx:'' + device_cap.Geometry.tlx + '',
							tly:'' + device_cap.Geometry.tly + '',
							x:'' + device_cap.Geometry.width + '',
							y:'' + device_cap.Geometry.height + ''};
	dataObject.mode = '' + ($('#mode_select').msDropdown().data('dd')).get('selectedText') + '';
	dataObject.source = '' + ($('#source_select').msDropdown().data('dd')).get('selectedText') + '';


	// Ajax request
	$.ajax({
		type: 'POST',
		url: 'index.php',
		data: { method: 'get_preview', preview_args: dataObject },
		contentType: 'application/x-www-form-urlencoded',
		dataType: 'json',
		success: function(response) {

			// verify response (status http 200 but error or no result on server side)
			if(response.result == 'false') {
				requestError(xhr, status, 'get_all_devices bad response');
			}

			// verify image size and ratio		
			$('.jcrop-holder img').attr('src',response.preview_file);
			$('.jcrop-holder img').on('load', function(){
				//alert($('.jcrop-holder img').prop('naturalWidth'));
				dpi_img = response.request_args.resolution;
				// get real size image and dpi
				realsize_w_px = $('.jcrop-holder img').prop('naturalWidth');
				realsize_h_px = $('.jcrop-holder img').prop('naturalHeight');
				dpi_img = response.request_args.resolution;

				// convert to mm and force css resize image preview 
				realsize_w_mm = (realsize_w_px / dpi_img) * 25.4;
				realsize_h_mm = (realsize_h_px / dpi_img) * 25.4;
				geometry.max_x = realsize_w_mm;
				geometry.max_y = realsize_h_mm;
				var img_width = '' + Math.round(realsize_w_mm * geometry.scale) + 'px';
				var img_height = '' + Math.round(realsize_h_mm * geometry.scale) + 'px';
				$('#preview_image').attr('src',response.preview_file);
				$('#preview_image').css({width:img_width, height:img_height});

				// Reset Jcrop
				// destroy it and recreate it
				// Some device have blankpage detection
				// so preview image is different size than device glasses
				// Without this, image is not correctly displayed (etired)
				jcrop_api.destroy();
				$('#preview_image').Jcrop({
					bgColor:	'black',
					onChange:   showCoords,
					onSelect:   showCoords,
					onRelease:  clearCoords,
					setSelect: [ 	(geometry.scale * pos_x) , 
									(geometry.scale * pos_y),
									(geometry.scale * (pos_x + geometry.x)),
									(geometry.scale * (pos_y + geometry.y))],
				}, function(){
					jcrop_api = this;
				});

				// remove event on load
				$('.jcrop-holder img').off('load');
			});
		},
		error: requestError,
	});
}

// get image
function getScan() {
	// initialise variables
	var Device_index = ($('#scanner_select').msDropdown().data('dd')).get('selectedIndex');
	var device_cap = devices_list[Device_index];

	// create data object with necessary argument
	var dataObject = {};
	dataObject.deviceUri = '' + device_cap.DeviceUri + '';
	dataObject.resolution = '' + ($('#resolution_select').msDropdown().data('dd')).get('selectedText') + '';
	dataObject.geometry = { tlx:'' + $('#pos_x').val() + '',
							tly:'' + $('#pos_y').val() + '',
							x:'' + $('#geometry_x').val() + '',
							y:'' + $('#geometry_y').val() + ''};
	dataObject.contrast = (function (){
		if (device_cap.Contrast.enabled === 'false') {
			return 'false';
		}else{
			return document.getElementById('contrast_slider').noUiSlider.get();
		}
	})();
	dataObject.brightness = (function(){
		if (device_cap.Brightness.enabled === 'false') {
			return 'false';
		}else{
			return document.getElementById('brightness_slider').noUiSlider.get();
		}
	})();
	dataObject.mode = '' + ($('#mode_select').msDropdown().data('dd')).get('selectedText') + '';
	dataObject.source = '' + ($('#source_select').msDropdown().data('dd')).get('selectedText') + '';

	var src_select_type = ($('#source_select').msDropdown().data('dd')).get('selectedText');
	dataObject.src_type = '' + device_cap.Sources.sources[src_select_type] + '';
	
	dataObject.file_format = '' + ($('#format_select').msDropdown().data('dd')).get('value') + '';
	dataObject.filename = '' + $('#scan_filename').val() + '';

	// Ajax request
	$.ajax({
		type: 'POST',
		url: 'index.php',
		data: { method: 'get_scan', scan_args: dataObject },
		contentType: 'application/x-www-form-urlencoded',
		dataType: 'json',
		success: function(response) {

			// verify response (status http 200 but error or no result on server side)
			if(response.result == 'false') {
				requestError(xhr, status, 'get_all_devices bad response');
			}
			
			saved_scan.push(response.file_datas);
			UpdateFileList();
			//alert('Scan effectué');

		},
		error: requestError,
	});
}

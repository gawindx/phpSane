<?php
/* phpSANE
// Version: 0.8.2
// John Walsh <john.walsh@mini-net.co.uk>
// Wojciech Bronisz <wojtek@bronisz.eu>
// David Fröhlich <?>
// Gawindx <decauxnico@gmail.com>
// moraumereu <?>
*/

include("incl/functions.php");
include("incl/language.php");
include("incl/config.php");
include("incl/security.php");
include("incl/scan.php");

//set localized formatting
setlocale(LC_ALL, $lang[$lang_id][19]);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta name="author" content="root">
	<meta name="robots" content="noindex">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>phpSANE</title>
	<link rel="icon" href="favicon.ico" type="image/png" />
	<link rel="shortcut icon" href="favicon.ico" type="image/png" />
	<link rel="stylesheet" href="css/jquery.jcrop.css" type="text/css" />
	<link rel="stylesheet" href="css/dd.css" type="text/css" />
	<link rel="stylesheet" href="css/nouislider.fox.css" type="text/css" />
	<link rel="stylesheet" href="css/style.css" type="text/css" />
	<script src="javascript/jquery.min.js" type="text/javascript"></script>
	<script src="javascript/jquery.jcrop.min.js" type="text/javascript"></script>
	<script src="javascript/jquery.msdropdown_custom.min.js" type="text/javascript"></script>
	<script src="javascript/jquery.nouislider.min.js" type="text/javascript"></script>
	<script type="text/javascript">
	$(document).ready(function() {
////////////////////////////////////////////////////////
//////scan area selection
<?php
	if ($scanner_ok) {
	echo "
		var jcrop_api;
		$('#preview_image').Jcrop({
			onChange:   showCoords,
			onSelect:   showCoords,
			onRelease:  clearCoords, setSelect: [" . ($PREVIEW_SCALE * $pos_x) . ", "
												. ($PREVIEW_SCALE * $pos_y) . ", "
												. ($PREVIEW_SCALE * ($pos_x + $geometry_x)) . ", "
												. ($PREVIEW_SCALE * ($pos_y + $geometry_y)) . "]\n
		}, function(){
			jcrop_api = this;
		});";
}
?>
		$('#menuForm').on('change','input',function(e){
		var pos_x = getSizePx($('#pos_x').val()),
			pos_y = getSizePx($('#pos_y').val()),
			geometry_x = getSizePx($('#geometry_x').val()),
			geometry_y = getSizePx($('#geometry_y').val());

			try{
				jcrop_api.setSelect([pos_x, pos_y, (pos_x + geometry_x), (pos_y + geometry_y)]);
			} catch(e) {
				console.log(e.message);
			}
		});

		//jcrop onChange and onSelect event handler
		function showCoords(c){
			var pos_x = getSizeMm(c.x);
			var pos_y = getSizeMm(c.y);
			if($('#pos_x').val !== pos_x) $('#pos_x').val(pos_x);
			if($('#pos_y').val !== pos_y) $('#pos_y').val(pos_y);

			var geometry_x = getSizeMm(c.w);
			var geometry_y = getSizeMm(c.h);
			if($('#geometry_x').val !== geometry_x) $('#geometry_x').val(geometry_x);
			if($('#geometry_y').val !== geometry_y) $('#geometry_y').val(geometry_y);

			var pagesize_string = geometry_x + ',' + geometry_y;
			var current_pagesize = $('#pagesize').val();
			if(current_pagesize !== pagesize_string) {
				var value_to_select = "0,0";
				$("#pagesize > option").each(function() {
					if(this.value === pagesize_string) value_to_select = this.value;
				});

				if(current_pagesize !== value_to_select) $('#pagesize').val(value_to_select).change();
			}
		};

		//jcrop onRelease event handler
		function clearCoords(){
			$('#pos_x').val(0);
			$('#pos_y').val(0);
			$('#geometry_x').val('');
			$('#geometry_y').val('');
		};

		function getSizeMm(sizePx) {
			return sizePx / <?php echo $PREVIEW_SCALE ?>;
		}

		function getSizePx(sizeMm) {
			return sizeMm * <?php echo $PREVIEW_SCALE ?>;
		}

		//change page size
		$("#pagesize").change(function(){
			var page_width_mm = parseFloat(this.value.split(",")[0]);
			var page_height_mm = parseFloat(this.value.split(",")[1]);
			if ((page_width_mm > 0) && (page_height_mm > 0)) {
				var preview_width_mm = <?php echo $PREVIEW_WIDTH_MM ?>;
//				$('#pos_x').val(preview_width_mm - page_width_mm);
				$('#pos_x').val(0);
				$('#pos_y').val(0);
				$('#geometry_x').val(preview_width_mm - (preview_width_mm - page_width_mm));
				$('#geometry_y').val(page_height_mm);

				//trigger the change event on one of the fields so crop selection changes
				$('#pos_x').trigger('change');
			}
		});
<?php
if ($do_source){
	echo "
		//Change source flatbed/ADF
		$('#source').change(function(){
			var source_selected = this.value;
			var format_option = original_format_select.split('\\n');
			if ( source_selected == original_source_value ){
				$('#format').empty();
				$('#format')[0].innerHTML = original_format_select;
				try{
					var formatHandler = $('#format').msDropDown({reverseMode:true}).data('dd');
					for(var i = (formatHandler.childElementCount -1) ; i > -1; i--){
						formatHandler.remove(i); //remove the current first option
					}
					var select_index = -1;
					for(var i = 0; i < format_option.length; i++){
						if (!format_option[i].replace(/\s/g, '').length) {
							continue;
						}

						option_text = format_option[i].match(/>(.*)</);
						option_value = format_option[i].match(/.*value=\"(.*)\".*=/);
						option_title = '';
						option_image = format_option[i].match(/data-image=\"(.*)\">/);
						option_description = '';
						formatHandler.add({text:option_text[1], value:option_value[1], description:option_description, image:option_image[1], className:'', title:option_title}); //remove the current first option
						select_index++;
						if (option_value[1] == 'pdf'){
							formatHandler.set('selectedIndex', select_index);
						}
					}
				} catch(e) {
					alert(e.message);
				}
			}else{
				$('#format').empty();
				$('#format')[0].innerHTML = original_format_select;
				var i = 0;
				for (i = ($('#format')[0].length - 1); i > -1; i--) {
					var select_value = $('#format option:eq(' + i + ')').val();
					if ( select_value != 'tif' && select_value != 'pdf' && select_value != 'txt'){
						$('#format option:eq(' + i + ')').remove();
					}else if ( select_value == 'pdf' ) {
						$('#format option:eq(' + i + ')').prop('selected', true);
					}
				}
				try{
					var formatHandler = $('#format').msDropDown({reverseMode:true}).data('dd');
					for(var i = (formatHandler.childElementCount -1) ; i > -1; i--){
						formatHandler.remove(i); //remove the current first option
					}
					var select_index = -1;
					for(var i = 0; i < format_option.length; i++){
						if (!format_option[i].replace(/\s/g, '').length) {
							continue;
						}
						option_text = format_option[i].match(/>(.*)</);
						option_value = format_option[i].match(/.*value=\"(.*)\".*=/);
						option_title = '';
						option_image = format_option[i].match(/data-image=\"(.*)\">/);
						option_description = '';
						if ( option_value[1] == 'pdf' || option_value[1] == 'tif' || option_value[1] == 'txt' ) {
							select_index++;
							formatHandler.add({text:option_text[1], value:option_value[1], description:option_description, image:option_image[1], className:'', title:option_title}); //remove the current first option
							if (option_value[1] == 'pdf'){
								formatHandler.set('selectedIndex', select_index);
							}
						}
					}
				} catch(e) {
					alert(e.message);
				}
			}
		});
////////////////////////////////////////////////////////
";
}
?>

////////////////////////////////////////////////////////
//////various
<?php
	if ($do_source && $source_default != "" ) {
		echo "
		var original_format_select = $('#format')[0].innerHTML;
		var original_source_value ='".$source_default."';
		";
	}
?>
		//enable msDropDown on select boxes
		try {
			$("body select").msDropDown({reverseMode:true});
			$("#source").trigger('change');
		} catch(e) {
			alert(e.message);
		}

		//prevent form submit when pressing enter key in text field
		$(document).keypress(function(event) {
			var event = (event) ? event : ((event) ? event : null);
			var node = (event.target) ? event.target : ((event.srcElement) ? event.srcElement : null);
			if ((event.keyCode == 13) && (node.type=="text")) {node.blur(); return false;}
		});

		//save language id in cookie
		$("#language_select").change(function(){
			document.cookie = "language_id=" + this.value + "; max-age=" + 10 * 365 * 24 * 60 * 60 + "; path=/;"
			$("#menuForm").submit();
		});

		//brightness menu option
		$("#brightness_slider").noUiSlider({
			range: [<?php echo $brightness_minimum . ',' . $brightness_maximum ?>]
			,start: <?php echo $brightness . "\r\n" ?>
			,step: 1
			,handles: 1
			,slide: function(){
				var values = $(this).val();
				$("#brightness").val(values);
			}
		});
		$("#brightness").change(function() {
			var brightnessValue = parseInt(this.value);
			if(isNaN(brightnessValue)) {
				this.value = $("#brightness_slider").val();
			} else {
				$("#brightness_slider").val(brightnessValue);
				this.value = $("#brightness_slider").val();
			}
		});

		//contrast menu option
		$("#contrast_slider").noUiSlider({
			range: [<?php echo $contrast_minimum . ',' . $contrast_maximum ?>]
			,start: <?php echo $contrast . "\r\n" ?>
			,step: 1
			,handles: 1
			,slide: function(){
				var values = $(this).val();
				$("#contrast").val(values);
			}
		});
		$("#contrast").change(function() {
			var contrastValue = parseInt(this.value);
			if(isNaN(contrastValue)) {
				this.value = $("#contrast_slider").val();
			} else {
				$("#contrast_slider").val(contrastValue);
				this.value = $("#contrast_slider").val();
			}
		});
////////////////////////////////////////////////////////


////////////////////////////////////////////////////////
//////file selection and buttons

		$("#select_menu_button").mousedown(function(event) {
			if(event.target.id != "select_menu_checkbox") {
				$("#select_menu_dropdown").toggle();
			}
		});

		$(document).click(function(event) {
			if(event.target.id != "select_menu_button" && $(event.target).parents('#select_menu_button').length == 0) {
				$("#select_menu_dropdown").hide();
			}
		});

		$("#select_menu_checkbox").click(function(){fileSelectAll($("#select_menu_checkbox").prop('checked'));});
		$("#select_menu_all").mouseup(function(){fileSelectAll(true);});
		$("#select_menu_none").mouseup(function(){fileSelectAll(false);});
		function fileSelectAll(select) {
			$(".selected_files").each(function() {
				$(this).prop('checked', select);
				$("#select_menu_checkbox").prop('checked', select);
			});
			fileSelectEvaluateCheckbox();
			$("#select_menu_dropdown").hide();
		};

		$("#select_menu_documents").mouseup(function(){fileSelectCategory('document');});
		$("#select_menu_images").mouseup(function(){fileSelectCategory('image');});
		$("#select_menu_other").mouseup(function(){fileSelectCategory('other');});
		function fileSelectCategory(category) {
			//skip the header row
			var rows = $("#files_table tr:gt(0)");
			rows.each(function() {
				var fileCategory = $(this).find(".file_column_category").html();
				$(this).find(".selected_files").prop('checked', fileCategory == category);
			});
			fileSelectEvaluateCheckbox();
			$("#select_menu_dropdown").hide();
		};

		$(".selected_files").click(fileSelectEvaluateCheckbox);
		function fileSelectEvaluateCheckbox() {
			var nrOfFiles = $(".selected_files").length;
			var nrOfSelectedFiles = $(".selected_files:checked").length;

			var buttonCheckbox = $("#select_menu_checkbox");
			var buttonDelete = $("#file_delete");
			var buttonDownload = $("#file_download");
			if(nrOfSelectedFiles == 0) {
				//no files selected
				if(buttonCheckbox.length > 0) {
					buttonCheckbox.prop('indeterminate', false);
					buttonCheckbox.prop('checked', false);
				}
				if(buttonDelete.length > 0){buttonDelete.hide();}
				if(buttonDownload.length > 0){buttonDownload.hide();}
			} else if(nrOfFiles == nrOfSelectedFiles) {
				//all files selected
				if(buttonCheckbox.length > 0) {
					buttonCheckbox.prop('indeterminate', false);
					buttonCheckbox.prop('checked', true);
				}
				if(buttonDelete.length > 0){buttonDelete.show();}
				if(buttonDownload.length > 0){buttonDownload.show();}
			} else {
				//some files selected
				if(buttonCheckbox.length > 0) {
					buttonCheckbox.prop('indeterminate', true);
					buttonCheckbox.prop('checked', true);
				}
				if(buttonDelete.length > 0){buttonDelete.show();}
				if(buttonDownload.length > 0){buttonDownload.show();}
			}
		}

		//delete selected files
		$("#file_delete").click(function(event){
			if(!confirm("<?php echo $lang[$lang_id][12] ?>")) {
				//user cancelled, prevent commit
				event.preventDefault();
			}
		});

		//download selected files
		$("#file_download").click(function(event){
			event.target.disabled = true;
			$('#file_download_spinner').show();

			var selectedFiles = [];
			$('.selected_files:checked').each(function() {
				selectedFiles.push($(this).val());
			});
			$.post('./download.php', {selected_files: selectedFiles})
			.done(function(data) {
				if(data.length > 0) {
					window.location.assign(data);
				} else {
					alert("<?php echo $lang[$lang_id][13] ?>");
			}
			})
			.fail(function() {
				alert("<?php echo $lang[$lang_id][13] ?>");
			})
			.always(function() {
				event.target.disabled = false;
				$('#file_download_spinner').hide();
			});
		});
////////////////////////////////////////////////////////


////////////////////////////////////////////////////////
<?php
//extend scanned document with another page only if multiple source is not activated or source is "Flatbed" (default value)
	if ($action_save && (($format == "pdf" && $do_append_pdf) || ($format == "txt" && $do_append_txt )) && (!($do_source) || $source == $source_default ) ) {
		echo "
		if(confirm(\"{$lang[$lang_id][50]}\")) {
			$('#append_file').val('{$file_save}');
			$('#tab_menu_buttons_accept').click();
		}";
	}
 ?>

////////////////////////////////////////////////////////
	});
</script>
</head>
<body>
<?php

////////////////////////////////////////////////////////////////////////
	echo "	<form id='menuForm' action='index.php' method='POST'>
	<input type=hidden name='first' value='$first'>
	<input type=hidden name='lang_id' id='lang_id' value='$lang_id'>
	<input type=hidden name='sid' value='$sid'>
	<input type=hidden name='preview_images' value='$preview_images'>
	<input type=hidden name='append_file' id='append_file' value=''>\n";
	if(!$do_file_name) {
		$filename = ($file_name_prefix !== -1 ? $file_name_prefix : $lang[$lang_id][60]) . date("Y-m-d_H.i.s", time());
		echo "	<input type=hidden name='file_name' id='file_name' value='$filename'>\n";
	}

////////////////////////////////////////////////////////////////////////
// page header

	echo "	<table id='page_header' width='100%'>
		<tr>
			<td>
				<a href='index.php'>
				<img id='logo' src='images/logo.jpg' alt='phpSANE'>
				</a>";

	if($do_btn_help || $do_lang_toggle) { 
		echo "					<ul id='header_menu'>";
		if($do_btn_help) echo "						<li><a href='help/help_{$lang_id}.php' target='_blank'>{$lang[$lang_id][37]}</a></li>";
		if($do_lang_toggle) {
			echo "					<li>
						<!--<select id='language_select' name='lang_id' onchange='setLanguage(this.value);this.form.submit()'>//-->
						<select id='language_select' name='lang_id'>
							<option"; if ($lang_id==0) echo " selected"; echo " value='0' data-image='images/lang_de.gif'>" . $lang[0][20]."</option>
							<option"; if ($lang_id==1) echo " selected"; echo " value='1' data-image='images/lang_en.gif'>" . $lang[1][20]."</option>
							<option"; if ($lang_id==2) echo " selected"; echo " value='2' data-image='images/lang_pl.gif'>" . $lang[2][20]."</option>
							<option"; if ($lang_id==3) echo " selected"; echo " value='3' data-image='images/lang_fi.gif'>" . $lang[3][20]."</option>
							<option"; if ($lang_id==4) echo " selected"; echo " value='4' data-image='images/lang_ru.gif'>" . $lang[4][20]."</option>
							<option"; if ($lang_id==5) echo " selected"; echo " value='5' data-image='images/lang_uk.gif'>" . $lang[5][20]."</option>
							<option"; if ($lang_id==6) echo " selected"; echo " value='6' data-image='images/lang_fr.gif'>" . $lang[6][20]."</option>
							<option"; if ($lang_id==7) echo " selected"; echo " value='7' data-image='images/lang_nl.gif'>" . $lang[7][20]."</option>
							<option"; if ($lang_id==8) echo " selected"; echo " value='8' data-image='images/lang_cz.gif'>" . $lang[8][20]."</option>
							<option"; if ($lang_id==9) echo " selected"; echo " value='9' data-image='images/lang_it.gif'>" . $lang[9][20]."</option>
						</select>
					</li>
				</ul>";
		}
	}
	echo "			</td>
		</tr>
		<tr><td class='ruler' colspan=2 /></tr>
	</table>\n";
// page header - end
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// page body

	echo "
	<table id='page_body' style='height: {$PREVIEW_HEIGHT_PX}px;'>
		<tr>
			<td id='tab_preview'>
				<img src='$preview_images' id='preview_image' width='$PREVIEW_WIDTH_PX' height='$PREVIEW_HEIGHT_PX' />
			</td>
			<td id='tab_menu'>\n";
	if ($scanner_ok) {
		include("incl/menu.php");
	} else {
		echo "
				<input type=hidden name='pos_x' value='".$pos_x."'>
				<input type=hidden name='pos_y' value='".$pos_y."'>
				<input type=hidden name='geometry_x' value='".$geometry_x."'>
				<input type=hidden name='geometry_y' value='".$geometry_y."'>
				<input type=hidden name='format' value='".$format."'>
				<input type=hidden name='mode' value='".$mode."'>
				<input type=hidden name='resolution' value='".$resolution."'>
				<input type=hidden name='brightness' value='".$brightness."'>
				<input type=hidden name='contrast' value='".$contrast."'>
				<input type=hidden name='source' value='".$source."'>
	<table>
		<tr>
			<td id='tab_menu_error_text'>".$lang[$lang_id][33]."</td>
		</tr>
		<tr>
			<td><input id='tab_menu_error_button' type='submit' name='action' value='".$lang[$lang_id][34]."'></td>
		</tr>
	</table>\n";
	}
	echo "	</td>
		</tr>
		<tr>
	<td colspan='2'>\n";
	include("incl/files.php");
	echo "
		</td>
	</table>\n";

// page body - end
////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////
// page footer
	echo "
	<table id='page_footer'>
		<tr><td class='ruler' /></tr>
		<tr><td>
			# $cmd_device
		</td></tr>
		<tr><td class='ruler' /></tr>
	</table>
</form>
</body>
</html>\n";
?>

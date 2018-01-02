<?php
/*
#
# Copyright (C) 2012 Alexander Weidinger <aw@sz9i.net>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
# GNU General Public License for more details.
#
*/

function validate($value, $constraint, $regex='') {
	switch ($constraint) {
		case 'empty_or_numeric':
			return empty($value) or validate($value, 'numeric');
		case 'filepath':
			return (validate($value, 'regex', '/^[a-z0-9 ._\/-]+$/i')
					and validate($value, 'inverse_regex', '/\.\./'));
		case 'numeric':
			return is_numeric($value);
		case 'regex':
			return preg_match($regex, $value);
		case 'inverse_regex':
			return ! preg_match($regex, $value);
		default:
			return FALSE;
	}
}

function valid_or_dead($key, $constraint, $regex='') {
	foreach(array($_GET, $_POST, $_SERVER) as $_arr) {
		if (!array_key_exists($key, $_arr)) {
			continue;
		}
		$value = $_arr[$key];
		if (!validate($value, $constraint, $regex)) {
			die('Input validation of '.$key.' failed! <br/>'
				.'Constraint: "'.$constraint.'" <br/>'
				.'Value: "'.strip_tags(htmlspecialchars($value)).'"');
		}
	}
}

// action
valid_or_dead('action', 'regex', '/[a-z0-9]*/i');
// brightness
valid_or_dead('brightness', 'empty_or_numeric');
// contrast
valid_or_dead('contrast', 'empty_or_numeric');
// depth
valid_or_dead('depth', 'numeric');
// first
valid_or_dead('first', 'regex', '/^[01]$/i');
// format
valid_or_dead('format', 'regex', '/^[a-z]+$/i');
// pos_x
valid_or_dead('pos_x', 'numeric');
// pos_y
valid_or_dead('pos_y', 'numeric');
// geometry_x
valid_or_dead('geometry_x', 'numeric');
// geometry_y
valid_or_dead('geometry_y', 'numeric');
// lang_id
valid_or_dead('lang_id', 'numeric');
// mode
valid_or_dead('mode', 'regex', '/^[a-z]+$/i');
// file_name
valid_or_dead('file_name', 'filepath');
// preview_images
valid_or_dead('preview_images', 'filepath');
// resolution
valid_or_dead('resolution', 'regex', '/^(|auto|[0-9]+)$/i');
// sid
valid_or_dead('sid', 'filepath');
// usr_opt
valid_or_dead('usr_opt', 'inverse_regex', '(;|&&|\|\||<|>|<<|>>)');
// file_save
valid_or_dead('file_save', 'filepath');
// file_save_image
valid_or_dead('first', 'regex', '/^[01]$/i');

// REMOTE_ADDR
valid_or_dead('REMOTE_ADDR', 'regex', '/^[0-9.:]+$/i');

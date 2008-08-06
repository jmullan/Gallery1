<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id: text.php 16777 2007-07-18 22:15:34Z jenst $
 */
?>
<?php

/**
 * @package text
 * @author Jens Tkotz
 */
?>
<?php
/**
 * Returns a truncated text.
 * 3 Types of position are supported:
 * - left : "...foo"
 * - right: "foo..."
 * - middle: "foo...bar"
 *
 * @param string	$text
 * @param int		$minLength
 * @param int		$dotcount
 * @param string	$position
 * @return string	$truncated
 * @author Jens Tkotz
 */
function truncateText($text = '', $maxLength = 10, $position = 'middle') {
	$dotchar = '.';
	$dotcount = 3;
	$dots = str_repeat($dotchar, $dotcount);

	$length = strlen($text);

	if(empty($text) ||
	   $length < $maxLength ||
	   $maxLength <= $dotcount ||
	   ($position == 'middle' && $maxLength <= 2 * $dotcount) ||
	   ! in_array($position, array('left', 'right', 'middle')))
	{
	    return $text;
	}

	switch ($position) {
		case 'left':
			$truncated = $dots . substr($text, $dotcount - $maxLength);
		break;

		case 'right':
			$truncated = substr($text, 0, $maxLength - $dotcount) . $dots;
		break;

		case 'middle':
		default:
			$truncated = $dots . substr($text, $dotcount, $maxLength - $dotcount) . $dots;
		break;
	}

	return $truncated;
}
<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>
<?php

/*
** This function checks if a given value is a valid integer.
** Valid means:
** --- its a numeric value
** --- is not lower a given minum (can be 1 or 0)
** You can give a default to correct an invalid input
**
** Return is an array that contains:
** --- Status, can be 0 for OK, 1 for set to default, 2 failure and no default
** --- Original or default value
** --- Debug messag
*/

function isValidInteger($mixed, $includingZero = false, $default = false) {
    $minimum = ($includingZero == true) ? 0 : 1;
    $int_val = intval($mixed);
    if (! is_numeric($int_val)) {
	if (!isset($default)) {
	    return array(1,$default, _("Value was set to given default. Because the original value is not numeric."));
	} else {
	    return array(2, false, _("The given Value is not numeric."));
	}
    }
   
    if($int_val < $minimum) {
	if (isset($default)) {
            return array(1, $default, _("Value was set to given default. Because the original value is not a valid Integer"));
        } else {
            return array(2, false, _("The given Value not a valid Integer."));
        }
    }

    return array(0, $int_val, '');
}
?>

<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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

function insertFormJS($formName) {

/* Note: the w3c-suggested "text/javascript" doesn't work with Navigator 4 */

?>
<script language="javascript">
// <!-- 
function setCheck(val,elementName) {
	ufne=document.<?php echo $formName; ?>;
	len = ufne.elements.length;
	for(i = 0 ; i < len ; i++) {
		if (ufne.elements[i].name==elementName) {
			ufne.elements[i].checked=val;
		}
	}
}

function invertCheck(elementName) {
	ufne=document.<?php echo $formName; ?>;
	len = ufne.elements.length;
	for(i = 0 ; i < len ; i++) {
		if (ufne.elements[i].name==elementName) {
			ufne.elements[i].checked = !(ufne.elements[i].checked);
		}
	}
}
// -->
</script>
<?php
}

function insertFormJSLinks($elementName) {
$buf='
	<a href="javascript:setCheck(1,\'' . $elementName . '\')">'. _("Check All") . '</a>
	-
	<a href="javascript:setCheck(0,\'' . $elementName . '\')">'. _("Clear All") . '</a>
	-
	<a href="javascript:invertCheck(\'' . $elementName . '\')">'. _("Invert Selection") .'</a>';

return $buf;
}
?>

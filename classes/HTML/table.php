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
class galleryTable {

    var $headers;
    var $columns;
    var $attrs;	
    var $elements;

    function galleryTable($tableArgs = array('columns' => 3, 'attrs' => array())) {
	$this->columns = $tableArgs['columns'];
	$this->attrs = $tableArgs['attrs'];
	$this->elements = array();
    }

    function addElement($element = array('content' => null, 'cellArgs' => array())) {
	if (!empty($element)) {
	    $this->elements[] = $element;
	    return true;
	} else {
	    return false;
        }
    }

    function render() {
	$buf = "\n<table";
	foreach ($this->attrs as $attr => $value) {
	    $buf .= " $attr=\"$value\"";
        }
	$buf .= '>'; 

	if (!empty($this->headers)) {
	    $buf .= "\n<tr>";
	    $i = 0;
	    foreach ($this->headers as $header) {
		$i++;
		$buf .="\n<th>$header</th>";
	    }

	    for (; $i < $this->columns; $i++) {
		$buf .="\n<th>&nbsp;</th>";
	    }

	    /* Override count of columns */
	    $this->columns = $i;

	}
	if (!empty($this->elements)) {
	    $i = 0;
	    $buf .= "\n<tr>";
	    foreach ($this->elements as $nr => $element) {
		$i++;
		if ($i % $this->columns) {
		    $buf .= "\n</tr>\n<tr>";
		}
		$buf .= "\n    <td>". $element['content'] ."</td>";
	    }
	    $buf .= "\n</tr>";
	}
	$buf .= "\n</table>";

	return $buf;
    }
}   

?>

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
setAttrs * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php
class galleryTable {

    var $attrs;	
    var $headers;
    var $headerClass;
    var $caption;
    var $captionClass;
    var $columnCount;
    var $elements;

    function galleryTable() {
	$this->attrs = array();
	$this->headers = array();
	$this->headerClass = '';
	$this->caption = '';
	$this->captionClass = '';
	$this->columnCount = 3;
	$this->elements = array();
    }

    function setAttrs($attrs = array()) {
	$this->attrs = $attrs;
    }

    function setColumnCount($nr) {
	$this->columnCount = $nr;
    }

    function addElement($element = array('content' => null, 'cellArgs' => array())) {
	if (!empty($element)) {
	    $this->elements[] = $element;
	    return true;
	} else {
	    return false;
        }
    }

    function setHeaders($headers = array(), $class = '') {
	$this->headers = $headers;
	$this->headerClass = $class;
    }

    function setCaption($caption = '', $class = '') {
	$this->caption = $caption;
	$this->captionClass = $class;
    }

    function render($indent = 0) {
	if (empty($this->elements)) {
	    return '';
 	}

	$ind = '';
	$numElements = sizeof($this->elements);

	for($i = 0; $i < $indent; $i++) {
	    $ind .= "    ";
	}

	$buf = "\n$ind<table";
	foreach ($this->attrs as $attr => $value) {
	    $buf .= " $attr=\"$value\"";
        }
	$buf .= '>'; 

	if (!empty($this->caption)) {
	    $buf .= "\n$ind<caption class=\"". $this->captionClass ."\">". $this->caption ."</caption>";
	}

	if (!empty($this->headers)) {
	    $buf .= "\n$ind<tr>";
	    $i = 0;
	    foreach ($this->headers as $header) {
		$i++;
		$buf .="\n$ind<th class=\"". $this->headerClass ."\">$header</th>";
	    }

	    for (; $i < $this->columnCount; $i++) {
		$buf .="\n$ind<th class=\"". $this->headerClass ."\">&nbsp;</th>";
	    }

	    /* Override value of columnCount */
	    $this->columnCount = $i;
	}

	if (!empty($this->elements)) {
	    $i = 0;
	    $buf .= "\n$ind<tr>";
	    foreach ($this->elements as $nr => $element) {
	        $buf .= "\n$ind    <td";
		if(!empty($element['cellArgs'])) {
		    foreach ($element['cellArgs'] as $attr => $value) {
			$buf .= " $attr=\"$value\"";
		    }
		}
		$buf .= '>'. $element['content'] .'</td>';
		
		if(isset($element['cellArgs']['colspan'])) {
		    $i += $element['cellArgs']['colspan'];
		}
		else {
		    $i++;
		}

		if (!($i % $this->columnCount) && $nr < $numElements-1 && $this->columnCount > 0) {
		    $buf .= "\n$ind </tr>\n$ind<tr>";
		}
	    }
	    $buf .= "\n$ind</tr>";
	}

	$buf .= "\n$ind</table>\n$ind";

	return $buf;
    }
}   

?>

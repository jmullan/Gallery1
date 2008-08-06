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
 * $Id$
 */

/**
 * Class for handling a HTML table
 *
 */
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

	/**
	 * Adds an element to the table.
	 *
	 * @param array $element	an element is an array consiting of 'content' and 'cellArgs'.
	 *				'cellArgs' is also an array.
	 * @return boolean		true if the element was successfully added.
	 * @author Jens Tkotz
	 */
	function addElement($element = array('content' => null, 'cellArgs' => array())) {
		if (!empty($element)) {
			$this->elements[] = $element;
			return true;
		}
		else {
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
			$ind .= '	';
		}

		$html = "\n$ind<table";

		foreach ($this->attrs as $attr => $value) {
			$html .= " $attr=\"$value\"";
		}
		$html .= '>';

		if (!empty($this->caption)) {
			$html .= "\n$ind<caption class=\"". $this->captionClass ."\">". $this->caption ."</caption>";
		}

		if (!empty($this->headers)) {
			$html .= "\n$ind<tr>";
			$i = 0;
			foreach ($this->headers as $header) {
				$i++;
				$html .="\n$ind<th class=\"". $this->headerClass ."\">$header</th>";
			}

			for (; $i < $this->columnCount; $i++) {
				$html .="\n$ind<th class=\"". $this->headerClass ."\">&nbsp;</th>";
			}

			/* Override value of columnCount */
			$this->columnCount = $i;
		}

		if (!empty($this->elements)) {
			$i = 0;
			$html .= "\n$ind<tr>";
			foreach ($this->elements as $nr => $element) {
				$html .= "\n$ind	<td";
				if(!empty($element['cellArgs'])) {
					foreach ($element['cellArgs'] as $attr => $value) {
						$html .= " $attr=\"$value\"";
					}
				}
				$html .= '>'. $element['content'] .'</td>';

				if(isset($element['cellArgs']['colspan'])) {
					$i += $element['cellArgs']['colspan'];
				}
				else {
					$i++;
				}

				if (!($i % $this->columnCount) && $nr < $numElements-1 && $this->columnCount > 0) {
					$html .= "\n$ind </tr>\n$ind<tr>";
				}
			}
			$html .= "\n$ind</tr>";
		}

		$html .= "\n$ind</table>\n$ind";

		return $html;
	}
}

?>

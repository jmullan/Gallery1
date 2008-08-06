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
?>
<script type="text/javascript">
<!--

/*
 * These values define the margin between your image and the navigation and or a left block.
 * Change to your suites.
*/
var marginLeft = 100;
var marginTop  = 275;

/*
 * Don't touch
 * Here are the dimensions of the original image
*/
var imagewidth  = <?php echo $imageWidth; ?>;
var imageheight = <?php echo $imageHeight; ?>;
var imageratio  = imagewidth/imageheight;

/*
 * Get the window width. NS and IE use different methods
*/
function windowWidth() {
	if (window.innerWidth) {
		return window.innerWidth;
	}
	else if (document.documentElement.clientWidth) {
		return document.documentElement.clientWidth;
	}
	else {
		return document.body.clientWidth;
	}
}

/*
 * Get the window height. NS and IE use different methods
*/
function windowHeight() {
	if (window.innerHeight) {
		return window.innerHeight;
	}
	else if (document.documentElement.clientHeight) {
		return document.documentElement.clientHeight;
	}
	else {
		return document.body.clientHeight;
	}
}

/*
 * We load this in the header, so the page is not fully rendered.
 * save the windowdimensions.
*/
function calculateNewSize(){
	windowWidth = windowWidth();
	windowHeight= windowHeight();

	newwidth = imagewidth;
	newheight = imageheight;

	if ( imagewidth > (windowWidth - marginLeft)) {
		newwidth = windowWidth - marginLeft;
		newheight = parseInt(newwidth / imageratio);
	}

	if ( newheight > (windowHeight - marginTop)) {
		newheight = windowHeight - marginTop;
		newwidth = parseInt( newheight * imageratio);
	}

	setReducedSize();
}

function setReducedSize() {
	document.getElementById('galleryImage').height	= newheight;
	document.getElementById('galleryImage').width	= newwidth;

	if (document.getElementById('frameRR')) {
			document.getElementById('frameRR').style.height = newheight + 'px';
			document.getElementById('frameLL').style.height = newheight + 'px';
	}

	if (document.getElementById('galleryImageFrame')) {
		document.getElementById('galleryImageFrame').style.height = newheight + 'px';
		document.getElementById('galleryImageFrame').style.width  = newwidth  + 'px';
	}
}

function sizeChange() {
	this.full = false;

	this.toggle = function toggle() {
		if (this.full == true) {
			setReducedSize();

			this.full = false;
		}
		else {
			document.getElementById('galleryImage').height	= imageheight;
			document.getElementById('galleryImage').width	= imagewidth;

			if (document.getElementById('frameRR')) {
				document.getElementById('frameRR').style.height = imageheight + 'px';
				document.getElementById('frameLL').style.height = imageheight + 'px';
			}

			if (document.getElementById('galleryImageFrame')) {
				document.getElementById('galleryImageFrame').style.height = imageheight + 'px';
				document.getElementById('galleryImageFrame').style.width  = imagewidth  + 'px';
			}

			this.full = true;
		}
	}
}

sizeChange = new sizeChange();

// -->
</script>

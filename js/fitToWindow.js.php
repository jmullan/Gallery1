<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
** These values define the margin between your image and the navigation and or a left block.
** Change to your suites.
*/
    var marginLeft = 100;
    var marginTop  = 275;

/* 
** Dont touch
** Here are the dimensions of the original image
*/
    var imagewidth  = <?php echo $imageWidth; ?>;
    var imageheight = <?php echo $imageHeight; ?>;
    var imageratio  = imagewidth/imageheight;

/* 
** Get the window width. NS and IE use different methods 
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
** Get the window height. NS and IE use different methods 
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
** We load this in the header, so the page is not fully rendered.
** save the windowdimensions.
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
    document.getElementById('galleryImage').height = newheight;
    document.getElementById('galleryImage').width = newwidth;
    if (document.getElementsByName('frameRR')) {
	document.getElementsByName('frameRR').height = newheight;
	document.getElementsByName('frameLL').height = newheight;
    }
}

function sizeChange() {
    this.full = false;

    this.toggle = function toggle() {
	if (this.full == true) {
	    this.full = false;
	    setReducedSize();
	} else {
	    document.getElementById('galleryImage').height = imageheight;
	    document.getElementById('galleryImage').width = imagewidth;

	    this.full = true;
	}
    }
}

sizeChange = new sizeChange();

// -->
</script>

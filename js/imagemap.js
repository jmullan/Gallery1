/**
 * Gallery SVN ID:
 * $Id$
 */

var minX;
var minY;
var width;
var height;

var xVals = new Array();
var yVals = new Array();
var imgxVals = new Array();
var imgyVals = new Array();

var jg;

function initPaintArea() {
	jg = new jsGraphics("myCanvas");
	jg.setColor("#FFFFFF");

	getMyPicCoords();

	if(typeof map != "undefined") {
		/* Update map coords */
		for(i = 0; i < map.length; ++i) {
			for (j = 0; j < map[i]['x_coords'].length; ++j) {
				map[i]['x_coords'][j] = map[i]['x_coords'][j] + minX;
				map[i]['y_coords'][j] = map[i]['y_coords'][j] + minY;
			}
		}
	}
}


// find out if ie runs in quirks mode
var docEl = (typeof document.compatMode != "undefined" && document.compatMode != "BackCompat")? "documentElement" : "body";

// register event
// capture it for nc 4x (ok it's a dino)
function init_mousemove() {
	if(document.layers) document.captureEvents(Event.MOUSEMOVE);
	document.onmousemove = dpl_mouse_move;
	document.onmousedown = dpl_mouse_click;
}

function dpl_mouse_move(e) {

	// position where mousemove fired
	//
	var xPos    =  e? e.pageX : window.event.x;
	var yPos    =  e? e.pageY : window.event.y;

	// for ie add scroll position
	//
	if (document.all && !document.captureEvents) {
		xPos    += document[docEl].scrollTop;
		yPos    += document[docEl].scrollTop;
	}

	var imgPosX = xPos - minX;
	var imgPosY = yPos - minY;

	el = document.getElementById('current_position');
	// display position
	if(!(xPos < minX || yPos < minY || xPos > minX+width || yPos > minY+height)) {
		el.style.backgroundColor='green';
	} else {
		el.style.backgroundColor='red';
		imgPosX = 0;
		imgPosY = 0;
	}

	// display position
	document.areas.ausg.value    =  "Left = " + xPos + " : Top = " + yPos;

	// for the dino pass event
	if (document.layers) routeEvent(e);
}

function dpl_mouse_click(e) {
	getMyPicCoords();
	// position where mousemove fired
	var xPos    =  e? e.pageX : window.event.x;
	var yPos    =  e? e.pageY : window.event.y;

	// for ie add scroll position
	//
	if (document.all && !document.captureEvents) {
		xPos    += document[docEl].scrollTop;
		yPos    += document[docEl].scrollTop;
	}

	if(xPos < minX || yPos < minY || xPos > minX+width || yPos > minY+height) {
		// do nothing
	}
	else {
		var imgPosX = xPos - minX;
		var imgPosY = yPos - minY;

		imgxVals.push(imgPosX);
		imgyVals.push(imgPosY);
		xVals.push(xPos);
		yVals.push(yPos);
		document.areas.xvals.value    = imgxVals;
		document.areas.yvals.value    = imgyVals;
		// draw

		jg.clear();
		jg.drawPolygon(xVals, yVals);
		jg.paint();
	}

	// for the dino pass event
	if (document.layers) routeEvent(e);

}

function Int(d_x, d_y) {
	return isNaN(d_y = parseInt(d_x))? 0 : d_y;
};

function getMyPicCoords() {
	element = document.getElementById('myPic');

	width   = element.width;
	height  = element.height;

	minX = minY = 0; //global helper vars
	while(element) {
		minX += Int(element.offsetLeft);
		minY += Int(element.offsetTop);
		element = element.offsetParent || null;
	}
};


function resetAndClear() {
	jg.clear();
	xVals = new Array();
	yVals = new Array();
	imgxVals = new Array();
	imgyVals = new Array();
	document.areas.xvals.value =  xVals;
	document.areas.yvals.value =  yVals;
}

function updatePictureAndArea() {
	el = document.getElementById('imageareas');
	areaurl = document.getElementById('areaurl');
	areatext = document.getElementById('areatext');
	selected = 0;
	count_selected = 0;

	jg.clear();
	for (i = 0; i < el.length; ++i) {
		if (el.options[i].selected == true) {
			area_index = el.options[i].value;
			jg.drawPolygon(map[area_index]['x_coords'], map[area_index]['y_coords']);
			selected = area_index;
			count_selected++;
		}
	}

	jg.paint();

	if (count_selected == 1) {
		areaurl.value = map[selected]['url'];
		areatext.value = unescape(map[selected]['hover_text']);
	}
	else {
		areaurl.value = '';
		areatext.value = '';
	}
}

function callBack(color) {
	jg.setColor(color);
	updatePictureAndArea();
}

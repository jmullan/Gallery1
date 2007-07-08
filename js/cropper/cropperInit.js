/* $Id$ */

function setRatio() {

	var ratio = document.getElementById('cropRatio').value.split('|');
	var ratioDir = document.getElementById('cropRatioDir').value;

	if(ratioDir < 0) {
		var swap = ratio[0];
		ratio[0] = ratio[1];
		ratio[1] = swap;
	}

	if(ratio[0] > 0) {
		g_cropper.applyRatio(g_cropper.areaCoords, { x: ratio[0], y: ratio[1] }, {x: 1, y:1 });
	}

	g_cropper.options.ratioDim = { x: ratio[0], y: ratio[1] };
	g_cropper.ratioX = ratio[0];
	g_cropper.ratioY = ratio[1];
	g_cropper.drawArea();

}

// setup the callback function
function onEndCrop( coords, dimensions ) {
	document.getElementById('x1').value = coords.x1;
	document.getElementById('y1').value = coords.y1;
	document.getElementById('x2').value = coords.x2;
	document.getElementById('y2').value = coords.y2;
	document.getElementById('width').value = dimensions.width;
	document.getElementById('height').value = dimensions.height;
}

var g_cropper;

Event.observe(
	window,
	'load',
	function() {
		g_cropper = new Cropper.Img(
			'cropImage',
			{
				onloadCoords: { x1: 10, y1: 10, x2: 150, y2: 150 },
				displayOnInit: true,
				onEndCrop: onEndCrop
			}
		);
	}
);

function previewCrop() {
	g_cropper.remove();

	var cropArea = document.getElementById('cropArea');
	var image = document.getElementById('cropImage');

	var x1 = -parseInt(document.getElementById('x1').value) + 20;
	var y1 = -parseInt(document.getElementById('y1').value) + 20;

	var width = parseInt(document.getElementById('width').value) + 20;
	var height = parseInt(document.getElementById('height').value) + 20;

	var rect = 'rect(20px, '+ width +'px, '+ height +'px, 20px)';
	cropArea.style.clip = rect;

	image.style.left	= x1 + 'px';
	image.style.top		= y1 + 'px';
}

function resetCrop() {
	g_cropper.reset();

	var cropArea = document.getElementById('cropArea');
	var image = document.getElementById('cropImage');

	image.style.top = '0px';
	image.style.left = '0px';

	var rect = 'rect(0px, '+ image.width +'px, '+ image.height +'px, 0px)';

	cropArea.style.clip = rect;
}
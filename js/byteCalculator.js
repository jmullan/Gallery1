/**
 * Gallery SVN ID:
 * $Id$
*/

function showByteCalculator(calcID) {
	calcIcon	= document.getElementById(calcID + '_byteCalcIcon');
	bytes		= document.getElementById(calcID);
	calcBox		= document.getElementById(calcID + '_byteCalcBox');
	mixedSize	= document.getElementById(calcID + '_mixedSize');
	unit		= document.getElementById(calcID + '_unit');

	mixedSize.value = bytes.value;
	unit.options[0].selected = true;
	calcBox.style.display= 'inline';

	calcIcon.style.display= 'none';
}

function update(calcID) {
	bytes	= document.getElementById(calcID);
	mixed	= document.getElementById(calcID + '_mixedSize');
	unit	= document.getElementById(calcID + '_unit');
	niceBytes = document.getElementById(calcID + '_niceBytes');

	if(isNaN(mixed.value)) {
		mixed.value = mixed.value.slice(0,mixed.value.length-1);
	}

	bytes.value = Math.round(mixed.value * unit.value);

	niceBytes.value = '';
	niceBytes.value = formatted_filesize(bytes.value);
}

function closeByteCalculator(calcID) {
	calcIcon	= document.getElementById(calcID + '_byteCalcIcon');
	calcBox		= document.getElementById(calcID + '_byteCalcBox');

	calcBox.style.display= 'none';

	calcIcon.style.display= 'inline';
}

function formatted_filesize(bytes) {

	filesize = Math.round(bytes);


	units = new Array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	unit_count = units.length

	pass = 0; // set zero, for Bytes
	while( filesize >= 1024 && pass < unit_count ) {
		filesize /= 1024;
		pass++;
	}

	result = round(filesize, 3);

	return result + ' ' + units[pass];
}

function round(number, decimals) {
	divisor = Math.pow(10, decimals);

	rounded = Math.round(number * divisor) / divisor;

	return rounded;
}



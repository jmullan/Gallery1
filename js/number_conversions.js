
/* CONVERSIONS
 * Taken from
 * DHTML Color Picker v1.0.3, Programming by Ulyses, ColorJack.com
 * Updated August 24th, 2007
 *
 * Modified by Jens Tkotz to fit with Gallery 1.5.8+
*/


function toHex(v) { v=Math.round(Math.min(Math.max(0,v),255)); return("0123456789ABCDEF".charAt((v-v%16)/16)+"0123456789ABCDEF".charAt(v%16)); }
function hex2rgb(r) { return({0:parseInt(r.substr(0,2),16),1:parseInt(r.substr(2,2),16),2:parseInt(r.substr(4,2),16)}); }
function rgb2hex(r) { return(toHex(r[0])+toHex(r[1])+toHex(r[2])); }
function hsv2hex(h) { return(rgb2hex(hsv2rgb(h))); }
function hex2hsv(v) { return(rgb2hsv(hex2rgb(v))); }

function hsv2rgb(r) { // easyrgb.com/math.php?MATH=M21#text21

	var R,B,G,S=r[1]/100,V=r[2]/100,H=r[0]/360;

	if(S>0) {
		if(H>=1) {
			H=0;
		}

		H=6*H; F=H-Math.floor(H);
		A=Math.round(255*V*(1.0-S));
		B=Math.round(255*V*(1.0-(S*F)));
		C=Math.round(255*V*(1.0-(S*(1.0-F))));
		V=Math.round(255*V);

		switch(Math.floor(H)) {

			case 0: R=V; G=C; B=A; break;
			case 1: R=B; G=V; B=A; break;
			case 2: R=A; G=V; B=C; break;
			case 3: R=A; G=B; B=V; break;
			case 4: R=C; G=A; B=V; break;
			case 5: R=V; G=A; B=B; break;

		}

		return([R?R:0,G?G:0,B?B:0]);

	}
	else {
		return([(V=Math.round(V*255)),V,V]);
	}

}

function rgb2hsv(r) { // easyrgb.com/math.php?MATH=M20#text20

	var max=Math.max(r[0],r[1],r[2]),delta=max-Math.min(r[0],r[1],r[2]),H,S,V;

	if(max!=0) {
		S=Math.round(delta/max*100);

		if(r[0]==max) {
			H=(r[1]-r[2])/delta;
		}
		else if(r[1]==max) {
			H=2+(r[2]-r[0])/delta;
		}
		else if(r[2]==max) {
			H=4+(r[0]-r[1])/delta;
		}

		var H=Math.min(Math.round(H*60),360);
		if(H<0) {
			H+=360;
		}
	}

	return({0:H?H:0,1:S?S:0,2:Math.round((max/255)*100)});

}

/* Credits for this function goes to the horde project.
 * http://horde.org
*/
function brightness(color) {
	var r = new Number("0x" + color.substr(0, 2));
	var g = new Number("0x" + color.substr(2, 2));
	var b = new Number("0x" + color.substr(4, 2));
	return ((r * 299) + (g * 587) + (b * 114)) / 1000;
}
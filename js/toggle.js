/**
 * Gallery SVN ID:
 * $Id$
*/

function gallery_toggle(id) {
	var img;
	var elem;

	img = document.getElementById('toggleBut_' + id);
	elem = document.getElementById('toggleFrame_' + id);

	if (elem.style.display == 'none') {
		elem.style.display = 'inline';
		img.src = '../images/collapse.gif';
	} else {
		elem.style.display = 'none';
		img.src = '../images/expand.gif';
	}
}

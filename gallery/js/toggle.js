function gallery_toggle(id) {
	var img;
	var elem;

	img = document.getElementById('toogleBut_' + id);
	elem = document.getElementById('toogleFrame_' + id);
	
	if (elem.style.display == 'none') {
		elem.style.display = 'inline';
		img.src = '../images/collapse.gif';
	} else {
		elem.style.display = 'none';
		img.src = '../images/expand.gif';
	}
}

<?php

/**
 * Gallery SVN ID:
 * $Id$
*/

if(file_exists(dirname(dirname(__FILE__)) . '/config.php')) {
	require_once(dirname(dirname(__FILE__)) . '/config.php');
}

if(!empty($gallery->app->photoAlbumURL)) {
	$path = $gallery->app->photoAlbumURL;
}
else {
	$path = '../';
}

?>
var path = '<?php echo $path; ?>';

function gallery_toggle(id) {
	var img;
	var elem;

	img = document.getElementById('toggleBut_' + id);
	elem = document.getElementById('toggleFrame_' + id);

	if (elem.style.display == 'none') {
		elem.style.display = 'inline';
		img.src = path + '/images/collapse.gif';
	}
	else {
		elem.style.display = 'none';
		img.src = path + '/images/expand.gif';
	}
}

function gallery_toggle2(id) {
	var elem;

	elem = document.getElementById('toggleFrame_' + id);

	if (elem.style.display == 'none' || elem.style.display == '') {
		elem.style.display = 'inline';
	}
	else {
		elem.style.display = 'none';
	}
}

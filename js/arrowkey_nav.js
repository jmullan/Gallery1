/**
 * Gallery SVN ID:
 * $Id$
*/

function cursor_nav(e) {
	if(document.all) {
		taste = window.event.keyCode;
	}
	else {
		taste = e.which;
	}
	switch (taste) {
		case 37:
			backward = document.getElementById("g-nav-backward_1");
			if(backward) {
				window.location.href = backward.href;
			}
		break;

		case 39:
			forward = document.getElementById("g-nav-forward_1");
			if(forward) {
				window.location.href = forward.href;
			}
		break;

		default:
		break;
	}
}

document.onkeydown = cursor_nav;

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

	if(document.getElementById("g-navtable").dir == 'ltr') {
		var back    = 37;
		var forward = 39;
	}
	else {
		var back    = 39;
		var forward = 37;

	}

	switch (taste) {
		case back:
			if(document.getElementById("g-nav-backward_1")) {
			backward = document.getElementById("g-nav-backward_1");
			}
			else if(document.getElementById("g-navmicro-backward_1")) {
				backward = document.getElementById("g-navmicro-backward_1");
			}

			if(backward) {
				window.location.href = backward.href;
			}

		break;

		case forward:
			forward = document.getElementById("g-nav-forward_1");

			if(document.getElementById("g-nav-forward_1")) {
				forward = document.getElementById("g-nav-forward_1");
			}
			else if(document.getElementById("g-navmicro-forward_1")) {
				forward = document.getElementById("g-navmicro-forward_1");
			}


			if(forward) {
				window.location.href = forward.href;
			}
		break;

		default:
		break;
	}
}

document.onkeydown = cursor_nav;

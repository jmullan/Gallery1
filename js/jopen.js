/**
 * Gallery SVN ID:
 * $Id$
*/

function jopen(element) {
	selected = element.selectedIndex;
	if(selected == -1) {
		return;
	}

	selectedOption = element.options[selected];

	if(selectedOption.className.indexOf('g-disabled') > -1) {
		return;
	}

	if(selectedOption.className == 'url') {
		location.href = selectedOption.value;
	}
	else {
		window.open(selectedOption.value,'GalleryPopup','height=550,width=600,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes');
	}
}
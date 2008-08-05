/**
 * Gallery SVN ID:
 * $Id: jopen.js 16783 2007-07-20 22:48:27Z jenst $
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
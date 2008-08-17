/**
 * Gallery SVN ID:
 * $Id: sectionTabs.js.php 17305 2007-12-25 20:22:53Z JensT $
*/

function gallerySideBar() {
	this.status = 'closed';

	this.toggle = function() {
		if(this.status == 'open') {
			document.getElementById('g-sidebar-content').style.display='none';
			document.getElementById('g-sidebar-icon1').style.display='inline';
			document.getElementById('g-sidebar-icon2').style.display='none';

			this.status = 'close';
		}
		else {
			document.getElementById('g-sidebar-content').style.display='inline';
			document.getElementById('g-sidebar-icon1').style.display='none';
			document.getElementById('g-sidebar-icon2').style.display='inline';

			this.status = 'open';
		}
	}
}

g_sidebar = new gallerySideBar();

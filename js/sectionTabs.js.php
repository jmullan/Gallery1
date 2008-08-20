<?php

/**
 * Gallery SVN ID:
 * $Id$
*/

function insertSectionToggle() {
?>
	<!-- This Javascript and the Tabs are inspired by the Horde Forms code -->

	function configSection(inittab) {

		this.toggle = function(id) {
			document.getElementById(this.oldtab).style.display 	= 'none';
			document.getElementById('tab_' + this.oldtab).className	= '';
			document.getElementById(id).style.display		= 'inline';
			document.getElementById('tab_' + id).className		= 'g-activeTab';

			this.oldtab = id;
			document.getElementById('initialtab').value = id;
			this.currentSectionNr= this.getTabByName(id);

			if (this.currentSectionNr == Sections.length-1) {
				buttonDisableByName('go_nextTab');
			}
			else {
				buttonEnableByName('go_nextTab');
			}

			if (this.currentSectionNr == 0) {
				buttonDisableByName('go_backTab');
			}
			else {
				buttonEnableByName('go_backTab');
			}
		}

		this.getTabByNr = function(nr) {
			for (var itemNr=0; itemNr <= Sections.length; itemNr++) {
				if (Sections[itemNr] == Sections[nr]) {
					return (Sections[itemNr]);
				}
			}
		}

		this.getTabByName = function(name) {
			for (var itemNr = 0; itemNr <= Sections.length; itemNr++) {
				if (Sections[itemNr] == name) {
					return (itemNr);
				}
			}
		}

		this.nextTab = function() {
			if (this.currentSectionNr < Sections.length-1) {
				nextTab = this.getTabByNr(this.currentSectionNr+1);
				this.toggle(nextTab);
			}
		}

		this.prevTab = function() {
			if (this.currentSectionNr >0) {
				prevTab = this.getTabByNr(this.currentSectionNr-1);
				this.toggle(prevTab);
			}
		}

		// Init Values

		this.oldtab = inittab;
		this.currentSectionNr = this.getTabByName(inittab);

	}

	function buttonDisableByName(name) {
		for(nr in document.getElementsByName(name)) {
			document.getElementsByName(name)[nr].className = 'g-buttonDisable';
			document.getElementsByName(name)[nr].disabled = true;
		}
	}

	function buttonEnableByName(name) {
		for(nr in document.getElementsByName(name)) {
			document.getElementsByName(name)[nr].className = 'g-button';
			document.getElementsByName(name)[nr].disabled = false;
		}
	}

	<?php
}


<?php

function insertSectionToggle() {
?>

<!-- This Javascript and the Tabs are inspired by the Horde Forms code -->

        function configSection(inittab) {

                this.toggle = function(id) {
                        document.getElementById(this.oldtab).style.display 	= 'none';
                        document.getElementById('tab_' + this.oldtab).className = 'tab';
                        document.getElementById(id).style.display 		= 'inline';
                        document.getElementById('tab_' + id).className 		= 'tab-hi';

                        this.oldtab=id;
			document.getElementById('initialtab').value = id;
			this.currentSectionNr= this.getTabByName(id);
                }

		this.getTabByNr = function(nr) {
			for (var itemNr=0; itemNr <= Sections.length; itemNr++) {
				if (Sections[itemNr] == Sections[nr]) {
					return (Sections[itemNr]);
				}
			}
		}

		this.getTabByName = function(name) {
			for (var itemNr=0; itemNr <= Sections.length; itemNr++) {
				if (Sections[itemNr] == name) {
					return (itemNr);
				}
			}
		}

		this.nextTab = function() {
			if (this.currentSectionNr < Sections.length-1) {
				nextTab=this.getTabByNr(this.currentSectionNr+1);
				this.toggle(nextTab);
			}
		}

		this.prevTab = function() {
			if (this.currentSectionNr >0) {
				prevTab=this.getTabByNr(this.currentSectionNr-1);
				this.toggle(prevTab);
			}
		}

		// Init Values

                this.oldtab=inittab;
		this.currentSectionNr= this.getTabByName(inittab);

	}
<?php
}

<?php
/**
 * Gallery SVN ID:
 * $Id: multiInput.js.php 13850 2006-06-19 12:37:37Z jenst $
 * @author Jens Tkotz
*/
?>
<script language="JavaScript" type="text/javascript">

function MultiInput(inputFieldId, containerFieldId) {
	this.nr = 0;

	var container = document.getElementById(containerFieldId);
	var initInputField = document.getElementById(inputFieldId);

	this.newField = function() {
		if(initInputField.value == '') {
			return false;
		}

		this.nr++;
		var subdiv = document.createElement('div');
		subdiv.id = containerFieldId +'_'+ this.nr;

		// New input field
		var new_element = document.createElement('input');
		new_element.type = 'text';
		new_element.name = inputFieldId +'[]';
		new_element.value = initInputField.value;

		// New field deleter
		var new_deleter = document.createElement('input');
		new_deleter.type = 'button';
		new_deleter.value = '<?php echo "Delete" ?>';
		new_deleter.setAttribute('class', 'g-button');
		new_deleter.onclick = function() {
			container.removeChild(document.getElementById(subdiv.id));
			return false;
		}

		// Add new elements
		container.appendChild(subdiv);
		subdiv.appendChild(new_element);
		subdiv.appendChild(new_deleter);

		initInputField.value = '';
		initInputField.focus();
	}
}
</script>

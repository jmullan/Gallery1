<script language="JavaScript" type="text/javascript">
/**
 *
 * Licence:
 *   Use this however/wherever you like, just don't blame me if it breaks anything.
 *
 * Credit:
 *   If you're nice, you'll leave this bit:
 *
 *   Class by Stickman -- http://www.the-stickman.com
 *      with thanks to:
 *      [for Safari fixes]
 *         Luis Torrefranca -- http://www.law.pitt.edu
 *         and
 *         Shawn Parker & John Pennypacker -- http://www.fuzzycoconut.com
 *      [for duplicate name bug]
 *         'neal'
 */
function MultiSelector(list_target, max, fieldname, withCaption){

    // Where to write the list
    this.list_target = list_target;

    // How many elements?
    this.count = 0;

    // Is there a maximum?
    if( max ){
        this.max = max;
    } else {
        this.max = -1;
    };
    this.fieldname = fieldname;
    
    /**
    * Add a new file input element
    */
    this.addElement = function(element){

        // Make sure it's a file input element
        if( element.tagName == 'INPUT' && element.type == 'file' ){

            // Element name -- what number am I?
            element.name = this.fieldname + '[]';

            // Add reference to this object
            element.multi_selector = this;

            // What to do when a file is selected
            element.onchange = function(){

                // New file input
                var new_element = document.createElement('input');
                new_element.type = 'file';

                // Add new element
                this.parentNode.insertBefore(new_element, this);

                // Apply 'update' to element
                this.multi_selector.addElement(new_element);

                // Update list
                this.multi_selector.addListRow(this);

                // Hide this: we can't use display:none because Safari doesn't like it
                this.style.position = 'absolute';
                this.style.left = '-1000px';

            };
            // If we've reached maximum number, disable input element
            if( this.max != -1 && this.count >= this.max ){
                element.disabled = true;
            };

            // File element counter
            this.count++;
            // Most recent element
            this.current_element = element;

        } else {
            // This can only be applied to file input elements!
            alert('Error: not a file input element');
        };

    };

    /**
    * Add a new row to the list of files
    */

    this.addListRow = function( element ){

        var line = document.createElement('div');
        line.style.textAlign='right';
        var filenameCaptionDiv = document.createElement('div');
        filenameCaptionDiv.style.textAlign='left';

        line.appendChild(filenameCaptionDiv);

        // Delete button
        var deleteButton = document.createElement('input');
        deleteButton.type = 'button';
        deleteButton.value = '<?php echo _("Delete"); ?>';

        // Caption field
        var caption = document.createElement('input');
        caption.type = 'text';
        caption.size = 60;
        caption.name = 'usercaption[]';

	// horizontal line
	var hr = document.createElement('hr');

        // References
        line.element = element;

        // Delete function
        deleteButton.onclick= function(){

            // Remove element from form
            this.parentNode.element.parentNode.removeChild(this.parentNode.element);

            // Remove this row from the list
            this.parentNode.parentNode.removeChild(this.parentNode);

            // Decrement counter
            this.parentNode.element.multi_selector.count--;

            // Re-enable input element (if it's disabled)
            this.parentNode.element.multi_selector.current_element.disabled = false;

            // Appease Safari
            //    without it Safari wants to reload the browser window
            //    which nixes your already queued uploads
            return false;
        };

        if(withCaption) {
            // Set row value
            filenameCaptionDiv.innerHTML = element.value +'<br><?php echo _("Caption:"); ?> ';
            // Add caption
            filenameCaptionDiv.appendChild(caption);
        }
        else {
            // Set row value
            filenameCaptionDiv.innerHTML = element.value;
        }
        
        // Add button
        line.appendChild(deleteButton);

        // Add button
        line.appendChild(hr);

        // Add it to the list
        this.list_target.appendChild(line);
    };
};
</script>
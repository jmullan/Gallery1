/**
 * Gallery SVN ID:
 * $Id$
*/

/* -------------------------------------------------------------------
 * hasOptions(obj)
 *  Utility function to determine if a select object has an options array
 * Author: Matt Kruse <matt@mattkruse.com>
 * WWW: http://www.mattkruse.com/
 * -------------------------------------------------------------------
*/
function hasOptions(obj) {
    if (obj!=null && obj.options!=null) {
        return true;
    }
    else {
        return false;
    }
}
	
/* -------------------------------------------------------------------
 * sortSelect(select_object)
 *   Pass this function a SELECT object and the options will be sorted
 *   by their text (display) values
 * Author: Matt Kruse <matt@mattkruse.com>
 * WWW: http://www.mattkruse.com/
 * -------------------------------------------------------------------
*/
function sortSelect(obj) {
    if (!hasOptions(obj)) {
        return;
    }
    
    var o = new Array();

    for (var i = 0; i < obj.options.length; i++) {
        o[o.length] = new Option(obj.options[i].text, obj.options[i].value, obj.options[i].defaultSelected, obj.options[i].selected) ;
        o[o.length-1].className = obj.options[i].className;
    }
    
    if (o.length == 0) { return; }
    o = o.sort(
        function(a,b) {
            if ((a.text + "") < (b.text + "")) { return -1; }
            if ((a.text + "") > (b.text + "")) { return 1; }
            return 0;
        }
    );

    for (var i = 0; i < o.length; i++) {
        obj.options[i] = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
        obj.options[i].className = o[i].className;
    }
}

function moveSelected(from, to) {
    var fromBox = document.getElementById(from);
    var toBox = document.getElementById(to);

    var fromCount = fromBox.length;
    var removeArray = new Array();
    
    for (i = 0; i < fromCount; i++) {
        if(fromBox.options[i].selected) {
            var newEntry = document.createElement("option");
            newEntry.text = fromBox.options[i].text;
            newEntry.value = fromBox.options[i].value;
            if(fromBox.options[i].className != 'g-selected') {
                newEntry.className = 'g-selected';
            }
            
            toBox.add(newEntry, null);
            removeArray[removeArray.length] = i;
        }
    }
    
    if(removeArray.length > 0) {
        removeArray.reverse();
        for (i = 0; i < removeArray.length; i++) {
            fromBox.remove(removeArray[i]);
        }
    }
    
   sortSelect(toBox);
}

function checkAllOptions(selectBoxId) {
    var selectBox = document.getElementById(selectBoxId);
    for(i = 0 ; i < selectBox.options.length; i++) {
        selectBox.options[i].selected = true;
    }
}
<?php 
	if (stristr($HTTP_SERVER_VARS['REQUEST_URI'],"slideshow") == FALSE) echo "    <form name=\"TopForm\">";
?>
	<td valign="bottom">
		<select style="font-size:8pt;" name="newlang" onChange="ML_reload()" size="1">
<?php

	if ($GALLERY_EMBEDDED_INSIDE) $language_names=$nuke_langname ;
	else $language_names=$langname;

	foreach ($gallery->ML->available_lang as $value) {
		if ($gallery->ML->language == $value) $selected="selected"; else $selected="";
		echo "\n\t\t<option value=\"$value\" $selected>" . $nls['languages'][$value] ."</option>";
	}
?>
		</select>
	</td>
<?php
	if (stristr($HTTP_SERVER_VARS['REQUEST_URI'],"slideshow") == FALSE) echo "\n   </form>\n";
?>
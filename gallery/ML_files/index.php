<?php
require "./ML_config.php";

?>
<html>
        <head>
        <title>Gallery Check</title>
	<style>
		td,th { text-align:center;padding-left:20px }
	</style>
        </head>
<body>
<table border="0">
<tr>
	<th></th>
	<th width="30">&nbsp;</th>
	<th>Value</th>
	<th>Status</th>
</tr>
<tr>
	<td>PHP Version</td>
	<td>:</td>
	<td><?php echo phpversion() ; ?></td>
	<td><?php 
		if ($gallery->version_ok == 1) {
			echo '<img src="ML_icons/kasten_haken_gruen.gif"';
		} else {
			echo '<img src="ML_icons/kasten_kreuz_rot.gif"';
		}
	?>
	</td>
</tr>
<tr>
	<td>Gettext Support</td>
	<td>:</td>
	<td>&nbsp;</td>
	<td><?php 
		if ($gallery->gettext_ok == 1) {
			echo '<img src="ML_icons/kasten_haken_gruen.gif"';
		} else {
			echo '<img src="ML_icons/kasten_kreuz_rot.gif"';
		}
	?>
	</td>
</tr>
<tr>
	<td>Locale Support </td>
	<td>:</td>
	<td><?php echo $gallery->locale ; ?></td>
	<td><?php 
		if ($gallery->locale_ok == 1) {
			if ($gallery->ML_warning) {
				echo '<img src="ML_icons/kasten_haken_gelb.gif"';
			} else {
				echo '<img src="ML_icons/kasten_haken_gruen.gif"';
			}
		} else {
			echo '<img src="ML_icons/kasten_kreuz_rot.gif"';
		}
	?>
	</td>
</tr>
<tr>
	<td>Path to Gallery locales</td>
	<td>:</td>
	<td><?php echo $gallery->locale_path ; ?></td>
	<td><?php 
		if ($gallery->locale_path_ok != 0) {
			echo '<img src="ML_icons/kasten_haken_gruen.gif"';
		} else {
			echo '<img src="ML_icons/kasten_kreuz_rot.gif"';
		}
	?>
	</td>
</tr>
</table>

</body>
</html>
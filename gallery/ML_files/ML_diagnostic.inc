<?php

function show_status($status) {
	$replace=array("ML_files","setup");
	$url=str_replace ($replace,"",dirname($_SERVER['SCRIPT_NAME'])) ;
	
	if ($status==2) {
	       echo '<img src="' . $url . '/ML_files/ML_icons/kasten_haken_gruen.gif">';
	}

	if ($status==1) {
	       echo '<img src="' . $url . '/ML_files/ML_icons/kasten_haken_gelb.gif">';
	}

	if ($status==0) {
		echo '<img src="' . $url . '/ML_files/ML_icons/kasten_kreuz_rot.gif">';
	}
}
?>
<div align="center">
<p>
	<?php echo _("You are using the Multilanguage Version of <i>Gallery</i>") ; ?>
<br>	<?php echo _("If you see any red cross below you cant use the ML") ; ?>
<br>	<?php echo _("If the language file exists and everything is fine you should see the page in : ") . $nls['languages'][$gallery->ML->language] ; ?>
</p>

<table border="1" width="80%">
<tr>
	<th>&nbsp;</th>
	<th width="20">&nbsp;</th>
	<th><?php echo _("Note") ; ?></th>
	<th><?php echo _("Value") ; ?></th>
	<th><?php echo _("Status") ; ?></th>
</tr>
<tr>
	<td><?php echo _("PHP Version") ; ?></td>
	<td>:</td>
	<td>Minimum for ML is 4.1.0</td>
	<td align="center"><?php echo phpversion() ; ?></td>
	<td align="center"><?php show_status($gallery->ML->version_ok); ?></td>
</tr>
<tr>
	<td><?php echo _("Gettext Support") ?></td>
	<td>:</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td align="center"><?php show_status($gallery->ML->gettext_ok) ; ?></td>
</tr>
<?php	
	if ($gallery->ML->gettext_ok == 1) { ?>
<tr>
	<td colspan="5" style="color:red"><b><?php echo $gallery->ML->warning ; ?></b></td>
</tr>
<?php	}

if ($gallery->ML->gettext_ok == 2) {
?>
<tr>
	<td><?php echo _("Locale Support") ; ?> </td>
	<td>:</td>
	<td>
<?php 
switch ($gallery->ML->locale_ok) {
	case 0:
        	echo _("You don't have a sufficient locale Support!");
        break;
	case 1:
		echo _("ML Gallery could be displayed in ") . count($nls['languages']) . 
			 _(" languages, but your system does not have all necessary locales installed.");
        break;
	case 2:
		echo _("ML Gallery is ready to be displayed in all possible languages") ;
        break;
}
?>
	</td>
	<td>&nbsp;</td>
	<td align="center"><?php echo show_status($gallery->ML->locale_ok) ; ?></td>
</tr>
<tr>
	<td><?php echo _("Path to Gallery locales") ?></td>
	<td>:</td>
	<td>&nbsp;</td>
	<td align="center"><?php echo $gallery->ML->locale_path ; ?></td>
	<td align="center"><?php show_status($gallery->ML->locale_path_ok) ; ?></td>
</tr>
<?php
}
?>
</table>
</div>

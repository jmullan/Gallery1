<?php

	error_reporting(E_ALL & ~E_NOTICE);

	$cwd=strtr (getcwd(),"\\","\/");
	$replace=array("/ML_files","/setup");
	$gallery->path=str_replace($replace,"",$cwd) . "/";

//echo "<br> cwd " . getcwd();
//echo "<br>path ". $gallery->path;

	$filename = $gallery->path . "ML_files/ML_config.php";
	if ($_POST) {
/*
		echo "POST-Daten:<br>";
		echo "<pre>";
		print_r($_POST);
		echo "</pre>";
*/

		if (!$handle = fopen($filename, "w+")) {
			echo _("Unable to open ") . $filename ;
			exit;
		}
		
		fwrite($handle,"<?php\n");

		$notforconfig=array("old_select");
		foreach ($_POST as $key => $value) {
			if (! in_array($key,$notforconfig)) {			
				if (is_array($value)) {
					foreach ($value as $subkey => $subvalue) {
						$line="\n ". '$gallery->ML->'.$key . "['$subkey']='$subvalue';";
//						echo "<br>". $line;
						fwrite($handle, $line);
					}
				} else {
					$line="\n ". '$gallery->ML->'. "$key='$value';";
//					echo "<br>". $line;
					fwrite($handle, $line);
				}
			}
		}

		fwrite($handle,"\n\n". '$gallery->ML->ignore_errors = \'no\';');
		fwrite($handle,"\n". '$gallery->path = \'' . $gallery->path .'\';');
		fwrite($handle,"\n\n". 'require($gallery->path . "ML_files/ML_functions.php");');
		fwrite($handle,"\n\n ?>\n");
	
		fclose($handle);

		$_POST=""; $new_conf=1;
	}

	@include $gallery->path . "ML_files/ML_config.php";
	if (! $gallery->ML) {
		require($gallery->path . "ML_files/ML_functions.php");
	}
?>
<html>
<body dir=<?php echo $gallery->direction ?>>
<?php
        include ($gallery->path ."ML_files/ML_info_addon.inc");
?>
<h2 align="center"><?php echo _("ML Config Wizard"); ?></h2>
<p align="center">
<?php
	echo _("This is the Configuration Wizard for the multilanguage Version of Gallery") ; 
	echo "\n<br>" . _("First we do some check if the ML works at all");
?>
</p>
<?php 
	require ($gallery->path . "ML_files/ML_diagnostic.inc") ; 
	
	if (strpos(dirname($_SERVER['SCRIPT_NAME']),"setup")) {
		$index="../index.php";
		$wizard="ML_wizard.php";
	}
	else {
		$index="index.php";
		$wizard="setup/ML_wizard.php";
	}

	if ($new_conf) {


		?>
		<div align="center">
		<p style="color:green"><?php echo _("Done. Your ML configuration was written succesfully.") ; ?></p>
		<br>		
		<form>
		<input type="button" name="gallery_config" value="<?php echo _("Start Gallery config") ; ?>" onClick="self.location.href='<?php echo $index ; ?>' ;">
		<input type="button" name="gallery_config" value="<?php echo _("Reconfigure ML") ; ?>" onClick="self.location.href='<?php echo $wizard ; ?>';">
		</form>
		</p>
		<?php

	}


	if (! file_exists($filename)) {
		$gallery->ML->error= _("Your ML_config.php does not exist, please create it in the ML_files folder.");
	}
	elseif (! is_writable($filename)) {
		$gallery->ML->error= _("Your ML_config.php is not writeable, please go into the ML_files folder and chmod it 777");
	}
	
	if (! $gallery->ML->error && ! $new_conf) {
?>
<br>
<form name="config" method="post">

<table width="80%" align="center" border="1">

<tr>
	<td><?php
		echo _("ML Gallery has three modes.");
		echo "\n\t<br><br>" . _("Mode 1 : Gallery is only displayed in one language");
		echo "\n\t<br>" . _("Mode 2 : Gallery is displayed in the language the browser sends.");
		echo "\n\t<br>" . _("Mode 3 : The User can choose the language via select Box.");
	?></td>
	<td><?php echo _("Gallery Mode"); ?>
		<select name="mode">
<?php
		if (! $gallery->ML->mode) $gallery->ML->mode=2;
		for ($i=1; $i<=3; $i++) {
			echo "\n\t\t<option";
			if ($gallery->ML->mode == $i) echo " selected";
			echo ">$i</option>";
		}
?>
		</select>
	</td>
</tr>

<tr><td colspan="2" align="center">---------------------------------</td></tr>

<tr>
	<td><?php 
		if ($gallery->ML->gettext_ok == 1) {
			echo _("Your are using ML Gallery with a PHP without gettext Support.") ;
			echo "\n<br>". _("This will slow Gallery a bit, but therefor ML gallery can be shown in all possible languages.");
		}
		if ($gallery->ML->gettext_ok == 2 && $gallery->ML->locale_ok > 0 ) {
			echo _("ML Gallery works right now without problems with these languages :") . "<br>";

			$last=$gallery->ML->working_locales[count($gallery->ML->working_locales)-1] ;
			foreach ($gallery->ML->working_locales as $value) {
				echo $nls['languages'][$value];
				if ($value != $last ) echo ", ";
			}
		}
		?>
	</td>
</tr>
<?php
	if ($gallery->ML->maybe_working_locales) { ?>
<tr>
	<td><?php
		echo _("These languages are maybe possible, but there seems to be a locale problem.");
		echo "\n\t<br>" . _("Please select a locale which you think it might work.") ;
	?>
	</td>

	<td>
		<table border="1">
		<tr>
			<th><?php echo _("Language") ; ?></th>
			<th><?php echo _("Possible Locales") ?></th>
		</tr>
	<?php
	foreach ($gallery->ML->maybe_working_locales as $value) {
		echo "\t\t<tr>";
		echo "\n\t\t\t<td style=\"background-color:yellow\">" . $nls['languages'][$value] ."</td>";
		echo "\n\t\t\t<td><select name=\"locale_alias[$value]\">";
		foreach ($gallery->ML->working_locale_alias[$value] as $v2) {
			$sel ="" ; if ($v2 == $value) $sel='selected' ;
			echo "\n\t\t\t\t<option $sel >" . $v2 ."</option>";
			}
		echo "\n\t\t\t</td>";
		echo "\n\t\t</tr>\n";
	}
	?>
		</table>
	</td>
</tr>
<?php
}

	if ($gallery->ML->non_working_locales) {?>
<tr>
	<td><?php echo _("Unfortunately these language wont work, because the corresponding locales are missing."); ?></td>
</tr>
<tr>
	<td style="color:red"><?php
	$last=$gallery->ML->non_working_locales[count($gallery->ML->non_working_locales)-1] ;
	foreach ($gallery->ML->non_working_locales as $value) {
		echo $nls['languages'][$value] ;
		if ($value != $last ) echo ", ";
	}
?>
	</td>
</tr>

<tr><td colspan="2" align="center">---------------------------------</td></tr>
<?php } ?>

<script type="text/javascript">
function CheckAuswahl() {
	var old_select;
	var selected;
	selected=document.config.elements[1].selectedIndex;
	old_select=document.config.old_select.value;
	document.config.elements[3+selected].checked=true;
	document.config.elements[3+eval(old_select)].checked=false;
	document.config.old_select.value=selected;
}
</script>


<tr>
	<td><?php echo _("Which language do you want as your default language ?"); ?></td>

	<td><select name="default[language]" onChange="CheckAuswahl();" >

	<?php
	$i=0;
	foreach ($gallery->ML->working_locales as $value) {
		$sel ="" ; if ($value == $gallery->ML->browser_language) {
			$sel='selected';
			$stelle=$i;
		}
		echo "\n\t<option value=\"$value\" $sel>" . $nls['languages'][$value] . "</option>";
		$i++;
	}
	if ($gallery->ML->maybe_working_locales) {
		foreach ($gallery->ML->maybe_working_locales as $value) {
			$sel ="" ; if ($value == $gallery->ML->browser_language) {
				$sel='selected' ;
				$stelle=$i ;
			}
			echo "\n\t<option value=\"$value\" $sel style=\"background-color:yellow\">" . $nls['languages'][$value] . "</option>";
			$i++;
		}
	}
	?>
	</select>
	</td>
<input name="old_select" type="hidden" value="<?php echo $stelle ; ?>">
</tr>

<tr><td colspan="2" align="center">---------------------------------</td></tr>

<?php
	if ($gallery->ML->working_locales || $gallery->ML->maybe_working_locales) { ?>
<tr>
	<td><?php echo _("If you want to use mode 3, which language should be available ?") ; ?></td>

	<td>
	<?php
	if ($gallery->ML->working_locales) { 
		foreach ($gallery->ML->working_locales as $value) {
			$sel ="" ; if ($value == $gallery->ML->browser_language) $sel='checked' ;
			echo "\n\t<br><input name=\"available_lang[]\" value=\"$value\" type=\"checkbox\" $sel>" . $nls['languages'][$value] ;
		}
	}

	if ($gallery->ML->maybe_working_locales) {
		foreach ($gallery->ML->maybe_working_locales as $value) {
			$sel ="" ; if ($value == $gallery->ML->browser_language) $sel='checked' ;
			echo "\n\t<br><input name=\"available_lang[]\" value=\"$value\" type=\"checkbox\" $sel><span style=\"background-color:yellow\">" . $nls['languages'][$value] ."</span>";
		}
	}
	?>
	</td>
</tr>

<tr><td colspan="2" align="center">---------------------------------</td></tr>
<?php } ?>

<tr>
	<td><?php 
		echo _("ML Gallery contains a little <i>hack</i> which allows ALL users to uss the \"View all Comments\" Feature.") ;
		echo "\n<br>(" ._("Do you want to turn this on the ?"); 
	?></td>

	<td><select name="comment_hack">
		<option value="1"><?php echo _("yes") ; ?></option>
		<option value="0"><?php echo _("no") ; ?></option>
	</select>
	</td>
</tr>
</table>

<p align="center">
	<input type="submit" value="<?php echo _("Create ML Config") ; ?>">
</p>
</form>

<?php
}
if ($gallery->ML->error) {
	echo '<p align="center" style="color:red">' . _("Error") . ': '. $gallery->ML->error . '</p>';
}
?>
</body>
</html>

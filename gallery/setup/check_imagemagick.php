<?php /* $Id$ */ ?>
<?php require("../config.php"); ?>
<html>
<body dir=<?php echo $gallery->direction ?>>

<h1> <?php echo _("Check") ?> ImageMagick </h1>

<?php echo _("This script is designed to examine your ImageMagick installation to see if it is ok to be used by Gallery.") ?>
<?php echo _("You should run this script <b>after</b> you have run the config wizard, if you have had problems with your ImageMagick installation that the wizard did not detect.") ?>

<p>
<ol>

<li> <?php echo _("Loading configuration files.") ?>
<?php echo _("If you see an error here, it is probably because you have not successfully run the config wizard.") ?>

<?php
require('init.php'); 
require("../config.php"); 
?>

<p>

<li> <?php echo _("Let us see if we can figure out what operating system you are using.") ?>

<p> 
<?php echo _("This is what your system reports") ?>:
<br>
<b><?php passthru("uname -a"); ?></b>

<p>
<?php echo _("This is the type of system PHP was compiled on") ?>:
<br>
<b><?php echo php_uname() ?></b>

<p>

<?php echo _("Make sure that the values above make sense to you.") ?>

<p>

<?php echo _("Look for keywords like &quot;Linux&quot;, &quot;Windows&quot;, &quot;FreeBSD&quot;, etc. in the output above.") ?>
<?php echo _("If both the attempts above failed, you should ask your ISP what operating system you are using.") ?>
<?php echo _("You can check via") ?> <a href="http://www.netcraft.com/whats?host=<?php echo $HTTP_SERVER_VARS['HTTP_HOST'] ?>">Netcraft</a>,
<?php echo _("they can often tell you.") ?>
<p>

<li> <?php echo _("You told the config wizard that your ImageMagick binaries live here") ?>:
<p>
<ul>
<b><?php echo $gallery->app->ImPath ?></b>
</ul>
<p>

<?php echo _("If that is not right (or if it is blank), re-run the configuration wizard and enter a location for ImageMagick.") ?>

<p>

<?php
$debugfile = tempnam($gallery->app->tmpDir, "gallerydbg");
?>

<?php
if (!inOpenBasedir($gallery->app->imDir)) {
?>
<b><?php echo _("Note") ?></b>:  <?php echo _("Your ImageMagick directory") ?> (<?php echo $gallery->app->imDir ?>)
<?php echo _("is not in your open_basedir list (specified in php.ini)") ?>: <ul>
 <?php echo join('<br>', explode(':', ini_get('open_basedir'))) ?>
 </ul>
<?php echo _("So we can't perform all of our basic checks on the files to make sure that they exist and they're executable.") ?>
<br><br>

<?php
}
?>

<li><?php echo _("We are going to test each ImageMagick binary individually.") ?>

<?php
if ($show_details) {
	print "<a href=check_imagemagick.php?show_details=0>" .
			_("Click here") ."</a> ". _("to hide the details") ."</a>";
} else {
	print _("If you see errors, you should") .
		" <a href=check_imagemagick.php?show_details=1>" .
			_("click here") ."</a> " . _("to see more details") ."</a>";
}
?>

<pre>
<?php
$binaries = array(
		"identify",
		"convert"
	    );

foreach ($binaries as $bin) {
	checkImageMagick($bin);
}

if (fs_file_exists($debugfile)) {
    fs_unlink($debugfile);
}

function checkImageMagick($cmd) {
	global $gallery;
	global $show_details;
	global $debugfile;

	$cmd = fs_executable($gallery->app->ImPath . "/$cmd");
	print _("Checking"). " ". fs_import_filename($cmd). "\n";

	$ok = 1;

	if ($ok) {
		if (inOpenBasedir($gallery->app->pnmDir)) {
			if (!fs_file_exists($cmd)) {
				$error = _("File") . $cmd . _("does not exist.") ;
				$ok = 0;
			}
		}
	}

	$cmd .= " -help";
	
	fs_exec($cmd, $results, $status, $debugfile);

	if ($ok) {
		if ($status != $gallery->app->expectedExecStatus) {
			$error = _("Expected status") .": " .
				$gallery->app->expectedExecStatus .
				", ". _("but actually received status") .": $status";
			$ok = 0;
		}
	}

	/*
	 * Windows does not appear to allow us to redirect STDERR output, which
	 * means that we can't detect the version number.
	 */
	if ($ok) {
	    if (substr(PHP_OS, 0, 3) == 'WIN') {
			$version = "<i>" . _("can't detect version on Windows") ."</i>";
	    }
		else if (eregi("version: (.*) http(.*)$", $results[0], $regs)) {
			$version = $regs[1];
		} else {
			$error = $results[0];
			$ok = 0;
		}
	}

	if ($ok) {
                print "<font color=green>". _("Ok!  Version") .": $version</font>";
        } else {
                print "<font color=red>". _("Error") ."! ($error) </font>";
        }
	print "\n\n";
}
    
function inOpenBasedir($dir) {
    $openBasedir = ini_get('open_basedir');
    if (empty($openBasedir)) {
	return true;
    }

    return in_array($dir, explode(':', $openBasedir));
}
    
?>
</pre>

<p>

<?php echo _("If you see an error above complaining about reading or writing to") ?> <b><?php echo $debugfile ?></b>,
<?php echo _("then this is likely a permission/configuration issue on your system.") ?>
<?php echo _("If it mentions <i>open_basedir</i> then it's because your system is configured with") ?> <a href="http://www.php.net/manual/en/configuration.php#ini.open-basedir"> open_basedir</a> <?php echo _("enabled") ?>.
<?php echo _("You should talk to your system administrator about this, or see the") ?> <a href=http://gallery.sourceforge.net/help.php><?php echo _("Gallery Help Page") ?></a>

<p>

<?php echo _("For other errors, please refer to the list of possible responses in") ?> <a href=http://gallery.sourceforge.net/faq.php>FAQ</a> 4.4
<?php echo _("to get more information") ?>.

</ol>

<?php echo _("Return to the") ?> <a href="index.php"><?php echo _("config wizard") ?></a>.

</body>
</html>

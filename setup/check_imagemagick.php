<?php /* $Id$ */ ?>
<?php 
require("../config.php"); 
require("../util.php");
emulate_gettext();
?>

<html>
<body dir=<?php echo $gallery->direction ?>>
<?php $app_name='ImageMagick' ?>

<h1> <?php echo sprintf(_("Check %s"), $app_name) ?> </h1>

<?php echo sprintf(_("This script is designed to examine your %s installation to see if it is ok to be used by Gallery."), $app_name);
echo sprintf(_("You should run this script <b>after</b> you have run the config wizard, if you have had problems with your %s installation that the wizard did not detect."), $app_name) ?>

<p>
<ol>

<li> <?php echo _("Loading configuration files.  If you see an error here, it is probably because you have not successfully run the config wizard.") ?>

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
<?php echo _("This is the type of system on which PHP was compiled") ?>:
<br>
<b><?php echo php_uname() ?></b>

<p>

<?php echo _("Make sure that the values above make sense to you.") ?>

<p>

<?php 
echo sprintf(_("Look for keywords like %s, %s, %s etc. in the output above."),
		'&quot;Linux&quot;', '&quot;Windows&quot;', '&quot;FreeBSD&quot;');
echo _("If both the attempts above failed, you should ask your ISP what operating system you are using."); 
echo sprintf(_("You can check via %s, they can often tell you."),
		'<a href="http://www.netcraft.com/whats?host=' . 
		$HTTP_SERVER_VARS['HTTP_HOST'] . 
		'">Netcraft</a>') ;
?>
<p>

<li> <?php echo sprintf(_("You told the config wizard that your %s binaries live here:"), 
		$app_name) ?>
<p>
<ul>
<b><?php echo $gallery->app->ImPath ?></b>
</ul>
<p>

<?php echo sprintf(_("If that is not right (or if it is blank), re-run the configuration wizard and enter a location for %s."), $app_name) ?>

<p>

<?php
$debugfile = tempnam($gallery->app->tmpDir, "gallerydbg");
?>

<?php
if (!inOpenBasedir($gallery->app->ImPath)) {
?>
<?php echo sprintf(_("<b>Note:</b>  Your %s directory (%s) is not in your open_basedir list (specified in php.ini) %s so we can't perform all of our basic checks on the files to make sure that they exist and they're executable."),
		$app_name,
		$gallery->app->ImPath,
		'<ul>'.  join('<br>', explode(':', ini_get('open_basedir'))) .
		'</ul>') ?>
<br><br>

<?php
}
?>

<li><?php echo sprintf(_("We are going to test each %s binary individually."), $app_name) ?>

<?php
if (!empty($show_details)) {
	print sprintf(_("%sClick here%s to hide the details"),
		'<a href="check_imagemagick.php?show_details=0">', '</a>');
} else {
	print sprintf(_("If you see errors, you should %sclick here%s to see more details"), 
			'<a href="check_imagemagick.php?show_details=1">',
			'</a>');
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
	print sprintf(_("Checking %s"), fs_import_filename($cmd)) . "\n";

	$ok = 1;

	if ($ok) {
		if (inOpenBasedir($gallery->app->pnmDir)) {
			if (!fs_file_exists($cmd)) {
				$error = sprintf(_("File %s does not exist."),
						$cmd);
				$ok = 0;
			}
		}
	}

	$cmd .= " -help";
	
	fs_exec($cmd, $results, $status, $debugfile);

	if ($ok) {
		if ($status != $gallery->app->expectedExecStatus) {
			$error = sprintf(_("Expected status: %s, but actually received status %s."),
					$gallery->app->expectedExecStatus,
					$status);
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
                print "<font color=green>". sprintf(_("Ok!  Version: %s"),
				$version)."</font>";
        } else {
                print "<font color=red>". sprintf(_("Error! %s"), $error)." </font>";
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

<?php 
echo sprintf(_("If you see an error above complaining about reading or writing to %s then this is likely a permission/configuration issue on your system.  If it mentions %s then it's because your system is configured with %s enabled."),
		"<b>$debugfile</b>",
		'<i>open_basedir</i>',
		'<i>open_basedir</i>');

echo "  ";
echo sprintf(_("You should talk to your system administrator about this, or see the %sGallery Help Page%s."),
                '<a href=http://gallery.sourceforge.net/help.php>',
                '</a>');
?>
<p>

<?php echo sprintf(_("For other errors, please refer to the list of possible responses in %s to get more information."),
		'<a href="http://gallery.sourceforge.net/faq.php">FAQ</a> C.2');
?>
</ol>

<?php echo sprintf(_("Return to the %sconfig wizard%s."),
		'<a href="index.php">', '</a>');
?>

</body>
</html>

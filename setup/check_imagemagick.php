<h1> Check ImageMagick </h1>

This script is designed to examine your ImageMagick installation to see if
it is ok to be used by Gallery.  It really should be integrated into
the config wizard, but one thing at a time.  You should run this
script <b>after</b> you have run the config wizard, if you have had
problems with your ImageMagick installation that the wizard did not detect.

<p>
<ol>

<li> Loading configuration files.  If you see an error here, it is probably
because you have not successfully run the config wizard.

<?php
require('init.php'); 
require("../config.php"); 
?>

<p>

<li> Let us see if we can figure out what operating system you are
using.

<p>
This is what your system reports:
<br>
<b><?php passthru("uname -a"); ?></b>

<p>
This is the type of system PHP was compiled on:
<br>
<b><?php echo php_uname() ?></b>

<p>

Make sure that the values above make sense to you.

<p>

Look for keywords like "Linux", "Windows", "FreeBSD", etc. in the
output above.  If both the attempts above failed, you should ask your
ISP what operating system you are using.  You can check via
<a href="http://www.netcraft.com/whats?host=<?php echo $HTTP_SERVER_VARS['HTTP_HOST']?>">Netcraft</a>,
they can often tell you.  
<p>

<li> You told the config wizard that your ImageMagick binaries live here:
<p>
<ul>
<b><?php echo $gallery->app->ImPath ?></b>
</ul>
<p>

If that is not right (or if it is blank), re-run the configuration
wizard and enter a location for ImageMagick.

<p>

<?php
$debugfile = tempnam($gallery->app->tmpDir, "gallerydbg");
?>

<li>We are going to test each ImageMagick binary individually.  

<?php
if ($show_details) {
	print "<a href=check_imagemagick.php?show_details=0>" .
			"Click here</a> to hide the details</a>";
} else {
	print "If you see errors, you should <a href=check_imagemagick.php?" .
			"show_details=1>click here</a> to see more details</a>";
}
?>

<pre>
<?php
$binaries = array(
		"identify",
		"convert",
		"composite"
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
	print "Checking ". fs_import_filename($cmd). "\n";

	$ok = 1;

	if ($ok) {
		if (!fs_file_exists($cmd)) {
			$error = "File $cmd does not exist.";
			$ok = 0;
		}
	}

	$cmd .= " -help";
	
	fs_exec($cmd, $results, $status, $debugfile);

	if ($ok) {
		if ($status != $gallery->app->expectedExecStatus) {
			$error = "Expected status: " .
				$gallery->app->expectedExecStatus .
				", but actually received status: $status";
			$ok = 0;
		}
	}

	/*
	 * Windows does not appear to allow us to redirect STDERR output, which
	 * means that we can't detect the version number.
	 */
	if ($ok) {
	    if (substr(PHP_OS, 0, 3) == 'WIN') {
			$version = "<i>can't detect version on Windows</i>";
	    }
		else if (eregi("version: (.*) http(.*)$", $results[0], $regs)) {
			$version = $regs[1];
		} else {
			$error = $results[0];
			$ok = 0;
		}
	}

	if ($ok) {
		print "<font color=green>Ok!  Version: $version</font>";
	} else {
		print "<font color=red>Error! ($error) </font>";
	}
	print "\n\n";
}
    
?>
</pre>

<p>

If you see an error above complaining about reading or writing to
<b><?php echo $debugfile ?></b>, then this is likely a permission/configuration
issue on your system.  If it mentions <i>open_basedir</i> then it's
because your system is configured with <a
href="http://www.php.net/manual/en/configuration.php#ini.open-basedir">
open_basedir</a> enabled.  You should talk to your system
administrator about this, or see the <a href=http://gallery.sourceforge.net/help.php>Gallery Help Page</a>

<p>

For other errors, please refer to the list of possible responses in <a
href=http://gallery.sourceforge.net/faq.php>FAQ</a> 4.4 to get more
information.

</ol>

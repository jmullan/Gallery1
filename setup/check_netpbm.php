<h1> Check NetPBM </h1>

This script is designed to examine your NetPBM installation to see if
it's ok to be used by Gallery.  It really should be integrated into
the config wizard, but one thing at a time.  You should run this
script <b>after</b> you've run the config wizard, if you've had
problems with your NetPBM installation that the wizard did not detect.

<p>
<ol>

<li> Loading configuration files.  If you see an error here, it is probably
because you have not successfully run the config wizard.

<? 
require('init.php'); 
require("../config.php"); 
?>

<p>

<li> Let's see if we can figure out what operating system you're
using.

<p>
Attempt #1: <b><? passthru("uname -a"); ?></b>

<p>
Attempt #2: <b><?= php_uname() ?></b>

<p>

Make sure that the value above makes sense to you.

<p>

Look for keywords like "Linux", "Windows", "FreeBSD", etc. in the
output above.  If both the attempts above failed, you should ask your
ISP what operating system you're using.  You can also try entering
your website hostname into <a href="http://www.netcraft.com">Netcraft</a>,
they can often tell you.  
<p>

<li> You told the config wizard that your NetPBM binaries live here:
<p>
<ul>
<b><?= $gallery->app->pnmDir ?></b>
</ul>
<p>

If that's not right (or if it's blank), re-run the configuration
wizard and enter a location for NetPBM.

<p>

<?
$debugfile = tempnam("", "gallerydbg");
?>

<li>We're going to test each NetPBM binary individually.  

<?
if ($show_details) {
	print "<a href=check_netpbm.php?show_details=0>Click here</a> to hide the details</a>";
} else {
	print "<a href=check_netpbm.php?show_details=1>Click here</a> to see more details</a>";
}
?>

<pre>
<?
$binaries = array("giftopnm",
		  "jpegtopnm",
		  "pngtopnm",
		  "pnmcut",
		  "pnmfilxe",
		  "pnmflip",
		  "pnmrotate",
		  "pnmscale",
		  "pnmtopng",
		  "ppmquant",
		  "ppmtogif",
		  $gallery->app->pnmtojpeg,
	    );

foreach ($binaries as $bin) {
	checkNetPbm($bin);
}

function checkNetPbm($cmd) {
	global $gallery;
	global $show_details;
	global $debugfile;

	$cmd = fs_import_filename($gallery->app->pnmDir . "/$cmd");
	print "Checking $cmd\n";

	$ok = 1;

	if ($ok) {
		if (!fs_file_exists("$cmd")) {
			$error = "File $cmd does not exist.";
			$ok = 0;
		}
	}

	$cmd .= " --version";
	fs_exec($cmd, $results, $status, $debugfile);

	if ($ok) {
		if ($status != $gallery->app->expectedExecStatus) {
			$error = "Expected status: " .
				$gallery->app->expectedExecStatus .
				", but actually received status: $status";
			$ok = 0;
		}
	}

	if ($ok) {
		if ($fd = fopen($debugfile, "r")) {
			$linecount = 0;
			$version = null;
			while (!feof($fd)) {
				$linecount++;
				$buf = fgets($fd, 4096);
				if ($linecount == 1) {
					if (eregi("using libpbm from netpbm version: netpbm (.*)[\n\r]$", 
						$buf, $regs)) {
						$version = $regs[1];
					} else {
						$error = $buf;
						$ok = 0;
					}
				}
				if ($show_details) {
					print $buf;
				}
			}
			fclose($fd);
		}
	}

	if ($ok) {
		print "<font color=green>Ok!  Version $version</font>";
	} else {
		print "<font color=red>Error! ($error) </font>";
	}
	unlink($debugfile);
	print "\n\n";
}
    
?>
</pre>

<p>

If you see an error above complaining about reading or writing to
<b><?=$debugfile?></b>, then this is likely a permission/configuration
issue on your system.  If it mentions <i>open_basedir</i> then it's
because your system is configured with <a
href="http://www.php.net/manual/en/configuration.php#ini.open-basedir">
open_basedir</a> enabled.  You should talk to your system
administrator about this, or ask for help on the <a
href=http://gallery.sourceforge.net/lists.php>mailing list</a>

<p>

For other errors, please refer to the list of possible responses in <a
href=http://gallery.sf.net/faq.php>FAQ</a> 4.4 to get more
information.

</ol>


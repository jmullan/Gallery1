<?
$outfile = dirname(getenv("SCRIPT_FILENAME")) . "/../config.php";
copy("gpl.txt", $outfile);
if ($fd = @fopen($outfile, "a")) {
	fwrite($fd, urldecode($data));
	fclose($fd);
?>
<title> Success! </title>
<center>
<font size=+2> Your configuration has been successfully saved! </font>
<p>
<font size=+2 color=red>Note!</font> Don't forget to run:
<br>
<center>
<code>
% sh secure.sh
</code>
</center>
<br>
in your Gallery directory to make your application secure!  Gallery
won't run if it is not in secure mode.  You can reconfigure Gallery
at any time by re-running <code>configure.sh</code> and then returning
to the setup URL.

<p>
<center>
<a href=../albums.php>Enter the Gallery</a>

<?
} else {
?>
<title> Failure! </title>
<font size=+2> Unable to write to <?=$outfile?> </font>
<?
}
?>
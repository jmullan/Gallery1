<?php
	// This works only on Linux
	
	include (dirname(dirname(__FILE__)). '/Version.php');

	$path=dirname(__FILE__);
	shell_exec ("php $path/g1-report.php > $path/reports/$gallery->version--report.html");
	shell_exec ("rm $path/g1-report.html");
	shell_exec ("ln -s $path/reports/$gallery->version--report.html $path/g1-report.html");
	
?>

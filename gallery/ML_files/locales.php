<html>
        <header>
        <title>Locale Check</title>
        </header>
<body>

<?php
if (is_readable("/etc/locale.gen") {
	echo "<p>/etc/locale.gen</p>";
	$locale=shell_exec("cat /etc/locale.gen");
}
else {
	echo "<p>locale -a</p>";
	$locale=shell_exec("locale -a");
}
?>
<pre>
	<?php echo $locale; ?>
</pre>

</body>
</html>
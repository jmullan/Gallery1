<html>
<head>
  <title>Gallery Configuration Error</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>
<center>
<span class="error">
Gallery has not been configured!
<p>
To configure it, type:
	<table><tr><td>
		<code>
		% cd <?=dirname(getenv("SCRIPT_FILENAME"))?>
		<br>
		% sh ./configure.sh
	</td></tr></table>
<p>
And then go <a href="setup/index.php">here</a>
</span>
</body>
</html>

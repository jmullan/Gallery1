<html>
<head>
  <title>Gallery is misconfigured</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>	
<center>
<span class="error"> Uh oh! </span>
<p>
<center>
<table width=80%><tr><td>
Gallery is not configured correctly.  There could be a variety of reasons
for this.  The easiest way to fix this problem is to re-run the configuration
wizard.  In a shell do this:
<p><center>
<table><tr><td>
	<code>
	% cd <?=dirname(getenv("SCRIPT_FILENAME"))?>
	<br>
	% sh ./configure.sh
</td></tr></table>
<p>
Then launch the <a href=setup/index.php>configuration wizard</a>.
</table>

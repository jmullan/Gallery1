<html>
<head>
  <title>Gallery in Configuration Mode</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>	
<center>
<span class="error"> Uh oh! </span>
<p>
<table width=80%><tr><td>
Gallery is still in configuration mode which means it's
anybody out there can mess with it.  
For safety's sake we don't let you run the app in this mode.
You need to put it in secure mode before you can use it.  Put
it in secure mode by doing this:
	<p><center>
<table><tr><td>
	<code>
	% cd <?=dirname(getenv("SCRIPT_FILENAME"))?>
	<br>
	% sh ./secure.sh
</td></tr></table>
<p>
When you've done this, just reload this page and all should
be well.
</table>

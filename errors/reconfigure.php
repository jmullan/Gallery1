<html>
<head>
  <title>Gallery needs Re-Configuration</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>	
<center>
<span class="error"> Uh oh! </span>
<p>
<center>
<table width=80%><tr><td>
Your Gallery configuration was created using the config wizard
from an older version of Gallery.  It is out of date.  Please
re-run the configuration wizard!  In a shell do this:
<p><center>
<table><tr><td>
	<code>
	% cd <?=dirname(getenv("SCRIPT_FILENAME"))?>
	<br>
	% sh ./configure.sh
</td></tr></table>
<p>
Then launch the <a href=<?=$app->photoAlbumURL?>/setup/index.php>configuration wizard</a>.
</table>

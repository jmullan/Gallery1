<? $version = phpversion() ?>
<html>
<head>
  <title>PHP <?= $version ?> has a bug</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>	
<center>
<span class="error"> Uh oh! </span>
<p>
<center>
<table width=80%><tr><td>

There is a bug in your version of PHP (<?= $version ?>) that prevents
Gallery from saving and storing objects in its database.  This is a
known issue that was fixed in later versions of PHP.  This bug may
occur intermittently, so you won't always see this message.  To fix it, 
please upgrade PHP to at least version 4.0.4pl1.

</table>

<? require($GALLERY_BASEDIR . "errors/configure_instructions.php") ?>
<html>
<head>
  <title>Gallery Configuration Error</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>
<center>
<span class="title">
Gallery has not been configured!
</span>
<p>
To configure it, type:
<?= configure("configure.sh"); ?>
<p>
And then start the <a href="setup/index.php">Configuration Wizard</a>
</span>
</body>
</html>

<? require($GALLERY_BASEDIR . "errors/configure_instructions.php") ?>
<html>
<head>
  <title>Gallery is misconfigured</title>
  <?= getStyleSheetLink() ?>
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
<p>
<?= configure("configure"); ?>
<p>
Then launch the <a href="<?=$GALLERY_BASEDIR?>setup/index.php">configuration wizard</a>.
</table>

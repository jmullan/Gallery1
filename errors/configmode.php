<?
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
                    !empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
                    !empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
            print "Security violation\n";
	            exit;
		    }
?>
<? require($GALLERY_BASEDIR . "errors/configure_instructions.php") ?>
<html>
<head>
  <title>Gallery in Configuration Mode</title>
  <?= getStyleSheetLink() ?>
</head>
<body>	
<center>
<span class="title"> Gallery: Configuration Mode </span>
<p>
<table width=80%><tr><td>
<br>
<center>
To configure gallery, 
<font size=+1>
<a href="<?=$GALLERY_BASEDIR?>setup/index.php">Start the configuration wizard</a>
</font>
</center>
<br>

If you've finished your configuration but you're still seeing this
page, that's because for safety's sake we don't let you run Gallery in
an insecure mode.  You need to switch to secure mode before you can
use it.  Here's how:

<p><center>
<?= configure("secure"); ?>
<p>
Then just reload this page and all should be well.  

<? include($GALLERY_BASEDIR . "errors/configure_help.php"); ?>

</table>
</body>
</html>

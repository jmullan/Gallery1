<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
                    !empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
                    !empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
            print "Security violation\n";
	            exit;
		    }
?>
<?php require($GALLERY_BASEDIR . "errors/configure_instructions.php") ?>
<html>
<head>
  <title>Gallery Configuration Error</title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>
<center>
<span class="title">
Gallery has not been configured!
</span>
<p>
<center>
<table width=80%><tr><td>
Gallery must be configured before you can use it.  First, you must put it
into configuration mode.  Here's how:
<?php echo configure("configure"); ?>
<p>
And then start the <a href="<?php echo $GALLERY_BASEDIR?>setup/index.php">Configuration Wizard</a>

<?php include($GALLERY_BASEDIR . "errors/configure_help.php"); ?>
</table>
</body>
</html>

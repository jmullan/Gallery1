<?php /* $Id$ */ ?>
<?php

	$GALLERY_BASEDIR="../";
	require($GALLERY_BASEDIR . 'util.php');
	require($GALLERY_BASEDIR . 'setup/init.php');
	require($GALLERY_BASEDIR . 'setup/functions.inc');

	initLanguage();

	// We set this to false to get the config stylesheet
        $GALLERY_OK=false;

// Pull the $destroy variable into the global namespace
extract($HTTP_GET_VARS);

session_start();

// Pull the $count variable in also
foreach($HTTP_SESSION_VARS as $key => $value) {
    eval("\$$key =& \$HTTP_SESSION_VARS[\"$key\"];");
}
session_register("count");


if (isset($destroy)) {
    session_destroy();
    header("Location: session_test.php");
    exit;
}
$count++;
?>

<html>
<head>
	<title><?php echo _("Gallery Session Test") ?></title>
	<?php echo getStyleSheetLink() ?>
</head>

<body dir="<?php echo $gallery->direction ?>">
	<h1 class="header"><?php echo _("Session Test") ?></h1>
<?php
	if (getOS() == OS_WINDOWS) {
		if (fs_file_exists("SECURE")) {
		echo _("You cannot access this file while gallery is in secure mode.");
		echo "</body></html>";
		exit;
	    }
	}
?>
	<div class="sitedesc">
		<?php echo _("If sessions are configured properly in your PHP installation, then you should see a session id below.") ?>
	<br>
		<?php echo _("The &quot;page views&quot; number should increase every time you reload the page.") ?>
	<br>
		<?php echo sprintf(_("Clicking %s should reset the page view number back to 1."), '"Start over"') ?>
	<p>
		<?php echo _("If this <b>does not</b> work, then you most likely have a configuration issue with your PHP installation.") ?>   
		<?php echo _("Gallery will not work properly until PHP's session management is configured properly.") ?>  
	</p>
	</div>
	<table width="100%">
	<tr>
		<td>
		<table width="100%" class="inner">
		<tr>
			<td class="shortdesc"><?php echo _("Your session id is") ?></td>
			<td class="desc"><?php echo session_id() ?></td>
		</tr>
		<tr>
			<td class="shortdesc"><?php echo _("Page views in this session") ?></td>
			<td class="desc"><?php echo $count ?></td>
		</tr>
		<tr>
			<td class="shortdesc"><?php echo _("Server IP address") ?></td>
			<td class="desc"><?php echo $HTTP_SERVER_VARS["SERVER_ADDR"] ?></td>
		</tr>
		</table>
		</td>
	</tr>
	</table>
	
	<table width="100%" class="inner">
	<tr>
		<td class="desc" align="center"><a href="session_test.php?destroy=1"><?php echo _("Start over") ?></a>
      		<p><?php echo returnToConfig(); ?></p>
</body>
</html>

<?php /* $Id$ */ ?>
<?php

require ("../config.php") ;
// Pull the $destroy variable into the global namespace
extract($HTTP_GET_VARS);

session_start();

// Pull the $count variable in also
foreach($HTTP_SESSION_VARS as $key => $value) {
    eval("\$$key =& \$HTTP_SESSION_VARS[\"$key\"];");
}
session_register("count");


if ($destroy) {
    session_destroy();
    header("Location: session_test.php");
    exit;
}
$count++;
?>

  <html>
    <head>
      <title><?php echo _("Gallery Session Test") ?></title>
    </head>
    <body dir=<?php echo $gallery->direction ?>>
      <H1><?php echo _("Session Test") ?></H1>

	<?php echo _("If sessions are configured properly in your PHP installation, then you should see a session id below.") ?>  
	<?php echo _("The \"page views\" number should increase every time you reload the page.") ?>  
	<?php echo sprintf(_("Clicking %s should reset the page view number back to 1."), '"Start over"') ?>

      <p>

	<?php echo _("If this <b>does not</b> work, then you most likely have a configuration issue with your PHP installation.") ?>   
	<?php echo _("Gallery will not work properly until PHP's session management is configured properly.") ?>  

      <p>

      <table border=1>
	<tr>
	  <td>
	    <?php echo _("Your session id is") ?>
	  </td>
	  <td>
	    <?php echo session_id() ?> &nbsp;
	  </td>
	</tr>
	<tr>
	  <td>
	   <?php echo _("Page views in this session") ?>
	  </td>
	  <td>
	    <?php echo $count ?>
	  </td>
	</tr>
	<tr>
	  <td>
	   <?php echo _("Server IP address") ?>
	  </td>
	  <td>
	    <?php echo $HTTP_SERVER_VARS["SERVER_ADDR"] ?>
	  </td>
	</tr>
      </table>

      <a href="session_test.php?destroy=1"><?php echo _("Start over") ?></a>
      <p>
      <?php echo sprintf(_("Return to the %sDiagnostics Page%s"), 
		      '<a href="diagnostics.php">', '</a>') ?>
    </body>
  </html>

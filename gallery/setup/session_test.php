<?php /* $Id$ */ ?>
<?php

require ("../ML_files/ML_config.php") ;
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
	<br>
	<?php echo _("The \"page views\" number should increase every time you reload the page.") ?>
 	<br>
	<?php echo _("Clicking\"start over\" should reset the page view number back to 1.") ?>

      <p>

	<?php echo _("If this <b>does not</b> work, then you most likely have a configuration issue with your PHP installation.") ?>
	<br>
	<?php echo _(" Gallery will not work properly until PHP's session management is configured properly.") ?>

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
	    <?php echo $_ENV["SERVER_ADDR"] ?>
	  </td>
	</tr>
      </table>

      <a href="session_test.php?destroy=1"><?php echo _("Start over") ?></a>
      <p>
      <?php echo _("Return to the") ?> <a href="diagnostics.php"><?php echo _("Diagnostics Page") ?></a>
    </body>
  </html>

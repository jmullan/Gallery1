<?
session_start();
if ($destroy) {
    session_destroy();
    header("Location: session_test.php");
    exit;
}
session_register("count");
$count++;
?>

  <html>
    <head>
      <title> Gallery Session Test </title>
    </head>
    <body>
      <H1> Session Test </H1>

      If sessions are configured properly in your PHP installation,
      then you should see a session id below, and the "page views"
      number should increase every time you reload the page.  Clicking
      "start over" should reset the page view number back to 1.

      <p>

      If this <b>does not</b> work, then you most likely have a
      configuration issue with your PHP installation.  Gallery will
      not work properly until PHP's session management is configured
      properly.

      <p>

      <table border=1>
	<tr>
	  <td>
	    Your session id is
	  </td>
	  <td>
	    <?=session_id()?> &nbsp;
	  </td>
	</tr>
	<tr>
	  <td>
	    Page views in this session
	  </td>
	  <td>
	    <?=$count?>
	  </td>
	</tr>
      </table>

      <a href="session_test.php?destroy=1">Start over</a>
    </body>
  </html>

  <!-- $Id$ -->
  <html>
    <head>
      <title> Gallery Diagnostics Page </title>
    </head>
    <body>
      <center>
	<H1> Gallery Diagnostics </H1>
      </center>

      This page is designed to provide some diagnostics about your
      server to help you find issues that may prevent Gallery from
      functioning properly.  The config wizard tries all kinds of
      diagnostics to try to find and work around any issues that it
      finds on your system, but there may be other problems that we
      have not thought of.  You can use these tools to find out more
      about your setup:

      <p></p>

      <center>
	<table width=90% border=1>
	  <tr>
	    <th bgcolor=#99AACC> Tool </th>
	    <th bgcolor=#99AACC> Description </th>
	  </tr>
	  <tr>
	    <td width=140 align=center valign=top>
	      <a href=phpinfo.php>PHP Info</a>
	    </td>
	    <td>
	      This page provides information about your PHP
	      installation.  It's a good place to look to examine all
	      the various PHP configuration settings, and to find out
	      what kind of system you're running on (sometimes it's
	      difficult to tell when you're on an ISP's machine)
	    </td>
	  </tr>

	  <tr>
	    <td width=140 align=center valign=top>
	      <a href=check_netpbm.php>Check NetPBM</a>
	    </td>
	    <td>
	      This page provides information about your NetPBM
	      binaries.  You can only use this page after you have
	      successfully complete the configuration wizard (as it
	      expects that you've already located and configured
	      Gallery with the right path to NetPBM).
	    </td>
	  </tr>

	  <tr>
	    <td width=140 align=center valign=top>
	      <a href=check_imagemagick.php>Check ImageMagick</a>
	    </td>
	    <td>
	      This page provides information about your ImageMagick
	      binaries.  You can only use this page after you have
	      successfully complete the configuration wizard (as it
	      expects that you've already located and configured
	      Gallery with the right path to ImageMagick).
	    </td>
	  </tr>

	  <tr>
	    <td width=140 align=center valign=top>
	      <a href=session_test.php>Check Sessions</a>
	    </td>
	    <td>
	      This page runs a very simple test on your PHP session
	      configuration.  Gallery requires that your PHP
	      installation is configured with proper session support.
	    </td>
	  </tr>

	</table>

	<p> </p>

	<center>
	  Return to the <a href="index.php">config wizard</a>.
	</center>

    </body>
  </html>
  

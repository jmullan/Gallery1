<?php /* $Id: README.php 5431 2004-03-04 16:58:17Z jenst $ */ ?>
<html><head><title>How to create new frames</title></head>
<body bgcolor="#DDAAAA">
<!--

               TL  TTL     TT     TTR TR
                   +----------------+
               LLT |                | RRT
                   |                |
                   |                |
                LL |  IMAGE         | RR
                   |                |
                   |                |
               LLB |                | RRB
                   +----------------+
               BL  BBL     BB     BBR BR 
-->
Eventually this will contain full instructions for building your own files.  In the meantime, here's a quick diagram of where the diffent images go, and something to test<p>
<center>
<table border="1">
<tr>
	<td>TL</td>
	<td><table border="1"><tr>
		<td width="30">TTL</td>
		<td width="140" align="center">TT</td>
		<td width="30">TTR</td>
	</tr></table></td>
	<td>TR</td>
</tr>
<tr>
	<td><table border="1">
		<tr><td height="30">LLT</td></tr>
		<tr><td height="140">LL</td></tr>
		<tr><td height="30">LLB</td></tr>
	</table></td> 
	<td height="200" width="200" valign="center" align="center">IMAGE</td>
	<td><table border="1">
		<tr><td height="30">RRT</td></tr>
		<tr><td height="140">RR</td></tr>
		<tr><td height="30">RRB</td></tr>
	</table></td>
</tr>
<tr>
	<td>BL</td>
	<td><table border="1"><tr>
		<td width="30">BBL</td>
		<td width="140" align="center">BB</td>
		<td width="30">BBR</td>
	</tr></table></td>
	<td>BR</td>
</tr>
</table>

</center>

To test you frames, just insert the name of your frame were indicated below.
<?php
include (dirname(dirname(dirname(__FILE__))) . '/config.php');
include (dirname(dirname(dirname(__FILE__))) . '/util.php');

$gallery->html_wrap['frame'] = "polaroid"; /*** PUT YOUR FRAME DIR HERE ***/
$gallery->html_wrap['borderColor'] = "#AAAAFF";
$gallery->html_wrap['borderWidth'] = 0;
$gallery->html_wrap['imageWidth'] = 300;
$gallery->html_wrap['imageHeight'] = 200;
$gallery->html_wrap['imageHref'] = null;
$gallery->html_wrap['imageTag'] = '<img src="../../images/bar.gif" width="300" height="200">';
error_reporting(E_ALL);
?> 
<br><br><br>
<center>
<?php include "../inline_imagewrap.inc"; ?> 
<br><BR>
<?php print "Your frame is called <b>$name</b> and you've described it as <b>$description</b>" ?>
</center>
</body>
</html>

<?
/*######################################################## 
## Mod Title:    Gallery - phpBB2 Integration 
## Mod Version:  1.4.3
##
## $Date$
## $Revision$
########################################################*/


// INSTALL SQL changes for Gallery->PhpBB2 Integration


// ******************* NO EDITING BELOW THIS LINE *******************

$ver = '1.4.1';

// connect to phpbb
include('./config.php');

$cnx = mysql_connect($dbhost, $dbuser, $dbpasswd)
		or die("Unable to connect to database server.");
mysql_select_db($dbname, $cnx)
		or die("Unable to select database.");


$sql = "ALTER TABLE phpbb_users ADD user_gallery_perm tinyint(1) unsigned NOT NULL default '0'";
$sql1 =	"ALTER TABLE phpbb_groups ADD group_gallery_perm tinyint(1) unsigned NOT NULL default '0'";

$result = (mysql_query($sql) && mysql_query($sql1));

echo "<HTML><HEAD><TITLE>Installing: Gallery SQL to version ".$calver."</TITLE></HEAD>";
echo "<BODY>";

if(!$result)
	echo "Install failed<BR><BR>";
	if (mysql_error()) { echo "Error Report: <B>".mysql_error()."</B><BR><BR>"; }
else {
	echo "Install complete, new columns added.<BR><BR><BR>Please remember to delete this file ASAP.";
	echo "<center><img src='http://www.snailsource.com/files/gallery".$ver."__5166.jpeg'></center>";
	} 
echo "<BODY></HTML>";
exit;
?>


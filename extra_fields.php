<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
 *
 * This file created by Joan McGalliard, Copyright 2003
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = '';
}
require($GALLERY_BASEDIR . "init.php"); ?>
<?php
// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}

$special_fields=array("Title", "Upload Date", "Capture Date");
	
if ($save) {
	$count=0;
	if (!$extra_fields)
	{
		$extra_fields = array();
	}
	$num_fields=$num_user_fields+num_special_fields($extra_fields);
	$gallery->album->setExtraFields($extra_fields);
	if ($num_fields > 0 && !$gallery->album->getExtraFields())
	{
		$gallery->album->setExtraFields(array());
	}
	if (sizeof ($gallery->album->getExtraFields()) < $num_fields)
	{
		$gallery->album->setExtraFields( array_pad(
			$gallery->album->getExtraFields(), $num_fields, 
			"untitled field"));
	}
	if (sizeof ($gallery->album->getExtraFields()) > $num_fields)
	{
		$gallery->album->setExtraFields(
			array_slice($gallery->album->getExtraFields(), 
			0, $num_fields));
	}
	if ($setNested) 
	{
		$gallery->album->setNestedExtraFields();
	}
	$gallery->album->save();

	reload();
}

?>
<html>
<head>
  <title>Configure Extra Fields</title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

<center>
Configure Extra Fields

<p>
<?php echo makeFormIntro("extra_fields.php", 
			array("name" => "theform", 
				"method" => "POST")); ?>
<input type=hidden name="save" value=1>

Number of user defined extra fields
<?php $num_user_fields=sizeof($gallery->album->getExtraFields()) -
	num_special_fields($gallery->album->getExtraFields()); ?>
<input type=text size=4 name="num_user_fields" value="<?php echo $num_user_fields ?>">
<table>

<?php
$extra_fields=$gallery->album->getExtraFields();
foreach ($special_fields as $special_field)
{
?>
	<tr><td><?php print $special_field ?></td><td><input type=checkbox 
	name="extra_fields[]"
	value="<?php print $special_field ?>"
	<?php print in_array($special_field, $extra_fields) ?  "checked" : ""; 
	?> > </td></tr>
<?php
}
?>
<tr></tr>
<?php
$i=0;

foreach ($extra_fields as $value)
{
	if (in_array($value, $special_fields))
		continue;
	print "<tr><td>Field".($i+1).": </td><td>";
	print "<input type=text name=\"extra_fields[]\"";
        print "value=\"".$value."\"><p></td></tr>";
	$i++;
}

function num_special_fields($extra_fields)
{
	global $special_fields;
	$num_special_fields=0;
	foreach ($special_fields as $special_field) {
		if (in_array($special_field, $extra_fields))
			$num_special_fields++;
	}
	return $num_special_fields;
}
?>
</table>
<input type=checkbox name=setNested value="1">Apply to nested Albums.
<p>
<input type=submit name="submit" value="Apply">
<input type=reset value="Undo">
<input type=submit name="submit" value="Close" onclick='parent.close()'>

</form>
</body>
</html>


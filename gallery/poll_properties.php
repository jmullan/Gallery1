<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
 *
 * $Id$
 */
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation")."\n";
	exit;
}
?>
<?php 
if (!isset($GALLERY_BASEDIR)) {
       	$GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . "init.php"); 

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
	
$error="";
if (!empty($apply)) {
	for ($i=0; $i<$gallery->album->getPollScale() ; $i++)
	{
		//convert values to numbers
		$nv_pairs[$i]["value"]=0+$nv_pairs[$i]["value"];
	}
	$gallery->album->fields["poll_nv_pairs"]=$nv_pairs;
	$gallery->album->fields["poll_hint"]=$poll_hint;
	$gallery->album->fields["poll_type"] = $poll_type;
	if ($voter_class == "Logged in" &&
	    $gallery->album->fields["voter_class"] == "Everybody" &&
	    sizeof($gallery->album->fields["votes"]) > 0)
	{
		$error="<br>" .
			sprintf(_("Warning: you have changed voters from %s to %s.  It is advisable to reset the poll to remove all previous votes."),
					"<i>". _("Everybody") ."</i>",
					"<i>". _("Logged in") ."</i>");
	}
	$gallery->album->fields["voter_class"] = $voter_class;
	$gallery->album->fields["poll_scale"] = $poll_scale;
	$gallery->album->fields["poll_show_results"] = $poll_show_results;
	$gallery->album->fields["poll_num_results"] = $poll_num_results;
	$gallery->album->fields["poll_orientation"] = $poll_orientation;
	$gallery->album->save(array(i18n("Poll properties change")));

	if (isset($setNested)) {
		$gallery->album->setNestedPollProperties();
       	}
	reload();
}

?>
<html>
<head>
  <title><?php echo _("Poll Properties") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

<center>
<?php echo _("Poll Properties"); ?>

<span class="error"><?php echo $error; ?></span>
<?php echo makeFormIntro("poll_properties.php", 
			array("name" => "theform", 
				"method" => "POST")); ?>
<table>
<tr>
<td><?php echo _("Type of poll for this album") ?></td>
<td><select name="poll_type"><?php selectOptions($gallery->album, "poll_type", array("rank" => _("Rank"), "critique" => _("Critique"))) ?></select></td>
</tr>
<tr>
<td><?php echo _("Number of voting options") ?></td>
<td><input type=text name="poll_scale" value="<?php echo $gallery->album->getPollScale() ?>"></td>
</tr>
<tr>
<td><?php echo _("Show results of voting to all visitors?") ?></td>
<td><select name="poll_show_results"><?php selectOptions($gallery->album, "poll_show_results", array("no" => _("no"), "yes" => _("yes"))) ?></select></td>
</tr>
<tr>
<td><?php echo _("Number of lines of results graph to display on the album page") ?></td>
<td><input type=text name="poll_num_results" value="<?php echo $gallery->album->getPollNumResults() ?>"></td>
</tr>
<tr>
<td><?php echo _("Who can vote") ?></td>
<td><select name="voter_class"><?php selectOptions($gallery->album, "voter_class", array("Logged in" => _("Logged in"), "Everybody" => _("Everybody"), "Nobody" => _("Nobody"))) ?></select></td>
</tr>
<tr>
<td><?php echo _("Orientation of vote choices") ?></td>
<td><select name="poll_orientation"><?php selectOptions($gallery->album, "poll_orientation", array("horizontal" => _("Horizontal"), "vertical" => _("Vertical"))) ?></select></td>
</tr>
<tr>
<td><?php echo _("Vote hint") ?></td>
<td><input type=text name="poll_hint" value="<?php echo $gallery->album->getPollHint() ?>"></td>
</tr>
</table>

<table>
<tr><td><?php echo _("Displayed Value") ?></td><td>Points</td></tr>
<?php
$nv_pairs=$gallery->album->getVoteNVPairs();
for ($i=0; $i<$gallery->album->getPollScale() ; $i++)
{
?>
	<tr>
	<td><input type=text name=<?php print "\"nv_pairs[$i][name]\"";
	print "value=\"".$nv_pairs[$i]["name"]."\"></td>\n";
	?>
	<td><input type=text name=<?php print "\"nv_pairs[$i][value]\"";
	print "value=".$nv_pairs[$i]["value"]."></td>\n";
	?>
	</tr>
<?php
}
?>
<table>
<br>
<input type="checkbox" name="setNested" value="1"><span class="popup"><?php echo _("Apply values to nested Albums.") ?></span>
<br>
<br>

<input type=submit name="apply" value="<?php echo _("Apply") ?>">
<input type=reset value="<?php echo _("Undo") ?>">
<input type=submit value="<?php echo _("Close") ?>" onclick='parent.close()'>

</form>
</body>
</html>


<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
 *
 * This file Copyright (C) 2003-2004 Joan McGalliard
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

require(dirname(__FILE__) . '/init.php');

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	echo _("You are no allowed to perform this action !");
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

doctype();
?>
<html>
<head>
  <title><?php echo _("Poll Properties") ?></title>
  <?php common_header(); ?>
</head>
<body>

<center>
<p class="popuphead"><?php echo _("Poll Properties"); ?></p>
<?php
if (! empty($error)) {
	echo "<\p>". gallery_error($error) . "</p>";
}
	echo makeFormIntro("poll_properties.php", 
			array("name" => "theform", 
				"method" => "POST")); ?>
<table border="0">
<tr>
	<td><?php echo _("Type of poll for this album") ?></td>
	<td><select name="poll_type"><?php selectOptions($gallery->album, "poll_type", array("rank" => _("Rank"), "critique" => _("Critique"))) ?></select></td>
</tr>
<tr>
	<td><?php echo _("Number of voting options") ?></td>
	<td><input type="text" name="poll_scale" value="<?php echo $gallery->album->getPollScale() ?>"></td>
</tr>
<tr>
	<td><?php echo _("Show results of voting to all visitors?") ?></td>
	<td><select name="poll_show_results"><?php selectOptions($gallery->album, "poll_show_results", array("no" => _("no"), "yes" => _("yes"))) ?></select></td>
</tr>
<tr>
	<td width="50%"><?php echo _("Number of lines of results graph to display on the album page") ?></td>
	<td><input type="text" name="poll_num_results" value="<?php echo $gallery->album->getPollNumResults() ?>"></td>
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
	<td><input type="text" name="poll_hint" value="<?php echo $gallery->album->getPollHint() ?>" size="45"></td>
</tr>
</table>

<br>

<table border="0">
<tr>
	<td><?php echo _("Displayed Value") ?></td>
	<td>Points</td>
</tr>
<?php
$nv_pairs=$gallery->album->getVoteNVPairs();
for ($i=0; $i<$gallery->album->getPollScale() ; $i++) {
?>
<tr>
	<td><input type="text" name="nv_pairs[<?php echo $i?>][name]" value="<?php echo $nv_pairs[$i]["name"] ?>"></td>
	<td><input type=text name="nv_pairs[<?php echo $i?>][value]" value="<?php echo $nv_pairs[$i]["value"] ?>"></td>
</tr>
<?php
}
?>
</table>

<p>
<input type="checkbox" name="setNested" value="1" class="popup"><?php echo _("Apply values to nested Albums.") ?>
</p>

<input type="submit" name="apply" value="<?php echo _("Apply") ?>">
<input type="reset" value="<?php echo _("Undo") ?>">
<input type="submit" value="<?php echo _("Close") ?>" onclick='parent.close()'>

</form>
</center>
<?php print gallery_validation_link("poll_properties.php"); ?>
</body>
</html>


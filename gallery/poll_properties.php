<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */

require_once(dirname(__FILE__) . '/init.php');

list($apply, $nv_pairs, $voter_class, $poll_scale, $poll_show_results, $poll_num_results, $poll_orientation, $poll_hint, $poll_type) =
  getRequestVar(array('apply', 'nv_pairs', 'voter_class', 'poll_scale', 'poll_show_results', 'poll_num_results', 'poll_orientation', 'poll_hint', 'poll_type'));

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

$error = '';
if (!empty($apply)) {
	for ($i = 0; $i < $gallery->album->getPollScale() ; $i++) {
		//convert values to numbers
		$nv_pairs[$i]["value"] = 0+$nv_pairs[$i]["value"];
	}
	$gallery->album->fields["poll_nv_pairs"]=$nv_pairs;
	$gallery->album->fields["poll_hint"]=$poll_hint;
	$gallery->album->fields["poll_type"] = $poll_type;
	if ($voter_class == "Logged in" &&
	    $gallery->album->fields["voter_class"] == "Everybody" &&
	    sizeof($gallery->album->fields["votes"]) > 0) {
		$error = "<br>" .
			sprintf(gTranslate('core', "Warning: you have changed voters from %s to %s.  It is advisable to reset the poll to remove all previous votes."),
					"<i>". gTranslate('core', "Everybody") ."</i>",
					"<i>". gTranslate('core', "Logged in") ."</i>");
	}
	$gallery->album->fields["voter_class"] = $voter_class;
	$gallery->album->fields["poll_scale"] = $poll_scale;
	$gallery->album->fields["poll_show_results"] = $poll_show_results;
	$gallery->album->fields["poll_num_results"] = $poll_num_results;
	$gallery->album->fields["poll_orientation"] = $poll_orientation;
	$gallery->album->save(array(i18n("Poll properties change")));

	if (getRequestVar('setNested')) {
	    $gallery->album->setNestedPollProperties();
	}
	reload();
}

doctype();
?>
<html>
<head>
  <title><?php echo gTranslate('core', "Poll Properties") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo gTranslate('core', "Poll Properties"); ?></div>
<div class="popup" align="center">
<?php
if (! empty($error)) {
	echo "<p>". gallery_error($error) . "</p>";
}
	echo makeFormIntro("poll_properties.php",
			array("name" => "theform"),
			array("type" => "popup")); ?>
<table border="0">
<tr>
	<td><?php echo gTranslate('core', "Type of poll for this album") ?></td>
	<td><select name="poll_type"><?php selectOptions($gallery->album, "poll_type", array("rank" => gTranslate('core', "Rank"), "critique" => gTranslate('core', "Critique"))) ?></select></td>
</tr>
<tr>
	<td><?php echo gTranslate('core', "Number of voting options") ?></td>
	<td><input type="text" name="poll_scale" value="<?php echo $gallery->album->getPollScale() ?>"></td>
</tr>
<tr>
	<td><?php echo gTranslate('core', "Show results of voting to all visitors?") ?></td>
	<td><select name="poll_show_results"><?php selectOptions($gallery->album, "poll_show_results", array("no" => gTranslate('core', "No"), "yes" => gTranslate('core', "Yes"))) ?></select></td>
</tr>
<tr>
	<td width="50%"><?php echo gTranslate('core', "Number of lines of results graph to display on the album page") ?></td>
	<td><input type="text" name="poll_num_results" value="<?php echo $gallery->album->getPollNumResults() ?>"></td>
</tr>
<tr>
	<td><?php echo gTranslate('core', "Who can vote") ?></td>
	<td><select name="voter_class"><?php selectOptions($gallery->album, "voter_class", array("Logged in" => gTranslate('core', "Logged in"), "Everybody" => gTranslate('core', "Everybody"), "Nobody" => gTranslate('core', "Nobody"))) ?></select></td>
</tr>
<tr>
	<td><?php echo gTranslate('core', "Orientation of vote choices") ?></td>
	<td><select name="poll_orientation"><?php selectOptions($gallery->album, "poll_orientation", array("horizontal" => gTranslate('core', "Horizontal"), "vertical" => gTranslate('core', "Vertical"))) ?></select></td>
</tr>
<tr>
	<td><?php echo gTranslate('core', "Vote hint") ?></td>
	<td><input type="text" name="poll_hint" value="<?php echo $gallery->album->getPollHint() ?>" size="45"></td>
</tr>
</table>

<br>

<table border="0">
<tr>
	<td><?php echo gTranslate('core', "Displayed Value"); ?></td>
	<td><?php echo gTranslate('core', "Points"); ?></td>
</tr>
<?php
$nv_pairs=$gallery->album->getVoteNVPairs();
for ($i=0; $i<$gallery->album->getPollScale() ; $i++) {
?>
<tr>
	<td><input type="text" name="nv_pairs[<?php echo $i?>][name]" value="<?php echo $nv_pairs[$i]["name"] ?>"></td>
	<td><input type="text" name="nv_pairs[<?php echo $i?>][value]" value="<?php echo $nv_pairs[$i]["value"] ?>"></td>
</tr>
<?php
}
?>
</table>

<p>
<input type="checkbox" name="setNested" value="1" class="popup"><?php echo gTranslate('core', "Apply values to nested albums.") ?>
</p>

<?php echo gSubmit('apply', gTranslate('core', "Apply")); ?>
<?php echo gReset('reset', gTranslate('core', "Undo")); ?>
<?php echo gButton('close', gTranslate('core', "Close"), 'parent.close()'); ?>

</form>
</div>
<?php print gallery_validation_link("poll_properties.php"); ?>
</body>
</html>


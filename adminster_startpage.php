<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 *
 */
?>
<?php

if (!isset($gallery->version)) {
        require_once(dirname(__FILE__) . '/init.php');
}

if (!$gallery->user->isAdmin()) {
        echo _("You are not allowed to perform this action!");
        exit;
}

list($sort, $order, $fieldname) = getRequestVar(array('sort', 'order', 'fieldname'));

$adminOptions[] = array( 'text' => _("Rebuild highlights"),
			 'url' =>  doCommand('rebuild_highlights'),
			 'longtext' => _("rebuild highlights"));

$adminOptions[] = array( 'text' => _("Sort albumorder"),
			 'url' => makeGalleryUrl('adminster_startpage.php', array('sort' => 1)),
			 'longtext' => _("Sort albumorder"));

array_sort_by_fields($adminOptions, 'text', 'asc');

doctype();
printPopupStart(_("Adminster Startpage"));

if(empty($sort)) {
    echo "\n<table width=\"100%\">";
    foreach ($adminOptions as $option) {
	echo "\n<tr>";
	if (isset($option['url'])) {
		$link = '<a class="admin" href="'. $option['url'] .'">'. $option['text'] .'</a>';
	} else {
		$link = popup_link($option['text'], $option['popupFile'], false, true, 500, 500, 'admin');
	}
	echo "\n<td class=\"adm_options\">$link</td>";
	echo "\n<td class=\"adm_options\">". $option['longtext'] ."</td>";
	echo "\n</tr>";
    }
    echo "\n</table>";
}
elseif (empty($order)) {
echo makeFormIntro('adminster_startpage.php');
?>
<table>
  <caption><?php echo _("Sort albums on startpage"); ?></caption>
  <tr>
    <td><input checked type="radio" name="fieldname" value="name"><?php echo _("By (physical) name") ?></td>
  </tr>
  <tr>
    <td align="center">
<?php echo _("Sort Order:"); ?>
    <select name="order">
        <option value="asc"><?php echo _("Ascending") ?></option>
        <option value="desc"><?php echo _("Descending") ?></option>
    </select>
    </td>
  </tr>
</table>
<br><br>
<input type="hidden" name="sort" value="1">
<input type="submit" name="confirm" value="<?php echo _("Sort") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<?php
}
else {
    /* Read the album list */
    $albumDB = new AlbumDB(FALSE);
    $albumDB->sortByField($fieldname, $order);
    dismissAndReload();
?>
    <input type="button" name="cancel" value="<?php echo _("Close Window") ?>" onclick='parent.close()'>
<?php
}
?>
</div>
</body>
</html>

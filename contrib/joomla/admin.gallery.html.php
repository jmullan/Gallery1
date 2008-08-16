<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
 * Gallery Component for Joomla 1.0.x based on the mambo component.
 * Original author: Beckett Madden-Woods <beckett@beckettmw.com>
 *
 * $Id$
 */

class HTML_content {

	function showSettings($option, $params, $act) {
?>

<table cellpadding="4" cellspacing="0" border="0" width="100%">
		<tr>
			<td width="228"><a target="_blank" href="http://gallery.sourceforge.net/"><img src="components/com_gallery/images/logo-228x67.png" border="0" width="228" height="67" align="middle" /></a></td>
			<td align="left" class="sectionname" style="margin-left: 10px;">Gallery Component Settings</td>
		</tr>
</table>

<script language="javascript" src="js/dhtml.js"></script>
<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			submitform(pressbutton);
		}
</script>
<form action="index2.php" method="post" name="adminForm">
    <table cellpadding="2" cellspacing="4" width="100%" class="adminlist">
	  <tr>
	  	<th width="150">Setting</th>
	  	<th>Value</th>
	  	<th width="50%">Additional info</th>
	  </tr>
      <tr>
        <td>Security Check</td>
		<td><input class="inputbox" type="text" name="path" size="70" value="<?php echo $params['path']; ?>"></td>
      	<td class="error">e.g. /home/user1/public_html/gallery</td>
      </tr>
      <tr>
        <td>Admin Level:</td>
        <td><?php echo $params['minAuthType']; ?></td>
      	<td class="error">Minimum authority level needed for <i>admin</i> privileges in Gallery.</td>
      </tr>
      <tr>
        <td>Level for creating root albums:</td>
        <td><?php echo $params['minAuthAlbums']; ?></td>
      	<td class="error">Minimum authority level needed for creating root albums in your Gallery.</td>
      </tr>
      <tr>
      	<td>Hide Right-hand modules:</td>
        <td><?php echo $params['hideRightSide']; ?></td>
      	<td class="error">Hiding right-hand modules gives Gallery more room.</td>
      </tr>
    </table>
  <input type="hidden" name="option" value="<?php echo $option; ?>">
  <input type="hidden" name="act" value="<?php echo $act; ?>">
  <input type="hidden" name="task" value="">
</form>

<?php
	}
}
?>

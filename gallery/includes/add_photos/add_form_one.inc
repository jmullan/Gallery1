<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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

<p>

<span class="popup">
<?php echo _("Click the <b>Browse</b> button to locate a photo to upload.") ?>
</span>
<span class="admin">
<br>
&nbsp;&nbsp;(<?php echo _("Supported file types") ?>: <?php echo join(", ", acceptableFormatList()) ?>)
</span>

<br><br>

<?php echo makeFormIntro("add_photo.php",
			array("name" => "upload_form",
				"enctype" => "multipart/form-data",
				"method" => "POST")); ?>
<input type="hidden" name="max_file_size" value="10000000">
<table>
<tr><td>
<?php echo _("File") ?></td>
<td><input name="userfile" type="file" size=40></td></tr>
<td><?php echo _("Caption") ?></td><td> <textarea name="caption" rows=2 cols=40></textarea></td></tr>
<?php
foreach ($gallery->album->getExtraFields() as $field) {
        if (in_array($field, array_keys(automaticFieldsList())))
        {
                continue;
        }
        if ($field == "Title")
        {
        	print "<tr><td valign=top>Title</td><td>";
                print "<input type=text name=\"extra_fields[$field]\" value=\"\" size=\"40\">";
        }
	else
	{
        	print "<tr><td valign=top>$field</td><td>";
        	print "<textarea name=\"extra_fields[$field]\" rows=2 cols=40>";
        	print "</textarea>";
	}
        print "</td></tr>";
}
?>

</table>
<input type="checkbox" name="setCaption" checked value="1"><?php echo _("Use filename as caption if no caption is specified.") ?>
<br>
<center>
<input type="submit" value="<?php echo _("Upload Now") ?>">
<input type="button" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</center>
</form>

</p>
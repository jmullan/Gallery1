<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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

<?php echo makeFormIntro("save_photos.php",
			array("name" => "uploadurl_form",
				"method" => "POST")); ?>
<?php echo _("Or, upload any images found at this location.") ?>
<?php echo _("The location can either be a URL or a directory on the server.") ?>
<br>

&nbsp;&nbsp;<?php echo _("Tip: FTP images to a directory on your server then provide that path here!") ?>
<br>

<input type="text" name="urls[]" size=40>
<br>
<input type="checkbox" name="setCaption" checked value="1"><?php echo _("Set photo captions with original filenames.") ?>
<br>
<center>
<input type="button" value="<?php echo _("Submit URL or directory") ?>" onClick='parent.opener.showProgress(); document.uploadurl_form.submit()'>
<input type=submit value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</center>
</form>

</p>
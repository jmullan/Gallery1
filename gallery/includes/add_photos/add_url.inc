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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>

<p>
<?php
	echo _("Upload any images found at this location.");
	echo _("The location can either be a URL or a local directory on the server.");
?>
<br>
<?php 
	echo '<p><b>'. _("Tip:") . '</b> ';
	echo _("FTP images to a directory on your server then provide that path here!");
?>
</p>

<?php 
	echo makeFormIntro("save_photos.php",
			array("name" => "uploadurl_form",
				"method" => "POST"), array('type' => 'popup'));
?>
<input type="text" name="urls[]" size="40">
<p>
<?php
	echo _("What would you like the captions to default to?");
	 echo "\n<br><br>";
	include(dirname(__FILE__) .'/captionOptions.inc.php');
?>
</p>
<?php
if (isset($gallery->app->watermarkDir)) {
   global $watermarkForm;
   $watermarkForm["askRecursive"] = 0;
   $watermarkForm["askPreview"] = 0;
   $watermarkForm["allowNone"] = 1;
   includeLayout('watermarkform.inc');
}
?>
<p align="center">
<input type=submit value="<?php echo _("Submit URL or directory") ?>" onClick='parent.opener.showProgress(); document.uploadurl_form.submit()'>
<input type=submit value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</p>
</form>

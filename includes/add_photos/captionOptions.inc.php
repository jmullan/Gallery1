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
<input type="radio" name="setCaption" value="0" id="setCaption0"><label for="setCaption0"><?php echo _("Leave blank.") ?></label>
<br>
<input type="radio" name="setCaption" value="1" id="setCaption1" checked><label for="setCaption1"><?php echo _("Use filename as caption.") ?></label>
<br>
<input type="radio" name="setCaption" value="2" id="setCaption2"><label for="setCaption2"><?php echo _("Use file creation date stamp.") ?></label>
<br>
<?php
if (isset($gallery->app->use_exif)) {
        echo '<input type="radio" name="setCaption" value="3" id="setCaption3">';
	echo '<label for="setCaption3">';
        echo _("Set photo captions with file capture times.");
	echo '</label>';
}

echo "\n<br><br>";
echo _("For the last two options Gallery will use the format you specified in config for date/time strings.");
?>

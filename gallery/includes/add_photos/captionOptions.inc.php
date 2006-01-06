<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
?>
<input type="radio" name="setCaption" value="0" id="setCaption0"><label for="setCaption0"><?php echo gTranslate('core', "Leave blank.") ?></label>
<br>
<input type="radio" name="setCaption" value="1" id="setCaption1" checked><label for="setCaption1"><?php echo gTranslate('core', "Use filename as caption.") ?></label>
<br>
<input type="radio" name="setCaption" value="2" id="setCaption2"><label for="setCaption2"><?php echo gTranslate('core', "Use file creation date/time stamp.") ?></label>
<br>
<?php
if (isset($gallery->app->use_exif)) {
        echo '<input type="radio" name="setCaption" value="3" id="setCaption3">';
	echo '<label for="setCaption3">';
        echo gTranslate('core', "Set photo captions with file capture times.");
	echo '</label>';
}

echo "\n<br><br>";
echo gTranslate('core', "For the last two options Gallery will use the format you specified in the config for date/time strings.");
?>

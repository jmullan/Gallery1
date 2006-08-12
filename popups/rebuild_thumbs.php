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
 * $Id: do_command.php 13778 2006-06-08 17:51:08Z jenst $
 */
?>
<?php
require_once(dirname(dirname(__FILE__)) . '/init.php');

$recursive = getRequestVar('recursive');

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
        echo gTranslate('core', "You are not allowed to perform this action!");
        exit;
}

printPopupStart(sprintf(gTranslate('core', "Rebuilding Thumbnails: %s"), $gallery->album->fields["title"]), '', 'center');

if ($recursive == 'no') {
    $np = $gallery->album->numPhotos(1);
    echo "\n<h3>" . sprintf(gTranslate('core', "Rebuilding %d thumbnails..."), $np) .'</h3>';
    my_flush();
    for ($i = 1; $i <= $np; $i++) {
	echo "\n<h4>". sprintf(gTranslate('core', "Processing image %d..."), $i) .'</h4>';
	my_flush();
	set_time_limit($gallery->app->timeLimit);
	$gallery->album->makeThumbnail($i);
    }
    echo "\n<hr width=\"100%\">";

   $gallery->album->save();
   echo '<script type="text/javascript">opener.location.reload();</script>';
}
else if ($recursive == 'yes') {
    $index = 'all';
    $gallery->album->makeThumbnailRecursive($index);
    $gallery->album->save();
    echo '<script type="text/javascript">opener.location.reload();</script>';
}
    	
    echo gTranslate('core', "Do you also want to rebuild the thumbnails in subalbums?");
    echo "\n<br>";
    
    echo makeFormIntro('rebuild_thumbs.php', array(), array('type' => 'popup'));
?>   
	<input type="radio" name="recursive" value="yes"> <?php echo gTranslate('core', "Yes"); ?>
	<input type="radio" name="recursive" value="no" checked> <?php echo gTranslate('core', "No"); ?>
    <br><br>
    <?php 

echo gSubmit('recreate', empty($recreate_type) ? gTranslate('core', "_Start") : gTranslate('core', "_Start over"));
echo gButton('close', gTranslate('core', "_Close"), 'parent.close()');
?>
      </form>
</div>
</body>
</html>

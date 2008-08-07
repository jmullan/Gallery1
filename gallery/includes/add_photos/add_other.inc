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
 * $Id$
 */

/**
 * Writes infos about other methods to add items to Gallery 1.
 *
 * @package Add_Items
 */

?>
<br><br>
<?php echo gTranslate('core', "You can use one of these desktop agents to drag and drop photos from your desktop") ?>:
<br>

<ul>
  <li>
	<b><a href="#" onClick="parent.opener.location = 'http://gallery.sourceforge.net/gallery_remote.php'; parent.close();">Gallery Remote</a></b>
	<br>
	<?php echo gTranslate('core', "A Java application that runs on Mac, Windows and Unix") ?>
	<br><br>
  </li>

  <li>
	<b><a href="#" onClick="parent.opener.location = 'http://codex.gallery2.org/Other_Clients'; parent.close();"><?php echo gTranslate('core', "Other methods") ?></a></b>
	<br>
	<?php echo gTranslate('core', "See a couple of other Methods for adding pictures to your Gallery on Gallerys Website.") ?>
	<br><br>
  </li>

<?php if (empty($GALLERY_EMBEDDED_INSIDE) || $GALLERY_EMBEDDED_INSIDE_TYPE='GeekLog') { ?>
  <li>
	<b><a target="_parent" href="<?php echo makeGalleryUrl("publish_xp_docs.php") ?>"><?php echo gTranslate('core', "Windows XP Publishing Agent") ?></a></b>
  </li>
<?php } ?>
</ul>
</p>
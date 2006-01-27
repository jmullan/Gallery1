<?php
/**
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
<p>
<?php echo _("You can use one of these desktop agents to drag and drop photos from your desktop") ?>:
<br>

<ul>
	<li><b><a href="#" onClick="parent.opener.location = 'http://gallery.sourceforge.net/gallery_remote.php'; parent.close();">Gallery Remote</a></b></li>
	<dt><?php echo _("A Java application that runs on Mac, Windows and Unix") ?></dt>

<br>
	<li><b><a href="#" onClick="parent.opener.location = 'http://gallery.sourceforge.net/wiki.php?page=Other%20methods'; parent.close();"><?php echo _("Other methods") ?></a></b></li>
	<dt><?php echo _("See a couple of other Methods for adding pictures to your Gallery on Gallerys Website.") ?></dt>

<?php if (empty($GALLERY_EMBEDDED_INSIDE) || $GALLERY_EMBEDDED_INSIDE_TYPE='GeekLog') { ?>
<br>
	<li><b><a href="<?php echo makeGalleryUrl("publish_xp_docs.php") ?>"><?php echo _("Windows XP Publishing Agent") ?></a></b></li>
	<dt><?php echo _("<i>Note:</i> this feature is still experimental!") ?></dt>
<?php } ?>					 
</ul>
</p>
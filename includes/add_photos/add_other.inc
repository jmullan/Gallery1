<?php

/**
 * <?php
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
<?php echo _("You can use one of these desktop agents to drag and drop photos from your desktop") ?>:
<br>
&nbsp;&nbsp;&nbsp;<b><a href="#" onClick="parent.opener.location = 'http://gallery.menalto.com/modules.php?op=modload&name=Downloads&file=index&req=getit&lid=20'; parent.close();">Gallery Remote</a></b>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo _("A Java application that runs on Mac, Windows and Unix") ?>
<br>
&nbsp;&nbsp;&nbsp;<b><a href="#" onClick="parent.opener.location = 'http://gallery.menalto.com/modules.php?op=modload&name=phpWiki&file=index&pagename=Other%20methods'; parent.close();"><?php echo _("Other methods") ?></a></b>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo _("Methods for adding pictures to your Gallery") ?>
<br>
<?php if (empty($GALLERY_EMBEDDED_INSIDE)) { ?>
&nbsp;&nbsp;&nbsp;<b><a href="<?php echo makeGalleryUrl("publish_xp_docs.php") ?>"><?php echo _("Windows XP Publishing Agent") ?></a></b>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo _("<i>Note:</i> this feature is still experimental!") ?>
<?php } ?>					 
</p>
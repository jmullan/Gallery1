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
<?php

require(dirname(__FILE__) . '/init.php');
?>

<html>
<head>
  <title><?php echo _("Add Photos") ?></title>
</head>
<frameset rows="100%, 0%" border=0 frameborder=0 framespacing=0>
  <frame src="<?php echo makeGalleryUrl('add_photos.php', array('set_albumName' => $gallery->session->albumName)); ?>" noresize />
  <frame name="hack" src="" noresize noscroll />
</frameset>

<!-- This is a hack that's needed for the GR applet to be able to
call add_photos_refresh.php without a big ugly page popping up:
we hide the page inside the second (hidden) frame in this set. -->
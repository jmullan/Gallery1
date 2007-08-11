<?php

/**
 * Gallery SVN ID:
 * $Id$
*/

   global $gallery, $albumDB, $index;
   $uptodate=true;
   $albumDB = new AlbumDB(FALSE);
   $mynumalbums = $albumDB->numAlbums($gallery->user);
   // display the menu title
   echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"135\" align=\"left\">";
   // display all top level albums links
      for ($i=1; $i<=$mynumalbums; $i++) {
      $myAlbum = $albumDB->getAlbum($gallery->user, $i);
      $albumName = $myAlbum->fields['name'];
      $albumTitle = $myAlbum->fields['title'];
?>
<tr><td height="31" width="135" valign="middle" align="center" class="offnav2" onmouseover="this.className='onnav2';" onmouseout="this.className='offnav2';"><span class="menu">
<?php
      echo "<a href=\"" .makeAlbumUrl($albumName). "\">$albumTitle</a><br>";
?>
</span></td></tr>
<?php
     }
?>
</table>


<?php

if (isset($printShutterflyForm)) { ?>
<form name="sflyc4p" action="http://www.shutterfly.com/c4p/UpdateCart.jsp" method="post">
  <input type=hidden name=addim value="1">
  <input type=hidden name=protocol value="SFP,100">
  <input type=hidden name=pid value="C4PP">
  <input type=hidden name=psid value="GALL">
  <input type=hidden name=referid value="gallery">
  <input type=hidden name=returl value="this-gets-set-by-javascript-in-onClick">
  <input type=hidden name=imraw-1 value="<?php echo $rawImage ?>">
  <input type=hidden name=imrawheight-1 value="<?php echo $imageHeight ?>">
  <input type=hidden name=imrawwidth-1 value="<?php echo $imageWidth ?>">
  <input type=hidden name=imthumb-1 value="<?php echo $thumbImage ?>">
  <?php
  /* Print the caption on back of photo. If no caption,
  * then print the URL to this page. Shutterfly cuts
  * the message off at 80 characters. */
  $imbkprnt = $gallery->album->getCaption($index);
  if (empty($imbkprnt)) {
  	$imbkprnt = makeAlbumUrl($gallery->session->albumName, $id);
  }
  ?>
  <input type=hidden name=imbkprnta-1 value="<?php echo htmlentities(strip_tags($imbkprnt)) ?>">
</form>
<?php }
if (isset($printFotoserveForm)) { ?>
<form name="fotoserve"
action="http://www.fotoserve.com/menalto/build.html" method="post">
  <input type="hidden" name="image" value="<?php echo $rawImage ?>">
  <input type="hidden" name="thumb" value="<?php echo $thumbImage ?>">
  <input type="hidden" name="redirect" value="this-gets-set-by-javascript-in-onClick">
  <input type="hidden" name="name" value="<?php echo $photo->image->name . '.' . $photo->image->type; ?>">
</form>
<?php }
if (isset($printPhotoAccessForm)) { ?>
  <form method="post" name="photoAccess" action="http://www.tkqlhce.com/click-1660787-10381744">
  <input type="hidden" name="cb" value="CB_GP">
  <input type="hidden" name="redir" value="true">
  <input type="hidden" name="returnUrl" value="this-gets-set-by-javascript-in-onClick">
  <input type="hidden" name="imageId" value="<?php echo $photo->image->name . '.' . $photo->image->type; ?>">
  <input type="hidden" name="imageUrl" value="<?php echo $rawImage ?>">
  <input type="hidden" name="thumbUrl" value="<?php echo $thumbImage ?>">
  <input type="hidden" name="imgWidth" value="<?php echo $imageWidth ?>">
  <input type="hidden" name="imgHeight" value="<?php echo $imageHeight ?>">
</form>
<?php }

?>

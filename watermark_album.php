<?php
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
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = '';
}
require(dirname(__FILE__) . '/init.php'); ?>
<?php
// Hack check
if (!$gallery->user->canChangeTextOfAlbum($gallery->album)) {
	exit;
}

$err = "";	
if (!strcmp($submit, "Save")) {
        if (($wmAlign > 0) && ($wmAlign < 12))
        {
		print "<html><body>\n";
                echo "<center> ". _("Watermarking album.")."<br/>(". _("this may take a while"). ")</center>\n";

                my_flush();
                set_time_limit($gallery->app->timeLimit);
                $gallery->album->watermarkAlbum($wmName, "", $wmAlign, $wmAlignX, $wmAlignY);
                $gallery->album->save();
                dismissAndReload();
                return;
        } else {
            $err = "Alignment must be between 1 and 11, inclusive.";
        }
}
?>
<html>
<head>
  <title><?php echo _("Watermark Album") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

<?php
   if ($err)
   {
      echo "<span class=error>$err</span><br><br>";
   }
   echo makeFormIntro("watermark_album.php",
                      array("name" => "theform",
                            "method" => "POST"));
   include $GALLERY_BASEDIR . 'layout/watermarkform.inc';
?>
<input type=hidden name="index" value="<?php echo $index ?>"/>
<input type=submit name="submit" value="<?php echo _("Save") ?>">
<input type=submit name="submit" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>

<script language="javascript1.2">
<!--   
// position cursor in top form field
document.theform.data.focus();
//-->
</script>

</body>
</html>

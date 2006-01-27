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
<?php
	include(dirname(__FILE__) . '/init.php');
	doctype();
?>
<html>
<head>
  <title> <?php echo _("Gallery Configuration") .':: '. _("Frames") ?> </title>
  <?php common_header(); ?>

	<!--
		This Javascript and the Tabs are inspired by the Horde Forms code
	-->
	<script language="JavaScript" type="text/javascript">
	<!--
	function configSection(inittab) {

		this.oldtab=inittab;

		this.toggle = function(id) {
			document.getElementById(this.oldtab).style.display = 'none';
			document.getElementById('tab_' + this.oldtab).className = 'tab';
	
			document.getElementById(id).style.display = 'inline';
			document.getElementById('tab_' + id).className = 'tab-hi';

			this.oldtab=id;
		}
	}
	//-->
	</script>
</head>
<body>
      <?php
	$descriptions = array();
        $names = array();

        $names["none"] = _("None");
        $descriptions["none"] = _("No frames");
        $names["dots"] = _("Dots");
        $descriptions["dots"] = _("Just a simple dashed border around the thumb.");
        $names["solid"] = _("Solid");
        $descriptions["solid"] = _("Just a simple solid border around the thumb.");

        $dir = GALLERY_BASE . '/html_wrap/frames';
        if (fs_is_dir($dir) && is_readable($dir) && $fd = fs_opendir($dir)) {
                while ($file = readdir($fd)) {
                        $subdir="$dir/$file";
                        $frameinc="$subdir/frame.def";
                        if (fs_is_dir($subdir) && fs_file_exists($frameinc)) {
                                $name=NULL;
                                $description=NULL;
                                require($frameinc);
                                if (empty($name)) {
                                        $name=$file;
                                }
                                if (empty($description)) {
					$description=$file;
                                }
                                $names[$file] = $name;
                                $descriptions[$file] = $description;
//print "<option value=\"$file\">$file</option>\n";
                        } else {
                                if (false && isDebugging())
                                {
                                        echo gallery_error(sprintf(_("Skipping %s."),
								$subdir));
                                }
                        }
                }
        } else {
                echo '<--' . sprintf(_("Can't open %s"), $dir) . '-->';
        }
        foreach (array_keys($names) as $key) {
  //         print '<option value="' . $key . '">' . $names[$key] . '</option>';
        }

?>
    <table width="100%" cellspacing="0">
    <tr>
<?php
        $count = 0;
        $col = 0;
        foreach (array_keys($names) as $key) {
           if (isset($_GET['frame'])) {
              if ($key != $_GET['frame']) {
                 $tab = "tab";
              } else {
                 $firstkey = $key;
                 $tab = "tab-hi";
              }
           } else {
              if ($count) {
                 $tab = "tab";
              } else {
                 $firstkey = $key;
                 $tab = "tab-hi";
              }
	   }
           if ($col) {
              print "<td class=\"tabspacer\">&nbsp;</td>\n";
           }
           print "<td class=\"$tab\" id=\"tab_group_$key\" onClick=\"section_tabs.toggle('group_$key')\">".$names[$key]."</td>\n";
           $count++;
	   $col++;
           if ($col > 3) {
?>
    </tr>
    </table>
    <table width="100%"cellspacing="0" style="margin-top:5px;">
    <tr>
<?php
             $col = 0;
           } // end if ($col > 3)
        } // end foreach
?>
    </tr>
    </table>
<?php if (isset($firstkey)) { ?>
    <script language="JavaScript" type="text/javascript">
	 section_tabs = new configSection('group_<?php echo $firstkey ?>')
    </script>
<?php } ?>

<?php
   global $gallery;
   list($iWidth, $iHeight) = getDimensions("../images/movie.thumb.jpg");

   $gallery->html_wrap['imageWidth']  = $iWidth;
   $gallery->html_wrap['imageHeight'] = $iHeight;

   $gallery->html_wrap['borderColor'] = $borderColor;
   $gallery->html_wrap['borderWidth'] = 1;
   $gallery->html_wrap['pixelImage'] = getImagePath('pixel_trans.gif');
   $gallery->html_wrap['imageTag'] = '<img src="../images/movie.thumb.jpg" alt="movie_thumb">';
   $gallery->html_wrap['base'] = "..";
   foreach (array_keys($names) as $key) {
     $display = "none";
     if ($key == $firstkey) {
        $display = "inline";
     }
     print "<div id=\"group_$key\" style=\"display: $display\">";
     print "<p>".$descriptions[$key]."</p>";
     $gallery->html_wrap['frame'] = $key;
     includeHtmlWrap('inline_gallerythumb.frame');
     print "</div>";
   }
?>
<p align="center">
	<input type="button" name="close" value="<?php echo _("Close Window") ?>" onClick="window.close()">
</p>
</body>
</html>

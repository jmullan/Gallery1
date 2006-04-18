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
require_once(dirname(__FILE__) . '/init.php');

global $gallery;

printPopupStart(_("Gallery Configuration") .':: '. _("Frames"));
?>
	<!--
		This Javascript and the Tabs are inspired by the Horde Forms code
	-->
	<script language="JavaScript" type="text/javascript">
	function configSection(inittab) {

	    this.oldtab = inittab;

	    this.toggle = function(id) {
	        document.getElementById(this.oldtab).style.display = 'none';
	        document.getElementById('tab_' + this.oldtab).className = '';

	        document.getElementById(id).style.display = 'inline';
	        document.getElementById('tab_' + id).className = 'g-activeTab';

	        this.oldtab = id;
	    }
	}
	</script>
<?php
$descriptions = array();
$names = array();

$names["none"] = _("None");
$descriptions["none"] = _("No frames");
$names["dots"] = _("Dots");
$descriptions["dots"] = _("Just a simple dashed border around the thumb.");
$names["solid"] = _("Solid");
$descriptions["solid"] = _("Just a simple solid border around the thumb.");
$names["siriux"] = 'Siriux';
$descriptions["siriux"] = _("The frame from Nico Kaisers Siriux theme.") ;

$dir = GALLERY_BASE . '/html_wrap/frames';
if (fs_is_dir($dir) && is_readable($dir) && $fd = fs_opendir($dir)) {
    while ($file = readdir($fd)) {
        $subdir = "$dir/$file";
        $frameinc = "$subdir/frame.def";
        if (fs_is_dir($subdir) && fs_file_exists($frameinc)) {
            $name = NULL;
            $description = NULL;
            require($frameinc);
            if (empty($name)) {
                $name = $file;
            }
            if (empty($description)) {
                $description = $file;
            }
            $names[$file] = $name;
            $descriptions[$file] = $description;
        } else {
            if (false && isDebugging()) {
                echo gallery_error(sprintf(_("Skipping %s."), $subdir));
            }
        }
    }
} else {
    echo '<--' . sprintf(_("Can't open %s"), $dir) . '-->';
}

?>
<div class="g-tabset">
<?php
$count = 0;

foreach (array_keys($names) as $key) {
    $class = '';
    if (isset($_GET['frame'])) {
        if ($key == $_GET['frame']) {
            $firstkey = $key;
 	    $class = ' class="g-activeTab"';
        }
    }
    echo "<a$class id=\"tab_group_$key\" onClick=\"section_tabs.toggle('group_$key')\">".$names[$key]."</a>\n";
}

?>
</div>
<?php if (isset($firstkey)) { ?>
    <script language="JavaScript" type="text/javascript">
    section_tabs = new configSection('group_<?php echo $firstkey ?>')
    </script>
 
<?php }


list($iWidth, $iHeight) = getDimensions("../images/movie.thumb.jpg");

$gallery->html_wrap['imageWidth']  = $iWidth;
$gallery->html_wrap['imageHeight'] = $iHeight;

if(!isset($borderColor)) {
    $borderColor = '#FF00FF';
}
$gallery->html_wrap['borderColor'] = $borderColor;
$gallery->html_wrap['borderWidth'] = 1;
$gallery->html_wrap['pixelImage'] = getImagePath('pixel_trans.gif');
$gallery->html_wrap['imageTag'] = '<img src="../images/movie.thumb.jpg" alt="movie_thumb">';
$gallery->html_wrap['imageHref'] = '';
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
</div>
<p align="center">
    <input type="button" name="close" value="<?php echo _("Close Window") ?>" onClick="window.close()" class="g-button">
</p>
</body>
</html>

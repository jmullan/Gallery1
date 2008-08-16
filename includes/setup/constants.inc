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
 * $Id: constants.inc 13795 2006-06-11 14:21:15Z jenst $
 */

/**
 * Defines all the settings from step 2.
 *
 * @package Setup
 */

/**
 * Function and defintions for the statistics.
 */
require_once(GALLERY_BASE . '/includes/stats/stats.inc.php');

/**
 * Printservices
 */
require_once(GALLERY_BASE . '/includes/definitions/services.php');

$stats_choices = array();
foreach ($stats['types'] as $key => $trash) {
	if(!empty($stats['types'][$key]['linktext'])) {
		$stats_choices[$key] = $stats['types'][$key]['linktext'];
	}
}

$locale_check = check_locale();

/* $elawisyl <=> Enable LAnguages WIthout SYstem Locales
 * is set to "no" when we have no system locales and are in linux
 * "no" disables all language settings
*/

if ($locale_check == NULL) {
	$elawisyl = 'no';
}
else {
	$elawisyl = 'yes';
}

$netpbm_check = check_graphics();
$IM_check = check_graphics_im();

// If all watermarking/overlay applications are missing
if ( isset($netpbm_check[2]['pnmcomp']) && isset($netpbm_check[2]['pamcomp']) && isset($IM_check[2]['composite']) ) {
	$enablewatermark = 'no';
} else {
	$enablewatermark = 'yes';
}

if (function_exists('posix_getpwuid')) {
	$rec = @posix_getpwuid(posix_getuid());
	$webserver_user = $rec['name'];
}
else {
	$whoami = locateFile('whoami');
	if ($whoami) {
		fs_exec($whoami, $results, $status);
		$webserver_user = $results[0];
	}
	else {
		$webserver_user = gTranslate('config',"unknown");
	}
}


$common_messages['getting-netpbm'] = '<br>'.
  sprintf(gTranslate('config',"Gallery prefers Netpbm version 10.13 and up.  You can install just the binaries Gallery needs from the %sGallery Download Page%s. Or, you can compile and install the entire Netpbm package from the %sNetpbm website%s."),
  "<a href=\"http://sourceforge.net/projects/gallery\">",
  '</a>',
  "<a href=\"http://sourceforge.net/projects/netpbm\">",
  '</a>')  .
  '<p style="font-weight: bold;">' . gTranslate('config',"Note:"). '</p>'.
  gTranslate('config',"You can install the binaries from the Gallery site even if you don't have root access on your box!  If you can't get it working, try leaving the Netpbm path blank and using ImageMagick instead.");

$common_messages['fail-netpbm-partial'] =
  gTranslate('config',"I've found part of Netpbm, but some critical components are missing (see below).  Perhaps your version of Netpbm is out of date.") . '  ' .
  $common_messages['getting-netpbm'];

$common_messages['getting-imagemagick'] = '<br>'.
  sprintf(gTranslate('config',"Gallery prefers ImageMagick version 5.4.8 and up. (Tough Version 6.x is recommended.)  You can get the entire ImageMagick package from the %sImageMagick website%s." ),
  '<a href="http://www.imagemagick.org">', '</a>') .
  '<p style="font-weight: bold;">' . gTranslate('config',"Note:"). '</p>'.
  gTranslate('config',"The site also has binaries available for assorted operating systems.  If you can't get it working, try leaving the ImageMagick path blank and using Netpbm instead.");


$constants =  array_merge(array(
	'group_mandatory' => array (
		'type' => 'group_start',
		'default' => 'inline',
		'title' => gTranslate('config',"General settings"),
		'contains_required' => true,
		'inst_type' => 'easy',
	),
	'galleryTitle' => array(
		'prompt' => gTranslate('config',"Gallery Title"),
		'desc' => gTranslate('config',"The name of your Gallery.  E.g. <i>My Cool Photos</i>"),
		'value' => gTranslate('config',"Gallery"),
		'type' => 'text',
		'attrs' => array('size' => 50),
	),
	'editPassword' => check_admins(),
	'skinname' => array(
		'prompt' => gTranslate('config',"Skin Name"),
		'desc' => gTranslate('config',"Select a skin to use for Gallery. You now have a wide choice of &quot;skins&quot; that affect the look of every page of your Gallery.  You can have fun experimenting with the available skins. Choose &quot;None&quot; to use the default skin.") .
		  			available_skins(true),
		'choices' => available_skins(),
		'value' => 'none'
	),
	'useIcons' => array(
		'prompt' => gTranslate('config',"Use Icons ?"),
		'desc' => gTranslate('config',"You can choose whether you want to use Gallery icons, text only, or both. When you choose icons only, then there will be a tooltip."),
		'choices' => array(
			'both' => gTranslate('config',"Icons and text"),
			'no' => gTranslate('config',"Text only"),
			'yes' => gTranslate('config',"Icons only")
			),
		'value' => 'no'
	),
	'uploadMode' => array(
		'prompt' => gTranslate('config',"Default upload method"),
		'desc' => gTranslate('config',"Which method of uploading photos should be defaulted to for users who don't specify?"),
		'choices' => array(
			'form' => gTranslate('config',"Form-based Upload"),
			'applet_mini' => gTranslate('config',"Mini-Applet"),
			'applet' => gTranslate('config',"Full-Applet"),
			'url' => gTranslate('config',"URL")
			),
		'value' => 'form',
		'inst_type' => 'advanced'
		),
	'name_display' => array(
		'prompt' => gTranslate('config',"Format for commenter and owner names"),
		'desc' => gTranslate('config',"Names of logged in users and owners of albums and photos can be displayed in a variety of different ways"),
		'choices' => displayNameOptions(),
		'value' => '!!FULLNAME!! (!!USERNAME!!)',
		'inst_type' => 'advanced'
	),
	'group_mandatory_end' => array (
		'type' => 'group_end',
	),
	'group_URLS' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Locations and URLs"),
		'contains_required' => true,
		'inst_type' => 'easy',
	),
	'albumDir' => array(
		'prompt' => gTranslate('config',"Album directory"),
		'desc' => sprintf(gTranslate('config',"The full path on physical disk to the directory where your photos will be stored.  This directory should start off empty&#8212;Gallery will fill it.  Note that this directory needs to have write access by the user who is running the web server (in your case this user is %s)."),
		  '<b>'. $webserver_user . '</b>'),
		'value' => getenv('DOCUMENT_ROOT') . '/albums',
		'require-write-dir' => 1,
		'filename' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'required' => true,
		'noStripSlash' => true
	),
	'tmpDir' => array(
		'prompt' => gTranslate('config',"Temporary directory"),
		'desc' => sprintf(gTranslate('config',"The filesystem path to a temporary directory.  E.g. %s or %s.  Note that if you have <b>open_basedir</b> configured on your system, then this temporary directory must be inside the <b>open_basedir</b> path!  In that case you may need to create the directory yourself and make sure that it's writable by the webserver process."),
		  '<i>/tmp</i>','<i>c:\\windows\\temp</i>'),
		'value' => getenv('TMP'),
		'filename' => 1,
		'require-write-dir' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'required' => true,
		'noStripSlash' => true,
	),
	'photoAlbumURL' => array(
		'prompt' => "Gallery URL",
		'desc' => sprintf(gTranslate('config',"The full URL to the Gallery web application. (e.g. %s) Gallery can operate with relative URLs, however some features, such as printing, may not function correctly."),
		  '<i>http://www.example.com/gallery</i>'),
		'value' => 'http://' . getenv('SERVER_NAME') . $GALLERY_URL,
		'no-trailing-slash' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'required' => true,
	),
	'albumDirURL' => array(
		'prompt' => gTranslate('config',"Albums URL"),
		'desc' => sprintf(gTranslate('config',"The full URL to your album directory on your web server.  (e.g. %s) Gallery can operate with relative URLs, however some features, such as printing, may not function correctly."),
		  '<i>http://www.example.com/albums</i>'),
		'value' => 'http://' . getenv('SERVER_NAME') . '/albums',
		'no-trailing-slash' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'required' => true,
	),
	'movieThumbnail' => array(
		'prompt' => gTranslate('config',"Thumbnail image"),
		'desc' => sprintf(gTranslate('config','The filesystem path to the thumbnail image to show for movies. E.g. %s'), '<i>/home/foo/thumbnail.jpg</i>'),
		'value' => GALLERY_BASE . '/images/movie.thumb.jpg',
		'type' => 'text',
		'attrs' => array('size' => 50),
		'noStripSlash' => true,
		'inst_type' => 'advanced'
	),
	'mirrorSites' => array(
		'prompt' => gTranslate('config',"Mirror sites"),
		'desc' => sprintf(gTranslate('config',"A list of URLs that contain mirrors of your albums directory.  Specify one URL per line with the ones that are more reliable towards the top.  E.g. %s"),
		  '<center><i>' .
		  'http://www.fast.server/albums<br>http://www.other.server/albums</i>' .
		  '</center>'),
		'optional' => 1,
		'type' => 'textarea',
		'value' => NULL,
		'attrs' => array('cols' => 50, 'rows' => 5),
		'inst_type' => 'advanced'
	),
	'groupLocations_end' => array (
		'type' => 'group_end',
	),

	'groupToolkits' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Toolkits / image processing"),
		'contains_required' => true,
		'inst_type' => 'easy',
	),
	'graphics' => array(
		'prompt' => gTranslate('config',"Graphics suite to use"),
		'desc' => gTranslate('config',"This setting tells Gallery which set of graphics tools you prefer it to use.  If Gallery says both are okay (on the first page) then this is simply user preference.  Otherwise go with the one that Gallery was able to find without error."),
		'choices' => array('Netpbm' => "Netpbm", 'ImageMagick' => "ImageMagick"),
		'value' => default_graphics(),
		'required' => true,
		'attrs' => array('id' => 'graphics', 'onChange' => 'toggleGraphics(this.value)')
	),
	'subgroup_ImageMagick' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"ImageMagick Settings"),
		'display' => (getCurrentGraphicTool() == 'ImageMagick') ? 'inline' : 'none',
		'inst_type' => 'easy'
	),
	'ImPath' => array(
		'optional' => 1,
		'prompt' => gTranslate('config',"Path to ImageMagick"),
		'desc' => gTranslate('config',"The path to the directory containing ImageMagick on your system."). '<br>'.
		  sprintf(gTranslate('config',"This path is only required if you choosed %s above. Otherwise you can leave it like it is, or empty."), "ImageMagick"),
		'verify-func' => 'check_graphics_im',
		'value' => locateDir(fs_executable('identify'), (isset($gallery->app->ImPath)) ? $gallery->app->ImPath : ''),
		'fail-imagemagick' => gTranslate('config',"I can't find ImageMagick at the location you provided.") . '  ' .
		  $common_messages["getting-imagemagick"],
		'filename' => 1,
		'attrs' => array('size' => 50),
		'no-trailing-slash' => 1,
		'type' => 'text',
		'verify-func-takes-graphics-type' => true,
		'required' => true,
		'noStripSlash' => true,
	),
	'subgroup_imagemagick_end' => array (
		'type' => 'subgroup_end',
	),
	'subgroup_Netpbm' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"Netpbm Settings"),
		'display' => (getCurrentGraphicTool() == 'Netpbm') ? 'inline' : 'none',
		'inst_type' => 'easy'
	),
	'pnmDir' => array(
		'optional' => 1,
		'prompt' => gTranslate('config',"Path to Netpbm"),
		'desc' => sprintf(gTranslate('config',"The path to the directory containing Netpbm on your system.  If you downloaded Netpbm from the Gallery download page, don't forget to make the files executable.  E.g. %s or %s or %s"),
		  '<i>/usr/local/netpbm</i>',
		  '<i>/usr/local/bin</i>',
		  '<i>c:\\apps\\netpbm</i>'). '<br>'.
		  sprintf(gTranslate('config',"This path is only required if you choosed %s above. Otherwise you can leave it like it is, or empty."), "Netpbm"),
		'value' => locateDir(fs_executable('jpegtopnm'), isset($gallery->app->pnmDir) ? $gallery->app->pnmDir : ''),
		'verify-func' => 'check_graphics',
		'fail-netpbm' => gTranslate('config',"I can't find Netpbm at the location you provided.") . '  ' .
		  $common_messages['getting-netpbm'],
		'fail-netpbm-partial' => $common_messages['fail-netpbm-partial'],
		'filename' => 1,
		'attrs' => array('size' => 50),
		'no-trailing-slash' => 1,
		'type' => 'text',
		'verify-func-takes-graphics-type' => true,
		'required' => true,
		'noStripSlash' => true,
	),
	'pnmtojpeg' => array(
		'prompt' => gTranslate('config',"PNM to JPEG converter"),
		'desc' => gTranslate('config',"This value is only relevant if you use Netpbm.  Netpbm uses a tool to encode its internal image format into JPEG.  This tool is called either <i>ppmtojpeg</i> or <i>pnmtojpeg</i> depending on the version of Netpbm that you have installed.  Check your Netpbm directory to see which one you have installed and specify it here.  If you aren't sure, just take a guess and we'll try to verify it for you."),
		'choices' => array('pnmtojpeg' => 'pnmtojpeg', 'ppmtojpeg' => 'ppmtojpeg'),
		'value' => 'pnmtojpeg'
	),
	'pnmcomp' => array(
		'prompt' => gTranslate('config',"PNM Composite (Overlay)"),
		'desc' => gTranslate('config',"This value is only relevant if you use Netpbm.  Netpbm uses a tool to overlay one image over another.  This tool is called either <i>pnmcomp</i> or <i>pamcomp</i> depending on the version of Netpbm that you have installed.  Check your Netpbm directory to see which one you have installed and specify it here.  If you aren't sure, just take a guess and we'll try to verify it for you."),
		'choices' => array('pnmcomp' => 'pnmcomp', 'pamcomp' => 'pamcomp'),
		'value' => 'pnmcomp',
	),
	'subgroup_netPBM_end' => array (
		'type' => 'subgroup_end',
	),
	'autorotate' => array(
		'prompt' => gTranslate('config',"Auto-rotate images"),
		'desc' => gTranslate('config',"Selecting this option has Gallery automatically attempt to rotate images on upload, based on the orientation information that is stored in JPEG EXIF data by some newer digital cameras.") . '<br>' .
		'<span class="g-attention">' . gTranslate('config',"Requires Jhead EXIF parser v.1.9 or greater (See optional tools).") . '</span>',
		'choices' => array('yes' => gTranslate('config',"Yes"), 'no' => gTranslate('config',"No")),
		'value' => 'yes',
		'inst_type' => 'advanced'
	),
	'subgroup_quality' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"JPEG Quality <b>(Advanced)</b>"),
		'desc' => gTranslate('config',"The JPEG file format trades off size versus quality.  Gallery can control the quality of the JPEGs that it produces when you resize, rotate or make thumbnails from JPEG images.  Higher quality images are larger in size than lower quality images.  However, in many cases there's a point at which raising the quality increases the size of the files without noticeably affecting the appearance of the image.  Setting this value to &quot;100&quot; is not recommended. If you notice that your thumbnails and resized images are larger than the originals and you don't like this, you can try lowering this value.  <b>Note</b> that changing this value will only affect images changed in the <i>future</i>."),
		'display' => 'inline',
	),
	'jpegImageQuality' => array(
		'prompt' => gTranslate('config',"JPEG Quality for resized images"),
		'desc' => '',
		'choices' => array(
			50 => 50, 55 => 55, 60 => 60, 65 => 65,
			70 => 70, 75 => 75, 80 => 80, 85 => 85,
			90 => 90, 95 => 95, 100 => 100
		),
		'value' => 90
	),
	'highlightJpegImageQuality' => array(
		'prompt' => gTranslate('config',"JPEG Quality for highlight images on the startpage"),
		'desc' => '',
		'choices' => array(
			50 => 50, 55 => 55, 60 => 60, 65 => 65,
			70 => 70, 75 => 75, 80 => 80, 85 => 85,
			90 => 90, 95 => 95, 100 => 100
		),
		'value' => 80
	),
	'thumbJpegImageQuality' => array(
		'prompt' => gTranslate('config',"JPEG Quality for thumbs inside the albums"),
		'desc' => '',
		'choices' => array(
			50 => 50, 55 => 55, 60 => 60, 65 => 65,
			70 => 70, 75 => 75, 80 => 80, 85 => 85,
			90 => 90, 95 => 95, 100 => 100
		),
		'value' => 80
	),
	'subgroup_quality_end' => array (
		'type' => 'subgroup_end',
	),
	'groupToolkits_end' => array (
		'type' => 'group_end',
	),
	'group_embedded' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Embedded Gallery"),
		'desc' => gTranslate('config',"Gallery automatically detects when it is embedded within one of the supported CMS environments, however there may be some environments which have special options which need to be set.  Any options which are not automatically detected will need to be specified on this screen.") . ' <b>' . gTranslate('config',"If your ISP or CMS requires register_globals to be enabled, you may be required to enable the 'Disable register_globals check' option under the Technical tab.") . '</b>',
	),
	'geeklog_dir' => array(
		'prompt' => gTranslate('config',"GeekLog Dir"),
		'optional' => 1,
		'desc' => gTranslate('config',"If you use Geeklog, please enter the location of the GeekLog public_html directory. i.e. '/path/to/geeklog/public_html'"),
		'attrs' => array('size' => 80),
		'type' => 'text',
		'value' => '',
		'noStripSlash' => true,
	),
	'group_embedded_end' => array (
		'type' => 'group_end'
	),
	'group_mainpage' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Main Gallery page"),
		'contains_required' => true,
		'inst_type' => 'easy',
	),
	'theme' => array(
	   'prompt' => gTranslate('config', "Theme"),
	   'desc'  => gTranslate('config', "Choose the theme for the mainpage"),
	   'choices' => array(
		   'classic' => gTranslate('config', "The classic layout since Gallery 1.0"),
		   'matrix' => gTranslate('config', "A lightweight layout, with less textual information. Inspired by the Gallery 2 Matrix")
		),
		'required' => true,
		'value' => 'classic'
	),
	'albumTreeDepth' => array(
		'prompt' => gTranslate('config',"Show the album tree ?"),
		'desc' => gTranslate('config',"If you turn this option on, you'll see a list of sub-albums when you're browsing the top level of your Gallery.  This will make navigating to a specific sub album much easier. You can choose whether or not you want to limit the depth of the tree to avoid severely cluttering the appearance of the top Gallery page.") .
				  '<br><b>'.
				  gTranslate('config', "This setting has no effect in matrix theme.") .
				  '</b>',
		'choices' => array(
			0 => gTranslate('config',"No tree"),
			1 => sprintf (gTranslate('config',"%d sublevel"),1),
			2 => sprintf (gTranslate('config',"%d sublevels"),2),
			3 => sprintf (gTranslate('config',"%d sublevels"),3),
			4 => sprintf (gTranslate('config',"%d sublevels"),4),
			5 => sprintf (gTranslate('config',"%d sublevels"),5),
			10 => sprintf (gTranslate('config',"%d sublevels"),10),
			1000 => gTranslate('config',"Complete Tree")
		),
		'value' => 1000,
		'inst_type' => 'advanced'
	),
	'microTree' => array(
		'prompt' => gTranslate('config',"Replace album tree with microthumbs"),
		'desc' => gTranslate('config',"If you have set album tree to anything but off, this will display microthumbs of the sub albums instead of the album tree.") .
				  '<br><b>'.
				  gTranslate('config', "This setting has no effect in matrix theme.") .
				  '</b>',
		'choices' => array('yes' => gTranslate('config',"Yes"), 'no' => gTranslate('config',"No")),
		'value' => 'no',
		'inst_type' => 'advanced'
	),
	'highlight_size' => array(
		'prompt' => gTranslate('config',"Highlight size"),
		'desc' => gTranslate('config',"The target size (in pixels) of thumbnails shown in the Gallery page (highlight images)."),
		'value' => 200,
		'type' => 'text',
		'attrs' => array('size' => 5),
		'inst_type' => 'advanced'
	),
	'highlight_ratio' => array(
		'prompt' => gTranslate('config',"Highlight Ratio"),
		'desc' => gTranslate('config',"The ratio in which the highlights are made. This affects only new highlights. For existing use 'rebuild highlights'"),
		'choices' => array('0' => gTranslate('config',"As the original image"), '1/1' => gTranslate('config',"Square highlight")),
		'value' => 0,
		'inst_type' => 'advanced'
	),
	'showOwners' => array(
		'prompt' => gTranslate('config',"Show Owner"),
		'desc' => gTranslate('config',"Show album owner's name next to each album?"),
		'choices' => array('yes' => gTranslate('config',"Yes"), 'no' => gTranslate('config',"No")),
		'value' => 'no',
		'inst_type' => 'advanced'
	),
	'albumsPerPage' => array(
		'prompt' => gTranslate('config',"Albums per page"),
		'desc' => gTranslate('config',"How many albums should be displayed per Gallery page?"),
		'value' => 5,
		'attrs' => array('size' => 10),
		'type' => 'text'
	),
	'showSearchEngine' => array(
		'prompt' => gTranslate('config',"Search Engine"),
		'desc' => gTranslate('config',"Display search engine on main Gallery page?"),
		'choices' => array('yes' => gTranslate('config',"Yes"), 'no' => gTranslate('config',"No")),
		'value' => 'yes'
	),
	'slowPhotoCount' => array(
		'prompt' => gTranslate('config',"Accurate Photo Count"),
		'desc' => gTranslate('config',"Display the album and photo count on the Gallery root page using an accurate, but incredibly slow, counting method.  This was added at the request of many users, however it is not recommended for Galleries where you will have large numbers of albums and photos.  Some examples have been shown to cause the index page to load in as long as 30 seconds with this option enabled."),
		'choices' => array('yes' => gTranslate('config',"Yes"), 'no' => gTranslate('config',"No")),
		'value' => 'no',
		'inst_type' => 'advanced'
	),
	'gallery_thumb_frame_style' => array(
		'prompt' => gTranslate('config',"Frame around albums"),
		'desc' => gTranslate('config',"On the main page, the image representing each album has a frame around it.  The following styles are available:") .
		  available_frames(true),
		'choices' => available_frames(),
		'value' => 'simple_book'
	),
	'group3_end' => array (
		'type' => 'group_end',
	),
	'group_optionals' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Optional tools")
	),
	'subgroup_archives' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"Archives"),
		'display' => 'inline',
	),
	'zipinfo' => array(
		'prompt' => "Zipinfo",
		'desc' => sprintf(gTranslate('config',"The filesystem path to the <b>%s</b> binary.  E.g. %s or %s. This is optional so if you don't have it, don't worry about it too much."),
		  '<a href="http://www.info-zip.org/" target="_blank">zipinfo</a>', '<i>/usr/local/bin/zipinfo</i>', '<i>c:\\bin\\zipinfo.exe</i>'),
		  'value' => locateFile("zipinfo"),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'noStripSlash' => true,
	),
	'unzip' => array(
		'prompt' => "Unzip",
		'desc' => sprintf(gTranslate('config',"The filesystem path to the <b>%s</b> binary.  E.g. %s or %s. This is optional so if you don't have it, don't worry about it too much."),
		  '<a href="http://www.info-zip.org/" target="_blank">unzip</a>', '<i>/usr/local/bin/unzip</i>', '<i>c:\\bin\\unzip.exe</i>'),
		'value' => locateFile('unzip'),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'noStripSlash' => true,
	),
	'zip' => array(
		'prompt' => "zip",
		'desc' => sprintf(gTranslate('config',"The filesystem path to the <b>%s</b> binary.  E.g. %s or %s. This is optional so if you don't have it, don't worry about it too much."),
		'<a href="http://www.info-zip.org/" target="_blank">zip</a>', '<i>/usr/local/bin/zip</i>', '<i>c:\\bin\\zip.exe</i>'),
		'value' => locateFile('zip'),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'noStripSlash' => true,
	),
	'rar' => array(
		'prompt' => "rar",
		'desc' => sprintf(gTranslate('config',"The filesystem path to the <b>%s</b> binary.  E.g. %s or %s. This is optional so if you don't have it, don't worry about it too much."),
		'<a href="http://www.rarlab.com/" target="_blank">rar</a>', '<i>/usr/local/bin/rar</i>', '<i>c:\\bin\\rar.exe</i>'),
		'value' => locateFile('rar'),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'noStripSlash' => true,
	),
	'subgroup_archives_end' => array (
		'type' => 'subgroup_end',
	),
	'subgroup_exif' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"EXIF Data"),
		'display' => 'inline',
	),
	'use_exif' => array(
		'prompt' => "Jhead EXIF Parser",
		'desc' => sprintf(gTranslate('config',"The filesystem path to the <b>%s</b> EXIF parser.  E.g. %s or %s. This is optional so if you don't have it, don't worry about it too much."),
		  '<a href="http://www.sentex.net/~mwandel/jhead/" target="_blank">jhead</a>', '<i>/usr/local/bin/jhead</i>', '<i>c:\\bin\\jhead.exe</i>'),
		'value' => locateFile('jhead'),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'noStripSlash' => true,
	),
	'exiftags' => array(
		'prompt' => "exiftags EXIF Parser",
		'desc' => sprintf(gTranslate('config',"The filesystem path to the <b>%s</b> EXIF parser.  E.g. %s or %s. This is optional so if you don't have it, don't worry about it too much."),
		  '<a href="http://johnst.org/sw/exiftags/" target="_blank">exiftags</a>', '<i>/usr/local/bin/exiftags</i>', '<i>c:\\bin\\exiftags.exe</i>'),
		'value' => locateFile('exiftags'),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'noStripSlash' => true,
	),
	'subgroup_exif_end' => array (
		'type' => 'subgroup_end',
	),
	'subgroup_tools_other' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"Other"),
		'display' => 'inline',
	),
	'use_jpegtran' => array (
		'prompt' => "Jpegtran",
		'desc' => sprintf(gTranslate('config',"The filesystem path to the <b>jpegtran</b> utility.  E.g. %s or %s.  This is optional so if you don't have it, don't worry about it too much."),
		  '<i>/usr/local/bin/jpegtran</i>', '<i>c:\\bin\\jpegtran.exe</i>'),
		'value' => locateFile('jpegtran'),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => 'text',
		'attrs' => array ('size' => 50),
		'noStripSlash' => true,
	),
	'subgroup_tools_other_end' => array (
		'type' => 'subgroup_end',
	),
	'group5_end' => array (
		'type' => 'group_end',
	),
	'group_languages' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Languages and locales"),
		'inst_type' => 'easy',
	),
	'default_language' => array(
		'prompt' => gTranslate('config',"Default language"),
		'optional' => 1,
		'desc' => sprintf(gTranslate('config',"This is the language you will see as %s, and what users will see if no other preference is set."), '<b>admin</b>'),
		'type' => (sizeof(gallery_languages()) > 1) ? 'choices' : 'hidden',
		'choices' => array_merge(gallery_languages(), array('browser' => '>>> '. gTranslate('config',"Language given by Browser") .' <<<')),
		'value' => (!empty($gallery->language) && sizeof(gallery_languages()) >1 ) ? $gallery->language : 'en_US',
		'enabled' => $elawisyl
	),
	'ML_mode' => array(
		'prompt' => gTranslate('config',"Multi language mode"),
		'desc' => gTranslate('config',"Gallery has three multi language modes.  These affect the language that a user will see when visiting the site."),
		'type' => ( (sizeof(gallery_languages()) > 1) && ($elawisyl == 'yes') ) ? 'choices' : 'hidden',
		'choices' => array(
			1 => "1: " . gTranslate('config',"Gallery is only displayed in one language"),
			2 => "2: " . gTranslate('config',"Gallery is displayed in the language the browser sends"),
			3 => "3: " . gTranslate('config',"The User can choose the language via pulldown menu or national flags.")),
		'value' => ( (sizeof(gallery_languages()) > 1) && ($elawisyl == 'yes') ) ? 3 : 0,
	),
	'available_lang' => array(
		'prompt' => gTranslate('config',"Available languages"),
		'type' => (sizeof(gallery_languages()) > 1) ? 'choices' : 'hidden',
		'optional' => 1,
		'desc' => gTranslate('config',"These languages will be available as user choices in a pulldown menu or national flags, if you have choosen language Mode 3, and will be available to registered users as their default language in Mode 2 or 3."),
		'multiple_choices' => array_merge(gallery_languages(), array('addon' => insertFormJSLinks('available_lang[]'))),
		'value' => array_keys(gallery_languages()),
		'enabled' => $elawisyl
	),
	'show_flags' => array(
		'prompt' => gTranslate('config',"Show flags?"),
		'type' => (sizeof(gallery_languages()) > 1) ? 'choices' : 'hidden',
		'desc' => gTranslate('config',"In Mode 3 you can choose to display national flags instead of a small pulldown menu for the available languages."),
		'choices' => array('yes' => gTranslate('config',"Yes"), 'no' => gTranslate('config',"No")),
		'value' => 'no',
		'enabled' => $elawisyl
	),
	'dateString' => array(
		'prompt' => "<b>(" . gTranslate('config',"Advanced") .")</b><br> ". gTranslate('config',"Format for date strings."),
		'desc' => sprintf(gTranslate('config',"This format string will be used to display %s in %s.  It is recommended to use %s which will change format depending on language choice.  However, if you wish to change this string, please look at the %sPHP documentation%s."),
		  gTranslate('config',"date without time"), Gallery(), "<b>%x</b>",
		  '<a href="'.gTranslate('config',"http://www.php.net/manual/en/function.strftime.php"). '" target="_blank">', '</a>') .  '<br>' .
		  sprintf(gTranslate('config',"For example, %s will display date like this: %s."), "<b>%b %d, %Y</b>", "<b>" . strftime("%b %d, %Y") . "</b>"),
		'value' => '%x',
		'type' => 'text',
		'attrs' => array('size' => 20),
		'inst_type' => 'advanced'
		),
	'dateTimeString' => array(
		'prompt' => "<b>(" . gTranslate('config',"Advanced") .")</b><br> ". gTranslate('config',"Format for date/time strings."),
		'desc' => sprintf(gTranslate('config',"This format string will be used to display %s in %s.  It is recommended to use %s which will change format depending on language choice.  However, if you wish to change this string, please look at the %sPHP documentation%s."),
		  gTranslate('config',"date with time"), Gallery(), "<b>%c</b>",
		  '<a href="'.gTranslate('config',"http://www.php.net/manual/en/function.strftime.php"). '" target="_blank">', '</a>') .  "<br>" .
		  sprintf(gTranslate('config',"For example, %s will display date and time like this: %s."), "<b>%H:%M, %b %d, %Y</b>", "<b>" . strftime("%H:%M, %b %d, %Y") . "</b>"),
		'value' => '%c',
		'type' => 'text',
		'attrs' => array('size' => 20),
		'inst_type' => 'advanced'
	),
),
	config_maybe_locales()
);
// Continue constants ...
$constants = array_merge($constants, array(
	'group6_end' => array (
		'type' => 'group_end',
	),
	'group_email' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Email"),
		'desc' => sprintf(gTranslate('config',"If you are having problems with email, please run the %s diagnostic."),
		'<a href="check_mail.php">' . gTranslate('config',"Check Email") . '</a>'),
	),
	'emailOn' => array(
		'prompt' => gTranslate('config',"Enable email"),
		'verify-func' => 'verify_email',
		'desc' => sprintf(gTranslate('config',"Turns email on for the whole gallery.  If you set this to %s then none of the rest of this section will apply."), '<i>' . gTranslate('config',"No") . '</i>' ) .  '<br>' .
		  '<span style="font-weight: bold;">' . gTranslate('config',"Note:") . '</span> ' .
		  gTranslate('config',"If you enable these settings you give your users the opportunity to let Gallery send them their lost password via email.") . '<br>'.
		  '<span class="g-attention">' . gTranslate('config',"You need to enable this setting if you want to enable Self Registration.") . '</span>',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),
	'adminEmail' => array(
		'prompt' => gTranslate('config',"Admin email address"),
		'desc' => gTranslate('config',"Set this value to a valid working email address to receive email notices upon certain user actions and also so that users know whom to reach in case of problems. You may specify more than one address by separating them with commas.") .'<br>',
		'type' => 'text',
		'value' => '',
		'attrs' => array('size' => 50),
		'optional' => 1
	),
	'senderEmail' => array(
		'prompt' => gTranslate('config',"Sender email address"),
		'desc' => gTranslate('config',"Set this value to a valid working email address.  Emails that are automatically sent by Gallery will use this as a return address.  You should monitor this email address for bounce messages and replies from recipients.") .'<br>',
		'type' => 'text',
		'value' => '',
		'attrs' => array('size' => 50),
		'optional' => 1
	),
	'emailSubjPrefix' => array(
		'prompt' => gTranslate('config',"Email subject prefix"),
		'desc' => gTranslate('config',"All Gallery emails will have this as the first part of their subject."),
		'type' => 'text',
		'attrs' => array('size' => 50),
		'value' => (!empty( $gallery->app->galleryTitle)) ? '['. $gallery->app->galleryTitle .']'	: '['. Gallery() .']'
	),
	'emailGreeting' => array(
		'prompt' => gTranslate('config',"Welcome email message text"),
		'desc' => placeholderDescription(),
		'optional' => 1,
		'type' => 'textarea',
		'value' => '',
		'attrs' => array('cols' => 50, 'rows' => 11)
	),
	'adminCommentsEmail' => array(
		'desc' => gTranslate('config',"This allows the &quot;admin&quot; account to get email whenever a comment is added to any album"),
		'prompt' => gTranslate('config',"Admin comment email"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),
	'adminOtherChangesEmail' => array(
		'desc' => gTranslate('config',"This allows the &quot;admin&quot; account to get email whenever a change is made to any album"),
		'prompt' => gTranslate('config',"Admin Other Changes email"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),
	'email_notification' => array(
		'prompt' => gTranslate('config',"Admin notification of email activity"),
		'desc' => gTranslate('config',"You can keep a record of every email that is sent by Gallery.") .'<br>',
		'multiple_choices' => array(
			'email' => gTranslate('config',"Send summary email"),
			'logfile' => gTranslate('config',"Log to a file"),
			'bcc' => gTranslate('config',"Send duplicate email")
		),
		'optional' => 1,
		'value' => array()
	),
	'useOtherSMTP' => array(
		'prompt' => gTranslate('config',"Use SMTP server."),
		'desc' => gTranslate('config',"Use a smtp server to process outgoing gallery email, instead of the internal PHP mail() function. This server can (not must) run on a different machine (ie: Your ISPs mail server)."),
		'choices' => array(
			'no' => gTranslate('config',"No"),
			'yes' => gTranslate('config',"Yes")
		),
		'optional' => 1,
		'value' => 'no',
		'attrs' => array('id' => 'smtp', 'onChange' => 'toggleSMTP(this.value)')
	),
	'subgroup_SMTP' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"SMTP Settings"),
		'display' => (useSMTP() == 'yes') ? 'inline' : 'none'
	),
	'smtpHost' => array(
		'prompt' => gTranslate('config',"SMTP Server"),
		'desc' => gTranslate('config',"The name or IP address of the SMTP Server you want to use.") .'<br>',
		'type' => 'text',
		'value' => 'localhost',
		'attrs' => array('size' => 60),
		'optional' => 1
	),
	'smtpFromHost' => array(
		'prompt' => gTranslate('config',"Sender hostname"),
		'desc' => gTranslate('config',"The full hostname of the server you are sending the mail from (The Server where your Gallery is installed on). If your Server is behind a NAT, this would be the DNS name of your router."),
		'type' => 'text',
		'value' => (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : 'localhost',
		'attrs' => array('size' => 60),
		'optional' => 0
	),
	'smtpPort' => array(
		'prompt' => gTranslate('config',"SMTP Port"),
		'desc' => gTranslate('config',"The port on the SMTP server you are connecting to.") .'<br>',
		'type' => 'text',
		'value' => 25,
		'attrs' => array('size' => 10),
		'optional' => 1
	),
	'smtpUserName' => array(
		'prompt' => gTranslate('config',"SMTP account name"),
		'desc' => gTranslate('config',"If your provide requires a username for outgoing email, use it here, otherwise leave blank.") .'<br>',
		'type' => 'text',
		'value' => '',
		'attrs' => array('size' => 20),
		'optional' => 1
	),
	'smtpPassword' => array(
		'prompt' => gTranslate('config',"SMTP password"),
		'desc' => gTranslate('config',"Password for sending email through smtp server. NOTE: This password is stored in cleartext in config.php. Make sure that permissions are set to keep others from getting at it!") .'<br>',
		'type' => 'password',
		'value' => '',
		'verify-func' => 'verify_password',
		'attrs' => array('size' => 20),
		'optional' => 1
	),
	'subgroup_SMTP_end' => array (
		'type' => 'subgroup_end',
	),
	'group_email_end' => array (
		'type' => 'group_end',
	),
	'group_register' => array(
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Registration"),
		'desc' => gTranslate('config',"Adds the possibility that user can registrate themself.") .
			'<div class="g-attention">' . gTranslate('config',"You need to enable emailing if you want to use this feature.") . '</div>',
	),
	'selfReg' => array(
		'prompt' => gTranslate('config',"Allow visitors to self register."),
		'desc' => gTranslate('config',"Enabling this will allow visitors to your site to create their own login accounts."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
		'requires-features' => array('emailOn' => gTranslate('config',"Email"))
	),
	'selfRegCreate' => array(
		'prompt' => gTranslate('config',"Self registered users can create albums."),
		'desc' => gTranslate('config',"Enabling this will allow self registered users to create their own albums as soon as they register.  Not having this enabled means you will have to give them permission manually before they can create or alter any contents of the Gallery.  This setting is ignored if self-registration is disabled.") .'<br>',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'),
	'multiple_create' => array(
		'prompt' => gTranslate('config',"Enable bulk account creation"),
		'desc' => gTranslate('config',"This option allows you to create many new user accounts at one time by creating a file of new users.") .'<br>',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),
	'group_register_end' => array(
		'type' => 'group_end',
	),
	'group_slideshow' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Gallery wide slideshow"),
		'desc' => gTranslate('config',"Adds a slide show link on the main page of the gallery.   Note for large galleries this may run very slowly and use a lot of system resources.") . '  ' .
		  gTranslate('config',"These settings do not apply to the low tech slideshow."),
	),
	'gallery_slideshow_type' => array(
		'prompt' => gTranslate('config',"Slideshow preferences"),
		'desc' => '<dl>'
			.'<dt>'. gTranslate('config',"Off") . '</dt>'
			.'<dd>'. gTranslate('config',"No slideshow link will appear.") . '</dd>'
			.'<dt>'. gTranslate('config',"Ordered") . '</dt>'
			.'<dd>'. gTranslate('config',"Images will display in the same order as they appear in the Gallery.") . '</dd>'
			.'<dt>'. gTranslate('config',"Random") . '</dt>'
			.'<dd>'. gTranslate('config',"Images will appear in a random order.") . '</dd>'
			.'</dl>',
		'choices' => array(
			'off' => gTranslate('config',"Off"),
			'ordered' => gTranslate('config',"Ordered"),
			'random' => gTranslate('config',"Random"),
		),
		'value' => 'off',
	),
	'gallery_slideshow_length' => array(
		'prompt' => gTranslate('config',"Slideshow length"),
		'desc' => gTranslate('config',"Number of images to display in slideshow.  Set to 0 or blank to display all images.  Set to a number if slideshow is random and you want to display only a selection of the images."),
		'type' => 'text',
		'value' => 20,
		'attrs' => array('size' => 5),
		'allow_empty' => true
	),
	'gallery_slideshow_loop' => array(
		'prompt' => gTranslate('config',"Allow slideshow to loop"),
		'desc' => sprintf(gTranslate('config',"This option will allow visitors to have your slideshows repeat until they close the window or go to another page.  Set it to %s if you want to save bandwidth."), "<b>".gTranslate('config',"No")."</b>"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes'
	),
	'slideshowMode' => array(
		'prompt' => gTranslate('config',"Slideshow Mode"),
		'desc' => gTranslate('config',"This allows you to set the default mode for the slideshow.  &quot;Java Applet&quot; requires that the user has the Java Runtime Environment installed in their browser, but allows for fullscreen viewing. &quot;JavaScript&quot; is supported by all modern browsers without extra plugins, but is more limited than the Java Applet."),
		'choices' => array(
			'applet' => gTranslate('config',"Java Applet"),
			'high' => gTranslate('config',"JavaScript")
		),
		'value' => 'high'
	),
	'group7_end' => array (
		'type' => 'group_end',
	),
	'group_comments' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Comments"),
	),
	'comments_enabled' => array(
		'prompt' => gTranslate('config',"Enable commenting"),
		'desc' => gTranslate('config',"This option will enable or disable the possibility for your users to make comment.  If you set this to &quot;no&quot; all comment functionality is disabled."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes'
	),
	'comments_indication' => array(
		'prompt' => gTranslate('config',"Indicate if there are comments available"),
		'desc' => '<dl>'
			. '<dt>' . gTranslate('config',"Off") . '</dt>'
			. '<dd>' . gTranslate('config',"No indication that an item has comments.") . '</dd>'
			. '<dt>' . gTranslate('config',"Photos") . '</dt>'
			. '<dd>' . gTranslate('config',"Images that have comments will be marked.") . '</dd>'
			. '<dt>' . gTranslate('config',"Albums"). ' </dt>'
			. '<dd>' . gTranslate('config',"Albums and sub-albums that have comments will be marked.") . '</dd>'
			. '<dt>' .gTranslate('config',"Both") . '</dt>'
			. '<dd>' . gTranslate('config',"Images and albums that have comments will be marked.") . '</dd>'
			. '</dl>',
		'choices' => array(
			'off' => gTranslate('config',"Off"),
			'photos' => gTranslate('config',"Photos"),
			'albums' => gTranslate('config',"Albums"),
			'both' => gTranslate('config',"Both"),
		),
		'value' => 'photos',
	),
	'comments_indication_verbose' => array(
		'prompt' => gTranslate('config',"Display date of last comment?"),
		'desc' => sprintf(gTranslate('config',"If an item has comments, display date of last comment, rather than a %s."), '"*"'),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'comments_anonymous' => array(
		'prompt' => gTranslate('config',"Anonymous comments from known users?"),
		'desc' => sprintf(gTranslate('config',"When logged in, comments will automatically use format described below, unless this is set to %s."),
		  '<b>'. gTranslate('config',"Yes"). '</b>'),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'comments_addType' => array(
		'prompt' => gTranslate('config',"Add comments inside the photo page"),
		'desc' => gTranslate('config',"Do you want to have the possibility to add comments inside the view photo page, or do you prefer a link to a popup"),
		'choices' => array (
			'popup' => gTranslate('config',"Popup"),
			'inside' => gTranslate('config',"Inside")
		),
		'value' => 'popup'
	),
	'comments_length' => array(
		'prompt' => gTranslate('config',"Maximum length of comments"),
		'desc' => gTranslate('config',"Enter a suitable value for the maximum length of comments to prevent abuse. Gallery limits comments to no more than 1000 characters.  This value will be used if less than 1000."),
		'type' => 'text',
		'must-be-integer' => 1,
		'value' => 300
	),
	'comments_overview_for_all' => array(
		'prompt' => gTranslate('config',"Can every permitted user see a comments overview"),
		'desc' => gTranslate('config',"Do you want to allow every user who is allowed to view comments (set in permission) to see a complete overview of all comments of an Album? Set this to &quot;no&quot; if only admin and owners can use this feature."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'group_comments_end' => array (
		'type' => 'group_end',
	),
	'group_watermark' => array(
		'type'		=> 'group_start',
		'default'	=> 'none',
		'title'		=> gTranslate('config',"Watermarking"),
		'desc'		=> ($enablewatermark == 'yes') ?
		  gTranslate('config',"Choose whether you want to enable or disable watermarking. And set the defaults") :
		  '<span class="g-attention">' . gTranslate('config',"Watermarking is not possible in this installation !") . '</span>'
	),
	'watermarkDir' => array(
		'prompt' => gTranslate('config',"Watermark directory"),
		'desc' => sprintf(gTranslate('config',"The full path on physical disk to the directory where your watermark images will be stored.  This directory may contain .png, .tif, and .gif images used to overlay visible watermarks on photos. These formats are supported because they may contain transparency or alpha channels.  <b>Leaving this blank will disable watermarking support.</b> Note that this directory needs to have read access by the user who is running the web server (in your case this user is %s)."),
		  '<b>'. $webserver_user . '</b>'),
		'value' => '',
		'enabled' => $enablewatermark,
		'optional' => 1,
		'filename' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50)
	),
	'watermarkSizes' => array(
		'prompt' => gTranslate('config',"Which Imagetype is watermarked"),
		'desc' => gTranslate('config',"Here you can choose which of the images should be watermarked per default."),
		'value' => 0,
		'enabled' => $enablewatermark,
		'optional' => 1,
		'choices' => array(
			0 => gTranslate('config',"Watermark both resized and full images"),
			1 => gTranslate('config',"Watermark resized image only"),
			2 => gTranslate('config',"Watermark full sized image only")
		),
		'value' => 0,
	),
	'group_watermark_end' => array (
		'type' => 'group_end',
	),
	'group_stats' => array(
		'type'		=> 'group_start',
		'default' 	=> 'none',
		'title'		=> gTranslate('config',"Statistics"),
		'desc'		=> gTranslate('config',"Caching improves performance for a large gallery at the cost of potentially displaying photos that are out of order or with out of date rating information.") . "\n<br>".
		  gTranslate('config',"There's a different cache for each stats type supported with the exception of random."). "\n<br>".
		  gTranslate('config',"The caches can be refreshed by reloading the stats page and specifying the url variable 'refreshcache'."). "\n<br>".
		  gTranslate('config',"Caches will also refresh themselves after they have expired."). "\n<br>".
		  gTranslate('config',"The expire time in seconds is specified for each cache below."). "\n<br>".
		  gTranslate('config',"If the expire time is set to -1 then the cache will not auto refresh.")
	),

	'stats_foruser'	=> array(
		'prompt'		=> gTranslate('config',"Available Stats for 'normal' users"),
		'desc'			=> gTranslate('config',"Gallery can give a lot of statistics for admins, however you can enable some of them to normal users.") ."\n<br>".
		  sprintf(gTranslate('config',"For every selected item Gallery will create a link on the mainpage. The stats are based on the defaults defined in %s."), "'<i>includes/stats/stats.inc.php</i>'"),
		'multiple_choices' 	=> $stats_choices,
		'optional'	=> 1
	),
	'stats_viewsCacheOn'	=> array(
		'prompt' => gTranslate('config',"View cache"),
		'choices' => array(
			1 => gTranslate('config',"On"),
			0 => gTranslate('config',"Off")
		),
		'value' => 0,
	),
	'stats_viewsCacheExpireSecs' => array(
		'prompt' => gTranslate('config',"View cache expiration"),
		'type' => 'text',
		'must-be-number' => 1,
		'value' => 60
	),
	'stats_commentsCacheOn'	=> array(
		'prompt' => gTranslate('config',"Comment cache"),
		'choices' => array(
			1 => gTranslate('config',"On"),
			0 => gTranslate('config',"Off")
		),
		'value' => 0,
	),
	'stats_commentsCacheExpireSecs' => array(
		'prompt' => gTranslate('config',"Comment cache expiration"),
		'type' => 'text',
		'must-be-number' => 1,
		'value' => 600
	),
	'stats_dateCacheOn'	=> array(
		'prompt' => gTranslate('config',"Date cache"),
		'choices' => array(
			1 => gTranslate('config',"On"),
			0 => gTranslate('config',"Off")
		),
		'value' => 0,
	),
	'stats_dateCacheExpireSecs' => array(
		'prompt' => gTranslate('config',"Date cache expiration"),
		'type' => 'text',
		'must-be-number' => 1,
		'value' => -1
	),
	'stats_votesCacheOn'	=> array(
		'prompt' => gTranslate('config',"Vote cache"),
		'choices' => array(
			1 => gTranslate('config',"On"),
			0 => gTranslate('config',"Off")
		),
		'value' => 0,
	),
	'stats_votesCacheExpireSecs' => array(
		'prompt' => gTranslate('config',"Vote cache expiration"),
		'type' => 'text',
		'must-be-number' => 1,
		'value' => 3600
	),
	'stats_ratingsCacheOn'	=> array(
		'prompt' => gTranslate('config',"Rating cache"),
		'choices' => array(
			1 => gTranslate('config',"On"),
			0 => gTranslate('config',"Off")
		),
		'value' => 0,
	),
	'stats_ratingsCacheExpireSecs' => array(
		'prompt' => gTranslate('config',"Rating cache expiration"),
		'type' => 'text',
		'must-be-number' => 1,
		'value' => 3600
	),
	'stats_cDateCacheOn'	=> array(
		'prompt' => gTranslate('config',"Capturedate cache"),
		'choices' => array(
			1 => gTranslate('config',"On"),
			0 => gTranslate('config',"Off")
		),
		'value' => 0,
	),
	'stats_cDateCacheExpireSecs' => array(
		'prompt' => gTranslate('config',"Capturedate cache expiration"),
		'type' => 'text',
		'must-be-number' => 1,
		'value' => -1
	),
	'group_stats_end' => array(
		'type'		=> 'group_end'
	),
	'group_blocks' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config', "Blocks"),
		'desc' => gTranslate('config', "These settings are for the blocks provided by Gallery."),
	),
	'subgroup_randomBlock' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"RandomBlock Settings"),
		'display' => 'inline'
	),
	'blockRandomAlbum' => array(
		'prompt' => gTranslate('config',"RandomBlock Source"),
		'desc' => gTranslate('config',"You can either use a random pictures from the whole Gallery or from certain albums."),
		'value' => 'none',
		'optional' => true,
		'type' => 'hidden' //	 'choices' => array(),
	),
	'blockRandomFrame' => array(
		'prompt' => gTranslate('config',"Frame around random photo."),
		'desc' => availableRandomBlockFrames(),
		'choices' => available_frames(false, true),
		'value' => 'none'
	),
	'blockRandomFrameBorderWidth' => array(
		'prompt' => gTranslate('config',"Width of solid/dotted frameborder around random photo."),
		'desc' => '',
		'type' => 'text',
		'must-be-integer' => 1,
		'must-not-be-zero' => 1,
		'optional' => 1,
		'value' => 1
	),
	'blockRandomFrameBorderColor' => array(
		'prompt' => gTranslate('config',"Color of solid/dotted frameborder around random photo."),
		'desc' => '',
		'value' => 'black',
		'type' => 'colorpicker'
	),
	'blockRandomCache' => array(
		'prompt' => gTranslate('config',"Random Block cache time"),
		'desc' => gTranslate('config',"This is the amount of time that the Random Block will cache data about your photos and albums, speeding up requests for random photos.  The more often that your albums are modified, the lower you should set this value to ensure that new photos are chosen quickly, and to prevent attempts to retrieve photos which may have been deleted. The cache is automatically regenerated when a user views the random block after the time limit has expired."),
		'value' => 86400,
		'choices' => array(
			86400 => gTranslate('config',"24 Hours"),
			43200 => gTranslate('config',"12 Hours"),
			21600 => gTranslate('config',"6 Hours"),
			1440 => gTranslate('config',"1 Hour"),
			720 => gTranslate('config',"30 Minutes")
		)
	),
	'blockRandomAttempts' => array(
		'prompt' => gTranslate('config',"Random Block retrieval attempts"),
		'desc' => gTranslate('config',"This variable controls the number of times that the random block will attempt to retrieve an image from Gallery.  The larger the value, the less likely a 'No Photo Chosen' will be returned, but it increases the time it takes for the Random Block to load."),
		'value' => 2,
		'must-be-integer' => 1,
		'must-not-be-zero' => 1,
		'type' => 'text',
	),
	'subgroup_randomBlock_end' => array (
		'type' => 'subgroup_end',
	),
	'subgroup_featureBlock' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"Featured-photo-block settings"),
		'display' => 'inline'
	),
	'featureBlockFrame' => array(
		'prompt' => gTranslate('config',"Frame around featured photo."),
		'desc' => availableRandomBlockFrames(),
		'choices' => available_frames(false, true),
		'value' => 'none'
	),
	'featureBlockFrameBorderWidth' => array(
		'prompt' => gTranslate('config',"Width of solid/dotted frameborder around featured photo."),
		'desc' => '',
		'type' => 'text',
		'must-be-integer' => 1,
		'must-not-be-zero' => 1,
		'optional' => 1,
		'value' => 1
	),
	'featureBlockFrameBorderColor' => array(
		'prompt' => gTranslate('config',"Color of solid/dotted frameborder around featured photo."),
		'desc' => '',
		'value' => 'black',
		'type' => 'colorpicker'
	),
	'subgroup_featureBlock_end' => array (
		'type' => 'subgroup_end',
	),
	'group_blocks_end' => array(
		'type'		=> 'group_end'
	),
	'group_technical' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Technical"),
		'desc' => gTranslate('config',"The settings in this section are intended for advanced users only.") .
		  '<div class="g-attention">'.
		  gTranslate('config',"Be aware that settings changed on this tab may have severe performance or security consequences and do so at your own risk.") .
		  '</div>'
	),
	'debuglevel' => array(
		'prompt' => gTranslate('config',"Debug Level"),
		'desc' => gTranslate('config',"Turn debug on so that you can diagnose problems?") .
			'<p style="font-weight: bold;">'. gTranslate('config',"Note: Dont leave this option on in production systems! Else you might show users more information about your Gallery and system than you want!") . '</p>',
		'choices' => array(
			0 => gTranslate('config',"No Debug"),
			1 => gTranslate('config',"Normal"),
			2 => gTranslate('config',"High (former Debug 'on')"),
			3 => gTranslate('config',"Paranoid")),
		'value' => 0,
	),
	'skipRegisterGlobals' => array(
		'prompt' => gTranslate('config','Disable register_globals check'),
		'desc' => sprintf(gTranslate('config','<b>Be aware that changing this setting potentially decreases the security of your Gallery, and we may or may not support you if you encounter issues that don\'t occur when this is turned off.</b><br><br>Enabling this causes %s to ignore register_globals being turned on.  %s normally tries to unset all variables created by register_globals to help enforce security.  If a script that you run requires register_globals to be on, and will not cooperate with %s, set this to &quot;yes&quot;.'), Gallery(), Gallery(), Gallery(), Gallery()),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'timeLimit' => array(
		'prompt' => gTranslate('config',"Time Limit"),
		'desc' => gTranslate('config',"The maximum amount of time in seconds any single operation should take.  If you find that you are getting the <b>Maximum execution time exceeded</b> error message, you can try making this a larger number.  Be warned that if you make this too large you <i>might</i> have a process that is stuck for a very long time without doing anything.  The default value is 30 which is pretty reasonable."),
		'type' => 'text',
		'must-be-integer' => 1,
		'must-not-be-zero' => 1,
		'value' => 30,
		'attrs' => array('size' => 5),
	),
	'IM_HQ' => array(
		'prompt' => gTranslate('config',"ImageMagick high quality"),
		'desc' => gTranslate('config',"If this setting is 'yes' and you use ImageMagick then resized Images will have a slightly(!) higher quality, but with the drawback of a higher load and a higher creation time."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes'
	),

	'cacheExif' => array(
		'prompt' => "<b>(" . gTranslate('config',"Advanced") . ")</b><br> ". gTranslate('config',"Cache EXIF data"),
		'desc' => gTranslate('config',"The EXIF data extracted from JPEG files can be cached after it is read the first time.  The tradeoff is that if you cache this data it's possible for your album data files to get quite large which may slow down viewing your albums.  But, if you don't cache this data then it needs to be retrieved every time any user looks at the properties of a photo which can also be slow."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),

	'devMode' => array(
		'prompt' => gTranslate('config',"Developer Mode"),
		'desc' => gTranslate('config',"Turn on developer mode to see all PHP warnings and notices.  Enabling this is not recommended for users that aren't familiar with PHP."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'useSyslog' => array(
		'prompt' => gTranslate('config',"Use Syslog"),
		'desc' => gTranslate('config',"Report user login, logout, and serious errors via syslog (UNIX) or System Event Manager (Windows)"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'use_flock' => array(
		'prompt' => sprintf(gTranslate('config',"Use %s"), " flock()"),
		'desc' => gTranslate('config',"Gallery uses a locking system called flock() for added protection against data corruption.  This is not supported on all platforms.  If you're seeing errors like <i>Could not acquire lock</i> then set this value to <i>no</i>."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes'
	),
	'expectedExecStatus' => array(
		'prompt' => gTranslate('config',"Status code"),
		'desc' => gTranslate('config',"The status code Gallery expects to see after a successful <i>exec()</i> call.  Gallery will try to figure this out on its own so you probably won't need to change this value unless you have problems."),
		'value' => detect_exec_status(),
		'type' => 'text',
		'attrs' => array('size' => 5)
	),
	'sessionVar' => array(
		'prompt' => gTranslate('config',"Session variable"),
		'desc' => gTranslate('config',"This is the session variable used to contain all Gallery session information.  Change this if there is a conflict between Gallery and another PHP app on your website."),
		'value' => 'gallery_session',
		'type' => 'text',
		'attrs' => array('size' => 20),
	),
	'group_technical_end' => array (
		'type' => 'group_end',
	),
	'group_rss' => array(
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"RSS Publisher"),
		'desc' => gTranslate('config',"Options for the Gallery RSS Publisher"),
	),
	'rssEnabled' => array(
		'prompt' => gTranslate('config',"Enable RSS Generator"),
		'desc' => gTranslate('config',"Enable or Disable the Gallery RSS Generator"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")),
		'value' => 'yes',
	),
	'rssMode' => array(
		'prompt' => gTranslate('config',"RSS Content"),
		'desc' => gTranslate('config',"Specifies which information is to be included in your RSS feed."),
		'choices' => array(
		'basic' => gTranslate('config',"Basic information only"),
		'highlight' => gTranslate('config',"Album highlight"),
		'thumbs' => gTranslate('config',"All image thumbnails"),
		'thumbs-with-captions' => gTranslate('config',"All image thumbnails and captions")),
		'value' => 'basic',
	),
	'rssHighlight' => array(
		'prompt' => gTranslate('config',"RSS Highlight"),
		'desc' => gTranslate('config',"Specify which album will be displayed as the icon for all feed entries: An empty field means that there will be no default highlight.  '*' uses the most recently update album's highlight.  Specifying an album name will use that album's highlight image."),
		'type' => 'text',
		'attrs' => array('size' => 30),
		'optional' => 1,
	),
	'rssMaxAlbums' => array(
		'prompt' => gTranslate('config',"Maximum Albums Returned"),
		'desc' => gTranslate('config',"This value is the maximum number of albums that the RSS feed will display to the user. Only the most recently modified albums are chosen for this value."),
		'value' => 25,
		'type' => 'text',
		'must-be-integer' => 1,
		'must-not-be-zero' => 1,
	),
	'rssVisibleOnly' => array(
		'prompt' => gTranslate('config',"Visible Albums Only"),
		'desc' => gTranslate('config',"Specifies whether hidden albums should be included in the RSS feed.  This is not advised unless you are the only Gallery user.  Other users could become very upset if their hidden images become displayed to the public."),
		'choices' => array(
			'yes' => gTranslate('config',"Display Visible Albums Only"),
			'no' => gTranslate('config',"Display Hidden Albums")),
		'value' => 'yes',
	),
	'rssDCDate' => array(
		'prompt' => gTranslate('config',"Use dc:date"),
		'desc' => gTranslate('config',"(Advanced) If users complain about their RSS readers being unable to process the RSS Feed, try enabling this option.  Including dc:date is not RSS 2.0 compliant, but some feed readers require it anyway. Turning this on will cause your feed to not validate as RSS 2.0."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")),
		'value' => 'no',
	),
	'rssBigPhoto' => array(
		'prompt' => gTranslate('config',"Use Big Photo"),
		'desc' => gTranslate('config',"(Advanced) If users complain about their RSS readers corrupting the channel thumbnail, you can turn this feature on.  Turning this on will cause your feed to not validate as valid RSS 2.0."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")),
		'value' => 'no',
	),
	'rssPhotoTag' => array(
		'prompt' => gTranslate('config',"Use Photo Tag"),
		'desc' => gTranslate('config',"(Advanced) Turning this off will cause two RSS extensions which allow for images representing items to be removed from the feed.  Leaving this enabled should cause no problems - disabling it is a personal preference."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")),
		'value' => 'yes',
	),
	'group_rss_end' => array(
		'type' => 'group_end'
	),
	'group_uploadPaths' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Paths for upload from server"),
	),
	'uploadPaths' => array(
		'prompt' => gTranslate('config', "Allowed paths for upload from Server"),
		'desc' => gTranslate('config', "Specify the legal directories on the local server where a user can store files and then upload them into Gallery using the 'From local Server' method. The paths you enter here and all the files and directories under those paths will be available to any Gallery user who has upload privileges, so you should limit this to directories that won't contain sensitive data (eg. /tmp or /usr/ftp/incoming)"),
		'value' => '',
		'type' => 'text',
		'multiInput' => true,
		'remove_empty' => true,
		'optional' => 1,
		'autocomplete' => 'path'

	),
	'group_uploadPaths_end' => array(
		'type' => 'group_end'
	),
	'group_captcha' => array (
		'type' => 'group_start',
		'default' => 'none',
		'title' => gTranslate('config',"Captcha"),
		'desc' => gTranslate('config', "Protect your Gallery against comment spam with captcha.") .
				  '<br>' .
				  gTranslate('config', "Gallery creates captha images that contains letter and number that have to be entered. These images are not readable by robots.") .
				  '<br>' .
				  gTranslate('config', "There are more settings, but those are in a separate file. For details look into includes/captcha/captcha_init.php")
	),
	'enableCaptcha' => array(
		'prompt' => gTranslate('config', "Enable Captcha ?"),
		'desc' => '',
		'value' => 'no',
		'choices' => array(
						'no'		=> gTranslate('config', "No"),
						'yes'		=> gTranslate('config', "Yes, for everybody"),
						'anonymous'	=> gTranslate('config', "Yes, but only for anonymous users.")
		),
		'optional' => 1,
		'enabled' => gdAvailable() ? 'yes' : 'no'
	),
	'captchaDisableMessage' => array(
		'type' => 'message',
		'message' => gallery_error(gTranslate('config', "Your PHP does not support the GD library, thus the captcha images can not be created. Captcha support will be disabled.")),
		'enabled' => gdAvailable() ? 'no' : 'yes'
	),
	'group_captcha_end' => array(
		'type' => 'group_end'
	),
	// Keep these two at the end of the list because they're hidden
	'userDir' => array(
		'desc' => "Filesystem location of user information",
		'prompt' => ".users directory",
		'type' => 'hidden',
		'value' => '',
		'eval' => '"{$gallery->session->configForm->albumDir}/.users"',
		'persistent' => 1,
		'filename' => 1
	),
  )
);

?>
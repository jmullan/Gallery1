<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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

require_once(GALLERY_BASE . '/includes/stats/stats.inc.php');
require_once(GALLERY_BASE . '/includes/definitions/services.php');

$stats_choices = array();
foreach ($stats['types'] as $key => $trash) {
	if(!empty($stats['types'][$key]['linktext'])) {
		$stats_choices[$key] = $key;
	}
}

$locale_check = check_locale();
/* $elawisyl <=> Enable LAnguages WIthout SYstem Locales
** is set to "no" when we have no system locales and are in linux
** "no" disables all language settings
*/

if ($locale_check == NULL) {
	$elawisyl = 'no';
} else {
	$elawisyl = 'yes';
}

$netpbm_check = check_graphics();
$IM_check = check_graphics_im();

/* If all watermarking/overlay applications are missing */
if ( isset($netpbm_check[2]['pnmcomp']) && isset($netpbm_check[2]['pamcomp']) && isset($IM_check[2]['composite']) ) {
	$enablewatermark = 'no';
} else {
	$enablewatermark = 'yes';
}

if (function_exists('posix_getpwuid')) {
	$rec = @posix_getpwuid(posix_getuid());
	$webserver_user = $rec['name'];
} else {
	$whoami = locateFile('whoami');
	if ($whoami) {
		fs_exec($whoami, $results, $status);
		$webserver_user = $results[0];
	} else {
		$webserver_user = gTranslate('config',"unknown");
	}
}

$common_messages['getting-netpbm'] = '<br>'.
  sprintf(gTranslate('config',"Gallery prefers NetPBM version 9.9 and up.  You can install just the binaries Gallery needs from the %sGallery Download Page%s. Or, you can compile and install the entire NetPBM package from the %sNetPBM website%s."),
  "<a href=\"http://sourceforge.net/projects/gallery\">",
  '</a>',
  "<a href=\"http://sourceforge.net/projects/netpbm\">",
  '</a>')  .
  '<br><span class="emphasis">' . gTranslate('config',"Note:"). '</span> ' .
  gTranslate('config',"You can install the binaries from the Gallery site even if you don't have root access on your box!  If you can't get it working, try leaving the NetPBM path blank and using ImageMagick instead.");

$common_messages['fail-netpbm-partial'] =
  gTranslate('config',"I've found part of NetPBM, but some critical components are missing (see below).  Perhaps your version of NetPBM is out of date.") . '  ' .
  $common_messages['getting-netpbm'];

$common_messages['getting-imagemagick'] = '<br>'.
  sprintf(gTranslate('config',"Gallery prefers ImageMagick version 5.4.8 and up.  You can compile and install the entire ImageMagick package from the %sImageMagick website%s." ),
  '<a href="http://www.imagemagick.org">', '</a>') .
  '<br><span class="emphasis">' . gTranslate('config',"Note:"). '</span> ' .
  gTranslate('config',"The site also has binaries available for assorted operating systems.  If you can't get it working, try leaving the ImageMagick path blank and using NetPBM instead.");

$common_messages['fail-imagemagick-partial'] =
  gTranslate('config',"I've found part of ImageMagick , but some critical components are missing (see below).") . '  ' .
  $common_messages['getting-imagemagick'];

$common_messages['open-basedir-active'] =
  '<span class="emphasis">' . gTranslate('config',"Note:"). '</span>'.
  sprintf(gTranslate('config',"Your PHP installation is configured with the %s restriction.   This may make it difficult for Gallery to detect and verify your binaries, even if you've already successfully used the Config. Wizard in the past.  Don't worry too much if this page gives warnings; your settings will still be saved properly."),
  "<b><a href=\"http://www.php.net/manual/en/features.safe-mode.php#ini.open-basedir\" target=\"_blank\">open_basedir</a></b>" );

$checklist = array(
	'gallery_version' => array(
		'prompt' => gTranslate('config',"Gallery version"),
		'optional' => 1,
		'desc' => sprintf(gTranslate('config',"Check to see how old %s version %s is."),
		  Gallery(),
		  "<i>$gallery->version</i>"),
		'func' => 'check_gallery_version'
	),
	'php' => array(
		'prompt' => gTranslate('config',"PHP version"),
		'desc' => gTranslate('config',"Check to see if you are running a supported Version of PHP.") .'<br>'.  gTranslate('config',"Due to bugs and missing functionality in some PHP versions, the minimum PHP version for Gallery 1.5 and newer is at least PHP 4.1.0"),
		'func' => 'check_php',
		'fail-too-old' => sprintf(gTranslate('config',"You're running PHP version %s.  You need to upgrade to PHP %s."), phpversion(), $MIN_PHP_MAJOR_VERSION),
		'fail-incompatible' => sprintf(gTranslate('config',"You're running PHP version %s which has known bugs that cause Gallery to fail."),  phpversion()),
		'fail-buggy__FILE__' => gTranslate('config',"It seems your PHP has a problem with __FILE__, please upgrade to a recent Version"),
		'serious' => 1,
	),
	'register_globals' => array(
		'prompt' => gTranslate('config',"Register globals"),
		'desc' => gTranslate('config',"Gallery highly recommends that the PHP option 'register_globals' be turned off, for security purposes.  Some software, notably the 'Geeklog' CMS, requires that 'register_globals' be enabled."),
		'func' => 'check_register_globals',
		'warn-register_globals' => gTranslate('config',"The PHP option 'register_globals' is enabled on your server. Gallery highly recommends that you disable 'register_globals' unless it is required by software on your site.  Gallery does not officially support 'register_globals' being enabled, and will attempt to disable it internally."),
		'serious' => 1,
		'optional' => 1,
	),
	'safe_mode' => array(
		'prompt' => gTranslate('config',"PHPs safe mode disabled"),
		'desc' => gTranslate('config',"Check to see if PHP is running in <b>safe_mode</b>.  Safe Mode is a PHP security feature commonly used in shared environments.  Gallery cannot run when Safe Mode is enabled because it imposes too many restrictions."),
		'func' => 'check_safe_mode',
		'fail-safe-mode' => sprintf(gTranslate('config',"<b>safe_mode</b> is enabled on this server.  You must disable it before you can use Gallery.  Refer to the FAQ, the forums, and the help page on the %sGallery website%s and the %sPHP website%s for assistance."),
		  '<a href="http://gallery.sourceforge.net">',
		  '</a>',
		  '<a href="http://www.php.net/manual/en/features.safe-mode.php">',
		  '</a>') .
		  '<br><br>' .
		  '<span class="emphasis">' . gTranslate('config',"Note:"). '</span>'.
		  sprintf(gTranslate('config',"In some rare circumstances, Gallery thinks that Safe Mode is on, when it's really off.  You can see for yourself by looking in the <i>Local</i> column of the <i>safe_mode</i> row in the %sPHP Info%s page.  If it says 'Off' or '0' or 'undefined' then it's possible that Gallery made a mistake and you can continue.  If not, then you must resolve this problem before you can use Gallery."),
		  '<a href="phpinfo.php">',
		  '</a>'),
		'optional' => 1,
		'serious' => 1
	),
	'exec' => array(
		'prompt' => gTranslate('config',"PHPs exec() function enabled"),
		'desc' => gTranslate('config',"The <b>exec()</b> function is what Gallery uses to launch programs such as NetPBM, ImageMagick, and jhead.  If <b>exec()</b> is disabled on your server then Gallery will not be able operate."),
		'func' => 'check_exec',
		'fail-exec' => gTranslate('config',"The <b>exec()</b> function is disabled on your server.  Gallery will not function with this PHP configuration."),
		'serious' => 1,
	),
	'htaccess' => array(
		'prompt' => gTranslate('config',".htaccess processed by apache"),
		'desc' => gTranslate('config',"Allow the Gallery <b>.htaccess</b> file to set application specific configuration options for the Gallery application.  This is controlled by the webserver owner so it's not always possible for you to change it.  The main drawbacks to not having this are that you may not be able to upload files > 2MB (e.g. big ZIP files or movies)."),
		'func' => 'check_htaccess',
		'fail-htaccess' => gTranslate('config',"Apache is not obeying the 'php_value' lines in your <b>.htaccess</b> file. Try entering the following into your web server's <b>httpd.conf</b> file:") .'<br><pre>' .
		  '&lt;Directory ' . GALLERY_BASE . "&gt;\n" .
		  "\tAllowOverride Options FileInfo\n" .
		  "&lt;/Directory&gt;</pre>\n" .
		  gTranslate('config',"If you are running PHP in CGI mode, this message is unavoidable."),
		'optional' => 1
	),
	'magic-quotes' => array(
		'prompt' => gTranslate('config',"PHPs magic quotes off"),
		'optional' => 1,
		'desc' => sprintf(gTranslate('config',"Check to see if %smagic quotes%s are enabled on your PHP server.  If they're on, you may notice strange behavior if you put ' (single-quote), &quot; (double quote) or &#92; (backslash) characters in titles or captions in Gallery."),
		  '<b><a href="http://www.php.net/manual/en/configuration.php#ini.magic-quotes-gpc" target="_blank">',
		  '</a></b>'),
		'func' => 'check_magic_quotes',
		'fail-magic-quotes' => sprintf(gTranslate('config',"Magic quotes are on.  Your webserver administrator can turn them off by putting %s in your <b>php.ini</b> file."),
		  ": <pre>\n\tmagic_quotes_gpc = Off</pre>")
	),
	'mod-rewrite' => array(
		'prompt' => gTranslate('config',"Apache mod_rewrite"),
		'desc' => gTranslate('config',"Check to see if your webserver is compiled with <b>mod_rewrite</b>.  This Apache module allows Gallery to use shorter URLs that are easier to read and look nicer when you mail them to your web-challenged relatives.  It's not an essential feature."),
		'func' => 'check_mod_rewrite',
		'fail-mod-rewrite' => gTranslate('config',"Either <b>mod_rewrite</b> is not installed or your <b>.htaccess</b> file is not enabled (see above).  Either way, we'll have to use longer URLs in the Gallery.  If you want to turn it on, first make sure that your <b>.htaccess</b> file is being obeyed. If it still doesn't work, you may need to reconfigure and rebuild Apache with this flag: ") .
		  "<pre>\t--enable-module=rewrite</pre>",
		'optional' => 1
	),
	'NetPBM' => array(
		'prompt' => gTranslate('config',"NetPBM installed"),
		'optional' => 1,
		'desc' => gTranslate('config',"Check to see if NetPBM is installed so that Gallery can manipulate images (rotate, resize, make thumbnails, etc.).  You only need to have either NetPBM <b>or</b> ImageMagick installed."),
		'func' => 'check_graphics',
		'fail-netpbm' => gTranslate('config',"I can't find NetPBM.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path to NetPBM by hand on the following page.  If it's not installed, you have two choices: " ) .
		  $common_messages['getting-netpbm'],
		'fail-netpbm-partial' =>
		  $common_messages['fail-netpbm-partial'] .
		  '<p><b> ' . gTranslate('config',"If NetPBM is installed and Gallery isn't finding it automatically that's OK&#8212;you can specify the path to NetPBM by hand on the following page.") .' </b>',
		  'open-basedir-note' => $common_messages['open-basedir-active']
	),
	'ImageMagick' => array(
		'prompt' => gTranslate('config',"ImageMagick installed"),
		'optional' => 1,
		'desc' => gTranslate('config',"Check to see if ImageMagick is installed so that Gallery can manipulate images (rotate, resize, make thumbnails, etc.).  You only need to have either ImageMagick <b>or</b> NetPBM installed."),
		'func' => 'check_graphics_im',
		'fail-imagemagick' => gTranslate('config',"I can't find ImageMagick.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path to ImageMagick by hand on the following page.  If it's not installed, you must install it.  ") .
		  $common_messages['getting-imagemagick'],
		'fail-imagemagick-partial' =>
		  $common_messages['fail-imagemagick-partial'] .
		  '<p><b> ' . gTranslate('config',"If ImageMagick is installed and Gallery isn't finding it automatically that's OK&#8212;you can specify the path to ImageMagick by hand on the following page.") .' </b>',
		  'open-basedir-note' => $common_messages['open-basedir-active']
	),
	'exif' => array(
		'prompt' => gTranslate('config',"Jhead installed"),
		'optional' => 1,
		'desc' => gTranslate('config',"Check to see if <b>jhead</b> is installed so that Gallery can examine EXIF headers embedded in images created with most digital cameras."),
		'func' => 'check_exif',
		'fail-exif' => sprintf(gTranslate('config',"I can't find <b>jhead</b>.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path by hand on the following page.  If it's not installed, you can install it yourself. Jhead is a public domain EXIF parser.  Source, linux binaries, and windows binaries can be found at the %sjhead homepage%s."),
		  '<a href="http://www.sentex.net/~mwandel/jhead/index.html">',
		  '</a>'),
		'open-basedir-note' => $common_messages['open-basedir-active']
		),
	'jpegtran' => array (
		'prompt' => gTranslate('config',"jpegtran installiert"),
		'optional' => 1,
		'desc' => gTranslate('config',"Check to see if <b>jpegtran</b> is installed so that Gallery can perform lossless rotations on JPEG images."),
		'func' => 'check_jpegtran',
		'fail-jpegtran' => sprintf(gTranslate('config',"I can't find <b>jpegtran</b>.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path by hand on the following page.  If it's not installed, you can install it yourself.  Jpegtran is a free tool distributed by the Independent JPEG Group as part of <i>libjpeg</i>.  Source may be found at the %sIJG homepage%s.  Binaries may be found at the %sJPEG Club website%s or in the <i>libjpeg</i> package of Linux distributions."),
		  '<a href="http://www.ijg.org">', '</a>',
		  '<a href="http://jpegclub.org/">', '</a>'),
		  'open-basedir-note' => $common_messages['open-basedir-active']
	),
	'gettext' => array (
		'prompt' => gTranslate('config',"Gettext support in PHP"),
		'optional' => 1,
		'desc' => gTranslate('config',"Check to see if PHP is compiled with <b>GNU gettext</b> support for fast translations."),
		'func' => 'check_gettext',
		'fail-gettext' => sprintf(gTranslate('config',"Your PHP does not have <b>GNU gettext</b> support installed.  To include it, you'll need to recompile PHP (see the %sPHP docs%s for more information). Multi-language support is still available in Gallery without <b>gettext</b>, but the pages may take slightly longer to load."),
		  '<a href="http://www.php.net/manual/en/ref.gettext.php">', '</a>')
	),
	'gallery-locale' => array (
		'prompt' => gTranslate('config',"Locales needed for Gallery"),
		'optional' => 1,
		'desc' => gTranslate('config',"Check to see what languages are available."),
		'func' => 'check_gallery_languages'
	),
	'missing-locales' => array (
		'prompt' => gTranslate('config',"missing locales"),
		'optional' => 1,
		'enabled' => (sizeof(gallery_languages()) > 1) ? 'yes' :'no',
		'desc' => gTranslate('config',"Testing to see which <b>system</b> locales (if any) are absent.") . '<br>'.
		  gTranslate('config',"These locales are required to use multilanguage capabilities of Gallery when your PHP has gettext support. They are also needed to format dates and times according to the selected language."),
		'func' => 'check_absent_locales',
		'serious' => ( $elawisyl == 'no' ) ? 1 : 0 ,
		),
	'check-versions' => array (
		'prompt' => gTranslate('config',"File integrity"),
		'desc' => gTranslate('config',"Checking to see if you have the latest versions of all Gallery files."),
		'func' => 'check_gallery_versions',
		'serious' => 1,
		'optional' => 1,
	),
);

$features = array(
	'zip' => array('zipinfo', 'unzip'),
	'rewrite' => array('GALLERY_REWRITE_OK', 'htaccess_file'),
	'mirror' => array('mirrorSites'),
);

$constants =  array_merge(array(
	'group_mandatory' => array (
		'type' => 'group_start',
		'name' => 'group_mandatory',
		'default' => 'inline',
		'title' => gTranslate('config',"General settings"),
		'contains_required' => true,
	),
	'galleryTitle' => array(
		'prompt' => gTranslate('config',"Gallery Title"),
		'desc' => gTranslate('config',"The name of your Gallery.  E.g. <i>My Cool Photos</i>"),
		'value' => gTranslate('config',"Gallery"),
		'type' => 'text',
		'attrs' => array('size' => 50)
	),
	'editPassword' => check_admins(),
	'useIcons' => array(
		'prompt' => gTranslate('config',"Use Icons ?"),
		'desc' => gTranslate('config',"You can choose wether you want to use Gallery icons, text only, or both. When you choose icons only, then there will be an onMousoverText."),
		'choices' => array(
			'both' => gTranslate('config',"Icons and text"),
			'no' => gTranslate('config',"Text only"),
			'yes' => gTranslate('config',"Icons only")
			),
		'value' => 'no'
	),
	'skinname' => array(
		'prompt' => gTranslate('config',"Skin Name"),
		'desc' => gTranslate('config',"Select a skin to use for Gallery. You now have a wide choice of &quot;skins&quot; that affect the look of every page of your Gallery.  You can have fun experimenting with the available skins. Choose &quot;None&quot; to use the look from v1.4.") .
		  '<br><a href="http://www.pownuke.com/galleryskins/">'. gTranslate('config',"Here you can get a more Gallery Skins by Drazan") .'</a>'.
		  available_skins(true),
		'choices' => available_skins(),
		'value' => 'none'
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
		),
	
	'group_mandatory_end' => array (
		'type' => 'group_end',
	),
	'groupLocations' => array (
		'type' => 'group_start',
		'name' => 'group_URLS',
		'default' => 'none',
		'title' => gTranslate('config',"Locations and URLs"),
		'contains_required' => true,
	),
	'albumDir' => array(
		'prompt' => gTranslate('config',"Album directory"),
		'desc' => '(<span class="attention">' . gTranslate('config',"REQUIRED") . '</span>) ' .
		  sprintf(gTranslate('config',"The full path on physical disk to the directory where your photos will be stored.  This directory should start off empty&#8212;Gallery will fill it.  Note that this directory needs to have write access by the user who is running the web server (in your case this user is %s)."),
		  '<b>'. $webserver_user . '</b>'),
		'value' => getenv('DOCUMENT_ROOT') . '/albums',
		'require-write-dir' => 1,
		'filename' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'required' => true,
	),
	'tmpDir' => array(
		'prompt' => gTranslate('config',"Temporary directory"),
		'desc' => '(<span class="attention">' . gTranslate('config',"REQUIRED") . '</span>) ' .
		  sprintf(gTranslate('config',"The filesystem path to a temporary directory.  E.g. %s or %s.  Note that if you have <b>open_basedir</b> configured on your system, then this temporary directory must be inside the <b>open_basedir</b> path!  In that case you may need to create the directory yourself and make sure that it's writable by the webserver process."),
		  '<i>/tmp</i>','<i>c:\\windows\\temp</i>'),
		'value' => getenv('TMP'),
		'filename' => 1,
		'require-write-dir' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'required' => true,
	),
	'photoAlbumURL' => array(
		'prompt' => "Gallery URL",
		'desc' => '(<span class="attention">' . gTranslate('config',"REQUIRED") . '</span>) ' .
		  sprintf(gTranslate('config',"The full URL to the Gallery web application. (e.g. %s) Gallery can operate with relative URLs, however some features, such as printing, may not function correctly."),
		  '<i>http://www.example.com/gallery</i>'),
		'value' => 'http://' . getenv('SERVER_NAME') . $GALLERY_URL,
		'no-trailing-slash' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50),
		'required' => true,
	),
	'albumDirURL' => array(
		'prompt' => gTranslate('config',"Albums URL"),
		'desc' => '(<span class="attention">' . gTranslate('config',"REQUIRED") . '</span>) ' .
		  sprintf(gTranslate('config',"The full URL to your album directory on your web server.  (e.g. %s) Gallery can operate with relative URLs, however some features, such as printing, may not function correctly."),
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
		'attrs' => array('size' => 50)
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
		'attrs' => array('cols' => 50, 'rows' => 5)
	),
	'groupLocations_end' => array (
		'type' => 'group_end',
	),
	
	'groupToolkits' => array (
		'type' => 'group_start',
		'name' => 'group_toolkits',
		'default' => 'none',
		'title' => gTranslate('config',"Toolkits / image processing"),
		'contains_required' => true,
	),
	'graphics' => array(
		'prompt' => gTranslate('config',"Graphics suite to use"),
		'desc' => gTranslate('config',"This setting tells Gallery which set of graphics tools you prefer it to use.  If Gallery says both are okay (on the first page) then this is simply user preference.  Otherwise go with the one that Gallery was able to find without error."),
		'choices' => array('NetPBM' => "NetPBM", 'ImageMagick' => "ImageMagick"),
		'value' => default_graphics(),
		'required' => true,
		'attrs' => array('id' => 'graphics', 'onChange' => 'toggleGraphics(this.value)')
	),
	'subgroup_ImageMagick' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"ImageMagick Settings"),
		'display' => (getCurrentGraphicTool() == 'ImageMagick') ? 'inline' : 'none'
	),
	'ImPath' => array(
		'optional' => 1,
		'prompt' => gTranslate('config',"Path to ImageMagick"),
		'desc' => gTranslate('config',"The path to the directory containing ImageMagick on your system."). '<br>'.
		  sprintf(gTranslate('config',"This path is only required if you choosed %s above. Otherwise you can leave it like it is, or empty."), "ImageMagick"),
		'verify-func' => 'check_graphics_im',
		'value' => locateDir('convert', isset($gallery->app->ImPath) ? $gallery->app->ImPath : ''),
		'fail-imagemagick' => gTranslate('config',"I can't find ImageMagick at the location you provided.") . '  ' .
		  $common_messages["getting-imagemagick"],
		'filename' => 1,
		'attrs' => array('size' => 50),
		'no-trailing-slash' => 1,
		'type' => 'text',
		'verify-func-takes-graphics-type' => true,
		'required' => true,
	),
	'subgroup_imagemagick_end' => array (
		'type' => 'subgroup_end',
	),
	'subgroup_NetPBM' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"NetPBM Settings"),
		'display' => (getCurrentGraphicTool() == 'NetPBM') ? 'inline' : 'none'
		),
	'pnmDir' => array(
		'optional' => 1,
		'prompt' => gTranslate('config',"Path to NetPBM"),
		'desc' => sprintf(gTranslate('config',"The path to the directory containing NetPBM on your system.  If you downloaded NetPBM from the Gallery download page, don't forget to make the files executable.  E.g. %s or %s or %s"),
		  '<i>/usr/local/netpbm</i>',
		  '<i>/usr/local/bin</i>',
		  '<i>c:\\apps\\netpbm</i>'). '<br>'.
		  sprintf(gTranslate('config',"This path is only required if you choosed %s above. Otherwise you can leave it like it is, or empty."), "NetPBM"),
		'value' => locateDir("jpegtopnm", isset($gallery->app->pnmDir) ? $gallery->app->pnmDir : ''),
		'verify-func' => 'check_graphics',
		'fail-netpbm' => gTranslate('config',"I can't find NetPBM at the location you provided.") . '  ' .
		  $common_messages['getting-netpbm'],
		'fail-netpbm-partial' => $common_messages['fail-netpbm-partial'],
		'filename' => 1,
		'attrs' => array('size' => 50),
		'no-trailing-slash' => 1,
		'type' => 'text',
		'verify-func-takes-graphics-type' => true,
		'required' => true,
		),
	'pnmtojpeg' => array(
		'prompt' => gTranslate('config',"PNM to JPEG converter"),
		'desc' => gTranslate('config',"This value is only relevant if you use NetPBM.  NetPBM uses a tool to encode its internal image format into JPEG.  This tool is called either <i>ppmtojpeg</i> or <i>pnmtojpeg</i> depending on the version of NetPBM that you have installed.  Check your NetPBM directory to see which one you have installed and specify it here.  If you aren't sure, just take a guess and we'll try to verify it for you."),
		'choices' => array('pnmtojpeg' => 'pnmtojpeg', 'ppmtojpeg' => 'ppmtojpeg'),
		'value' => 'pnmtojpeg'
	),
	'pnmcomp' => array(
		'prompt' => gTranslate('config',"PNM Composite (Overlay)"),
		'desc' => gTranslate('config',"This value is only relevant if you use NetPBM.  NetPBM uses a tool to overlay one image over another.  This tool is called either <i>pnmcomp</i> or <i>pamcomp</i> depending on the version of NetPBM that you have installed.  Check your NetPBM directory to see which one you have installed and specify it here.  If you aren't sure, just take a guess and we'll try to verify it for you."),
		'choices' => array('pnmcomp' => 'pnmcomp', 'pamcomp' => 'pamcomp'),
		'value' => 'pnmcomp',
	),
	'subgroup_netPBM_end' => array (
		'type' => 'subgroup_end',
	),
	'autorotate' => array(
		'prompt' => gTranslate('config',"Auto-rotate images"),
		'desc' => gTranslate('config',"Selecting this option has Gallery automatically attempt to rotate images on upload, based on the orientation information that is stored in JPEG EXIF data by some newer digital cameras.") . '<br>' .
		'<span class="attention">' . gTranslate('config',"Requires Jhead EXIF parser v.1.9 or greater (See optional tools).") . '</span>',
		'choices' => array('yes' => gTranslate('config',"Yes"), 'no' => gTranslate('config',"No")),
		'value' => 'yes'
	),
	'subgroup_quality' => array (
		'type' => 'subgroup',
		'title' => gTranslate('config',"JPEG Quality <b>(Advanced)</b>"),
		'desc' => gTranslate('config',"The JPEG file format trades off size versus quality.  Gallery can control the quality of the JPEGs that it produces when you resize, rotate or make thumbnails from JPEG images.  Higher quality images are larger in size than lower quality images.  However, in many cases there's a point at which raising the quality increases the size of the files without noticeably affecting the appearance of the image.  Setting this value to &quot;100&quot; is not recommended. If you notice that your thumbnails and resized images are larger than the originals and you don't like this, you can try lowering this value.  <b>Note</b> that changing this value will only affect images changed in the <i>future</i>."),
		'display' => 'inline'
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
		'value' => 70
	),
	'thumbJpegImageQuality' => array(
		'prompt' => gTranslate('config',"JPEG Quality for thumbs inside the albums"),
		'desc' => '',
		'choices' => array(
			50 => 50, 55 => 55, 60 => 60, 65 => 65,
			70 => 70, 75 => 75, 80 => 80, 85 => 85,
			90 => 90, 95 => 95, 100 => 100
		),
		'value' => 50
	),
	'subgroup_quality_end' => array (
		'type' => 'subgroup_end',
	),
	'groupToolkits_end' => array (
		'type' => 'group_end',
	),
	'group_embedded' => array (
		'type' => 'group_start',
		'name' => 'group_embedded',
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
		'value' => ''
	),
	'group_embedded_end' => array (
		'type' => 'group_end'
	),
	'group3' => array (
		'type' => 'group_start',
		'name' => 'group_mainpage',
		'default' => 'none',
		'title' => gTranslate('config',"Main Gallery page"),
	),
	'albumTreeDepth' => array(
		'prompt' => gTranslate('config',"Show the album tree ?"),
		'desc' => gTranslate('config',"If you turn this option on, you'll see a list of sub-albums when you're browsing the top level of your Gallery.  This will make navigating to a specific sub album much easier. You can choose whether or not you want to limit the depth of the tree to avoid severely cluttering the appearance of the top Gallery page."),
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
	),
	'microTree' => array(
		'prompt' => gTranslate('config',"Replace album tree with microthumbs"),
		'desc' => gTranslate('config',"If you have set album tree to anything but off, this will display microthumbs of the sub albums instead of the album tree."),
		'choices' => array('yes' => gTranslate('config',"Yes"), 'no' => gTranslate('config',"No")),
		'value' => 'no'
	),
	'highlight_size' => array(
		'prompt' => gTranslate('config',"Highlight size"),
		'desc' => gTranslate('config',"The target size (in pixels) of thumbnails shown in the Gallery page (highlight images)."),
		'value' => 200,
		'type' => 'text',
		'attrs' => array('size' => 5)
	),
	'highlight_ratio' => array(
		'prompt' => gTranslate('config',"Highlight Ratio"),
		'desc' => gTranslate('config',"The ratio in which the highlights are made. This affects only new highlights. For existing use 'rebuild highlights'"),
		'choices' => array('0' => gTranslate('config',"As the original image"), '1/1' => gTranslate('config',"Square highlight")),
		'value' => 0,
	),
	'showOwners' => array(
		'prompt' => gTranslate('config',"Show Owner"),
		'desc' => gTranslate('config',"Show album owner's name next to each album?"),
		'choices' => array('yes' => gTranslate('config',"Yes"), 'no' => gTranslate('config',"No")),
		'value' => 'no'
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
	'group5' => array (
		'type' => 'group_start',
		'name' => 'group_optionals',
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
		'attrs' => array('size' => 50)
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
		'attrs' => array('size' => 50)
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
		'attrs' => array('size' => 50)
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
		'attrs' => array('size' => 50)
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
		  '<a href="http://www.sentex.net/~mwandel/jhead/" target=_"blank">jhead</a>', '<i>/usr/local/bin/jhead</i>', '<i>c:\\bin\\jhead.exe</i>'),
		'value' => locateFile('jhead'),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => 'text',
		'attrs' => array('size' => 50)
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
		'attrs' => array('size' => 50)
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
		'attrs' => array ('size' => 50)
	),
	'subgroup_tools_other_end' => array (
		'type' => 'subgroup_end',
	),
	'group5_end' => array (
		'type' => 'group_end',
	),
	'group6' => array (
		'type' => 'group_start',
		'name' => 'group_languages',
		'default' => 'none',
		'title' => gTranslate('config',"Languages and locales"),
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
		'attrs' => array('size' => 20)
		),
	'dateTimeString' => array(
		'prompt' => "<b>(" . gTranslate('config',"Advanced") .")</b><br> ". gTranslate('config',"Format for date/time strings."),
		'desc' => sprintf(gTranslate('config',"This format string will be used to display %s in %s.  It is recommended to use %s which will change format depending on language choice.  However, if you wish to change this string, please look at the %sPHP documentation%s."),
		  gTranslate('config',"date with time"), Gallery(), "<b>%c</b>",
		  '<a href="'.gTranslate('config',"http://www.php.net/manual/en/function.strftime.php"). '" target="_blank">', '</a>') .  "<br>" .
		  sprintf(gTranslate('config',"For example, %s will display date and time like this: %s."), "<b>%H:%M, %b %d, %Y</b>", "<b>" . strftime("%H:%M, %b %d, %Y") . "</b>"),
		'value' => '%c',
		'type' => 'text',
		'attrs' => array('size' => 20)
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
		'name' => 'group_email',
		'default' => 'none',
		'title' => gTranslate('config',"Email and Registration"),
		'desc' => sprintf(gTranslate('config',"If you are having problems with email, please run the %s diagnostic."),
		'<a href="check_mail.php">' . gTranslate('config',"Check Email") . '</a>'),
	),
	'emailOn' => array(
		'prompt' => gTranslate('config',"Enable email"),
		'verify-func' => 'verify_email',
		'desc' => sprintf(gTranslate('config',"Turns email on for the whole gallery.  If you set this to %s then none of the rest of this section will apply."), '<i>' . gTranslate('config',"No") . '</i>' ) .  '<br>' .
		  '<span class="emphasis">' . gTranslate('config',"Note:") . '</span> ' .
		  gTranslate('config',"If you enable these settings you give your users the opportunity to let Gallery send them their lost password via email.") . '<br>'.
		  '<span class="attention">' . gTranslate('config',"You need to enable this setting if you want to enable Self Registration.") . '</span>',
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
	'selfReg' => array(
		'prompt' => gTranslate('config',"Allow visitors to self register."),
		'desc' => gTranslate('config',"Enabling this will allow visitors to your site to create their own login accounts.") . '<br>' .
		  '<span class="attention">' . gTranslate('config',"This option requires the Email feature to be enabled.") . '</span>',
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
		'prompt' => gTranslate('config',"Use non-local SMTP server."),
		'desc' => gTranslate('config',"Use a smtp server running on a different machine (ie: Your ISPs mail server) to process outgoing gallery email."). '<br>',
		'choices' => array(
			'no' => gTranslate('config',"No"),
			'yes' => gTranslate('config',"Yes")
		),
		'optional' => 1,
		'value' => 'no',
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
	'group_email_end' => array (
		'type' => 'group_end',
	),
	'group7' => array (
		'type' => 'group_start',
		'name' => 'group_slideshow',
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
		'name' => 'group_comments',
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
	'comments_display_name' => array(
		'prompt' => gTranslate('config',"Format for commenter names"),
		'desc' => gTranslate('config',"Commenter names of logged in users can be displayed in a variety of different ways"),
		'choices' => displayNameOptions(),
		'value' => '!!FULLNAME!! (!!USERNAME!!)',
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
	'group_watermark_start' => array(
		'type'		=> 'group_start',
		'name'		=> 'group_watermark',
		'default'	=> 'none',
		'title'		=> gTranslate('config',"Watermarking"),
		'desc'		=> ($enablewatermark == 'yes') ?
		  gTranslate('config',"Choose wether you want to enable or disable watermarking. And set the defaults") :
		  '<span class="attention">' . gTranslate('config',"Watermarking is not possible in this installation !") . '</span>'
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
	'group_stats_start' => array(
		'type'		=> 'group_start',
		'name'		=> 'group_stats',
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
	'group_technical' => array (
		'type' => 'group_start',
		'name' => 'group_technical',
		'default' => 'none',
		'title' => gTranslate('config',"Technical"),
		'desc' => gTranslate('config',"The settings in this section are intended for advanced users only.") .
		  '<br><span class="attention">'.
		  gTranslate('config',"Be aware that settings changed on this tab may have severe performance or security consequences and do so at your own risk.") .
		'<span>'
	),
	'debuglevel' => array(
		'prompt' => gTranslate('config',"Debug Level"),
		'desc' => gTranslate('config',"Turn debug on so that you can diagnose problems?") .'<br><span class="emphasis">'. gTranslate('config',"Note: Dont leave this option on in production systems! Else you might show users more information about your Gallery and system than you want!") . '</span>',
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
	'blockRandomCache' => array(
		'prompt' => gTranslate('config',"Random Block Cache Time"),
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
		'prompt' => gTranslate('config',"Random Block Retrieval Attempts"),
		'desc' => gTranslate('config',"This variable controls the number of times that the random block will attempt to retrieve an image from Gallery.  The larger the value, the less likely a 'No Photo Chosen' will be returned, but it increases the time it takes for the Random Block to load."),
		'value' => 2,
		'must-be-integer' => 1,
		'must-not-be-zero' => 1,
		'type' => 'text',
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
		'attrs' => array('size' => 5),
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
		'name' => 'group_rss',
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

$defaults = array(
	'group_layout' => array (
		'type' => 'group_start',
		'name' => 'group_layout',
		'default' => 'inline',
		'title' => gTranslate('config',"Layout")
	),
	'cols' => array(
		'prompt' => gTranslate('config',"Default Columns"),
		'desc' => gTranslate('config',"The default number of columns in an album."),
		'value' => 3,
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9),
	),
	'rows' => array(
		'prompt' => gTranslate('config',"Default Rows"),
		'desc' => gTranslate('config',"The default number of rows in an album."),
		'value' => 3,
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10),
	),
	'group_layout_end' => array (
		'type' => 'group_end',
	),
	'group_appearance' => array (
		'type' => 'group_start',
		'name' => 'group_appearance',
		'default' => 'none',
		'title' => gTranslate('config',"Appearance")
	),
	'bordercolor' => array(
		'prompt' => gTranslate('config',"Border color"),
		'desc' => sprintf(gTranslate('config',"Default color of (image) borders. You can learn about %scolors%s here."),
		  '<a href="http://www.permadi.com/tutorial/websafecolor/" target="_blank">', '</a>'),
		'value' => "black",
		'type' => 'colorpicker'
	),
	'border' => array(
		'prompt' => gTranslate('config',"Border width"),
		'desc' => gTranslate('config',"Default width of (image) borders."),
		'value' => 1,
		'choices' => array(0 => 0, 1 => 1, 2 => 2, 3 => 3)
	),
	'font' => array(
		'prompt' => gTranslate('config',"Default Font"),
		'desc' => gTranslate('config',"Default font face.  E.g. <i>arial</i> or <i>trebuchet</i>"),
		'value' => 'arial',
		'attrs' => array('size' => 10),
		'type' => 'text'
	),
	'group_appearance_end' => array (
		'type' => 'group_end',
	),
	'group3' => array (
		'type' => 'group_start',
		'name' => 'group_display',
		'default' => 'none',
		'title' => gTranslate('config',"Image Processing and Display")
	),
	'thumb_size' => array(
		'prompt' => gTranslate('config',"Thumbnail size"),
		'desc' => gTranslate('config',"The default target size (in pixels) of album thumbnails."),
		'value' => 150,
		'attrs' => array('size' => 10),
		'type' => 'text'
	),
	'thumb_ratio' => array(
		'prompt' => gTranslate('config',"Thumbnail Ratio"),
		'desc' => gTranslate('config',"The ratio in which the thumbnails are made"),
		'choices' => array(
			'0' => gTranslate('config',"As the original image"),
			'1/1' => gTranslate('config',"Square thumbs")
		),
		'value' => 0,
	),
	'resize_size' => array(
		'prompt' => gTranslate('config',"Resized image size"),
		'desc' => gTranslate('config',"When large images are added to an album, an intermediate version will automatically be generated at this size."),
		'value' => 640,
		'choices' => array(
			0 => gTranslate('config',"off"),
			400 => 400,
			500 => 500,
			600 => 600,
			640 => 640,
			700 => 700,
			800 => 800,
			1024 => 1024
		)
	),
	'resize_file_size' => array(
		'prompt' => gTranslate('config',"Resized JPEG/PNG images file size"),
		'desc' => gTranslate('config',"The default target size (in kilobytes) of resized JPEG/PNG images.  Including a value may slow down file uploads, but will speed up page viewing time especially for people with dialup connections.  Set to 0 for no target size."),
		'value' => 0,
		'optional' => 1,
		'type' => 'text'
	),
	'max_size' => array(
		'prompt' => gTranslate('config','Maximum image size'),
		'desc' => gTranslate('config',"You can optionally limit the dimensions your images on upload to Gallery.") .
		  '<br><span class="attention>'. gTranslate('config',"Warning:") .'</span> '.
		  gTranslate('config',"Enabling this feature will leave you <b>without a full size original</b> image in your Gallery, but allows you to save limited hard drive space. Gallery will attempt to preserve JPEG comments and EXIF data for the shrunken image, but this will require either <b>ImageMagick</b> or the optional <b>jhead</b> programs (see Step 2)."),
		'value' => 'off',
		'choices' => array(
			0 => gTranslate('config',"Off"),
			400 => 400,
			500 => 500,
			600 => 600,
			640 => 640,
			700 => 700,
			800 => 800,
			1024 => 1024,
			1280 => sprintf(gTranslate('config','%d (%d MPix)'), 1280, 1),
			1600 => sprintf(gTranslate('config','%d (%d MPix)'), 1600, 2),
			2048 => sprintf(gTranslate('config','%d (%d MPix)'), 2048, 3))
	),
	'max_file_size' => array(
		'prompt' => gTranslate('config','Maximum JPEG/PNG image file size'),
		'desc' => gTranslate('config',"You can optionally limit the filesize of your JPEG/PNG images on upload to Gallery.") .
		  '<br><span class="attention">'. gTranslate('config',"Warning:") . '</span> '.
		  gTranslate('config',"Enabling this feature will leave you <b>without an original quality image</b> in your Gallery. Set the default target filesize (in kilobytes).  Including a value may slow down file uploads, but will speed up page viewing time especially for people with dialup connections. Set to 0 if you don't want to restrict filesize."),
		'value' => 0,
		'optional' => 1,
		'type' => 'text'
	),
	'useOriginalFileNames' => array(
		'prompt' => gTranslate('config',"Preserve Names"),
		'desc' => gTranslate('config',"When uploading images to Gallery, should we preserve the original names?  If you select <i>no</i>, Gallery will assign a new name for each photo or movie that gets uploaded."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes',
	),
	'add_to_beginning' => array(
		'prompt' => gTranslate('config',"Add to beginning"),
		'desc' => gTranslate('config',"New images added to beginning rather than end of albums."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'fit_to_window' => array(
		'prompt' => gTranslate('config',"Fit-to-window"),
		'desc' => gTranslate('config',"When viewing photos which don't have an intermediate version, Gallery can automatically resize the image to fit the viewer's browser window (using JavaScript).  This may not work in embedded environments such as Nuke."),
		'value' => 'no',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		)
	),
	'use_fullOnly' => array(
		'prompt' => gTranslate('config',"Visitor size prefs"),
		'desc' => gTranslate('config',"Offer visitors the choice of always viewing the full sized version of an image instead of the intermediate size.  This setting only lasts for the visitor's session."),
		'value' => 'no',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		)
	),
	'group3_end' => array (
		'type' => 'group_end',
	),
	'group4' => array (
		'type' => 'group_start',
		'name' => 'group_options2',
		'title' => gTranslate('config',"Options"),
		'default' => 'none',
	),
	/* old .. needs to be fixed */
	'print_photos' => array(
		'prompt' => gTranslate('config',"Printing Services"),
		'desc' => gTranslate('config',"Allow visitors to print photos directly from the album.  Fotoserve is based in the United Kindom and Fotokasten is a print service based in Germany so they are good for the European market. EZ Prints, PhotoWorks and Shutterfly are based in the United States. mPUSH is a service that allows you to send photos to cellular phones worldwide. All services will give a small donation to the Gallery project for every print you make. Using these services is a great way for you to help support this free software."),
		'multiple_choices' => makeMultipleChoiceContent($services),
		'value' => array(
			'fotokasten' => array(
				'checked' => true
			),
			'photoaccess' => array(
				'checked' => true
			),
			'shutterfly' => array(
				'checked' => true
			),
			'fotoserve' => array(
				'checked' => true
			),
			'mpush' => array(
				'checked' => true,
				'account' => 'gallery'
			),
		),
		'optional' => true
	),
	'mPUSHAccount' => array(
		'prompt' => gTranslate('config',"Account for the mPUSH service"),
		'desc' => sprintf(gTranslate('config',"If you want to use mPUSH cellular photo service you can either use your own account, or leave this to 'gallery'. Visit this link to %screate your own account%s."),
		  '<a href="http://mpush.msolutions.cc/safe.php?p=Register&ref=gallery" target="_blank">', '</a>'),
		'value' => 'gallery',
		'type' => 'text',
	),
	'ecards' => array(
		'prompt' => gTranslate('config',"Enable Ecards ?"),
		'desc' => gTranslate('config',"Set this to 'yes' to allow users to send a picure as eCard via eMail") . '<br>'.
		  '<span class="attention">' . gTranslate('config',"This option requires the Email feature to be enabled.") . '</span>',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),
	'returnto' => array(
		'prompt' => gTranslate('config',"Gallery Link"),
		'desc' => gTranslate('config',"Display a link navigation from your album to the top level Gallery link within the album?  If you turn this feature off, the user will not be able to navigate out of the album.  This is only useful when you want to embed an album within a website and not allow users to (easily) view the other albums in the gallery."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes',
	),
	'defaultPerms' => array(
		'prompt' => gTranslate('config',"Default Album Permissions"),
		'desc' => gTranslate('config',"Defines the default set of permissions for an album.  Specifically, who can see the album, and view its photos."),
		'choices' => array(
			'everybody' => gTranslate('config',"Everybody"),
			'loggedin' => gTranslate('config',"Logged-In Users"),
			'nobody' => gTranslate('config',"Nobody")
		),
		'value' => 'everybody',
	),
	'group4_end' => array (
		'type' => 'group_end',
	),
	'group5' => array (
		'type' => 'group_start',
		'name' => 'group_displayed_data',
		'default' => 'none',
		'title' => gTranslate('config',"Data displayed")
	),
	'display_clicks' => array(
		'prompt' => gTranslate('config',"Click counts"),
		'desc' => gTranslate('config',"Display click counts within Albums?"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes',
	),
	'extra_fields' => array(
		'prompt' => gTranslate('config',"Custom Fields"),
		'optional' => 1,
		'desc' => gTranslate('config',"Custom fields that are available to describe each image.  They should be separated by commas.  E.g. <i>Description, Location, Film Type</i>"),
		'attrs' => array('size' => 80),
		'type' => 'text',
		'value' => 'Description'
	),
	'showDimensions' => array(
		'prompt' => gTranslate('config',"Display clickable image dimensions"),
		'desc' => gTranslate('config',"Display clickable image dimensions beneath the thumbnails.  Will show dimensions for resized images (if any) first, then for the full image, depending on album settings user permissions.  You may click on these instead of the thumbnail to either image."),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),
	'group5_end' => array (
		'type' => 'group_end',
	),
	'group6' => array (
		'type' => 'group_start',
		'name' => 'group_itemOwner',
		'default' => 'none',
		'title' => gTranslate('config',"Item Owner")
	),
	'item_owner_modify' => array(
		'prompt' => gTranslate('config',"Photo owner modification"),
		'desc' => gTranslate('config',"Allow photo owners to edit and rotate/flip their own photos?"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes',
	),
	'item_owner_delete' => array(
		'prompt' => gTranslate('config',"Photo owner deletion"),
		'desc' => gTranslate('config',"Allow photo owners to delete their own photos?"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")),
		'value' => 'yes',
	),
	'item_owner_display' => array(
		'prompt' => gTranslate('config',"Photo owner display"),
		'desc' => gTranslate('config',"Display the name of photo owners with the caption?"),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'group6_end' => array (
		'type' => 'group_end'
	),
	'group7' => array (
		'type' => 'group_start',
		'name' => 'group_polling',
		'default' => 'none',
		'title' => gTranslate('config',"Polling configuration"),
		'desc' =>
		  sprintf(gTranslate('config',"It allows your visitors to vote for or rate the contents of an album.  You set defaults for polling here, which will apply to new albums, and to all existing albums when you upgraded from an earlier version then %s."),
		  '1.4.1') .
		  '<p>' .
		  gTranslate('config',"In each album, voting can be switched off, restricted to logged in users or open to all visitors.")
		  .'<p>' .
		  sprintf(gTranslate('config',"There are two sorts of polls: %s, which allows voters to chose their favourites as first, second, third etc., and %s, which allows voters to rate any or all of the images without restriction.  %s is like a film review where all the images can get (for example) 5 points, while %s is useful in scoring competitions."),
		  '<b>'. gTranslate('config',"Rank") .'</b>',
		  '<b>'. gTranslate('config',"Critique") .'</b>',
		  gTranslate('config',"Critique"),
		  gTranslate('config',"Rank")
		  )
	),
	'voter_class' => array(
		'prompt' => gTranslate('config',"Voter class"),
		'desc' => gTranslate('config',"Voting is available to:") .
			'<dl>'
			.'<dt>'. gTranslate('config',"Nobody").'</dt>'
			.'<dd>'. gTranslate('config',"Voting is off.") .'</dd>'
			.'<dt>'. gTranslate('config',"Everybody") . '</dt>'
			.'<dd>'. gTranslate('config',"Anyone can vote.  Votes are recorded against session ID.") .'</dd>'
			.'<dt>'. gTranslate('config',"Logged in") .'</dt>'
			.'<dd>'. gTranslate('config',"Registered users can vote.  Votes are recorded against User ID, and voters can come back and change their choices.") . '</dd>'
			.'</dl>' ,
		'choices' => array(
			'Nobody' => gTranslate('config',"Nobody"),
			'Everybody' => gTranslate('config',"Everybody"),
			'Logged in' => gTranslate('config',"Logged in")
		),
		'value' => 'Nobody'
		),
	'poll_type' => array(
		'prompt' => gTranslate('config',"Poll type"),
		'desc' => gTranslate('config',"Gallery offers two sorts of polls for voting for images.  See above for a more detailed explanation.") .
		'<dl>'
		.'<dt>'. gTranslate('config',"Rank").'</dt>'
		.'<dd>'. gTranslate('config',"Users have a set of votes which they can allocate to the images.") . '</dd>'
		.'<dt>'. gTranslate('config',"Critique").'</dt>'
		.'<dd>'. gTranslate('config',"Enables users to give all images points without restriction.") . '</dd>'
		.'</dl>' ,
		'choices' => array(
			'rank' => gTranslate('config',"Rank"),
			'critique' => gTranslate('config',"Critique")
		),
		'value' => 'critique'
	),
	'poll_scale' => array(
		'prompt' => gTranslate('config',"Number of choices"),
		'desc' => gTranslate('config',"Number of choices that will be available to voters as radio buttons or in a pulldown menu."),
		'value' => 3,
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)
	),
	'poll_hint' => array(
		'prompt' => gTranslate('config',"Vote hint"),
		'desc' => gTranslate('config',"This is displayed above the voting options under each image."),
		'type' => 'text',
		'value' => gTranslate('config',"Vote for this image"),
		'attrs' => array('size' => 50),
		'optional' => 1
	),
	'poll_show_results' => array(
		'prompt' => gTranslate('config',"Show vote results"),
		'desc' => sprintf(gTranslate('config',"Display a summary and graph on the album page of images that have received the highest votes, and provide a link to the full results.  If this option is %s, results of voting will only be available to admins and album owners."),
		  gTranslate('config',"No")),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no',
	),
	'poll_num_results' => array(
		'prompt' => gTranslate('config',"Number of results displayed"),
		'desc' => sprintf(gTranslate('config',"If %s is %s, display a graph showing this many top results.  (Full results will be available on a linked page.)"),
		  '"'. gTranslate('config',"Show vote results").'"' ,
		  '"'. gTranslate('config',"Yes").'"'),
		  'value' => 3,
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)
	),
	'poll_orientation' => array(
		'prompt' => gTranslate('config',"Orientation of vote choices?"),
		'desc' => gTranslate('config',"Rank poll choices can be displayed beside each other (horizontally) or underneath each other (vertically).  Has no effect on critique voting which is a pulldown menu."),
		'value' => 'vertical',
		'choices' => array(
			'horizontal' => gTranslate('config',"Horizontal"),
			'vertical' => gTranslate('config',"Vertical"))
	) ,
	'poll_nv_pairs' => array(
		'prompt' => gTranslate('config',"Vote words and values"),
		'desc' => gTranslate('config',"This allows you to configure the actual text that will be displayed for each voting option, plus the number of points that option is worth."),
		'value' => array (
			array('name' => gTranslate('config',"Excellent"), 'value' => 5),
			array('name' => gTranslate('config',"Very Good"), 'value' => 4),
			array('name' => gTranslate('config',"Good"), 'value' => 3),
			array('name' => gTranslate('config',"Average"), 'value' => 2),
			array('name' => gTranslate('config',"Poor"), 'value' => 1)
		),
		'type' => 'nv_pairs',
		'verify-func' => 'check_poll_nv_pairs',
		'size' => 9
	),
	'group7_end' => array (
		'type' => 'group_end',
	),
	'group8' => array (
		'type' => 'group_start',
		'name' => 'group_albumSlidshow',
		'default' => 'none',
		'title' => gTranslate('config',"Album Slideshow"),
		'desc' => gTranslate('config',"These settings do not apply to the low tech slideshow.")
	),
	'slideshow_type' => array(
		'prompt' => gTranslate('config',"Slideshow Type"),
		'desc' => '<dl>'
		.'<dt>'. gTranslate('config',"Off") .'</dt>'
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
		'value' => 'ordered',
	),
	'slideshow_recursive' => array(
		'prompt' => gTranslate('config',"Recursive slideshow"),
		'desc' => sprintf(gTranslate('config',"Set to %s if you want to include images in sub albums as part of the slideshow."), '<b>'.gTranslate('config',"Yes").'</b>'),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'no'
	),
	'slideshow_loop' => array(
		'prompt' => gTranslate('config',"Allow slideshow to loop"),
		'desc' => sprintf(gTranslate('config',"This option will allow visitors to have your slideshow repeat until they close the window or go to another page.  Set it to %s if you want to save bandwidth."), '<b>'.gTranslate('config',"No").'</b>'),
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		),
		'value' => 'yes'
	),
	'slideshow_length' => array(
		'prompt' => gTranslate('config',"Slideshow length"),
		'desc' => gTranslate('config',"Number of images to display in slideshow.  Set to 0 or blank to display all images.  Set to a number if slideshow is random and you want to display only a selection of the images."),
		'type' => 'text',
		'value' => 0,
		'optional' => 1,
		'attrs' => array('size' => 5)
	),
	'group8_end' => array (
		'type' => 'group_end',
	),
	'group_MicroNav_Start' => array (
		'type' => 'group_start',
		'name' => 'group_photoMicroNav',
		'default' => 'none',
		'title' => gTranslate('config',"Microthumb Photo Navigation"),
		'desc' => gTranslate('config',"These settings apply to the microthumb photo navigation system.  The microthumbs are only visible when viewing individual images.")
	),
	'nav_thumbs' => array(
		'prompt' => gTranslate('config',"Microthumb Photo Navigation"),
		'desc' => gTranslate('config',"Uses microthumbs for photo navigation. This creates a navigation panel with tiny thumbnails of the images around the photo you are viewing.  You can also choose both to display the microthumb navigation system AND the old style navigation system."),
		'value' => 'no',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No"),
			'both' => gTranslate('config',"both")
		)
	),
	'nav_thumbs_style' => array(
		'prompt' => gTranslate('config',"Microthumb Photo Navigation"),
		'desc' => '<dl>'
		  .'<dt>'. gTranslate('config',"Fixed") .'</dt>'
		  .'<dd>'. gTranslate('config',"Displays prev+next groups of thumbs, changing only to display next group of thumbs.") . '</dd>'
		  .'<dt>'. gTranslate('config',"Dynamic") . '</dt>'
		  .'<dd>'. gTranslate('config',"Displays previos & next images, with current image between.") . '</dd>'
		  .'</dl>',
		'value' => 'fixed',
		'choices' => array(
			'fixed' => gTranslate('config',"Fixed"),
			'dynamic' => gTranslate('config',"Dynamic")
		)
	),
	'nav_thumbs_first_last' => array(
		'prompt' => gTranslate('config',"Show Microthumb for First/Last"),
		'desc' => gTranslate('config',"This allows you to turn on and off the display of the first and last microthumb.  Since these two images are always the same, some might find that it is not very useful and would rather display more next/previous thumbs."),
		'value' => 'no',
		'choices' => array(
			'yes' => gTranslate('config',"Yes"),
			'no' => gTranslate('config',"No")
		)
	),
	'nav_thumbs_prev_shown' => array(
		'prompt' => gTranslate('config',"Number of Previous Microthumbs"),
		'desc' => gTranslate('config',"This is the number of previous microthumbs on the screen at a time.  You might prefer to show fewer previous microthumbs and more next microthumbs."),
		'value' => 1,
		'choices' => array(
			1 => 1, 2 => 2, 3 => 3, 4 => 4,
			5 => 5, 6 => 6, 6 => 6, 7 => 7,
			8 => 8, 9 => 9, 10 => 10
		)
	),
	'nav_thumbs_next_shown' => array(
		'prompt' => gTranslate('config',"Number of Next Microthumbs"),
		'desc' => gTranslate('config',"This is the number of next microthumbs on the screen at a time.  You might prefer to show fewer previous microthumbs and more next microthumbs."),
		'value' => 1,
		'choices' => array(
			1 => 1, 2 => 2, 3 => 3, 4 => 4,
			5 => 5, 6 => 6, 6 => 6, 7 => 7,
			8 => 8, 9 => 9, 10 => 10
		)
	),
	'nav_thumbs_location' => array(
		'prompt' => gTranslate('config',"Location of Microthumb Navigation"),
		'desc' => gTranslate('config',"Displays the microthumb navigation bar at the Top, Bottom or Both of the photo viewing page.  Since the microthumb navigation system is larger than the original navigation system, you may want to display it at only the top or bottom to conserve space usage."),
		'value' => 'top',
		'choices' => array(
			'top' => gTranslate('config',"Top"),
			'both' => gTranslate('config',"Both"),
			'bottom' => gTranslate('config',"Bottom")
		)
	),
	'nav_thumbs_size' => array(
		'prompt' => gTranslate('config',"Height of Microthumbs"),
		'desc' => gTranslate('config',"The size of microthumbs will determine how clear the images are as well as how much room they take up.  This value is the height and is in pixels.  45 is a reasonable value for viewing at 1024x768."),
		'value' => '45',
		'type' => 'text'
	),
	'nav_thumbs_current_bonus' => array(
		'prompt' => gTranslate('config',"Bonus to Current Microthumb's Size"),
		'desc' => gTranslate('config',"This is the bonus in pixels to the size of the current microthumb.  This creates a nice look and a value of 10-15 is reasonable.  Zero (0) and negative numbers (-10) are also valid here.  Zero (0) will result in the current thumb being of identical size to all the other microthumbs and a negative value will result in a smaller microthumb."),
		'value' => '15',
		'type' => 'text'
	),
	'group_MicroNav_End' => array (
		'type' => 'group_end',
	),
	'group_frames' => array (
		'type' => 'group_start',
		'name' => 'group_frames',
		'default' => 'none',
		'title' => gTranslate('config',"Frames"),
		'desc' => sprintf(gTranslate('config',"Each image (thumbnails, resized and full sized) in %s can have a frame.  The following styles available:"),Gallery()) .
		available_frames(true),
	),
	'album_frame' => array(
		'prompt' => gTranslate('config',"Subalbum frames"),
		'desc' => gTranslate('config',"This frame will appear around the thumbnail image representing a subalbum."),
		'choices' => available_frames(),
		'value' => 
		'simple_book'
	),
	'thumb_frame' => array(
		'prompt' => gTranslate('config',"Frame around thumbs"),
		'desc' => gTranslate('config',"This frame will appear around the thumbnail image representing a photo or movie."),
		'choices' => available_frames(),
		'value' => 'solid'
	),
	'image_frame' => array(
		'prompt' => gTranslate('config',"Frame around images"),
		'desc' => gTranslate('config',"This frame will appear around fulled sized and resized photos."),
		'choices' => available_frames(),
		'value' => 'solid'
	),
	'group_frames_end' => array (
		'type' => 'group_end',
	),
);
?>
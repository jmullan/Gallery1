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
	
$locale_check=check_locale();
/* $elawisyl <=> Enable LAnguages WIthout SYstem Locales
** is set to "no" when we have no system locales and are in linux
** "no" disables all language settings 
*/

if ($locale_check == NULL) {
	$elawisyl = "no";
} else {
	$elawisyl = "yes";
}

$netpbm_check=check_graphics();
$IM_check=check_graphics_im();

/* If all watermarking/overlay applications are missing */
if ( isset($netpbm_check[2]['pnmcomp']) && isset($netpbm_check[2]['pamcomp']) && isset($IM_check[2]['composite']) ) {
	$enablewatermark = "no";
} else {
	$enablewatermark = "yes";
}

if (function_exists("posix_getpwuid")) {
	$rec = @posix_getpwuid(posix_getuid());
	$webserver_user = $rec["name"];
} else {
	$whoami = locateFile("whoami");
	if ($whoami) {
		fs_exec($whoami, $results, $status);
		$webserver_user = $results[0];
	} else {
		$webserver_user = _("unknown");
	}
}


$common_messages["getting-netpbm"] =
			sprintf(_("Gallery prefers NetPBM version 9.9 and up.  You can install just the binaries Gallery needs from the %sGallery Download Page%s. Or, you can compile and install the entire NetPBM package from the %sNetPBM website%s."), 
				 "<a href=\"http://sourceforge.net/projects/gallery\">",
				 "</a>",
				 "<a href=\"http://sourceforge.net/projects/netpbm\">",
				 "</a>")  .
				'<br><span class="emphasis">' . _("Note:"). '</span> ' .
			_("You can install the binaries from the Gallery site even if you don't have root access on your box!  If you can't get it working, try leaving the NetPBM path blank and using ImageMagick instead.");
			
$common_messages["fail-netpbm-partial"] =
			_("I've found part of NetPBM, but some critical components are missing (see below).  Perhaps your version of NetPBM is out of date.") . "  " .
			$common_messages["getting-netpbm"];

$common_messages["getting-imagemagick"] =
			sprintf(_("Gallery prefers ImageMagick version 5.4.8 and up.  You can compile and install the entire ImageMagick package from the %sImageMagick website%s." ), 
				'<a href="http://www.imagemagick.org">', '</a>') .
			'<br><span class="emphasis">' . _("Note:"). '</span> ' .
			_("The site also has binaries available for assorted operating systems.  If you can't get it working, try leaving the ImageMagick path blank and using NetPBM instead.");

$common_messages["fail-imagemagick-partial"] =
			_("I've found part of ImageMagick , but some critical components are missing (see below).") . "  " .
			$common_messages["getting-imagemagick"];
						
$common_messages["open-basedir-active"] =
	'<span class="emphasis">' . _("Note:"). '</span>'.
	sprintf(_("Your PHP installation is configured with the %s restriction.   This may make it difficult for Gallery to detect and verify your binaries, even if you've already successfully used the Config. Wizard in the past.  Don't worry too much if this page gives warnings; your settings will still be saved properly."),
			"<b><a href=\"http://www.php.net/manual/en/features.safe-mode.php#ini.open-basedir\" target=\"_blank\">open_basedir</a></b>" );
			
$checklist = array(
	'gallery_version' => array(
		'optional' => 1,
		'desc' => sprintf(_("Check to see how old %s version %s is."),
			Gallery(),
			"<i>$gallery->version</i>"),
	     	'func' => "check_gallery_version"
	),
	'php' => array(
		'desc' => _("Check to see if you are running PHP 4.1.0 or greater.") ."<br>".  _("Since Gallery 1.4.5 Gallery needs at least PHP 4.1.0, this is due to that some versions of PHP have bugs that cause Gallery to fail and that older Version does not have required functionality."),
	     	'func' => "check_php",
		'fail-too-old' => sprintf(_("You're running PHP version %s.  You need to upgrade to PHP %s."), phpversion(), $MIN_PHP_MAJOR_VERSION),
		'fail-incompatible' => sprintf(_("You're running PHP version %s which has known bugs that cause Gallery to fail."),  phpversion()),
		'serious' => 1,
	),
	'buggy_php' => array(
		'desc' => _("We are using predefined variables which some older version of PHP have problems with."),
		'func' => "check_filedirective",
		'buggy__FILE__' => _("It seems your PHP has a problem with __FILE__, please upgrade to a recent Version"),
		'serious' => 1
	),
	'register_globals' => array(
		'desc' => _("Gallery highly recommends that the PHP option 'register_globals' be turned off, for security purposes.  Some software, notably the 'Geeklog' CMS, requires that 'register_globals' be enabled."),
		'func' => "check_register_globals",
		'warn-register_globals' => _("The PHP option 'register_globals' is enabled on your server. Gallery highly recommends that you disable 'register_globals' unless it is required by software on your site.  Gallery does not officially support 'register_globals' being enabled, and will attempt to disable it internally."),
		'serious' => 1,
		'optional' => 1,
	),
	'safe_mode' => array(
		'desc' => _("Check to see if PHP is running in <b>safe_mode</b>.  Safe Mode is a PHP security feature commonly used in shared environments.  Gallery cannot run when Safe Mode is enabled because it imposes too many restrictions."),
		'func' => "check_safe_mode",
		'fail-safe-mode' => sprintf(_("<b>safe_mode</b> is enabled on this server.  You must disable it before you can use Gallery.  Refer to the FAQ, the forums, and the help page on the %sGallery website%s and the %sPHP website%s for assistance."),
			'<a href="http://gallery.sourceforge.net">',
			'</a>',
			'<a href="http://www.php.net/manual/en/features.safe-mode.php">',
			'</a>') .
		"<br><br>" .
		'<span class="emphasis">' . _("Note:"). '</span>'.
			  sprintf(_("In some rare circumstances, Gallery thinks that Safe Mode is on, when it's really off.  You can see for yourself by looking in the <i>Local</i> column of the <i>safe_mode</i> row in the %sPHP Info%s page.  If it says 'Off' or '0' or 'undefined' then it's possible that Gallery made a mistake and you can continue.  If not, then you must resolve this problem before you can use Gallery."),
				 '<a href="phpinfo.php">',
				 '</a>'),
		'optional' => 1,
		'serious' => 1
	),
	'exec' => array(
		'desc' => _("The <b>exec()</b> function is what Gallery uses to launch programs such as NetPBM, ImageMagick, and jhead.  If <b>exec()</b> is disabled on your server then Gallery will not be able operate."),
		'func' => "check_exec",
		'fail-exec' => _("The <b>exec()</b> function is disabled on your server.  Gallery will not function with this PHP configuration."),
		'serious' => 1,
	),
	'htaccess' => array(
		'desc' => _("Allow the Gallery <b>.htaccess</b> file to set application specific configuration options for the Gallery application.  This is controlled by the webserver owner so it's not always possible for you to change it.  The main drawbacks to not having this are that you may not be able to upload files > 2MB (e.g. big ZIP files or movies)."),
		'func' => "check_htaccess",
		'fail-htaccess' => _("Apache is not obeying the 'php_value' lines in your <b>.htaccess</b> file. Try entering the following into your web server's <b>httpd.conf</b> file:") ."<br><pre>" .
			 "&lt;Directory " . GALLERY_BASE . "&gt;\n" .
			 "\tAllowOverride Options FileInfo\n" .
			 "&lt;/Directory&gt;</pre>\n" .
			 _("If you are running PHP in CGI mode, this message is unavoidable."),
		'optional' => 1
	),
	'magic-quotes' => array(
		'optional' => 1,
		'desc' => sprintf(_("Check to see if %smagic quotes%s are enabled on your PHP server.  If they're on, you may notice strange behavior if you put ' (single-quote), &quot; (double quote) or &#92; (backslash) characters in titles or captions in Gallery."),
		 '<b><a href="http://www.php.net/manual/en/configuration.php#ini.magic-quotes-gpc" target="_blank">',
		'</a></b>'),
	     	'func' => "check_magic_quotes",
		'fail-magic-quotes' => sprintf(_("Magic quotes are on.  Your webserver administrator can turn them off by putting %s in your <b>php.ini</b> file."),
			": <pre>\n\tmagic_quotes_gpc = Off</pre>")
	),
	'mod-rewrite' => array(
		'desc' => _("Check to see if your webserver is compiled with <b>mod_rewrite</b>.  This Apache module allows Gallery to use shorter URLs that are easier to read and look nicer when you mail them to your web-challenged relatives.  It's not an essential feature."),
		'func' => "check_mod_rewrite",
		'fail-mod-rewrite' => _("Either <b>mod_rewrite</b> is not installed or your <b>.htaccess</b> file is not enabled (see above).  Either way, we'll have to use longer URLs in the Gallery.  If you want to turn it on, first make sure that your <b>.htaccess</b> file is being obeyed. If it still doesn't work, you may need to reconfigure and rebuild Apache with this flag: ") .
			"<pre>\t--enable-module=rewrite</pre>",
		'optional' => 1
	),
	'NetPBM' => array(
		'optional' => 1,
		'desc' => _("Check to see if NetPBM is installed so that Gallery can manipulate images (rotate, resize, make thumbnails, etc.).  You only need to have either NetPBM <b>or</b> ImageMagick installed."),
		'func' => "check_graphics",
		'fail-netpbm' => _("I can't find NetPBM.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path to NetPBM by hand on the following page.  If it's not installed, you have two choices: " ) .
			$common_messages["getting-netpbm"],

		'fail-netpbm-partial' => 
			$common_messages["fail-netpbm-partial"] .
			"<p><b> " . _("If NetPBM is installed and Gallery isn't finding it automatically that's OK&#8212;you can specify the path to NetPBM by hand on the following page.") ." </b>",

		'open-basedir-note' => $common_messages["open-basedir-active"]
	),
	'ImageMagick' => array(
		'optional' => 1,
		'desc' => _("Check to see if ImageMagick is installed so that Gallery can manipulate images (rotate, resize, make thumbnails, etc.).  You only need to have either ImageMagick <b>or</b> NetPBM installed."),
		'func' => "check_graphics_im",
		'fail-imagemagick' => _("I can't find ImageMagick.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path to ImageMagick by hand on the following page.  If it's not installed, you must install it.  ") .
			$common_messages["getting-imagemagick"],
		'fail-imagemagick-partial' => 
			$common_messages["fail-imagemagick-partial"] .
			"<p><b> " . _("If ImageMagick is installed and Gallery isn't finding it automatically that's OK&#8212;you can specify the path to ImageMagick by hand on the following page.") ." </b>",
		'open-basedir-note' => $common_messages["open-basedir-active"]
	),
	'exif' => array(
		'optional' => 1,
		'desc' => _("Check to see if <b>jhead</b> is installed so that Gallery can examine EXIF headers embedded in images created with most digital cameras."),
		'func' => "check_exif",
		'fail-exif' => sprintf(_("I can't find <b>jhead</b>.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path by hand on the following page.  If it's not installed, you can install it yourself. Jhead is a public domain EXIF parser.  Source, linux binaries, and windows binaries can be found at the %sjhead homepage%s."),
			'<a href="http://www.sentex.net/~mwandel/jhead/index.html">',
			'</a>'),
		'open-basedir-note' => $common_messages["open-basedir-active"]
	),
	'jpegtran' => array (
		'optional' => 1,
		'desc' => _("Check to see if <b>jpegtran</b> is installed so that Gallery can perform lossless rotations on JPEG images."),
		'func' => "check_jpegtran",
		'fail-jpegtran' => sprintf(_("I can't find <b>jpegtran</b>.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path by hand on the following page.  If it's not installed, you can install it yourself.  Jpegtran is a free tool distributed by the Independent JPEG Group as part of <i>libjpeg</i>.  Source may be found at the %sIJG homepage%s.  Binaries may be found at the %sJPEG Club website%s or in the <i>libjpeg</i> package of Linux distributions."),
			'<a href="http://www.ijg.org">', '</a>',
			'<a href="http://jpegclub.org/">', '</a>'), 
		'open-basedir-note' => $common_messages["open-basedir-active"]
	),	
	'gettext' => array (
		'optional' => 1,
		'desc' => _("Check to see if PHP is compiled with <b>GNU gettext</b> support for fast translations."),
		'func' => "check_gettext",
		'fail-gettext' => sprintf(_("Your PHP does not have <b>GNU gettext</b> support installed.  To include it, you'll need to recompile PHP (see the %sPHP docs%s for more information). Multi-language support is still available in Gallery without <b>gettext</b>, but the pages may take slightly longer to load."),
			'<a href="http://www.php.net/manual/en/ref.gettext.php">', '</a>')
	),
	'gallery-locale' => array (
		'optional' => 1,
		'desc' => _("Check to see what languages are available."),
		'func' => "check_gallery_languages"
	),
	'missing-locales' => array (
		'optional' => 1,
		'enabled' => (sizeof(gallery_languages()) > 1) ? "yes" :"no",
		'desc' => _("Testing to see which <b>system</b> locales (if any) are absent.") . "<br>".
				_("These locales are required to use multilanguage capabilities of Gallery when your PHP has gettext support. They are also needed to show date and time in the according format to the language."),
		'func' => "check_absent_locales",
		'serious' => ( $elawisyl == "no" ) ? 1 : 0 ,
	),
	'check-versions' => array (
		'desc' => _("Checking to see if you have the latest versions of all Gallery files."),
		'func' => "check_gallery_versions",
		'serious' => 1,
		'optional' => 1,
	),
);

$features = array(
	'zip' => array("zipinfo", "unzip"),
	'rewrite' => array("GALLERY_REWRITE_OK", "htaccess_file"),
	'mirror' => array("mirrorSites"),
);

$constants =  array_merge(
  array(
	'group_mandatory' => array (
		'type' => "group_start",
		'name' => "group_mandatory",
		'default' => "inline",
		'title' => _("General settings"),
		'contains_required' => true,
	),
	'galleryTitle' => array(
		'prompt' => _("Gallery Title"),
		'desc' => _("The name of your Gallery.  E.g. <i>My Cool Photos</i>"),
		'value' => _("Gallery"),
		'type' => "text",
		'attrs' => array('size' => 50)
	),
	'editPassword' => check_admins(),
	'useIcons' => array(
		'prompt' => _("Use Icons ?"),
		'desc' => _("You can choose wether you want to use Gallery icons, text only, or both. When you choose icons only, then there will be an onMousoverText."),
		'choices' => array(	'both' => _("Icons and text"),
					'no' => _("Text only"),
					'yes' => _("Icons only")
			),
		'value' => 'no'
	),
	'skinname' => array(
		'prompt' => _("Skin Name"),
		'desc' => _("Select a skin to use for Gallery. You now have a wide choice of &quot;skins&quot; that affect the look of every page of your Gallery.  You can have fun experimenting with the available skins. Choose &quot;None&quot; to use the look from v1.4.") . 
				'<br><a href="http://www.pownuke.com/galleryskins/">'. _("Here you can get a more Gallery Skins by Drazan") .'</a>'.
				available_skins(true),
		'choices' => available_skins(),
		'value' => "none"
	),
	'uploadMode' => array(
		'prompt' => _("Default upload method"),
		'desc' => _("Which method of uploading photos should be defaulted to for users who don't specify?"),
		'choices' => array('form' => _("Form-based Upload"), 
					'applet_mini' => _("Mini-Applet"), 
					'applet' => _("Full-Applet"),
					'url' => _("URL")),
		'value' => "form",
	),

	'group_mandatory_end' => array (
		'type' => "group_end",
	),
	'groupLocations' => array (
		'type' => "group_start",
		'name' => "group_URLS",
		'default' => "none",
		'title' => _("Locations and URLs"),
		'contains_required' => true,
	),
	'albumDir' => array(
		'prompt' => _("Album directory"),
		'desc' => '(<span class="attention">' . _('REQUIRED') . '</span>) ' .
			sprintf(_("The full path on physical disk to the directory where your photos will be stored.  This directory should start off empty&#8212;Gallery will fill it.  Note that this directory needs to have write access by the user who is running the web server (in your case this user is %s)."),
			"<b>". $webserver_user . "</b>"),
		'value' => getenv("DOCUMENT_ROOT") . "/albums",
		'require-write-dir' => 1,
		'filename' => 1,
		'type' => "text",
		'attrs' => array('size' => 50),
		'required' => true,
	),
	'tmpDir' => array(
		'prompt' => _("Temporary directory"),
		'desc' => '(<span class="attention">' . _('REQUIRED') . '</span>) ' .
			  sprintf(_("The filesystem path to a temporary directory.  E.g. %s or %s.  Note that if you have <b>open_basedir</b> configured on your system, then this temporary directory must be inside the <b>open_basedir</b> path!  In that case you may need to create the directory yourself and make sure that it's writable by the webserver process."),
		'<i>/tmp</i>','<i>c:\\windows\\temp</i>'),
		'value' => getenv("TMP"),
		'filename' => 1,
		'require-write-dir' => 1,
		'type' => "text",
		'attrs' => array('size' => 50),
		'required' => true,
	),
	'photoAlbumURL' => array(
		'prompt' => "Gallery URL",
		'desc' => '(<span class="attention">' . _('REQUIRED') . '</span>) ' .
				sprintf(_("The full URL to the Gallery web application. (e.g. %s) Gallery can operate with relative URLs, however some features, such as printing, may not function correctly."),
				'<i>http://www.example.com/gallery</i>'),
		'value' => "http://" . getenv("SERVER_NAME") . $GALLERY_URL,
		'no-trailing-slash' => 1,
		'type' => "text",
		'attrs' => array('size' => 50),
		'required' => true,
	),
	'albumDirURL' => array(
		'prompt' => _("Albums URL"),
		'desc' => '(<span class="attention">' . _('REQUIRED') . '</span>) ' .
			sprintf(_("The full URL to your album directory on your web server.  (e.g. %s) Gallery can operate with relative URLs, however some features, such as printing, may not function correctly."), 
			'<i>http://www.example.com/albums</i>'),
		'value' => "http://" . getenv("SERVER_NAME") . "/albums",
		'no-trailing-slash' => 1,
		'type' => "text",
		'attrs' => array('size' => 50),
		'required' => true,
	),

	'movieThumbnail' => array(
		'prompt' => _("Thumbnail image"),
		'desc' => sprintf(_('The filesystem path to the thumbnail image to show for movies. E.g. %s'), '<i>/home/foo/thumbnail.jpg</i>'),
		'value' => GALLERY_BASE . "/images/movie.thumb.jpg",
		'type' => "text",
		'attrs' => array('size' => 50)
	),
	'mirrorSites' => array(
		'prompt' => _("Mirror sites"),
		'desc' => sprintf(_("A list of URLs that contain mirrors of your albums directory.  Specify one URL per line with the ones that are more reliable towards the top.  E.g. %s"),
		'<center><i>' .
			'http://www.fast.server/albums<br>http://www.other.server/albums</i>' .
			'</center>'),
		'optional' => 1,
		'type' => "textarea",
		'value' => NULL,
		'attrs' => array('cols' => 50, 'rows' => 5)
	),
	'groupLocations_end' => array (
		'type' => "group_end",
	),

	'groupToolkits' => array (
		'type' => "group_start",
		'name' => "group_toolkits",
		'default' => "none",
		'title' => _("Toolkits / image processing"),
		'contains_required' => true,
	),
	'graphics' => array(
		'prompt' => _("Graphics suite to use"),
		'desc' => _("This setting tells Gallery which set of graphics tools you prefer it to use.  If Gallery says both are okay (on the first page) then this is simply user preference.  Otherwise go with the one that Gallery was able to find without error."),
		'choices' => array('NetPBM' => "NetPBM", 'ImageMagick' => "ImageMagick"),
		'value' => default_graphics(),
		'required' => true,
	),
	'pnmDir' => array(
		'optional' => 1,
		'prompt' => _("Path to NetPBM"),
		'desc' => sprintf(_("The path to the directory containing NetPBM on your system.  If you downloaded NetPBM from the Gallery download page, don't forget to make the files executable.  E.g. %s or %s or %s"),
			'<i>/usr/local/netpbm</i>',
			'<i>/usr/local/bin</i>', 
			'<i>c:\\apps\\netpbm</i>'). '<br>'.
			sprintf(_("This path is only required if you choosed %s above. Otherwise you can leave it like it is, or empty."), "NetPBM"),
		'value' => locateDir("jpegtopnm", isset($gallery->app->pnmDir) ? $gallery->app->pnmDir : ""),
		'verify-func' => "check_graphics",
		'fail-netpbm' => _("I can't find NetPBM at the location you provided.") . "  " .
			$common_messages["getting-netpbm"],
		'fail-netpbm-partial' => $common_messages["fail-netpbm-partial"],
		'filename' => 1,
		'attrs' => array('size' => 50),
		'no-trailing-slash' => 1,
		'type' => "text",
		'verify-func-takes-graphics-type' => true,
		'required' => true,
	),
	'pnmtojpeg' => array(
		'prompt' => _("PNM to JPEG converter"),
		'desc' => _("This value is only relevant if you use NetPBM.  NetPBM uses a tool to encode its internal image format into JPEG.  This tool is called either <i>ppmtojpeg</i> or <i>pnmtojpeg</i> depending on the version of NetPBM that you have installed.  Check your NetPBM directory to see which one you have installed and specify it here.  If you aren't sure, just take a guess and we'll try to verify it for you."),
		'choices' => array('pnmtojpeg' => "pnmtojpeg", 'ppmtojpeg' => "ppmtojpeg"),
		'value' => "pnmtojpeg"
	),
	'pnmcomp' => array(
		'prompt' => _("PNM Composite (Overlay)"),
		'desc' => _("This value is only relevant if you use NetPBM.  NetPBM uses a tool to overlay one image over another.  This tool is called either <i>pnmcomp</i> or <i>pamcomp</i> depending on the version of NetPBM that you have installed.  Check your NetPBM directory to see which one you have installed and specify it here.  If you aren't sure, just take a guess and we'll try to verify it for you."),
		'choices' => array('pnmcomp' => "pnmcomp", 'pamcomp' => "pamcomp"),
		'value' => "pnmcomp",
	),
	'ImPath' => array(
		'optional' => 1,
		'prompt' => _("Path to ImageMagick"),
		'desc' => _("The path to the directory containing ImageMagick on your system."). '<br>'.
			sprintf(_("This path is only required if you choosed %s above. Otherwise you can leave it like it is, or empty."), "ImageMagick"),
		'verify-func' => "check_graphics_im",
		'value' => locateDir("convert", isset($gallery->app->ImPath) ? $gallery->app->ImPath : ""),
		'fail-imagemagick' => _("I can't find ImageMagick at the location you provided.") . "  " .
			$common_messages["getting-imagemagick"],
		'filename' => 1,
		'attrs' => array('size' => 50),
		'no-trailing-slash' => 1,
		'type' => "text",
		'verify-func-takes-graphics-type' => true,
		'required' => true,
	),
	'autorotate' => array(
                'prompt' => _("Auto-rotate images"),
		'desc' => _("Selecting this option has Gallery automatically attempt to rotate images on upload, based on the orientation information that is stored in JPEG EXIF data by some newer digital cameras.") . '<br>' .
				'<span class="attention">' . _("Requires Jhead EXIF parser v.1.9 or greater (See optional tools).") . '</span>',
                'choices' => array('yes' => _("yes"), 'no' => _("no")),
                'value' => "yes"
        ),

	'jpegImageQuality' => array(
		'prompt' => "<b>(" . _("Advanced") .")</b><br> ". _("JPEG Quality"),
		'desc' => _("The JPEG file format trades off size versus quality.  Gallery can control the quality of the JPEGs that it produces when you resize, rotate or make thumbnails from JPEG images.  Higher quality images are larger in size than lower quality images.  However, in many cases there's a point at which raising the quality increases the size of the files without noticeably affecting the appearance of the image.  Setting this value to &quot;100&quot; is not recommended. If you notice that your thumbnails and resized images are larger than the originals and you don't like this, you can try lowering this value.  <b>Note</b> that changing this value will only affect images changed in the <i>future</i>."),
		'choices' => array(50 => 50, 55 => 55, 60 => 60, 65 => 65,
				   70 => 70, 75 => 75, 80 => 80, 85 => 85,
				   90 => 90, 95 => 95, 100 => 100),
		'value' => 90
	),
	'groupToolkits_end' => array (
		'type' => "group_end",
	),
	'group_embedded' => array (
		'type' => "group_start",
		'name' => "group_embedded",
		'default' => "none",
	       	'title' => _("Embedded Gallery"),
		'desc' => _("Gallery automatically detects when it is embedded within one of the supported CMS environments, however there may be some environments which have special options which need to be set.  Any options which are not automatically detected will need to be specified on this screen.") . ' <b>' . _("If your ISP or CMS requires register_globals to be enabled, you may be required to enable the 'Disable register_globals check' option under the Technical tab.") . '</b>',
	),
	'geeklog_dir' => array(
		'prompt' => _("GeekLog Dir"),
		'optional' => 1,
		'desc' => _("If you use Geeklog, please enter the location of the GeekLog public_html directory. i.e. '/path/to/geeklog/public_html'"),
		'attrs' => array('size' => 80),
		'type' => "text",
		'value' => ""
	),
	'group_embedded_end' => array (
			'type' => "group_end"
	),
	'group3' => array (
		'type' => "group_start",
		'name' => "group_mainpage",
		'default' => "none",
		'title' => _("Main Gallery page"),
	),
	'albumTreeDepth' => array(
		'prompt' => _("Show the album tree ?"),
		'desc' => _("If you turn this option on, you'll see a list of sub-albums when you're browsing the top level of your Gallery.  This will make navigating to a specific sub album much easier. You can choose whether or not you want to limit the depth of the tree to avoid severely cluttering the appearance of the top Gallery page."),
		'choices' => array(0 => _("No tree"), 
				1 => sprintf (_("%d sublevel"),1),
				2 => sprintf (_("%d sublevels"),2),
				3 => sprintf (_("%d sublevels"),3),
				4 => sprintf (_("%d sublevels"),4),
				5 => sprintf (_("%d sublevels"),5),
				10 => sprintf (_("%d sublevels"),10),
				1000 => _("Complete Tree")),
		'value' => 1000,
	),
	'microTree' => array(
		'prompt' => _("Replace album tree with micro thumbs"),
		'desc' => _("If you have set album tree to anything but off, this will display micro thumbs of the sub albums instead of the album tree."),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no"
	),
	'highlight_size' => array(
		'prompt' => _("Highlight size"),
		'desc' => _("The target size (in pixels) of thumbnails shown in the Gallery page (highlight images)."),
 		'value' => 200,
		'type' => "text",
		'attrs' => array('size' => 5)
	),
	'showOwners' => array(
		'prompt' => _("Show Owner"),
		'desc' => _("Show album owner's name next to each album?"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no"
	),
	'albumsPerPage' => array(

		'prompt' => _("Albums per page"),
		'desc' => _("How many albums should be displayed per Gallery page?"),
		'value' => "5",
		'attrs' => array('size' => 10),
		'type' => "text"
	),
	'showSearchEngine' => array(
		'prompt' => _("Search Engine"),
		'desc' => _("Display search engine on main Gallery page?"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "yes"
	),
	'slowPhotoCount' => array(
		'prompt' => _("Accurate Photo Count"),
		'desc' => _("Display the album and photo count on the Gallery root page using an accurate, but incredibly slow, counting method.  This was added at the request of many users, however it is not recommended for Galleries where you will have large numbers of albums and photos.  Some examples have been shown to cause the index page to load in as long as 30 seconds with this option enabled."),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no",
	),
	'gallery_thumb_frame_style' => array(
		'prompt' => _("Frame around albums"),
		'desc' => _("On the main page, the image representing each album has a frame around it.  The following styles are available:") .
			available_frames(true),
		'choices' => available_frames(),
		'value' => "simple_book"
	),

	'group3_end' => array (
		'type' => "group_end",
	),
	'group5' => array (
		'type' => "group_start",
		'name' => "group_optionals",
		'default' => "none",
		'title' => _("Optional tools")
	),
	'zipinfo' => array(
		'prompt' => "Zipinfo",
		'desc' => sprintf(_("The filesystem path to the <b>%s</b> binary.  E.g. %s or %s. This is optional so if you don't have it, don't worry about it too much."),
			'<a href="http://www.info-zip.org/" target="_blank">zipinfo</a>', '<i>/usr/local/bin/zipinfo</i>', '<i>c:\\bin\\zipinfo.exe</i>'),
		'value' => locateFile("zipinfo"),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => "text",
		'attrs' => array('size' => 50)
	),
	'unzip' => array(
		'prompt' => "Unzip",
		'desc' => sprintf(_("The filesystem path to the <b>%s</b> binary.  E.g. %s or %s. This is optional so if you don't have it, don't worry about it too much."),
			'<a href="http://www.info-zip.org/" target="_blank">unzip</a>', '<i>/usr/local/bin/unzip</i>', '<i>c:\\bin\\unzip.exe</i>'),
		'value' => locateFile("unzip"),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => "text",
		'attrs' => array('size' => 50)
	),
	'rar' => array(
		'prompt' => "rar",
		'desc' => sprintf(_("The filesystem path to the <b>%s</b> binary.  E.g. %s or %s. This is optional so if you don't have it, don't worry about it too much."),
			'<a href="http://www.rarlab.com/" target="_blank">rar</a>', '<i>/usr/local/bin/rar</i>', '<i>c:\\bin\\rar.exe</i>'),
		'value' => locateFile("rar"),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => "text",
		'attrs' => array('size' => 50)
	),
	'use_exif' => array(
		'prompt' => "Jhead EXIF Parser",
		'desc' => sprintf(_("The filesystem path to the <b>%s</b> EXIF parser.  E.g. %s or %s. This is optional so if you don't have it, don't worry about it too much."),
			'<a href="http://www.sentex.net/~mwandel/jhead/" target=_"blank">jhead</a>', '<i>/usr/local/bin/jhead</i>', '<i>c:\\bin\\jhead.exe</i>'),
		'value' => locateFile("jhead"),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => "text",
		'attrs' => array('size' => 50)
	),
	'exiftags' => array(
		'prompt' => "exiftags EXIF Parser",
		'desc' => sprintf(_("The filesystem path to the <b>%s</b> EXIF parser.  E.g. %s or %s. This is optional so if you don't have it, don't worry about it too much."),
			'<a href="http://johnst.org/sw/exiftags/" target="_blank">exiftags</a>', '<i>/usr/local/bin/exiftags</i>', '<i>c:\\bin\\exiftags.exe</i>'),
		'value' => locateFile("exiftags"),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => "text",
		'attrs' => array('size' => 50)
	),
	'use_jpegtran' => array (
		'prompt' => "Jpegtran",
		'desc' => sprintf(_("The filesystem path to the <b>jpegtran</b> utility.  E.g. %s or %s.  This is optional so if you don't have it, don't worry about it too much."), 
			'<i>/usr/local/bin/jpegtran</i>', '<i>c:\\bin\\jpegtran.exe</i>'),
		'value' => locateFile("jpegtran"),
		'must-be-file' => 1,
		'must-be-executable' => 1,
		'optional' => 1,
		'filename' => 1,
		'type' => "text",
		'attrs' => array ('size' => 50)
	),		
/*	
	'showContextHelp' => array(
		'prompt' => _("Show context help"),
		'desc' => _("Show context sensitive help links (little ? links that describe in-depth what each function of Gallery does)?") . newIn ("1.4.2"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "yes"
	),
*/
	'group5_end' => array (
		'type' => "group_end",
	),
	'group6' => array (
		'type' => "group_start",
		'name' => "group_languages",
		'default' => "none",
		'title' => _("Languages and locales"),
	),
	'default_language' => array(
		'prompt' => _("Default language"), 
		'optional' => 1, 
		'desc' => sprintf(_("This is the language you will see as %s, and what users will see if no other preference is set."), '<b>admin</b>'),
		'type' => (sizeof(gallery_languages()) > 1) ? "choices" : "hidden", 
		'choices' => array_merge(gallery_languages(), array('browser' => '>>> '. _("Language given by Browser") .' <<<')),
		'value' => (!empty($gallery->language) && sizeof(gallery_languages()) >1 ) ? $gallery->language : "en_US",
		'enabled' => $elawisyl
	),
	'ML_mode' => array(
		'prompt' => _("Multi language mode"), 
		'desc' => _("Gallery has three multi language modes.  These affect the language that a user will see when visiting the site."),
		'type' => ( (sizeof(gallery_languages()) > 1) && ($elawisyl == 'yes') ) ? "choices" : "hidden",
		'choices' => array(
				1 => "1: " . _("Gallery is only displayed in one language"), 
				2 => "2: " . _("Gallery is displayed in the language the browser sends"), 
				3 => "3: " . _("The User can choose the language via pulldown menu or national flags.")),
		'value' => ( (sizeof(gallery_languages()) > 1) && ($elawisyl == 'yes') ) ? 3 : 0,
	),
	'available_lang' => array(
		'prompt' => _("Available languages"), 
		'type' => (sizeof(gallery_languages()) > 1) ? "choices" : "hidden", 
		'optional' => 1, 
		'desc' => _("These languages will be available as user choices in a pulldown menu or national flags, if you have choosen language Mode 3, and will be available to registered users as their default language in Mode 2 or 3."), 
		'multiple_choices' => array_merge(gallery_languages(), array('addon' => insertFormJSLinks("available_lang[]"))),
		'value' => array_keys(gallery_languages()),
		'enabled' => $elawisyl
	),
	'show_flags' => array(
		'prompt' => _("Show flags?"),
		'type' => (sizeof(gallery_languages()) > 1) ? "choices" : "hidden",
		'desc' => _("In Mode 3 you can choose to display national flags instead of a small pulldown menu for the available languages."),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no",
		'enabled' => $elawisyl
	),
	'dateString' => array(
		'prompt' => "<b>(" . _("Advanced") .")</b><br> ". _("Format for date strings."),
		'desc' => sprintf(_("This format string will be used to display %s in %s.  It is recommended to use %s which will change format depending on language choice.  However, if you wish to change this string, please look at the %sPHP documentation%s."),
			_("date without time"), Gallery(), "<b>%x</b>",
			'<a href="'._("http://www.php.net/manual/en/function.strftime.php"). '" target="_blank">', '</a>') .  "<br>" .
			sprintf(_("For example, %s will display date like this: %s."), "<b>%b %d, %Y</b>", "<b>" . strftime("%b %d, %Y") . "</b>"), 
		'value' => "%x",
		'type' => "text",
		'attrs' => array('size' => 20)
	),
	'dateTimeString' => array(
		'prompt' => "<b>(" . _("Advanced") .")</b><br> ". _("Format for date/time strings."),
		'desc' => sprintf(_("This format string will be used to display %s in %s.  It is recommended to use %s which will change format depending on language choice.  However, if you wish to change this string, please look at the %sPHP documentation%s."),
			_("date with time"), Gallery(), "<b>%c</b>",
			'<a href="'._("http://www.php.net/manual/en/function.strftime.php"). '" target="_blank">', '</a>') .  "<br>" .
			sprintf(_("For example, %s will display date and time like this: %s."), "<b>%H:%M, %b %d, %Y</b>", "<b>" . strftime("%H:%M, %b %d, %Y") . "</b>"), 
		'value' => "%c",
		'type' => "text",
		'attrs' => array('size' => 20)
	),

   ),
	config_maybe_locales()
);
// Continue constants ...
$constants = array_merge($constants,
  array(
	'group6_end' => array (
		'type' => "group_end",
	),
	'group_email' => array (
		'type' => "group_start",
		'name' => "group_email",
		'default' => "none",
	       	'title' => _("Email and Registration"),
	       	'desc' => sprintf(_("If you are having problems with email, please run the %s diagnostic."),
		       	'<a href="check_mail.php">' . _("Check Email") . '</a>'),
	),
	'emailOn' => array(
		'prompt' => _("Enable email"),
		'verify-func' => "verify_email",
	       	'desc' => sprintf(_("Turns email on for the whole gallery.  If you set this to %s then none of the rest of this section will apply."), '<i>' . _("no") . '</i>' ) .  '<br>' .
					'<span class="emphasis">' . _("Note:") . '</span> ' .
					_("If you enable these settings you give your users the opportunity to let Gallery send them their lost password via email.") . '<br>'. 
					'<span class="attention">' . _("You need to enable this setting if you want to enable Self Registration.") . '</span>',
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no"
	),
	'adminEmail' => array(
		'prompt' => _("Admin email address"),
		'desc' => _("Set this value to a valid working email address to receive email notices upon certain user actions and also so that users know whom to reach in case of problems. You may specify more than one address by separating them with commas.") .
		"<br>",
		'type' => "text",
		'value' => "",
		'attrs' => array('size' => 50),
		'optional' => 1
	),
	'senderEmail' => array(
		'prompt' => _("Sender email address"),
		'desc' => _("Set this value to a valid working email address.  Emails that are automatically sent by Gallery will use this as a return address.  You should monitor this email address for bounce messages and replies from recipients.") .
		"<br>",
		'type' => "text",
		'value' => "",
		'attrs' => array('size' => 50),
		'optional' => 1
	),
	'emailSubjPrefix' => array(
		'prompt' => _("Email subject prefix"),
		'desc' => _("All Gallery emails will have this as the first part of their subject."),
		'type' => "text",
		'attrs' => array('size' => 50),
		'value' => (!empty( $gallery->app->galleryTitle)) ?
			"[" . $gallery->app->galleryTitle . "]"	:
	     		"[" . Gallery() . "]"
	),
	'emailGreeting' => array(
		'prompt' => _("Welcome email message text"),
	       	'desc' => placeholderDescription(),
		'optional' => 1,
		'type' => "textarea",
		'value' => "",
		'attrs' => array('cols' => 50, 'rows' => 11)),
	'selfReg' => array(
		'prompt' => _("Allow visitors to self register."),
		'desc' => _("Enabling this will allow visitors to your site to create their own login accounts.") . '<br>' .
			 	'<span class="attention">' . _("This option requires the Email feature to be enabled.") . '</span>',
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no",
		'requires-features' => array('emailOn' => _("Email"))
	),
	'selfRegCreate' => array(
		'prompt' => _("Self registered users can create albums."),
		'desc' => _("Enabling this will allow self registered users to create their own albums as soon as they register.  Not having this enabled means you will have to give them permission manually before they can create or alter any contents of the Gallery.  This setting is ignored if self-registration is disabled.") .
		"<br>",
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no"),
	'multiple_create' => array(
		'prompt' => _("Enable bulk account creation"),
		'desc' => _("This option allows you to create many new user accounts at one time by creating a file of new users.") .
		"<br>",
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no"
	),
	'adminCommentsEmail' => array(
		'desc' => _("This allows the &quot;admin&quot; account to get email whenever a comment is added to any album"),
		'prompt' => _("Admin comment email"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no"
		),
	'adminOtherChangesEmail' => array(
		'desc' => _("This allows the &quot;admin&quot; account to get email whenever a change is made to any album"),
		'prompt' => _("Admin Other Changes email"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no"
		),

	'email_notification' => array(
		'prompt' => _("Admin notification of email activity"),
		'desc' => _("You can keep a record of every email that is sent by Gallery.") .
		"<br>",
		'multiple_choices' => array('email' => _("Send summary email"), 
			'logfile' => _("Log to a file"),
			'bcc' => _("Send duplicate email")),
		'optional' => 1,
		'value' => array()
	),
        'useOtherSMTP' => array(
                'prompt' => _("Use non-local SMTP server."),
                'desc' => _("Use a smtp server running on a different machine (ie: Your ISPs mail server) to process outgoing gallery email."),
                "<br>",
                'choices' => array('no' => _("no"), 'yes' => _("yes")),
                'optional' => 1,
                'value' => 'no',
        ),
        'smtpHost' => array(
                'prompt' => _("SMTP Server"),
                'desc' => _("The name or IP address of the SMTP Server you want to use."),
                "<br>",
                'type' => "text",
                'value' => "localhost",
                'attrs' => array('size' => 60),
                'optional' => 1
        ),
        'smtpFromHost' => array(
                'prompt' => _("Sender hostname"),
                'desc' => _("The full hostname of the server you are sending the mail from (The Server where your Gallery is installed on). If your Server is behind a NAT, this would be the DNS name of your router."),
                'type' => "text",
                'value' => (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : 'localhost',
                'attrs' => array('size' => 60),
                'optional' => 0
        ),
	'smtpPort' => array(
                'prompt' => _("SMTP Port"),
                'desc' => _("The port on the SMTP server you are connecting to."),
                "<br>",
                'type' => "text",
                'value' => "25",
                'attrs' => array('size' => 10),
                'optional' => 1
        ),
        'smtpUserName' => array(
                'prompt' => _("SMTP account name"),
                'desc' => _("If your provide requires a username for outgoing email, use it here, otherwise leave blank."),
                "<br>",
                'type' => "text",
                'value' => "",
                'attrs' => array('size' => 20),
                'optional' => 1
        ),        
	'smtpPassword' => array(
                'prompt' => _("SMTP password"),
                'desc' => _("Password for sending email through smtp server. NOTE: This password is stored in cleartext in config.php. Make sure that permissions are set to keep others from getting at it!"),                
		"<br>",
                'type' => "password",
                'value' => "",
		'verify-func' => 'verify_password',
                'attrs' => array('size' => 20),
                'optional' => 1
        ),
	'group_email_end' => array (
		'type' => "group_end",
        ),
	'group7' => array (
		'type' => "group_start",
		'name' => "group_slideshow",
		'default' => "none",
		'title' => _("Gallery wide slideshow"),
		'desc' => _("Adds a slide show link on the main page of the gallery.   Note for large galleries this may run very slowly and use a lot of system resources.") . "  " .
			  _("These settings do not apply to the low tech slideshow."),
	),
	'gallery_slideshow_type' => array(
		       	'prompt' => _("Slideshow preferences"),
		       	'desc' => "<dl>"
				."<dt>". _("Off") . "</dt>"
				."<dd>". _("No slideshow link will appear.") . "</dd>"
				."<dt>". _("Ordered") . "</dt>"
				."<dd>". _("Images will display in the same order as they appear in the Gallery.") . "</dd>"
				."<dt>". _("Random") . "</dt>"
				."<dd>". _("Images will appear in a random order.") . "</dd>"
				."</dl>",
		       	'choices' => array(
			       	'off' => _("Off"),
			       	'ordered' => _("Ordered"),
			       	'random' => _("Random"),
				),
		       	'value' => "off",
			),
	'gallery_slideshow_length' => array(
		'prompt' => _("Slideshow length"),
		'desc' => _("Number of images to display in slideshow.  Set to 0 or blank to display all images.  Set to a number if slideshow is random and you want to display only a selection of the images."),
		'type' => "text",
		'value' => "20",
		'attrs' => array('size' => 5),
		'allow_empty' => true
	),
	'gallery_slideshow_loop' => array(
		'prompt' => _("Allow slideshow to loop"),
		'desc' => sprintf(_("This option will allow visitors to have your slideshows repeat until they close the window or go to another page.  Set it to %s if you want to save bandwidth."), "<b>"._("no")."</b>"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
	       	'value' => "yes"
	),
	'slideshowMode' => array(
		'prompt' => _("Slideshow Mode"),
		'desc' => _("This allows you to set the default mode for the slideshow.  &quot;Java Applet&quot; requires that the user has the Java Runtime Environment installed in their browser, but allows for fullscreen viewing. &quot;JavaScript&quot; is supported by all modern browsers without extra plugins, but is more limited than the Java Applet."),
		'choices' => array('applet' => _("Java Applet"), 'high' => _("JavaScript")),
		'value' => "high"
	),
	'group7_end' => array (
		'type' => "group_end",
        ),
	'group_comments' => array (
		'type' => "group_start",
		'name' => "group_comments",
		'default' => "none",
		'title' => _("Comments"),
	),
	'comments_enabled' => array(
			'prompt' => _("Enable commenting"),
			'desc' => _("This option will enable or disable the possibility for your users to make comment.  If you set this to &quot;no&quot; all comment functionality is disabled."),
			'choices' => array('yes' => _("yes"), 'no' => _("no")),
			'value' => "yes"
			),
	'comments_indication' => array(
		       	'prompt' => _("Indicate if there are comments available"),
		       	'desc' => "<dl>"
				. "<dt>" . _("Off") . "</dt>"
				. "<dd>" . _("No indication that an item has comments.") . "</dd>"
				. "<dt>" . _("Photos") . "</dt>"
				. "<dd>" . _("Images that have comments will be marked.") . "</dd>"
				. "<dt>" . _("Albums"). " </dt>"
				. "<dd>" . _("Albums and sub-albums that have comments will be marked.") . "</dd>"
				. "<dt>" ._("Both") . "</dt>"
				. "<dd>" . _("Images and albums that have comments will be marked.") . "</dd>"
				. "</dl>",
		       	'choices' => array(
			       	'off' => _("Off"),
			       	'photos' => _("Photos"),
			       	'albums' => _("Albums"),
			       	'both' => _("Both"),
				),
		       	'value' => "photos",
			),
	'comments_indication_verbose' => array(
		       	'prompt' => _("Display date of last comment?"),
		       	'desc' => sprintf(_("If an item has comments, display date of last comment, rather than a %s."), '"*"'),
			'choices' => array('yes' => _("yes"), 'no' => _("no")),
		       	'value' => "no",
			),
	'comments_anonymous' => array(
		       	'prompt' => _("Anonymous comments from known users?"),
		       	'desc' => sprintf(_("When logged in, comments will automatically use format described below, unless this is set to %s."), 
				"<b>". _("yes"). "</b>"),
			'choices' => array('yes' => _("yes"), 'no' => _("no")),
		       	'value' => "no",
			),
	'comments_display_name' => array(
			'prompt' => _("Format for commenter names"),
			'desc' => _("Commenter names of logged in users can be displayed in a variety of different ways"),
			'choices' => displayNameOptions(), 
			'value' => "!!FULLNAME!! (!!USERNAME!!)",
			),
	'comments_addType' => array(
			'prompt' => _("Add comments inside the photo page"),
			'desc' => _("Do you want to have the possibility to add comments inside the view photo page, or do you prefer a link to a popup"),
			'choices' => array ('popup' => _("Popup"), 'inside' => _("Inside")),
			'value' => "popup"
			),
	'comments_length' => array(
			'prompt' => _("Maximum length of comments"),
			'desc' => _("Enter a suitable value for the maximum length of comments to prevent abuse. Gallery limits comments to no more than 1000 characters.  This value will be used if less than 1000."),
			'type' => "text",
			'must-be-integer' => 1,
			'value' => 300
	),
	'comments_overview_for_all' => array(
			'prompt' => _("Can every permitted user see a comments overview"),
			'desc' => _("Do you want to allow every user who is allowed to view comments (set in permission) to see a complete overview of all comments of an Album? Set this to &quot;no&quot; if only admin and owners can use this feature."),
			'choices' => array('yes' => _("yes"), 'no' => _("no")),
		       	'value' => "no",
	),
	'group_comments_end' => array (
		'type' => "group_end",
        ),
	'group_watermark_start' => array(
		'type'		=> 'group_start',
		'name'		=> 'group_watermark',
		'default'	=> 'none',
		'title'		=> _("Watermarking"),
		'desc'		=> ($enablewatermark == 'yes') ? 
					_("Choose wether you want to enable or disable watermarking. And set the defaults") :
				'<span class="attention">' . _("Watermarking is not possible in this installation !") . '</span>'
	),
	'watermarkDir' => array(
		'prompt' => _("Watermark directory"),
		'desc' => sprintf(_("The full path on physical disk to the directory where your watermark images will be stored.  This directory may contain .png, .tif, and .gif images used to overlay visible watermarks on photos. These formats are supported because they may contain transparency or alpha channels.  <b>Leaving this blank will disable watermarking support.</b> Note that this directory needs to have read access by the user who is running the web server (in your case this user is %s)."),
			"<b>". $webserver_user . "</b>"),
		'value' => "",
		'enabled' => $enablewatermark,
		'optional' => 1,
		'filename' => 1,
		'type' => "text",
		'attrs' => array('size' => 50)
	),
	'watermarkSizes' => array(
		'prompt' => _("Which Imagetype is watermarked"),
		'desc' => _("Here you can choose which of the images should be watermarked per default."),
		'value' => 0,
		'enabled' => $enablewatermark,
		'optional' => 1,
		'choices' => array(
			0 => _("Watermark both resized and full images"),
			1 => _("Watermark resized image only"),
			2 => _("Watermark full sized image only")),
		'value' => 0,
	),
	'group_watermark_end' => array (
		'type' => "group_end",
        ),
	'group_stats_start' => array(
		'type'		=> 'group_start',
		'name'		=> 'group_stats',
		'default' 	=> 'none',
		'title'		=> _("Statistics"),
		'desc'		=> _("Caching improves performance for a large gallery at the cost of potentially displaying photos that are out of order or with out of date rating information.") . "\n<br>".
				   _("There's a different cache for each stats type supported with the exception of random."). "\n<br>".
				   _("The caches can be refreshed by reloading the stats page and specifying the url variable 'refreshcache'."). "\n<br>".
				   _("Caches will also refresh themselves after they have expired."). "\n<br>".
				   _("The expire time in seconds is specified for each cache below."). "\n<br>".
				   _("If the expire time is set to -1 then the cache will not auto refresh.")
		),

	'stats_foruser'	=> array(
		'prompt'		=> _("Available Stats for 'normal' users"),
		'desc'			=> _("Gallery can give a lot of statistics for admins, however you can enable some of them to normal users.") ."\n<br>".
					   sprintf(_("For every selected item Gallery will create a link on the mainpage. The stats are based on the defaults defined in %s."), "'<i>includes/stats/stats.inc.php</i>'"),
		'multiple_choices' 	=> $stats_choices,
		'optional'	=> 1
	),
	'stats_viewsCacheOn'	=> array(
		'prompt' => _("View cache"),
		'choices' => array(1 => _("On"), 0 => _("Off")),
		'value' => 0,
	),
	'stats_viewsCacheExpireSecs' => array(
		'prompt' => _("View cache expiration"),
		'type' => "text",
		'must-be-number' => 1,
		'value' => 60
	),
	'stats_commentsCacheOn'	=> array(
		'prompt' => _("Comment cache"),
		'choices' => array(1 => _("On"), 0 => _("Off")),
		'value' => 0,
	),
	'stats_commentsCacheExpireSecs' => array(
		'prompt' => _("Comment cache expiration"),
		'type' => "text",
		'must-be-number' => 1,
		'value' => 600
	),
	'stats_dateCacheOn'	=> array(
		'prompt' => _("Date cache"),
		'choices' => array(1 => _("On"), 0 => _("Off")),
		'value' => 0,
	),
	'stats_dateCacheExpireSecs' => array(
		'prompt' => _("Date cache expiration"),
		'type' => "text",
		'must-be-number' => 1,
		'value' => -1
	),
	'stats_votesCacheOn'	=> array(
		'prompt' => _("Vote cache"),
		'choices' => array(1 => _("On"), 0 => _("Off")),
		'value' => 0,
	),
	'stats_votesCacheExpireSecs' => array(
		'prompt' => _("Vote cache expiration"),
		'type' => "text",
		'must-be-number' => 1,
		'value' => 3600
	),
	'stats_ratingsCacheOn'	=> array(
		'prompt' => _("Rating cache"),
		'choices' => array(1 => _("On"), 0 => _("Off")),
		'value' => 0,
	),
	'stats_ratingsCacheExpireSecs' => array(
		'prompt' => _("Rating cache expiration"),
		'type' => "text",
		'must-be-number' => 1,
		'value' => 3600
	),
	'stats_cDateCacheOn'	=> array(
		'prompt' => _("Capturedate cache"),
		'choices' => array(1 => _("On"), 0 => _("Off")),
		'value' => 0,
	),
	'stats_cDateCacheExpireSecs' => array(
		'prompt' => _("Capturedate cache expiration"),
		'type' => "text",
		'must-be-number' => 1,
		'value' => -1
	),
	'group_stats_end' => array(
		'type'		=> 'group_end'
	),
	'group_technical' => array (
		'type' => "group_start",
		'name' => "group_technical",
		'default' => "none",
		'title' => _("Technical"),
		'desc' => _("The settings in this section are intended for advanced users only.") . 
			'<br><span class="attention">'.
			_("Be aware that settings changed on this tab may have severe performance or security consequences and do so at your own risk.") .
			'<span>'),
	'debuglevel' => array(
		'prompt' => _("Debug Level"),
		'desc' => _("Turn debug on so that you can diagnose problems?") .'<br><span class="emphasis">'. _("Note: Dont leave this option on in production systems! Else you might show users more information about your Gallery and system than you want!") . '</span>',
		'choices' => array(0 => _("No Debug"), 1 => _("Normal"), 2 => _("High (former Debug 'on')")),
		'value' => 0,
	),
	'skipRegisterGlobals' => array(
		'prompt' => _('Disable register_globals check'),
		'desc' => sprintf(_('<b>Be aware that changing this setting potentially decreases the security of your Gallery, and we may or may not support you if you encounter issues that don\'t occur when this is turned off.</b><br><br>Enabling this causes %s to ignore register_globals being turned on.  %s normally tries to unset all variables created by register_globals to help enforce security.  If a script that you run requires register_globals to be on, and will not cooperate with %s, set this to &quot;yes&quot;.'), Gallery(), Gallery(), Gallery(), Gallery()),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
                'value' => "no",
	),
        'timeLimit' => array(
		'prompt' => _("Time Limit"),
		'desc' => _("The maximum amount of time in seconds any single operation should take.  If you find that you are getting the <b>Maximum execution time exceeded</b> error message, you can try making this a larger number.  Be warned that if you make this too large you <i>might</i> have a process that is stuck for a very long time without doing anything.  The default value is 30 which is pretty reasonable."),
		'type' => "text",
		'must-be-integer' => 1,
		'must-not-be-zero' => 1,
		'value' => 30,
		'attrs' => array('size' => 5),
	),
	'blockRandomCache' => array(
		'prompt' => _("Random Block Cache Time"),
		'desc' => _("This is the amount of time that the Random Block will cache data about your photos and albums, speeding up requests for random photos.  The more often that your albums are modified, the lower you should set this value to ensure that new photos are chosen quickly, and to prevent attempts to retrieve photos which may have been deleted. The cache is automatically regenerated when a user views the random block after the time limit has expired."),
		'value' => 86400,
		'choices' => array(
				86400 => _("24 Hours"), 
				43200 => _("12 Hours"), 
				21600 => _("6 Hours"), 
				1440 => _("1 Hour"), 
				720 => _("30 Minutes")
				)
	),
	'blockRandomAttempts' => array(
		'prompt' => _("Random Block Retrieval Attempts"),
		'desc' => _("This variable controls the number of times that the random block will attempt to retrieve an image from Gallery.  The larger the value, the less likely a 'No Photo Chosen' will be returned, but it increases the time it takes for the Random Block to load."),
		'value' => 2,
		'must-be-integer' => 1,
		'must-not-be-zero' => 1,
		'type' => "text",
	),
	'cacheExif' => array(
		'prompt' => "<b>(" . _("Advanced") . ")</b><br> ". _("Cache EXIF data"),
		'desc' => _("The EXIF data extracted from JPEG files can be cached after it is read the first time.  The tradeoff is that if you cache this data it's possible for your album data files to get quite large which may slow down viewing your albums.  But, if you don't cache this data then it needs to be retrieved every time any user looks at the properties of a photo which can also be slow."),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no"
	),

	'devMode' => array(
		'prompt' => _("Developer Mode"),
		'desc' => _("Turn on developer mode to see all PHP warnings and notices.  Enabling this is not recommended for users that aren't familiar with PHP."),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no",
	), 
	'useSyslog' => array(
		'prompt' => _("Use Syslog"),
		'desc' => _("Report user login, logout, and serious errors via syslog (UNIX) or System Event Manager (Windows)"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no",
	),
	'use_flock' => array(
		'prompt' => sprintf(_("Use %s"), " flock()"),
		'desc' => _("Gallery uses a locking system called flock() for added protection against data corruption.  This is not supported on all platforms.  If you're seeing errors like <i>Could not acquire lock</i> then set this value to <i>no</i>."),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "yes"),
	'expectedExecStatus' => array(
		'prompt' => _("Status code"),
		'desc' => _("The status code Gallery expects to see after a successful <i>exec()</i> call.  Gallery will try to figure this out on its own so you probably won't need to change this value unless you have problems."),
		'value' => detect_exec_status(),
		'type' => "text",
		'attrs' => array('size' => 5),
	),
	'sessionVar' => array(
		'prompt' => _("Session variable"),
		'desc' => _("This is the session variable used to contain all Gallery session information.  Change this if there is a conflict between Gallery and another PHP app on your website."),
		'value' => "gallery_session",
		'type' => "text",
		'attrs' => array('size' => 20),
	),
	'group_technical_end' => array (
		'type' => "group_end",
        ),
	'group_rss' => array(
		'type' => 'group_start',
		'name' => 'group_rss',
		'default' => 'none',
		'title' => _("RSS Publisher"),
		'desc' => _("Options for the Gallery RSS Publisher"),
	),
	'rssEnabled' => array(
		'prompt' => _("Enable RSS Generator"),
		'desc' => _("Enable or Disable the Gallery RSS Generator"),
		'choices' => array(
			'yes' => _("Yes"),
			'no' => _("No")),
		'value' => "yes",
	),
	'rssMode' => array(
		'prompt' => _("RSS Content"),
		'desc' => _("Specifies which information is to be included in your RSS feed."),
		'choices' => array(
			'basic' => _("Basic information only"),
			'highlight' => _("Album highlight"),
			'thumbs' => _("All image thumbnails"),
			'thumbs-with-captions' => _("All image thumbnails and captions")),
		'value' => "basic",
	),
	'rssHighlight' => array(
		'prompt' => _("RSS Highlight"),
		'desc' => _("Specify which album will be displayed as the icon for all feed entries: An empty field means that there will be no default highlight.  '*' uses the most recently update album's highlight.  Specifying an album name will use that album's highlight image."),
		'type' => "text",
		'attrs' => array('size' => 30),
		'optional' => 1,
	),
	'rssMaxAlbums' => array(
		'prompt' => _("Maximum Albums Returned"),
		'desc' => _("This value is the maximum number of albums that the RSS feed will display to the user. Only the most recently modified albums are chosen for this value."),
		'value' => 25,
		'type' => "text",
		'must-be-integer' => 1,
		'must-not-be-zero' => 1,
	),	
	'rssVisibleOnly' => array(
		'prompt' => _("Visible Albums Only"),
		'desc' => _("Specifies whether hidden albums should be included in the RSS feed.  This is not advised unless you are the only Gallery user.  Other users could become very upset if their hidden images become displayed to the public."),
		'choices' => array(
			'yes' => _("Display Visible Albums Only"),
			'no' => _("Display Hidden Albums")),
		'value' => "yes",
	),
	'rssDCDate' => array(
		'prompt' => _("Use dc:date"),
		'desc' => _("(Advanced) If users complain about their RSS readers being unable to process the RSS Feed, try enabling this option.  Including dc:date is not RSS 2.0 compliant, but some feed readers require it anyway. Turning this on will cause your feed to not validate as RSS 2.0."),
		'choices' => array(
			'yes' => _("Yes"),
			'no' => _("No")),
		'value' => "no",
	),
	'rssBigPhoto' => array(
		'prompt' => _("Use Big Photo"),
		'desc' => _("(Advanced) If users complain about their RSS readers corrupting the channel thumbnail, you can turn this feature on.  Turning this on will cause your feed to not validate as valid RSS 2.0."),
		'choices' => array(
			'yes' => _("Yes"),
			'no' => _("No")),
		'value' => "no",
	),
	'rssPhotoTag' => array(
		'prompt' => _("Use Photo Tag"),
		'desc' => _("(Advanced) Turning this off will cause two RSS extensions which allow for images representing items to be removed from the feed.  Leaving this enabled should cause no problems - disabling it is a personal preference."),
		'choices' => array(
			'yes' => _("Yes"),
			'no' => _("No")),
		'value' => "yes",
	),
	'group_rss_end' => array(
		'type' => "group_end"
	),
	/* Keep these two at the end of the list because they're hidden */
	'userDir' => array(
		'desc' => "Filesystem location of user information",
		'prompt' => ".users directory",
		'type' => "hidden",
		'value' => "",
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
		'title' => _("Layout")
	),
	'cols' => array(
		'prompt' => _("Default Columns"),
		'desc' => _("The default number of columns in an album."),
		'value' => 3,
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9),
	),
	'rows' => array(
		'prompt' => _("Default Rows"),
		'desc' => _("The default number of rows in an album."),
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
		'title' => _("Appearance")
        ),
	'bordercolor' => array(
		'prompt' => _("Border color"),
 		'desc' => sprintf(_("Default color of (image) borders. You can learn about %scolors%s here."),    
			   '<a href="http://www.permadi.com/tutorial/websafecolor/" target="_blank">',
			   '</a>'),
			   // '  <a href="http://gotomy.com/color.html">' . 

		// <a href="http://www.w3.org/TR/REC-html40/types.html#h-6.5">W3.org</a>',
 		'value' => "black",
		'type' => 'colorpicker'
	),
	'border' => array(
		'prompt' => _("Border width"),
		'desc' => _("Default width of (image) borders."),
		'value' => 1,
		'choices' => array(0 => 0, 1 => 1, 2 => 2, 3 => 3)
	),
	'font' => array(
		'prompt' => _("Default Font"),
		'desc' => _("Default font face.  E.g. <i>arial</i> or <i>trebuchet</i>"),
		'value' => "arial",
		'attrs' => array('size' => 10),
		'type' => "text"
	),
	'group_appearance_end' => array (
		'type' => 'group_end',
	),

	'group3' => array (
		'type' => 'group_start',
		'name' => 'group_display',
		'default' => 'none',
		'title' => _("Image Processing and Display")
	),
	'thumb_size' => array(
		'prompt' => _("Thumbnail size"),
		'desc' => _("The default target size (in pixels) of album thumbnails."),
		'value' => 150,
		'attrs' => array('size' => 10),
		'type' => "text"
	),
	'resize_size' => array(
		'prompt' => _("Resized image size"),
		'desc' => _("When large images are added to an album, an intermediate version will automatically be generated at this size."),
		'value' => 640,
		'choices' => array(0 => _("off"), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024)
	),
	'resize_file_size' => array(
		'prompt' => _("Resized JPEG/PNG images file size"),
		'desc' => _("The default target size (in kilobytes) of resized JPEG/PNG images.  Including a value may slow down file uploads, but will speed up page viewing time especially for people with dialup connections.  Set to 0 for no target size."),
			  
		'value' => 0,
		'optional' => 1,
		'type' => "text"
	),
	'max_size' => array(
		'prompt' => _('Maximum image size'),
		'desc' => _("You can optionally limit the dimensions your images on upload to Gallery.") . 
			'<br><span class="attention>'. _("Warning:") .'</span> '. 
			_("Enabling this feature will leave you <b>without a full size original</b> image in your Gallery, but allows you to save limited hard drive space. Gallery will attempt to preserve JPEG comments and EXIF data for the shrunken image, but this will require either <b>ImageMagick</b> or the optional <b>jhead</b> programs (see Step 2)."),
		'value' => 'off',
		'choices' => array(0 => _('off'), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024, 1280 => sprintf(_('%d (%d MPix)'), 1280, 1), 1600 => sprintf(_('%d (%d MPix)'), 1600, 2), 2048 => sprintf(_('%d (%d MPix)'), 2048, 3))
	),
	'max_file_size' => array(
		'prompt' => _('Maximum JPEG/PNG image file size'),
		'desc' => _("You can optionally limit the filesize of your JPEG/PNG images on upload to Gallery.") . 
			'<br><span class="attention">'. _("Warning:") . '</span> '. 
			_("Enabling this feature will leave you <b>without an original quality image</b> in your Gallery. Set the default target filesize (in kilobytes).  Including a value may slow down file uploads, but will speed up page viewing time especially for people with dialup connections. Set to 0 if you don't want to restrict filesize."),
		'value' => 0,
		'optional' => 1,
		'type' => 'text'
	),
	'useOriginalFileNames' => array(
		'prompt' => _("Preserve Names"),
                'desc' => _("When uploading images to Gallery, should we preserve the original names?  If you select <i>no</i>, Gallery will assign a new name for each photo or movie that gets uploaded."),
                'choices' => array('yes' => _("yes"), 'no' => _("no")),
                'value' => "yes",
	),
	'add_to_beginning' => array(
		'prompt' => _("Add to beginning"),
		'desc' => _("New images added to beginning rather than end of albums."),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no",
	),
	'fit_to_window' => array(
		'prompt' => _("Fit-to-window"),
		'desc' => _("When viewing photos which don't have an intermediate version, Gallery can automatically resize the image to fit the viewer's browser window (using JavaScript).  This may not work in embedded environments such as Nuke."),
		'value' => "no",
		'choices' => array('yes' => _("yes"), 'no' => _("no"))
	),
	'use_fullOnly' => array(
		'prompt' => _("Visitor size prefs"),
		'desc' => _("Offer visitors the choice of always viewing the full sized version of an image instead of the intermediate size.  This setting only lasts for the visitor's session."),
		'value' => "no",
		'choices' => array('yes' => _("yes"), 'no' => _("no"))
	),
	'group3_end' => array (
		'type' => 'group_end',
	),
	'group4' => array (
		'type' => 'group_start',
		'name' => 'group_options',
		'default' => 'none',
		'title' => _("Options")
	),
	'print_photos' => array(
		'prompt' => _("Printing Services"),
 		'desc' => _("Allow visitors to print photos directly from the album.  Fotoserve is based in the United Kindom and Fotokasten is a print service based in Germany so they are good for the European market. EZ Prints, PhotoWorks and Shutterfly are based in the United States. mPUSH is a service that allows you to send photos to cellular phones worldwide. All services will give a small donation to the Gallery project for every print you make. Using these services is a great way for you to help support this free software."),
		'multiple_choices' => makeMultipleChoiceContent($services),
/* old .. needs to be fixed */
		'value' => array(
			'fotokasten' => array(
				'checked' => true),
			'photoaccess' => array(
				'checked' => true),
			'shutterfly' => array(
				'checked' => true),
			'fotoserve' => array(
				'checked' => true),
			'mpush' => array(
				'checked' => true,
				'account' => 'gallery'),
		),
		'optional' => true
	),
	'mPUSHAccount' => array(
		'prompt' => _("Account for the mPUSH service"),
		'desc' => sprintf(_("If you want to use mPUSH cellular photo service you can either use your own account, or leave this to 'gallery'. Visit this link to %screate your own account%s."), 
				'<a href="http://mpush.msolutions.cc/safe.php?p=Register&ref=gallery" target="_blank">', '</a>'),
		'value' => 'gallery',
		'type' => 'text',
	),
	'ecards' => array(
                'prompt' => _("Enable Ecards ?"),
                'desc' => _("Set this to 'yes' to allow users to send a picure as eCard via eMail") . '<br>'.
			'<span class="attention">' . _("This option requires the Email feature to be enabled.") . '</span>',
                'choices' => array("yes" => _("yes"), "no" => _("no")),
                'value' => 'no'
        ),
	'returnto' => array(
		'prompt' => _("Gallery Link"),
		'desc' => _("Display a link navigation from your album to the top level Gallery link within the album?  If you turn this feature off, the user will not be able to navigate out of the album.  This is only useful when you want to embed an album within a website and not allow users to (easily) view the other albums in the gallery."),
		'value' => "yes",
		'choices' => array('yes' => _("yes"), 'no' => _("no"))
	),
	'defaultPerms' => array(
		'prompt' => _("Default Album Permissions"),
		'desc' => _("Defines the default set of permissions for an album.  Specifically, who can see the album, and view its photos."),
		'choices' => array(
				'everybody' => _("Everybody"), 
				'loggedin' => _("Logged-In Users"),
				'nobody' => _("Nobody")
		),
		'value' => "everybody",
	),
	'group4_end' => array (
		'type' => 'group_end',
	),
	'group5' => array (
		'type' => 'group_start',
		'name' => 'group_displayed_data',
		'default' => 'none',
		'title' => _("Data displayed")
	),
	'display_clicks' => array(
		'prompt' => _("Click counts"),
		'desc' => _("Display click counts within Albums?"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "yes",
	),
	'extra_fields' => array(
		'prompt' => _("Custom Fields"),
		'optional' => 1,
		'desc' => _("Custom fields that are available to describe each image.  They should be separated by commas.  E.g. <i>Description, Location, Film Type</i>"),
		'attrs' => array('size' => 80),
		'type' => "text",
		'value' => "Description"
	),
	'showDimensions' => array(
		'prompt' => _("Display clickable image dimensions"),
		'desc' => _("Display clickable image dimensions beneath the thumbnails.  Will show dimensions for resized images (if any) first, then for the full image, depending on album settings user permissions.  You may click on these instead of the thumbnail to either image."),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no"
	),
	'group5_end' => array (
		'type' => 'group_end',
	),
	'group6' => array (
		'type' => 'group_start',
		'name' => 'group_itemOwner',
		'default' => 'none',
		'title' => _("Item Owner")
	),
	'item_owner_modify' => array(
		'prompt' => _("Photo owner modification"),
		'desc' => _("Allow photo owners to edit and rotate/flip their own photos?"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "yes",
	),
	'item_owner_delete' => array(
		'prompt' => _("Photo owner deletion"),
		'desc' => _("Allow photo owners to delete their own photos?"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "yes",
	),
	'item_owner_display' => array(
		'prompt' => _("Photo owner display"),
		'desc' => _("Display the name of photo owners with the caption?"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no",
	),
	'group6_end' => array (
		'type' => 'group_end'
	),
	'group7' => array (
		'type' => 'group_start',
		'name' => 'group_polling',
		'default' => 'none',
		'title' => _("Polling configuration"),
		'desc' => 
			sprintf(_("It allows your visitors to vote for or rate the contents of an album.  You set defaults for polling here, which will apply to new albums, and to all existing albums when you first upgrade to version %s."),
				"1.4.1") .
			"<p>" .
			_("In each album, voting can be switched off, restricted to logged in users or open to all visitors.") 
			. "<p>" .
			sprintf(_("There are two sorts of polls: %s, which allows voters to chose their favourites as first, second, third etc., and %s, which allows voters to rate any or all of the images without restriction.  %s is like a film review where all the images can get (for example) 5 points, while %s is useful in scoring competitions."),
				"<b>"._("Rank")."</b>",
				"<b>"._("Critique")."</b>",
				_("Critique"),
				_("Rank")) 
	),
	'voter_class' => array(
		'prompt' => _("Voter class"),
		'desc' => _("Voting is available to:") . 
			"<dl>"
			. "<dt>" . _("Nobody")."</dt>"
			. "<dd>" . _("Voting is off.") ."</dd>"
			. "<dt>" . _("Everybody") . "</dt>"
			. "<dd>" . _("Anyone can vote.  Votes are recorded against session ID.") ."</dd>"
			. "<dt>" . _("Logged in") ."</dt>"
			. "<dd>" . _("Registered users can vote.  Votes are recorded against User ID, and voters can come back and change their choices.") . "</dd>"
			. "</dl>" ,
		'choices' => array('Nobody' => _("Nobody"), 
			'Everybody' => _("Everybody"), 
			'Logged in' => _("Logged in")),
		'value' => "Nobody"),
	'poll_type' => array(
		'prompt' => _("Poll type"),
	       	'desc' => _("Gallery offers two sorts of polls for voting for images.  See above for a more detailed explanation.") . 
			"<dl>"
			. "<dt>" . _("Rank")."</dt>"
			. "<dd>" . _("Users have a set of votes which they can allocate to the images.") . "</dd>"
			. "<dt>" . _("Critique")."</dt>"
			. "<dd>" . _("Enables users to give all images points without restriction.") . "</dd>"
			. "</dl>" ,
		'choices' => array('rank' => _("Rank"), 
			'critique' => _("Critique")),
		'value' => "critique"
			),
	'poll_scale' => array(
		'prompt' => _("Number of choices"),
		'desc' => _("Number of choices that will be available to voters as radio buttons or in a pulldown menu."),
		'value' => 3,
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)
			),
	'poll_hint' => array(
		'prompt' => _("Vote hint"),
		'desc' => _("This is displayed above the voting options under each image."),
		'type' => "text",
		'value' => _("Vote for this image"),
		'attrs' => array('size' => 50),
		'optional' => 1
			),
	'poll_show_results' => array(
		'prompt' => _("Show vote results"),
		'desc' => sprintf(_("Display a summary and graph on the album page of images that have received the highest votes, and provide a link to the full results.  If this option is %s, results of voting will only be available to admins and album owners."),
			_("no")),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no",
			), 
	'poll_num_results' => array(
		'prompt' => _("Number of results displayed"),
		'desc' => sprintf(_("If %s is %s, display a graph showing this many top results.  (Full results will be available on a linked page.)"),
			'"'. _("Show vote results").'"' ,
			'"'. _("yes").'"'),
		'value' => 3,
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)),

	'poll_orientation' => array(
		'prompt' => _("Orientation of vote choices?"),
		'desc' => _("Rank poll choices can be displayed beside each other (horizontally) or underneath each other (vertically).  Has no effect on critique voting which is a pulldown menu."),
		'value' => "vertical",
		'choices' => array(
			'horizontal' => _("Horizontal"), 
			'vertical' => _("Vertical"))) ,
	'poll_nv_pairs' => array(
		'prompt' => _("Vote words and values"), 
		'desc' => _("This allows you to configure the actual text that will be displayed for each voting option, plus the number of points that option is worth."),
		'value' => array (
			array('name' => _("Excellent"), 'value' => 5),
			array('name' => _("Very Good"), 'value' => 4),
			array('name' => _("Good"), 'value' => 3),
			array('name' => _("Average"), 'value' => 2), 
			array('name' => _("Poor"), 'value' => 1)),
		'type' => "nv_pairs",
		'verify-func' => "check_poll_nv_pairs",
		'size' => 9),
	'group7_end' => array (
		'type' => 'group_end',
	),
	'group8' => array (
		'type' => 'group_start',
		'name' => 'group_albumSlidshow',
		'default' => 'none',
		'title' => _("Album Slideshow"),
		'desc' => _("These settings do not apply to the low tech slideshow.")
	),
	'slideshow_type' => array(
	      	'prompt' => _("Slideshow Type"),
	       	'desc' => "<dl>"
			. "<dt>" . _("Off") ."</dt>"
			. "<dd>" . _("No slideshow link will appear.") . "</dd>"
			. "<dt>" . _("Ordered") . "</dt>"
			. "<dd>" . _("Images will display in the same order as they appear in the Gallery.") . "</dd>"
			. "<dt>" . _("Random") . "</dt>"
			. "<dd>" . _("Images will appear in a random order.") . "</dd>"
			. "</dl>",
	      	'choices' => array(
		       	'off' => _("Off"),
		       	'ordered' => _("Ordered"),
		       	'random' => _("Random"),
			),
	       	'value' => "ordered",
	),
	'slideshow_recursive' => array(
		'prompt' => _("Recursive slideshow"),
		'desc' => sprintf(_("Set to %s if you want to include images in sub albums as part of the slideshow."), "<b>"._("yes")."</b>"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "no"
	),
	'slideshow_loop' => array(
		'prompt' => _("Allow slideshow to loop"),
		'desc' => sprintf(_("This option will allow visitors to have your slideshow repeat until they close the window or go to another page.  Set it to %s if you want to save bandwidth."), "<b>"._("no")."</b>"),
		'choices' => array('yes' => _("yes"), 'no' => _("no")),
		'value' => "yes"
	),
	'slideshow_length' => array(
		'prompt' => _("Slideshow length"),
		'desc' => _("Number of images to display in slideshow.  Set to 0 or blank to display all images.  Set to a number if slideshow is random and you want to display only a selection of the images."),
		'type' => "text",
		'value' => "0",
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
		'title' => _("Micro Thumb Photo Navigation"),
		'desc' => _("These settings apply to the micro-thumb photo navigation system.  The micro thumbs are only visible when viewing individual images.")
	),
	'nav_thumbs' => array(
		'prompt' => _("Micro Thumb Photo Navigation"),
		'desc' => _("Uses micro-thumbnails for photo navigation. This creates a navigation panel with tiny thumbnails of the images around the photo you are viewing.  You can also choose both to display the micro thumb navigation system AND the old style navigation system."),
		'value' => "no",
		'choices' => array('yes' => _("yes"), 'no' => _("no"), 'both' => _("both"))
	),
	'nav_thumbs_style' => array(
		'prompt' => _("Micro Thumb Photo Navigation"),
                        'desc' => "<dl>"
                                . "<dt>" . _("Fixed") ."</dt>"
                                . "<dd>" . _("Displays prev+next groups of thumbs, changing only to display next group of thumbs.") . "</dd>"
                                . "<dt>" . _("Dynamic") . "</dt>"
                                . "<dd>" . _("Displays previos & next images, with current image between.") . "</dd>"
                                . "</dl>",
		'value' => "fixed",
		'choices' => array('fixed' => _("Fixed"), 'dynamic' => _("Dynamic"))
	),
	'nav_thumbs_first_last' => array(
		'prompt' => _("Show Micro Thumb for First/Last"),
		'desc' => _("This allows you to turn on and off the display of the first and last micro-thumb.  Since these two images are always the same, some might find that it is not very useful and would rather display more next/previous thumbs."),
		'value' => "no",
		'choices' => array('yes' => _("yes"), 'no' => _("no"))
	),
	'nav_thumbs_prev_shown' => array(
		'prompt' => _("Number of Previous Micro Thumbs"),
		'desc' => _("This is the number of previous micro thumbs on the screen at a time.  You might prefer to show fewer previous micro thumbs and more next micro thumbs."),
		'value' => "no",
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4,
				5 => 5, 6 => 6, 6 => 6, 7 => 7,
				8 => 8, 9 => 9, 10 => 10)
	),
	'nav_thumbs_next_shown' => array(
		'prompt' => _("Number of Next Micro Thumbs"),
		'desc' => _("This is the number of next micro thumbs on the screen at a time.  You might prefer to show fewer previous micro thumbs and more next micro thumbs."),
		'value' => "no",
		'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4,
				5 => 5, 6 => 6, 6 => 6, 7 => 7,
				8 => 8, 9 => 9, 10 => 10)
	),
	'nav_thumbs_location' => array(
		'prompt' => _("Location of Micro Thumb Navigation"),
		'desc' => _("Displays the micro-thumb navigation bar at the Top, Bottom or Both of the photo viewing page.  Since the micro thumb navigation system is larger than the original navigation system, you may want to display it at only the top or bottom to conserve space usage."),
		'value' => "top",
		'choices' => array(
			'top' => _("Top"), 
			'both' => _("Both"), 
			'bottom' => _("Bottom"))
	),
	'nav_thumbs_size' => array(
		'prompt' => _("Height of Micro Thumbs"),
		'desc' => _("The size of micro thumbnails will determine how clear the images are as well as how much room they take up.  This value is the height and is in pixels.  45 is a reasonable value for viewing at 1024x768."),
		'value' => '45',
		'type' => 'text'
	),
	'nav_thumbs_current_bonus' => array(
		'prompt' => _("Bonus to Current Micro Thumb's Size"),
		'desc' => _("This is the bonus in pixels to the size of the current micro-thumb.  This creates a nice look and a value of 10-15 is reasonable.  Zero (0) and negative numbers (-10) are also valid here.  Zero (0) will result in the current thumb being of identical size to all the other micro thumbs and a negative value will result in a smaller micro thumb."),
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
		'title' => _("Frames"),
		'desc' => sprintf(_("Each image (thumbnails, resized and full sized) in %s can have a frame.  The following styles available:"),Gallery()) .
		available_frames(true),
	),
	'album_frame' => array(
		'prompt' => _("Subalbum frames"),
		'desc' => _("This frame will appear around the thumbnail image representing a subalbum."),
		'choices' => available_frames(),
		'value' => "simple_book"
	),
	'thumb_frame' => array(
		'prompt' => _("Frame around thumbs"),
		'desc' => _("This frame will appear around the thumbnail image representing a photo or movie."),
		'choices' => available_frames(),
		'value' => "solid"
	),
	
	'image_frame' => array(
		'prompt' => _("Frame around images"),
		'desc' => _("This frame will appear around fulled sized and resized photos."),
		'choices' => available_frames(),
		'value' => "solid"
	),
	'group_frames_end' => array (
		'type' => 'group_end',
	),
);

?>

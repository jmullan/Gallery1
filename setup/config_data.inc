<?php /* $Id$ */ ?>
<?php

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

$common_messages["getting-netpbm"] = 
			sprintf(_("Gallery prefers NetPBM version 9.9 and up.  You can install just the binaries Gallery needs from %sGallery on SourceForge%s.  Or, you can compile and install the entire NetPBM package from %sNetPBM on SourceForge%s."), 
				 "<a href=\"http://sourceforge.net/projects/gallery\">",
				 "</a>",
				 "<a href=\"http://sourceforge.net/projects/netpbm\">",
				 "</a>")  .
			"<br>" .
			_("<b>Note:</b> You can install the binaries from the Gallery site even if you don't have root access on your box!  If you can't get it working, try leaving the NetPBM path blank and using ImageMagick intead.");
			
$common_messages["fail-netpbm-partial"] =
			_("I've found part of NetPBM, but some critical components are missing (see below).  Perhaps your version of NetPBM is out of date?") . "  " .
			$common_messages["getting-netpbm"];

$common_messages["getting-imagemagick"] =
			sprintf(_("Gallery prefers ImageMagick version 5.4.8 and up.  You can compile and install the entire ImageMagick package from %s." ), 
				"<a href=\"http://www.imagemagick.org\">www.ImageMagick.org</a>") .
			"<br>" .
			_("<b>Note</b>:  They also have binaries available for assorted operating systems.  If you can't get it working, try leaving the ImageMagick path blank and using NetPBM instead.");
					

$common_messages["open-basedir-active"] = 
	"<br>" . 
	sprintf(_("<b>Note:</b> Your webserver is configured with the %s restriction.   This may make it difficult for Gallery to detect and verify your binaries, even if you've already successfully used the Config Wizard in the past.  Don't worry too much if this page gives warnings; your settings will still be saved properly."),
			"<a href=\"http://www.php.net/manual/en/features.safe-mode.php#ini.open-basedir\">open_basedir</a>" );
			
$checklist = array(
	"gallery_version" => array(
		"optional" => 1,
		"desc" => sprintf(_("Check to see how old version %s of %s is."),
			Gallery(),
			"<i>$gallery->version</i>"),
	     	"func" => "check_gallery_version"
	),
	"php4" => array(
		"desc" => _("Check to see if you are running PHP 4.  Note that some versions of PHP have bugs that cause Gallery to fail.  PHP4.0.0 uses a different file upload protocol, versions 4.0.3 and 4.0.3pl1 have a bug in the unserialization code that cause Gallery to be unable to read its own database.  Upgrade to at least PHP 4.0.4pl1 if you can."),
	     	"func" => "check_php",
		"fail-too-old" => sprintf(_("You're running version %s.  You need to upgrade to PHP %s."), phpversion(), $MIN_PHP_MAJOR_VERSION),
		"fail-incompatible" => sprintf(_("You're running version %s which has known bugs that cause Gallery to fail."),  phpversion())
	),
	"php4.1" => array(
		"optional" => 1,
		"desc" => _("Check to see if you are running PHP 4.1 or greater.  If you want to run the multi-language version of gallery, you need php 4.1.  Upgrade to at least PHP 4.1 if you can."),
	     	"func" => "check_php4_1",
		"fail-too-old" => sprintf(_("You're running version %s.  You need to upgrade to at least PHP 4.1.0 if you want to run the multilanguage version."),
			phpversion())
	),
	"safe_mode" => array(
		"desc" => _("Check to see if PHP is running in <b>Safe Mode</b>.  Safe mode is a PHP security feature commonly used in shared environments.  Gallery cannot run when safe mode is enabled because it imposes too many restrictions."),
		"func" => "check_safe_mode",
		"fail-safe-mode" => sprintf(_("<b>Safe Mode</b> is enabled on this server.  You must disable it before you can use Gallery.  Refer to the FAQ, the forums, and the help page on the %sGallery website%s and the %sPHP website%s for assistance."),
			'<a href="http://gallery.sourceforge.net">',
			'</a>',
			'<a href="http://www.php.net/manual/en/features.safe-mode.php">',
			'</a>') .
		"<br><br>" .
			  sprintf(_("<b>Note</b>: In some rare circumstances, Gallery thinks that <b>safe mode</b> is on, when it's really off.  You can see for yourself by looking in the <i>Local</i> column of the safe_mode row in the %sPHP Info%s page.  If it says 'Off' or '0' or 'undefined' then it's possible that Gallery made a mistake and you can continue.  If not, then you must resolve this problem before you can use Gallery."),
				 '<a href="phpinfo.php">',
				 '</a>'),
		"optional" => 1,
		"serious" => 1
	),
	"htaccess" => array(
		"desc" => _("Allow the Gallery <b>.htaccess</b> file to set application specific configuration options for the Gallery application.  This is controlled by the webserver owner so it's not always possible for you to change it.  The main drawbacks to not having this are that you may not be able to upload files > 2MB (eg, big ZIP files) and you may not be able to use mod_rewrite (see below)."),
	     	"func" => "check_htaccess",
		"fail-htaccess" => _("Apache is not obeying your .htaccess file.  Try entering the following into your web server's httpd.conf file:") ."<br><pre>" .
			 "&lt;Directory $GALLERY_DIR" .
			 "&gt;\n\tAllowOverride Options FileInfo\n" .
			 "&lt;/Directory&gt;",
		"optional" => 1
	),
	"magic-quotes" => array(
		"optional" => 1,
		"desc" => sprintf(_("Check to see if %smagic quotes%s are enabled on your PHP server.  If they're on, you may notice strange behaviour if you put ' (single-quote), &quot; (double quote) or &#92; (backslash) characters in titles or captions in the gallery."),
		 '<a href="http://www.php.net/manual/en/configuration.php#ini.magic-quotes-gpc target=_top">',
		'</a>'),
	     	"func" => "check_magic_quotes",
		"fail-magic-quotes" => sprintf(_("Magic quotes are on.  Your webserver owner can turn them off by putting %s in your php.ini file"),
			": <pre>\n\tmagic_quotes_gpc = Off</pre>")
	),
	"mod-rewrite" => array(
		"desc" => _("Check to see if your webserver is compiled with <b>mod_rewrite</b>.  Mod_rewrite allows the Gallery to use shorter URLs that are easier to read and look nicer when you mail them to your web-challenged relatives.  It's not an essential feature."),
	     	"func" => "check_mod_rewrite",
		"fail-mod-rewrite" => _("Either <b>mod_rewrite</b> is not installed or your <b>.htaccess</b> file is not enabled (see above).  Either way, we'll have to use longer URLs in the Gallery.  If you want to turn it on I'd suggest that you make sure that your <b>.htaccess</b> file works and then if it still doesn't work you may need to reconfigure and rebuild Apache with this flag:") . 
			"<br><b>--enable-module=rewrite</b>",
		"optional" => 1
	),
	"NetPBM" => array(
		"optional" => 1,
		"desc" => _("Check to see if NetPBM is installed so that Gallery can manipulate images (rotate, resize, make thumbnails, etc).  You need to have NetPBM <b>or</b> ImageMagick installed."),
		"func" => "check_graphics",
		"fail-netpbm" => _("I can't find NetPBM.  If it's installed and not in the path of the webserver user that's OK --  you can specify the path to NetPBM by hand on the following page.  If it's not installed, you have two choices:" ) .
			$common_messages["getting-netpbm"],

		"fail-netpbm-partial" => 
			$common_messages["fail-netpbm-partial"] . 
			"<p><b> " . _("If NetPBM is installed and Gallery isn't finding it automatically that's OK -- that just means you'll have to specify the path to NetPBM by hand on the following page") ." </b>",

		"open-basedir-note" => $common_messages["open-basedir-active"]
	),
	"ImageMagick" => array(
		"optional" => 1,
		"desc" => _("Check to see if ImageMagick is installed so that Gallery can manipulate images (rotate, resize, make thumbnails, etc).  You need to have ImageMagick <b>or</b> NetPBM installed."),
		"func" => "check_graphics_im",
		"fail-imagemagick" => _("I can't find ImageMagick.  If it's installed and not in the path of the webserver user that's OK -- you can specify the path to ImageMagick by hand on the following page.  If it's not installed, you must install it.  ") .
			$common_messages["getting-imagemagick"],
		"open-basedir-note" => $common_messages["open-basedir-active"]
	),
	"exif" => array(
		"optional" => 1,
		"desc" => _("Check to see if jhead is installed so that Gallery can examine EXIF headers embedded in images created with most digital cameras."),
		"func" => "check_exif",
		"fail-exif" => sprintf(_("I can't find jhead.  If it's installed and not in the path of the webserver user that's OK -- you can specify the path to jhead by hand on the following page.  If it's not installed, you can install it yourself.  jhead is a public domain EXIF parser.  Source, linux binaries, and windows binaries can be found at %sthe jhead homepage%s."),
			'<a href="http://www.sentex.net/~mwandel/jhead/index.html">',
			'</a>'),
		"open-basedir-note" => $common_messages["open-basedir-active"]
	),
	"jpegtran" => array (
		"optional" => 1,
		"desc" => _("Check to see if jpegtran is installed so that Gallery can perform lossless rotations (only works on JPEG images)"),
		"func" => "check_jpegtran",
		"fail-jpegtran" => sprintf(_("I can't find jpegtran.  If it's installed and not in the path of the webserver user that's OK -- you can specify the path to jpegtran by hand on the following page.  If it's not installed, you can install it yourself.  jpegtran is a freely distributable tool distributed by the Independent JPEG Group as part of libjpeg.  Source may be found at %sthe IJG homepage%s.  Binaries may be found at %sthe JPEG club website%s or in the libjpeg package of Linux distributions."),
			'<a href="http://www.ijg.org">', '</a>',
			'<a href="http://jpegclub.org/">', '</a>'). 
			"\n\t<br><font color=blue><b>(". sprintf(_("this is new in version %s"), "1.4") . ")</b></font>",
		"open-basedir-note" => $common_messages["open-basedir-active"]
	),	
	"gettext" => array (
		"optional" => 1,
		"desc" => _("Check to see if PHP is compiled with gettext for fast translations."),
		"func" => "check_gettext",
		"fail-gettext" => sprintf(_("Your PHP does not have gettext support.  To include gettext support, you need to recompile PHP.  For more information see %s.  However, multilanguage support is still available in Gallery without gettext, but the pages may take slightly longer to load."),
			'<a href="http://www.php.net/manual/en/ref.gettext.php">http://www.php.net/manual/en/ref.gettext.php</a>')
	),
	"gallery-locale" => array (
		"optional" => 1,
		"desc" => _("Check to see what languages are available"),
		"func" => "check_gallery_languages"
	),
	"missing-locales" => array (
		"optional" => 1,
		"desc" => _("Testing to see if which locales are absent"),
		"func" => "check_absent_locales",
		"serious" => ( $elawisyl == "no" ) ? 1 : 0 ,
	)
);

$features = array(
	"zip" => array("zipinfo", "unzip"),
	"rewrite" => array("GALLERY_REWRITE_OK"),
	"mirror" => array("mirrorSites")
);

$constants =  array_merge(
  array(
	"galleryTitle" => array(
		"prompt" => _("Gallery Title"),
		"desc" => _("The name of your gallery.  Eg. <i>My Cool Photos</i>"),
		"value" => _("Gallery"),
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"editPassword" => array(
		"prompt" => _("Admin password"),
		"desc" => '(<font color=red>' . _('REQUIRED') . '</font>) '.
		sprintf(_("The password for the %s account.  Unfortunately, you're required to re-enter this every time you reconfigure Gallery.  We'll fix this in a future release!"), '<i>admin</i>'),
		"type" => "text",
		"dont-write" => 1,
		"value" => "",
		"attrs" => array("size" => 50)
	),

	"separator1" => array (
		"type" => "separator",
		"title" => _("Locations and URLs")
	),
	"albumDir" => array(
		"prompt" => _("Album directory"),
		"desc" => sprintf(_("The full path on physical disk to the directory where your photos will be stored.  This directory should start off empty -- Gallery will fill it.  Note that this directory needs to have write access by the user who is running the web server (in your case this user is %s.)"),
			"<b>". $webserver_user . "</b>"),
		"value" => getenv("DOCUMENT_ROOT") . "/albums",
		"require-write-dir" => 1,
		"filename" => 1,
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"tmpDir" => array(
		"prompt" => _("Temporary directory"),
		"desc" => '(<font color=red>' . _('REQUIRED') . '</font>) '.
			  sprintf(_("The filesystem path to a temporary directory.  Eg. %s or %s.  Note that if you have open_basedir configured on your system, then this temporary directory must be inside the open_basedir path!  In that case you may need to create the tmp directory yourself and make sure that it's writable by the webserver process."),
		'<i>/tmp</i>','<i>c:\\windows\\temp</i>'),
		"value" => getenv("TMP"),
		"filename" => 1,
		"require-write-dir" => 1,
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"photoAlbumURL" => array(
		"prompt" => "Gallery URL",
		"desc" => sprintf(_("The full URL to the Gallery web application.  Eg. %s."),
			'<i>http://www.foo.com/gallery</i>'),
		"value" => "http://" . getenv("SERVER_NAME") . $GALLERY_URL,
		"no-trailing-slash" => 1,
		"must-be-url" => 1,
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"albumDirURL" => array(
		"prompt" => _("Albums") ." URL",
		"desc" => sprintf(_("The full URL to your album directory (specified above) on your web server.  Eg. %s."), 
			'<i>http://www.foo.com/albums</i>'),
		"value" => "http://" . getenv("SERVER_NAME") . "/albums",
		"no-trailing-slash" => 1,
		"must-be-url" => 1,
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"movieThumbnail" => array(
		"prompt" => _("Thumbnail image"),
		"desc" => sprintf(_("The filesystem path to the thumbnail image to show for movies.  Eg. %s"), "/home/foo/thumbnail.jpg"),
		"value" => $GALLERY_DIR . "/images/movie.thumb.jpg",
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"mirrorSites" => array(
		"prompt" => _("Mirror sites"),
		"desc" => sprintf(_("A list of URLs that contain mirrors of your albums directory.  Specify one URL per line with the ones that are more reliable towards the top.  Eg.%s."),
		'<center><i>' .
			'http://www.fast.server/albums<br>http://www.other.server/albums</i>' .
			'</center>'),
		"optional" => 1,
		"type" => "textarea",
		"value" => NULL,
		"attrs" => array("cols" => 50, "rows" => 5)
	),
	"separator2" => array (
		"type" => "separator",
		"title" => _("Toolkits / image processing")
	),
	"graphics" => array(
		"prompt" => _("Graphics suite to use?"),
		"desc" => _("This setting tells Gallery which set of graphics tools you prefer it to use.  If Gallery says both are okay (on the first page) then this is simply user preference.  Otherwise go with the one that Gallery was able to find without error."),
		"choices" => array("NetPBM" => "NetPBM", "ImageMagick" => "ImageMagick"),
		"value" => default_graphics()
	),
	"pnmDir" => array(
		"optional" => 1,
		"prompt" => _("Path to NetPBM"),
		"desc" => sprintf(_("The path to the directory containing NetPBM on your system.  If you downloaded NetPBM from the Gallery download page, don't forget to make the files executable.  Eg. %s or %s or %s"),
			'<i>/usr/local/netpbm</i>',
			'<i>/usr/local/bin</i>', 
			'<i>c:\\apps\\netpbm</i>'),
		"value" => locateDir("jpegtopnm", isset($gallery->app->pnmDir) ? $gallery->app->pnmDir : ""),
		"verify-func" => "check_graphics",
		"fail-netpbm" => _("I can't find NetPBM at the location you provided.") . "  " .
			$common_messages["getting-netpbm"],
		"fail-netpbm-partial" => $common_messages["fail-netpbm-partial"],
		"filename" => 1,
		"attrs" => array("size" => 50),
		"no-trailing-slash" => 1,
		"type" => "text",
		"verify-func-takes-graphics-type" => true
	),
	"pnmtojpeg" => array(
		"prompt" => _("PNM to JPEG converter"),
		"desc" => _("This value is only relevant if you use NetPBM.  NetPBM uses a tool to encode its internal image format into JPEG.  This tool is called either <i>ppmtojpeg</i> or <i>pnmtojpeg</i> depending on the version of NetPBM that you have installed.  Check your NetPBM directory to see which one you have installed and specify it here.  If you aren't sure, just take a guess and we'll try to verify it for you."),
		"choices" => array("pnmtojpeg" => "pnmtojpeg", "ppmtojpeg" => "ppmtojpeg"),
		"value" => "pnmtojpeg"
	),
	"ImPath" => array(
		"optional" => 1,
		"prompt" => _("Path to ImageMagick"),
		"desc" => _("The path to the directory containing ImageMagick on your system."),
		"verify-func" => "check_graphics_im",
		"value" => locateDir("convert", isset($gallery->app->ImPath) ? $gallery->app->ImPath : ""),
		"fail-imagemagick" => _("I can't find ImageMagick at the location you provided.") . "  " .
			$common_messages["getting-imagemagick"],
		"filename" => 1,
		"attrs" => array("size" => 50),
		"no-trailing-slash" => 1,
		"type" => "text",
		"verify-func-takes-graphics-type" => true
	),
	"autorotate" => array(
                "prompt" => _("Auto-rotate images"),
		"desc" => _("Selecting this option has Gallery automatically attempt to rotate images on upload, based on the orientation information that is stored in JPEG EXIF data by some newer digital cameras.  Requires Jhead EXIF parser v.1.9 or greater (see above)."),
                "choices" => array("yes" => _("yes"), "no" => _("no")),
                "value" => "yes"
        ),

	"jpegImageQuality" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b> ". _("JPEG Quality"),
		"desc" => _("The JPEG file format trades off size versus quality.  Gallery can control the quality of the JPEGs that it produces when you resize, rotate or make thumbnails from JPEG images.  Higher quality images are larger in size than lower quality images.  However, in many cases there's a point at which raising the quality increases the size of the files without noticeably affecting the appearance of the image.  If you notice that your thumbnails and resized images are larger than the originals and you don't like this, you can try lowering this value.  <b>Note</b> that changing this value will only affect images changed in the <i>future</i>."),
		"choices" => array(50 => 50, 55 => 55, 60 => 60, 65 => 65,
				   70 => 70, 75 => 75, 80 => 80, 85 => 85,
				   90 => 90, 95 => 95, 100 => 100),
		"value" => 95
	),
	"separator3" => array (
		"type" => "separator",
		"title" => _("Gallery top page"),
	),
	"showAlbumTree" => array(
		"prompt" => _("Show the album tree"),
		"desc" => _("If you turn this option on, you'll see a complete list of all sub-albums when you're browsing the top level of your Gallery.  This will make navigating to a specific sub album much easier (although it can severely clutter the appearance of the top Gallery page).  Set to <b>no</b> by default."),
		"choices" => array("yes" => _("yes"), "no" => _("no")), 
		"value" => "no"
	),
	"highlight_size" => array(
		"prompt" => _("Highlight size"),
		"desc" => _("The target size (in pixels) of thumbnails shown in the gallery page (highlight images)"),
 		"value" => 200,
		"type" => "text",
		"attrs" => array("size" => 5)
	),
	"showOwners" => array(
		"prompt" => _("Show Owner"),
		"desc" => _("Show album owner's name on the top level Gallery page?"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
	),
	"albumsPerPage" => array(
		"prompt" => _("Albums per page"),
		"desc" => _("How many albums should be displayed per Gallery page?"),
		"value" => "5",
		"attrs" => array("size" => 10),
		"type" => "text"
	),
	"showSearchEngine" => array(
		"prompt" => _("Search Engine"),
		"desc" => _("Display search engine on main Gallery page?"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes",
	),

	"separator4" => array (
		"type" => "separator",
		"title" => _("Optional tools")
	),
	"zipinfo" => array(
		"prompt" => "Zipinfo",
		"desc" => _("The filesystem path to the 'zipinfo' binary.  This is optional so if you don't have it, don't worry about it too much."),
		"value" => locateFile("zipinfo"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"unzip" => array(
		"prompt" => "Unzip",
		"desc" => _("The filesystem path to the 'unzip' binary.  This is optional so if you don't have it, don't worry about it too much."),
		"value" => locateFile("unzip"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"use_exif" => array(
		"prompt" => "EXIF",
		"desc" => sprintf(_("The filesystem path to the 'jhead' exif parser.  This is optional so if you don't have it, don't worry about it too much.  Eg. %s or %s."),
		'<i>/usr/local/bin/jhead</i>', '<i>c:\\bin\\jhead.exe</i>'),
		"value" => locateFile("jhead"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"cacheExif" => array(
		"prompt" => "<b>(" . _("Advanced") . ")</b> ". _("Cache EXIF data"),
		"desc" => _("The EXIF data extracted from JPEG files can be cached after it is read the first time.  The tradeoff is that if you cache this data it's possible for your album data files to get quite large which may slow down viewing your albums.  But, if you don't cache this data then it needs to be retrieved every time any user looks at the properties of a photo which can also be slow.  Set to <b>no</b> by default."),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no"
	),
	"use_jpegtran" => array (
		"prompt" => "Jpegtran",
		"desc" => sprintf(_("The filesystem path to the 'jpegtran' utility.  This is optional so if you don't have it, don't worry about it too much.  Eg. %s or %s."), 
			'<i>/usr/local/bin/jpegtran</i>', 
			'<i>c:\\bin\\jpegtran.exe</i>') .
			"<br><font color=blue><b>(". sprintf(_("this is new in version %s"), "1.4") . ")</b></font>",
		"value" => locateFile("jpegtran"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"type" => "text",
		"attrs" => array ("size" => 50)
	),		
	"separator5" => array (
		"type" => "separator",
		"title" => _("Languages and locales"),
	),
	"default_language" => array(
		"prompt" => _("Which language do you want as your default language"), 
		"optional" => 1, 
		"desc" => sprintf(_("This is what you will see as %s, and what users will see if no other preference is set."), 'admin'),
		"type" => (sizeof(gallery_languages()) > 1) ? "choices" : "hidden", 
		"choices" => gallery_languages(),
		"value" => $gallery->language,
		"enabled" => $elawisyl
	),
	"ML_mode" => array(
		"prompt" => _("Multi language mode"), 
		"desc" => _("Gallery has three multi language modes.  These affect the language that a user will see when visiting the site") .
			"\n\t<br><font color=blue><b>(". sprintf(_("this is new in version %s"), "1.4") . ")</b></font>",
		"type" => (sizeof(gallery_languages()) > 1) ? "choices" : "hidden", 
		"choices" => array(
				1 => "1: " . _("Gallery is only displayed in one language"), 
				2 => "2: " . _("Gallery is displayed in the language the browser sends"), 
				3 => "3: " . _("The User can choose the language via pulldown menu or flags.")),
		"value" => (sizeof(gallery_languages()) > 1) ? 3 : 1,
		"enabled" => $elawisyl
	),
	"available_lang" => array(
		"prompt" => _("Available languages in mode 3 and user preferences"), 
		"type" => (sizeof(gallery_languages()) > 1) ? "choices" : "hidden", 
		"optional" => 1, 
		"desc" => _("These languages will be available as user choices in a pulldown menu or flags, if you have chosen language mode 3, and will be available to registered users as their default language in mode 2 or 3.") .
			"\n\t<br><font color=blue><b>(". sprintf(_("this is new in version %s"), "1.4") . ")</b></font>",
		"multiple_choices" => gallery_languages(),
		"value" => array_keys(gallery_languages()),
		"enabled" => $elawisyl
	),
	"show_flags" => array(
		"prompt" => _("Show flags?"),
		"desc" => _("In Mode 3 you can choose if you want to display a small pulldown menu or flags for the available languages. Set to <b>no</b> per default as its only an eye catcher.") .
			"\n\t<br><font color=blue><b>(". sprintf(_("this is new in version %s"), "1.4.1") . ")</b></font>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
		"enabled" => $elawisyl
	),
	"dateString" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b> ". _("Format to use for date strings."),
		"desc" => sprintf(_("This format string will be used to display %s in %s.  It is recommended to use %s which will change format depending on language choice.  However, if you wish to change this string, please look at %s."),
			_("date without time"), Gallery(), "<b>%x</b>",
			"<a href=\"http://uk.php.net/manual/en/function.strftime.php\">http://uk.php.net/manual/en/function.strftime.php</a>.") .
			"\n\t<br><font color=blue><b>(". sprintf(_("this is new in version %s"), "1.4.1") . ")</b></font>" . "<p>" .
			sprintf(_("For example, %s will display date like this: %s."), "<b>%b %d, %Y</b>", "<b>" . strftime("%b %d, %Y") . "</b>"), 
		"value" => "%x",
		"type" => "text",
		"attrs" => array("size" => 20)
	),
	"dateTimeString" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b> ". _("Format to use for date/time strings."),
		"desc" => sprintf(_("This format string will be used to display %s in %s.  It is recommended to use %s which will change format depending on language choice.  However, if you wish to change this string, please look at %s."),
			_("date with time"), Gallery(), "<b>%c</b>",
			"<a href=\"http://uk.php.net/manual/en/function.strftime.php\">http://uk.php.net/manual/en/function.strftime.php</a>.") .
			"\n\t<br><font color=blue><b>(". sprintf(_("this is new in version %s"), "1.4.1") . ")</b></font>" ."<p>" .
			sprintf(_("For example, %s will display date and time like this: %s."), "<b>%H:%M, %b %d, %Y</b>", "<b>" . strftime("%H:%M, %b %d, %Y") . "</b>"), 
		"value" => "%c",
		"type" => "text",
		"attrs" => array("size" => 20)
	),

   ),
	config_maybe_locales()
);
// Continue constants ...
$constants = array_merge($constants,
  array(
	"separator6" => array (
		"type" => "separator",
		"title" => _("Advanced"),
	),

        "timeLimit" => array(
		"prompt" => _("Time Limit"),
		"desc" => _("The maximum amount of time in seconds any single operation should take.  If you find that you are getting the <b>Maximum execution time exceeded</b> error message, you can try making this a larger number.  Be warned that if you make this too large you <i>might</i> have a process that is stuck for a very long time without doing anything.  The default value is 30 which is pretty reasonable."),
		"type" => "text",
		"must-be-number" => 1,
		"must-not-be-zero" => 1,
		"value" => 30,
		"attrs" => array("size" => 5),
	),
	"debug" => array(
		"prompt" => _("Debug mode"),
		"desc" => _("Turn debug mode on so that you can diagnose problems?"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
	),
	"use_flock" => array(
		"prompt" => sprintf(_("Use %s?"), " flock()"),
		"desc" => _("Gallery uses a locking system called flock() for added protection against data corruption.  This is not supported on all platforms.  If you're seeing errors like <i>Could not acquire lock</i> then set this value to <i>no</i>"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes"),
	"expectedExecStatus" => array(
		"prompt" => _("Status code"),
		"desc" => _("The status code Gallery expects to see after a successful exec() call.  Gallery will try to figure this out on its own so you probably won't need to change this value unless you have problems."),
		"value" => detect_exec_status(),
		"type" => "text",
		"attrs" => array("size" => 5),
	),
	"sessionVar" => array(
		"prompt" => _("Session variable"),
		"desc" => _("This is the session variable used to contain all Gallery session information.  Change this if there is a conflict between Gallery and another PHP app on your website."),
		"value" => "gallery_session",
		"type" => "text",
		"attrs" => array("size" => 20),
	),

	/* Keep this one at the end of the list because it's hidden */
	"userDir" => array(
		"desc" => "Filesystem location of user information",
		"prompt" => ".users directory",
		"type" => "hidden",
		"value" => "",
		"eval" => '"$albumDir/.users"',
		"filename" => 1
	)
    )
);

$defaults = array(
	"separator1" => array (
               	"type" => "separator",
		"title" => _("Appearance")
        ),
	"bordercolor" => array(
		"prompt" => _("Border color"),
 		"desc" => sprintf(_("Default color of image borders. You can learn about %scolors%s here."),    
			   '<a href="http://www.permadi.com/tutorial/websafecolor/">',
			   '</a>'),
			   // '  <a href="http://gotomy.com/color.html">' . 

		// <a href="http://www.w3.org/TR/REC-html40/types.html#h-6.5">W3.org</a>',
 		"value" => "black",
		"type" => "text",
		"attrs" => array("size" => 10),
	),
	"border" => array(
		"prompt" => _("Border width"),
		"desc" => _("Default width of image borders"),
		"value" => 1,
		"choices" => array("off" => _("off"), 1 => 1, 2 => 2, 3 => 3)
	),
	"font" => array(
		"prompt" => _("Default Font"),
		"desc" => _("Default font face.  Eg. <i>arial</i> or <i>trebuchet</i>"),
		"value" => "arial",
		"attrs" => array("size" => 10),
		"type" => "text"
	),
	"separator2" => array (
               	"type" => "separator",
		"title" => _("Layout")
        ),
	"cols" => array(
		"prompt" => _("Default Columns"),
		"desc" => _("The default number of columns in an album"),
		"value" => 3,
		"choices" => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9),
	),
	"rows" => array(
		"prompt" => _("Default Rows"),
		"desc" => _("The default number of rows in an album"),
		"value" => 3,
		"choices" => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9),
	),
	"separator3" => array (
               	"type" => "separator",
		"title" => _("Image Processing and Display")
        ),
	"thumb_size" => array(
		"prompt" => _("Thumbnail size"),
		"desc" => _("The default target size (in pixels) of album thumbnails."),
		"value" => 150,
		"attrs" => array("size" => 10),
		"type" => "text"
	),
	"resize_size" => array(
		"prompt" => _("Resized image size"),
		"desc" => _("When large images are added to an album, an intermediary version will automatically be generated at this size"),
		"value" => 640,
		"choices" => array("off" => _("off"), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024)
	),
	"resize_file_size" => array(
		"prompt" => _("Resized images file size"),
		"desc" => _("The default target size (in kilobyte) of resized JPEG images.  Including a value may slow down file uploads, but will speed up page viewing time especially for people with dialup connections.  Set to 0 for no target size.").
			  "<br><font color=blue><b>(". sprintf(_("this is new in version %s"), "1.4") . ")</b></font>",
		"value" => 0,
		"optional" => 1,
		"type" => "text"
	),
	"useOriginalFileNames" => array(
		"prompt" => _("Preserve Names"),
                "desc" => _("When uploading images to Gallery, should we preserve the original names?  If you select <i>no</i>, Gallery will assign a new name for each photo or movie that gets uploaded."),
                "choices" => array("yes" => _("yes"), "no" => _("no")),
                "value" => "yes",
	),
	"add_to_beginning" => array(
		"prompt" => _("Add to beginning"),
		"desc" => _("New images added to beginning rather than end of albums") .
			  "<br><font color=blue><b>(" . sprintf(_("this is new in version %s"), "1.4") .")</b></font>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
	),
	"fit_to_window" => array(
		"prompt" => _("Fit-to-window"),
		"desc" => _("When viewing photos which don't have an intermediary version, Gallery can automatically resize the image to fit the viewer's browser window (using JavaScript).  This may not work in embedded environments such as Nuke"),
		"value" => "no",
		"choices" => array("yes" => _("yes"), "no" => _("no"))
	),
	"use_fullOnly" => array(
		"prompt" => _("Visitor size prefs"),
		"desc" => _("Offer visitors the choice of always viewing the full sized version of an image instead of the intermediary size.  This setting only lasts for the visitor's session."),
		"value" => "no",
		"choices" => array("yes" => _("yes"), "no" => _("no"))
	),
	"separator4" => array (
               	"type" => "separator",
		"title" => _("Options")
        ),
	"print_photos" => array(
		"prompt" => _("Printing Service"),
		"desc" => _("Allow visitors to print photos directly from the album.  If you choose shutterfly, Gallery will charge an extra 2 cents (US) for every photo printed (a 4x6 print normally costs 49 cents US) and 1.8 cents of that will get donated back to the Gallery project.  This is one great way for you to help support this free software."),
		"value" => "photoaccess",
		"choices" => array("none" => _("none"), 
			"photoaccess" => "photoaccess", 
			"fotokasten" => "fotokasten",
			"shutterfly" => "shutterfly", 
			"shutterfly without donation" => 
				sprintf(_("%s without donation"),'shutterfly'))
	),
	"returnto" => array(
		"prompt" => _("Gallery Link"),
		"desc" => _("Display the top level Gallery link within the album?  If you turn this feature off, the user will not be able to navigate out of the album.  This is only useful when you want to embed an album within a website and not allow users to (easily) view the other albums in the gallery"),
		"value" => "yes",
		"choices" => array("yes" => _("yes"), "no" => _("no"))
	),
	"separator5" => array (
               	"type" => "separator",
		"title" => _("Data displayed")
        ),
	"display_clicks" => array(
		"prompt" => _("Click counts"),
		"desc" => _("Display click counts within Albums?"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes",
	),
	"public_comments" => array(
		"prompt" => _("Comments"),
		"desc" => _("Allow the public to add comments to your photos?"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes",
	),
	"extra_fields" => array(
		"prompt" => _("Custom Fields"),
		"optional" => 1,
		"desc" => _("Custom fields that are available to describe each image.  They should be separated by commas.  Eg. <i>Description, Location, Film Type</i>"),
		"attrs" => array("size" => 80),
		"type" => "text",
		"value" => "Description"
	),
	"separator6" => array (
               	"type" => "separator",
		"title" => _("Item Owner")
        ),
	"item_owner_modify" => array(
		"prompt" => _("Photo owner modification"),
		"desc" => _("Allow photo owners to edit and rotate/flip their own photos?") .
			  "<br><font color=blue><b>(". sprintf(_("this is new in version %s"), "1.4") . ")</b></font>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes",
	),
	"item_owner_delete" => array(
		"prompt" => _("Photo owner deletion"),
		"desc" => _("Allow photo owners to delete their own photos?") .
			  "<br><font color=blue><b>(" . sprintf(_("this is new in version %s"), "1.4") . ")</b></font>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes",
	),
	"item_owner_display" => array(
		"prompt" => _("Photo owner display"),
		"desc" => _("Display the name of photo owners with the caption?") .
			  "<br><font color=blue><b>(" . sprintf(_("this is new in version %s"), "1.4") .")</b></font>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
	),
	"separator8" => array (
		"type" => "separator",
		"title" => _("Polling configuration")
	),
	"voter_class" => array(
		"prompt" => _("Voter class"),
		"desc" => _("Voting is available to:") . 
		"<dl><dt>"._("Nobody")."</dt><dd>" . 
		_("Voting is off.") .
		"</dd><dt>"._("Everybody")."</dt><dd>" . 
		_("Anyone can vote.  Votes are recorded against session id.") .
		"</dd><dt>"._("Logged in")."</dt><dd>" . 
		_("Registered users can vote.  Votes are recorded against UserID, and voters can come back and change their choices.") .
		"</dl>" .
			"<font color=blue><b>(" . sprintf(_("this is new in version %s"), "1.4.1") .")</b></font>",
		"choices" => array("Nobody" => _("Nobody"), 
			"Everybody" => _("Everybody"), 
			"Logged in" => _("Logged in")),
		"value" => "Nobody"),
	"poll_type" => array(
		"prompt" => _("Poll type"),
	       	"desc" => _("Gallery offers two sorts of polls for voting for images.") . 
		"<dl><dt>"._("Rank")."</dt><dd>" . 
		_("Allows users to vote for one image as their favourite, another image as  their second choice, etc etc.  Only one image can be number 1.") . 
		"<dd><dt>"._("Critique")."</dt><dd>" . 
		_("Enables users to give all images points.  In this mode, users can give all images 10 points if desired") .
		"</dl>" .
	      	 	"<font color=blue><b>(" . sprintf(_("this is new in version %s"), "1.4.1") .")</b></font>",
		"choices" => array("rank" => _("Rank"), 
			"critique" => _("Critique")),
		"value" => "critique"
			),
	"poll_scale" => array(
		"prompt" => _("Number of choices to offer voters"),
		"desc" => _("") . 
			"<br><font color=blue><b>(" . sprintf(_("this is new in version %s"), "1.4.1") .")</b></font>",
		"value" => 3,
		"choices" => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)
			),
	"poll_hint" => array(
		"prompt" => _("Vote hint"),
		"desc" => _("This is displayed above the voting options for each image.") .
			"<br><font color=blue><b>(" . sprintf(_("this is new in version %s"), "1.4.1") .")</b></font>",
		"type" => "text",
		"value" => _("Vote for this image"),
		"optional" => 1
			),
	"poll_show_results" => array(
		"prompt" => _("Show vote results"),
		"desc" => _("A summary results graph and breakdown will be displayed on the album page and a link to full results if this is yes.") .
			"<br><font color=blue><b>(" . sprintf(_("this is new in version %s"), "1.4.1") .")</b></font>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
			), 
	"poll_num_results" => array(
		"prompt" => _("Number of lines of graph to show on the album page"),
		"desc" => _("How many results you want to display on the actual thumbnail page when results are shown.") . 
			"<br><font color=blue><b>(" . sprintf(_("this is new in version %s"), "1.4.1") .")</b></font>",
		"value" => 3,
		"choices" => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)),

	"poll_orientation" => array(
		"prompt" => _("Orientation of vote choices?"),
		"desc" => _("Rank poll choices can be displayed beside each other (horizontally) or underneath each other (vertically).  Has no effect on critique voting which is a pulldown menu.") . 
			"<br><font color=blue><b>(" . sprintf(_("this is new in version %s"), "1.4.1") .")</b></font>",
		"value" => "vertical",
		"choices" => array(
			"horizontal" => _("Horizontal"), 
			"vertical" => _("Vertical"))) ,
	"poll_nv_pairs" => array(
			"prompt" => _("Vote words and values"), 
			"desc" => _("This allows you to configure the actual text that will be displayed for each voting option, plus the number of points that option is worth."),
			"value" => array (
				array("name" => _("1st"), "value" => 3),
				array("name" => _("2nd"), "value" => 2), 
				array("name" => _("3rd"), "value" => 1)),
			"type" => "nv_pairs",
			"size" => 9)
);

?>

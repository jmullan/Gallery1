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
			sprintf(_("Gallery prefers NetPBM version 9.9 and up.  You can install just the binaries Gallery needs from the %sGallery Download Page%s. Or, you can compile and install the entire NetPBM package from the %sNetPBM website%s."), 
				 "<a href=\"http://sourceforge.net/projects/gallery\">",
				 "</a>",
				 "<a href=\"http://sourceforge.net/projects/netpbm\">",
				 "</a>")  .
			"<br>" .
			_("<br><b>Note:</b> You can install the binaries from the Gallery site even if you don't have root access on your box!  If you can't get it working, try leaving the NetPBM path blank and using ImageMagick instead.");
			
$common_messages["fail-netpbm-partial"] =
			_("I've found part of NetPBM, but some critical components are missing (see below).  Perhaps your version of NetPBM is out of date.") . "  " .
			$common_messages["getting-netpbm"];

$common_messages["getting-imagemagick"] =
			sprintf(_("Gallery prefers ImageMagick version 5.4.8 and up.  You can compile and install the entire ImageMagick package from the %sImageMagick website%s." ), 
				'<a href="http://www.imagemagick.org">', '</a>') .
			"<br>" .
			_("<br><b>Note</b>:  The site also has binaries available for assorted operating systems.  If you can't get it working, try leaving the ImageMagick path blank and using NetPBM instead.");

$common_messages["open-basedir-active"] = 
	sprintf(_("<b>Note:</b> Your PHP installation is configured with the %s restriction.   This may make it difficult for Gallery to detect and verify your binaries, even if you've already successfully used the Config. Wizard in the past.  Don't worry too much if this page gives warnings; your settings will still be saved properly."),
			"<b><a href=\"http://www.php.net/manual/en/features.safe-mode.php#ini.open-basedir\" target=\"_blank\">open_basedir</a></b>" );
			
$checklist = array(
	"gallery_version" => array(
		"optional" => 1,
		"desc" => sprintf(_("Check to see how old %s version %s is."),
			Gallery(),
			"<i>$gallery->version</i>"),
	     	"func" => "check_gallery_version"
	),
	"php" => array(
		"desc" => _("Check to see if you are running PHP 4.  Note that some versions of PHP have bugs that cause Gallery to fail.  PHP 4.0.0 uses a different file upload protocol, and versions 4.0.3 and 4.0.3pl1 have a bug in the unserialization code that cause Gallery to be unable to read its own database.  Upgrade to at least PHP 4.0.4pl1 if you can. <p>") ,
	     	"func" => "check_php",
		"fail-too-old" => sprintf(_("You're running PHP version %s.  You need to upgrade to PHP %s."), phpversion(), $MIN_PHP_MAJOR_VERSION),
		"fail-incompatible" => sprintf(_("You're running PHP version %s which has known bugs that cause Gallery to fail."),  phpversion()),
	),
	"safe_mode" => array(
		"desc" => _("Check to see if PHP is running in <b>safe_mode</b>.  Safe Mode is a PHP security feature commonly used in shared environments.  Gallery cannot run when Safe Mode is enabled because it imposes too many restrictions."),
		"func" => "check_safe_mode",
		"fail-safe-mode" => sprintf(_("<b>safe_mode</b> is enabled on this server.  You must disable it before you can use Gallery.  Refer to the FAQ, the forums, and the help page on the %sGallery website%s and the %sPHP website%s for assistance."),
			'<a href="http://gallery.sourceforge.net">',
			'</a>',
			'<a href="http://www.php.net/manual/en/features.safe-mode.php">',
			'</a>') .
		"<br><br>" .
			  sprintf(_("<b>Note</b>: In some rare circumstances, Gallery thinks that Safe Mode is on, when it's really off.  You can see for yourself by looking in the <i>Local</i> column of the <i>safe_mode</i> row in the %sPHP Info%s page.  If it says 'Off' or '0' or 'undefined' then it's possible that Gallery made a mistake and you can continue.  If not, then you must resolve this problem before you can use Gallery."),
				 '<a href="phpinfo.php">',
				 '</a>'),
		"optional" => 1,
		"serious" => 1
	),
	"htaccess" => array(
		"desc" => _("Allow the Gallery <b>.htaccess</b> file to set application specific configuration options for the Gallery application.  This is controlled by the webserver owner so it's not always possible for you to change it.  The main drawbacks to not having this are that you may not be able to upload files > 2MB (e.g. big ZIP files) and you may not be able to use <b>mod_rewrite</b> (see below)."),
	     	"func" => "check_htaccess",
		"fail-htaccess" => _("Apache is not obeying your <b>.htaccess</b> file.  Try entering the following into your web server's <b>httpd.conf</b> file:") ."<br><pre>" .
			 "&lt;Directory $GALLERY_DIR" .
			 "&gt;\n\tAllowOverride Options FileInfo\n" .
			 "&lt;/Directory&gt;",
		"optional" => 1
	),
	"magic-quotes" => array(
		"optional" => 1,
		"desc" => sprintf(_("Check to see if %smagic quotes%s are enabled on your PHP server.  If they're on, you may notice strange behavior if you put ' (single-quote), &quot; (double quote) or &#92; (backslash) characters in titles or captions in Gallery."),
		 '<b><a href="http://www.php.net/manual/en/configuration.php#ini.magic-quotes-gpc" target="_blank">',
		'</a></b>'),
	     	"func" => "check_magic_quotes",
		"fail-magic-quotes" => sprintf(_("Magic quotes are on.  Your webserver administrator can turn them off by putting %s in your <b>php.ini</b> file."),
			": <pre>\n\tmagic_quotes_gpc = Off</pre>")
	),
	"mod-rewrite" => array(
		"desc" => _("Check to see if your webserver is compiled with <b>mod_rewrite</b>.  This Apache module allows Gallery to use shorter URLs that are easier to read and look nicer when you mail them to your web-challenged relatives.  It's not an essential feature."),
	     	"func" => "check_mod_rewrite",
		"fail-mod-rewrite" => _("Either <b>mod_rewrite</b> is not installed or your <b>.htaccess</b> file is not enabled (see above).  Either way, we'll have to use longer URLs in the Gallery.  If you want to turn it on, first make sure that your <b>.htaccess</b> file is being obeyed. If it still doesn't work, you may need to reconfigure and rebuild Apache with this flag: ") . 
			"<pre>\t--enable-module=rewrite</pre>",
		"optional" => 1
	),
	"NetPBM" => array(
		"optional" => 1,
		"desc" => _("Check to see if NetPBM is installed so that Gallery can manipulate images (rotate, resize, make thumbnails, etc.).  You only need to have either NetPBM <b>or</b> ImageMagick installed."),
		"func" => "check_graphics",
		"fail-netpbm" => _("I can't find NetPBM.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path to NetPBM by hand on the following page.  If it's not installed, you have two choices: " ) .
			$common_messages["getting-netpbm"],

		"fail-netpbm-partial" => 
			$common_messages["fail-netpbm-partial"] . 
			"<p><b> " . _("If NetPBM is installed and Gallery isn't finding it automatically that's OK&#8212;you can specify the path to NetPBM by hand on the following page.") ." </b>",

		"open-basedir-note" => $common_messages["open-basedir-active"]
	),
	"ImageMagick" => array(
		"optional" => 1,
		"desc" => _("Check to see if ImageMagick is installed so that Gallery can manipulate images (rotate, resize, make thumbnails, etc.).  You only need to have either ImageMagick <b>or</b> NetPBM installed."),
		"func" => "check_graphics_im",
		"fail-imagemagick" => _("I can't find ImageMagick.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path to ImageMagick by hand on the following page.  If it's not installed, you must install it.  ") .
			$common_messages["getting-imagemagick"],
		"open-basedir-note" => $common_messages["open-basedir-active"]
	),
	"exif" => array(
		"optional" => 1,
		"desc" => _("Check to see if <b>jhead</b> is installed so that Gallery can examine EXIF headers embedded in images created with most digital cameras."),
		"func" => "check_exif",
		"fail-exif" => sprintf(_("I can't find <b>jhead</b>.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path by hand on the following page.  If it's not installed, you can install it yourself. Jhead is a public domain EXIF parser.  Source, linux binaries, and windows binaries can be found at the %sjhead homepage%s."),
			'<a href="http://www.sentex.net/~mwandel/jhead/index.html">',
			'</a>'),
		"open-basedir-note" => $common_messages["open-basedir-active"]
	),
	"jpegtran" => array (
		"optional" => 1,
		"desc" => _("Check to see if <b>jpegtran</b> is installed so that Gallery can perform lossless rotations on JPEG images."),
		"func" => "check_jpegtran",
		"fail-jpegtran" => sprintf(_("I can't find <b>jpegtran</b>.  If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path by hand on the following page.  If it's not installed, you can install it yourself.  Jpegtran is a free tool distributed by the Independent JPEG Group as part of <i>libjpeg</i>.  Source may be found at the %sIJG homepage%s.  Binaries may be found at the %sJPEG Club website%s or in the <i>libjpeg</i> package of Linux distributions."),
			'<a href="http://www.ijg.org">', '</a>',
			'<a href="http://jpegclub.org/">', '</a>'), 
		"open-basedir-note" => $common_messages["open-basedir-active"]
	),	
	"gettext" => array (
		"optional" => 1,
		"desc" => _("Check to see if PHP is compiled with <b>GNU gettext</b> support for fast translations."),
		"func" => "check_gettext",
		"fail-gettext" => sprintf(_("Your PHP does not have <b>GNU gettext</b> support installed.  To include it, you'll need to recompile PHP (see the %sPHP docs%s for more information). Multi-language support is still available in Gallery without <b>gettext</b>, but the pages may take slightly longer to load."),
			'<a href="http://www.php.net/manual/en/ref.gettext.php">', '</a>')
	),
	"gallery-locale" => array (
		"optional" => 1,
		"desc" => _("Check to see what languages are available."),
		"func" => "check_gallery_languages"
	),
	"missing-locales" => array (
		"optional" => 1,
		"enabled" => (sizeof(gallery_languages()) > 1) ? "yes" :"no",
		"desc" => _("Testing to see which <b>system</b> locales (if any) are absent."),
		"func" => "check_absent_locales",
		"serious" => ( $elawisyl == "no" ) ? 1 : 0 ,
	),
	"check-versions" => array (
		"desc" => _("Checking to see if you have the latest versions of all gallery files."),
		"func" => "check_gallery_versions",
		"serious" => 1,
		"optional" => 1,
	),
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
		"desc" => _("The name of your gallery.  E.g. <i>My Cool Photos</i>"),
		"value" => _("Gallery"),
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"editPassword" => check_admins(),

	"separator1" => array (
		"type" => "separator",
		"title" => _("Locations and URLs")
	),
	"albumDir" => array(
		"prompt" => _("Album directory"),
		"desc" => sprintf(_("The full path on physical disk to the directory where your photos will be stored.  This directory should start off empty&#8212;Gallery will fill it.  Note that this directory needs to have write access by the user who is running the web server (in your case this user is %s)."),
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
			  sprintf(_("The filesystem path to a temporary directory.  E.g. %s or %s.  Note that if you have <b>open_basedir</b> configured on your system, then this temporary directory must be inside the <b>open_basedir</b> path!  In that case you may need to create the directory yourself and make sure that it's writable by the webserver process."),
		'<i>/tmp</i>','<i>c:\\windows\\temp</i>'),
		"value" => getenv("TMP"),
		"filename" => 1,
		"require-write-dir" => 1,
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"photoAlbumURL" => array(
		"prompt" => "Gallery URL",
		"desc" => sprintf(_("The full URL to the Gallery web application.  E.g. %s"),
			'<i>http://www.foo.com/gallery</i>'),
		"value" => "http://" . getenv("SERVER_NAME") . $GALLERY_URL,
		"no-trailing-slash" => 1,
		"must-be-url" => 1,
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"albumDirURL" => array(
		"prompt" => _("Albums URL"),
		"desc" => sprintf(_("The full URL to your album directory (specified above) on your web server.  E.g. %s"), 
			'<i>http://www.foo.com/albums</i>'),
		"value" => "http://" . getenv("SERVER_NAME") . "/albums",
		"no-trailing-slash" => 1,
		"must-be-url" => 1,
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"movieThumbnail" => array(
		"prompt" => _("Thumbnail image"),
		"desc" => sprintf(_('The filesystem path to the thumbnail image to show for movies. E.g. %s'), '<i>/home/foo/thumbnail.jpg</i>'),
		"value" => $GALLERY_DIR . "/images/movie.thumb.jpg",
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"mirrorSites" => array(
		"prompt" => _("Mirror sites"),
		"desc" => sprintf(_("A list of URLs that contain mirrors of your albums directory.  Specify one URL per line with the ones that are more reliable towards the top.  E.g. %s"),
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
		"prompt" => _("Graphics suite to use"),
		"desc" => _("This setting tells Gallery which set of graphics tools you prefer it to use.  If Gallery says both are okay (on the first page) then this is simply user preference.  Otherwise go with the one that Gallery was able to find without error."),
		"choices" => array("NetPBM" => "NetPBM", "ImageMagick" => "ImageMagick"),
		"value" => default_graphics()
	),
	"pnmDir" => array(
		"optional" => 1,
		"prompt" => _("Path to NetPBM"),
		"desc" => sprintf(_("The path to the directory containing NetPBM on your system.  If you downloaded NetPBM from the Gallery download page, don't forget to make the files executable.  E.g. %s or %s or %s"),
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
		"desc" => _("Selecting this option has Gallery automatically attempt to rotate images on upload, based on the orientation information that is stored in JPEG EXIF data by some newer digital cameras.  Requires Jhead EXIF parser v.1.9 or greater (see below)."),
                "choices" => array("yes" => _("yes"), "no" => _("no")),
                "value" => "yes"
        ),

	"jpegImageQuality" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b><br> ". _("JPEG Quality"),
		"desc" => _("The JPEG file format trades off size versus quality.  Gallery can control the quality of the JPEGs that it produces when you resize, rotate or make thumbnails from JPEG images.  Higher quality images are larger in size than lower quality images.  However, in many cases there's a point at which raising the quality increases the size of the files without noticeably affecting the appearance of the image.  Setting this value to &quot;100&quot; is not recommended. If you notice that your thumbnails and resized images are larger than the originals and you don't like this, you can try lowering this value.  <b>Note</b> that changing this value will only affect images changed in the <i>future</i>."),
		"choices" => array(50 => 50, 55 => 55, 60 => 60, 65 => 65,
				   70 => 70, 75 => 75, 80 => 80, 85 => 85,
				   90 => 90, 95 => 95, 100 => 100),
		"value" => 90
	),
	"separator3" => array (
		"type" => "separator",
		"title" => _("Main Gallery page"),
	),
	"showAlbumTree" => array(
		"prompt" => _("Show the album tree"),
		"desc" => _("If you turn this option on, you'll see a complete list of all sub-albums when you're browsing the top level of your Gallery.  This will make navigating to a specific sub album much easier (although it can severely clutter the appearance of the top Gallery page)."),
		"choices" => array("yes" => _("yes"), "no" => _("no")), 
		"value" => "no"
	),
	"highlight_size" => array(
		"prompt" => _("Highlight size"),
		"desc" => _("The target size (in pixels) of thumbnails shown in the Gallery page (highlight images)."),
 		"value" => 200,
		"type" => "text",
		"attrs" => array("size" => 5)
	),
	"showOwners" => array(
		"prompt" => _("Show Owner"),
		"desc" => _("Show album owner's name next to each album?"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no"
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
		"value" => "yes"
	),
	"skinname" => array(
		"prompt" => _("Skin Name"),
		"desc" => _("Select a skin to use for Gallery. You now have a wide choice of &quot;skins&quot; that affect the look of every page of your gallery.  You can have fun experimenting with the available skins. Choose &quot;None&quot; to use the look from v1.4.") . available_skins(true),
		"choices" => available_skins(),
		"value" => "none"
	),
	"gallery_thumb_frame_style" => array(
		"prompt" => "Frame around albums",
		"desc" => _("On the main page, the image representing each album has a frame arount it.  The following styles are available:") .
			available_frames(true),
		"choices" => available_frames(),
		"value" => "simple_book"
	),
	
	"separator5" => array (
		"type" => "separator",
		"title" => _("Optional tools")
	),
	"zipinfo" => array(
		"prompt" => "Zipinfo",
		"desc" => _("The filesystem path to the <b>zipinfo</b> binary.  This is optional so if you don't have it, don't worry about it too much."),
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
		"desc" => _("The filesystem path to the <b>unzip</b> binary.  This is optional so if you don't have it, don't worry about it too much."),
		"value" => locateFile("unzip"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"type" => "text",
		"attrs" => array("size" => 50)
	),
	"use_exif" => array(
		"prompt" => "Jhead EXIF Parser",
		"desc" => sprintf(_("The filesystem path to the <b>jhead</b> EXIF parser.  This is optional so if you don't have it, don't worry about it too much.  E.g. %s or %s"),
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
		"prompt" => "<b>(" . _("Advanced") . ")</b><br> ". _("Cache EXIF data"),
		"desc" => _("The EXIF data extracted from JPEG files can be cached after it is read the first time.  The tradeoff is that if you cache this data it's possible for your album data files to get quite large which may slow down viewing your albums.  But, if you don't cache this data then it needs to be retrieved every time any user looks at the properties of a photo which can also be slow."),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no"
	),
	"use_jpegtran" => array (
		"prompt" => "Jpegtran",
		"desc" => sprintf(_("The filesystem path to the <b>jpegtran</b> utility.  This is optional so if you don't have it, don't worry about it too much.  E.g. %s or %s"), 
			'<i>/usr/local/bin/jpegtran</i>', 
			'<i>c:\\bin\\jpegtran.exe</i>'),
		"value" => locateFile("jpegtran"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"type" => "text",
		"attrs" => array ("size" => 50)
	),		
	"showContextHelp" => array(
		"prompt" => _("Show context help"),
		"desc" => _("Show context sensitive help links (little ? links that describe in-depth what each function of Gallery does)?") . newIn ("1.4.2"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes",
	),
	"separator6" => array (
		"type" => "separator",
		"title" => _("Languages and locales"),
	),
	"default_language" => array(
		"prompt" => _("Default language"), 
		"optional" => 1, 
		"desc" => sprintf(_("This is the language you will see as %s, and what users will see if no other preference is set."), '<b>admin</b>'),
		"type" => (sizeof(gallery_languages()) > 1) ? "choices" : "hidden", 
		"choices" => gallery_languages(),
		"value" => (!empty($gallery->language) && sizeof(gallery_languages()) >1 ) ? $gallery->language : "en_US",
		"enabled" => $elawisyl
	),
	"ML_mode" => array(
		"prompt" => _("Multi language mode"), 
		"desc" => _("Gallery has three multi language modes.  These affect the language that a user will see when visiting the site."),
		"type" => ( (sizeof(gallery_languages()) > 1) && ($elawisyl == 'yes') ) ? "choices" : "hidden",
		"choices" => array(
				1 => "1: " . _("Gallery is only displayed in one language"), 
				2 => "2: " . _("Gallery is displayed in the language the browser sends"), 
				3 => "3: " . _("The User can choose the language via pulldown menu or flags.")),
		"value" => ( (sizeof(gallery_languages()) > 1) && ($elawisyl == 'yes') ) ? 3 : 0,
	),
	"available_lang" => array(
		"prompt" => _("Available languages"), 
		"type" => (sizeof(gallery_languages()) > 1) ? "choices" : "hidden", 
		"optional" => 1, 
		"desc" => _("These languages will be available as user choices in a pulldown menu or flags, if you have chosen language Mode 3, and will be available to registered users as their default language in Mode 2 or 3."), 
		"multiple_choices" => gallery_languages(),
		"value" => array_keys(gallery_languages()),
		"enabled" => $elawisyl
	),
	"show_flags" => array(
		"prompt" => _("Show flags?"),
		"type" => (sizeof(gallery_languages()) > 1) ? "choices" : "hidden",
		"desc" => _("In Mode 3 you can choose to display flags instead of a small pulldown menu for the available languages."),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
		"enabled" => $elawisyl
	),
	"dateString" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b><br> ". _("Format for date strings."),
		"desc" => sprintf(_("This format string will be used to display %s in %s.  It is recommended to use %s which will change format depending on language choice.  However, if you wish to change this string, please look at the %sPHP documentation%s."),
			_("date without time"), Gallery(), "<b>%x</b>",
			'<a href="'._("http://www.php.net/manual/en/function.strftime.php"). '" target="_blank">', '</a>') .  "<br>" .
			sprintf(_("For example, %s will display date like this: %s."), "<b>%b %d, %Y</b>", "<b>" . strftime("%b %d, %Y") . "</b>"), 
		"value" => "%x",
		"type" => "text",
		"attrs" => array("size" => 20)
	),
	"dateTimeString" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b><br> ". _("Format for date/time strings."),
		"desc" => sprintf(_("This format string will be used to display %s in %s.  It is recommended to use %s which will change format depending on language choice.  However, if you wish to change this string, please look at the %sPHP documentation%s."),
			_("date with time"), Gallery(), "<b>%c</b>",
			'<a href="'._("http://www.php.net/manual/en/function.strftime.php"). '" target="_blank">', '</a>') .  "<br>" .
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
	"separator_email" => array (
		"type" => "separator",
	       	"title" => _("Email and Registration"),
	       	"desc" => sprintf(_("If you are having problems with email, please run the %s diagnostic."),
		       	'<a href="check_mail.php">' . _("Check Email") . '</a>')
	),
	"emailOn" => array(
		"prompt" => _("Enable email"),
		"verify-func" => "verify_email",
	       	"desc" => sprintf(_("Turns email on for the whole gallery.  If you set this to %s then none of the rest of this section will apply."),
			"<i>" . _("no") . "</i>" ),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no"
	),
	"adminEmail" => array(
		"prompt" => _("Admin email address"),
		"desc" => _("Set this value to a valid working email address to receive email notices upon certain user actions and also so that users know whom to reach in case of problems. You may specify more than one address by separating them with commas.") .
		"<br>",
		"type" => "text",
		"value" => "",
		"attrs" => array("size" => 50),
		"optional" => 1
	),
	"senderEmail" => array(
		"prompt" => _("Sender email address"),
		"desc" => _("Set this value to a valid working email address.  Emails that are automatically sent by Gallery will use this as a return address.  You should monitor this email address for bounce messages and replies from recipients.") .
		"<br>",
		"type" => "text",
		"value" => "",
		"attrs" => array("size" => 50),
		"optional" => 1
	),
	"emailSubjPrefix" => array(
		"prompt" => _("Email subject prefix"),
		"desc" => _("All gallery emails will have this as the first part of their subject."),
		"type" => "text",
		"attrs" => array("size" => 50),
		"value" => (!empty( $gallery->app->galleryTitle)) ?
	     		"[" . $gallery->app->galleryTitle . "]"	:
	     		"[" . Gallery() . "]"
	),
	"emailGreeting" => array(
		"prompt" => _("Welcome email message text"),
	       	"desc" => _("This email will be sent when new accounts are created.") . "<p>" .
	       		sprintf(_("%s, %s and %s will be replaced by the fullname, user name and password of the newly created user."),
				"<i>!!FULLNAME!!</i>",
				"<i>!!USERNAME!!</i>",
				"<i>!!PASSWORD!!</i>") . "<p>" .
			sprintf(_("%s will be replaced by a link the new user can click on to create a new password"),
				"<i>!!NEWPASSWORDLINK!!</i>") . "<p>" .
			_("Leave this field blank to get the default message that can be translated into other languages.  The default message is:") . 
			'<table><tr><td width="20%"></td><td width="60%"><tt>' .
			str_replace("\n", "<br>", welcome_email(true)) . 
			'</tt></td><td width="20%"></td></tr></table>',
		"optional" => 1,
		"type" => "textarea",
		"value" => "",

		"attrs" => array("cols" => 50, "rows" => 11)),
	"selfReg" => array(
		"prompt" => _("Allow visitors to self register."),
		"desc" => _("Enabling this will allow visitors to your site to create their own login accounts.") .
		"<br>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no"
	),
	"selfRegCreate" => array(
		"prompt" => _("Self registered users can create albums."),
		"desc" => _("Enabling this will allow self registered users to create their own albums as soon as they register.  Not having this enabled means you will have to give them permission manually before they can create or alter any contents of the Gallery.  This setting is ignored if self-registration is disabled.") .
		"<br>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no"),
	"multiple_create" => array(
		"prompt" => _("Enable bulk account creation"),
		"desc" => _("This option allows your to create many new user accounts at one time by creating a file of new users.") .
		"<br>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no"
	),
	"email_notification" => array(
		"prompt" => _("Admin notification of email activity"),
		"desc" => _("You can keep a record of every email that is sent by Gallery.") .
		"<br>",
		"multiple_choices" => array("email" => _("Send summary email"), 
			"logfile" => _("Log to a file"),
			"bcc" => _("Send duplicate email")),
		"optional" => 1,
		"value" => array()
	),
       	"separator7" => array (
		       	"type" => "separator",
		       	"title" => _("Gallery wide slideshow"),
			"desc" => "<br>" .
				_("Adds a slide show link on the main page of the gallery.   Note for large galleries this may run very slowly and use a lot of system resources.") . "  " .
				_("These settings do not apply to the low tech slideshow.")
			),
	"gallery_slideshow_type" => array(
		       	"prompt" => _("Slideshow preferences"),
		       	"desc" => "<dl>"
				."<dt>". _("Off") . "</dt>"
				."<dd>". _("No slideshow link will appear.") . "</dd>"
				."<dt>". _("Ordered") . "</dt>"
				."<dd>". _("Images will display in the same order as they appear in the Gallery.") . "</dd>"
				."<dt>". _("Random") . "</dt>"
				."<dd>". _("Images will appear in a random order.") . "</dd>"
				."</dl>",
		       	"choices" => array(
			       	"off" => _("Off"),
			       	"ordered" => _("Ordered"),
			       	"random" => _("Random"),
				),
		       	"value" => "off",
			),
	"gallery_slideshow_length" => array(
		"prompt" => _("Slideshow length"),
		"desc" => _("Number of images to display in slideshow.  Set to 0 or blank to display all images.  Set to a number if slideshow is random and you want to display only a selection of the images."),
		"type" => "text",
		"value" => "20",
		"attrs" => array("size" => 5),
		"allow_empty" => true
	),
	"gallery_slideshow_loop" => array(
		"prompt" => _("Allow slideshow to loop"),
		"desc" => sprintf(_("This option will allow visitors to have your slideshows repeat until they close the window or go to another page.  Set it to %s if you want to save bandwidth."), "<b>"._("no")."</b>"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
	       	"value" => "yes"
		),
       	"separator_comments" => array (
		       	"type" => "separator",
		       	"title" => _("Comments")
			),
	"comments_indication" => array(
		       	"prompt" => _("Indicate if there are comments available"),
		       	"desc" => "<dl>"
				. "<dt>" . _("Off") . "</dt>"
				. "<dd>" . _("No indication that an item has comments.") . "</dd>"
				. "<dt>" . _("Photos") . "</dt>"
				. "<dd>" . _("Images that have comments will be marked.") . "</dd>"
				. "<dt>" . _("Albums"). " </dt>"
				. "<dd>" . _("Albums and sub-albums that have comments will be marked.") . "</dd>"
				. "<dt>" ._("Both") . "</dt>"
				. "<dd>" . _("Images and albums that have comments will be marked.") . "</dd>"
				. "</dl>",
		       	"choices" => array(
			       	"off" => _("Off"),
			       	"photos" => _("Photos"),
			       	"albums" => _("Albums"),
			       	"both" => _("Both"),
				),
		       	"value" => "photos",
			),
	"comments_indication_verbose" => array(
		       	"prompt" => _("Display date of last comment?"),
		       	"desc" => sprintf(_("If an item has comments, display date of last comment, rather than a %s."), '"*"'),
			"choices" => array("yes" => _("yes"), "no" => _("no")),
		       	"value" => "no",
			),
	"comments_anonymous" => array(
		       	"prompt" => _("Anonymous comments from known users?"),
		       	"desc" => sprintf(_("When logged in, comments will automatically use format described below, unless this is set to %s."), 
				"<b>"._("yes")."</b>"),
			"choices" => array("yes" => _("yes"), "no" => _("no")),
		       	"value" => "no",
			),

	"comments_display_name" => array(
			"prompt" => _("Format for commenter names"),
			"desc" => _("Commenter names of logged in users can be displayed in a variety of different ways") .
				newIn("1.4.2"),
			"choices" => displayNameOptions(), 
			"value" => "!!FULLNAME!! (!!USERNAME!!)",
			),
	"separator8" => array (
		"type" => "separator",
		"title" => _("Technical"),
		"desc" => _("You usually don't need to change anything in this section.")
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
		"prompt" => sprintf(_("Use %s"), " flock()"),
		"desc" => _("Gallery uses a locking system called flock() for added protection against data corruption.  This is not supported on all platforms.  If you're seeing errors like <i>Could not acquire lock</i> then set this value to <i>no</i>."),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes"),
	"expectedExecStatus" => array(
		"prompt" => _("Status code"),
		"desc" => _("The status code Gallery expects to see after a successful <i>exec()</i> call.  Gallery will try to figure this out on its own so you probably won't need to change this value unless you have problems."),
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
	/* Keep these two at the end of the list because they're hidden */
	"userDir" => array(
		"desc" => "Filesystem location of user information",
		"prompt" => ".users directory",
		"type" => "hidden",
		"value" => "",
		"eval" => '"$albumDir/.users"',
		"persistent" => 1,
		"filename" => 1
		),
	"devMode" => array(
		"desc" => "Developers should switch this to yes, to see all warnings and notices",
		"prompt" => "Developer Mode",
		"type" => "hidden",
		"persistent" => 1,
		"value" => "no"
		),
	"adminCommentsEmail" => array(
		"desc" => "This is allows the \"admin\" account to get email whenever a comment is added to any album",
		"prompt" => "Admin comment email",
		"type" => "hidden",
		"persistent" => 1,
		"value" => "no"
		),
	"adminOtherChangesEmail" => array(
		"desc" => "This is allows the &quot;admin&quot; account to get email whenever a change is made to any album",
		"prompt" => "Admin Other Changes email",
		"type" => "hidden",
		"persistent" => 1,
		"value" => "no"
		),
	"maximumAlbumDepth" => array(
		"desc" => "Stops the breadcrumb code infinitely recursing.",
		"prompt" => "Maximum album depth",
		"type" => "hidden",
		"persistent" => 1,
		"value" => 50
		),
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
			   '<a href="http://www.permadi.com/tutorial/websafecolor/" target="_blank">',
			   '</a>'),
			   // '  <a href="http://gotomy.com/color.html">' . 

		// <a href="http://www.w3.org/TR/REC-html40/types.html#h-6.5">W3.org</a>',
 		"value" => "black",
		"type" => "text",
		"attrs" => array("size" => 10),
	),
	"border" => array(
		"prompt" => _("Border width"),
		"desc" => _("Default width of image borders."),
		"value" => 1,
		"choices" => array("off" => _("off"), 1 => 1, 2 => 2, 3 => 3)
	),
	"font" => array(
		"prompt" => _("Default Font"),
		"desc" => _("Default font face.  E.g. <i>arial</i> or <i>trebuchet</i>"),
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
		"desc" => _("The default number of columns in an album."),
		"value" => 3,
		"choices" => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9),
	),
	"rows" => array(
		"prompt" => _("Default Rows"),
		"desc" => _("The default number of rows in an album."),
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
		"desc" => _("When large images are added to an album, an intermediate version will automatically be generated at this size."),
		"value" => 640,
		"choices" => array("off" => _("off"), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024)
	),
	"resize_file_size" => array(
		"prompt" => _("Resized JPEG/PNG images file size"),
		"desc" => _("The default target size (in kilobytes) of resized JPEG/PNG images.  Including a value may slow down file uploads, but will speed up page viewing time especially for people with dialup connections.  Set to 0 for no target size."),
			  
		"value" => 0,
		"optional" => 1,
		"type" => "text"
	),
	'max_size' => array(
		'prompt' => _('Maximum image size'),
		'desc' => _("You can optionally limit the dimensions your images on upload to Gallery.") . "<br><span style=\"color: red; font-weight:bold\">"  . _("Warning") . ':</span> '. _("Enabling this feature will leave you <b>without a full size original</b> image in your Gallery, but allows you to save limited hard drive space. Gallery will attempt to preserve JPEG comments and EXIF data for the shrunken image, but this will require either <b>ImageMagick</b> or the optional <b>jhead</b> programs (see Step 2)."),
		'value' => 'off',
		'choices' => array('off' => _('off'), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024, 1280 => sprintf(_('%d (%d MPix)'), 1280, 1), 1600 => sprintf(_('%d (%d MPix)'), 1600, 2), 2048 => sprintf(_('%d (%d MPix)'), 2048, 3))
	),
	'max_file_size' => array(
		'prompt' => _('Maximum JPEG/PNG image file size'),
		'desc' => _("You can optionally limit the filesize of your JPEG/PNG images on upload to Gallery.") . "<br><span style=\"color: red; font-weight:bold\">" . _("Warning") . ':</span> ' . _("Enabling this feature will leave you <b>without an original quality image</b> in your Gallery. Set the default target filesize (in kilobytes).  Including a value may slow down file uploads, but will speed up page viewing time especially for people with dialup connections. Set to 0 if you don't want to restrict filesize."),
		'value' => 0,
		'optional' => 1,
		'type' => 'text'
	),
	"useOriginalFileNames" => array(
		"prompt" => _("Preserve Names"),
                "desc" => _("When uploading images to Gallery, should we preserve the original names?  If you select <i>no</i>, Gallery will assign a new name for each photo or movie that gets uploaded."),
                "choices" => array("yes" => _("yes"), "no" => _("no")),
                "value" => "yes",
	),
	"add_to_beginning" => array(
		"prompt" => _("Add to beginning"),
		"desc" => _("New images added to beginning rather than end of albums."),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
	),
	"fit_to_window" => array(
		"prompt" => _("Fit-to-window"),
		"desc" => _("When viewing photos which don't have an intermediate version, Gallery can automatically resize the image to fit the viewer's browser window (using JavaScript).  This may not work in embedded environments such as Nuke."),
		"value" => "no",
		"choices" => array("yes" => _("yes"), "no" => _("no"))
	),
	"use_fullOnly" => array(
		"prompt" => _("Visitor size prefs"),
		"desc" => _("Offer visitors the choice of always viewing the full sized version of an image instead of the intermediate size.  This setting only lasts for the visitor's session."),
		"value" => "no",
		"choices" => array("yes" => _("yes"), "no" => _("no"))
	),
	"separator4" => array (
               	"type" => "separator",
		"title" => _("Options")
        ),
	"print_photos" => array(
		"prompt" => _("Printing Services"),
 		"desc" => _("Allow visitors to print photos directly from the album.  Fotokasten is a print service based in Germany so is good for the European market. EZ Prints, PhotoAccess and Shutterfly are based in the United States. All services will give a small donation to the Gallery project for every print you make. If you choose Shutterfly's &quot;with donation&quot; option, Gallery will charge an extra 2 cents (US) for every photo printed, and 1.8 cents of that will get donated back to the Gallery project. EZ Prints, PhotoAccess and Fotokasten do not charge extra for the Gallery donation. Using these services is a great way for you to help support this free software."),
		'type' => 'print_services',
		'services' => array(
			'photoaccess' => array(
				'name'    => 'PhotoAccess',
				'url'     => 'http://www.photoaccess.com/'),
			'shutterfly'  => array(
				'name'    => 'Shutterfly',
				'url'     => 'http://www.shutterfly.com/',
				'radio'   => array(
					'yes' => _('with donation'),
					'no'  => _('without donation')
				)
			),
			'fotokasten'  => array(
				'name'    => 'Fotokasten',
				'url'     => 'http://www.fotokasten.de/'),    
			'ezprints'    => array(
				'name'    => 'EZ Prints',
				'url'     => 'http://www.ezprints.com/')
		),
		'value' => array(
			'ezprints' => array(
				'checked' => true),
			'fotokasten' => array(
				'checked' => true),
			'photoaccess' => array(
				'checked' => true),
			'shutterfly' => array(
				'checked' => true,
				'donation' => 'yes')
		),
		'optional' => true
	),
	"returnto" => array(
		"prompt" => _("Gallery Link"),
		"desc" => _("Display the top level Gallery link within the album?  If you turn this feature off, the user will not be able to navigate out of the album.  This is only useful when you want to embed an album within a website and not allow users to (easily) view the other albums in the gallery."),
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
	"extra_fields" => array(
		"prompt" => _("Custom Fields"),
		"optional" => 1,
		"desc" => _("Custom fields that are available to describe each image.  They should be separated by commas.  E.g. <i>Description, Location, Film Type</i>"),
		"attrs" => array("size" => 80),
		"type" => "text",
		"value" => "Description"
	),
	"showDimensions" => array(
		"prompt" => _("Display clickable image dimensions"),
		"desc" => _("Display clickable image dimensions beneath the thumbnails.  Will show dimensions for resized images (if any) first, then for the full image, depending on album settings user permissions.  You may click on these instead of the thumbnail to either image."),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no"
	),
	"separator6" => array (
               	"type" => "separator",
		"title" => _("Item Owner")
        ),
	"item_owner_modify" => array(
		"prompt" => _("Photo owner modification"),
		"desc" => _("Allow photo owners to edit and rotate/flip their own photos?"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes",
	),
	"item_owner_delete" => array(
		"prompt" => _("Photo owner deletion"),
		"desc" => _("Allow photo owners to delete their own photos?"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes",
	),
	"item_owner_display" => array(
		"prompt" => _("Photo owner display"),
		"desc" => _("Display the name of photo owners with the caption?"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
	),
	"separator7" => array (
		"type" => "separator",
		"title" => _("Polling configuration"),
		"desc"  => 
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
	"voter_class" => array(
		"prompt" => _("Voter class"),
		"desc" => _("Voting is available to:") . 
			"<dl>"
			. "<dt>" . _("Nobody")."</dt>"
			. "<dd>" . _("Voting is off.") ."</dd>"
			. "<dt>" . _("Everybody") . "</dt>"
			. "<dd>" . _("Anyone can vote.  Votes are recorded against session ID.") ."</dd>"
			. "<dt>" . _("Logged in") ."</dt>"
			. "<dd>" . _("Registered users can vote.  Votes are recorded against User ID, and voters can come back and change their choices.") . "</dd>"
			. "</dl>" ,
		"choices" => array("Nobody" => _("Nobody"), 
			"Everybody" => _("Everybody"), 
			"Logged in" => _("Logged in")),
		"value" => "Nobody"),
	"poll_type" => array(
		"prompt" => _("Poll type"),
	       	"desc" => _("Gallery offers two sorts of polls for voting for images.  See above for a more detailed explanation.") . 
			"<dl>"
			. "<dt>" . _("Rank")."</dt>"
			. "<dd>" . _("Users have a set of votes which they can allocate to the images.") . "</dd>"
			. "<dt>" . _("Critique")."</dt>"
			. "<dd>" . _("Enables users to give all images points without restriction.") . "</dd>"
			. "</dl>" ,
		"choices" => array("rank" => _("Rank"), 
			"critique" => _("Critique")),
		"value" => "critique"
			),
	"poll_scale" => array(
		"prompt" => _("Number of choices"),
		"desc" => _("Number of choices that will be available to voters as radio buttons or in a pulldown menu."),
		"value" => 3,
		"choices" => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)
			),
	"poll_hint" => array(
		"prompt" => _("Vote hint"),
		"desc" => _("This is displayed above the voting options under each image."),
		"type" => "text",
		"value" => _("Vote for this image"),
		"attrs" => array("size" => 50),
		"optional" => 1
			),
	"poll_show_results" => array(
		"prompt" => _("Show vote results"),
		"desc" => sprintf(_("Display a summary and graph on the album page of images that have received the highest votes, and provide a link to the full results.  If this option is %s, results of voting will only be available to admins and album owners."),
			_("no")),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
			), 
	"poll_num_results" => array(
		"prompt" => _("Number of results displayed"),
		"desc" => sprintf(_("If %s is %s, display a graph showing this many top results.  (Full results will be available on a linked page.)"),
			'"'. _("Show vote results").'"' ,
			'"'. _("yes").'"'),
		"value" => 3,
		"choices" => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9)),

	"poll_orientation" => array(
		"prompt" => _("Orientation of vote choices?"),
		"desc" => _("Rank poll choices can be displayed beside each other (horizontally) or underneath each other (vertically).  Has no effect on critique voting which is a pulldown menu."),
		"value" => "vertical",
		"choices" => array(
			"horizontal" => _("Horizontal"), 
			"vertical" => _("Vertical"))) ,
	"poll_nv_pairs" => array(
			"prompt" => _("Vote words and values"), 
			"desc" => _("This allows you to configure the actual text that will be displayed for each voting option, plus the number of points that option is worth."),
			"value" => array (
				array("name" => _("Excellent"), "value" => 5),
				array("name" => _("Very Good"), "value" => 4),
				array("name" => _("Good"), "value" => 3),
				array("name" => _("Average"), "value" => 2), 
				array("name" => _("Poor"), "value" => 1)),
			"type" => "nv_pairs",
			"verify-func" => "check_poll_nv_pairs",
			"size" => 9),
       	"separator8" => array (
		       	"type" => "separator",
		       	"title" => _("Album Slideshow"),
			"desc" => _("These settings do not apply to the low tech slideshow.")
			),
	"slideshow_type" => array(
		       	"prompt" => _("Slideshow Type"),
		       	"desc" => "<dl>"
				. "<dt>" . _("Off") ."</dt>"
				. "<dd>" . _("No slideshow link will appear.") . "</dd>"
				. "<dt>" . _("Ordered") . "</dt>"
				. "<dd>" . _("Images will display in the same order as they appear in the Gallery.") . "</dd>"
				. "<dt>" . _("Random") . "</dt>"
				. "<dd>" . _("Images will appear in a random order.") . "</dd>"
				. "</dl>",
		       	"choices" => array(
			       	"off" => _("Off"),
			       	"ordered" => _("Ordered"),
			       	"random" => _("Random"),
				),
		       	"value" => "ordered",
			),
	"slideshow_recursive" => array(
		"prompt" => _("Recursive slideshow"),
		"desc" => sprintf(_("Set to %s if you want to include images in sub albums as part of the slideshow."), "<b>"._("yes")."</b>"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no"
			),
	"slideshow_loop" => array(
		"prompt" => _("Allow slideshow to loop"),
		"desc" => sprintf(_("This option will allow visitors to have your slideshow repeat until they close the window or go to another page.  Set it to %s if you want to save bandwidth."), "<b>"._("no")."</b>"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes"
			),
	"slideshow_length" => array(
		"prompt" => _("Slideshow length"),
		"desc" => _("Number of images to display in slideshow.  Set to 0 or blank to display all images.  Set to a number if slideshow is random and you want to display only a selection of the images."),
		"type" => "text",
		"value" => "0",
		"optional" => 1,
		"attrs" => array("size" => 5)
	),
       	"separator_frames" => array (
		       	"type" => "separator",
		       	"title" => _("Frames"),
		       	"desc" => sprintf(_("Each image (thumbnails, resized and full sized) in %s can have a frame.  The following styles available:"),Gallery()) .
		available_frames(true),
			),
	"album_frame" => array(
		"prompt" => _("Subalbum frames"),
		"desc" => _("This frame will appear around the thumbnail image representing a subalbum."),
		"choices" => available_frames(),
		"value" => "simple_book"
	),
	"thumb_frame" => array(
		"prompt" => _("Frame around thumbs"),
		"desc" => _("This frame will appear around the thumbnail image representing a photo or movie."),
		"choices" => available_frames(),
		"value" => "solid"
	),
	
	"image_frame" => array(
		"prompt" => _("Frame around images"),
		"desc" => _("This frame will appear around fulled sized and resized photos."),
		"choices" => available_frames(),
		"value" => "solid"
	),
	
	
);

?>


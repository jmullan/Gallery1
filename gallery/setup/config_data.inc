<?php /* $Id$ */ ?>
<?php

$common_messages["getting-netpbm"] = 
			_("Gallery prefers NetPBM version 9.9 and up."). "  " .
			_("You can install just the binaries Gallery needs from") . " <a href=http://sourceforge.net/projects/gallery>Gallery ". _("on") ." SourceForge</a> " .
			_("or, you can compile and install the entire NetPBM package from") ." <a href=http://sourceforge.net/projects/netpbm>NetPBM ". _("on") ." SourceForge</a>. " .
			"<br><b>". _("Note") .":</b> ". _("You can install the binaries from the Gallery site even if you don't have root access on your box!") . "  " .
			_("If you can't get it working, try leaving the NetPBM path blank and using ImageMagick intead.");
			
$common_messages["fail-netpbm-partial"] =
			_("I've found part of NetPBM, but some critical components are missing (see below).") . "  " .
			_("Perhaps your version of NetPBM is out of date?") . "  " .
			$common_messages["getting-netpbm"];

$common_messages["getting-imagemagick"] =
			_("Gallery prefers ImageMagick version 5.4.8 and up.") . "  " .
			_("You can compile and install the entire ImageMagick package from") .
			" <a href=http://www.imagemagick.org>www.ImageMagick.org</a>. " .
			"<br><b>" . _("Note") ."</b>:  " . _("They also have binaries available for assorted operating systems.") . "  " .
			_("If you can't get it working, try leaving the ImageMagick path blank and using NetPBM instead.") ;

$common_messages["open-basedir-active"] =
			"<b>" . _("Note") .":</b> " . _("Your webserver is configured with the") .
			" <a href=\"http://www.php.net/manual/en/features.safe-mode.php#ini.open-basedir\"> " .
			"open_basedir</a> " . _("restriction") .".  " .
			_("This may make it difficult for Gallery to detect and verify your binaries, even if you've already successfully used the Config Wizard in the past.") . "  " .
			_("Don't worry too much if this page gives warnings; your settings will still be saved properly.") ;
			
$checklist = array(
	"php4" => array(
		"desc" => _("Check to see if you are running PHP 4.") . "  " .
			_("Note that some versions of PHP have bugs that cause Gallery to fail.") . "  " .
			_("PHP4.0.0 uses a different file upload protocol,") . " " .
			_("versions 4.0.3 and 4.0.3pl1 have a bug in the unserialization code that cause Gallery to be unable to read its own database.") . "  " .
			_("Upgrade to at least PHP 4.0.4pl1 if you can."),
	     	"func" => "check_php",
		"fail-too-old" => sprintf(_("You're running version %s.  You need to upgrade to PHP %s."), phpversion(), $MIN_PHP_MAJOR_VERSION),
		"fail-incompatible" => sprintf(_("You're running version %s which has known bugs that cause Gallery to fail."),  phpversion())
	),
	"php4.1" => array(
		"optional" => 1,
		"desc" => _("Check to see if you are running PHP 4.1 or greater.") . "  " .
			 _("If you want to run the multi-language version of gallery, you need php 4.1.") . "  " .
			_("Upgrade to at least PHP 4.1 if you can."),
	     	"func" => "check_php4_1",
		"fail-too-old" => _("You're running version") .  " " . phpversion() .
			".  " . _("You need to upgrade to at least PHP 4.1.0 if you want to run the multilanguage version."),
	),
	"safe_mode" => array(
		"desc" => _("Check to see if PHP is running in <b>Safe Mode</b>.") . "  " .
			_("Safe mode is a PHP security feature commonly used in shared environments.") . "  " .
			_("Gallery cannot run when safe mode is enabled because it imposes too many restrictions."),
		"func" => "check_safe_mode",
		"fail-safe-mode" => "<b>Safe Mode</b> ". _("is enabled on this server.") . "  " .
			_("You must disable it before you can use Gallery.") . "  " .
			_("Refer to the FAQ, the forums, and the help page on the") ." <a href='http://gallery.sourceforge.net'>" .
			_("Gallery website") ."</a>, and the " .
			  "<a href='http://www.php.net/manual/en/features.safe-mode.php'>" .
			_("PHP website") ."</a> " . _("for assistance.") .
			  "<br>" .
			  "<br>" .
			  "<b>" . _("Note") ."</b>: " . _("In some rare circumstances, Gallery thinks that <b>safe mode</b> is on, when it's really off.") . "  " .
			_("You can see for yourself by looking in the <i>Local</i> column of the safe_mode row in the") ." <a href=phpinfo.php>". _("PHP Info") ."</a> " ._("page") . ". " .
			_("If it says 'Off' or '0' or 'undefined' then it's possible that Gallery made a mistake and you can continue.") . "  " .
			_("If not, then you must resolve this problem before you can use Gallery."),
		"optional" => 1,
		"serious" => 1,
	),
	"htaccess" => array(
		"desc" => _("Allow the Gallery <b>.htaccess</b> file to set application specific configuration options for the Gallery application.") . "  " .
			_("This is controlled by the webserver owner so it's not always possible for you to change it.") . "  " .
			_("The main drawbacks to not having this are that you may not be able to upload files > 2MB (eg, big ZIP files) and you may not be able to use mod_rewrite (see below)."),
	     	"func" => "check_htaccess",
		"fail-htaccess" => _("Apache is not obeying your .htaccess file.") . "  " .
			_("Try entering the following into your web server's httpd.conf file") .": <br><pre>" .
			 "&lt;Directory $GALLERY_DIR" .
			 "&gt;\n\tAllowOverride Options FileInfo\n" .
			 "&lt;/Directory&gt;",
		"optional" => 1,
	),
	"magic-quotes" => array(
		"optional" => 1,
		"desc" => _("Check to see if") . " <a href=http://www.php.net/manual/en/configuration.php#ini.magic-quotes-gpc target=_top>magic quotes</a> " . 
			_("are enabled on your PHP server.") . "  " .
			_("If they're on, you may notice strange behaviour if you put ' (single-quote), \" (double quote) or \\ (backslash) characters in titles or captions in the gallery."),
	     	"func" => "check_magic_quotes",
		"fail-magic-quotes" => _("Magic quotes are on.  Your webserver owner can turn them off by putting") .
			": <pre>\n\t" .
			 "magic_quotes_gpc = Off" .
			 "</pre> " . _("in your php.ini file"),
	),
	"mod-rewrite" => array(
		"desc" => _("Check to see if your webserver is compiled with <b>mod_rewrite</b>.") . "  " .
			_("Mod_rewrite allows the Gallery to use shorter URLs that are easier to read and look nicer when you mail them to your web-challenged relatives.") . "  " .
			_("It's not an essential feature."),
	     	"func" => "check_mod_rewrite",
		"fail-mod-rewrite" => _("Either <b>mod_rewrite</b> is not installed or your <b>.htaccess</b> file is not enabled (see above).") . 
			_("Either way, we'll have to use longer URLs in the Gallery.") . "  " .
			_("If you want to turn it on I'd suggest that you make sure that your <b>.htaccess</b> file works and then if it still doesn't work you may need to reconfigure and rebuild Apache with this flag:") . 
			"<br><b>--enable-module=rewrite</b>",
		"optional" => 1,
	),
	"NetPBM" => array(
		"optional" => 1,
		"desc" => _("Check to see if NetPBM is installed so that Gallery can manipulate images") .
			" (".  _("rotate, resize, make thumbnails, etc") .")." . "  " .
			_("You need to have NetPBM <b>or</b> ImageMagick installed."),
		"func" => "check_graphics",
		"fail-netpbm" => _("I can't find NetPBM.") . "  " .
				_("If it's installed and not in the path of the webserver user that's OK") ." -- " . 
				_("you can specify the path to NetPBM by hand on the following page.") . "  " .
				_("If it's not installed, you have two choices") .":  " .
			$common_messages["getting-netpbm"],

		"fail-netpbm-partial" => 
			$common_messages["fail-netpbm-partial"] . 
			"<p><b> " . _("If NetPBM is installed and Gallery isn't finding it automatically that's OK") . " -- " . 
			_("that just means you'll have to specify the path to NetPBM by hand on the following page") ." </b>",

		"open-basedir-note" => $common_messages["open-basedir-active"],
	),
	"ImageMagick" => array(
		"optional" => 1,
		"desc" => _("Check to see if ImageMagick is installed so that Gallery can manipulate images") .
 			" (" . _("rotate, resize, make thumbnails, etc") .")." . "  " .
			_("You need to have ImageMagick <b>or</b> NetPBM installed."),
		"func" => "check_graphics_im",
		"fail-imagemagick" => _("I can't find ImageMagick.") . "  " .
			_("If it's installed and not in the path of the webserver user that's OK") ." -- " .
			_("you can specify the path to ImageMagick by hand on the following page.") . "  " .
			_("If it's not installed, you must install it.") . "  " .
			$common_messages["getting-imagemagick"],
		"open-basedir-note" => $common_messages["open-basedir-active"],
	),
	"exif" => array(
		"optional" => 1,
		"desc" => _("Check to see if jhead is installed so that Gallery can examine EXIF headers embedded in images created with most digital cameras."),
		"func" => "check_exif",
		"fail-exif" => _("I can't find jhead.") . "  " .
			_("If it's installed and not in the path of the webserver user that's OK") . " -- " .
			_("you can specify the path to jhead by hand on the following page.") . "  " .
			_("If it's not installed, you can install it yourself.") . "  " .
			_("jhead is a public domain EXIF parser.") . "  " .
			_("Source, linux binaries, and windows binaries can be found at") .
			" <a href=http://www.sentex.net/~mwandel/jhead/index.html>" .
			_("the jhead homepage") ."</a>.",
		"open-basedir-note" => $common_messages["open-basedir-active"],
	),
	"jpegtran" => array (
		"optional" => 1,
		"desc" => _("Check to see if jpegtran is installed so that Gallery can perform lossless rotations"),
		"func" => "check_jpegtran",
		"fail-jpegtran" => _("I can't find jpegtran.") . "  " .
			_("If it's installed and not in the path of the webserver user that's OK") . " -- " .
			_("you can specify the path to jpegtran by hand on the following page.") . "  " .
			_("If it's not installed, you can install it yourself.") . "  " .
			_("jpegtran is a freely distributable tool distributed by the Independent JPEG Group as part of libjpeg.") . "  " .
			_("Source may be found at") .
			" <a href=http://www.ijg.org/>". _("the IJG homepage.") . "  " .
			_("Binaries may be found at") . " <a href=http://jpegclub.org/>" . _("the JPEG club website") ."</a> " .
			_("or in the libjpeg package of Linux distributions."),
		"open-basedir-note" => $common_messages["open-basedir-active"],
	),	
	"gettext" => array (
		"optional" => 1,
		"desc" => _("Check to see if Apache is compiled with gettext so that Gallery can run in many languages"),
		"func" => "check_gettext",
		"fail-gettext" => _("gettext in not installed.") . "  " .
			_("To include gettext support, you need to recompile apache.") . "  " .
			_("For more information see") . " " .
			'<a href="http://www.php.net/manual/en/ref.gettext.php">' .
			'http://www.php.net/manual/en/ref.gettext.php</a>.  '.
			_("However, multilanguage support is still available in Gallery without gettext, but the pages may take slightly longer to load.")
	),
	"locale" => array (
		"optional" => 1,
		"desc" => _("Check to see locale support is available"),
		"func" => "check_locale",
		"fail-locale" => _("locale in not installed."), 
		"warning-locale" => _("We cannot tell if locale support is available."), 
	),
	"gallery-locale" => array (
		"optional" => 1,
		"desc" => _("Check to see gallery locale files are present"),
		"func" => "check_gallery_locale",
		"fail-gallery-locale" => _("Gallery locale files are absent.") 
	),
	"missing-locales" => array (
		"optional" => 1,
		"desc" => _("Testing to see if which locales are absent"),
		"func" => "check_absent_locales"
	)
);

$features = array(
	"zip" => array("zipinfo", "unzip"),
	"rewrite" => array("GALLERY_REWRITE_OK"),
	"mirror" => array("mirrorSites"),
);

$constants = array_merge(
    array(
	"galleryTitle" => array(
		"prompt" => _("Gallery Title"),
		"desc" => _("The name of your gallery.  Eg. <i>My Cool Photos</i>"),
		"value" => _("Gallery"),
		"attrs" => array("size" => 50),
	),
	"editPassword" => array(
		"prompt" => _("Admin password"),
		"desc" => _("The password for the <i>admin</i> account.") . "  " .
			_("Unfortunately, you're required to re-enter this every time you reconfigure Gallery.") . "  " .
			_("We'll fix this in a future release!"),
		"type" => "text",
		"dont-write" => 1,
		"attrs" => array("size" => 50),
	),
	"graphics" => array(
		"prompt" => _("Graphics suite to use?"),
		"desc" => _("This setting tells Gallery which set of graphics tools you prefer it to use.") . "  " .
			_("If Gallery says both are okay (on the first page) then this is simply user preference.") . "  " .
			_("Otherwise go with the one that Gallery was able to find without error."),
		"choices" => array("NetPBM" => _("NetPBM"), "ImageMagick" => _("ImageMagick")),
		"value" => default_graphics(),
	),
	"pnmDir" => array(
		"optional" => 1,
		"prompt" => _("Path to NetPBM"),
		"desc" => _("The path to the directory containing NetPBM on your system.") . "  " .
			_("If you downloaded NetPBM from the Gallery download page, don't forget to make the files executable.") . "  " .
			_("Eg. <i>/usr/local/netpbm</i> or <i>/usr/local/bin</i> or <i>c:\\apps\\netpbm</i>"),
		"value" => locateDir("jpegtopnm", $gallery->app ? $gallery->app->pnmDir : ""),
		"verify-func" => "check_graphics",
		"fail-netpbm" => _("I can't find NetPBM at the location you provided.") . "  " .
			$common_messages["getting-netpbm"],
		"fail-netpbm-partial" => $common_messages["fail-netpbm-partial"],
		"filename" => 1,
		"attrs" => array("size" => 50),
		"no-trailing-slash" => 1,
		"verify-func-takes-graphics-type" => true,
	),
	"pnmtojpeg" => array(
		"prompt" => _("PNM to JPEG converter"),
		"desc" => _("This value is only relevant if you use NetPBM.") . "  " .
			_("NetPBM uses a tool to encode its internal image format into JPEG.") . "  " .
			_("This tool is called either <i>ppmtojpeg</i> or <i>pnmtojpeg</i> depending on the version of NetPBM that you have installed.") . "  " .
			_("Check your NetPBM directory to see which one you have installed and specify it here.") . "  " .
			_("If you aren't sure, just take a guess and we'll try to verify it for you."),
		"choices" => array("pnmtojpeg" => _("pnmtojpeg"), "ppmtojpeg" => _("ppmtojpeg")),
		"value" => "pnmtojpeg",
	),
	"ImPath" => array(
		"optional" => 1,
		"prompt" => _("Path to ImageMagick"),
		"desc" => _("The path to the directory containing ImageMagick on your system."),
		"verify-func" => "check_graphics_im",
		"value" => locateDir("convert", $gallery->app ? $gallery->app->ImPath :
 ""),
		"fail-imagemagick" => _("I can't find ImageMagick at the location you provided.") . "  " .
			$common_messages["getting-imagemagick"],
		"filename" => 1,
		"attrs" => array("size" => 50),
		"no-trailing-slash" => 1,
		"verify-func-takes-graphics-type" => true,
	),
	"highlight_size" => array(
		"prompt" => _("Highlight size"),
		"desc" => _("The target size (in pixels) of thumbnails shown in the gallery page (highlight images)"),
 		"value" => 200,
		"attrs" => array("size" => 5),
	),
	"zipinfo" => array(
		"prompt" => "Zipinfo",
		"desc" => _("The filesystem path to the 'zipinfo' binary.") . "  " .
			_("This is optional so if you don't have it, don't worry about it too much."),
		"value" => locateFile("zipinfo"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"attrs" => array("size" => 50),
	),
	"unzip" => array(
		"prompt" => "Unzip",
		"desc" => _("The filesystem path to the 'unzip' binary.") . "  " .
			_("This is optional so if you don't have it, don't worry about it too much."),
		"value" => locateFile("unzip"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"attrs" => array("size" => 50),
	),
	"use_exif" => array(
		"prompt" => "EXIF",
		"desc" => _("The filesystem path to the 'jhead' exif parser.") . "  " .
			_("This is optional so if you don't have it, don't worry about it too much.") ."  " .
			_("Eg.") ." <i>/usr/local/bin/jhead</i> ". _("or") ." <i>c:\\bin\\jhead.exe</i>",
		"value" => locateFile("jhead"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"attrs" => array("size" => 50),
	),
	"use_jpegtran" => array (
		"prompt" => "Jpegtran",
		"desc" => _("The filesystem path to the 'jpegtran' utility.") . "  " .
			_("This is optional so if you don't have it, don't worry about it too much.") . "  " .
			_("Eg.") ." <i> " .
			"/usr/local/bin/jpegtran</i> " . _("or") ." <i>c:\\bin\\jpegtran.exe</i>" .
			"<br><font color=blue><b>(". _("this is new in version 1.3.5"). ")</b></font>",
		"value" => locateFile("jpegtran"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"attrs" => array ("size" => 50),
	),		
	"movieThumbnail" => array(
		"prompt" => _("Thumbnail image"),
		"desc" => _("The filesystem path to the thumbnail image to show for movies.") . "  " .
 			_("Eg.") . " /home/foo/thumbnail.jpg",
		"value" => $GALLERY_DIR . "/images/movie.thumb.jpg",
		"attrs" => array("size" => 50),
	),
	"albumDir" => array(
		"prompt" => _("Album directory"),
		"desc" => _("The full path on physical disk to the directory where your photos will be stored.") . "  " .
			_("This directory should start off empty -- Gallery will fill it.") . "  " .
			_("Note that this directory needs to have write access by the user who is running the web server") . 
			" (" . _("in your case this user is") ." <b>" .
			$webserver_user . "</b>)",
		"value" => getenv("DOCUMENT_ROOT") . "/albums",
		"require-write-dir" => 1,
		"filename" => 1,
		"attrs" => array("size" => 50),
	),
	"tmpDir" => array(
		"prompt" => _("Temporary directory"),
		"desc" => _("The filesystem path to a temporary directory.") . "  " .
			_("Eg.") ." <i>/tmp</i> ". _("or") .
			" <i>c:\\windows\\temp</i>." . "  " .
			_("Note that if you have open_basedir configured on your system,") . " " .
			_("then this temporary directory must be inside the open_basedir path!") . "  " .
			_("In that case you may need to create the tmp directory yourself and make sure that it's writable by the webserver process."),
		"value" => getenv("TMP"),
		"filename" => 1,
		"require-write-dir" => 1,
		"attrs" => array("size" => 50),
	),
	"photoAlbumURL" => array(
		"prompt" => "Gallery URL",
		"desc" => _("The full URL to the Gallery web application.") . "  " .
			_("Eg.") . 
			" <i>http://www.foo.com/gallery</i>.",
		"value" => "http://" . getenv("SERVER_NAME") . $GALLERY_URL,
		"no-trailing-slash" => 1,
		"must-be-url" => 1,
		"attrs" => array("size" => 50),
	),
	"albumDirURL" => array(
		"prompt" => _("Albums") ." URL",
		"desc" => _("The full URL to your album directory (specified above) on your web server.") . "  " .
		_("Eg.") ." <i>http://www.foo.com/albums</i>",
		"value" => "http://" . getenv("SERVER_NAME") . "/albums",
		"no-trailing-slash" => 1,
		"must-be-url" => 1,
		"attrs" => array("size" => 50),
	),
	"mirrorSites" => array(
		"prompt" => _("Mirror sites"),
		"desc" => _("A list of URLs that contain mirrors of your albums directory.") . "  " .
			_("Specify one URL per line with the ones that are more reliable towards the top.") . "  " .
			_("Eg.") ." <center><i>" .
			"http://www.fast.server/albums<br>http://www.other.server/albums</i>" .
			"</center>",
		"optional" => 1,
		"type" => "textarea",
		"attrs" => array("cols" => 50, "rows" => 5),
	),
	"showAlbumTree" => array(
		"prompt" => _("Show the album tree"),
		"desc" => _("If you turn this option on, you'll see a complete list of all sub-albums when you're browsing the top level of your Gallery.") . "  " .
			_("This will make navigating to a specific sub album much easier") .
			" (" .
			_("although it can severely clutter the appearance of the top Gallery page") .
			").  " . _("Set to <b>no</b> by default."),
		"choices" => array("yes" => _("yes"), "no" => _("no")), 
		"value" => "no"
	),
	"autorotate" => array(
                "prompt" => _("Auto-rotate images"),
		"desc" => _("Selecting this option has Gallery automatically attempt to rotate images on upload, based on the orientation information that is stored in JPEG EXIF data by some newer digital cameras.") . "  " .
			_("Requires Jhead EXIF parser v.1.9 or greater (see above)."),
                "choices" => array("yes" => _("yes"), "no" => _("no")),
                "value" => "yes"
        ),
	"default_language" => array(
		"prompt" => _("Which language do you want as your default language"), 
		"optional" => 1, 
		"desc" => _("This is what you will see as admin, and what users will see no other preference is set."),
		"choices" => available_locales(),
		"value" => "en_US"
	),
	"ML_mode" => array(
		"prompt" => _("Multi language mode"), 
		"desc" => _("Gallery has three multi language modes.") . "  " .
			  _("These affect the language that a user will see when visiting the site") .
			"\n\t<br><font color=blue><b>(". _("this is new in version 1.3.5"). ")</b></font>",
		"choices" => array(
			1 => "1: " . _("Gallery is only displayed in one language"), 
			2 => "2: " . _("Gallery is displayed in the language the browser sends"), 
			3 => "3: " . _("The User can choose the language via select Box.")),
		"value" => 1
	),
	"available_lang" => array(
		"prompt" => _("Available languages in mode 3"), 
		"optional" => 1, 
		"desc" => _("These languages will be available as user choices in a drop down menu, if you have chosen ML mode 3") .
			"\n\t<br><font color=blue><b>(". _("this is new in version 1.3.5"). ")</b></font>",
		"multiple_choices" => available_locales(),
		"value" => array('en_US')
	)),
	config_maybe_locales(),
    array(
	"cacheExif" => array(
		"prompt" => "<b>(" . _("Advanced") . ")</b> ". _("Cache EXIF data"),
		"desc" => _("The EXIF data extracted from JPEG files can be cached after it is read the first time.") . "  " .
			_("The tradeoff is that if you cache this data it's possible for your album data files to get quite large which may slow down viewing your albums.") . "  " .
			_("But, if you don't cache this data then it needs to be retrieved every time any user looks at the properties of a photo which can also be slow.") . "  " .
 			_("Set to <b>no</b> by default."),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no"
	),
	"jpegImageQuality" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b> ". _("JPEG Quality"),
		"desc" => _("The JPEG file format trades off size versus quality.") . "  " .
			_("Gallery can control the quality of the JPEGs that it produces when you resize, rotate or make thumbnails from JPEG images.") . "  " .
			_("Higher quality images are larger in size than lower quality images.") . "  " .
			_("However, in many cases there's a point at which raising the quality increases the size of the files without noticeably affecting the appearance of the image.") . "  " .
			_("If you notice that your thumbnails and resized images are larger than the originals and you don't like this, you can try lowering this value.") . "  " .
			_("<b>Note</b> that changing this value will only affect images changed in the <i>future</i>."),
		"choices" => array(50 => 50, 55 => 55, 60 => 60, 65 => 65,
				   70 => 70, 75 => 75, 80 => 80, 85 => 85,
				   90 => 90, 95 => 95, 100 => 100),
		"value" => 95,
	),
        "timeLimit" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b> ". _("Time Limit"),
		"desc" => _("The maximum amount of time in seconds any single operation should take.") . "  " .
			_("If you find that you are getting the <b>Maximum execution time exceeded</b> error message, you can try making this a larger number.") . "  " .
			_("Be warned that if you make this too large you <i>might</i> have a process that is stuck for a very long time without doing anything.") . "  " .
			_("The default value is 30 which is pretty reasonable."),
		"type" => text,
		"must-be-number" => 1,
		"must-not-be-zero" => 1,
		"value" => 30,
		"attrs" => array("size" => 5),
	),
	"debug" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b> Debug mode",
		"desc" => _("Turn debug mode on so that you can diagnose problems?"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
	),
	"use_flock" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b> " . _("Use") ." flock()?",
		"desc" => _("Gallery uses a locking system called flock() for added protection against data corruption.") . "  " .
			_("This is not supported on all platforms.") . "  " .
			_("If you're seeing errors like <i>Could not acquire lock</i> then set this value to <i>no</i>"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes"),
	"expectedExecStatus" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b> ". _("Status code"),
		"desc" => _("The status code Gallery expects to see after a successful exec() call.") . "  " .
			_("Gallery will try to figure this out on its own so you probably won't need to change this value unless you have problems."),
		"value" => detect_exec_status(),
		"attrs" => array("size" => 5),
	),
	"sessionVar" => array(
		"prompt" => "<b>(" . _("Advanced") .")</b> ". _("Session variable"),
		"desc" => _("This is the session variable used to contain all Gallery session information.") . "  " .
			_("Change this if there is a conflict between Gallery and another PHP app on your website."),
		"value" => "gallery_session",
		"attrs" => array("size" => 20),
	),

	/* Keep this one at the end of the list because it's hidden */
	"userDir" => array(
		"desc" => "Filesystem location of user information",
		"type" => "hidden",
		"value" => "",
		"eval" => '"$albumDir/.users"',
		"filename" => 1,
	),
    )
);

$defaults = array(
	"bordercolor" => array(
		"prompt" => _("Border color"),
 		"desc" => _("Default color of image borders. You can learn about colors here:") .   
			   '  <a href="http://www.permadi.com/tutorial/websafecolor/">' . 
			   // '  <a href="http://gotomy.com/color.html">' . 
			   _("Colors") . '</a>',

		// <a href="http://www.w3.org/TR/REC-html40/types.html#h-6.5">W3.org</a>',
 		"value" => "black",
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
	"thumb_size" => array(
		"prompt" => _("Thumbnail size"),
		"desc" => _("The default target size (in pixels) of album thumbnails."),
		"value" => 150,
		"attrs" => array("size" => 10),
	),
	"resize_size" => array(
		"prompt" => _("Resized image size"),
		"desc" => _("When large images are added to an album, an intermediary version will automatically be generated at this size"),
		"value" => 640,
		"choices" => array("off" => _("off"), 400 => 400, 500 => 500, 600 => 600, 640 => 640, 700 => 700, 800 => 800, 1024 => 1024)
	),
	"fit_to_window" => array(
		"prompt" => _("Fit-to-window"),
		"desc" => _("When viewing photos which don't have an intermediary version") . ", " .
			_("Gallery can automatically resize the image to fit the viewer's browser window (using JavaScript).") . "  " .
			_("This may not work in embedded environments such as Nuke"),
		"value" => "no",
		"choices" => array("yes" => _("yes"), "no" => _("no"))
	),
	"use_fullOnly" => array(
		"prompt" => _("Visitor size prefs"),
		"desc" => _("Offer visitors the choice of always viewing the full sized version of an image instead of the intermediary size.") . "  " .
			_("This setting only lasts for the visitor's session."),
		"value" => "no",
		"choices" => array("yes" => _("yes"), "no" => _("no"))
	),
	"print_photos" => array(
		"prompt" => _("Printing Service"),
		"desc" => _("Allow visitors to print photos directly from the album.") . "  " .
			_("If you choose shutterfly, Gallery will charge an extra 2 cents (US) for every photo printed") .
			" (". _("a 4x6 print normally costs 49 cents US") .
			"), " . _("and 1.8 cents of that will get donated back to the Gallery project.") . "  " .
			_("This is one great way for you to help support this free software."),
		"value" => "shutterfly",
		"choices" => array("none" => _("none"), "shutterfly" => _("shutterfly"), "shutterfly without donation" => _("shutterfly without donation"), "photoaccess" => _("photoaccess"), "fotokasten" => _("fotokasten"))
	),
	"returnto" => array(
		"prompt" => _("Gallery Link"),
		"desc" => _("Display the top level Gallery link within the album?") . "  " .
			_("If you turn this feature off, the user will not be able to navigate out of the album.") . "  " .
			_("This is only useful when you want to embed an album within a website and not allow users to (easily) view the other albums in the gallery"),
		"value" => "yes",
		"choices" => array("yes" => _("yes"), "no" => _("no"))
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
	),
	"showSearchEngine" => array(
		"prompt" => _("Search Engine"),
		"desc" => _("Display search engine on main Gallery page?"),
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes",
	),
	"useOriginalFileNames" => array(
		"prompt" => _("Preserve Names"),
                "desc" => _("When uploading images to Gallery, should we preserve the original names?") . "  " .
			 _("If you select <i>no</i>, Gallery will assign a new name for each photo or movie that gets uploaded."),
                "choices" => array("yes" => _("yes"), "no" => _("no")),
                "value" => "yes",
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
		"desc" => _("Custom fields that are available to describe each image.") . "  " .
			  _("They should be separated by commas"). " " .
			  _("Eg. <i>Description, Location, Film Type</i>"),
		"attrs" => array("size" => 80),
		"value" => "Description"
	),
	"item_owner_modify" => array(
		"prompt" => _("Photo owner modification"),
		"desc" => _("Allow photo owners to edit and rotate/flip their own photos?") .
			  "<br><font color=blue><b>(". _("this is new in version 1.3.5") . ")</b></font>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes",
	),
	"item_owner_delete" => array(
		"prompt" => _("Photo owner deletion"),
		"desc" => _("Allow photo owners to delete their own photos?") .
			  "<br><font color=blue><b>(" . _("this is new in version 1.3.5") .")</b></font>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "yes",
	),
	"item_owner_display" => array(
		"prompt" => _("Photo owner display"),
		"desc" => _("Display the name of photo owners with the caption?") .
			  "<br><font color=blue><b>(" . _("this is new in version 1.3.5") .")</b></font>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
	),
	"add_to_beginning" => array(
		"prompt" => _("Add to beginning"),
		"desc" => _("New images added to beginning rather than end of albums") .
			  "<br><font color=blue><b>(" . _("this is new in version 1.3.5") .")</b></font>",
		"choices" => array("yes" => _("yes"), "no" => _("no")),
		"value" => "no",
	),
);

?>

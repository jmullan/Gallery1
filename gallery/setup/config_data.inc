<!-- $Id$ -->
<?php

$common_messages["getting-netpbm"] =
			"Gallery prefers NetPBM version 9.9 and up.  " .
			"You can install just the binaries Gallery needs from " .
			"<a href=http://sourceforge.net/projects/gallery>Gallery on SourceForge</a> ".
			"or, you can compile and install the entire NetPBM package from " .
			"<a href=http://sourceforge.net/projects/netpbm>NetPBM on SourceForge</a>. " .
			"<br><b>Note:</b> You can install the binaries from the Gallery site even ".
			"if you don't have root access on your box!  If you can't get it working, " .
                        "try leaving the NetPBM path blank and using ImageMagick intead.";
			
$common_messages["fail-netpbm-partial"] =
			"I've found part of NetPBM, but some critical components " .
			"are missing (see below).  Perhaps your version of NetPBM is out of date?  " .
			$common_messages["getting-netpbm"];

$common_messages["getting-imagemagick"] =
			"Gallery prefers ImageMagick version 5.4.8 and up.  " .
			"You can compile and install the entire ImageMagick package from " .
			"<a href=http://www.imagemagick.org>www.ImageMagick.org</a>. " .
			"<br><b>Note</b>:  They also have binaries available for assorted operating systems. " .
                        "If you can't get it working, try leaving the ImageMagick path blank " .
                        "and using NetPBM instead.";

$common_messages["open-basedir-active"] =
			"<b>Note:</b> Your webserver is configured with the " .
			"<a href=\"http://www.php.net/manual/en/features.safe-mode.php#ini.open-basedir\"> " .
			"open_basedir</a> restriction.  This may make it difficult for Gallery to detect " .
			"and verify your binaries, even if you've already successfully used the Config " .
			"Wizard in the past.  Don't worry too much if this page gives warnings; your " .
			"settings will still be saved properly.";
			
$checklist = array(
	"php4" => array(
		"desc" => "Check to see if you are running PHP 4.  Note that some versions of PHP ".
			  "have bugs that cause Gallery to fail.  PHP4.0.0 uses a different file " .
			  "upload protocol, versions 4.0.3 and 4.0.3pl1 have a bug in the " .
			  "unserialization code that cause Gallery to be unable to read its own " .
			  "database.  Upgrade to at least PHP 4.0.4pl1 if you can.",
	     	"func" => "check_php",
		"fail-too-old" => "You're running version " . phpversion() .
			". You need to upgrade to PHP ${MIN_PHP_MAJOR_VERSION}",
		"fail-incompatible" => "You're running version " . phpversion() .
			" which has known bugs that cause Gallery to fail.",
	),
	"safe_mode" => array(
		"desc" => "Check to see if PHP is running in <b>Safe Mode</b>.  Safe mode is a ".
			  "PHP security feature commonly used in shared environments.  Gallery ".
			  "cannot run when safe mode is enabled because it imposes too many ".
			  "restrictions.",
		"func" => "check_safe_mode",
		"fail-safe-mode" => "<b>Safe Mode</b> is enabled on this server.  You must ".
			  "disable it before you can use Gallery.  Refer to the FAQ, the ".
			  "forums, and the help page on the <a href='http://gallery.sourceforge.net'>".
			  "Gallery website</a>, and the ".
			  "<a href='http://www.php.net/manual/en/features.safe-mode.php'>".
			  "PHP website</a> for assistance." .
			  "<br>" .
			  "<br>" .
			  "<b>Note</b> That under some rare circumstances, Gallery thinks " .
			  "that <b>safe mode</b> is on, when it's really off.  You can " .
			  "see for yourself by looking in the <i>Local</i> column of " .
			  "the safe_mode row in the <a href=phpinfo.php>PHP Info</a> page. " .
			  "If it says 'Off' or '0' or 'undefined' then it's possible that " .
			  "Gallery made a mistake and you can continue.  If not, then " .
			  "you must resolve this problem before you can use Gallery.",
		"optional" => 1,
		"serious" => 1,
	),
	"htaccess" => array(
		"desc" => "Allow the Gallery <b>.htaccess</b> file to set " .
			  "application specific configuration options " .
			  "for the Gallery application.  This is controlled " .
			  "by the webserver owner so it's not always possible " .
			  "for you to change it.  The main drawbacks to not " .
			  "having this are that you may not be able to upload " .
			  "files > 2MB (eg, big ZIP files) and you may not be " .
			  "able to use mod_rewrite (see below).",
	     	"func" => "check_htaccess",
		"fail-htaccess" => "Apache is not obeying your .htaccess file.  Try ".
			"entering the following into your web server's httpd.conf " .
			 "file: <br><pre>" .
			 "&lt;Directory $GALLERY_DIR" .
			 "&gt;\n\tAllowOverride Options FileInfo\n" .
			 "&lt;/Directory&gt;",
		"optional" => 1,
	),
	"magic-quotes" => array(
		"optional" => 1,
		"desc" => "Check to see if <a href=" .
			"http://www.php.net/manual/en/configuration.php#ini.magic-quotes-gpc " .
			" target=_top>magic quotes</a> are enabled on your PHP server.  If " .
			"they're on, you may notice strange behaviour if you put ' (single-quote),".
			" \" (double quote) or \\ (backslash) characters in titles or " .
			"captions in the gallery.",
	     	"func" => "check_magic_quotes",
		"fail-magic-quotes" => "Magic quotes are on.  Your webserver owner can turn them off " .
			 "by putting: <pre>\n\t" .
			 "magic_quotes_gpc = Off" .
			 "</pre> in your php.ini file",
	),
	"mod-rewrite" => array(
		"desc" => "Check to see if your webserver is compiled with " .
			"<b>mod_rewrite</b>.  Mod_rewrite allows the Gallery to use ".
			"shorter URLs that are easier to read and look nicer when " .
			"you mail them to your web-challenged relatives. " .
			"It's not an essential feature.",
	     	"func" => "check_mod_rewrite",
		"fail-mod-rewrite" => "Either <b>mod_rewrite</b> is not installed or your " .
			"<b>.htaccess</b> file is not enabled (see above).  Either " .
			"way, we'll have to use longer URLs in the Gallery.  If you " .
			"want to turn it on I'd suggest that you make sure that your " .
			"<b>.htaccess</b> file works and then if it still doesn't work " .
			" you may need to reconfigure and rebuild Apache with this flag:<br>" .
			"<b>--enable-module=rewrite</b>",
		"optional" => 1,
	),
	"NetPBM" => array(
		"optional" => 1,
		"desc" => "Check to see if NetPBM is installed so that " .
			"Gallery can manipulate images (rotate, resize, make " .
			"thumbnails, etc).  You need to have NetPBM <b>or</b> " .
			"ImageMagick installed.",
		"func" => "check_graphics",
		"fail-netpbm" => "I can't find NetPBM.  If it's installed and not in " .
			"the path of the webserver user that's OK -- you can specify the " .
			"path to NetPBM by hand on the following page.  If it's not " .
			"installed, you have two choices:  " .
			$common_messages["getting-netpbm"],

		"fail-netpbm-partial" => 
			$common_messages["fail-netpbm-partial"] . 
			"<p><b> If NetPBM is installed and Gallery isn't finding it automatically " .
			"that's OK -- that just means you'll have to specify the path to NetPBM " .
			"by hand on the following page </b>",

		"open-basedir-note" => $common_messages["open-basedir-active"],
	),
	"ImageMagick" => array(
		"optional" => 1,
		"desc" => "Check to see if ImageMagick is installed so that " .
			"Gallery can manipulate images (rotate, resize, make " .
			"thumbnails, etc).  You need to have ImageMagick <b>or</b> " .
			"NetPBM installed.",
		"func" => "check_graphics_im",
		"fail-imagemagick" => "I can't find ImageMagick.  If it's installed and not in " .
			"the path of the webserver user that's OK -- you can specify the " .
			"path to ImageMagick by hand on the following page.  If it's not " .
			"installed, you must install it.  " .
			$common_messages["getting-imagemagick"],
		"open-basedir-note" => $common_messages["open-basedir-active"],
	),
	"exif" => array(
		"optional" => 1,
		"desc" => "Check to see if jhead is installed so that " .
			"Gallery can examine EXIF headers embedded in images " .
			"created with most digital cameras.",
		"func" => "check_exif",
		"fail-exif" => "I can't find jhead.  If it's installed and not in " .
			"the path of the webserver user that's OK -- you can specify the " .
			"path to jhead by hand on the following page.  If it's not " .
			"installed, you can install it yourself.  " .
			"jhead is a public domain EXIF parser. Source, linux binaries, and " .
			"windows binaries can be found at " .
			"<a href=http://www.sentex.net/~mwandel/jhead/index.html>the jhead " .
			"homepage</a>.",
		"open-basedir-note" => $common_messages["open-basedir-active"],
	)
);

$features = array(
	"zip" => array("zipinfo", "unzip"),
	"rewrite" => array("GALLERY_REWRITE_OK"),
	"mirror" => array("mirrorSites"),
);

$constants = array(
	"galleryTitle" => array(
		"prompt" => "Gallery Title",
		"desc" => "The name of your gallery.  Eg. <i>My Cool Photos</i>",
		"value" => "Gallery",
		"attrs" => array("size" => 50),
	),
	"editPassword" => array(
		"prompt" => "Admin password",
		"desc" => "The password for the <i>admin</i> account.  Unfortunately, you're " .
			"required to re-enter this every time you reconfigure Gallery. " .
			"We'll fix this in a future release!",
		"type" => "text",
		"dont-write" => 1,
		"attrs" => array("size" => 50),
	),
	"graphics" => array(
		"prompt" => "Graphics suite to use?",
		"desc" => "This setting tells Gallery which set of graphics tools you prefer it to use.  If Gallery says both are okay (on the first page) then this is simply user preference.  Otherwise go with the one that Gallery was able to find without error.",
		"choices" => array("NetPBM", "ImageMagick"),
		"value" => default_graphics(),
	),
	"pnmDir" => array(
		"optional" => 1,
		"prompt" => "Path to NetPBM",
		"desc" => "The path to the directory containing NetPBM on your system.  If you " .
			"downloaded NetPBM from the Gallery download page, don't forget to make " .
			"the files executable.  Eg. <i>/usr/local/netpbm</i> or <i>/usr/local/bin</i> " .
			"or <i>c:\\apps\\netpbm</i>",
		"value" => locateDir("jpegtopnm", $gallery->app ? $gallery->app->pnmDir : ""),
		"verify-func" => "check_graphics",
		"fail-netpbm" => "I can't find NetPBM at the location you provided.  " .
			$common_messages["getting-netpbm"],
		"fail-netpbm-partial" => $common_messages["fail-netpbm-partial"],
		"filename" => 1,
		"attrs" => array("size" => 50),
		"no-trailing-slash" => 1,
		"verify-func-takes-graphics-type" => true,
	),
	"pnmtojpeg" => array(
		"prompt" => "PNM to JPEG converter",
		"desc" => "This value is only relevant if you use NetPBM.  " .
			  "NetPBM uses a tool to encode its internal image format into JPEG. " .
			  "This tool is called either <i>ppmtojpeg</i> or <i>pnmtojpeg</i> " .
			  "depending on the version of NetPBM that you have installed.  " .
			  "Check your NetPBM directory to see which one you have installed and " .
			  "specify it here.  If you aren't sure, just take a guess and we'll " .
			  "try to verify it for you.  " ,

		"choices" => array("pnmtojpeg", "ppmtojpeg"),
		"value" => "pnmtojpeg",
	),
	"ImPath" => array(
		"optional" => 1,
		"prompt" => "Path to ImageMagick",
		"desc" => "The path to the directory containing ImageMagick on your system.",
		"verify-func" => "check_graphics_im",
		"value" => locateDir("convert", $gallery->app ? $gallery->app->ImPath :
 ""),
		"fail-imagemagick" => "I can't find ImageMagick at the location you provided.  " .
			$common_messages["getting-imagemagick"],
		"filename" => 1,
		"attrs" => array("size" => 50),
		"no-trailing-slash" => 1,
		"verify-func-takes-graphics-type" => true,
	),
	"highlight_size" => array(
		"prompt" => "Highlight size",
		"desc" => "The target size (in pixels) of thumbnails " .
			  "shown in the gallery page (highlight images)",
 		"value" => 200,
		"attrs" => array("size" => 5),
	),
	"zipinfo" => array(
		"prompt" => "Zipinfo",
		"desc" => "The filesystem path to the 'zipinfo' binary.  This is optional " .	
			"so if you don't have it, don't worry about it too much",
		"value" => locateFile("zipinfo"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"attrs" => array("size" => 50),
	),
	"unzip" => array(
		"prompt" => "Unzip",
		"desc" => "The filesystem path to the 'unzip' binary.  This is optional " .	
			"so if you don't have it, don't worry about it too much",
		"value" => locateFile("unzip"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"attrs" => array("size" => 50),
	),
	"use_exif" => array(
		"prompt" => "EXIF",
		"desc" => "The filesystem path to the 'jhead' exif parser.  This is optional " .	
			"so if you don't have it, don't worry about it too much.  Eg. <i> " .
			"/usr/local/bin/jhead</i> or <i>c:\\bin\\jhead.exe</i>",
		"value" => locateFile("jhead"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
		"attrs" => array("size" => 50),
	),
	"movieThumbnail" => array(
		"prompt" => "Thumbnail image",
		"desc" => "The filesystem path to the thumbnail image to show for movies.  Eg. " .
			"/home/foo/thumbnail.jpg",
		"value" => $GALLERY_DIR . "/images/movie.thumb.jpg",
		"attrs" => array("size" => 50),
	),
	"albumDir" => array(
		"prompt" => "Album directory",
		"desc" => "The full path on physical disk to the directory " .
			"where your photos will be stored.  This directory " .
			"should start off empty -- Gallery will fill it. " .
			"Note that this " .
			"directory needs to have write access by the user who " .
			"is running the web server (in your case this user is <b>" .
			$webserver_user . "</b>)",
		"value" => getenv("DOCUMENT_ROOT") . "/albums",
		"require-write-dir" => 1,
		"filename" => 1,
		"attrs" => array("size" => 50),
	),
	"tmpDir" => array(
		"prompt" => "Temporary directory",
		"desc" => "The filesystem path to a temporary directory.  Eg. <i>/tmp</i> or " .
			"<i>c:\\windows\\temp</i>.  Note that if you have open_basedir " .
			  "configured on your system, then this temporary directory " .
			  "must be inside the open_basedir path!  In that case you may need " .
			  "to create the tmp directory yourself and make sure that it's writable " .
			  "by the webserver process",
		"value" => getenv("TMP"),
		"filename" => 1,
		"require-write-dir" => 1,
		"attrs" => array("size" => 50),
	),
	"photoAlbumURL" => array(
		"prompt" => "Gallery URL",
		"desc" => "The full URL to the Gallery web application.  Eg. " .
			"<i>http://www.foo.com/gallery</i>." ,
		"value" => "http://" . getenv("SERVER_NAME") . $GALLERY_URL,
		"no-trailing-slash" => 1,
		"must-be-url" => 1,
		"attrs" => array("size" => 50),
	),
	"albumDirURL" => array(
		"prompt" => "Albums URL",
		"desc" => "The full URL to your album directory (specified above) " .
			"on your web server.  Eg. <i>http://www.foo.com/albums</i>",
		"value" => "http://" . getenv("SERVER_NAME") . "/albums",
		"no-trailing-slash" => 1,
		"must-be-url" => 1,
		"attrs" => array("size" => 50),
	),
	"mirrorSites" => array(
		"prompt" => "Mirror sites",
		"desc" => "A list of URLs that contain mirrors of your " .
			"albums directory.  Specify one URL per line with the ones " .
			"that are more reliable towards the top.  Eg. <center><i>" .
			"http://www.fast.server/albums<br>http://www.other.server/albums</i>" .
			"</center>",
		"optional" => 1,
		"type" => "textarea",
		"attrs" => array("cols" => 50, "rows" => 5),
	),
	"showAlbumTree" => array(
		"prompt" => "Show the album tree",
		"desc" => "If you turn this option on, you'll see a complete list " .
			"of all sub-albums when you're browsing the top level of " .
			"your Gallery.  This will make navigating to a specific " .
			"sub album much easier (although it can severely clutter " .
			"the appearance of the top Gallery page).  Set to <b>no</b> " .
			"by default. ",
		"choices" => array("yes", "no"),
		"value" => "no"
	),
	"autorotate" => array(
                "prompt" => "Auto-rotate images",
		"desc" => "Selecting this option has Gallery automatically attempt " .
			"to rotate images on upload, based on the orientation information " .
			"that is stored in JPEG EXIF data by some newer digital cameras. " .
			"Requires Jhead EXIF parser v.1.9 or greater (see above).",
                "choices" => array("yes", "no"),
                "value" => "yes"
        ),
	"cacheExif" => array(
		"prompt" => "<b>(Advanced)</b> Cache EXIF data",
		"desc" => "The EXIF data extracted from JPEG files can be cached " .
			"after it is read the first time.  The tradeoff is that if " .
			"you cache this data it's possible for your album data files " .
			"to get quite large which may slow down viewing your albums. " .
			"But, if you don't cache this data then it needs to be " .
			"retrieved every time any user looks at the properties of a " .
			"photo which can also be slow.  Set to <b>no</b> by default.",
		"choices" => array("yes", "no"),
		"value" => "no"
	),
	"jpegImageQuality" => array(
		"prompt" => "<b>(Advanced)</b> JPEG Quality",
		"desc" => "The JPEG file format trades off size versus quality.  " .
		    "Gallery can control the quality of the JPEGs that it produces " .
		    "when you resize, rotate or make thumbnails from JPEG images." .
		    "Higher quality images are larger in size than lower quality images. " .
		    "However, in many cases there's a point at which raising the " .
		    "quality increases the size of the files without noticeably " .
		    "affecting the appearance of the image.  If you notice that your " .
		    "thumbnails and resized images are larger than the originals and " .
		    "you don't like this, you can try lowering this value.  <b>Note</b> " .
		    "that changing this value will only affect images changed in the " .
		    "<i>future</i>.",
		"choices" => array(50, 55, 60, 65,
				   70, 75, 80, 85,
				   90, 95, 100),
		"value" => 95,
	),
        "timeLimit" => array(
		"prompt" => "<b>(Advanced)</b> Time Limit",
		"desc" => "The maximum amount of time in seconds any single operation should " .
			"take.  If you find that you are getting the <b>Maximum " .
			"execution time exceeded</b> error message, you can try making " .
			"this a larger number.  Be warned that if you make this too " .
			"large you <i>might</i> have a process that is stuck for a " .
			"very long time without doing anything.  The default value " .
			"is 30 which is pretty reasonable.",
		"type" => text,
		"must-be-number" => 1,
		"must-not-be-zero" => 1,
		"value" => 30,
		"attrs" => array("size" => 5),
	),
	"debug" => array(
		"prompt" => "<b>(Advanced)</b> Debug mode",
		"desc" => "Turn debug mode on so that you can diagnose problems?",
		"choices" => array("yes", "no"),
		"value" => "no",
	),
	"use_flock" => array(
		"prompt" => "<b>(Advanced)</b> Use flock()?",
		"desc" => "Gallery uses a locking system called flock() for added ".
			  "protection against data corruption.  This is not supported " .
			  "on all platforms.  If you're seeing errors like <i>Could " .
			  "not acquire lock</i> then set this value to <i>no</i>",
		"choices" => array("yes", "no"),
		"value" => "yes"),
	"expectedExecStatus" => array(
		"prompt" => "<b>(Advanced)</b> Status code",
		"desc" => "The status code Gallery expects to see after a successful " .
			"exec() call.  Gallery will try to figure this out on its own so you " .
			"probably won't need to change this value unless you have problems.",
		"value" => detect_exec_status(),
		"attrs" => array("size" => 5),
	),
	"sessionVar" => array(
		"prompt" => "<b>(Advanced)</b> Session variable",
		"desc" => "This is the session variable used to " .
			"contain all Gallery session information. " .
			"Change this if there is a conflict between " .
			"Gallery and another PHP app on your website.",
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
);

$defaults = array(
	"bordercolor" => array(
		"prompt" => "Border color",
 		"desc" => "Default color of image borders",
 		"value" => "black",
		"attrs" => array("size" => 10),
	),
	"border" => array(
		"prompt" => "Border width",
		"desc" => "Default width of image borders",
		"value" => 1,
		"choices" => array("off", 1, 2, 3)
	),
	"font" => array(
		"prompt" => "Default Font",
		"desc" => "Default font face.  Eg. <i>arial</i> or <i>trebuchet</i>",
		"value" => "arial",
		"attrs" => array("size" => 10),
	),
	"cols" => array(
		"prompt" => "Default Columns",
		"desc" => "The default number of columns in an album",
		"value" => 3,
		"choices" => array(1, 2, 3, 4, 5, 6, 7, 8, 9),
	),
	"rows" => array(
		"prompt" => "Default Rows",
		"desc" => "The default number of rows in an album",
		"value" => 3,
		"choices" => array(1, 2, 3, 4, 5, 6, 7, 8, 9),
	),
	"thumb_size" => array(
		"prompt" => "Thumbnail size",
		"desc" => "The default target size (in pixels) of album thumbnails.",
		"value" => 150,
		"attrs" => array("size" => 10),
	),
	"resize_size" => array(
		"prompt" => "Resized image size",
		"desc" => "When large images are added to an album, an intermediary version " .
			"will automatically be generated at this size",
		"value" => 640,
		"choices" => array("off", 400, 500, 600, 640, 700, 800, 1024)
	),
	"fit_to_window" => array(
		"prompt" => "Fit-to-window",
		"desc" => "When viewing photos which don't have an intermediary version, " .
			"Gallery can automatically resize the image to fit the viewer's " .
			"browser window (using JavaScript).  This may not work in embedded " .
			"environments such as Nuke",
		"value" => "no",
		"choices" => array("yes", "no")
	),
	"use_fullOnly" => array(
		"prompt" => "Visitor size prefs",
		"desc" => "Offer visitors the choice of always viewing the full sized version" .
			"of an image instead of the intermediary size.  This setting only " .
			"lasts for the visitor's session",
		"value" => "no",
		"choices" => array("yes", "no")
	),
	"print_photos" => array(
		"prompt" => "Printing Service",
		"desc" => "Allow visitors to print photos directly from the album.  If you choose " .
			  "shutterfly, Gallery will charge an extra 2 cents (US) for every " .
			  "photo printed (a 4x6 print normally costs 49 cents US), and 1.8 cents of " .
			  "that will get donated back to the Gallery project.  This is one great way " .
			  "for you to help support this free software. ",
		"value" => "shutterfly",
		"choices" => array("none", "shutterfly", "shutterfly without donation", "photoaccess", "fotokasten")
	),
	"returnto" => array(
		"prompt" => "Gallery Link",
		"desc" => "Display the top level Gallery link within the album?  If you " .
			"turn this feature off, the user will not be able to navigate out " .
			"of the album.  This is only useful when you want to embed an " .
			"album within a website and not allow users to (easily) view the " .
			"other albums in the gallery",
		"value" => "yes",
		"choices" => array("yes", "no")
	),
	"showOwners" => array(
		"prompt" => "Show Owner",
		"desc" => "Show album owner's name on the top level Gallery page?",
		"choices" => array("yes", "no"),
		"value" => "no",
	),
	"albumsPerPage" => array(
		"prompt" => "Albums per page",
		"desc" => "How many albums should be displayed per Gallery page?",
		"value" => "5",
		"attrs" => array("size" => 10),
	),
	"showSearchEngine" => array(
		"prompt" => "Search Engine",
		"desc" => "Display search engine on main Gallery page?",
		"choices" => array("yes", "no"),
		"value" => "yes",
	),
	"useOriginalFileNames" => array(
		"prompt" => "Preserve Names",
                "desc" => "When uploading images to Gallery, should we preserve the original " .
			"names?  If you select <i>no</i>, Gallery will assign a new name for " .
			"each photo or movie that gets uploaded.",
                "choices" => array("yes", "no"),
                "value" => "yes",
	),
	"display_clicks" => array(
		"prompt" => "Click counts",
		"desc" => "Display click counts within Albums?",
		"choices" => array("yes", "no"),
		"value" => "yes",
	),
	"public_comments" => array(
		"prompt" => "Comments",
		"desc" => "Allow the public to add comments to your photos?",
		"choices" => array("yes", "no"),
		"value" => "yes",
	),
	"extra_fields" => array(
		"prompt" => "Custom Fields",
		"optional" => 1,
		"desc" => "Custom fields that are available to describe each image.  ".
			  "They should be separated by commas ".
			  "Eg. <i>Description, Location, Film Type<i>",
		"attrs" => array("size" => 80),
		"value" => "Description"
	),
	"item_owner_modify" => array(
		"prompt" => "Photo owner modification",
		"desc" => "Allow photo owners to edit and rotate/flip their own photos?".
			  "<br><font color=blue><b>(this is new in version 1.3.5)</b></font>",
		"choices" => array("yes", "no"),
		"value" => "yes",
	),
	"item_owner_delete" => array(
		"prompt" => "Photo owner deletion",
		"desc" => "Allow photo owners to delete their own photos?".
			  "<br><font color=blue><b>(this is new in version 1.3.5)</b></font>",
		"choices" => array("yes", "no"),
		"value" => "yes",
	),
	"item_owner_display" => array(
		"prompt" => "Photo owner display",
		"desc" => "Display  the name of photo owners with the caption?".
			  "<br><font color=blue><b>(this is new in version 1.3.5)</b></font>",
		"choices" => array("yes", "no"),
		"value" => "no",
	)
);

?>

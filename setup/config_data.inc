<?

$common_messages["getting-netpbm"] =
			"Gallery prefers NetPBM version 9.9 and up.  " .
			"You can install the entire NetPBM package from " .
			"<a href=http://sourceforge.net/projects/netpbm>NetPBM on SourceForge</a> " .
			"or you can install just the binaries Gallery needs from " .
			"<a href=http://sourceforge.net/projects/gallery>Gallery on SourceForge</a>. ".
			"<b>Note:</b> You can install the binaries from the Gallery site even ".
			"if you don't have root access on your box!  If Gallery does not have the ".
			"specific binaries for your hardware/software combination post a request ".
			"On the Gallery website and we'll build them for you.";

			
$common_messages["fail-netpbm-partial"] =
			"I've found part of NetPBM, but some critical components " .
			"are missing (see below).  Perhaps your version of NetPBM is out of date?  " .
			$common_messages["getting-netpbm"];


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
			 "&lt;Directory " .
			 dirname(dirname(getenv("SCRIPT_FILENAME"))) .
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
	"Graphics" => array(
		"optional" => 1,
		"desc" => "Check to see if NetPBM is installed so that " .
			"Gallery can manipulate images (rotate, resize, make " .
			"thumbnails, etc).",
		"func" => "check_graphics",
		"fail-netpbm" => "I can't find NetPBM.  If it's installed and not in " .
			"the path of the webserver user that's OK -- you can specify the " .
			"path to NetPBM by hand on the following page.  If it's not " .
			"installed, you have two choices.  " .
			$common_messages["getting-netpbm"],

		"fail-netpbm-partial" => 
			$common_messages["fail-netpbm-partial"] . 
			"<p><b> If NetPBM is installed and Gallery isn't finding it automatically " .
			"that's OK -- that just means you'll have to specify the path to NetPBM " .
			"by hand on the following page </b>",
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
	"tmpDir" => array(
		"prompt" => "Temporary directory",
		"desc" => "The filesystem path to a temp directory.  Eg. <i>/tmp</i> or " .
			"<i>c:\windows\temp</i>",
		"value" => getenv("TMP"),
		"filename" => 1,
		"require-write-dir" => 1,
		"attrs" => array("size" => 50),
	),
	"pnmDir" => array(
		"prompt" => "Path to NetPBM",
		"desc" => "The path to NetPBM on your system.  If you downloaded NetPBM " .
			"from the Gallery download page, don't forget to make the files " .
			"executable.",
		"value" => locateDir("jpegtopnm", $gallery->app ? $gallery->app->pnmDir : ""),
		"verify_func" => "check_graphics",
		"fail-netpbm" => "I can't find NetPBM at the location you provided.  " .
			$common_messages["getting-netpbm"],
		"fail-netpbm-partial" => $common_messages["fail-netpbm-partial"],
		"filename" => 1,
		"attrs" => array("size" => 50),
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
			"so if you don't have it, don't worry about it too much",
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
	"photoAlbumURL" => array(
		"prompt" => "Gallery URL",
		"desc" => "The full URL to the Gallery web application.  Eg. " .
			"<i>http://www.foo.com/gallery</i>",
		"value" => "http://" . getenv("SERVER_NAME") . $GALLERY_URL,
		"no-trailing-slash" => 1,
		"attrs" => array("size" => 50),
	),
	"albumDirURL" => array(
		"prompt" => "Albums URL",
		"desc" => "The full URL to your album directory (specified above) " .
			"on your web server.  Eg. <i>http://www.foo.com/albums</i>",
		"value" => "http://" . getenv("SERVER_NAME") . "/albums",
		"no-trailing-slash" => 1,
		"attrs" => array("size" => 50),
	),
	"mirrorSites" => array(
		"prompt" => "Mirror sites",
		"desc" => "A list of URLs that contain mirrors of your " .
			"albums directory.  Specify one URL line with the ones " .
			"that are more reliable towards the top.  Eg. <center><i>" .
			"http://www.fast.server/albums<br>http://www.other.server/albums</i>" .
			"</center>",
		"optional" => 1,
		"type" => "textarea",
		"attrs" => array("cols" => 50, "rows" => 5),
	),
	"debug" => array(
		"prompt" => "<b>(Advanced)</b> Debug mode",
		"desc" => "Turn debug mode on so that you can diagnose problems?",
		"choices" => array("yes", "no"),
		"value" => "no",
	),
	"expectedExecStatus" => array(
		"prompt" => "<b>(Advanced)</b> Status code",
		"desc" => "The status code Gallery expects to see after a successful " .
			"exec() call.  It'll try to figure this out on its own so you " .
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
		"attrs" => array("size" => 10),
	),
	"rows" => array(
		"prompt" => "Default Rows",
		"desc" => "The default number of rows in an album",
		"value" => 3,
		"attrs" => array("size" => 10),
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
		"value" => 600,
		"choices" => array("off", 400, 500, 600, 700, 800)
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
		"desc" => "Allow visitors to print photos directly from the album.",
		"value" => "shutterfly",
		"choices" => array("none", "shutterfly")
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
                "value" => "no",
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
	)
);

?>

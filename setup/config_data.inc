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
);

$constants = array(
	"galleryTitle" => array(
		"desc" => "The Gallery Title",
		"value" => "Gallery"
	),
	"editPassword" => array(
		"desc" => "The password for the <i>admin</i> account",
		"type" => "text",
		"dont-write" => 1,
	),
	"tmpDir" => array(
		"desc" => "The filesystem path of a temp directory",
		"value" => getenv("TMP"),
		"filename" => 1,
		"require-write-dir" => 1,
	),
	"pnmDir" => array(
		"desc" => "The filesystem path to your NetPBM binaries.",
		"value" => locateDir("jpegtopnm", $gallery->app ? $gallery->app->pnmDir : ""),
		"verify_func" => "check_graphics",
		"fail-netpbm" => "I can't find NetPBM at the location you provided.  " .
			$common_messages["getting-netpbm"],
		"fail-netpbm-partial" => $common_messages["fail-netpbm-partial"],
		"filename" => 1,
	),
	"highlight_size" => array(
		"desc" => "The target size (in pixels) of thumbnails " .
			  "shown in the gallery page (highlight images)",
 		"value" => 200
	),
	"zipinfo" => array(
		"desc" => "The filesystem path to the 'zipinfo' binary",
		"value" => locateFile("zipinfo"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
	),
	"unzip" => array(
		"desc" => "The filesystem path to the 'unzip' binary",
		"value" => locateFile("unzip"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
	),
	"use_exif" => array(
		desc => "The filesystem path to the 'jhead' exif parser (optional)",
		"value" => locateFile("jhead"),
		"must-be-file" => 1,
		"must-be-executable" => 1,
		"optional" => 1,
		"filename" => 1,
	),
	"movieThumbnail" => array(
		"desc" => "The filesystem path to the thumbnail image to show for movies",
		"value" => $GALLERY_DIR . "/images/movie.thumb.jpg"
	),
	"albumDir" => array(
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
	),
	"userDir" => array(
		"desc" => "Filesystem location of user information",
		"type" => "hidden",
		"value" => "",
		"eval" => '"$albumDir/.users"',
		"filename" => 1,
	),
	"photoAlbumURL" => array(
		"desc" => "The full URL to the Gallery web app",
		"value" => "http://" . getenv("SERVER_NAME") . $GALLERY_URL,
		"no-trailing-slash" => 1,
	),
	"albumDirURL" => array(
		"desc" => "The relative path to the album directory (specified above) " .
			"on your web server",
		"value" => "/albums",
		"no-trailing-slash" => 1,
	),
	"debug" => array(
		desc => "Turn debug mode on so that you can diagnose problems?",
		choices => array("yes", "no"),
		value => "no",
	),
	"expectedExecStatus" => array(
		desc => "(Advanced) Status code returned by a successful exec() call." .
			"  You probably won't need to change this value",
		value => detect_exec_status()
	),
	"sessionVar" => array(
		desc => "(Advanced) The session variable used to " .
			"contain all Gallery session information. " .
			"Change this if there is a conflict between " .
			"Gallery and another PHP app on your website.",
		value => "gallery_session",
	),
);

$defaults = array(
	"bordercolor" => array(
 		"desc" => "Color of image borders",
 		"value" => "black"
	),
	"border" => array(
		"desc" => "Size of image borders",
		"value" => 1,
		"choices" => array("off", 1, 2, 3)
	),
	"font" => array(
		"desc" => "Font face",
		"value" => "arial"
	),
	"cols" => array(
		"desc" => "Number of columns",
		"value" => 3
	),
	"rows" => array(
		"desc" => "Number of rows",
		"value" => 3
	),
	"thumb_size" => array(
		"desc" => "The target size (in pixels) of thumbnails " .
			"shown in the albums",
		"value" => 150 
	),
	"resize_size" => array(
		"desc" => "The size images will be resized to this size ".
			"upon upload. ",
		"value" => 600,
		"choices" => array("off", 400, 500, 600, 700, 800)
	),
	"fit_to_window" => array(
		"desc" => "Auto fit-to-window for images without a resized copy",
		"value" => "no",
		"choices" => array("yes", "no")
	),
	"use_fullOnly" => array(
		"desc" => "Offer visitors ability to specify preference for full-size or resized images",
		"value" => "no",
		"choices" => array("yes", "no")
	),
	"print_photos" => array(
		"desc" => "Which photo printing service do you want to let visitors use?",
		"value" => "shutterfly",
		"choices" => array("none", "shutterfly")
	),
	"returnto" => array(
		"desc" => "Display the 'Return to Album List' link",
		"value" => "yes",
		"choices" => array("yes", "no")
	),
	"showOwners" => array(
		desc => "Show album owner's name on the gallery page",
		choices => array("yes", "no"),
		value => "no",
	),
	"albumsPerPage" => array(
		desc => "How many albums should be displayed per Gallery page?",
		value => "5",
	),
	"showSearchEngine" => array(
		desc => "Display search engine on main Gallery page?",
		choices => array("yes", "no"),
		value => "yes",
	),
	"useOriginalFileNames" => array(
                desc => "Use original files names for gallery storage?",
                choices => array("yes", "no"),
                value => "no",
	),
	"display_clicksDefault" => array(
		desc => "Display click counts within Albums?",
		choices => array("yes", "no"),
		value => "yes",
	),
	"photo_commentsDisplay" => array(
		desc => "Allow public photo commenting?",
		choices => array("yes", "no"),
		value => "yes",
	)
);
?>

<?
$checklist = array(
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
		"why" => "Enter the following into your web server's httpd.conf " .
			 "file: <br><pre>" .
			 "&lt;Directory " .
			 dirname(dirname(getenv("SCRIPT_FILENAME"))) .
			 "&gt;\n\tAllowOverride Options FileInfo\n" .
			 "&lt;/Directory&gt;",
		"optional" => 1,
	),
	"php4" => array(
		"desc" => "Check to see if you are running PHP 4.  Note that Gallery does " .
			"not function optimally with either 4.0.3 or 4.0.3pl1",
	     	"func" => "check_php",
		"why" => "You're running version " . phpversion() .
			". You need to upgrade to PHP ${MIN_PHP_MAJOR_VERSION}"
	),
	"magic_quotes" => array(
		"desc" => "Check to see if <a href=" .
			"http://www.php.net/manual/en/configuration.php#ini.magic-quotes-gpc " .
			" target=_top>magic quotes</a> are enabled on your PHP server.  If " .
			"they're on, you may notice strange behaviour if you put ' (single-quote),".
			" \" (double quote) or \\ (backslash) characters in titles or " .
			"captions in the gallery.",
	     	"func" => "check_magic_quotes",
		"why" => "Magic quotes are on.  Your webserver owner can turn them off" .
			 "by putting: <pre>\n\t" .
			 "magic_quotes_gpc = Off" .
			 "</pre> in your php.ini file",
	),
	"mod_rewrite" => array(
		"desc" => "Check to see if your webserver is compiled with " .
			"<b>mod_rewrite</b>.  Mod_rewrite allows the Gallery to use ".
			"shorter URLs that are easier to read and look nicer when " .
			"you mail them to your web-challenged relatives. " .
			"It's not an essential feature.",
	     	"func" => "check_mod_rewrite",
		"why" => "Either <b>mod_rewrite</b> is not installed or your " .
			"<b>.htaccess</b> file is not enabled (see above).  Either " .
			"way, we'll have to use longer URLs in the Gallery.  If you " .
			"want to turn it on I'd suggest that you make sure that your " .
			"<b>.htaccess</b> file works and then if it still doesn't work " .
			" you may need to reconfigure Apache with the <ul>" .
			"<b>--enable-module=rewrite</b> </ul> flag and then " .
			"rebuild and reinstall Apache.",
		"optional" => 1,
	), 
	"NetPBM" => array(
		"optional" => 1,
		"desc" => "Check to see if NetPBM is installed so that " .
			"Gallery can manipulate images (rotate, resize, make " .
			"thumbnails, etc).  NetPBM is way better than GD for some " .
			"operations.",
		"func" => "check_netpbm",
		"why" => "I can't find NetPBM.  If it's installed, it's not in " .
			  "the path of the webserver user.  If it's not installed, " .
			  "you'll need to <a href=http://sourceforge.net/projects/netpbm>install it</a>." .
			  "Gallery detects NetPBM by looking for the <i>anytopnm</i> script." .
			  "<p>" .
			  "<b> If it's installed and Gallery isn't finding it that's OK -- " .
			  "that just means you'll have to specify the path to NetPBM by hand " .
			  " on the following page </b>",
	),
);

$features = array(
	"zip" => array("zipinfo", "unzip"),
	"rewrite" => array("GALLERY_REWRITE"),
);

$constants = array(
	"galleryTitle" => array(
		"desc" => "The Gallery Title",
		"value" => "Gallery"
	),
	"editPassword" => array(
		"desc" => "The password for the <i>admin</i> account",
		"type" => "password",
		"dont-write" => 1,
	),
	"tmpDir" => array(
		"desc" => "The location of a temp directory",
		"value" => "/var/tmp"
	),
	"pnmDir" => array(
		"desc" => "The location of your NetPBM package.  If you don't " .
			  "have NetPBM, Gallery won't work at all.",
		"value" => locateDir("anytopnm")
	),
	"anytopnm" => array(
		"desc" => "anytopnm wrapper",
		"type" => "hidden",
		"value" => "",
		"eval" => 'makePathWrapper($pnmDir, "anytopnm")'
	),
	"highlight_size" => array(
		"desc" => "The target size (in pixels) of thumbnails " .
			  "shown in the gallery page (highlight images)",
 		"value" => 200
	),
	"zipinfo" => array(
		"desc" => "The path to the 'zipinfo' binary",
		"value" => locateFile("zipinfo"),
		"optional" => 1
	),
	"unzip" => array(
		"desc" => "The path to the 'unzip' binary",
		"value" => locateFile("unzip"),
		"optional" => 1
	),
	"movieThumbnail" => array(
		"desc" => "The path to the thumbnail image to show for movies",
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
			"require-write-dir" => 1
	),
	"userDir" => array(
		"desc" => "Location of user information",
		"type" => "hidden",
		"value" => "",
		"eval" => '"$albumDir/.users"',
	),
	"photoAlbumURL" => array(
		"desc" => "The full URL to the Gallery web app",
		"value" => "http://" . getenv("SERVER_NAME") . $GALLERY_URL
	),
	"albumDirURL" => array(
		"desc" => "The relative path to the album directory (specified above) " .
			"on your web server",
		"value" => "/albums"
	)
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
	"page" => array(
		"desc" => "Starting page",
		"value" => 1
	),
	"returnto" => array(
		"desc" => "Display the 'Return to Album List' link",
		"value" => "yes",
		"choices" => array("yes", "no")
	)
);
?>

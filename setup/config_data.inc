<?
$checklist = array(
	"htaccess" => array(
		"desc" => "Allow the Gallery .htaccess file to set " .
			"application specific configuration options for the " .
			"Gallery application.",
	     	"func" => "check_htaccess",
		"why" => "You may need to enter the following into " .
			"your web server's httpd.conf file: <br><pre>" .
			"&lt;Directory $GALLERY_URL&gt;\n\tAllowOverride Options\n" .
			"&lt;/Directory&gt;"
	),
	"php4" => array(
		"desc" => "Check to see if you are using the right " .
			"version of PHP.",
	     	"func" => "check_php",
		"why" => "You're running version " . phpversion() .
			". You need to upgrade to PHP ${MIN_PHP_MAJOR_VERSION}"
	),
	"mod_rewrite" => array(
		"desc" => "Check to see if your webserver is compiled with " .
			"mod_rewrite.  Without mod_rewrite Gallery won't be " .
			"able to let you use pretty URLs to go directly to " .
			"an album or a photo.",
	     	"func" => "check_mod_rewrite",
		"why" => "You need to reconfigure Apache with the <ul>" .
			"<b>--enable-module=rewrite</b> </ul> flag and then " .
			"reinstall it."
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
	"zip_support" => array("zipinfo", "unzip")
);

$constants = array(
	"editPassword" => array(
		"desc" => "The edit password",
		"type" => "password"
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
		"type" => "hidden",
		"override" => 1,
		"value" => makePathWrapper("anytopnm")
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
	"mv" => array(
		"desc" => "The path to your 'mv' binary",
		"value" => locateFile("mv"),
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
	"edit" => array(
		"desc" => "Start off in edit mode",
		"value" => "no",
		"choices" => array("yes", "no")
	),
	"returnto" => array(
		"desc" => "Display the 'Return to Album List' link",
		"value" => "yes",
		"choices" => array("yes", "no")
	)
);
?>

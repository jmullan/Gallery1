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
	"GD" => array(
		"desc" => "Check to see if PHP was compiled with " .
			"the GD libraries so that Gallery can manipulate " .
			"images (rotate, resize, make thumbnails, etc.)",
	     	"func" => "check_gd",
		"why" => "You need to reconfigure PHP with the <ul>" .
			"<b>--with-gd=<i>dir</i></b> </ul> flag and then " .
			"reinstall it.  Note that you have to specify the right " .
			"<i>directory</i> above.  You can get GD from: " .
			" <a href=ftp://ftp.boutell.com/pub/boutell/gd/> " .
			"ftp://ftp.boutell.com/pub/boutell/gd/</a>"
	), 
	"jpeg" => array(
		"desc" => "Check to see if PHP was compiled with " .
			"the JPEG libraries so that Gallery can load and " .
			"save JPEG files.",
	     	"func" => "check_jpeg",
		"why" => "You need to reconfigure PHP with the <ul>" .
			"<b>--with-jpeg-directory=<i>dir</i></b> </ul> flag and then " .
			"reinstall it.  Note that you have to specify the right " .
			"<i>directory</i> above.  You can get libjpeg from: " .
			" <a href=ftp://ftp.uu.net/graphics/jpeg/> " .
			"ftp://ftp.uu.net/graphics/jpeg/</a>"
	), 
);

$features = array(
	"zip_support" => array("zipinfo", "unzip"),
	"gif_support" => array("pnmDir")
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
			"have NetPBM, you won't be able to support GIF images.",
		"value" => locateDir("anytopnm")
	),
	"highlight_size" => array(
		"desc" => "The target size (in pixels) of thumbnails " .
			"shown in the gallery page (highlight images)",
		"value" => 200
	),
	"zipinfo" => array(
		"desc" => "The path to the 'zipinfo' binary",
		"value" => locateFile("zipinfo")
	),
	"unzip" => array(
		"desc" => "The path to the 'unzip' binary",
		"value" => locateFile("unzip")
	),
	"movieThumbnail" => array(
		"desc" => "The thumbnail image URL to show for movies",
		"value" => $GALLERY_URL . "/images/movie.thumb.jpg"
	),
	"albumDir" => array(
		"desc" => "The full path on physical disk to the directory " .
			"where your photos will be stored.  This directory " .
			"should start off empty -- Gallery will fill it. " .
			"Note that this " .
			"directory needs to have write access by the user who " .
			"is running the web server (in your case this user is <b>" .
			$webserver_user["name"] . "</b>)",
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
	),
);

$defaults = array(
	"bordercolor" => array(
 		"desc" => "Color of borders",
 		"value" => "black"
	),
	"border" => array(
		"desc" => "Size of borders",
		"value" => 1
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
	"imageborders" => array(
		"desc" => "Show image borders",
		"value" => "no",
		"choices" => array("yes", "no")
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

<?
$features = array(
			"zip_support" => 
				array("zipinfo", "unzip")
		);

$constants = array(
			"editPassword" => 
				array("desc" => "The edit password",
					"type" => "password"),
			"tmpDir" => 
				array("desc" => "The location of a temp directory",
					"value" => "/var/tmp"),
			"pnmDir" => 
				array("desc" => "The location of your NetPBM package",
					"value" => locateDir("anytopnm")),
			"thumb_size" =>
				array("desc" => "The target size (in pixels) of thumbnails",
					"value" => 200),
			"zipinfo" => 
				array("desc" => "The path to the 'zipinfo' binary",
					"value" => locateFile("zipinfo")),
			"unzip" => 
				array("desc" => "The path to the 'unzip' binary",
					"value" => locateFile("unzip")),
			"movieThumbnail" =>
				array("desc" => "The thumbnail image URL to show for movies",
					"value" => dirname(getenv("REQUEST_URI")) . 
						"/images/movie.thumb.jpg"),
			"albumDir" => 
				array("desc" => "The full path on physical disk to the directory " .
						"where your photos will be stored.  This directory " .
						"should start off empty -- Gallery will fill it. " .
						"Note that this " .
						"directory needs to have write access by the user who " .
						"is running the web server (in your case this user is <b>" .
						$webserver_user["name"] . "</b>)",
					"value" => getenv("DOCUMENT_ROOT") . "/albums",
					"require-write-dir" => 1),
			"photoAlbumURL" => 
				array("desc" => "The full URL to the Gallery web app",
					"value" => "http://" . getenv("SERVER_NAME") . 
						dirname(dirname(getenv("SCRIPT_NAME")))),
			"albumDirURL" => 
				array("desc" => "The relative path to the album directory (specified above) " .
						"on your web server",
					"value" => "/albums"),
		);

$defaults = array(
			"bordercolor" => 
				array("desc" => "Color of borders",
				      "value" => "black"),
			"border" => 
				array("desc" => "Size of borders",
					"value" => 1),
			"font" => 
				array("desc" => "Font face",
					"value" => "arial"),
			"cols" => 
				array("desc" => "Number of columns",
					"value" => 3),
			"rows" => 
				array("desc" => "Number of rows",
					"value" => 3),
			"page" =>
				array("desc" => "Starting page",
					"value" => 1),
			"edit" =>
				array("desc" => "Start off in edit mode",
					"value" => "no",
					"choices" => array("yes", "no")),
			"returnto" => 
				array("desc" => "Display the 'Return to Album List' link",
					"value" => "yes",
					"choices" => array("yes", "no"))
		);
?>

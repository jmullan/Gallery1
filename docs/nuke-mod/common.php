<?php
require ('include/common-doc.php');

if (!eregi("modules.php", $PHP_SELF)) {
	die ("You can't access this file directly");
}

$noteadmin = pnSecAuthAction (0, 'GalleryDocs::', '::', ACCESS_MODERATE);

function getTableName($table) {
    return 'gallery_' . $table;
}

function throwError ($error) {
	global $olddir;

	print '<b>Error</b>: '.$error;

	chdir ($olddir);
	
	CloseTable();
	require ('footer.php');
	
	exit;	
}

function interceptHeader () {
                $header = "";
                if ($fd = fopen("header.php", "r")) {
                        while (!feof($fd)) {
                                $line = fgets($fd, 1024);
                                $line = str_replace('<?php', '', $line);
                                $line = str_replace('?>', '', $line);
                                $header .= $line;
                                if (strstr($line, "<head")) {
					$header .= 'echo "<link rel=\"stylesheet\" href=\"modules/GalleryDocs/galleryweb/html.css\">\n";';
	                        }
			}
                }
		
		return $header;
}

function cleanNavigation () {
	global $navigation;

	// This is because XSLT creates some weird nav links because of my mods
	// so we need to clean it up

	foreach (array_keys ($navigation) as $key) {
		parse_str ($navigation[$key]);
		
		$navigation[$key] = $page;
	}
}
?>

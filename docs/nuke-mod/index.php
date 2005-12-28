<?
$included = 1;

require ('common.php');

eval (interceptHeader());
OpenTable();

$action =& $_REQUEST['action'];

$olddir = getcwd();
chdir ('modules/GalleryDocs');

if (!is_dir ('galleryweb')) {
	throwError ('Sanity Check Failed.  There is no <i>galleryweb</i> directory, please read the INSTALL instructions');
}

if (empty ($action)) {
	if (empty ($_GET['page'])) {
		$_GET['page'] = 'index.php';
	}
	
	$file =& $_GET['page'];

	$file = str_replace (array ('/', '\\', '..'), '', $file);

	if (!file_exists ('galleryweb/'.$file)) {
		throwError ('File not Found or Access Denied');
	}
	
	$pathinfo = pathinfo ($file);
	if ($pathinfo['extension'] != 'php') {
		throwError ('File not Found or Access Denied'); //throw same error to be ambiguous
	}
	
	$header .= "echo '<link rel=\"stylesheet\" href=\"galleryweb/html.css\">';\n";

	include ('galleryweb/'.$file);
	doNotes();
} else {
	if (get_magic_quotes_gpc()) {
		if (!empty($_POST['note'])) {
	    		$_POST['note'] = stripslashes($_POST['note']);
		}
    	}
	
	switch ($action) {
		case 'add-note':
			include ('include/add-note.php');
			break;
		case 'manage-note':
			include ('include/manage-note.php');
			break;
	    
		default:
			throwError ('Invalid Action');
			break;
	}
}

chdir ($olddir);

CloseTable();
require ('footer.php');
?>

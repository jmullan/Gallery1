<?php
if (!isset ($included)) {
	die ('Must be included');
}

$modPage = 'modules.php?op=modload&name=GalleryDocs&file=index&action=manage-note';

if (!$noteadmin) {
	throwError ('You must be a note admin to do this');
}

$action =& $_REQUEST['do'];

switch ($action) {
	case 'edit':
		handle_edit();
		break;
	case 'edit-post':
		check_preview();
		break;		
	case 'delete':
		confirm_delete();
		break;
        case 'delete-confirm':
        	handle_delete();
                break;
	default:
		throwError ('Invalid Action');
		break;
}

function handle_edit () {
	global $modPage;

	$note = getNoteByID ($_GET['id']);
	
	if (!$note) {
		throwError ('No note with that ID');
	}
	
	printNotesForm ($modPage, $note['id'], $note['sect'], $note['user'], $note['note'], array ('do' => 'edit-post'));
}

function handle_edit_post () {
	if (editNote ($_POST['id'], $_POST['user'], $_POST['note'])) {
		print 'Note successfully edited.  ';
		print '<a href="modules.php?op=modload&name=GalleryDocs&file=index&page='.$_POST['sect'].'">Back to where you came from</a>';
	} else {
		throwError ('Could not edit note.  Probably means this note doesn\'t exist');
	}
}

function confirm_delete () {
?>
Are you <b>sure</b> you want to delete this note?
<a href="modules.php?op=modload&name=GalleryDocs&file=index&action=manage-note&do=delete-confirm&id=<?php echo $_GET['id'];?>">Yes</a>
<a href="javascript:history.go(-1)">No</a>
<?php
}

function handle_delete () {
	if ($arr = removeNote ($_GET['id'])) {
		print 'Note sucessfully deleted.  ';
		print '<a href="modules.php?op=modload&name=GalleryDocs&file=index&page='.$arr['sect'].'">Back to where you came from</a>';
	} else {
		throwError ('Could not remove note.  Probably means this note doesn\'t exist');
	}
}

function handle_preview () {
	$note = array ('sect' => false,
        	       'user' => &$_POST['user'],
                       'ts' => time(),
                       'note' => &$_POST['note']
                      );
                      
	displayNote ($note);
        printNotesForm ($modPage, $_POST['id'], $_POST['sect'], $_POST['user'], $_POST['note'], array ('do' => 'edit-post'));
}

function check_preview () {
	if ($_POST['submit'] == 'Edit Note!') {
        	handle_edit_post();
	} else {
        	handle_preview();
	}
}
?>

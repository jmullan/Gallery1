<?php
if (!isset ($included)) {
	die ('Must be included');
}

$username = isLoggedIn ();
if ($username === false) {
	print 'You must register and/or login to add a note';
	exit;
}

$modPage = 'modules.php?op=modload&name=GalleryDocs&file=index&action=add-note';

if (isset ($_POST['submit'])) {
	$errors = '';

	if (empty ($_POST['sect'])) {
		$errors .= 'The section field didn\'t get passed to this page.  Please try submitting your note again<br>';
	}
	
	if (empty ($_POST['note'])) {
		$errors .= 'You didn\'t fill out any data to go in the note!<br>';
	}
	
	if (strlen ($_POST['note']) < 32) {
		$errors .= 'Your note is too short<br>';
	}
	
	if (strlen ($_POST['note']) > 4096) {
		$errors .= 'Your note is too long<br>';
	}
	
	if ($errors || $_POST['submit'] == 'Preview') {
        	if ($errors) {
			print '<table bgcolor="#ff8080" width="100%" border="1" bordercolor="#ff0000">';
			print '<tr><td>';
			print '<b>Error Submitting Note: </b><br><i>';
			print $errors;
			print '</i></td></tr></table>';
		} 
		
		if ($_POST['submit'] == 'Preview') {
			$note = array ('sect' => false,
        			       'user' => $username,
		                       'ts' => time(),
		                       'note' => &$_POST['note']
                		      );			
			displayNote ($note);
		}
		
		printNotesForm ($modPage, false, $_POST['sect'], false, $_POST['note']);
	} else {
		addNote ($_POST['sect'], $username, $_POST['note']);
		
		print 'Your note has been successfully added to the documentation.  It will show up in the documentation in an hour.<br/><br/>';
		
		print '<a href="modules.php?op=modload&name=GalleryDocs&file=index&page='.$_POST['sect'].'">Back to where you came from</a>';
	}
} else {
	printNotesForm ($modPage, false, $_GET['sect']);
}
?>

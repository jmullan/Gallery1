<?php
if (!isset ($included)) {
	die ('Must be included');
}

require_once ('db.php');

function getNotes ($section) {
	$table = getTableName('doc_notes');
        $result = dbQuery ('SELECT * FROM ' . $table . ' WHERE (sect="??" AND (('.time().' - ts) > 3600))', array ($section));
        
        $arr = array ();
        while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
                array_push ($arr, $row);
        }
        
        return $arr;
}

function printNotesHeader () {
        global $data, $navigation;
	
	$loggedin = isLoggedIn ();
?>
<table bgcolor="#dadada" border="0" bordercolor="#cccccc" width="100%" cellspacing="4" cellspacing="0">
 <tr>
  <td colspan="2">
  <table bgcolor="#cccccc" width="100%">
   <tr>
    <td width="50%">
     User Contributed Notes
    </td>
    <td width="50%" align="right">
    <?php
     if ($loggedin !== false) {
    ?>
     <a href="modules.php?op=modload&amp;name=GalleryDocs&amp;file=index&amp;action=add-note&amp;sect=<?php echo $navigation['this'];?>" style="color: #000000">
     [add a note]
     </a>
    <?
     } else {
      print '(please login or register to add a note)';
     }
    ?>
     </a>
    </td>
   </tr>
  </table>
  </td>
 </tr>
 <tr>
  <td colspan="2">
   <b>
    <?php echo ($data['parent_title'] ? $data['parent_title'].' :: ' : '').$data['title'];?>
   </b>
  </td>
 </tr>
<?php
}

function printNotesFooter () {
?>
</table>
<?php
}

function printNotes () {
	global $navigation;
	
	$notes = getNotes ($navigation['this']);
	
	foreach ($notes as $note) {
        	displayNote ($note);
	}
}

function displayNote (&$note) {
	global $navigation, $noteadmin;
?>
<tr>
 <td colspan="2" width="100%">
 <table bgcolor="#bbbbbb" width="100%">
  <tr>
   <td>
     <b>
      <?php echo spamProtectEmail ($note['user']);?>
     </b>
     <br/>
      <?php echo gmdate ('M d, Y H:i:s A \G\M\T', $note['ts']);?>
   </td>
   <td align="right" valign="top">
<?php
if ($noteadmin && $note['sect'] !== false) {
?>
   <small>
    <a href="modules.php?op=modload&name=GalleryDocs&file=index&action=manage-note&do=edit&id=<?php echo $note['id'];?>">[Edit]</a> |
    <a href="modules.php?op=modload&name=GalleryDocs&file=index&action=manage-note&do=delete&id=<?php echo $note['id'];?>">[Delete]</a>
   </small>
<?php
}
?>
   </td>
  </tr>
 </table>
 </td>
</tr>
<tr>
 <td colspan="2">
  <code>
   <?php echo nl2br ($note['note']);?>
  </code>
 </td>
</tr>
<!-- TODO: Admin stuff -->
<?php
}

function addNote ($sect, $user, $note, $status = '') {
	$note = htmlentities ($note); //no html allowed

	$table = getTableName('doc_notes');
	dbQuery ('INSERT into ' . $table . ' SET sect="??", user="??", note="??", status="??", ts="??"', array ($sect, $user, $note, $status, time()));
	
	$id = mysql_insert_id();
	
	$options = 'Manual Page   http://gallery.menalto.com/modules.php?op=modload&name=GalleryDocs&file=index&page='.$sect."\n".
		   'Edit Note	  http://gallery.menalto.com/modules.php?op=modload&name=GalleryDocs&file=index&action=manage-note&do=edit&id='.$id."\n".
		   'Delete Note   http://gallery.menalto.com/modules.php?op=modload&name=GalleryDocs&file=index&action=manage-note&do=delete&id='.$id;
		   
	mail ('gallery-docs-notes@lists.sf.net', 'Note '.$id.' added to '.$sect, $note."\n\n---------\n".$options, 'From: notes@gallery.menalto.com', '-fnotes@gallery.menalto.com');
}

function removeNote ($id) {
	$table = getTableName('doc_notes');
	$note = getNoteByID ($id);
	
	if (!$note) {
		return false;
	}
		
	dbQuery ('DELETE from ' . $table . ' WHERE id="??"', array ($id));

	mail ('gallery-docs-notes@list.sf.net', 'Note '.$note['id'].' deleted from '.$note['sect'].' by '.pnUserGetVar('uname'), 'Note Submitter: '.$note['user']."\n\n".$note['note'], 'From: notes@gallery.menalto.com', '-fnotes@gallery.menalto.com');
	
	return $note;
}

function editNote ($id, $user, $note, $status = '') {
	$note = htmlentities ($note); //no html again

	$table = getTableName('doc_notes');
	$note_orig = getNoteByID ($id);
	
	if (!$note_orig) {
		return false;
	}
	
	dbQuery ('UPDATE ' . $table . ' SET user="??", note="??", status="??" WHERE id="??"', array ($user, $note, $status, $id));
	
	mail ('gallery-docs-notes@lists.sf.net', 'Note '.$id.' from '.$note_orig['sect'].' modified by '.pnUserGetVar('uname'), $note."\n\n----was----\n\n".$note_orig['note'], 'From: notes@gallery.menalto.com', '-fnotes@gallery.menalto.com');
	
	return (mysql_affected_rows () ? true : false);	
}

function getNoteByID ($id) {
	$table = getTableName('doc_notes');
	$result = dbQuery ('SELECT * from ' . $table . ' WHERE id="??"', array ($id));
	
	return ( (mysql_num_rows ($result)) ? (mysql_fetch_array ($result, MYSQL_ASSOC)) : false);
}	
   
function doNotes () {
        printNotesHeader();
	printNotes();
	printNotesFooter();
}

function printNotesForm ($action, $id = false, $sect = false, $user = false, $note = false, $extraFields = false) {
	if (!$id) {
?>
<b>Note Submitting Guidelines:</b>
<ul>
 <li>
  <b>Do <i>not</i> submit support questions here.  Please use the
  <a href="http://gallery.sf.net/forums.php">forums</a> for support questions.  Your note will
  be deleted if you ask a support question.</b>
 </li>
 <li>
  All notes submitted here become the property of the Gallery Core Team.  Basically, this means
  we have the right to include them into the documentation proper if we find your note especially
  informative or useful.
 </li>
 <li>
  HTML is <i>not</i> allowed in the notes.  If you submit HTML, your post will contain the HTML
  literally.  Line breaks will be converted to &lt;br&gt;'s, so there is no need to use HTML &lt;br&gt;
  tags.
 </li>
 <li>
  Thank you for submitting your note!  We appreciate it!
 </li>
</ul>
<?php
	}
?>

<form method="post" action="<?php echo $action;?>">
<?php
if (is_array ($extraFields)) {
	foreach ($extraFields as $key => $value) {
		print "<input type=\"hidden\" name=\"$key\" value=\"$value\">";
	}
}
?>

<input type="hidden" name="id" value="<?php echo $id;?>">
<input type="hidden" name="sect" value="<?php echo $sect;?>">
<input type="hidden" name="action" value="<?php echo ($id ? 'manage' : 'add');?>-note">

<table bgcolor="#cfcfcf" width="100%">
 <tr>
  <th>
   Username
  </th>
  <td>
   <b><?php echo isLoggedIn(); ?></b>
  </td>
 </tr>
 <tr>
  <th>
   Note
  </th>
  <td>
   <textarea name="note" rows="15" cols="60"><?php echo $note;?></textarea>
  </td>
 </tr>
 <tr>
  <td colspan="2">
   <input type="submit" name="submit" value="Preview">
   <input type="submit" name="submit" value="<?php echo ($id ? "Edit" : "Add");?> Note!">
  </td>
 </tr>
</table>
<?php
}

function spamProtectEmail ($email) {
	$email = str_replace ('@', ' [at] ', $email);
	$email = str_replace ('.', ' [dot] ', $email);
	
	return $email;
}

function isLoggedIn () {
	$user = pnUserGetVar ('uname');
	
	if (empty ($user)) {
		return false;
	} else {
		return $user;
	}
}
?>

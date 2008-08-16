<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */

require_once(dirname(__FILE__) . '/init.php');

list($uname, $password, $langid, $lcid, $cmd, $set_albumName, $setCaption, $newAlbumTitle, $createNewAlbum, $album ) = getRequestVar(array('uname', 'password', 'langid', 'lcid', 'cmd', 'set_albumName', 'setCaption', 'newAlbumTitle', 'createNewAlbum', 'album'));

if (isset($_SERVER["HTTPS"] ) && stristr($_SERVER["HTTPS"], "on")) {
	$proto = "https";
}
else {
	$proto = "http";
}

if(empty($cmd)){

	header("Cache-control: private");
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: filename=install_registry.reg");

	$lines[] = 'Windows Registry Editor Version 5.00';
	$lines[] = '';
	$lines[] = '[HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\Explorer\PublishingWizard\PublishingWizard\Providers\\' . $gallery->app->galleryTitle . ']';
	$lines[] = '"displayname"="' . $gallery->app->galleryTitle . '"';
	$lines[] = '"description"="' . sprintf(gTranslate('core', "Publish Your Photos and Movies to %s."),  $gallery->app->galleryTitle) . '"';
	$lines[] = '"href"="' . makeGalleryUrl("publish_xp.php", array("cmd" => "publish")) . '"';
	$lines[] = '"icon"="' . $proto . '://' . $_SERVER['SERVER_NAME'] . '/favicon.ico"';
	print join("\r\n", $lines);
	print "\r\n";
	exit;
}

//---------------------------------------------------------
//-- check version --

//---------------------------------------------------------
$WIZARD_BUTTONS="false,true,false";
$ONBACK_SCRIPT="";
$ONNEXT_SCRIPT="";
$SCRIPT_CMD="";

$messages = array();

//-- login --
if ($cmd == 'login') {

	if ($uname && $password) {
		$tmpUser = $gallery->userDB->getUserByUsername($uname);
		if ($tmpUser && $tmpUser->isCorrectPassword($password)) {
			$gallery->session->username = $uname;
			$returnval = "SUCCESS";
			$WIZARD_BUTTONS="true,true,false";
			$cmd = 'fetch-albums';
			// We are going to do stuff so, the user variable has to get in.
			// I think this actually does the "login'
			$gallery->user = $gallery->userDB->getUserByUsername($gallery->session->username);
			$ONBACK_SCRIPT="history.go(-1);";
		}
		else {
			$messages[] = array(
				'type' => 'error',
				'text' => gTranslate('core', "Username and Password are not correct.")
			);
			$returnval = "Login Incorrect";
			$WIZARD_BUTTONS="false,true,false";
		}
	}
	else {
		$messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Please enter username and password.")
		);

		$returnval = "Login Incorrect";
		$WIZARD_BUTTONS="false,true,false";
	}

}

if ($cmd == "publish" || (isset($returnval) && $returnval == "Login Incorrect") ) {
	echo printPopupStart(sprintf(gTranslate('core', "Login to %s"), $gallery->app->galleryTitle));
	echo infoBox($messages);

	echo  makeFormIntro('publish_xp.php', array('id' => 'login'));
?>
<table>
<?php
echo gInput('text', 'uname', gTranslate('core', "_Username"), true);

echo gInput('password', 'password', gTranslate('core', "_Password"), true);
?>
</table>

	<input type="hidden" name="lcid" value="<?php echo $lcid; ?>">
	<input type="hidden" name="langid" value="<?php echo $langid; ?>">
	<input type="hidden" name="cmd" value="login">
</form>

<?php
$ONNEXT_SCRIPT="login.submit();";
$SCRIPT_CMD="this.login.uname.focus();";
}

//---------------------------------------------------------
//-- fetch-albums --

if (!strcmp($cmd, "fetch-albums")) {
	echo printPopupStart(gTranslate('core', "Select Album"));
	echo gTranslate('core', "Select the Album to which to Publish");
?>
<form id="folder">
	<select id="album" name="set_albumName" size="10" width="40">
<?php
$albumDB = new AlbumDB(FALSE);
$mynumalbums = $albumDB->numAlbums($gallery->user);

// display all albums that the user can move album to
for ($i=1; $i<=$mynumalbums; $i++) {
	$myAlbum=$albumDB->getAlbum($gallery->user, $i);
	$albumName = $myAlbum->fields['name'];
	$albumTitle = $myAlbum->fields['title'];

	if ($gallery->user->canAddToAlbum($myAlbum)) {
		echo "\t<option ";
		if (isset($album) && $albumName == $album) echo "selected ";
		echo "value=\"$albumName\">$albumTitle</option>\n";
	}
	if (! isset($album)) $album="";
	appendNestedAlbums(0, "canAddToAlbum", $albumName, $album);
}
?>
	</select><br>

	<input id="setCaption" type="checkbox" name="setCaption" checked value="1"><?php echo gTranslate('core', "Use filenames as captions") ?>

	<br><br>
	<?php echo gButton('create', gTranslate('core', "Create New Album"), "folder.cmd.value='new-album';folder.submit()"); ?>

	<input type="hidden" name="cmd" value="select-album">
</form>
<?php
$ONNEXT_SCRIPT="folder.submit();";
$ONBACK_SCRIPT="window.location.href = \"publish_xp.php?cmd=publish\";";
$WIZARD_BUTTONS="true,true,true";
}

function appendNestedAlbums($level, $permission, $albumName, $albumCompare = "") {
	global $gallery;

	$myAlbum = new Album();
	$myAlbum->load($albumName);

	$numPhotos = $myAlbum->numPhotos(1);

	for ($i=1; $i <= $numPhotos; $i++) {
		if ($myAlbum->isAlbum($i)) {
			$myName = $myAlbum->getAlbumName($i);
			$nestedAlbum = new Album();
			$nestedAlbum->load($myName);
			if ($gallery->user->$permission($nestedAlbum)) {
				$nextTitle = str_repeat("-- ", $level+1);
				$nextTitle .= $nestedAlbum->fields['title'];
				$nextTitle = $nextTitle;
				$nextName = $nestedAlbum->fields['name'];
				echo "\t<option ";
				if ($nextName == $albumCompare) {
					echo "selected ";
				}
				echo "value=\"$nextName\">$nextTitle</option>\n";
				appendNestedAlbums($level + 1, $permission, $myName, $albumCompare);
			}
		}
	}
}

//---------------------------------------------------------
//-- select-album --

if ($cmd == 'select-album') {
	echo printPopupStart(gTranslate('core', "Select Album"));
	// Do we have a logged in user?
	if (!$gallery->user->isLoggedIn()) {
		$messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Not Logged In!")
		);
	}

	if (empty($gallery->album) || empty($set_albumName)) {
		$messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "No album specified!")
		);
	}
	elseif (!$gallery->user->canAddToAlbum($gallery->album) && $set_albumName) {
		$messages[] = array(
			'type' => 'error',
			'text' => sprintf(gTranslate('core', "This user cannot add photos in %s."),
		$gallery->album->fields[title])
		);
	}

	if (!empty($messages)) {
		$messages[] = array(
			'type' => 'warning',
			'text' => gTranslate('core', "Press the 'Back' button and try again!")
		);

		echo infoBox($messages);
		$ONBACK_SCRIPT="window.location.href = \"publish_xp.php?cmd=fetch-albums\";";
		$WIZARD_BUTTONS="true,false,true";
	}
	else {
?>
		<form id="folder">
		<input type="hidden" name="album" value="<?php echo $gallery->album->fields['name'] ?>">
		<input type="hidden" name="setCaption" value="<?php echo $setCaption ?>">
		</form>
<?php
$SCRIPT_CMD="DOIT();";
	}
}

//---------------------------------------------------------
//-- new-album --

if (!strcmp($cmd, "new-album")) {
	echo printPopupStart(sprintf(gTranslate('core', "Create new album"), $gallery->app->galleryTitle));

	// Do we have a logged in user?
	if (!$gallery->user->isLoggedIn()) {
		$messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Not Logged In!")
		);
		// Permission checks
		// can the user create albums in the ROOT level
	}
	elseif (isset($createNewAlbum) && ($set_albumName == '_xp_wiz_root') && !($gallery->user->canCreateAlbums()) ) {
		$messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "User cannot create ROOT level album.")
		);
		// can the user create nested albums in the specified album
	}
	elseif (isset($createNewAlbum) &&
			isset($set_albumName) &&
			$set_albumName != '_xp_wiz_root' &&
			!($gallery->user->canCreateSubAlbum($gallery->album)))
	{
		$messages[] = array(
			'type' => 'error',
			'text' => sprintf(gTranslate('core', "User cannot create nested album in %s."), $gallery->album->fields[title])
		);
	}
	elseif (isset($createNewAlbum) && empty($set_albumName)) {
		$messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "No Parent Album Specified!")
		);
	}
	elseif (isset($createNewAlbum)) {
		if ($set_albumName == '_xp_wiz_root') {
			$parentName = '';
		} elseif (isset($set_albumName)) {
			$parentName = $set_albumName;
		}

		if ($set_albumName) {
			$success = createNewAlbum($parentName);
		}

		if ($newAlbumTitle) {
			$newAlbumTitle = strip_tags($newAlbumTitle);
			$gallery->album->fields["title"] = $newAlbumTitle;
			$gallery->album->save();
		}
	}
	else {
		if (empty($newAlbumTitle)) $newAlbumTitle = "Untitled";
		?>
<form id="folder">
	<?php echo gTranslate('core', "Enter New Album Title") ?>:
	<input id="newAlbumTitle" type="text" name="newAlbumTitle" value="<?php echo $newAlbumTitle ?>" size="25">

	<br><br><?php echo gTranslate('core', "Select Parent Album") ?>:
	<br><br>
	<select id="album" name="set_albumName" size="10" width="40">
		<?php
		$albumDB = new AlbumDB(FALSE);
		$mynumalbums = $albumDB->numAlbums($gallery->user);
		if ($gallery->user->canCreateAlbums()) {
			echo "<option value=\"_xp_wiz_root\">" . gTranslate('core', "NEW TOP LEVEL ALBUM") ."</option>\n";
		}
		// display all albums that the user can move album to
		for ($i=1; $i<=$mynumalbums; $i++) {
			$myAlbum=$albumDB->getAlbum($gallery->user, $i);
			$albumName = $myAlbum->fields[name];
			$albumTitle = $myAlbum->fields[title];
			if ($gallery->user->canCreateSubAlbum($myAlbum)) {
				echo "\t<option value=\"$albumName\">$albumTitle</option>\n";
			}
			appendNestedAlbums(0, "canCreateSubAlbum", $albumName);
		}
		?>
	</select>

	<input type="hidden" name="cmd" value="new-album">
	<input type="hidden" name="createNewAlbum" value="1">
</form>

		<?php
		$SCRIPT_CMD = "this.folder.newAlbumTitle.focus();this.folder.newAlbumTitle.select();";
		$ONNEXT_SCRIPT="folder.submit();";
		$ONBACK_SCRIPT="window.location.href = \"publish_xp.php?cmd=fetch-albums\";";
		$WIZARD_BUTTONS="true,true,true";
	}

	if (!empty($messages)) {
		$messages[] = array(
			'type' => 'warning',
			'text' => gTranslate('core', "Press the 'Back' button and try again!")
		);
		echo infoBox($messages);
		echo "<form id=\"folder\">";
		echo "<input type=\"hidden\" name=\"cmd\" value=\"new-album\">\n";
		echo "<input type=\"hidden\" name=\"newAlbumTitle\" value=\"$newAlbumTitle\">\n";
		echo "</form>\n";
		$ONBACK_SCRIPT="folder.submit();";
		$WIZARD_BUTTONS="true,false,true";
	}
	else {
		echo "<form id=\"folder\">\n";
		$albumname = isset($gallery->album->fields) ? $gallery->album->fields['name'] : '';
		echo "<input type=\"hidden\" name=\"album\" value=\"$albumname\">\n";
		echo "<input type=\"hidden\" name=\"cmd\" value=\"fetch-albums\">\n";
		echo "</form>\n";

		if (isset($success)) {
			$SCRIPT_CMD = "folder.submit();";
		}
	}
}

//---------------------------------------------------------
//-- add-photo --

if (!strcmp($cmd, "add-item")) {

	// Hack check
	if (!$gallery->user->canAddToAlbum($gallery->album)) {
		$error = gTranslate('core', "User cannot add to album");
	}

	else if (empty($_FILES['userfile']['name'])) {
		$error = gTranslate('core', "No file specified");
	}

	else {
		$name = $_FILES['userfile']['name'];
		$file = $_FILES['userfile']['tmp_name'];
		$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $name);
		$tag = strtolower($tag);

		if (!empty($name)) {
			processNewImage($file, $tag, $name, "", $setCaption);
		}

		$gallery->album->save(array(i18n("Image added")));

		if (!empty($temp_files)) {
			/* Clean up the temporary url file */
			foreach ($temp_files as $tf => $junk) {
				fs_unlink($tf);
			}
		}
	}

	if (!empty($error)) {
		echo gallery_error($error);
	}
	else {
		echo "SUCCESS";
	}
}

function forceQuestionMark($url) {
	if (!strstr("?", $url)) {
		$url .= "?";
	}
	return $url;
}
?>

<script>
function DOIT() {
	var xml = window.external.Property("TransferManifest");
	var files = xml.selectNodes("transfermanifest/filelist/file");

	for (i = 0; i < files.length; i++) {
		var postTag = xml.createNode(1, "post", "");
		postTag.setAttribute("href", "<?php echo forceQuestionMark(makeGalleryUrl("publish_xp.php")) ?>&set_albumName=" + folder.album.value);
		postTag.setAttribute("name", "userfile");

		var dataTag = xml.createNode(1, "formdata", "");
		dataTag.setAttribute("name", "max_file_size");
		dataTag.text = "10000000";
		postTag.appendChild(dataTag);

		var dataTag = xml.createNode(1, "formdata", "");
		dataTag.setAttribute("name", "cmd");
		dataTag.text = "add-item";
		postTag.appendChild(dataTag);

		var dataTag = xml.createNode(1, "formdata", "");
		dataTag.setAttribute("name", "setCaption");
		dataTag.text = folder.setCaption.value;
		postTag.appendChild(dataTag);

		var dataTag = xml.createNode(1, "formdata", "");
		dataTag.setAttribute("name", "userfile_name");
		dataTag.text = files[i].getAttribute("destination");
		postTag.appendChild(dataTag);

		dataTag.setAttribute("name", "action");
		dataTag.text = "SAVE";
		postTag.appendChild(dataTag);

		files.item(i).appendChild(postTag);
	}
	var uploadTag = xml.createNode(1, "uploadinfo", "");
	var htmluiTag = xml.createNode(1, "htmlui", "");
	htmluiTag.text = "<?php echo forceQuestionMark(makeGalleryUrl("view_album.php")) ?>&set_albumName="+folder.album.value;
	uploadTag.appendChild(htmluiTag);

	xml.documentElement.appendChild(uploadTag);

	window.external.Property("TransferManifest")=xml;
	window.external.SetWizardButtons(true,true,true);
	window.external.FinalNext();
}

function OnBack() {
	<?php echo $ONBACK_SCRIPT; ?>
	window.external.SetWizardButtons(false,true,false);
}

function OnNext() {
	<?php echo $ONNEXT_SCRIPT; ?>
}

function window.onload() {
	window.external.SetHeaderText("<?php echo $gallery->app->galleryTitle ?> <?php echo gTranslate('core', "Photo Upload") ?>","<?php echo gTranslate('core', "Upload Photos to") ?> <?php echo $gallery->app->galleryTitle ?>");
	window.external.SetWizardButtons(<?php echo $WIZARD_BUTTONS; ?>);
}

<?php echo $SCRIPT_CMD; ?>

</script>
</body>
</html>

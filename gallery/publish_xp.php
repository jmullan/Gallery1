<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}

if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = '';
}
require($GALLERY_BASEDIR . "init.php");

if (stristr($HTTP_SERVER_VARS["HTTPS"], "on")) {
    $proto = "https";
} else {
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
  $lines[] = '"description"="Publish Your Photos and Movies to ' . $gallery->app->galleryTitle . '."';
  $lines[] = '"href"="' . makeGalleryUrl("publish_xp.php", array("cmd" => "publish")) . '"';
  $lines[] = '"icon"="' . $proto . '://' . $HTTP_SERVER_VARS['SERVER_NAME'] . '/favicon.ico"';
  print join("\r\n", $lines);
  print "\r\n";
  exit;
}
?>
<html>
  <head>
  <title>Login to <?php echo $gallery->app->galleryTitle?></title>
  <?php echo getStyleSheetLink() ?>
  </head>
<body>
<?php
//---------------------------------------------------------
//-- check version --

//---------------------------------------------------------
$WIZARD_BUTTONS="false,true,false";
$ONBACK_SCRIPT="";
$ONNEXT_SCRIPT="";
$SCRIPT_CMD="";

//-- login --
if (!strcmp($cmd, "login")) {

	if ($uname && $password) {
		$tmpUser = $gallery->userDB->getUserByUsername($uname);
		if ($tmpUser && $tmpUser->isCorrectPassword($password)) {
			$gallery->session->username = $uname;
			$returnval = "SUCCESS";
			$WIZARD_BUTTONS="true,true,false";
		        $cmd='fetch-albums';
                        // We are going to do stuff so, the user variable has to get in.
                        // I think this actually does the "login'
			$gallery->user = 
			   $gallery->userDB->getUserByUsername($gallery->session->username);
                        $ONBACK_SCRIPT="history.go(-1);";
		} else {
			echo "<span class='error'>Username and Password are not correct.</span>";
			$returnval = "Login Incorrect";
			$WIZARD_BUTTONS="false,true,false";
		}
	} else {
		echo "<span class='error'>Please Enter Username and Password</span>";
			$returnval = "Login Incorrect";
		$WIZARD_BUTTONS="false,true,false";
	}

}

if (!strcmp($cmd,"publish") || $returnval == "Login Incorrect") {?>
<center>
<span class="popuphead">Login to <?php echo $gallery->app->galleryTitle?></span>
<br>
<?php echo  makeFormIntro("publish_xp.php", array("id" => "login", "method" => "POST")); ?>
<table>
 <tr>
  <td>Username:</td><td><input type='TEXT' name='uname' value=''/></td>
 </tr>
 <tr>
  <td>Password:</td><td><input type='PASSWORD' name='password' value=''/></td>
 </tr>
</table>
<input type=hidden name='lcid' value='<?php echo $lcid; ?>'/>
<input type=hidden name='langid' value='<?php echo $langid; ?>'/>
<input type=hidden name='cmd' value='login'/>
<?php 
$ONNEXT_SCRIPT="login.submit();";
$SCRIPT_CMD="this.login.uname.focus();";
?>
</form>
</center>
<?php 
}

//---------------------------------------------------------
//-- fetch-albums --

if (!strcmp($cmd, "fetch-albums")) {
    echo "<center>"; ?>
<span class='popuphead'>Select the Album to Which to Publish</span>
<?php	
    echo "<form id='folder'>";
    echo "<table border=0>";
    echo "<tr><td align=center>";
    echo "<select id='album' name='set_albumName' size=10 width=40>";

    $albumDB = new AlbumDB(FALSE);
    $mynumalbums = $albumDB->numAlbums($gallery->user);

    // display all albums that the user can move album to
    for ($i=1; $i<=$mynumalbums; $i++) {
        $myAlbum=$albumDB->getAlbum($gallery->user, $i);
        $albumName = $myAlbum->fields[name];
        $albumTitle = $myAlbum->fields[title];
        if ($gallery->user->canAddToAlbum($myAlbum)) {
		echo "<option ";
		if ($albumName == $album) echo "selected ";
		echo "value='$albumName'>\t$albumTitle</option>\n";
        }
        appendNestedAlbums(0, "canAddToAlbum", $albumName, $albumString, $album);
    }

    echo "</select><br>\n";
    echo "</td></tr><tr><td align=center>\n";
    echo "<input id='setCaption' type=checkbox name=setCaption checked value=1>Use filenames as captions<br><br>\n";
    echo "<input type=button value='Create New Album' onClick=\"folder.cmd.value='new-album';folder.submit();\">\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "<input type=hidden name='cmd' value='select-album'>\n";
    echo "</form></center>\n";
    $ONNEXT_SCRIPT="folder.submit();"; 
    $ONBACK_SCRIPT="window.location.href = \"publish_xp.php?cmd=publish\";";
    $WIZARD_BUTTONS="true,true,true";
}

function appendNestedAlbums($level, $permission, $albumName, $albumString, $albumCompare) {
    global $gallery;
 
    $myAlbum = new Album();
    $myAlbum->load($albumName);
   
    $numPhotos = $myAlbum->numPhotos(1);

    for ($i=1; $i <= $numPhotos; $i++) {
        $myName = $myAlbum->isAlbumName($i);
        if ($myName) {
            $nestedAlbum = new Album();
            $nestedAlbum->load($myName);
            if ($gallery->user->$permission($nestedAlbum)) {
                $nextTitle = str_repeat("-- ", $level+1);
                $nextTitle .= $nestedAlbum->fields[title];
				$nextTitle = $nextTitle;
                $nextName = $nestedAlbum->fields[name];
				echo "<option ";
				if ($nextName == $albumCompare) echo "selected ";
				echo "value='$nextName'>\t$nextTitle</option>\n";
                appendNestedAlbums($level + 1, $permission, $myName, $albumString, $albumCompare);
            }
        }
    }
}

//---------------------------------------------------------
//-- select-album --

if (!strcmp($cmd, "select-album")) {

	// Do we have a logged in user?
	if (!$gallery->user->isLoggedIn()) {
		echo "Not Logged In!";
		exit;
	}

	if (!$gallery->album || !$set_albumName) {

	    $error = "No album specified!<br>\n";

	} elseif (!$gallery->user->canAddToAlbum($gallery->album) && $set_albumName) {

	    $error = "User cannot add photos in " . $gallery->album->fields[title] . ".<br>\n";

	}

	if ($error) {
		echo "<span class='error'>$error</span><br>";
		echo "Press the 'Back' button and try again!";
		$ONBACK_SCRIPT="window.location.href = \"publish_xp.php?cmd=fetch-albums\";";
    		$WIZARD_BUTTONS="true,false,true";
	} else {
		echo "<form id='folder'>\n";
		echo "<input type=hidden name=album value=" . $gallery->album->fields[name] . ">\n";
		echo "<input type=hidden name=setCaption value=$setCaption>\n";
		echo "</form>\n";

		$SCRIPT_CMD="DOIT();";
	}
}

//---------------------------------------------------------
//-- new-album --

if (!strcmp($cmd, "new-album")) {

        // Do we have a logged in user?
        if (!$gallery->user->isLoggedIn()) {
                echo "Not Logged In!";
                exit;
        }

        // Permission checks

	// can the user create albums in the ROOT level
        if ($createNewAlbum && ($set_albumName == '_xp_wiz_root') && !($gallery->user->canCreateAlbums()) ) {

            $error = "User cannot create ROOT level album.<br>\n";

	// can the user create nested albums in the specified album
        } elseif ($createNewAlbum && $set_albumName && $set_albumName != '_xp_wiz_root' && !($gallery->user->canCreateSubAlbum($gallery->album)) ) {

            $error = "User cannot create nested album in " . $gallery->album->fields[title] . ".<br>\n";

	} elseif ($createNewAlbum && !$set_albumName) {

		$error = "No Parent Album Specified!\n";

        } elseif ($createNewAlbum) {

		if ($set_albumName == '_xp_wiz_root') {
			$parentName = '';
		} elseif ($set_albumName) {
			$parentName = $set_albumName;
		}

		if ($set_albumName) {
			$success = createNewAlbum($parentName);
		}

                if ($newAlbumTitle) {
			$newAlbumTitle = removeTags($newAlbumTitle);
                        $gallery->album->fields["title"] = $newAlbumTitle;
                	$gallery->album->save();
                }

	} else {
		if (!$newAlbumTitle) $newAlbumTitle = "Untitled";
                echo "<center>";
                echo "<form id='folder'>";
                echo "<table border=0>";
                echo "<tr><td align=center>\n";
		echo "<span class='popuphead'>Create New Album</span>";
                echo "<br><br>Enter New Album Title:  ";
                echo "<input id='newAlbumTitle' type='text' name=newAlbumTitle value=\"$newAlbumTitle\" size=25><br>\n";
                echo "</td></tr>\n";
                echo "<tr><td align=center>\n";
                echo "<br>Select Parent Album:<br><br>\n";
                echo "<select id='album' name='set_albumName' size=10 width=40>";

                $albumDB = new AlbumDB(FALSE);
                $mynumalbums = $albumDB->numAlbums($gallery->user);
                if ($gallery->user->canCreateAlbums()) {
                        echo "<option value='_xp_wiz_root'>\tNEW TOP LEVEL ALBUM</option>\n";
                }
                // display all albums that the user can move album to
                for ($i=1; $i<=$mynumalbums; $i++) {
                        $myAlbum=$albumDB->getAlbum($gallery->user, $i);
                        $albumName = $myAlbum->fields[name];
                        $albumTitle = $myAlbum->fields[title];
                        if ($gallery->user->canCreateSubAlbum($myAlbum)) {
                                echo "<option value='$albumName'>\t$albumTitle</option>\n";
                        }
                        appendNestedAlbums(0, "canCreateSubAlbum", $albumName, $albumString);
                }

                echo "</select>\n";
                echo "</td></tr>\n";
                echo "</table>\n";
                echo "<input type=hidden name='cmd' value='new-album'/>\n";
                echo "<input type=hidden name='createNewAlbum' value='1'/>\n";
                echo "</form></center>\n";
		$SCRIPT_CMD = "this.folder.newAlbumTitle.focus();this.folder.newAlbumTitle.select();";
                $ONNEXT_SCRIPT="folder.submit();";
		$ONBACK_SCRIPT="window.location.href = \"publish_xp.php?cmd=fetch-albums\";";
                $WIZARD_BUTTONS="true,true,true";
	}

        if ($error) {
                echo "<span class='error'>$error</span><p>";
		echo "Press the 'Back' button and try again!";
		echo "<form id='folder'>";
		echo "<input type=hidden name='cmd' value='new-album'>\n";
		echo "<input type=hidden name='newAlbumTitle' value=\"$newAlbumTitle\">\n";
		echo "</form>\n";
                $ONBACK_SCRIPT="folder.submit();";
                $WIZARD_BUTTONS="true,false,true";
        } else {
                echo "<form id='folder'>\n";
                echo "<input type=hidden name=album value=" . $gallery->album->fields[name] . ">\n";
		echo "<input type=hidden name=cmd value='fetch-albums'>\n";
                echo "</form>\n";
		
		if ($success) {	
			$SCRIPT_CMD = "folder.submit();";
		}
        }
}

//---------------------------------------------------------
//-- add-photo --

if (!strcmp($cmd, "add-item")) {

	// Hack check
	if (!$gallery->user->canAddToAlbum($gallery->album)) {
	    $error = "User cannot add to album";
	}

	else if (!$userfile_name) {
    	$error = "No file specified";
	}

	else {
		$name = $userfile_name;
		$file = $userfile;
		$tag = ereg_replace(".*\.([^\.]*)$", "\\1", $name);
		$tag = strtolower($tag);

		if ($name) {
    			processNewImage($userfile, $tag, $userfile_name,"",$setCaption);
		}

		$gallery->album->save();

		if ($temp_files) {
    		/* Clean up the temporary url file */
    		foreach ($temp_files as $tf => $junk) {
        		fs_unlink($tf);
    		}
		}

	}

	if ($error) {
    	echo ("ERROR: $error");
	} else {
    	echo ("SUCCESS");
	}

}
?>
<div id="content"/>

</div>
<?php
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
postTag.setAttribute("href", "<?php echo forceQuestionMark(makeGalleryUrl("publish_xp.php"))?>&set_albumName=" + folder.album.value);
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
htmluiTag.text = "<?php echo forceQuestionMark(makeGalleryUrl("view_album.php"))?>&set_albumName="+folder.album.value;
uploadTag.appendChild(htmluiTag);

xml.documentElement.appendChild(uploadTag);

window.external.Property("TransferManifest")=xml;
window.external.SetWizardButtons(true,true,true);
content.innerHtml=xml;
window.external.FinalNext();
}

function OnBack() {
  <?php echo $ONBACK_SCRIPT; ?>
  window.external.SetWizardButtons(false,true,false);
}

function OnNext() {
  <?php echo $ONNEXT_SCRIPT; ?>
}

function OnCancel() {
  content.innerHtml+="<br>OnCancel";

}

function window.onload() {
   window.external.SetHeaderText("<?php echo $gallery->app->galleryTitle?> Photo Upload","Upload Photos to <?php echo $gallery->app->galleryTitle?>");
   window.external.SetWizardButtons(<?php echo $WIZARD_BUTTONS; ?>);
}

<?php echo $SCRIPT_CMD; ?>

</script>
</body>
</html>

<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
			echo ("Username and Password are not correct.");
			$returnval = "Login Incorrect";
			$WIZARD_BUTTONS="false,true,false";
		}
	} else {
		echo "Please Enter Username and Password";
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
<?php $ONNEXT_SCRIPT="login.submit();" ?>
</form>
</center>
<?php 
}

//---------------------------------------------------------
//-- fetch-albums --

if (!strcmp($cmd, "fetch-albums")) {
	echo "<center>"; ?>
<span class='popuphead'>Logged in to <?php echo $gallery->app->galleryTitle?></span>
<br>If you are not "<b><i><?php echo $gallery->session->username?></i></b>" please click <a href="<?php echo makeGalleryUrl("publish_xp.php", array("cmd" => "publish"))?>">here</a>.<br><br>
<?php	echo "<span class='admin'>";
        echo "Select the folder to which to publish, or <br>";
        echo "select a new folder location and enter the name.</span>";
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
				echo "<option value='$albumName'>\t$albumTitle</option>\n";
        }
        appendNestedAlbums(0, $albumName, $albumString);
    }

    echo "</select><br>\n";
    echo "</td><td>\n";
    echo "<input id='setCaption' type=checkbox name=setCaption checked value=1>Use filenames as caption<br>\n";
    echo "<input id='createNewFolder' type=checkbox name=createNewFolder value=1>Create new folder\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=2 align=center>\n";
    echo "New folder name:\n";
    //echo "</td><td>\n";
    echo "<input id='newFolderName' type='text' name=newFolderName value='' size=25><br>\n";
    echo "</td></tr></table>\n";
    echo "<input type=hidden name='cmd' value='check-error'/>\n";
    echo "</form></center>\n";
    #$ONNEXT_SCRIPT="DOIT();"; 
    $ONNEXT_SCRIPT="folder.submit();"; 
    $WIZARD_BUTTONS="true,true,true";
}

function appendNestedAlbums($level, $albumName, $albumString) {
    global $gallery;
 
    $myAlbum = new Album();
    $myAlbum->load($albumName);
   
    $numPhotos = $myAlbum->numPhotos(1);

    for ($i=1; $i <= $numPhotos; $i++) {
        $myName = $myAlbum->isAlbumName($i);
        if ($myName) {
            $nestedAlbum = new Album();
            $nestedAlbum->load($myName);
            if ($gallery->user->canAddToAlbum($nestedAlbum)) {
                $nextTitle = str_repeat("-- ", $level+1);
                $nextTitle .= $nestedAlbum->fields[title];
				$nextTitle = $nextTitle;
                $nextName = $nestedAlbum->fields[name];
				echo "<option value='$nextName'>\t$nextTitle</option>\n";
                appendNestedAlbums($level + 1, $myName, $albumString);
            }
        }
    }
}

//---------------------------------------------------------
//-- check-error --

if (!strcmp($cmd, "check-error")) {

	// Permissions checks
	if (!$gallery->album) {
	    $error = "No album specified!<br>\n";
	} elseif (!$gallery->user->canAddToAlbum($gallery->album) && $set_albumName) {
	    $error = "User cannot add photos in " . $gallery->album->fields[name] . ".<br>\n";
	} elseif ($createNewFolder && !$set_albumName && !($gallery->user->canCreateAlbums()) ) {
	    $error = "User cannot create ROOT level album.<br>\n";
	} elseif ($createNewFolder && $set_albumName && !($gallery->user->canCreateSubAlbum($gallery->album)) ) {
	    $error = "User cannot create nested album in " . $gallery->album->fields[name] . ".<br>\n";
	} elseif ($createNewFolder) {
		$parentName = $set_albumName;
		
		$albumDB = new AlbumDB(FALSE);
                $gallery->session->albumName = $albumDB->newAlbumName();
                $gallery->album = new Album();
                $gallery->album->fields["name"] = $gallery->session->albumName;
		if ($newFolderName) {
                	$gallery->album->fields["title"] = $newFolderName;
		}
                $gallery->album->setOwner($gallery->user->getUid());
                $gallery->album->save();

		/* if this is a nested album, set nested parameters */
		// la la la... code replication... la la la...  
		// this next section should be put in a subroutine in 
		// the main gallery code and called from here...
		// else, this has to be maintained when new attribs are added to albums
		if ($parentName) {
			$gallery->album->fields[parentAlbumName] = $parentName;
			$parentAlbum = $albumDB->getAlbumbyName($parentName);
			$parentAlbum->addNestedAlbum($gallery->session->albumName);
			$parentAlbum->save();
			// Set default values in nested album to match settings of parent.
			$gallery->album->fields["perms"]           = $parentAlbum->fields["perms"];
			$gallery->album->fields["bgcolor"]         = $parentAlbum->fields["bgcolor"];
			$gallery->album->fields["textcolor"]       = $parentAlbum->fields["textcolor"];
			$gallery->album->fields["linkcolor"]       = $parentAlbum->fields["linkcolor"];
			$gallery->album->fields["font"]            = $parentAlbum->fields["font"];
			$gallery->album->fields["border"]          = $parentAlbum->fields["border"];
			$gallery->album->fields["bordercolor"]     = $parentAlbum->fields["bordercolor"];
			$gallery->album->fields["returnto"]        = $parentAlbum->fields["returnto"];
			$gallery->album->fields["thumb_size"]      = $parentAlbum->fields["thumb_size"];
			$gallery->album->fields["resize_size"]     = $parentAlbum->fields["resize_size"];
			$gallery->album->fields["rows"]            = $parentAlbum->fields["rows"];
			$gallery->album->fields["cols"]            = $parentAlbum->fields["cols"];
			$gallery->album->fields["fit_to_window"]   = $parentAlbum->fields["fit_to_window"];
			$gallery->album->fields["use_fullOnly"]    = $parentAlbum->fields["use_fullOnly"];
			$gallery->album->fields["print_photos"]    = $parentAlbum->fields["print_photos"];
			$gallery->album->fields["use_exif"]        = $parentAlbum->fields["use_exif"];
			$gallery->album->fields["display_clicks"]  = $parentAlbum->fields["display_clicks"];
			$gallery->album->fields["public_comments"] = $parentAlbum->fields["public_comments"];

			$gallery->album->save();
		} else {
			/*
			* Get a new albumDB because our old copy is not up to
			* date after we created a new album
			*/
			$albumDB = new AlbumDB(FALSE);

			/* move the album to the top if not a nested album*/
			$numAlbums = $albumDB->numAlbums($gallery->user);
			$albumDB->moveAlbum($gallery->user, $numAlbums, 1);
			$albumDB->save();
		}
	}

	if ($error) {
		print "$error<br>";
		$ONBACK_SCRIPT="history.go(-2);";
    		$WIZARD_BUTTONS="true,true,true";
	} else {
		echo "<form id='folder'>\n";
		echo "<input type=hidden name=album value=" . $gallery->album->fields[name] . ">\n";
		echo "<input type=hidden name=setCaption value=$setCaption>\n";
		echo "</form>\n";

		$doit = 1;
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
    		process($userfile, $tag, $userfile_name, $setCaption);
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

//------------------------------------------------
//-- this process function is identical to that in save_photos.
//-- Ugh.

function process($file, $tag, $name, $setCaption="") {
    global $gallery;

    if (!strcmp($tag, "zip")) {
        if (!$gallery->app->feature["zip"]) {
            $error = "Zip not supported";
            continue;
        }
        /* Figure out what files we can handle */
        list($files, $status) = exec_internal(
            fs_import_filename($gallery->app->zipinfo, 1) .
            " -1 " .
            fs_import_filename($file, 1));
        sort($files);
        foreach ($files as $pic_path) {
            $pic = basename($pic_path);
            $tag = ereg_replace(".*\.([^\.]*)$", "\\1", $pic);
            $tag = strtolower($tag);

            if (acceptableFormat($tag) || !strcmp($tag, "zip")) {
                $cmd_pic_path = str_replace("[", "\[", $pic_path);
                $cmd_pic_path = str_replace("]", "\]", $cmd_pic_path);
                exec_wrapper(fs_import_filename($gallery->app->unzip, 1) .
                         " -j -o " .
                         fs_import_filename($file, 1) .
                         " '" .
                         fs_import_filename($cmd_pic_path, 1) .
                         "' -d " .
                         fs_import_filename($gallery->app->tmpDir, 1));
                process($gallery->app->tmpDir . "/$pic", $tag, $pic, $setCaption);
                fs_unlink($gallery->app->tmpDir . "/$pic");
            }
        }
    } else {
        // remove %20 and the like from name
        $name = urldecode($name);
        // parse out original filename without extension
        $originalFilename = eregi_replace(".$tag$", "", $name);
        // replace multiple non-word characters with a single "_"
        $mangledFilename = ereg_replace("[^[:alnum:]]", "_", $originalFilename);

        /* Get rid of extra underscores */
        $mangledFilename = ereg_replace("_+", "_", $mangledFilename);
        $mangledFilename = ereg_replace("(^_|_$)", "", $mangledFilename);
   
        /*
        need to prevent users from using original filenames that are purely numeric.
        Purely numeric filenames mess up the rewriterules that we use for mod_rewrite
        specifically:
        RewriteRule ^([^\.\?/]+)/([0-9]+)$  /~jpk/gallery/view_photo.php?set_albumName=$1&index=$2  [QSA]
        */
   
        if (ereg("^([0-9]+)$", $mangledFilename)) {
            $mangledFilename .= "_G";
        }
   
        set_time_limit($gallery->app->timeLimit);
        if (acceptableFormat($tag)) {
            if ($setCaption) {
                $caption = $originalFilename;
            } else {
                $caption = "";
            }
   
	    /*
	     * Move the uploaded image to our temporary directory
	     * using move_uploaded_file so that we work around
	     * issues with the open_basedir restriction.
	     */
	    if (function_exists('move_uploaded_file')) {
		$newFile = tempnam($gallery->app->tmpDir, "gallery");
		if (move_uploaded_file($file, $newFile)) {
		    $file = $newFile;

		    /* Make sure we remove this file when we're done */
		    $temp_files[$file]++;
		}
	    }

            $err = $gallery->album->addPhoto($file, $tag, $mangledFilename, $caption);
            if (!$err) {
                /* resize the photo if needed */
                if ($gallery->album->fields["resize_size"] > 0 && isImage($tag)) {
                    $index = $gallery->album->numPhotos(1);
                    $photo = $gallery->album->getPhoto($index);
                    list($w, $h) = $photo->image->getRawDimensions();
                    if ($w > $gallery->album->fields["resize_size"] ||
                        $h > $gallery->album->fields["resize_size"]) {
                        $gallery->album->resizePhoto($index, $gallery->album->fields["resize_size"]);
                    }
                }
            } else {
                $error = "$err";
            }
        } else {
            $error = "Skipping $name (can't handle '$tag' format)";
        }
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
<?php
if ($doit) {
	echo "DOIT()";
}
?>
</script>
</body>
</html>

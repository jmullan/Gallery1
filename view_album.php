<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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

require(dirname(__FILE__) . '/init.php');

//Prevent error
if (!$gallery->session->albumName) {
	header("Location: " . makeAlbumUrl());
	return;
}

// Hack check
if (!$gallery->user->canReadAlbum($gallery->album)) {
	header("Location: " . makeAlbumUrl());
	return;
}

if (!$gallery->album->isLoaded()) {
	header("Location: " . makeAlbumUrl());
	return;
}

$gallery->session->offlineAlbums[$gallery->album->fields["name"]]=true;


if (!isset($page)) {
    if (isset($gallery->session->albumPage[$gallery->album->fields['name']])) {
	$page = $gallery->session->albumPage[$gallery->album->fields["name"]];
    } else {
	$page = 1;
    }
} else {
	$gallery->session->albumPage[$gallery->album->fields["name"]] = $page;
}

$albumName = $gallery->session->albumName;

if (!isset($gallery->session->viewedAlbum[$albumName]) && !$gallery->session->offline) {
	$gallery->session->viewedAlbum[$albumName] = 1;
	$gallery->album->incrementClicks();
} 

$rows = $gallery->album->fields["rows"];
$cols = $gallery->album->fields["cols"];
list ($numPhotos, $numAlbums) = $gallery->album->numVisibleItems($gallery->user);
$perPage = $rows * $cols;
$maxPages = max(ceil(($numPhotos + $numAlbums) / $perPage), 1);

if ($page > $maxPages) {
	$page = $maxPages;
}

$start = ($page - 1) * $perPage + 1;
$end = $start + $perPage;

$nextPage = $page + 1;
if ($nextPage > $maxPages) {
	$nextPage = 1;
        $last = 1;
}

$previousPage = $page - 1;
if ($previousPage == 0) {
	$previousPage = $maxPages;
	$first = 1;
}


if (!empty($Vote)) {
       if ($gallery->album->getPollScale() == 1 && $gallery->album->getPollType() != "rank")
       {
               for ($index=$start; $index < $start+$perPage; $index ++)
               {
                       $id=$gallery->album->getPhotoId($index);
                       if (!$votes[$id])
                       {
                               $votes[$id]=null;
                       }

               }
       }
       saveResults($votes);
}

$bordercolor = $gallery->album->fields["bordercolor"];

$imageCellWidth = floor(100 / $cols) . "%";

$navigator["page"] = $page;
$navigator["pageVar"] = "page";
$navigator["maxPages"] = $maxPages;
$navigator["fullWidth"] = "100";
$navigator["widthUnits"] = "%";
$navigator["url"] = makeAlbumUrl($gallery->session->albumName);
$navigator["spread"] = 5;
$navigator["bordercolor"] = $bordercolor;

$fullWidth = $navigator["fullWidth"] . $navigator["widthUnits"];
$breadCount = 0;
$breadtext = array();
$pAlbum = $gallery->album;
$depth=0;
$upArrowURL = '<img src="' . getImagePath('nav_home.gif') . '" width="13" height="11" alt="' . _("navigate UP") .'" title="' .
_("navigate UP") .'" border="0">';
do {
  if (!strcmp($pAlbum->fields["returnto"], "no")) {
    break;
  }
  $depth++;
  $pAlbumName = $pAlbum->fields['parentAlbumName'];
  if ($pAlbumName && (!$gallery->session->offline 
     || isset($gallery->session->offlineAlbums[$pAlbumName]))) {
	$pAlbum = new Album();
	$pAlbum->load($pAlbumName);
	$breadtext[$breadCount] = _("Album") .": <a class=\"bread\" href=\"" . makeAlbumUrl($pAlbumName) . 
	"\">" . $pAlbum->fields['title'] . "&nbsp;" . $upArrowURL . "</a>";
  } elseif (!$gallery->session->offline || isset($gallery->session->offlineAlbums["albums.php"])) {
	//-- we're at the top! --- 
	$breadtext[$breadCount] = _("Gallery") .": <a class=\"bread\" href=\"" . makeGalleryUrl("albums.php") . 
	"\">" . $gallery->app->galleryTitle . "&nbsp;" . $upArrowURL . "</a>";
	$pAlbumName = '';
  } 
  elseif ($gallery->session->offline) {	// test is redundant.  offline must be 
  					// true if you reach this line.
	break; 
  }

  $breadCount++;
} while ($pAlbumName && $depth < $gallery->app->maximumAlbumDepth);

//-- we built the array backwards, so reverse it now ---
for ($i = count($breadtext) - 1; $i >= 0; $i--) {
	$breadcrumb["text"][] = $breadtext[$i];
}
$breadcrumb["bordercolor"] = $bordercolor;

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html> 
<head>
  <title><?php echo $gallery->app->galleryTitle ?> :: <?php echo $gallery->album->fields["title"] ?></title>
  <?php common_header(); ?>

  <?php /* prefetching/navigation */
  if (!isset($first)) { ?>
      <link rel="first" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => 1)) ?>" >
      <link rel="prev" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $previousPage)) ?>" >
  <?php }
  if (!isset($last)) { ?>
      <link rel="next" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $nextPage)) ?>" >
      <link rel="last" href="<?php echo makeAlbumUrl($gallery->session->albumName, '', array('page' => $maxPages)) ?>" >
  <?php } if ($gallery->album->isRoot() && 
  	(!$gallery->session->offline || 
	 isset($gallery->session->offlineAlbums["albums.php"]))) { ?>
  <link rel="up" href="<?php echo makeAlbumUrl(); ?>" >
      <?php
      } else if (!$gallery->session->offline || 
	 isset($gallery->session->offlineAlbums[$pAlbum->fields['parentAlbumName']])) { ?>
  <link rel="up" href="<?php echo makeAlbumUrl($gallery->album->fields['parentAlbumName']); ?>" >
  <?php } 
  	if (!$gallery->session->offline || 
	 isset($gallery->session->offlineAlbums["albums.php"])) { ?>
  <link rel="top" href="<?php echo makeGalleryUrl('albums.php', array('set_albumListPage' => 1)) ?>" >
  <?php } ?>
  <style type="text/css">
<?php
// the link colors have to be done here to override the style sheet 
if ($gallery->album->fields["linkcolor"]) {
?>
    A:link, A:visited, A:active
      { color: <?php echo $gallery->album->fields['linkcolor'] ?>; }
    A:hover
      { color: #ff6600; }
<?php
}
if ($gallery->album->fields["bgcolor"]) {
	echo "BODY { background-color:".$gallery->album->fields['bgcolor']."; }";
}
if (isset($gallery->album->fields['background']) && $gallery->album->fields['background']) {
	echo "BODY { background-image:url(".$gallery->album->fields['background']."); } ";
}
if ($gallery->album->fields["textcolor"]) {
	echo "BODY, TD {color:".$gallery->album->fields['textcolor']."; }";
	echo ".head {color:".$gallery->album->fields['textcolor']."; }";
	echo ".headbox {background-color:".$gallery->album->fields['bgcolor']."; }";
}
?>
  </style>
</head>

<body dir="<?php echo $gallery->direction ?>">
<?php } ?>
<?php includeHtmlWrap("album.header"); ?>

<?php if (!$gallery->session->offline) { ?>

  <script language="javascript1.2" type="text/JavaScript">
  <!-- //
  var statusWin;
  function showProgress() {
	statusWin = <?php echo popup_status("progress_uploading.php"); ?>
  }

  function hideProgress() {
	if (typeof(statusWin) != "undefined") {
		statusWin.close();
		statusWin = void(0);
	}
  }

  function hideProgressAndReload() {
	hideProgress();
	history.go(0);
  }

  function imageEditChoice(selected_select) {
	  var sel_index = selected_select.selectedIndex;
	  var sel_value = selected_select.options[sel_index].value;
	  var sel_class = selected_select.options[sel_index].className;
	  selected_select.options[0].selected = true;
	  selected_select.blur();
          if (sel_class == "url") {
	      document.location = sel_value;
	  } else {
              // the only other option should be popup
	      <?php echo popup('sel_value', 1) ?>
          }
  } 
  //--> 
  </script>
<?php } ?>

<?php 
function showChoice($label, $target, $args, $class="popup") {
    global $gallery, $showAdminForm;
    if (!$showAdminForm)
    	return;
    
    if (empty($args['set_albumName'])) {
	$args['set_albumName'] = $gallery->session->albumName;
    }
    echo "<option class=\"$class\" value='" . htmlspecialchars(makeGalleryUrl($target, $args)) . "'>$label</option>";
}

$adminText = "<span class=\"admin\">";
$albums_str= pluralize_n($numAlbums, _("1 sub-album"), _("sub-albums"), _("No albums"));
$imags_str= pluralize_n($numPhotos, _("1 image"), _("images") , _("no images"));
$pages_str=pluralize_n($maxPages, _("1 page") , _("pages") , _("0 pages"));

if ($numAlbums && $maxPages > 1) {
	$adminText .= sprintf(_("%s and %s in this album on %s"),
			$albums_str, $imags_str, $pages_str);
} else if ($numAlbums) {
	$adminText .= sprintf(_("%s and %s in this album"),
			$albums_str, $imags_str);
} else if ($maxPages > 1) {
	$adminText .= sprintf(_("%s in this album on %s"),
			$imags_str, $pages_str);
} else {
	$adminText .= sprintf(_("%s in this album"),
			$imags_str);
}

if ($gallery->user->canWriteToAlbum($gallery->album) && 
	!$gallery->session->offline) {
	$hidden = $gallery->album->numHidden();
	$verb = _("%s are hidden");
	if ($hidden == 1) {
		$verb = _("%s is hidden");
	}
	if ($hidden) {
		$adminText .= "(".sprintf($verb, $hidden).")";
	}
} 
$adminText .="</span>";

/* admin items for drop-down menu */
$adminOptions = array(
		      'add_photos'      => array('name' 	=> _('add photos'),
						 'requirements' => array('canAddToAlbum'),
						 'action' 	=> 'popup',
						 'value' 	=> makeGalleryUrl('add_photos_frame.php', 
									array('set_albumName' => $gallery->session->albumName, 
										'type' => 'popup'))),
		      'rename_album'    => array('name' => _('rename album'),
						 'requirements' => array('isAlbumOwner'),
						 'action' => 'popup',
						 'value' => makeGalleryUrl('rename_album.php',
								array('set_albumName' => $gallery->session->albumName,
									'type' => 'popup',
									'index' => $i, 'useLoad' => 1))),
		      'nested_album'    => array('name' => _('new nested album'),
						 'requirements' => array('canCreateSubAlbum',
									 'notOffline'),
						 'action' => 'url',
						 'value' => doCommand('new-album', array('parentName' => $gallery->session->albumName),
									'view_album.php')),
		      'custom_fields'   => array('name' => _('custom fields'),
						 'requirements' => array('canChangeText',
									 'notOffline'),
						 'action' => 'popup',
						 'value' => makeGalleryUrl('extra_fields.php',
								array('set_albumName' => $gallery->session->albumName,
									'type' => 'popup'))),
		      'edit_captions'   => array('name' => _('edit captions'),
						 'requirements' => array('canChangeText',
									 'notOffline'),
						 'action' => 'url',
						 'value' => makeGalleryUrl('captionator.php',
								array('set_albumName' => $gallery->session->albumName,
									'page' => $page, 'perPage' => $perPage))),
		      'sort_items'      => array('name' => _('sort items'),
						 'requirements' => array('canWriteToAlbum',
									 'photosExist'),
						 'action' => 'popup',
						 'value' => makeGalleryUrl('sort_album.php',
								array('set_albumName' => $gallery->session->albumName,
									'type' => 'popup'))),
		      'resize_all'      => array('name' => _('resize all'),
						 'requirements' => array('canWriteToAlbum',
									 'photosExist'),
						 'action' => 'popup',
						 'value' => makeGalleryUrl('resize_photo.php',
								array('set_albumName' => $gallery->session->albumName,
									'index' => 'all',
									'type' => 'popup'))),
		      'rebuild_thumbs'  => array('name' => _('rebuild thumbs'),
						 'requirements' => array('canWriteToAlbum',
									 'photosExist'),
						 'action' => 'popup',
						 'value' => doCommand('remake-thumbnail',
								      array('set_albumName' => $gallery->session->albumName, 'index' => 'all'),
								      'view_album.php')),
		      'properties'      => array('name' => _('properties'),
						 'requirements' => array('canWriteToAlbum'),
						 'action' => 'popup',
						 'value' => makeGalleryUrl('edit_appearance.php',
								array('set_albumName' => $gallery->session->albumName,
									'type' => 'popup'))),
		      'permissions'     => array('name' => _('permissions'),
						 'requirements' => array('isAdminOrAlbumOwner'),
						 'action' => 'popup',
						 'value' => makeGalleryUrl('album_permissions.php',
								array('set_albumName' => $gallery->session->albumName,
									'type' => 'popup'))),
		      'poll_properties' => array('name' => _('poll properties'),
						 'requirements' => array('isAdminOrAlbumOwner'),
						 'action' => 'popup',
						 'value' => makeGalleryUrl('poll_properties.php',
								array('set_albumName' => $gallery->session->albumName,
									'type' => 'popup'))),
		      'poll_results'    => array('name' => _('poll results'),
						 'requirements' => array('isAdminOrAlbumOwner'),
						 'action' => 'url',
						 'value' => makeGalleryUrl('poll_results.php',
								array('set_albumName' => $gallery->session->albumName,
									'type' => 'popup'))),
		      'poll_reset'      => array('name' => _('poll reset'),
						 'requirements' => array('isAdminOrAlbumOwner'),
						 'action' => 'popup',
						 'value' => makeGalleryUrl('reset_votes.php',
								array('set_albumName' => $gallery->session->albumName,
									'type' => 'popup'))),
		      'view_comments'   => array('name' => _('view comments'),
						 'requirements' => array('isAdminOrAlbumOwner',
									 'allowComments',
									 'comments_enabled',
									 'hasComments'),
						 'action' => 'url',
						 'value' => makeGalleryUrl('view_comments.php',
									   array('set_albumName' => $gallery->session->albumName)))
);

/* sort the drop-down array by translated name */
$cmp = create_function('$a, $b', "return strcmp(\$a['name'], \$b['name']);");
uasort($adminOptions, $cmp);
reset($adminOptions);

$adminOptionHTML = '';
$adminJavaScript = '';
/* determine which options to include in admin drop-down menu */
if (!$gallery->session->offline) {
  foreach ($adminOptions as $key => $data) {
    $enabled = true;
    while ($enabled && $test = array_shift($data['requirements'])) {
	$success = testRequirement($test);
	if (!$success) {
	    $enabled = false;
	}
    }
    if ($enabled) {
	$adminOptionHTML .= "\t\t<option value=\"$key\">${data['name']}</option>\n";
	$adminJavaScript .= "adminOptions.$key = new Object;\n";
	$adminJavaScript .= "adminOptions.$key.action = \"${data['action']}\";\n";
	/* We need to pass un-html-entityified URLs to the JavaScript
	 * This line effectively reverses htmlentities() */
	$decodeHtml = strtr($data['value'], array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	$adminJavaScript .= "adminOptions.$key.value = \"${decodeHtml}\";\n";
    }
  }
}

$adminCommands = '';
$adminJSFrame = '';
/* build up drop-down menu and related javascript */
if (!empty($adminOptionHTML)) {
    $adminJSFrame .= "<script language=\"javascript1.2\" type=\"text/JavaScript\">\n"
	    . "adminOptions = new Object;\n"
	    . $adminJavaScript
	    . "\nfunction execAdminOption() {\n"
	    . "\tkey = document.forms.admin_options_form.admin_select.value;\n"
	    . "\tswitch (adminOptions[key].action) {\n"
	    . "\tcase 'popup':\n"
	    . "\t\tnw = window.open(adminOptions[key].value, 'Edit', 'height=500,width=600,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes');\n"
	    . "\t\tnw.opener=self;\n"
	    . "\t\tbreak;\n"
	    . "\tcase 'url':\n"
	    . "\t\tdocument.location = adminOptions[key].value;\n"
	    . "\t\tbreak;\n"
	    . "\t}\n"
	    . "\tdocument.forms.admin_options_form.admin_select.selectedIndex = 0;\n"
	    . "\tdocument.forms.admin_options_form.admin_select.blur();\n"
	    . "}\n"
	    . "</script>\n\n";
    
    $adminCommands .= "\n\t<select class=\"adminform\" name=\"admin_select\" onChange=\"execAdminOption()\">\n";
    $adminCommands .= "\t\t<option value=\"\">&laquo; " . _('admin options') . " &raquo;</option>\n";
    $adminCommands .= $adminOptionHTML;
    $adminCommands .= "\t</select>\n";
}

$userCommands = '';
if ($gallery->album->fields["slideshow_type"] != "off" && $numPhotos !=0) {
       	$userCommands .= "<a class=\"admin\" href=\"" . 
	       	makeGalleryUrl("slideshow.php",
			       	array("set_albumName" => $albumName)) .
	      	'">['. _("slideshow") ."]</a>&nbsp;";
}
if (!$GALLERY_EMBEDDED_INSIDE && !$gallery->session->offline) {
	if ($gallery->user->isLoggedIn()) {
	        $userCommands .= "<a class=\"admin\" href=\"" .
					doCommand("logout", array(), "view_album.php", array("page" => $page)) .
				  "\">[" . _("logout") . "]</a>\n";
	} else {
		$userCommands .= popup_link("[". _("login") ."]", "login.php", false, true, 500, 500, 'admin') . "\n";
	} 
}
$adminbox["text"] = $adminText;
$adminbox["commands"] =	"<span class =\"admin\">" .  $adminCommands . 
			$userCommands .  "</span>";
$adminbox["bordercolor"] = $bordercolor;
$adminbox["top"] = true;

if (!empty($adminOptionHTML)) {
    print $adminJSFrame;
    print "<form name=\"admin_options_form\" action=\"view_album.php\">\n";
}
includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
?>

<!-- top nav -->
<?php
$breadcrumb["top"] = true;
$breadcrumb['bottom'] = false;
if ($breadCount) {
	includeLayout('navtablemiddle.inc');
	includeLayout('breadcrumb.inc');
}
includeLayout('navtablemiddle.inc');
includeLayout('navigator.inc');
includeLayout('navtableend.inc');

if (!empty($adminOptionHTML)) {
    print "</form>\n";
}

#-- if borders are off, just make them the bgcolor ----
$borderwidth = $gallery->album->fields["border"];
if (!strcmp($borderwidth, "off")) {
	$bordercolor = $gallery->album->fields["bgcolor"];
	$borderwidth = 1;
}
if (($gallery->album->getPollType() == "rank") && canVote())
{
        $my_choices=array();
        if ( $gallery->album->fields["votes"])
	{
            foreach ($gallery->album->fields["votes"] as $id => $image_votes)
            {
		$index=$gallery->album->getIndexByVotingId($id);
		if ($index < 0)
		{
			// image has been deleted!
			unset($gallery->album->fields["votes"][$id]);
			continue;
		}

                if (isset($image_votes[getVotingID()]))
                {
                        $my_choices[$image_votes[getVotingID()]] = $id;
                }
            }
	}
        if (sizeof($my_choices) == 0
		&& $gallery->album->getVoterClass() ==  "Logged in")
        {
		print _("You have no votes recorded for this poll."). '<br>';

        }
        else if (sizeof($my_choices) > 0)
        {
                ksort($my_choices);
                print _("Your current choices are");
                print "<table>\n";
                $nv_pairs=$gallery->album->getVoteNVPairs();
                foreach ($my_choices as $key => $id)
                {
                        print "<tr><td>".
                                $nv_pairs[$key]["name"].
                                ":</td>\n";
			$index=$gallery->album->getIndexByVotingId($id);
			if ($gallery->album->isAlbum($index)) {
				$albumName = $gallery->album->getAlbumName($index);
                        	print "<td><a href=\n".
					makeAlbumUrl($albumName). ">\n";
			       	$myAlbum = new Album();
			       	$myAlbum->load($albumName);
			       	print sprintf(_("Album: %s"), $myAlbum->fields['title']);
			       	print  "</a></td></tr>\n";
			} else {
                        	print "<td><a href=\n".
					makeAlbumUrl($gallery->session->albumName, $id);
                        	print  ">\n";
				$desc = $gallery->album->getCaption($index);
				if (trim($desc) == "") {
					$desc=$gallery->album->getPhotoId($index);
				}
                        	print  $desc;
			       	print  "</a></td></tr>\n";
			}
                }
                print "</table>\n";
        }

}
$results=1;
if ($gallery->album->getPollShowResults())
{
        list($buf, $results)=showResultsGraph( $gallery->album->getPollNumResults());
	print $buf;
       	if ($results)
       	{
	       	print '<a href="' . makeGalleryUrl("poll_results.php",
	       	array("set_albumName" => $gallery->session->albumName)).
		      	'">' ._("See full poll results") . '</a><br>';
       	}
}

echo makeFormIntro("view_album.php",
	       	array("name" => "vote_form", "method" => "POST"));
if (canVote())
{ 
 		$nv_pairs=$gallery->album->getVoteNVPairs();
 		if ($gallery->album->getPollScale()==1)
 		{
 			$options = $nv_pairs[0]["name"];
 		}
 		else
 		{
			/* note to translators:
			   This produces (in English) a list of the form: "a, b, c or d".  Correct translation
			   of ", " and " or  " should produce a version that makes sense in your language.
			   */
			$options = "";
 			for ($count=0; $count < $gallery->album->getPollScale()-2 ; $count++)
 			{
 				$options .= $nv_pairs[$count]["name"]._(", ");
 			}
 			$options .= $nv_pairs[$count++]["name"]._(" or ");  
 			$options .= $nv_pairs[$count]["name"];
 			
 		}
		print '<span class="attention">';
		print sprintf(_("To vote for an image, click on %s."), $options);
 		print "  ".sprintf(_("You MUST click on %s for your vote to be recorded."), 
				"<b>"._("Vote")."</b>");
 		if ($gallery->album->getPollType() == "rank")
 		{
 		    print "  ".sprintf(_("You have a total of %s and can change them if you wish."),
				    pluralize_n($gallery->album->getPollScale(),
					    _("1 vote"), _("votes"), 
					    _("no votes"))) .
				    '</span><p>';
 		}
 		else
 		{
 		    print "  "._("You can change your choices if you wish."). "</span><p>";
 			
 		}

 ?>
   <script language="javascript1.2" type="text/JavaScript">
 function chooseOnlyOne(i, form_pos, scale)
 {
   for(var j=0;j<scale;j++)
     {
         if(j != i)
 	    {
 		eval("document.vote_form['votes["+j+"]']["+form_pos+"].checked=false");
 	    }
     }
 }
   </script>
 		<table width="100%"><tr><td align=center>
                <?php if (canVote()) { ?>
 		<input type=submit name="Vote" value="<?php print _("Vote") ?>">
                <?php } ?>
 		</td></tr></table>
<?php
}
?>
<?php
if ($page == 1)
{
        print $gallery->album->fields["summary"];
} ?>

<!-- image grid table -->
<br>
<table width="<?php echo $fullWidth ?>" border="0" cellspacing="0" cellpadding="7">
<?php
$numPhotos = $gallery->album->numPhotos(1);
$displayCommentLegend = 0;  // this determines if we display "* Item contains a comment" at end of page
if ($numPhotos) {
	$rowCount = 0;

	// Find the correct starting point, accounting for hidden photos
	$rowStart = 0;
	$cnt = 0;
	$form_pos=0; // counts number of images that have votes below, ie withou albums;
	while ($cnt < $start) {
		$rowStart = getNextPhoto($rowStart);
		$cnt++;
	}

	while ($rowCount < $rows) {
		/* Do the inline_albumthumb header row */
		$i = $rowStart;
		$j = 1;
		$printTableRow = false;
		if ($j <= $cols && $i <= $numPhotos) {
			$printTableRow = true;
			echo('<tr>');
		}
		while ($j <= $cols && $i <= $numPhotos) {
			echo("<td>");
			includeHtmlWrap("inline_albumthumb.header");
			echo("</td>");
			$j++; 
			$i = getNextPhoto($i);
		}
		if ($printTableRow) {
			echo('</tr>');
		}

		/* Do the picture row */
		$i = $rowStart;
		$j = 1;
		if ($printTableRow) {
			echo('<tr>');
		}
		while ($j <= $cols && $i <= $numPhotos) {
			echo("<td width=\"$imageCellWidth\" align=\"center\" valign=\"middle\">");

			//-- put some parameters for the wrap files in the global object ---
			$gallery->html_wrap['borderColor'] = $bordercolor;
			$gallery->html_wrap['borderWidth'] = $borderwidth;
			$gallery->html_wrap['pixelImage'] = getImagePath('pixel_trans.gif');
			if ($gallery->album->isAlbum($i)) {
				$scaleTo = 0; //$gallery->album->fields["thumb_size"];
				$album = $gallery->album->getNestedAlbum($i);
				list($iWidth, $iHeight) = $album->getHighlightDimensions($scaleTo);
			} else {
				$scaleTo=0;  // thumbs already the right 
					    //	size for this album
				list($iWidth, $iHeight) = $gallery->album->getThumbDimensions($i, $scaleTo);
			}
			if ($iWidth == 0) {
			    $iWidth = $gallery->album->fields["thumb_size"];
			}
			if ($iHeight == 0) {
			    $iHeight = 100;
			}
			$gallery->html_wrap['imageWidth'] = $iWidth;
			$gallery->html_wrap['imageHeight'] = $iHeight;

			$id = $gallery->album->getPhotoId($i);
			if ($gallery->album->isMovie($id)) {
				$gallery->html_wrap['imageTag'] = $gallery->album->getThumbnailTag($i);
				$gallery->html_wrap['imageHref'] = $gallery->album->getPhotoPath($i);
				$gallery->html_wrap['frame'] = $gallery->album->fields['thumb_frame'];
			       	/*begin backwards compatibility */
			       	$gallery->html_wrap['thumbTag'] = 
					$gallery->html_wrap['imageTag'];
			       	$gallery->html_wrap['thumbHref'] = 
					$gallery->html_wrap['imageHref'];
			       	/*end backwards compatibility*/
				includeHtmlWrap('inline_moviethumb.frame');
			} elseif ($gallery->album->isAlbum($i)) {
				$myAlbum = new Album();
				$myAlbum->load($gallery->album->getAlbumName($i));

				$gallery->html_wrap['imageTag'] = $myAlbum->getHighlightTag($scaleTo,'',_("Highlight for Album: ").htmlentities(removeTags($myAlbum->fields['title']),ENT_COMPAT));
				$gallery->html_wrap['imageHref'] = makeAlbumUrl($gallery->album->getAlbumName($i));
				$gallery->html_wrap['frame'] = $gallery->album->fields['album_frame'];
			       	/*begin backwards compatibility */
			       	$gallery->html_wrap['thumbWidth'] = 
					$gallery->html_wrap['imageWidth'];
			       	$gallery->html_wrap['thumbHeight'] = 
					$gallery->html_wrap['imageHeight'];
			       	$gallery->html_wrap['thumbTag'] = 
					$gallery->html_wrap['imageTag'];
			       	$gallery->html_wrap['thumbHref'] = 
					$gallery->html_wrap['imageHref'];
			       	/*end backwards compatibility*/
      
				includeHtmlWrap('inline_albumthumb.frame');

			} else {
				$gallery->html_wrap['imageTag'] = $gallery->album->getThumbnailTag($i);
				$gallery->html_wrap['imageHref'] = makeAlbumUrl($gallery->session->albumName, $id);
				$gallery->html_wrap['frame'] = $gallery->album->fields['thumb_frame'];
			       	/*begin backwards compatibility */
			       	$gallery->html_wrap['thumbTag'] =
				       	$gallery->html_wrap['imageTag'];
			       	$gallery->html_wrap['thumbHref'] =
				       	$gallery->html_wrap['imageHref'];
			       	/*end backwards compatibility*/
      
				includeHtmlWrap('inline_photothumb.frame');

				if (!strcmp($gallery->album->fields['showDimensions'], 'yes')) {
					$photo    = $gallery->album->getPhoto($i);
					$image    = $photo->image;
					$viewFull = $gallery->user->canViewFullImages($gallery->album);
					$fullOnly = (isset($gallery->session->fullOnly) &&
						!strcmp($gallery->session->fullOnly, 'on') &&
						!strcmp($gallery->album->fields['use_fullOnly'], 'yes'));
					list($wr, $hr) = $image->getDimensions();
					list($wf, $hf) = $image->getRawDimensions();
					/* display file sizes if dimensions are identical */
					if ($wr == $wf && $hr == $hf && $viewFull && $photo->isResized()) {
					    $fsr = ' ' . sprintf(_('%dkB'), (int) $photo->getFileSize(0) >> 10);
					    $fsf = ' ' . sprintf(_('%dkB'), (int) $photo->getFileSize(1) >> 10);
					} else {
					    $fsr = '';
					    $fsf = '';
					}
					echo "<br >\n";
					if (($photo->isResized() && !$fullOnly) || !$viewFull) {
						echo '<a href="'.
							makeAlbumUrl($gallery->session->albumName, $id) .
								"\">[${wr}x{$hr}${fsr}]</a>&nbsp;";
					}
					if ($viewFull) {
						echo '<a href="'.
							makeAlbumUrl($gallery->session->albumName,
							$id, array('full' => 1)) .
							"\">[${wf}x${hf}${fsf}]</a>";
					}
				}
			}

			echo("</td>");
			$j++; 
			$i = getNextPhoto($i);
		}
		if ($printTableRow) {
			echo('</tr>');
		}
	
		/* Now do the caption row */
		$i = $rowStart;
		$j = 1;
		if ($printTableRow) {
		    echo('<tr>');
		}
		while ($j <= $cols && $i <= $numPhotos) {
			if ($gallery->album->isAlbum($i)) {
				$myAlbum = new Album;
				$myAlbum->load($gallery->album->getAlbumName($i));
			}       
			else {
				$myAlbum = NULL;
			}
                        
			if ($gallery->album->isAlbum($i)) {
			    $iWidth = $gallery->album->fields['thumb_size'];
			} else {
			    list($iWidth, $iHeight) = $gallery->album->getThumbDimensions($i);
			}
			echo("<td width=\"$imageCellWidth\" valign=\"top\" align=\"center\">");

			// put form outside caption to compress lines
			if (!$gallery->session->offline &&
			   (($gallery->user->canDeleteFromAlbum($gallery->album)) ||  
			   ($gallery->user->canWriteToAlbum($gallery->album)) || 
			   ($gallery->user->canChangeTextOfAlbum($gallery->album)) ||
			   (($gallery->album->getItemOwnerModify() || $gallery->album->getItemOwnerDelete()) &&
			   ($gallery->album->isItemOwner($gallery->user->getUid(), $i) || 
			   (isset($myAlbum) && $gallery->user->isOwnerOfAlbum($myAlbum))))))
			{
				$showAdminForm = 1;
			} else { 
				$showAdminForm = 0;
			}
			echo '<table width="' . $gallery->album->fields['thumb_size']
				. '" border="0" cellpadding="0" cellspacing="4"><tr><td class="modcaption">';
			$id = $gallery->album->getPhotoId($i);
			if ($gallery->album->isHidden($i) && !$gallery->session->offline) {
				echo "(" . _("hidden") .")<br>";
			}
			if (isset($myAlbum)) {
				$myDescription = $myAlbum->fields['description'];
				$buf = "";
				$buf = $buf."<b>". sprintf(_("Album: %s"), $myAlbum->fields['title'])."</b>";
				if ($myDescription != _("No description") &&
					$myDescription != "No description" && 
					$myDescription != "") {
					$buf = $buf."<br>".$myDescription."";
				}
				echo $buf;

				echo '<br><br><span class="fineprint">';
				echo _("Changed: ") ." ". $myAlbum->getLastModificationDate();
 				echo "\n<br>";
				echo _("Contains: ") ." ". pluralize_n(array_sum($myAlbum->numVisibleItems($gallery->user)), _("1 item"), _("items"), _("0 items")) . '.';
				$lastCommentDate = $myAlbum->lastCommentDate();
				if ($lastCommentDate > 0) {
					print lastCommentString($lastCommentDate, $displayCommentLegend);
				}
				echo '</span><br>';
				if (!(strcmp($gallery->album->fields["display_clicks"] , "yes")) &&  !$gallery->session->offline && ($myAlbum->getClicks() > 0)) {
					echo '<span class="viewcounter">';
					echo _("Viewed:") . " ". pluralize_n($myAlbum->getClicks(), _("1 time") , _("times"), _("0 times"));
					echo ".<br></span>";
				}
				echo '</span>';
			} 
			else {
				echo(nl2br($gallery->album->getCaption($i)));
				echo($gallery->album->getCaptionName($i));
				// indicate with * if we have a comment for a given photo
				if ($gallery->user->canViewComments($gallery->album) 
					&& $gallery->app->comments_enabled == 'yes') {
					$lastCommentDate = $gallery->album->itemLastCommentDate($i);
					print lastCommentString($lastCommentDate, $displayCommentLegend);
				}
				echo("<br>");
				if (!(strcmp($gallery->album->fields["display_clicks"] , "yes")) && !$gallery->session->offline && ($gallery->album->getItemClicks($i) > 0)) {
					echo '<span class="viewcounter">';
					echo _("Viewed:") ." ". pluralize_n($gallery->album->getItemClicks($i), _("1 time"), _("times") ,_("0 times")) ;
					echo ".<br></span>";
				}
			}
		       	echo "</td></tr></table>\n";
		       	if (canVote()) {
				print '<table><tr><td align="left">';
			       	addPolling($gallery->album->getVotingIdByIndex($i),
					       	$form_pos, false);
			       	$form_pos++;
		       	}

			if ($showAdminForm) {
				if ($gallery->album->isMovie($id)) {
					$label = _("Movie");
				} elseif ($gallery->album->isAlbum($i)) {
					$label = _("Album");
				} else {
					$label = _("Photo");
				}
			       	if (canVote()) {
				       	print '</td></tr><tr><td align="left">';
				}
				echo("<select style='FONT-SIZE: 10px;' name='s$i' ".
					"onChange='imageEditChoice(document.vote_form.s$i)'>");
				echo("<option value=''>&laquo; ". _("Edit") . " $label &raquo;</option>");
			}
			if ($gallery->album->getItemOwnerModify() && 
			    $gallery->album->isItemOwner($gallery->user->getUid(), $i) && 
			    !$gallery->album->isAlbum($i) &&
			    !$gallery->user->canChangeTextOfAlbum($gallery->album)) {
				showChoice("Edit Text", "edit_caption.php", array("index" => $i));
			}
			if ($gallery->album->getItemOwnerModify() && 
			    $gallery->album->isItemOwner($gallery->user->getUid(), $i) && 
			    !$gallery->album->isAlbum($i) &&
			    !$gallery->album->isMovie($id) &&
			    !$gallery->user->canWriteToAlbum($gallery->album)) {
				showChoice("Edit Thumbnail", "edit_thumb.php", array("index" => $i));
				showChoice("Rotate/Flip $label", "rotate_photo.php", array("index" => $i));
				if (strlen($gallery->app->watermarkDir)) {
					showChoice(_("Edit Watermark"), "edit_watermark.php", array("index" => $i));
				}
			}
			if ($gallery->album->getItemOwnerDelete() && 
			    $gallery->album->isItemOwner($gallery->user->getUid(), $i) && 
			    !$gallery->album->isAlbum($i) &&
			    !$gallery->user->canDeleteFromAlbum($gallery->album)) {
				showChoice("Delete $label", "delete_photo.php", array("id" => $id));
			}
			if ($gallery->user->canChangeTextOfAlbum($gallery->album) && $showAdminForm) {
				if (isset($myAlbum)) {
					if ($gallery->user->canChangeTextOfAlbum($myAlbum)) {	
						_("title");
						showChoice(_("Edit Title"),
							"edit_field.php", 
							array("set_albumName" => $myAlbum->fields["name"],
								"field" => "title")) . 
						_("description");
						showChoice(_("Edit Description"),
							"edit_field.php",
							array("set_albumName" => $myAlbum->fields["name"],
								"field" => "description"));
					}
					if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($myAlbum)) {
						showChoice(_("Rename Album"),
							"rename_album.php",
							array("set_albumName" => $myAlbum->fields["name"],
							      "index" => $i));
					}
				} else {
					showChoice(_("Edit Text"), "edit_caption.php", array("index" => $i));
				}
			}
			if ($gallery->user->canWriteToAlbum($gallery->album) && $showAdminForm) {
				if (!$gallery->album->isMovie($id) && !$gallery->album->isAlbum($i)) {
					showChoice(_("Edit Thumbnail"), "edit_thumb.php", array("index" => $i));
					showChoice(_("Rotate/Flip") ." $label", "rotate_photo.php", array("index" => $i));
					if (!empty($gallery->app->watermarkDir)) {
						showChoice(_("Edit Watermark"), "edit_watermark.php", array("index" => $i));
					}
				}
				if (!$gallery->album->isMovie($id)) {
					 /* Show Highlight Album/Photo only when this i a photo, or Album has a highlight */
					$nestedAlbum=$gallery->album->getNestedAlbum($i);
					if (!$gallery->album->isAlbum($i) || $nestedAlbum->hasHighlight()) {
						showChoice(_('Highlight') . " $label", 'do_command.php', array('cmd' => 'highlight', 'index' => $i));
					}
				}
				if ($gallery->album->isAlbum($i)) {
					showChoice(_("Reset Counter"), "do_command.php",
						array("cmd" => "reset-album-clicks",
						      "set_albumName" => $gallery->album->getAlbumName($i),
							"return" => urlencode(makeGalleryUrl("view_album.php"))));
				}
				showChoice(_("Move ") . $label, "move_photo.php", array("index" => $i, 'reorder' => 0));
				showChoice(_("Reorder ") . $label, "move_photo.php", array("index" => $i, 'reorder' => 1));
				if (!$gallery->album->isAlbum($i)) {
					showChoice(_("Copy ") . $label, "copy_photo.php", array("index" => $i));
				}
			}
			if ($gallery->user->isAdmin() || ((isset($myAlbum) && $gallery->user->isOwnerOfAlbum($myAlbum)) || 
				$gallery->album->isItemOwner($gallery->user->getUid(), $i)) && 
				$showAdminForm) {
				if ($gallery->album->isHidden($i)) {
 					showChoice(_("Show") . " $label", "do_command.php", array("cmd" => "show", "index" => $i));
				} else {
					showChoice(_("Hide") . " $label", "do_command.php", array("cmd" => "hide", "index" => $i));
				}
			}
			if ($gallery->user->canDeleteFromAlbum($gallery->album) && $showAdminForm) {
				if($gallery->album->isAlbum($i)) {
					if($gallery->user->canDeleteAlbum($myAlbum)) {
						showChoice(_("Delete") . " $label", "delete_photo.php",
							array("id" => $myAlbum->fields["name"],
							      "albumDelete" => 1));
					}
				} else {
					showChoice(_("Delete") ." $label", "delete_photo.php",
						   array("id" => $id));
				}
			}
			if($gallery->album->isAlbum($i)) {
			    if ($gallery->user->isAdmin() || $gallery->user->isOwnerOfAlbum($myAlbum) && $showAdminForm) {
				showChoice(_("Permissions"), "album_permissions.php",
					   array("set_albumName" => $myAlbum->fields["name"]));

				/* Watermarking support is enabled and user is allowed to watermark images/albums */
				if (strlen($gallery->app->watermarkDir)) {
					showChoice(_("Watermark Album"), "watermark_album.php", array("set_albumName" => $myAlbum->fields["name"]));
				}
                                if ($gallery->user->canViewComments($myAlbum) &&
                                    ($myAlbum->lastCommentDate() != -1))
                                {
                                        showChoice(_("View Comments"), "view_comments.php", array("set_albumName" => $myAlbum->fields["name"]),"url");
                                }
			    }
			}
                       if ($gallery->user->isAdmin() && !$gallery->album->isAlbum($i)) {
                               showChoice(_("Change Owner"), "photo_owner.php", array("id" => $id));
                       }
		       if ($showAdminForm) {
			       echo "</select>\n";
		       }
		       if (canVote()) {
			       print '</td></tr></table>';
		       }

			echo('</td>');
			$j++;
			$i = getNextPhoto($i);
		}
		if ($printTableRow) {
			echo('</tr>');
		}

		/* Now do the inline_albumthumb footer row */
		$i = $rowStart;
		$j = 1;
		if ($printTableRow) {
			echo('<tr>');
		}
		while ($j <= $cols && $i <= $numPhotos) {
			echo("<td>");
			includeHtmlWrap("inline_albumthumb.footer");
			echo("</td>");
			$j++;
			$i = getNextPhoto($i);
		}
		if ($printTableRow) {
			echo('</tr>');
		}
		$rowCount++;
		$rowStart = $i;
	}
} else {
?>

	<td colspan="<?php echo $rows ?>" align="center" class="headbox">
<?php if ($gallery->user->canAddToAlbum($gallery->album) && !$gallery->session->offline) { ?>
	<span class="head"><?php echo _("Hey! Add some photos.") ?></span>
<?php } else { ?>
	<span class="head"><?php echo _("This album is empty.") ?></span>
<?php } ?>
	</td>
	</tr>
<?php
}
?>

</table>

<?php if ($displayCommentLegend) { //display legend for comments ?>
<span class="error">*</span><span class="fineprint"> <?php echo _("Comments available for this item.") ?></span>
<br><br>
<?php } ?>

<?php
if (canVote())
{
?>
	<table width="100%">
	<tr>
		<td align="center"><input type=submit name="Vote" value="<?php print _("Vote") ?>"></td>
	</tr>
	</table>
<?php
}

?>
	</form>
<?php if ($gallery->user->isLoggedIn() &&  
		$gallery->user->getEmail() &&
		!$gallery->session->offline &&
		$gallery->app->emailOn == "yes") {
	if (isset($submitEmailMe)) {
		if (isset($comments)) {
			$gallery->album->setEmailMe('comments', $gallery->user);
		} else {
			$gallery->album->unsetEmailMe('comments', $gallery->user);
		}
		if (isset($other)) {
			$gallery->album->setEmailMe('other', $gallery->user);
		} else {
			$gallery->album->unsetEmailMe('other', $gallery->user);
		}
	}
	echo makeFormIntro("view_album.php",
	       	array("name" => "email_me", "method" => "POST"));
       	print _("Email me when:")."  ";
       	print _("Comments are added"); ?>
	<input type="checkbox" name="comments" <?php echo ($gallery->album->getEmailMe('comments', $gallery->user)) ? "checked" : "" ?>
	       	onclick="document.email_me.submit()" >
		<?php print _("Other changes are made") ?>
		<input type="checkbox" name="other" <?php echo ($gallery->album->getEmailMe('other', $gallery->user)) ? "checked" : "" ?>
		        onclick="document.email_me.submit()" >
	       	<input type="hidden" name="submitEmailMe">
		</form>
<?php } ?>
<!-- bottom nav -->
<?php 
includeLayout('navtablebegin.inc');
includeLayout('navigator.inc');
if (strcmp($gallery->album->fields["returnto"], "no")) {
	$breadcrumb["top"] = false;
	includeLayout('navtablemiddle.inc');
	includeLayout('breadcrumb.inc');
}
includeLayout('navtableend.inc');
includeLayout('ml_pulldown.inc');
includeHtmlWrap("album.footer");
?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>

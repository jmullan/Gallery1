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
require_once(dirname(__FILE__) . '/init.php');

// Set defaults, if RSS has not been setup via config wizard
if (!isset($gallery->app->rssEnabled)) {
	$gallery->app->rssEnabled = "yes";
	$gallery->app->rssMode = "basic";
	$gallery->app->rssHighlight = "";
	$gallery->app->rssVisibleOnly = "yes";
	$gallery->app->rssDCDate = "no";
	$gallery->app->rssBigPhoto = "yes";
	$gallery->app->rssPhotoTag = "yes";
}

if ($gallery->app->rssEnabled == "no") {
	header("Location: " . makeAlbumHeaderUrl());
}
list($set_albumName) = getRequestVar(array('set_albumName'));

$gallery->session->offlineAlbums["albums.php"] = true;

function albumSort($a, $b) {
	$aTime = $a["!date"];
	$bTime = $b["!date"];

	if ($aTime < $bTime) {
		return 1;
	} else {
		return -1;
	}
}

function bestDate($album) {
	if (isset($album->fields['clicks_date']) && strtotime($album->fields["clicks_date"]) > strtotime($album->fields["last_mod_time"])) {
		return $album->fields['clicks_date'];
	}
	else {
		return $album->fields['last_mod_time'];
	}
}

function getThumbs($album) {
	$tags = "border=0 vspace=2 hspace=0 align=top";
	
	$photos = "";
	$photoCount = $album->numPhotos(1);
	
	for ($i = 1; $i <= $photoCount; $i += 1) {
		$photo = $album->getPhoto($i);
		if (!$photo->isHidden() && !$photo->isMovie() && $photo->thumbnail) {
			$imgtag = $album->getThumbnailTag($i, 0, $tags);
			$photos .= "<a href=\"" . makeAlbumUrl($album->fields['name'], $i) . "\">" . $imgtag . "</a>\n";
		}
	}
	
	return $photos;
}

function getThumbsAndCaptions($album) {
	$tags = "border=0 vspace=2 hspace=0 align=top";
	
	$photos = "";
	$photoCount = $album->numPhotos(1);
	
	for ($i = 1; $i <= $photoCount; $i += 1) {
		$photo = $album->getPhoto($i);
		if (!$photo->isHidden() && !$photo->isMovie() && is_object($photo->thumbnail)) {
			$imgtag = $album->getThumbnailTag($i, 0, $tags);
			$caption = $photo->getCaption();
			$photos .= "<a href=\"" . makeAlbumUrl($album->fields['name'], $i) . "\">" . $imgtag . "</a>\n";
		}
	}
	
	return $photos;
}

function makeDCDate($unixDate) {
	$dcDate = date("Y-m-d\TH:i:sO", $unixDate);
	
	/* CAUTION: This will not work in zones with 
	 * half-our time offsets
	 */
	
	return eregi_replace("..$", ":00", $dcDate);
}

/* Read the album list */
$albumDB = new AlbumDB(FALSE);
$gallery->session->albumName = "";
$page = 1;

$albumList = array();
/* Initialize album and photo counts */
$numAlbums = 0;
$numPhotos = 0;
if (isset($set_albumName)) {
	$rssAlbumList = $albumDB->getAlbumsByRoot($set_albumName);
}
else {
	$rssAlbumList = $albumDB->albumList;
}

foreach ($rssAlbumList as $album) {

	// Save time later.. if we can't read it, don't add it.
	if (!$gallery->user->canReadAlbum($album)) {
		continue;
	}

	// Increment counters
	$numAlbums++;
	$album->load($album->fields['name']);
	$numPhotos += $album->numPhotos(0, 1);

	$albumInfo = array(
		"!name" => $album->fields["name"],
		"link" => makeAlbumUrl($album->fields["name"]),
		"guid" => array($album->fields['guid'], array("isPermaLink" => "false")),
		"!date" => bestDate($album),
		"title" => htmlspecialchars($album->fields["title"]));

	// DATE TAGS

	$unixDate = $albumInfo["!date"];
	if (isset($unixDate)) {
		$albumInfo["pubDate"] = date("r", $unixDate);
		if ($gallery->app->rssDCDate == "yes") {
			$albumInfo["dc:date"] = makeDCDate($unixDate);
		}
	}

	// COMMENTS TAG

	if (method_exists($album, "canViewComments") 
	   && $album->canViewComments($gallery->user->uid)) {
		$albumInfo["comments"] = makeGalleryUrl("view_comments.php", 
		  array("set_albumName" => $album->fields["name"]));
	}

	// PHEED AND PHOTO TAGS

	if ($gallery->app->rssPhotoTag == "yes") {
		if (!$album->transient->photosloaded) {
			$album->load($album->fields["name"], TRUE);
		}

		list($subalbum, $highlight) = $album->getHighlightedItem();

		if ($highlight) {
			# makeAlbumURL is for the pretty page, getAlbumDirURL is for the image itself

			// $subalbum is either the current album, or the album which contains the
			// highlight, so it's always correct.
			$base = $subalbum->getAlbumDirURL("highlight");
			$albumInfo["photo:imgsrc"] = $highlight->thumbnail->getPath($base);
			$albumInfo["photo:thumbnail"] = $highlight->getPhotoPath($base);
			
			$width = $highlight->thumbnail->width;
			$height = $highlight->thumbnail->height;

			if ($gallery->app->rssBigPhoto == "no") {
				if ($width > 144) {
					$width = 144;
				}
			
				if ($height > 400) {
					$height = 400;
				}
			}

		$albumInfo["pb:thumb"] = array($highlight->thumbnail->getPath($base),
		array("height" => $height, "width" => $width));
		}
	}

	// INSET HTML IMAGES

	if (!isset($gallery->app->rssMode)) {
		$gallery->app->rssMode="basic";
	}

	if ($gallery->app->rssMode == "thumbs") {
		if (!$album->transient->photosloaded) {
			$album->load($album->fields["name"], TRUE);
		}
		
		$albumInfo["description"]  = $album->fields["description"] . '<p />';
		$albumInfo["description"] .= getThumbs($album);
	} elseif ($gallery->app->rssMode == "thumbs-with-captions") {
		if (!$album->transient->photosloaded) {
			$album->load($album->fields["name"], TRUE);
		}

		$albumInfo["description"]  = $album->fields["description"] . '<p />';
		$albumInfo["description"] .= getThumbsAndCaptions($album);
	} elseif ($gallery->app->rssMode == "highlight" && isset($highlight)) {
		$url = makeAlbumUrl($album->fields["name"]);
		$imgtag = $highlight->thumbnail->getTag($base, 0, 0, 'border=0');
		$albumInfo["description"]  = "<a href=\"$url\">$imgtag</a><br>";
		$albumInfo["description"] .= $album->fields["description"];
	} else { # mode = "basic"
		$albumInfo["description"] = $album->fields["description"];
	}

	$albumInfo["description"] = htmlspecialchars($albumInfo["description"]);

	array_push($albumList, $albumInfo);
}

usort($albumList, "albumSort");

unset($ha); $channel_image = $channel_width = $channel_height = "";
if (isset($gallery->app->rssHighlight) && $gallery->app->rssHighlight != "*") {
	foreach($albumList as $album) {
		if ($album["!name"] == $gallery->app->rssHighlight && isset($album["pb:thumb"])) {
			$ha = $album;
			break;
		}
	}
} elseif (isset($albumList[0]["pb:thumb"])) {
	$ha = $albumList[0];
}

if (isset($ha)) {
	$channel_image = $ha["pb:thumb"][0];
	$channel_image_width = $ha["pb:thumb"][1]["width"];
	$channel_image_height = $ha["pb:thumb"][1]["height"];
}

$total_str = pluralize_n2(ngettext(_("1 album"), _("%s albums"), $numAlbums), $numAlbums, _("no albums"));
$image_str = pluralize_n2(ngettext(_("1 photo"), _("%s photos"), $numPhotos), $numPhotos, _("no photos"));

$description = sprintf(_("%s in %s"), $image_str, $total_str);

header("Content-Type: application/xml");

$xml_header = 'xml version="1.0"';
if ($gallery->locale == 0) {
	$gallery->locale = 'ISO-8859-1';
}

echo '<' . '?xml version="1.0" encoding="' . $gallery->locale . '"?' . '>';

?>

<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:photo="http://www.pheed.com/pheed/" xmlns:pb="http://snaplog.com/backend/PhotoBlog.html">
	<channel>
		<title><?php echo htmlspecialchars($gallery->app->galleryTitle) ?></title>
		<link><?php echo $gallery->app->photoAlbumURL ?></link>
		<description><?php echo htmlspecialchars($description) ?></description>
<?php if (isset($gallery->app->default_language)) { ?>
		<language><?php echo ereg_replace("_", "-", $gallery->app->default_language) ?></language>
<?php } ?>
		<lastBuildDate><?php echo date("r"); ?></lastBuildDate>
<?php if (isset($gallery->app->adminEmail)) { ?>
		<managingEditor><?php echo $gallery->app->adminEmail ?></managingEditor>
		<webMaster><?php echo $gallery->app->adminEmail ?></webMaster>
<?php } ?>
		<generator>Gallery <?php echo $gallery->version; ?>, http://gallery.menalto.com/</generator>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<ttl>40</ttl>
<?php if ($channel_image) { ?>
		<image>
			<title><?php echo htmlspecialchars($gallery->app->galleryTitle) ?></title>
			<url><?php echo $channel_image ?></url>
			<link><?php echo $gallery->app->photoAlbumURL ?></link>
			<width><?php echo $channel_image_width ?></width>
			<height><?php echo $channel_image_height ?></height>
		</image>
<?php } ?>
<?php

$maxAlbums = 0;
foreach($albumList as $album) {
	
	// If we've hit the max album limit, bust out.
	if($maxAlbums > $gallery->app->rssMaxAlbums) {
		break;
	}

	// Retrieve
	if (isset($gallery->app->rssVisibleOnly)) {
		$myAlbum = $albumDB->getAlbumByName($album['!name']);
		if ($myAlbum->isHiddenRecurse() || !$myAlbum->canReadRecurse($gallery->user->uid)) {
			continue;
		}
	} 

	// Only increment after we've determined that the album
	// is valid to add to the feed
	$maxAlbums++;

	echo "\t\t<item>\n";
	foreach($album as $tag => $info) {
		# meta fields that should not be printed in the feed
		# start with bang.
		if (ereg("^!", $tag)) {
			continue;
		}
		
		if (is_array($info)) {
			echo "\t\t\t<$tag";
			foreach($info[1] as $attr => $value) {
				echo ' ' . $attr . '="' . $value . '"';
			}
			echo ">$info[0]</$tag>\n";
		} else {
			echo "\t\t\t<$tag>$info</$tag>\n";
		}
	}
	
	echo "\t\t</item>\n";
}

?>
	</channel>
</rss>

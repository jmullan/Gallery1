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
/* CONFIGURATION SECTION */

# mode
#
# In-set HTML photo modes.  If you turn this on, <img> tags will be
# placed in each item's description.  Options for this are:
#
# basic - no images at all
# highlight - only the highlighted image
# thumbs - all thumbnails
# thumbs-with-captions - all thumbnails AND captions for each
# mdm - the mystery option!
# 
# Note that this setting has no effect on the photo and pb elements,
# discussed above.  Also note that 'thumbs' and 'thumbs-with-captions'
# have been known to crash NetNewsWire.

$mode = "highlight";

# highlightAlbum
# 
# Which album's highlight picture should be the highlight for the
# whole RSS feed?  If set to '*' it will select the highlight image
# of the most-recently-created album.
# 
# If set to "", it will turn off the channel highlight feature.
# (See noBigPhoto below for more info).

$highlightAlbum = "";

# onlyFindable
#
# Suppose you have an album with 5 sub-albums, and you don't want 
# anyone to look at any of them, so you mark the big album as
# hidden.  Technically, users can still look at the sub-albums, but
# they can't actually _find_ them.
#
# Given that definition of findable, turn this off if you want
# only finadable albums to be visible.

$onlyFindable = TRUE;

# noPhotoTag
#
# There are two RSS extensions that allow for images representing
# items to be linked from feeds.  They are:
# 
# From Pheed Spec (see http://www.pheed.com/pheed/):
# photo:imgsrc and photo:thumbnail
# 
# From PhotoBlog Spec (see http://snaplog.com/backend/PhotoBlog.html):
# pb:thumb
# 
# These elements shouldn't be damaging at all, even to clients
# unaware of their meaning, but if you want to turn them off, you can.

$noPhotoTag = FALSE;

# noDCDate
#
# Having both pubDate and dc:date elements for an item may cause it
# to not validate.  If you absolutely MUST have strictly valid RSS, 
# set this option to TRUE.  However, doing this may break some older
# RSS readers that don't fully support RSS 2.0.

$noDCDate = FALSE;

# noBigPhotos
#
# The whole channel can have one highlight image, but it can be no
# more than 144 pixels wide and 400 pixels tall.  Since most galleries
# have thumbnails 150 pixels wide (for landscape photos), a small
# amount of clipping may occur on some clients.  So most people won't
# really even notice, we lie about the sizes if the thumbnail is too
# big.
#
# If some clients complain that the channel thumbnail is garbled
# in their news reader because of the bogus size, you can set this to
# false to turn that feature off, but it will cause your RSS feed
# to not validate.

$noBigPhoto = TRUE;

/* END OF CONFIGURATION SECTION */

require(dirname(__FILE__) . '/init.php');

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
	if (isset($album->fields["clicks_date"])) {
		return $album->fields["clicks_date"];
	} else {
		return $album->fields["last_mod_time"];
	}
}

function removeUnprintable($string) {
	return preg_replace("/[^[:print:]]/", "", $string);
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

$numAlbums = 0;
$albumList = array();
if (method_exists($albumDB, "getCachedNumPhotos")) {
	$numPhotos = $albumDB->getCachedNumPhotos($gallery->user);
} else {
	$numPhotos = $albumDB->numPhotos($gallery->user);
}

foreach ($albumDB->albumList as $album) {
	if ($onlyFindable) {
		if($album->isHiddenRecurse()) {
			continue;
		}
	} else {
		if(!$gallery->user->canReadAlbum($album)) {
			continue;
		}
	}

	$numAlbums++;

	$albumInfo = array(
		"!name" => $album->fields["name"],
		"link" => makeAlbumUrl($album->fields["name"]),
		"guid" => makeAlbumUrl($album->fields["name"]),
		"!date" => bestDate($album),
		"title" => htmlspecialchars(removeUnprintable($album->fields["title"])));

	# DATE TAGS

	$unixDate = $albumInfo["!date"];
	if (IsSet($unixDate)) {
		$albumInfo["pubDate"] = date("r", $unixDate);
		if (! $noDCDate) {
			$albumInfo["dc:date"] = makeDCDate($unixDate);
		}
	}

	# COMMENTS TAG

	if (method_exists($album, "canViewComments") 
	   && $album->canViewComments($gallery->user->uid)) {
		$albumInfo["comments"] = makeGalleryUrl("view_comments.php", 
		  array("set_albumName" => $album->fields["name"]));
	}

	# PHEED AND PHOTO TAGS

	if (!$noPhotoTag) {
		if (! $album->transient->photosloaded) {
			$album->load($album->fields["name"], TRUE);
		}

		list($subalbum, $highlight) = $album->getHighlightedItem();

		if($highlight) {
			# makeAlbumURL is for the pretty page, getAlbumDirURL is for the image itself
			$base = $album->getAlbumDirURL("highlight");
			$albumInfo["photo:imgsrc"] = $highlight->thumbnail->getPath($base);
			$albumInfo["photo:thumbnail"] = $highlight->getPhotoPath($base);
			
			$width = $highlight->thumbnail->width;
			$height = $highlight->thumbnail->height;

			if ($noBigPhoto) {
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

	# INSET HTML IMAGES

	if ($mode == "thumbs" || $mode == "mdm") {
		if (! $album->transient->photosloaded) {
			$album->load($album->fields["name"], TRUE);
		}
		
		$albumInfo["description"]  = removeUnprintable($album->fields["description"]) . '<p />';
		$albumInfo["description"] .= getThumbs($album);
	} elseif ($mode == "thumbs-with-captions") {
		if (! $album->transient->photosloaded) {
			$album->load($album->fields["name"], TRUE);
		}

		$albumInfo["description"]  = removeUnprintable($album->fields["description"]) . '<p />';
		$albumInfo["description"] .= getThumbsAndCaptions($album);
	} elseif ($mode == "highlight" && isset($highlight)) {
		$url = makeAlbumUrl($album->fields["name"]);
		$imgtag = $highlight->thumbnail->getTag($base, 0, 0, 'border=0');
		$albumInfo["description"]  = "<a href=\"$url\">$imgtag</a> ";
		$albumInfo["description"] .= removeUnprintable($album->fields["description"]);      
	} else { # mode = "basic"
		$albumInfo["description"] = removeUnprintable($album->fields["description"]);
	}

	$albumInfo["description"] = htmlspecialchars($albumInfo["description"]);

	array_push($albumList, $albumInfo);
}

usort($albumList, "albumSort");

unset($ha); $channel_image = $channel_width = $channel_height = "";
if (isset($highlightAlbum) && $highlightAlbum != "*") {
	foreach($albumList as $album) {
		if ($album["!name"] == $highlightAlbum && isset($album["pb:thumb"])) {
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

if (function_exists('pluralize_n2')) {
	$total_str = pluralize_n2($numAlbums, _("1 album"), _("albums"), _("no albums"));
	$image_str = pluralize_n2($numPhotos, _("1 photo"), _("photos"), _("no photos"));
} else {
	/* Probably older version of Gallery */
	$total_str = pluralize($numAlbums, "album", "no");
	$image_str = pluralize($numPhotos, "photo", "no");
}

$description = sprintf(_("%s in %s"), $image_str, $total_str);

@header("Content-Type: application/xml");

$xml_header = 'xml version="1.0"';
if($gallery->locale == 0) {
	$gallery->locale = 'ISO-8859-1';
}

echo '<' . '?xml version="1.0" encoding="' . $gallery->locale . '"?' . '>';

?>

<rss version="2.0" xmlns="http://blogs.law.harvard.edu/tech/rss" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:photo="http://www.pheed.com/pheed/" xmlns:pb="http://snaplog.com/backend/PhotoBlog.html">
	<channel>
		<title><?php echo htmlspecialchars($gallery->app->galleryTitle) ?></title>
		<link><?php echo $gallery->app->photoAlbumURL ?></link>
		<description><?php echo htmlspecialchars($description) ?></description>
<?php if (isset($gallery->app->default_language)) { ?>
		<language><?php echo preg_replace("/_/", "-", $gallery->app->default_language) ?></language>
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

foreach($albumList as $album) {
	echo "\t\t<item>\n";
	
	foreach($album as $tag => $info) {
		# meta fields that should not be printed in the feed
		# start with bang.
		if(ereg("^!", $tag)) {
			continue;
		}
		
		if(is_array($info)) {
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

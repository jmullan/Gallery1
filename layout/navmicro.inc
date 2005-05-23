<?php /* $Id$ */ ?>
<?php
global $navigator;
if(!includeTemplate('navmico.tpl')) {
	// Calculate the beginning and ending of the navigator range
	$begin = 0;
	$navpage = sizeof($navigator["allIds"]) - 1;
	$navpage=array_search($navigator["id"], $navigator["allIds"]);
	$navcount = sizeof($navigator["allIds"]);

	// If the border color is not passed in, we do a white one
	if ($navigator["bordercolor"]) {
		$borderIn = $navigator["bordercolor"];
	} else {
		$borderIn = "#FFFFFF";
	}

	if (isset($navigator["fullWidth"]) && isset($navigator["widthUnits"])) {
		$width=' width="'. $navigator["fullWidth"] . $navigator["widthUnits"] .'"';
	}

	$firstPage = $navigator["allIds"][0];
	$lastPage = $navigator["allIds"][$navcount-1];

/* If first/last thumbs are enabled, generate them here. */
if ( !strcmp($gallery->album->fields["nav_thumbs_first_last"],"yes") ) {
	$navThumbsFirst = '<a href="'. makeAlbumUrl($gallery->session->albumName, $firstPage) .'">';
	$navThumbsFirst .= $gallery->album->getThumbnailTagById($firstPage,
	   $gallery->album->fields["nav_thumbs_size"],"class=\"nav_micro_first_img\"");
	$navThumbsFirst .='</a>&nbsp';

	$navThumbsLast ='<a href="'. makeAlbumUrl($gallery->session->albumName, $lastPage) .'">';
	$navThumbsLast .= $gallery->album->getThumbnailTagById($lastPage,
	   $gallery->album->fields["nav_thumbs_size"],"class=\"nav_micro_last_img\"");
	$navThumbsLast .='</a>&nbsp';

/* If first/last thumbs are disabled, fill each with a space. */
} else {
	$navThumbsFirst = '&nbsp';
	$navThumbsLast = '&nbsp';
}

/* If the "Dynamic" style is selected, generate it */
if ( !strcmp($gallery->album->fields["nav_thumbs_style"],"dynamic") ) {

	/* Create a list of the previous thumbs, starting at the farthest back. */
	$navThumbsPrev = '';
	for ($i = $gallery->album->fields["nav_thumbs_prev_shown"]; $i >= 1; $i--) {
		if ( $navpage-$i < 0 ) { continue; }
		$prevPage = $navigator["allIds"][$navpage-$i];
		$navThumbsPrev .= '<a href="'. makeAlbumUrl($gallery->session->albumName, ($prevPage)).'">';
		$navThumbsPrev .= $gallery->album->getThumbnailTagById($prevPage,
		   $gallery->album->fields["nav_thumbs_size"],"class=\"nav_micro_img\"");
		$navThumbsPrev .='</a>&nbsp&nbsp';
	}
	
	/* Create a list of the next thumbs, starting from right after current. */
	$navThumbsNext = '';
	for ($i = 1; $i <= $gallery->album->fields["nav_thumbs_next_shown"]; $i++) {
		if ( $navpage+$i > $navcount-1 ) { break; }
		$nextPage = $navigator["allIds"][$navpage+$i];
		$navThumbsNext .= '&nbsp&nbsp<a href="'. makeAlbumUrl($gallery->session->albumName, ($nextPage)).'">';
		$navThumbsNext .= $gallery->album->getThumbnailTagById($nextPage,
		   $gallery->album->fields["nav_thumbs_size"],"class=\"nav_micro_img\"");
		$navThumbsNext .='</a>';
	}
	
	/* Create the current thumb. */
	$navThumbsCur = $gallery->album->getThumbnailTagById($navigator["allIds"][$navpage],($gallery->album->fields["nav_thumbs_size"]
	 +$gallery->album->fields["nav_thumbs_current_bonus"]),"class=\"nav_micro_current_img\"");
}

/* If style is "Fixed" begin generation of fixed micro thumb navigation */
if ( !strcmp($gallery->album->fields["nav_thumbs_style"],"fixed") ) {
	
	/* Add previous + next to get our image block size */
	$navBlockSize = $gallery->album->fields["nav_thumbs_next_shown"]+$gallery->album->fields["nav_thumbs_prev_shown"];
	if ( $navBlockSize <= 2 ) { $navBlockSize += 1; }

	/* Determine our starting point based on the image we are at. */
	if ( $navpage < $navBlockSize-1 ) {
		$startThumb = 0;

	/* This will display the final images with the same number of thumbs as all others */
	/* Commenting it out allows the end to display just the remaining images. */
/* Commented to only display remaining images at end
	} elseif ( $navpage > $navcount-($navBlockSize-1) ) {
		$startThumb = ($navcount-$navBlockSize) ? ($navcount-$navBlockSize) : 0;
*/

	/* This is what displays anything that isn't at the beginning or end */
	} else {
		$X = $navBlockSize-2;
		$startThumb = $X * (int)(($navpage-1)/$X);
	}

	/* After we have decided on a starting point, loop through and create them */
	$navBlogThumb = '';
	for ($i = $startThumb; $i < $navBlockSize+$startThumb; $i++) {
		/* In case we are close to the end, stop generating thumbs */
		if ( $i >= $navcount ) { break; }
		$navBlogThumb .= '<a href="'. makeAlbumUrl($gallery->session->albumName,
		   ($navigator["allIds"][$i])).'">';

		/* Current image gets special CSS */
		if ( $i == $navpage ) {
			$navBlogThumb .= $gallery->album->getThumbnailTagById($navigator["allIds"][$i],
        	           $gallery->album->fields["nav_thumbs_size"]+$gallery->album->fields["nav_thumbs_current_bonus"]
			   ,"class=\"nav_micro_current_img\"");
		/* All other images get another CSS */
		} else {
			$navBlogThumb .= $gallery->album->getThumbnailTagById($navigator["allIds"][$i],
        	           $gallery->album->fields["nav_thumbs_size"],"class=\"nav_micro_img\"");
		}
		$navBlogThumb .= '</a>';
		$navBlogThumb .= '&nbsp&nbsp';
	}
}
?>

<!-- Photo Navigator -->
<table<?php echo $width ?> border="0" cellspacing="0" cellpadding="0" class="modnavbox">
<tr>
	<td colspan="5">
		<table width="100%" border="0" align="center">
			<tr valign="bottom" align="center">
				<td><?php echo $navThumbsFirst; ?></td>
<?php if ( !strcmp($gallery->album->fields["nav_thumbs_style"],"dynamic") ) { ?>
			        <td nowrap width="40%" align="right"><?php echo $navThumbsPrev; ?></td>
			        <td width="5"><?php echo $navThumbsCur; ?></td>
			        <td nowrap width="40%" align="left"><?php echo $navThumbsNext; ?></td>
<?php } elseif ( !strcmp($gallery->album->fields["nav_thumbs_style"],"fixed") ) { ?>
				<td colspan="3"><?php echo $navBlogThumb; ?></td>
<?php } ?>
				<td><?php echo $navThumbsLast; ?></td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php } ?>

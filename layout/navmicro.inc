<?php
/* $Id$ */

global $navigator, $full, $gallery, $navc;

if($gallery->album->fields['nav_thumbs'] == 'yes') {
	$navc++;
}

if(includeTemplate('navmicro.tpl')) {
	exit;
}

// Calculate the beginning and ending of the navigator range
$begin		= 0;
$navcount	= sizeof($navigator['allIds']);
$navpage	= $navcount - 1;
$navpage	= array_search($navigator['id'], $navigator['allIds']);

$firstPage	= $navigator['allIds'][0];
$lastPage	= $navigator['allIds'][$navcount-1];

// If the border color is not passed in, we do a white one
if ($navigator['bordercolor']) {
	$borderIn = $navigator['bordercolor'];
}
else {
	$borderIn = "#FFFFFF";
}

if (isset($navigator['fullWidth']) && isset($navigator['widthUnits'])) {
	$width=' width="'. $navigator['fullWidth'] . $navigator['widthUnits'] .'"';
}

/* If first/last thumbs are enabled, generate them here. */
$fpUrl	= makeAlbumUrl($gallery->session->albumName, $firstPage, ($full) ? array('full' => 1) : NULL) ;
$lpUrl	= makeAlbumUrl($gallery->session->albumName, $lastPage, ($full) ? array('full' => 1) : NULL);

if ( $gallery->album->fields['nav_thumbs_first_last'] == "yes" ) {
	$fpImage = $gallery->album->getThumbnailTagById(
					$firstPage,
					$gallery->album->fields['nav_thumbs_size'],
					array('class' => 'nav_micro_first_img'));

	$navThumbsFirst = galleryLink($fpUrl, $fpImage, array(), '', false, false);


	$lpImage = $gallery->album->getThumbnailTagById(
					$lastPage,
					$gallery->album->fields['nav_thumbs_size'],
					array('class' => 'nav_micro_last_img'));

	$navThumbsLast = galleryLink($lpUrl, $lpImage, array(), '', false, false);

}
else {
	$navThumbsFirst = '';
	$navThumbsLast  = '';
}

/* If the "Dynamic" style is selected, generate it */
if ($gallery->album->fields['nav_thumbs_style'] == "dynamic") {

	/* Create a list of the previous thumbs, starting at the farthest back. */
	$navThumbsPrev = '';

	for ($i = $gallery->album->fields['nav_thumbs_prev_shown']; $i >= 1; $i--) {
		if ( $navpage-$i < 0 ) {
			continue;
		}

		$prevPage	= $navigator['allIds'][$navpage-$i];
		$ppUrl		= makeAlbumUrl($gallery->session->albumName, $prevPage, ($full) ? array('full' => 1) : NULL);

		$ppImage 	= $gallery->album->getThumbnailTagById(
							$prevPage,
							$gallery->album->fields['nav_thumbs_size'],
							array('class' => 'nav_micro_img'));

		$navThumbsPrev .= galleryLink($ppUrl, $ppImage, array(), '', false, false);
	}

	/* Create a list of the next thumbs, starting from right after current. */
	$navThumbsNext = '';
	for ($i = 1; $i <= $gallery->album->fields['nav_thumbs_next_shown']; $i++) {
		if ( $navpage+$i > $navcount-1 ) {
			break;
		}

		$nextPage	= $navigator['allIds'][$navpage+$i];
		$npUrl		= makeAlbumUrl($gallery->session->albumName, $nextPage, ($full) ? array('full' => 1) : NULL);

		$npImage	= $gallery->album->getThumbnailTagById(
							$nextPage,
							$gallery->album->fields['nav_thumbs_size'],
							array('class' => 'nav_micro_img'));

		$navThumbsNext .= galleryLink($npUrl, $npImage, array(), '', false, false);
	}

	/* Create the current thumb. */
	$navThumbsCur = $gallery->album->getThumbnailTagById(
						$navigator['allIds'][$navpage],
						($gallery->album->fields['nav_thumbs_size'] + $gallery->album->fields['nav_thumbs_current_bonus']),
						array('class' => 'nav_micro_current_img'));
}

/* If style is "Fixed" begin generation of fixed micro thumb navigation */
if ($gallery->album->fields['nav_thumbs_style'] == "fixed") {

	/* Add previous + next to get our image block size */
	$navBlockSize = $gallery->album->fields['nav_thumbs_next_shown']+$gallery->album->fields['nav_thumbs_prev_shown'];
	if ( $navBlockSize <= 2 ) { $navBlockSize += 1; }

	/* Determine our starting point based on the image we are at. */
	if ( $navpage < $navBlockSize-1 ) {
		$startThumb = 0;

		/* This will display the final images with the same number of thumbs as all others
		Commenting it out allows the end to display just the remaining images.
		Commented to only display remaining images at end
	}
	elseif ( $navpage > $navcount-($navBlockSize-1) ) {
		$startThumb = ($navcount-$navBlockSize) ? ($navcount-$navBlockSize) : 0;
	*/

	/* This is what displays anything that isn't at the beginning or end */
	}
	else {
		$X = $navBlockSize-2;
		$startThumb = $X * (int)(($navpage-1)/$X);
	}

	/* After we have decided on a starting point, loop through and create them */
	$navBlogThumb = '';
	for ($i = $startThumb; $i < $navBlockSize+$startThumb; $i++) {
		/* In case we are close to the end, stop generating thumbs */
		if ( $i >= $navcount ) {
			break;
		}

		$navBlogUrl = makeAlbumUrl($gallery->session->albumName, ($navigator['allIds'][$i]));

		/* Current image gets special CSS */
		if ( $i == $navpage ) {
			$navBlogThumbImage = $gallery->album->getThumbnailTagById(
								$navigator['allIds'][$i],
								$gallery->album->fields['nav_thumbs_size'] + $gallery->album->fields['nav_thumbs_current_bonus'],
								array('class' => 'nav_micro_current_img'));
			/* All other images get another CSS */
		}
		else {
			$navBlogThumbImage = $gallery->album->getThumbnailTagById(
								$navigator['allIds'][$i],
								$gallery->album->fields['nav_thumbs_size'],
								array('class' => 'nav_micro_img'));
		}

		$navBlogThumb .= galleryLink($navBlogUrl, $navBlogThumbImage, array(), '', false, false);
	}
}

if($gallery->album->fields['nav_thumbs'] == 'yes') {
	if($navc < 2) {
		$id = 'id="g-navtable"';

		if ($navpage > 0) {
			$prevPage = $navigator["allIds"][$navpage-1];
			$ppUrl    = makeAlbumUrl($gallery->session->albumName, $prevPage, ($full) ? array('full' => 1) : NULL);

			echo galleryLink($ppUrl, '', array('id' => "g-navmicro-backward_$navc"));
		}

		if ($navpage < $navcount-1) {
			$nextPage = $navigator["allIds"][$navpage+1];
			$npUrl	  = makeAlbumUrl($gallery->session->albumName, $nextPage, ($full) ? array('full' => 1) : NULL);

			echo galleryLink($npUrl, '', array('id' => "g-navmicro-forward_$navc"));
		}
	}
	else {
		$id = 'id="g-navtable-bottom"';
	}
}
else {
	$id = '';
}

?>
<!-- Microthumb Photo Navigator -->
<?php if ($gallery->album->fields['nav_thumbs_style'] == 'dynamic') :?>

<table width="100%" align="center" <?php echo $id; ?> dir="<?php echo $gallery->direction; ?>">
<tr>
	<td><?php echo $navThumbsFirst; ?></td>
	<td nowrap width="40%" class="right"><?php echo $navThumbsPrev; ?></td>
	<td width="5"><?php echo $navThumbsCur; ?></td>
	<td nowrap width="40%" align="left"><?php echo $navThumbsNext; ?></td>
	<td><?php echo $navThumbsLast; ?></td>
</tr>
</table>

<?php else : ?>

<table width="100%" align="center" <?php echo $id; ?> dir="<?php echo $gallery->direction; ?>">
<tr>
	<td><?php echo $navThumbsFirst; ?></td>
	<td colspan="3" align="center"><?php echo $navBlogThumb; ?></td>
	<td><?php echo $navThumbsLast; ?></td>
</tr>
</table>
<?php endif ?>
<?php
if($gallery->app->comments_addType != "inside" && $gallery->album->fields['nav_thumbs'] == 'yes') {
	echo jsHtml('arrowkey_nav.js');
}

?>

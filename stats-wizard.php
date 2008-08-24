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
 *
 */

if (!isset($gallery->version)) {
	require_once(dirname(__FILE__) . '/init.php');
}

// Security check
if (!$gallery->user->isAdmin()) {
	header("Location: " . makeAlbumHeaderUrl());
	exit;
}

require_once(dirname(__FILE__) . '/includes/stats/stats.inc.php');

$iconElements = array();

$iconElements[] = galleryIconLink(
				makeGalleryUrl("admin-page.php"),
				'navigation/return_to.gif',
				gTranslate('core', "Return to _admin page"));

$iconElements[] = galleryIconLink(
				makeAlbumUrl(),
				'navigation/return_to.gif',
				gTranslate('core', "Return to _gallery"));

$iconElements[] = LoginLogoutButton(makeGalleryUrl());

$adminbox['text']	= '<span class="g-title">'.  gTranslate('core', "Assemble your Gallery statistic") .'</span>';
$adminbox['commands']	= makeIconMenu($iconElements, 'right');

$breadcrumb['text'][] = languageSelector();

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
  <title><?php echo clearGalleryTitle(gTranslate('core', "Statistic Wizard")) ?></title>
  <?php common_header(); ?>
</head>
<body>
<?php
}

includeTemplate("gallery.header");
includeLayout('adminbox.inc');
includeLayout('breadcrumb.inc');

$url = makeGalleryHeaderUrl('stats.php');
$url .= (!$GALLERY_EMBEDDED_INSIDE) ? '?' : '';
?>
<div class="g-content-popup">
<?php
/* note: the script is below as the header of the environment needs to loaded before. */
?>
<script type="text/javascript">
  function updateUrl() {
	var value;
	var url;
	url='<?php echo $url; ?>';

	/* This javascript goes through all elements of the form 'stats_form'
	** depending if set or not it generates an string that represents the parameters for stats.php
	*/
	for(var i = 0; i < document.stats_form.length; i++) {
		value = false;
		/* special case */
		if ((document.stats_form.elements[i].name == 'cols' || document.stats_form.elements[i].name == 'rows') &&
			document.stats_form.showGrid.checked == false) {
			continue;
		}

		if(document.stats_form.elements[i].name == 'name' ||
		   document.stats_form.elements[i].name == 'include')
		{
			continue;
		}

		switch(document.stats_form.elements[i].type) {
			case 'submit':
				continue;
			break;

			case 'checkbox':
				if(document.stats_form.elements[i].checked) {
				value = 1;
				}
			break;

			case 'radio':
				if (document.stats_form.elements[i].checked) {
				value = document.stats_form.elements[i].value;
				}
			break;

			default:
				value = document.stats_form.elements[i].value;
			break;
		}

		if (value) {
			url = url + '&'+ document.stats_form.elements[i].name +'=' + value;
		}
	}
	document.getElementById('stats_url').value = url;
}
</script>

<?php
	echo makeFormIntro('stats.php', array('name' => 'stats_form', 'onClick' => 'updateUrl()'));
?>
	<table width="100%" class="g-stats-wizard">
	<tr>
	  <td width="50%">
		<?php stats_showBlock($stats['types'], gTranslate('core',"Type")); ?>
	  </td>

	  <td width="50%">
		<?php stats_showBlock($stats['options'], gTranslate('core',"Options")); ?>
	  </td>
	</tr>
	<tr>
	  <td width="50%">
		<?php stats_showBlock($stats['layout'], gTranslate('core',"Layout")); ?>
	  </td>

	  <td width="50%">
		<?php stats_showBlock($stats['filter'], gTranslate('core',"Filter by Capture Date")); ?>
	  </td>

	</tr>
	</table>
	<br>
	<?php echo gSubmit('submitbutton', gTranslate('core', "_Show statistics")); ?>
	</form>

	<br>
<?php
	echo gTranslate('core',"Maybe your want to use your OWN statistics somewhere .. Just copy and paste the url from this textbox.");
	?>

	<input type="text" id="stats_url" size="150" value="" readonly>

	<script type="text/javascript">
	  // Run the script at the when page is showed.
	  // We could do this onLoad, but this doesnt work embedded.
	  updateUrl();
	</script>

</div>

<?php
includeTemplate('overall.footer');

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>

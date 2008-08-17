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
 * $Id: welcome.inc 13903 2006-06-28 13:46:35Z jenst $
*/


$gallerySanity = gallerySanityCheck();
$install = ($gallerySanity == 'unconfigured') ? true : false;

?>
<input type="hidden" name="this_page" value="welcome">
<input type="hidden" name="next_page" value="check">

<div class="g-header">
	<div class="g-pagetitle"><?php echo gTranslate('config', "Gallery configuration wizard"); ?></div>
</div>

<div class="g-config">
	<?php include(GALLERY_BASE . '/includes/setup/stepbar.inc'); ?>

	<div class="g-config-content">
		<div class="g-config-step floatleft"><?php echo gTranslate('config', "Welcome"); ?></div>

		<div class="floatright right">
			<?php echo gSubmit('go_next', gTranslate('config', "_Next Step >>")); ?>
			<?php if ($install): ?>
			<div class="g-emphasis" style="margin-top: 2px;">
				<?php echo gTranslate('config', "Installation Type:"); ?>
				<select name="inst_type">
				   <option value="easy"><?php echo gTranslate('config', "easy"); ?></option>
				   <option value="advanced"><?php echo gTranslate('config', "advanced"); ?></option>
				</select>
			</div>
			<?php
			$onThisPage['inst_type'] = true;
			$preserve['inst_type'] = true;
			endif; ?>
		</div>

		<div class="clear"></div>

		<h1><?php
			if($install) {
				printf(gTranslate('config',"Installation of '%s' version: %s"), Gallery(), "<i>$gallery->version</i>");
			}
			else {
				printf(gTranslate('config',"Configuration of '%s' version: %s"), Gallery(), "<i>$gallery->version</i>");
			}
		?></h1>

		<div class="g-config-main">
			<?php
				echo gTranslate('config', "Welcome to the Setup of Gallery.");
				if ($install) {
					echo "\n<br>";
					echo gTranslate('config', "Gallery must be configured before you can use it.");
			?>

			<table class="g-sitedesc" cellspacing="10">
				<tr>
					<td><?php echo gTranslate('config', "1."); ?></td>
					<td>
						<?php echo gTranslate('config', "Create an empty file config.php in the main folder of your Gallery."); ?><br>
						<?php echo gTranslate('config', "You can optionally create an empty file .htaccess which is used for short urls and other settings. (This is not supported by all hosts.)"); ?>
					</td>
				</tr>
				<tr>
					<td><?php echo gTranslate('config', "2."); ?></td>
					<td><?php echo gTranslate('config', "Create an albums folder for your pictures and movies. This must be in your webfolder. We recommend that you don't put it in your Gallery folder, but that's up to you."); ?></td>
				</tr>
				<tr>
					<td colspan="2" class="g-emphasis"><?php echo gTranslate('config', "Make sure that both files and the folder are read and writeable for your webserver!"); ?></td>
				</tr>
			</table>

			<?php
				}
				else if(isset($gallery->app->config_version) &&
					$gallery->app->config_version != $gallery->config_version)
				{
					echo infobox(array(array(
						'type' => 'warning',
						'text' => gTranslate('config', "Your Gallery needs a reconfiguration.") .
						'<p>' .
						gTranslate('config', "Your Gallery settings were configured with an older version of Gallery, and are out of date. Please re-run the Configuration Wizard!") .
						'</p>'
					)));
				}
			?>
			<p>

			<?php
				echo gTranslate('config', "To configure/install Gallery there are only five steps in this simple procedure.");
			?>
			</p>

			<table cellspacing="5">
			<?php if ($install): ?>
			<tr>
				<td class="g-emphasis nowrap"><?php echo gTranslate('config', "Start:"); ?></td>
				<td><?php echo gTranslate('config', "Basic instructions and the choice for installation type."); ?></td>
			</tr>
			<?php endif; ?>
			<tr>
				<td class="g-emphasis nowrap"><?php echo gTranslate('config', "Step 1:"); ?></td>
				<td><?php echo gTranslate('config', "The installer will check to see if your web space can run Gallery."); ?></td>
			</tr>
			<tr>
				<td class="g-emphasis nowrap"><?php echo gTranslate('config', "Step 2:"); ?></td>
				<td><?php echo gTranslate('config', "You will be asked to enter settings needed to run Gallery such as the location of Gallery, language settings, etc."); ?></td>
			</tr>
			<tr>
				<td class="g-emphasis nowrap"><?php echo gTranslate('config', "Step 3:"); ?></td>
				<td><?php echo gTranslate('config', "Define default settings for new albums. Predefined values are provided or you can customize them as needed."); ?></td>
			</tr>
			<tr>
				<td class="g-emphasis nowrap"><?php echo gTranslate('config', "Step 4:"); ?></td>
				<td><?php echo gTranslate('config', "Installation confirmation."); ?></td>
			</tr>
			<tr>
				<td class="g-emphasis nowrap"><?php echo gTranslate('config', "Step 5:"); ?></td>
				<td><?php echo gTranslate('config', "Saving the configuration."); ?></td>
			</tr>
			</table>
		</div>

		<br>

		<?php
			printf(gTranslate('config',"If you experience problems, you can find help on the %sGallery Help Page%s."),
						'<a href="http://gallery.sourceforge.net/help.php" target="_blank">', '</a>');
		?>
		<div class="floatright right">
			<?php echo gSubmit('go_next', gTranslate('config', "_Next Step >>")); ?>
		</div>
		<div class="clear"></div>
	</div>

	<div class="clear"></div>
</div>

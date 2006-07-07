<?php
/*
* Gallery - a web based photo album viewer and editor
* Copyright (C) 2000-2006 Bharat Mediratta
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
* $Id: check.inc 13903 2006-06-28 13:46:35Z jenst $
*/
?>
<?php

$gallerySanity = gallerySanityCheck();
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

        <div class="floatright">
        	<?php echo gSubmit('go_next', '_Next >>'); ?>
        </div>

        <div class="clear"></div>

        <h1><?php printf(gTranslate('config',"Installation of '%s' version: %s"), Gallery(), "<i>$gallery->version</i>"); ?></h1>

        <div class="g-config-main">
            <?php
                echo gTranslate('config', "Welcome to the configuration wizard of Gallery.");
                if($gallerySanity == 'unconfigured') {
                    echo gTranslate('core', "Gallery must be configured before you can use it.");
                ?>
                <table class="g-sitedesc">
                <tr>
                    <td><?php echo gTranslate('core', "1."); ?></td>
                    <td><?php echo gTranslate('core', "Create an empty file .htaccess and an empty file config.php in the mainfolder of your Gallery."); ?></td>
                </tr>
                <tr>
                    <td><?php echo gTranslate('core', "2."); ?></td>
                    <td><?php echo gTranslate('core', "Create an albums folder for your pictures and movies. This must be in your webfolder. We recommend not to "); ?></td>
                </tr>
                <tr>
                    <td colspan="2" class="g-emphasis"><?php echo gTranslate('core', "Make sure that both files and the folder are read and writeable for your webserver !"); ?></td>
                </tr>
                </table>

                <br>
                <?php
                    printf(gTranslate('config',"If you experience problems, you can find help on the %sGallery Help Page%s."),
                        '<a href="http://gallery.sourceforge.net/help.php">', '</a>');
                }
                else if($gallerySanity == 'reconfigure') {
                    echo infobox(array(array(
                        'type' => 'warning',
                        'text' => gTranslate('config', "Your Gallery needs a reconfiguration,") .
                                  '<p>' .
                                   gTranslate('config', "Your Gallery settings were configured with an older version of Gallery, and are out of date. Please re-run the Configuration Wizard!") .
                                  '</p>'
                        )));
                    echo "\n<p>";
                    printf(gTranslate('config',"If you experience problems, you can find help on the %sGallery Help Page%s."),
                        '<a href="http://gallery.sourceforge.net/help.php">', '</a>');

                    echo "\n</p>";
                }

                echo "\n<br>";
                echo gTranslate('config', "To configure/install Galery there are only five steps in this simple procedure.");
            ?>

            <table>
            <tr>
                <td class="g-emphasis nowrap">Step 1:</td>
                <td>The installer will check to see if your web space can run Gallery.</td>
            </tr>
            <tr>
                <td class="g-emphasis nowrap">Step 2:</td>
                <td>You will be asked to enter settings needed to run Gallery such as the location of Gallery, language settings, etc.</td>
            </tr>
            <tr>
                <td class="g-emphasis nowrap">Step 3:</td>
                <td>Define default settings for new albums. Predefined values are provided or you can customize them as needed.</td>
            </tr>
            <tr>
                <td class="g-emphasis nowrap">Step 4:</td>
                <td>Installation confirmation</td>
            </tr>
            <tr>
                <td class="g-emphasis nowrap">Step 5:</td>
                <td>Saving the configuration</td>
            </tr>
            </table>
        </div>

        <div class="clear"></div>
    </div>

    <div class="clear"></div>
</div>



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
 */
?>

<script type="text/javascript" src="<?php echo makeGalleryUrl('js/toggle.js.php'); ?>"></script>

<input type="hidden" name="this_page" value="check">
<input type="hidden" name="next_page" value="constants">

<div class="header"><?php echo sprintf (gTranslate('config', "Gallery Configuration Wizard: Step %d"),1); ?></div>

<div class="sitedesc">
<?php
echo gTranslate('config', "This is the Gallery system check page.") . "  ";
echo gTranslate('config', "This page will validate your installation to make sure that you have all the necessary components and permissions on this system and that everything is configured reasonably.") ;

echo '<br><br>';

$diagUrl='diagnostics.php';
$diagnostics_page='<a href="'. $diagUrl .'">' . gTranslate('config', "Gallery Diagnostics Page").'</a>';
$help_page='<a href="http://gallery.sourceforge.net/help.php">'. gTranslate('config', "Gallery Help Page"). '</a>';
$docs_page = galleryDocs();

if ($docs_page) {
	echo sprintf(gTranslate('config', "Having problems?  Try the %s, %s and %s."),
	'<a href="'. $docs_page .'">'. gTranslate('config', "documentation") .'</a>', $diagnostics_page, $help_page);
}
else {
	echo sprintf(gTranslate('config', "Having problems?  Try the %s and %s."),
	$diagnostics_page, $help_page);
}

echo "</div>";
echo "<br>";

$configTestStatus = array(
	0 => array(
		'image' => gImage('green_trafficlight.gif', gTranslate('config', "Success!")),
		'text' => gTranslate('config', "Success!"),
        	'border' => 'border: 1px solid #b2ffb2',
        	'type' => 'success'
	),
	5 => array(
		'image' => gImage('yellow_trafficlight.gif', gTranslate('config', "Warning, but optional.")),
		'text' => gTranslate('config', "Warning, but optional."),
        	'border' => 'border: 2px solid #ffff98',
        	'type' => 'warning'
	),
	10 => array(
		'image' => gImage('yellow_red_trafficlight.gif', gTranslate('config', "Serious warning, but optional.")),
		'text' => gTranslate('config', "Serious warning, but optional."),
		'border' => 'border: 2px solid #ff9898',
        	'type' => 'error'
	),
	51 => array(
		'image' => gImage('red_trafficlight.gif', gTranslate('config', "Serious warning, no go!")),
		'text' => gTranslate('config', "Serious warning, no go!"),
        	'border' => 'border: 4px solid #ec000a',
        	'type' => 'error'
	),
	100 => array(
		'image' => gImage('red_trafficlight.gif', gTranslate('config', "Failure!")),
		'text' => gTranslate('config', "Failure!"),
		'border' => 'border: 4px solid #ec000a',
		'type' => 'error'
	)
);

$diagnostics_page = galleryLink(
    makeGalleryUrl('setup/diagnostics.php'),
    gTranslate('config', "Gallery Diagnostics Page"));

$help_page = galleryLink(
    'http://gallery.sourceforge.net/help.php',
    gTranslate('config', "Gallery Help Page"),
    array('target' => '_blank'));

$openBasedir = ini_get('open_basedir');

$error = 0;
$warning = 0;
?>

<script type="text/javascript" src="<?php echo makeGalleryUrl('js/toggle.js.php'); ?>"></script>

<input type="hidden" name="this_page" value="check">
<input type="hidden" name="next_page" value="constants">

<?php

        foreach ($checkPriority as $key => $checklistGroup) {
            echo "\n\t<h1>". $checklistGroup['shortdesc'] . '</h1>';

            echo "\n\t" . $checklistGroup['longdesc'];

            $table = new galleryTable();
            $table->setColumnCount(4);
            $table->setAttrs(array(
                'cellspacing' => 0,
                'id' => "setupchecks_$key",
                'class' => 'inner',
		'style' => 'width: 100%'
	    ));

            /* $checklist contains actually the complete list of checks */
            foreach ($checklistGroup['checkslist'] as $short) {
                $check = $checklist[$short];
	if( isset($check["enabled"]) && $check["enabled"] == "no") continue;

                $func = $check['func'];
	$result = $func();

	list($success, $fail, $warn) = $result;
	$status = getCheckStatus($result, $check);
	$usedStatus[$status] = $status;

	// shortdesc
                $table->addElement(array(
                    'content' => $check['prompt'] . '<br>' . toggleBox($short, $check["desc"], 'none'),
                    'cellArgs' => array(
				'class' => 'desc emphasis',
				'style' => 'width: 40%; vertical-align: top; border-bottom: 2px solid #ececec;'
		    )
		));

                // toggle button for the long description
                $table->addElement(array(
                    'content' => toggleButton($short),
                    'cellArgs' => array(
				'class' => 'desc',
				'style' => 'vertical-align: top; border-right: 2px solid #ececec; border-bottom: 2px solid #ececec;'
		    )
		));

	// traffic light
                $table->addElement(array(
                    'content' => $configTestStatus[$status]['image'],
                    'cellArgs' => array(
				'class' => 'desc',
				'style' => 'border-right: 2px solid #ececec; border-bottom: 2px solid #ececec;'
		    )
		));

	// result
                $message = '';

               foreach ($success as $successMsg) {
                    if ($successMsg != '') {
                        $message .= $successMsg;
		}
	}

	foreach ($fail as $key => $val) {
		if (isset($check['optional']) && $check['optional']) {
                        if (isset($check['serious']) && $check['serious']) {
				$serious_warning = true;
			}
			if (isset($check) && isset($check[$key])) {
                            $message .= $check[$key];
			}
			$warning++;
		}
		else {
                        if (isset($check['serious']) && $check['serious']) {
				$serious_warning = true;
			}

			if (isset($check) && isset($check[$key])) {
                            $message .= $check[$key];
			}
                        $error++;
		}

                    if (isset($check['open-basedir-note']) && !empty($openBasedir)) {
                        $message .= '<p>'. $check['open-basedir-note'] . '</p>';
		}

		if (is_array($val)) {
			foreach ($val as $msg) {
				if ($val) {
                                $message .= $msg;
				}
			}
		}
		else if (is_string($val)) {
                        $message .= $val;
		}
	}

	// Nothing failed, just warnings
	if (isset($warn) && !empty($warn)) {
		foreach ($warn as $key => $val) {
			if (isset($check[$key])) {
                            $message .= $check[$key];
                        }
                        else {
                            $message .= $val;
			}
			$warning++;
		}
	}

                $table->addElement(array(
                    'content' => infoBox(array(array(
                        'type' => $configTestStatus[$status]['type'],
                        'text' => $message)
                        ), '', false
                    ),
                    'cellArgs' => array(
				'class' => 'desc',
				'style' => 'vertical-align: top;border-bottom: 2px solid #ececec;'
		    )
		));
            }

            // Now show the checklist.
            ?>
            <div class="g-config-main">
            <?php echo $table->render(); ?>
            </div>

            <div class="clear"></div>
            <?php
}
    ?>

    <div class="floatleft left" style="width: 30%">
      <table>
        <tr>
          <td colspan="2" class="left"><?php echo gTranslate ('config', "Legend:"); ?></td>
        </tr><?php
        sort($usedStatus);
foreach ($usedStatus as $status) {
            echo "\n    <tr>";
            echo "\n      <td>". $configTestStatus[$status]['image'] .'</td>';
            echo "\n     <td style=\"padding: 2px; ". $configTestStatus[$status]['border'] .'">'. $configTestStatus[$status]['text'] .'</td>';
            echo "\n    </tr>";
}
?>
      </table>
    </div>

    <div class="floatleft center">
        <div style="width: 100%; padding: 10px 15px 2px 15px;">
<?php
if ($error > 0) {
    echo gallery_error(
        gTranslate('config', "There are errors in your configuration that will prevent Gallery from functioning properly.") .
        '<br>'.
        gTranslate('config', "You must fix them before you can continue.")
    );
}
else {
	if ($warning > 0) {
		if (isset($serious_warning)) {
            echo gallery_error(
		gTranslate('config', "Your installation has <b>serious warnings</b>!<br>Continue at your own risk...")
            );
        }
        else {
            echo gallery_warning(
                gTranslate('config', "Your installation has warnings, but this is usually OK.  Keep going!")
            );
		}
	}
    else {
        echo gallery_success(
            gTranslate('config', "Your installation passed with flying colors!  Go go go!")
        );
}
?>
        </div>
        <br>
<?php
echo gSubmit(
    'go_next',
    gTranslate('config', "Next Step &gt;&gt;"),
    array('disabled' => 'disabled')
);
}
?>
    </div>
    <div class="clear"></div>
    <br>


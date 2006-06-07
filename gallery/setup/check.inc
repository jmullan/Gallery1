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
 * $Id$
 */
?>
<?php

$configTestStatus = array(
	0 => array(
		'image' => gImage('green_trafficlight.gif', gTranslate('config', "Success")),
		'text' => gTranslate('config', "Success"),
	       	'border' => 'border: 1px solid #b2ffb2',
		'type' => 'success'
	),
	5 => array(
		'image' => gImage('yellow_trafficlight.gif', gTranslate('config', "Warning, but optional")),
		'text' => gTranslate('config', "Warning, but optional"),
		'border' => 'border: 2px solid #ffff98',
		'type' => 'warning'
	),
	10 => array(
		'image' => gImage('yellow_red_trafficlight.gif', gTranslate('config', "Serious warning, but optional")),
		'text' => gTranslate('config', "Serious warning, but optional"),
		'border' => 'border: 2px solid #ff9898',
		'type' => 'error'
	),
	51 => array(
		'image' => gImage('red_trafficlight.gif', gTranslate('config', "Serious warning, no go !")),
		'text' => gTranslate('config', "Serious warning, no go !"),
		'border' => 'border: 4px solid #ec000a',
		'type' => 'nogo'
	),
	100 => array(
		'image' => gImage('red_trafficlight.gif', gTranslate('config', "Failure")),
		'text' => gTranslate('config', "Failure"),
		'border' => 'border: 4px solid #ec000a',
		'type' => 'nogo'
	)
);

        $diagnostics_page = galleryLink(
		makeGalleryUrl('setup/diagnostics.php'),
		gTranslate('config', "Gallery Dia_gnostics Page"));
	$help_page = galleryLink(
		'http://gallery.sourceforge.net/help.php',
		gTranslate('config', "Gallery _help page"));
	$docs_page = galleryLink(galleryDocs(), gTranslate('config', "_documentation"));
?>

<script type="text/javascript" src="<?php echo makeGalleryUrl('js/toggle.js'); ?>"></script>

<input type="hidden" name="this_page" value="check">
<input type="hidden" name="next_page" value="constants">

<div class="g-header">
    <div class="g-pagetitle"><?php echo sprintf (gTranslate('config', "Gallery Configuration Wizard: Step %d"),1); ?></div>
</div>

<div class="g-sitedesc">
<?php 
	echo gTranslate('config', "This is the Gallery system check page.") . "  ";
	echo gTranslate('config', "This page will validate your installation to make sure that you have all the necessary components and permissions on this system and that everything is configured reasonably.") ;

	echo '<br><br>';

	if (galleryDocs()) {
	    printf(gTranslate('config', "Having problems?  Try the %s, %s and %s."),
		$docs_page,
		$diagnostics_page,
		$help_page
	    );
	}
	else {
	    printf(gTranslate('config', "Having problems?  Try the %s and %s."),
		$diagnostics_page,
		$help_page
	    );
	}
?>
</div>
<br>

<table class="g-double-bottom-border-spacer" width="80%" cellspacing="0" align="center">
<?php
$warning = 0;
foreach ($checklist as $short => $check) {
    if( isset($check["enabled"]) && $check["enabled"] == "no") continue;

    $func = $check["func"];
    $result = $func();
    
    list($success, $fail, $warn) = $result;
    $status = getCheckStatus($result, $check);
    $usedStatus[$status] = $status;
    
    // new Line //
    echo "\n<tr style=\"margin-top: 2px\">";
    // shortdesc
    echo "\n\t<td class=\"g-desc-cell\" width=\"35%\" style=\"font-weight: bold; vertical-align: top; border-bottom: 2px solid #ececec;\">". $check['prompt'];
    echo "\n\t\t". '<br><div style="font-weight: normal; display:none;" id="toogleFrame_'. $short .'">'. $check["desc"] .'</div>';
    echo "\n</td>"; 
    
    // toogle button
    echo "\n\t<td class=\"g-desc-cell\" style=\"vertical-align: top; border-right: 2px solid #ececec; border-bottom: 2px solid #ececec;\">" .
        "\n\t\t<a href=\"#\" onClick=\"gallery_toggle('$short'); return false;\">" .
        gImage('expand.gif', gTranslate('config', "Show/hide more information"), array('id' => "toogleBut_$short")) .
        '</a>';
    echo "\n\t</td>";
    
    // traffic light
    echo "\n\t<td class=\"g-desc-cell\" style=\"border-right: 2px solid #ececec; border-bottom: 2px solid #ececec;\">";
    echo $configTestStatus[$status]['image'];
    echo "\n\t</td>";
    
    // result
    echo "\n\t<td class=\"g-desc-cell\" style=\"vertical-align: top;border-bottom: 2px solid #ececec;\">";
    $message = '';
    foreach ($success as $key) {
	if ($key != '') {
	    $message .= $key;
	}
    }

    $openBasedir = ini_get('open_basedir');
    foreach ($fail as $key => $val) {
	    if (isset($check['optional']) && $check['optional'] == 1) {
	        if (isset($check["serious"]) && $check["serious"] == 1) {
	            $serious_warning = true;
	        }
	        if (isset($check) && isset($check[$key])) {
		    $message .= $check[$key];
	        }
	        $warning++;
	    } else {
	        if (isset($check["serious"]) && $check["serious"] == 1) {
	            $serious_warning = true;
	        }

	        if (isset($check) && isset($check[$key])) {
		    $message .= $check[$key];
	        }
	        $error = 1;
	    }

	    if (isset($check["open-basedir-note"]) && !empty($openBasedir)) {
		    $message .= '<p>'. $check["open-basedir-note"] . '</p>';
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

    echo infoBox(array(array(
	'type' => $configTestStatus[$status]['type'],
	'text' => $message)
	), '', false
    );
    echo "</td>"; 
	
    echo "\n</tr>";
}
?>
</table>

<div class="floatleft left" style="width: 30%">
  <table>
    <tr>
      <td colspan="2" class="left"><?php echo gTranslate ('config', "Legend:"); ?></td>
    </tr>
<?php
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
<?php
if (isset($error)) {
    echo infoBox(array(array(
	'type' => 'error',
        'text' => gTranslate('config', "There are errors in your configuration that will prevent Gallery from functioning properly.") .
                  '<br>'.
		  gTranslate('config', "You must fix them before you can continue.")
    )));
}
else {
    if ($warning > 0) {
	if (isset($serious_warning)) {
	   echo infoBox(array(array(
		'type' => 'error',
                'text' => gTranslate('config', "Your installation has <b>serious warnings</b>!<br>Continue at your own risk...")
	    )));
	}
	else {
	    echo infoBox(array(array(
		'type' => 'warning',
		'text' =>  gTranslate('config', "Your installation has warnings, but this is usually OK.  Keep going!")
	    )));
	}
    }
    else {
	echo infoBox(array(array(
	    'type' => 'success',
	    'text' =>  ("Your installation passed with flying colors!  Go go go!")
	)));
    }
    echo gSubmit(
	'go_next',
	gTranslate('config', "_Next Step ->"),
	array('disabled' => 'disabled')
    );
}
?>
</div>

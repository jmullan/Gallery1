<?php
/**
 * Copyright 2002-2005 Michael Cochrane <mike@graftonhall.co.nz>
 *
 * See the enclosed file COPYING for license information (LGPL).  If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 */

/* This file was taken from the Horde Framework (http://www.horde.org)
** The Version was: horde/services/images/colorpicker.php,v 1.21
**
** Jens Tkotz 25.04.2005
*/
require_once(dirname(dirname(__FILE__)) . '/init.php');

$target = getRequestVar('target');

doctype();
?>
<html>
<head>
  <title><?php echo _("Color Picker") ?></title>
  <?php common_header(); ?>
</head>

<img src="<?php echo getImagePath('colorscale.png') ?>" alt="" title="" id="colorpicker" onclick="changeColor(getColor(event)); return false;" onmousemove="demoColor(getColor(event)); return false;" style="cursor:crosshair;background-color:white;padding:1px" />

<div id="colorDemo" style="background-color:white;width:100px;height:20px;padding:1px"></div>
<script language="Javascript" type="text/javascript">
<!--
function changeColor(color)
{
    if (parent.opener.closed) {
        alert("<?php echo addslashes(_("The Options window has closed. Exiting.")) ?>");
        this.close();
        return;
    }

    if (!parent.opener.document.getElementById('<?php echo $target ?>')) {
        alert("<?php echo addslashes(_("This window must be called from an Options window")) ?>");
        this.close();
        return;
    }

    parent.opener.document.getElementById('<?php echo $target ?>').value = color;
    parent.opener.document.getElementById('<?php echo $target ?>').style.backgroundColor = color;
    parent.opener.document.getElementById('<?php echo $target ?>').style.color = brightness(color) < 128 ? 'white' : 'black';

    this.close();
}

function demoColor(color)
{
    var target = document.getElementById('colorDemo');
    target.style.backgroundColor = color;
    target.style.color = brightness(color) < 128 ? 'white' : 'black';
    target.innerHTML = color;
}

function getColor(event)
{
    var img = document.getElementById('colorpicker');

    var x = event.clientX - 10;
    var y = event.clientY - 10;

    var rmax = 0;
    var gmax = 0;
    var bmax = 0;

    if (y <= 32) {
        rmax = 255;
        gmax = (y / 32.0) * 255;
        bmax = 0;
    } else if (y <= 64) {
        y = y - 32;
        rmax = 255 - (y / 32.0) * 255;
        gmax = 255;
        bmax = 0;
    } else if (y <= 96) {
        y = y - 64;
        rmax = 0;
        gmax = 255;
        bmax = (y / 32.0) * 255;
    } else if (y <= 128) {
        y = y - 96;
        rmax = 0;
        gmax = 255 - (y / 32.0) * 255;
        bmax = 255;
    } else if (y <= 160) {
        y = y - 128;
        rmax = (y / 32.0) * 255;
        gmax = 0;
        bmax = 255;
    } else {
        y = y - 160;
        rmax = 255;
        gmax = 0;
        bmax = 255 - (y / 32.0) * 255;
    }

    if (x <= 50) {
        var r = Math.abs(Math.floor(rmax * x / 50.0));
        var g = Math.abs(Math.floor(gmax * x / 50.0));
        var b = Math.abs(Math.floor(bmax * x / 50.0));
    } else {
        x -= 50;
        var r = Math.abs(Math.floor(rmax + (x / 50.0) * (255 - rmax)));
        var g = Math.abs(Math.floor(gmax + (x / 50.0) * (255 - gmax)));
        var b = Math.abs(Math.floor(bmax + (x / 50.0) * (255 - bmax)));
    }

    return makeColor(r, g, b);
}

function makeColor(r, g, b)
{
    color = '#';
    color += hex(Math.floor(r / 16));
    color += hex(r % 16);
    color += hex(Math.floor(g / 16));
    color += hex(g % 16);
    color += hex(Math.floor(b / 16));
    color += hex(b % 16);
    return color;
}

function brightness(color)
{
    var r = new Number("0x" + color.substr(1, 2));
    var g = new Number("0x" + color.substr(3, 2));
    var b = new Number("0x" + color.substr(5, 2));
    return ((r * 299) + (g * 587) + (b * 114)) / 1000;
}

function hex(dec)
{
    return (dec).toString(16);
}
//-->
</script>
</body>
</html>

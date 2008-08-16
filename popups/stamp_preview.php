<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
 *
 * This file Copyright (C) 2003-2004 Joan McGalliard
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

// ######################################################################
// # IBPS E-C@ard for Gallery		   Version 1		        #
// # Copyright 2002 IBPS Friedrichs	 info@ibps-friedrichs.de	#
// # Ported for Gallery By freenik	  webmaster@cittadipolicoro.com #
// ######################################################################

/* Modified by Jens Tkotz */

require_once(dirname(dirname(__FILE__)) . '/init.php');

doctype();
?>
<html>
	<?php common_header(); ?>
	<title><?php echo gTranslate('core', "Poststamp preview"); ?></title>
</head>

<body class="g-popup">
<div class="g-header-popup"><?php echo gTranslate('core', "Choose a stamp:"); ?></div>

<table width="100" border="0" cellspacing="2" cellpadding="2">
<?php
for($i = 1; $i <= 27; $i++) {
	$nr = sprintf("%02d", $i-1);
	echo "\n<tr>";
	echo "\n". '<td style="background-color: #ccc; vertical-align: middle; text-align: center" scope="col">'. $i .'</td>';
	echo "\n". '<td style="background-color: #ccc" scope="col">';
	echo "\n\t" . gImage("/ecard_images/$nr.gif");
	echo "\n</td>";
	echo "\n</tr>";
}
?>

</table>
</body>
</html>

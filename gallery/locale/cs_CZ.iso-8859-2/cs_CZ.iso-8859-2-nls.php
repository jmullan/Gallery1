<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
 *
 * Version: 1_4_1_RC2
 */
/**
 * NLS (National Language System) array.
 *
 * The basic idea and values was taken from then Horde Framework (http://horde.org)
 * The original filename was horde/config/nls.php.dist and it was 
 * maintained by Jan Schneider (jan@horde.org)
 * The modifications to fit it for Gallery were made by Jens Tkotz 
 * (jens@peino.de)
 *
 */


/**
 ** Native languagename
 **/
	$nls['language']['cs_CZ.iso-8859-2'] =	'&#x010c;esky (ISO-8859-2)'; // Czech (ISO-8859-2)

/**
 ** Aliases for languages with different browser and gettext codes
 **/

	$nls['alias']['cs'] = 			'cs_CZ.iso-8859-2';	

/**
 ** Aliases for languages which we substitte or send by NUKE
 **/

	$nls['alias']['czech'] =                'cs_CZ.iso-8859-2' ;
	$nls['alias']['cs_CZ.ISO8859-1'] =	'cs_CZ.iso-8859-2' ;
 	$nls['alias']['cs_CZ.ISO8859-2'] =	'cs_CZ.iso-8859-2' ;

/**
 ** Charset
 **
 **/	
	
	$nls['charset']['cs_CZ'] =              'ISO-8859-2';

/**
 ** phpNuke
 **/
	$nls['phpnuke']['cs_CZ'] = 		'czech' ;

?>

<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
 * Version: 1.4.4-pl2
 */
/**
 * NLS (National Language System) array.
 *
 * The basic idea and values was taken from then Horde Framework (http://horde.org)
 * The original filename was horde/config/nls.php.dist.
 * The modifications to fit it for Gallery were made by Jens Tkotz 
 * (jens@peino.de)
 *
 */


/**
 ** Native languagename
 **/
	$nls['language']['no_NO'] = 		'Norsk bokm&#229;l';

/**
 ** Alias for languages with different browser and gettext codes
 **/

	$nls['alias']['no'] = 			'no_NO';

/**
 ** Alias for languages which we substitte or send by NUKE
 **/

	$nls['alias']['norwegian'] = 		'no_NO' ;
	$nls['alias']['nor']=			'no_NO' ;
	$nls['alias']['no_NO.ISO8859-1'] =	'no_NO' ;
	
/**
 ** phpNuke
 **/
	$nls['phpnuke']['no_NO'] = 		'norwegian';

/**
 ** postNuke
 **/
	$nls['postnuke']['no_NO'] = 		'nor';
?>

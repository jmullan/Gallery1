<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Version: 1.5
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
	$nls['language']['ru_RU.cp1251'] = 		'&#x0420;&#x0443;&#x0441;&#x0441;&#x043a;&#x0438;&#x0439; (Windows)';

/**
 ** Alias for languages with different browser and gettext codes
 **/

	$nls['alias']['ru'] = 			'ru_RU.cp1251';

/**
 ** Alias for languages which we substitte or send by NUKE
 **/

	$nls['alias']['russian'] =		'ru_RU.cp1251';
	$nls['alias']['rus'] =			'ru_RU.cp1251';

/**
 ** Charset
 **
 **/	
	
	$nls['charset']['ru_RU.cp1251'] = 		'cp1251';

/**
 ** phpNuke
 **/
	$nls['phpnuke']['ru_RU.cp1251'] = 		'russian';

/**
 ** postNuke
 **/
	$nls['postnuke']['ru_RU.cp1251'] = 		'rus';
?>
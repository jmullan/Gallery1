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
 * Version: 1.5-RC2
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
	$nls['language']['zh_CN'] = 	'&#31616;&#20307;&#20013;&#25991;';	// Simplified Chinese

/**
 ** Alias for languages which we substitte or send by NUKE or GeekLog
 **/

	//$nls['alias']['chinese'] =		'zh_CN' ;
	$nls['alias']['zh_CN.EUC'] =		'zh_CN' ;
	$nls['alias']['chinese_gb2312'] =	'zh_CN' ;

/**
 ** Charset
 **/	
	
	$nls['charset']['zh_CN'] = 	'GB2312';
	
/**
 ** Multibyte charset
 **/

	$nls['multibyte']['GB2312'] =   true;

/**
 ** phpNuke
 **/
	//$nls['phpnuke']['zh_CN'] =	'chinese' ;
?>

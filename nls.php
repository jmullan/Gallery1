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
 */
/**
 * NLS (National Language System) array.
 *
 * This array was taken from then Horde Framework (http://horde.org)
 * The original filename was horde/config/nls.php.dist and it was 
 * maintained by Jan Schneider (mail@janschneider.de)
 * The modifications to fit it for Gallery were made by Jens Tkotz 
 * (jens@f2h9.de)
 */

function getNLS () {
	$nls['languages']['da_DK'] = 'Dansk';
	$nls['languages']['de_DE'] = 'Deutsch';
	$nls['languages']['en_GB'] = 'English (UK)';
	$nls['languages']['en_US'] = 'English (US)';
	$nls['languages']['es_ES'] = 'Espa&ntilde;ol';
	$nls['languages']['fr_FR'] = 'Fran&ccedil;ais';
	$nls['languages']['fr_CA'] = 'Canadien Fran&ccedil;ais';
	$nls['languages']['it_IT'] = 'Italiano';
	$nls['languages']['he_IL'] = 'Hebrew';
	$nls['languages']['is_IS'] = '&Iacute;slenska';
	$nls['languages']['lt_LT'] = 'Lietuvi&#x0173;';
	$nls['languages']['nl_NL'] = 'Nederlands';
	$nls['languages']['nl_BE'] = 'Dutch';
	$nls['languages']['no_NO'] = 'Norsk';
	$nls['languages']['pl_PL'] = 'Polski';
	$nls['languages']['ru_RU'] = 'Russian (&#x0420;&#x0443;&#x0441;&#x0441;&#x043a;&#x0438;&#x0439;) (Windows)';
	$nls['languages']['ru_RU.koi8r'] = 'Russian (&#x0420;&#x0443;&#x0441;&#x0441;&#x043a;&#x0438;&#x0439;) (KOI8-R)';
	$nls['languages']['sv_SE'] = 'Svenska';
	
	
	/**
	 ** Aliases for languages with different browser and gettext codes
	 **/
	
	$nls['aliases']['de'] = 'de_DE';
	$nls['aliases']['en'] = 'en_US';
	$nls['aliases']['es'] = 'es_ES';
	$nls['aliases']['fr'] = 'fr_FR';
	$nls['aliases']['is'] = 'is_IS';
	$nls['aliases']['it'] = 'it_IT';
	$nls['aliases']['lt'] = 'lt_LT';
	$nls['aliases']['nl'] = 'nl_NL';
	$nls['aliases']['no'] = 'no_NO';
	$nls['aliases']['pl'] = 'pl_PL';
	$nls['aliases']['ru'] = 'ru_RU';
	$nls['aliases']['sv'] = 'sv_SE';
	
	$nls['aliases']['de_LI'] = 		'de_DE' ;
	$nls['aliases']['de_LU'] = 		'de_DE' ;
	$nls['aliases']['de_CH'] = 		'de_DE' ;
	$nls['aliases']['de_AT'] = 		'de_DE' ;
	$nls['aliases']['german'] =		'de_DE' ;
	$nls['aliases']['de_DE.ISO8859-1'] =    'de_DE' ;
	
	$nls['aliases']['dutch'] = 		'nl_NL' ;
	
	$nls['aliases']['en_EN'] = 		'en_US' ;
	$nls['aliases']['english'] = 		'en_US' ;
	$nls['aliases']['en_US.ISO8859-1'] =    'en_US' ;
	$nls['aliases']['en_GB.ISO8859-1'] =    'en_GB' ;

	$nls['aliases']['es_ES.ISO8859-1'] =    'es_ES' ;
	
	$nls['aliases']['fr_FR'] = 		'fr_CA' ;
	$nls['aliases']['fr_BE'] = 		'fr_CA' ;
	$nls['aliases']['fr_LU'] = 		'fr_CA' ;
	$nls['aliases']['fr_CH'] = 		'fr_CA' ;
	$nls['aliases']['french'] =		'fr_CA' ;
	$nls['aliases']['fr_CA.ISO8859-1'] =    'fr_CA' ;
	
	$nls['aliases']['icelandic']=		'is_IS' ;
	$nls['aliases']['is_IS.ISO8859-1'] =    'is_IS' ;
	
	$nls['aliases']['italian'] =		'it_IT' ;
	$nls['aliases']['it_IT.ISO8859-1'] =    'it_IT' ;
	
	$nls['aliases']['he_HE'] = 		'he_IL' ;
	$nls['aliases']['hebrew'] =		'he_IL' ;
	$nls['aliases']['he_IL.ISO8859-8'] =    'he_IL' ;
	
	//$nls['aliases']['lithuanian'] =	'lt_LT' ;
	$nls['aliases']['lt_LT.ISO8859-4'] =    'lt_LT' ;
	$nls['aliases']['lt_LT.ISO8859-13'] =   'lt_LT' ;
	
	$nls['aliases']['nl_BE.ISO8859-1'] =    'nl_BE' ;
	$nls['aliases']['nl_NL.ISO8859-1'] =    'nl_NL' ;
	
	$nls['aliases']['norwegian'] = 		'no_NO' ;
	$nls['aliases']['no_NO.ISO8859-1'] =    'no_NO' ;
	
	$nls['aliases']['polish'] =		'pl_PL' ;
	$nls['aliases']['pl_PL.ISO8859-2'] =    'pl_PL' ;
	
	$nls['aliases']['russian'] =		'ru_RU';
	//$nls['aliases']['russian'] =		'ru_RU.koi8r';
	$nls['aliases']['ru_RU.ISO8859-5'] =    'ru_RU' ;
	$nls['aliases']['ru_RU.KOI8-R'] =       'ru_RU.koi8r' ;
	
	$nls['aliases']['sv_SV'] = 		'sv_SE' ;
	$nls['aliases']['swedish'] =		'sv_SE' ;
	$nls['aliases']['sv_SE.ISO8859-1'] =    'sv_SE' ;
	
	$nls['aliases']['spanish'] = 		'es_ES' ;
	
	/**
	 ** Charsets
	 **/
	
	// Add your own charsets, if your system uses others than "normal"
	
	$nls['default']['charset'] = 'ISO-8859-1';
	
	$nls['charset']['pl_PL'] = 'ISO-8859-2';
	$nls['charset']['ru_RU'] = 'windows-1251';
	$nls['charset']['ru_RU.KOI8-R'] = 'KOI8-R';
	$nls['charset']['lt_LT'] = 'windows-1257';
	$nls['charset']['he_IL'] = 'windows-1255';
	
	//$nls['charset']['de_DE']='de_DE.ISO-8859-15@euro' ;
	//$nls['charset']['lt_LT'] = 'ISO-8859-13';
	
	
	// Direction
	
	$nls['default']['direction'] = 'ltr';
	$nls['direction']['he_IL'] = 'rtl' ;
	
	// Alignment
	
	$nls['default']['align'] = 'left';
	$nls['align']['he_IL'] = 'right' ;
	return $nls;
}
?>

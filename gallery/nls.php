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
 * (jens@peino.de)
 *
 */


/*
 * IMPORTANT
 * +++++++++
 * If you add or delete a language from this file, please bump the 
 * $gallery->config_version in Version.php
 *
 * If you add a new language please use alphatical order by name.
 */
function getNLS () {
	$nls['language']['zh_CN'] = 'Simplified Chinese (&#31616;&#20307;&#20013;&#25991;)';
	$nls['language']['zh_TW'] = 'Traditional Chinese (&#32321;&#39636;&#20013;&#25991;)';
	$nls['language']['cs_CZ'] = '&#x010c;esky';
	$nls['language']['da_DK'] = 'Dansk';
	$nls['language']['de_DE'] = 'Deutsch';
	$nls['language']['en_GB'] = 'English (UK)';
	$nls['language']['en_US'] = 'English (US)';
	$nls['language']['es_ES'] = 'Espa&#241;ol';
	$nls['language']['fr_FR'] = 'Fran&#231;ais';
	$nls['language']['it_IT'] = 'Italiano';
	$nls['language']['he_IL'] = 'Hebrew';
	$nls['language']['is_IS'] = '&#205;slenska';
	$nls['language']['ja_JP'] = '&#x65e5;&#x672c;&#x8a9e; (EUC-JP)';
	$nls['language']['lt_LT'] = 'Lietuvi&#x0173;';
	$nls['language']['nl_NL'] = 'Nederlands';
	$nls['language']['no_NO'] = 'Norsk bokm&#229;l';
	$nls['language']['pl_PL'] = 'Polski';
	$nls['language']['pt_PT'] = 'Portugu&#234;s';
	$nls['language']['ru_RU'] = '&#x0420;&#x0443;&#x0441;&#x0441;&#x043a;&#x0438;&#x0439; (Windows)';
	$nls['language']['ru_RU.koi8r'] = '&#x0420;&#x0443;&#x0441;&#x0441;&#x043a;&#x0438;&#x0439; (KOI8-R)';
	$nls['language']['sl_SI'] = 'Sloven&#x0161;&#x010d;ina';
	$nls['language']['fi_FI'] = 'Suomi';
	$nls['language']['sv_SE'] = 'Svenska';
	$nls['language']['tr_TR'] = 'T&#252;rk&#231;e';
	$nls['language']['uk_UA'] = '&#x0423;&#x043a;&#x0440;&#x0430;&#x0457;&#x043d;&#x0441;&#x044c;&#x043a;&#x0430;';

/**
 ** Aliases for languages with different browser and gettext codes
 **/
	
	$nls['alias']['cs'] = 'cs_CZ';
	$nls['alias']['de'] = 'de_DE';
	$nls['alias']['da'] = 'da_DK';
	$nls['alias']['en'] = 'en_US';
	$nls['alias']['es'] = 'es_ES';
	$nls['alias']['fi'] = 'fi_FI';
	$nls['alias']['fr'] = 'fr_FR';
	$nls['alias']['is'] = 'is_IS';
	$nls['alias']['it'] = 'it_IT';
	$nls['alias']['ja'] = 'ja_JP';
	$nls['alias']['lt'] = 'lt_LT';
	$nls['alias']['nl'] = 'nl_NL';
	$nls['alias']['no'] = 'no_NO';
	$nls['alias']['nb'] = 'no_NO';
	$nls['alias']['pl'] = 'pl_PL';
	$nls['alias']['pt'] = 'pt_PT';
	$nls['alias']['ru'] = 'ru_RU';
	$nls['alias']['sl'] = 'sl_SI';
	$nls['alias']['sv'] = 'sv_SE';
	$nls['alias']['tr'] = 'tr_TR';
	$nls['alias']['uk'] = 'uk_UA';

/**
 ** Aliases for languages which we substitte or send by NUKE
 **/

	$nls['alias']['czech'] =		'cs_CZ' ;
	$nls['alias']['cs_CZ.ISO8859-1'] =	'cs_CZ' ;

	$nls['alias']['danish'] =		'da_DK' ;
	$nls['alias']['da_DK.ISO8859-1'] =	'da_DK' ;

	$nls['alias']['de_LI'] = 		'de_DE' ;
	$nls['alias']['de_LU'] = 		'de_DE' ;
	$nls['alias']['de_CH'] = 		'de_DE' ;
	$nls['alias']['de_AT'] = 		'de_DE' ;
	$nls['alias']['german'] =		'de_DE' ;
	$nls['alias']['de_DE.ISO8859-1'] =	'de_DE' ;

	$nls['alias']['spanish'] =		'es_ES' ;
	$nls['alias']['es_ES.ISO8859-1'] =	'es_ES' ;

	$nls['alias']['en_EN'] = 		'en_GB' ;
	$nls['alias']['en_GB.ISO8859-1'] =	'en_GB' ;

	$nls['alias']['english'] = 		'en_US' ;
	$nls['alias']['en_US.ISO8859-1'] =	'en_US' ;
	
	$nls['alias']['fr_BE'] = 		'fr_FR' ;
	$nls['alias']['fr_CA'] = 		'fr_FR' ;
	$nls['alias']['fr_LU'] = 		'fr_FR' ;
	$nls['alias']['fr_CH'] = 		'fr_FR' ;
	$nls['alias']['french'] =		'fr_FR' ;
	$nls['alias']['fr_FR.ISO8859-1'] =	'fr_FR' ;

	$nls['alias']['finnish'] =		'fi_FI' ;
	$nls['alias']['fi_FI.ISO8859-1'] =	'fi_FI' ;
	
	$nls['alias']['icelandic']=		'is_IS' ;
	$nls['alias']['is_IS.ISO8859-1'] =	'is_IS' ;
	
	$nls['alias']['italian'] =		'it_IT' ;
	$nls['alias']['it_IT.ISO8859-1'] =	'it_IT' ;
	
	$nls['alias']['he_HE'] = 		'he_IL' ;
	$nls['alias']['hebrew'] =		'he_IL' ;
	$nls['alias']['he_IL.ISO8859-8'] =	'he_IL' ;

	$nls['alias']['ja_JP.EUC-JP'] =		'ja_JP' ;
	
	$nls['alias']['lt_LT.ISO8859-4'] =	'lt_LT' ;
	$nls['alias']['lt_LT.ISO8859-13'] =	'lt_LT' ;
	
	$nls['alias']['dutch'] = 		'nl_NL' ;
	$nls['alias']['nl_BE'] = 		'nl_NL' ;
	$nls['alias']['nl_NL.ISO8859-1'] =	'nl_NL' ;
	
	$nls['alias']['norwegian'] = 		'no_NO' ;
	$nls['alias']['no_NO.ISO8859-1'] =	'no_NO' ;
	
	$nls['alias']['polish'] =		'pl_PL' ;
	$nls['alias']['pl_PL.ISO8859-2'] =	'pl_PL' ;

	$nls['alias']['portuguese'] =		'pt_PT' ;
	$nls['alias']['pt_PT.ISO8859-2'] =	'pt_PT' ;
	$nls['alias']['pt_PT.ISO8859-1'] =	'pt_PT' ;
	
	$nls['alias']['russian'] =		'ru_RU';
	//$nls['alias']['russian'] =		'ru_RU.koi8r';
	$nls['alias']['ru_RU.ISO8859-5'] =	'ru_RU' ;
	$nls['alias']['ru_RU.KOI8-R'] =		'ru_RU.koi8r' ;

	$nls['alias']['slovenian'] =    	'sl_SI' ;
	$nls['alias']['sl_SI.ISO8859-2'] =	'sl_SI' ;
	
	$nls['alias']['sv_SV'] = 		'sv_SE' ;
	$nls['alias']['swedish'] =		'sv_SE' ;
	$nls['alias']['sv_SE.ISO8859-1'] =	'sv_SE' ;

	$nls['alias']['turkish'] =		'tr_TR' ;	
	$nls['alias']['tr_TR.ISO8859-9'] =	'tr_TR' ;

	$nls['alias']['ukrainian'] =        	'uk_UA' ;
	$nls['alias']['uk_UA.KOI8-U'] =         'uk_UA' ;

	$nls['alias']['zh_TW.GB2312'] =         'zh_CN' ;

	$nls['alias']['zh_TW.Big5']      =	'zh_TW' ;
	$nls['alias']['chinese'] = 		'zh_TW' ;
	
/**
 ** Charsets
 **
 ** Add your own charsets, if your system uses others than "normal"
 **
 **/	
	
	$nls['default']['charset'] =	 	'ISO-8859-1';

	$nls['charset']['cs_CZ'] = 		'ISO-8859-2';	
	$nls['charset']['he_IL'] = 		'windows-1255';
	$nls['charset']['ja_JP'] = 		'EUC-JP';
	$nls['charset']['lt_LT'] = 		'windows-1257';
	$nls['charset']['ja_JP'] = 		'EUC-JP';
	$nls['charset']['pl_PL'] = 		'ISO-8859-2';
	$nls['charset']['ru_RU'] = 		'windows-1251';
	$nls['charset']['ru_RU.KOI8-R'] = 	'KOI8-R';
	$nls['charset']['sl_SI'] = 		'ISO-8859-2';
	$nls['charset']['tr_TR'] = 		'ISO-8859-9';
	$nls['charset']['uk_UA'] = 		'KOI8-U';
	$nls['charset']['zh_CN'] = 		'GB2312';
	$nls['charset']['zh_TW'] = 		'BIG5';	

	//$nls['charset']['de_DE'] =		'de_DE.ISO-8859-15@euro' ;
	//$nls['charset']['lt_LT'] = 		'ISO-8859-13';
	
/**
 ** Multibyte charsets
 **/

	$nls['multibyte']['BIG5'] = 	true;
	$nls['multibyte']['GB2312'] =   true;
	$nls['multibyte']['EUC-JP'] = 	true;
	$nls['multibyte']['UTF-8'] = 	true;	

/**
 ** Direction
 **/
	
	$nls['default']['direction'] =	'ltr';
	$nls['direction']['he_IL'] = 	'rtl' ;

/**
 ** Alignment
 **/
	
	$nls['default']['alignment'] =	'left';
	$nls['alignment']['he_IL'] =	'right' ;

/**
 ** Nuke
 **/
	$nls['nuke']['zh_TW'] = 'chinese' ;
	$nls['nuke']['cs_CZ'] = 'czech' ;
	$nls['nuke']['de_DE'] = 'german';
	$nls['nuke']['da_DK'] = 'danish';
	$nls['nuke']['en_US'] = 'english';
	$nls['nuke']['es_ES'] = 'spanish';
	$nls['nuke']['fr_FR'] = 'french';
	$nls['nuke']['fi_FI'] = 'finnish';
	$nls['nuke']['it_IT'] = 'italian';
	$nls['nuke']['is_IS'] = 'icelandic';
	$nls['nuke']['nl_NL'] = 'dutch';
	$nls['nuke']['no_NO'] = 'norwegian';
	$nls['nuke']['pl_PL'] = 'polish';
	$nls['nuke']['pt_PT'] = 'portuguese';
	$nls['nuke']['ru_RU'] = 'russian';
	$nls['nuke']['sv_SE'] = 'swedish';
	$nls['nuke']['sl_SI'] = 'slovenian';
	$nls['nuke']['tr_TR'] = 'turkish';
	$nls['nuke']['uk_UA'] = 'ukrainian';

return $nls;
}
?>

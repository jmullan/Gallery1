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
 * The basic idea and values was taken from then Horde Framework (http://horde.org)
 * The original filename was horde/config/nls.php.dist and it was 
 * maintained by Jan Schneider (jan@horde.org)
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
	$nls['language']['zh_CN'] = '&#31616;&#20307;&#20013;&#25991;';	// Simplified Chinese
	$nls['language']['zh_TW'] = '&#32321;&#39636;&#20013;&#25991;'; // Traditional Chinese
	$nls['language']['zh_TW.utf8'] = '&#32321;&#39636;&#20013;&#25991; (UTF-8)'; // Traditional Chinese (UTF-8)
	$nls['language']['bg_BG'] = '&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;'; // Bulgarian
	$nls['language']['ca_ES'] = 'Catal&#xe0;'; // Catalan
	$nls['language']['cs_CZ'] = '&#x010c;esky'; // Czech
	$nls['language']['cs_CZ.cp1250'] = '&#x010c;esky CP'; // Czech
	$nls['language']['cs_CZ.iso'] = '&#x010c;esky ISO'; // Czech
	$nls['language']['da_DK'] = 'Dansk'; // Danish
	$nls['language']['de_DE'] = 'Deutsch'; // German
	$nls['language']['en_GB'] = 'English (UK)';
	$nls['language']['en_US'] = 'English (US)';
	$nls['language']['es_ES'] = 'Espa&#241;ol'; // Spanish
	$nls['language']['fr_FR'] = 'Fran&#231;ais'; // French
	$nls['language']['it_IT'] = 'Italiano'; // Italian
	$nls['language']['he_IL'] = '&#1506;&#1489;&#1512;&#1497;&#1514;'; // Hebrew
	$nls['language']['is_IS'] = '&#205;slenska'; // Icelandic
	$nls['language']['ja_JP'] = '&#x65e5;&#x672c;&#x8a9e; (EUC-JP)'; // Japanese (EUC-JP)
	$nls['language']['ko_KR'] = '&#xd55c;&#xad6d;&#xc5b4;'; // Korean
	$nls['language']['lt_LT'] = 'Lietuvi&#x0173;'; // Lithuanian
	$nls['language']['hu_HU'] = 'Magyar'; // Hungarian
	$nls['language']['nl_NL'] = 'Nederlands'; // Dutch
	$nls['language']['no_NO'] = 'Norsk bokm&#229;l'; // Norwegian (Bokmal)
	$nls['language']['pl_PL'] = 'Polski'; // Polish
	$nls['language']['pt_PT'] = 'Portugu&#234;s'; // Portuguese
	$nls['language']['pt_BR'] = 'Portugu&#234;s Brasileiro'; // Portuguese (Brazil)
	$nls['language']['ru_RU'] = '&#x0420;&#x0443;&#x0441;&#x0441;&#x043a;&#x0438;&#x0439; (Windows)'; // Russian (Windows)
	$nls['language']['ru_RU.koi8r'] = '&#x0420;&#x0443;&#x0441;&#x0441;&#x043a;&#x0438;&#x0439; (KOI8-R)'; // Russian (KOI8-R)
	$nls['language']['sl_SI'] = 'Sloven&#x0161;&#x010d;ina'; // Slovenian
	$nls['language']['fi_FI'] = 'Suomi'; // Finnish
	$nls['language']['sv_SE'] = 'Svenska'; // Swedish
	$nls['language']['tr_TR'] = 'T&#252;rk&#231;e'; // Turkish
	$nls['language']['uk_UA'] = '&#x0423;&#x043a;&#x0440;&#x0430;&#x0457;&#x043d;&#x0441;&#x044c;&#x043a;&#x0430;'; // Ukranian

/**
 ** Aliases for languages with different browser and gettext codes
 **/

	$nls['alias']['ca'] = 'ca_ES';
	$nls['alias']['cs'] = 'cs_CZ';	
	$nls['alias']['da'] = 'da_DK';
	$nls['alias']['de'] = 'de_DE';
	$nls['alias']['en'] = 'en_US';
	$nls['alias']['es'] = 'es_ES';
	$nls['alias']['fi'] = 'fi_FI';
	$nls['alias']['fr'] = 'fr_FR';
	$nls['alias']['hu'] = 'hu_HU';
	$nls['alias']['is'] = 'is_IS';
	$nls['alias']['it'] = 'it_IT';
	$nls['alias']['ja'] = 'ja_JP';
	$nls['alias']['ko'] = 'ko_KR';
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
	$nls['alias']['bg_BG.CP1251'] =         'bg_BG' ;

	$nls['alias']['catala'] =               'ca_ES' ;
        $nls['alias']['ca_ES.ISO8859-1'] =      'ca_ES' ;

	$nls['alias']['czech'] =                'cs_CZ' ;
	$nls['alias']['cs_CZ.ISO8859-1'] =      'cs_CZ' ;
 	$nls['alias']['cs_CZ.ISO8859-2'] =	'cs_CZ' ;

	$nls['alias']['danish'] =               'da_DK' ;
        $nls['alias']['da_DK.ISO8859-1'] =      'da_DK' ;

	$nls['alias']['german'] =		'de_DE' ;
	$nls['alias']['deu'] =			'de_DE' ;
	$nls['alias']['de_LI'] = 		'de_DE' ;
	$nls['alias']['de_LU'] = 		'de_DE' ;
	$nls['alias']['de_CH'] = 		'de_DE' ;
	$nls['alias']['de_AT'] = 		'de_DE' ;
	$nls['alias']['de_DE.ISO8859-1'] =	'de_DE' ;
	
	$nls['alias']['spanish'] =		'es_ES' ;
	$nls['alias']['spa'] =			'es_ES' ;
	$nls['alias']['es_ES.ISO8859-1'] =	'es_ES' ;

	$nls['alias']['en_EN'] = 		'en_GB' ;
	$nls['alias']['en_GB.ISO8859-1'] =	'en_GB' ;
	$nls['alias']['en_AU'] =		'en_GB' ;

	$nls['alias']['english'] = 		'en_US' ;
	$nls['alias']['eng'] =			'en_US' ;
	$nls['alias']['en_US.ISO8859-1'] =	'en_US' ;
	
	$nls['alias']['french'] =		'fr_FR' ;
	$nls['alias']['fra'] =			'fr_FR' ;
	$nls['alias']['fr_BE'] = 		'fr_FR' ;
	$nls['alias']['fr_CA'] = 		'fr_FR' ;
	$nls['alias']['fr_LU'] = 		'fr_FR' ;
	$nls['alias']['fr_CH'] = 		'fr_FR' ;
	$nls['alias']['fr_FR.ISO8859-1'] =	'fr_FR' ;

	$nls['alias']['finnish'] =		'fi_FI' ;
	$nls['alias']['fi_FI.ISO8859-1'] =	'fi_FI' ;

	$nls['alias']['hebrew'] =		'he_IL' ;
	$nls['alias']['he_HE'] = 		'he_IL' ;
	$nls['alias']['he_IL.ISO8859-8'] =	'he_IL' ;

	$nls['alias']['hungarian'] =		'hu_HU' ;
	$nls['alias']['hu_HU.ISO8859-8'] =	'hu_HU' ;
	$nls['alias']['hu_HU.ISO8859-2'] =	'hu_HU' ;

	$nls['alias']['icelandic']=		'is_IS' ;
	$nls['alias']['isl']=			'is_IS' ;
	$nls['alias']['is_IS.ISO8859-1'] =	'is_IS' ;
	
	$nls['alias']['italian'] =		'it_IT' ;
	$nls['alias']['ita']=			'it_IT' ;
	$nls['alias']['it_IT.ISO8859-1'] =	'it_IT' ;

	$nls['alias']['ja_JP.EUC-JP'] =		'ja_JP' ;	
 	$nls['alias']['ja_JP.EUC'] =		'ja_JP' ;
	
	$nls['alias']['ko_KR.EUC-KR'] =         'ko_KR' ;
	$nls['alias']['ko_KR.EUC'] =            'ko_KR' ;

	$nls['alias']['lt_LT.ISO8859-4'] =	'lt_LT' ;
	$nls['alias']['lt_LT.ISO8859-13'] =	'lt_LT' ;
	
	$nls['alias']['dutch'] = 		'nl_NL' ;
	$nls['alias']['nl_BE'] = 		'nl_NL' ;
	$nls['alias']['nl_NL.ISO8859-1'] =	'nl_NL' ;
	
	$nls['alias']['norwegian'] = 		'no_NO' ;
	$nls['alias']['nor']=			'no_NO' ;
	$nls['alias']['no_NO.ISO8859-1'] =	'no_NO' ;
	
	$nls['alias']['polish'] =		'pl_PL' ;
	$nls['alias']['pl_PL.ISO8859-2'] =	'pl_PL' ;

	$nls['alias']['brazilian'] =            'pt_BR' ;
	$nls['alias']['pt_BR.ISO8859-1'] =      'pt_BR' ;
	$nls['alias']['pt_BR.ISO8859-15'] =	'pt_BR' ;

	$nls['alias']['portuguese'] =		'pt_PT' ;
	$nls['alias']['pt_PT.ISO8859-1'] =	'pt_PT' ;
	$nls['alias']['pt_PT.ISO8859-15'] =	'pt_PT' ;
	
	$nls['alias']['russian'] =		'ru_RU';
	$nls['alias']['rus'] =			'ru_RU';
	//$nls['alias']['russian'] =		'ru_RU.koi8r';
	//$nls['alias']['rus'] =		'ru_RU.koi8r';
	$nls['alias']['ru_RU.ISO8859-5'] =	'ru_RU' ;
	$nls['alias']['ru_RU.KOI8-R'] =		'ru_RU.koi8r' ;

	$nls['alias']['slovenian'] =    	'sl_SI' ;
	$nls['alias']['sl_SI.ISO8859-2'] =	'sl_SI' ;
	
	$nls['alias']['swedish'] =		'sv_SE' ;
	$nls['alias']['sv_SV'] = 		'sv_SE' ;
	$nls['alias']['swe'] =			'sv_SE' ;
	$nls['alias']['sv_SE.ISO8859-1'] =	'sv_SE' ;

	$nls['alias']['turkish'] =		'tr_TR' ;	
	$nls['alias']['tr_TR.ISO8859-9'] =	'tr_TR' ;

	$nls['alias']['ukrainian'] =            'uk_UA' ;
	$nls['alias']['uk_UA.KOI8-U'] =         'uk_UA' ;

	$nls['alias']['zh_CN.EUC'] =            'zh_CN' ;

	$nls['alias']['chinese'] = 		'zh_TW' ;
	$nls['alias']['zh_TW.GB2312'] =         'zh_TW' ;
	$nls['alias']['zh_TW.Big5']      =	'zh_TW' ;

/**
 ** Charsets
 **
 ** Add your own charsets, if your system uses others than "normal"
 **
 **/	
	
	$nls['default']['charset'] = 		'ISO-8859-1';
	
	$nls['charset']['bg_BG'] =              'windows-1251';
	$nls['charset']['cs_CZ'] =              'ISO-8859-2';
	$nls['charset']['he_IL'] = 		'windows-1255';
	$nls['charset']['hu_HU'] =		'ISO-8859-2';
	$nls['charset']['ja_JP'] = 		'EUC-JP';
	$nls['charset']['ko_KR'] =              'EUC-KR';
	$nls['charset']['lt_LT'] = 		'windows-1257';
	$nls['charset']['pl_PL'] = 		'ISO-8859-2';
	$nls['charset']['ru_RU'] = 		'windows-1251';
	$nls['charset']['ru_RU.KOI8-R'] =	'KOI8-R';
	$nls['charset']['sl_SI'] = 		'ISO-8859-2';
	$nls['charset']['tr_TR'] = 		'ISO-8859-9';
	$nls['charset']['uk_UA'] =              'KOI8-U';
	$nls['charset']['zh_CN'] = 		'GB2312';
	$nls['charset']['zh_TW'] = 		'BIG5';	
	$nls['charset']['zh_TW.utf8'] = 	'UTF-8';	

	//$nls['charset']['de_DE'] =		'de_DE.ISO-8859-15@euro' ;
	//$nls['charset']['lt_LT'] = 		'ISO-8859-13';
	
/**
 ** Multibyte charsets
 **/

	$nls['multibyte']['BIG5'] = 	true;
	$nls['multibyte']['EUC-JP'] =   true;
	$nls['multibyte']['EUC-KR'] =   true;
	$nls['multibyte']['GB2312'] =   true;
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
	$nls['alignment']['he_IL'] = 	'right' ;

/**
 ** phpNuke
 **/
	$nls['phpnuke']['pt_BR'] = 'brazilian' ;
	$nls['phpnuke']['ca_ES'] = 'catala' ;
	$nls['phpnuke']['zh_TW'] = 'chinese' ;
	$nls['phpnuke']['cs_CZ'] = 'czech' ;
	$nls['phpnuke']['da_DK'] = 'danish';
	$nls['phpnuke']['nl_NL'] = 'dutch';
	$nls['phpnuke']['en_US'] = 'english';
	$nls['phpnuke']['fi_FI'] = 'finnish';
	$nls['phpnuke']['fr_FR'] = 'french';
	$nls['phpnuke']['de_DE'] = 'german';
	$nls['phpnuke']['hu_HU'] = 'hungarian';
	$nls['phpnuke']['it_IT'] = 'italian';
	$nls['phpnuke']['is_IS'] = 'icelandic';
	$nls['phpnuke']['no_NO'] = 'norwegian';
	$nls['phpnuke']['pl_PL'] = 'polish';
	$nls['phpnuke']['pt_PT'] = 'portuguese';
	$nls['phpnuke']['ru_RU'] = 'russian';
	$nls['phpnuke']['sl_SI'] = 'slovenian';
	$nls['phpnuke']['es_ES'] = 'spanish';
	$nls['phpnuke']['sv_SE'] = 'swedish';
	$nls['phpnuke']['tr_TR'] = 'turkish';
	$nls['phpnuke']['uk_UA'] = 'ukrainian';

/**
 ** postNuke
 **/
	$nls['postnuke']['de_DE'] = 'deu';
	$nls['postnuke']['en_US'] = 'eng';
	$nls['postnuke']['es_ES'] = 'spa';
	$nls['postnuke']['fr_FR'] = 'fra';
	$nls['postnuke']['fi_FI'] = 'fin';
	$nls['postnuke']['it_IT'] = 'ita';
	$nls['postnuke']['is_IS'] = 'isl';
	$nls['postnuke']['no_NO'] = 'nor';
	$nls['postnuke']['ru_RU'] = 'rus';
	$nls['postnuke']['sv_SE'] = 'swe';


/**
 ** Flags "alias"
 **/
	$nls['flag']['ru_RU.koi8r'] =	'ru_RU';
	$nls['flag']['zh_TW.utf8'] =	'zh_TW';

return $nls;
}
?>

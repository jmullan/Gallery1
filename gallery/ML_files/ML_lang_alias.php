<?php

/**
 * NLS (National Language System) configuration file.
 *
 * This file was taken from then Horde Framework (http://horde.org)
 * The original filename was horde/config/nls.php.dist and it was maintained by Jan Schneider (mail@janschneider.de)
 * The modifications to fit it for ML Gallery were made by Jens Tkotz (jens@f2h9.de
 */


/**
 ** Language
 **/

$nls['languages']['da_DK'] = 'Dansk';
$nls['languages']['de_DE'] = 'Deutsch';
$nls['languages']['en_GB'] = 'English (UK)';
$nls['languages']['en_US'] = 'English (US)';
$nls['languages']['es_ES'] = 'Espa&ntilde;ol';
$nls['languages']['fr_FR'] = 'Fran&ccedil;ais';
$nls['languages']['it_IT'] = 'Italiano';
$nls['languages']['is_IS'] = '&Iacute;slenska';
$nls['languages']['lt_LT'] = 'Lietuvi&#x0173;';
$nls['languages']['nl_NL'] = 'Nederlands';
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

$nls['aliases']['dutch'] = 		'nl_NL' ;

$nls['aliases']['en_EN'] = 		'en_US' ;
$nls['aliases']['english'] = 		'en_US' ;

$nls['aliases']['fr_FR'] = 		'fr_CA' ;
$nls['aliases']['fr_BE'] = 		'fr_CA' ;
$nls['aliases']['fr_LU'] = 		'fr_CA' ;
$nls['aliases']['fr_CH'] = 		'fr_CA' ;
$nls['aliases']['french'] =		'fr_CA' ;

$nls['aliases']['icelandic']=		'is_IS' ;
$nls['aliases']['italian'] =		'it_IT' ;

$nls['aliases']['he_HE'] = 		'he_IL' ;
$nls['aliases']['hebrew'] =		'he_IL' ;

//$nls['aliases']['lithuanian'] =	'lt_LT' ,

$nls['aliases']['norwegian'] = 		'no_NO' ;

$nls['aliases']['polish'] =		'pl_PL' ;

$nls['aliases']['russian'] =		'ru_RU';
//$nls['aliases']['russian'] =		'ru_RU.koi8r';

$nls['aliases']['sv_SV'] = 		'sv_SE' ;
$nls['aliases']['swedish'] =		'sv_SE' ;

$nls['aliases']['spanish'] = 		'es_ES' ;

/**
 ** Charsets
 **/

// Add your own charsets, if your system uses others then "normal"

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

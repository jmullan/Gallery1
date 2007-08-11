<?php
/**
 * Central capta settings
 *
 * @package Captcha
 *
 * $Id$
 */

// ConfigArray
$CAPTCHA_INIT = array(
	'tempfolder'	 => 'captcha_tmp/',   // string: relative path (with trailing slash!) inside the albums folder of Gallery
										   // to a writeable tempfolder which is also accessible via HTTP!
										   // NOTE: This is different to the original hn_captcha !!
	'TTF_folder'	 => dirname(__FILE__) .'/',					 // string: absolute path (with trailing slash!) to folder which contains your TrueType-Fontfiles.
	// mixed (array or string): basename(s) of TrueType-Fontfiles
	'TTF_RANGE'	  => array('COM430.ttf'),
	'chars'		  => 5,			// integer: number of chars to use for ID
	'minsize'		=> 15,		// integer: minimal size of chars
	'maxsize'		=> 15,		// integer: maximal size of chars
	'maxrotation'	=> 25,		// integer: define the maximal angle for char-rotation, good results are between 0 and 30

	'noise'		  => TRUE,		// boolean: TRUE = noisy chars | FALSE = grid
	'websafecolors'  => FALSE,	// boolean
	'refreshlink'	=> TRUE,	// boolean				 ; Unused in Gallery (always showed)
	'lang'		   => 'en',		// string:  ['en'|'de']	; Unused in Gallery
	'maxtry'		 => 3,		// integer: [1-9]		  ; Unused in Gallery (unlimited)

	'badguys_url'	=> '/',		// string: URL			 ; Unused in Gallery
	'secretstring'   => 'Disco in Frisco, says Jenz from Erkelenz',
	'secretposition' => 24,		// integer: [1-32]

	'debug'		  => FALSE,

	'counter_filename'		=> '',			  // string: absolute filename for textfile which stores current counter-value. Needs read- & write-access!
	'prefix'				=> 'hn_captcha_',   // string: prefix for the captcha-images, is needed to identify the files in shared tempfolders
	'collect_garbage_after'	=> 20,			 // integer: the garbage-collector run once after this number of script-calls
	'maxlifetime'			=> 60			  // integer: only imagefiles which are older than this amount of seconds will be deleted
);

?>

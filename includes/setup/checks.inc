<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
 * $Id: checks.inc 13795 2006-06-11 14:21:15Z jenst $
 */

/**
 * Defines the checklist and checkpriority for checks in step 1 of the setup.
 *
 * @package Setup
 */

$locale_check = check_locale();
/*
 * $elawisyl <=> Enable LAnguages WIthout SYstem Locales
 * is set to "no" when we have no system locales and are in linux
 * "no" disables all language settings
*/

if ($locale_check == NULL) {
	$elawisyl = 'no';
}
else {
	$elawisyl = 'yes';
}

$netpbm_check = check_graphics();
$IM_check = check_graphics_im();

/* If all watermarking/overlay applications are missing */
if ( isset($netpbm_check[2]['pnmcomp']) && isset($netpbm_check[2]['pamcomp']) && isset($IM_check[2]['composite']) ) {
	$enablewatermark = 'no';
}
else {
	$enablewatermark = 'yes';
}

if (function_exists('posix_getpwuid')) {
	$rec = @posix_getpwuid(posix_getuid());
	$webserver_user = $rec['name'];
}
else {
	$whoami = locateFile('whoami');
	if ($whoami) {
		fs_exec($whoami, $results, $status);
		$webserver_user = $results[0];
	}
	else {
		$webserver_user = gTranslate('config',"unknown");
	}
}

$common_messages['getting-netpbm'] = '<br>'.
  sprintf(
      gTranslate('config',"Gallery prefers Netpbm version 10.3 and up.  You can install just the binaries Gallery needs from the %sGallery Download Page%s. Or, you can compile and install the entire Netpbm package from the %sNetpbm website%s."),
      '"<a href="http://sourceforge.net/projects/gallery" target="_blank">',
      '</a>',
      '<a href="http://sourceforge.net/projects/netpbm" target="_blank">',
      '</a>'
  )  .
  '<p class="g-emphasis">' . gTranslate('config',"Note:"). '</p>' .
  gTranslate('config',"You can install the binaries from the Gallery site even if you don't have root access on your box!  If you can't get it working, try leaving the Netpbm path blank and using ImageMagick instead.");

$common_messages['getting-imagemagick'] = '<br>'.
  sprintf(
    gTranslate('config',"Gallery prefers ImageMagick version 5.4.8 and up. (Tough Version 6.x is recommended.)  You can get the entire ImageMagick package from the %sImageMagick website%s." ),
    '<a href="http://www.imagemagick.org" target="_blank">', '</a>'
  ) .
  '<p class="g-emphasis">' . gTranslate('config',"Note:"). '</p>' .
  gTranslate('config',"The site also has binaries available for assorted operating systems.  If you can't get it working, try leaving the ImageMagick path blank and using Netpbm instead.");

$common_messages['open-basedir-active'] =
  '<p class="g-emphasis">' . gTranslate('config',"Note:"). '</p>' .
  sprintf(gTranslate('config',"Your PHP installation is configured with the %s restriction.   This may make it difficult for Gallery to detect and verify your binaries, even if you've already successfully used the Config. Wizard in the past.  Don't worry too much if this page gives warnings; your settings will still be saved properly."),
    '<b><a href="http://www.php.net/manual/en/features.safe-mode.php#ini.open-basedir" target="_blank">open_basedir</a></b>'
  );

/* The list of checks done in step 1 */
$checklist = array(
	'gallery_version' => array(
		'prompt' => gTranslate('config',"Gallery version"),
		'desc' => sprintf(gTranslate('config',"Check to see how old %s version %s is."),
		  Gallery(),
		  "<i>$gallery->version</i>"),
		'func' => 'check_gallery_version',
		'optional' => 1,
	),
	'php' => array(
		'prompt' => gTranslate('config',"PHP version"),
		'desc' => gTranslate('config',"Check to see if you are running a supported Version of PHP.") .'<br>'.  gTranslate('config',"Due to bugs and missing functionality in some PHP versions, the minimum PHP version for Gallery 1.5 and newer is at least PHP 4.1.0"),
		'func' => 'check_php',
		'fail-too-old' => sprintf(gTranslate('config',"You're running PHP version %s.  You need to upgrade to at least PHP %s."), phpversion(), $MIN_PHP_MAJOR_VERSION),
		'fail-incompatible' => sprintf(gTranslate('config',"You're running PHP version %s which has known bugs that cause Gallery to fail."),  phpversion()),
		'fail-buggy__FILE__' => gTranslate('config',"It seems your PHP has a problem with __FILE__, please upgrade to a recent Version"),
		'serious' => 1,
	),
	'register_globals' => array(
		'prompt' => gTranslate('config',"Register globals"),
		'desc' => gTranslate('config',"Gallery highly recommends that the PHP option 'register_globals' be turned off, for security purposes.  Some software, notably the 'Geeklog' CMS, requires that 'register_globals' be enabled."),
		'func' => 'check_register_globals',
		'warn-register_globals' => gTranslate('config',"The PHP option 'register_globals' is enabled on your server. Gallery highly recommends that you disable 'register_globals' unless it is required by software on your site.  Gallery does not officially support 'register_globals' being enabled, and will attempt to disable it internally."),
		'serious' => 1,
		'optional' => 1,
	),
	'safe_mode' => array(
		'prompt' => gTranslate('config',"PHP's safe mode disabled"),
		'desc' => gTranslate('config',"Check to see if PHP is running in <b>safe_mode</b>.  Safe Mode is a PHP security feature commonly used in shared environments.  Gallery cannot run when Safe Mode is enabled because it imposes too many restrictions."),
		'func' => 'check_safe_mode',
		'fail-safe-mode' => gTranslate('config',"<b>safe_mode</b> is enabled on this server.") .
            toggleBox('check_safe_mode',
                sprintf(gTranslate('config',"<b>safe_mode</b> is enabled on this server.  You must disable it before you can use Gallery.  Refer to the FAQ, the forums, and the help page on the %sGallery website%s and the %sPHP website%s for assistance."),
                    '<a href="http://gallery.sourceforge.net" target="_blank">',
                    '</a>',
                    '<a href="http://www.php.net/manual/en/features.safe-mode.php" target="_blank">',
                    '</a>'
                ) .
                '<br><br>' .
                '<p class="g-emphasis">' . gTranslate('config',"Note:") . '</p>'.
                sprintf(gTranslate('config',"In some rare circumstances, Gallery thinks that Safe Mode is on, when it's really off.  You can see for yourself by looking in the <i>Local</i> column of the <i>safe_mode</i> row in the %sPHP Info%s page.  If it says 'Off' or '0' or 'undefined' then it's possible that Gallery made a mistake and you can continue.  If not, then you must resolve this problem before you can use Gallery."),
        		  '<a href="phpinfo.php" target="_blank">',
        		  '</a>')
            ),
		'optional' => 1,
		'serious' => 1
	),
	'exec' => array(
		'prompt' => gTranslate('config',"PHP's exec() function enabled"),
		'desc' => gTranslate('config',"The <b>exec()</b> function is what Gallery uses to launch programs such as Netpbm, ImageMagick, and jhead.  If <b>exec()</b> is disabled on your server then Gallery will not be able operate."),
		'func' => 'check_exec',
		'fail-exec' => gTranslate('config',"The <b>exec()</b> function is disabled on your server.  Gallery will not function with this PHP configuration."),
		'serious' => 1,
	),
	'htaccess' => array(
		'prompt' => gTranslate('config',".htaccess processed by Apache"),
		'desc' => gTranslate('config',"Allow the Gallery <b>.htaccess</b> file to set application specific configuration options for the Gallery application.  This is controlled by the webserver owner so it's not always possible for you to change it.  The main drawbacks to not having this are that you may not be able to upload files > 2MB (e.g. big ZIP files or movies)."),
		'func' => 'check_htaccess',
		'fail-htaccess' => gTranslate('config',"Your webserver is not obeying the 'php_value' lines in your <b>.htaccess</b> file. Try entering the following into your web server's <b>httpd.conf</b> file:") .'<br><pre>' .
		  '&lt;Directory ' . GALLERY_BASE . "&gt;\n" .
		  "\tAllowOverride Options FileInfo\n" .
		  "&lt;/Directory&gt;</pre>\n" .
		  gTranslate('config',"If you are running PHP in CGI mode, this message is unavoidable."),
		'fail-nohtaccess' => gTranslate('config',"Gallery could not test if your webserver is obeying the 'php_value' lines in your <b>.htaccess</b> as the (original) .htaccess file in your setup folder is missing."),
		'optional' => 1
	),
	'magic-quotes' => array(
		'prompt' => gTranslate('config',"PHPs magic quotes off"),
		'desc' => sprintf(gTranslate('config',"Check to see if %smagic quotes%s are enabled on your PHP server.  If they're on, you may notice strange behavior if you put ' (single-quote), &quot; (double quote) or &#92; (backslash) characters in titles or captions in Gallery."),
		  '<b><a href="http://www.php.net/manual/en/configuration.php#ini.magic-quotes-gpc" target="_blank">',
		  '</a></b>'),
		'func' => 'check_magic_quotes',
		'fail-magic-quotes' => sprintf(gTranslate('config',"Magic quotes are on.  Your webserver administrator can turn them off by putting %s in your <b>php.ini</b> file."),
		  ": <pre>\n\tmagic_quotes_gpc = Off</pre>"),
		'optional' => 1
	),
	'mod-rewrite' => array(
		'prompt' => gTranslate('config',"Apache mod_rewrite"),
		'desc' => gTranslate('config',"Check to see if your webserver is compiled with <b>mod_rewrite</b>.  This Apache module allows Gallery to use shorter URLs that are easier to read and look nicer when you mail them to your web-challenged relatives.  It's not an essential feature."),
		'func' => 'check_mod_rewrite',
		'fail-mod-rewrite' => gTranslate('config',"Either <b>mod_rewrite</b> is not installed or your <b>.htaccess</b> file is not enabled (see above).") .
					toggleBox('check_mod_rewrite',
					gTranslate('config', "Either way, we'll have to use longer URLs in the Gallery.  If you want to turn it on, first make sure that your <b>.htaccess</b> file is being obeyed. If it still doesn't work, you may need to reconfigure and rebuild Apache with this flag: ") .
					"<pre>\t--enable-module=rewrite</pre>"),
		'fail-mod-rewrite-nohtaccess' => gTranslate('config',"Gallery could not test if <b>mod_rewrite</b> is working, as the (original) .htaccess file in your setup folder is missing."),
		'optional' => 1
	),
	'Netpbm' => array(
		'prompt' => gTranslate('config',"Netpbm installed"),
    	'desc' => gTranslate('config',"Check to see if Netpbm is installed so that Gallery can manipulate images (rotate, resize, make thumbnails, etc.).  You only need to have either Netpbm <b>or</b> ImageMagick installed."),
		'func' => 'check_graphics',
		'fail-netpbm' => gTranslate('config',"Netpbm was not found") .
					toggleBox('checkNetpbm', gTranslate('config',
					"If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path to Netpbm by hand on the following page.  If it's not installed, you have two choices: " ) .
					$common_messages['getting-netpbm']),
		'fail-netpbm-partial' => gTranslate('config',"I've found part of Netpbm, but some components are missing (see below).") .
						toggleBox('fail_netpbm_partial', gTranslate('config',"Perhaps your version of Netpbm is out of date.") . '  ' .
						$common_messages['getting-netpbm'] .
						'<p><b> ' . gTranslate('config',"If Netpbm is installed and Gallery isn't finding it automatically that's OK&#8212;you can specify the path to Netpbm by hand on the following page.") .' </b>'
		),
		'open-basedir-note' => $common_messages['open-basedir-active'],
		'optional' => 1,
	),
	'ImageMagick' => array(
		'prompt' => gTranslate('config',"ImageMagick installed"),
		'optional' => 1,
		'desc' => gTranslate('config',"Check to see if ImageMagick is installed so that Gallery can manipulate images (rotate, resize, make thumbnails, etc.).  You only need to have either ImageMagick <b>or</b> Netpbm installed."),
		'func' => 'check_graphics_im',
		'fail-imagemagick' => gTranslate('config',"ImageMagick was not found.") .
					toggleBox('fail_imagemagick', gTranslate('config',
					"If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path to ImageMagick by hand on the following page.  If it's not installed, you must install it.  ") .
					$common_messages['getting-imagemagick']),
		'fail-imagemagick-partial' => gTranslate('config',"I've found part of ImageMagick, but some components are missing (see below).") .
						toggleBox('fail_imagemagick_partial',
						$common_messages['getting-imagemagick'] .
						'<p><b> ' . gTranslate('config',"If ImageMagick is installed and Gallery isn't finding it automatically that's OK&#8212;you can specify the path to ImageMagick by hand on the following page.") .' </b>'
            ),
		  'open-basedir-note' => $common_messages['open-basedir-active']
	),
	'exif' => array(
		'prompt' => gTranslate('config',"Jhead installed"),
		'optional' => 1,
		'desc' => gTranslate('config',"Check to see if <b>jhead</b> is installed so that Gallery can examine EXIF headers embedded in images created with most digital cameras."),
		'func' => 'check_exif',
		'fail-exif' => gTranslate('config',"jhead was not found.") .
				toggleBox('check_exif', sprintf(gTranslate('core',
				"If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path by hand on the following page.  If it's not installed, you can install it yourself. Jhead is a public domain EXIF parser.  Source, linux binaries, and windows binaries can be found at the %sjhead homepage%s."),
				'<a href="http://www.sentex.net/~mwandel/jhead/index.html" target="_blank">', '</a>')),
				'open-basedir-note' => $common_messages['open-basedir-active']
		),
	'jpegtran' => array (
		'prompt' => gTranslate('config',"jpegtran installed"),
		'optional' => 1,
		'desc' => gTranslate('config',"Check to see if <b>jpegtran</b> is installed so that Gallery can perform lossless rotations on JPEG images."),
		'func' => 'check_jpegtran',
		'fail-jpegtran' => gTranslate('config',"jpegtran was not found.") .
					toggleBox('check_jpegtran', sprintf(gTranslate('config', "If it's installed and not in the path of the webserver user that's OK&#8212;you can specify the path by hand on the following page.  If it's not installed, you can install it yourself.  Jpegtran is a free tool distributed by the Independent JPEG Group as part of <i>libjpeg</i>.  Source may be found at the %sIJG homepage%s.  Binaries may be found at the %sJPEG Club website%s or in the <i>libjpeg</i> package of Linux distributions."),
					'<a href="http://www.ijg.org" target="_blank">', '</a>',
					'<a href="http://jpegclub.org/" target="_blank">', '</a>')),
		  'open-basedir-note' => $common_messages['open-basedir-active']
	),
	'gettext' => array (
		'prompt' => gTranslate('config',"Gettext support in PHP"),
		'optional' => 1,
		'desc' => gTranslate('config',"Check to see if PHP is compiled with <b>GNU gettext</b> support for fast translations."),
		'func' => 'check_gettext',
		'fail-gettext' => sprintf(gTranslate('config',"Your PHP does not have <b>GNU gettext</b> support installed.  To include it, you'll need to recompile PHP (see the %sPHP docs%s for more information). Multi-language support is still available in Gallery without <b>gettext</b>, but the pages may take slightly longer to load."),
					'<a href="http://www.php.net/manual/en/ref.gettext.php" target="_blank">', '</a>')
	),
	'gallery-locale' => array (
		'prompt' => gTranslate('config',"Locales needed for Gallery"),
		'optional' => 1,
		'desc' => gTranslate('config',"Check to see what languages are available."),
		'func' => 'check_gallery_languages'
	),
	'missing-locales' => array (
		'prompt' => gTranslate('config',"missing locales"),
		'optional' => 1,
		'enabled' => (sizeof(gallery_languages()) > 1) ? 'yes' :'no',
		'desc' => gTranslate('config',"Testing to see which <b>system</b> locales (if any) are absent.") . '<br>'.
				gTranslate('config',"These locales are required to use multilanguage capabilities of Gallery when your PHP has gettext support. They are also needed to format dates and times according to the selected language."),
		'func' => 'check_absent_locales',
		'serious' => ( $elawisyl == 'no' ) ? 1 : 0 ,
		),
	'check-versions' => array (
		'prompt' => gTranslate('config',"File integrity"),
		'desc' => gTranslate('config',"Checking to see if you have the latest versions of all Gallery files."),
		'func' => 'check_gallery_versions',
		'serious' => 1,
		'optional' => 1,
	),
);

$checkPriority = array(
    'mandatory' => array(
        'checkslist' => array('gallery_version', 'php', 'safe_mode', 'exec',
                             'Netpbm', 'ImageMagick', 'check-versions'),
        'shortdesc' => gTranslate('config',"Mandatory checks"),
        'longdesc' => gTranslate('config' ,"If any of these items are highlighted in red then please take actions to correct them. Failure to do so could lead to your Gallery installation not functioning correctly.")
    ),
    'recommend' => array(
        'checkslist' => array('register_globals', 'htaccess', 'magic-quotes', 'mod-rewrite', 'exif',
                             'jpegtran', 'gettext', 'gallery-locale', 'missing-locales'),
        'shortdesc' => gTranslate('config',"Recommended things"),
        'longdesc' => gTranslate('config' ,"Nice to have.")
    )
);

?>
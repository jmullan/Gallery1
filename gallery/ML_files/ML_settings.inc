<?php

// If theres an error switch to Mode 2
//
if ($gallery->ML->error and $gallery->ML->ignore_errors =='no') $gallery->ML->mode=2;

// Set Language
        putenv("LANG=". $gallery->ML->locale);

// Set Local
	setlocale(LC_ALL,$gallery->ML->locale);

if ($gallery->ML->gettext_ok == 2) {

	//Name and Position for the .mo translation files
		$bindtextdomain=bindtextdomain("gallery", $gallery->ML->locale_path);
		textdomain("gallery");
}

//Set Charset
        header('Content-Type: text/html; charset=' . $gallery->ML->charset);
?>
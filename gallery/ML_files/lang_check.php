<?php

include "./ML_lang_alias.php";

	$lang = explode (",", $HTTP_SERVER_VARS["HTTP_ACCEPT_LANGUAGE"]);
        $lang_pieces=explode ("-",$lang[0]);

        if (strlen($lang[0]) ==2) {
                $browser_language=$lang[0] ."_".strtoupper($lang[0]);
        }
        else {
		$browser_language=strtolower($lang_pieces[0])."_".strtoupper($lang_pieces[1]) ;
        }

if ($langalias[$browser_language]) {
	$language= $langalias[$browser_language] ;
}
else {
	$language = $browser_language;
}
?>
<html>
        <header>
        <title>Language Check</title>
        </header>
<body>

<h3 style="position:absolute; left:200px">
Your Browser accepts this languages :
<?php = $HTTP_SERVER_VARS["HTTP_ACCEPT_LANGUAGE"] ; ?>
<hr width="80%">	
<br>
The First Language is : <?php = $lang[0] ?>.
<br>
The Multilanguage Version interprets this as : <?php = $browser_language ?>.
<?php
	if ($langalias[$browser_language]) {
        $language= $langalias[$browser_language] ;
	echo "<br><br>There is an alias for $browser_language : $language";
}
else {
        $language = $browser_language;
}
?>
<hr width="80%">	
<br>so ML Gallery will try to find : <?php = $language ?>-gallery.po in &lt;path to gallery&gt;/po/
<br><br>respective gallery.mo in &lt;path to gallery&gt;/locale/<?php = $language ?>/LC_MESSAGES/

<hr width="80%">
<center>	
<?php
	if ($langname[$language]) echo "Gallery should appear in $langname[$language]";
?>
</center>
<?php

if (! $locale[$language]) $locale[$language]=$language;

?>
<hr width="80%">	
<p>
Now we do a locale Test. Gallery will use : <?php = $locale[$language] ?>
</p>
<p>
<?php

        echo "WEEKDAY in given language : ";

        setlocale (LC_ALL, "fr_FR");
        echo "\n <br><br>"  . strftime ("%A") . " in French / fr_FR" ;

        setlocale (LC_ALL, "de_DE");
        echo "\n <br><br>"  . strftime ("%A") . " in German / de_DE" ;

	setlocale(LC_ALL,$locale[$language]);
        echo "\n <br><br>"  . strftime ("%A") . " in the language / locale Gallery detects ($langname[$language] / $locale[$language])" ;
?>
</p>
<?php
$locale_=shell_exec("locale -a");
if (! stristr ($locale_, $locale[$language])) {
	echo "<b>LOCALE ERROR !!!</b><br><br>";
	echo "Your System excepts only knows these <a href=locales.php>locales</a>";
}

?>
</body>
</html>
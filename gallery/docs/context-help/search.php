<?php 
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
                !empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
                !empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
        print _("Security violation")."\n";
        exit;
}


$GALLERY_BASEDIR="../../";

require ("../../init.php");


  ?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Gallery Context Help</title>
<?php 
     echo getStyleSheetLink();
    ?>
</head>
<body>
<a name="top"></a>
 <span class="popuphead">Search&nbsp;<a href="#" onclick="javascript: window.close()">[Close Window]</a></span>

 <p>
  Searching allows you to search for albums and pictures, just like a search
  engine.  Wildcards (i.e. an astrick) are allowed, and the search is
  case-insensitive.
 </p>
</body>
</html>

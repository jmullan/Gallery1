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
 <span class="popuphead">Slideshow&nbsp;<a href="#" onclick="javascript: window.close()">[Close Window]</a></span>
 
 <p>
  The slideshow feature of Gallery allows you to view all the photos in a
  particular album (or, if enabled, recursively through every album or
  sub-album).  You can pause and restart the slideshow and configure the
  delay between each photo.
 </p>
</body>
</html>

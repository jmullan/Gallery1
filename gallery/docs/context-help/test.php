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
 
  
   <span style="font-size: 13px; font-weight: bold"><a name="test.bleh">Hello</a></span>
    
    <p>
     <em>Test</em> Hi!
    </p>
  
 
</body>
</html>

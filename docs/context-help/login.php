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
 <span class="popuphead">Login&nbsp;<a href="#" onclick="javascript: window.close()">[Close Window]</a></span>
 
 <p>
  Logging in to Gallery allows you to receive more permissions, in order to
  view, create, modify, and delete certain albums.  Simply type your login
  name and password, and you will receive the new permissions.
 </p>
 <p>
  The default username for the admin account (the one created in the
  configuration wizard) is <strong>admin</strong>.  The password is what
  you specified in the configuration wizard.
 </p>
 <p>
  If you have trouble logging in, be sure to check with your admin and
  the <a href="#" onclick="javascript:window.opener.location.href='http://gallery.sf.net/faq.php'">Gallery FAQ</a>
 </p>
</body>
</html>

<script type="text/javascript">
<!--

function Fensterweite()
{
 if (window.innerWidth) return window.innerWidth;
 else if (document.body && document.body.offsetWidth) return document.body.offsetWidth;
 else return 0;
}

function Fensterhoehe()
{
 if (window.innerHeight) return window.innerHeight;
 else if (document.body && document.body.offsetHeight) return document.body.offsetHeight;
 else return 0;
}


function neuAufbau()
{
 if (Weite != Fensterweite() || Hoehe != Fensterhoehe())
 window.history.go(0);
}

/*Überwachung von Netscape initialisieren*/
if(!window.Weite && window.innerWidth)
  {
   window.onresize = neuAufbau;
   Weite = Fensterweite();
   Hoehe = Fensterhoehe();
  }

//-->
</script>

<script type="text/javascript">
<!--
 /*Überwachung von MS Internet Explorer initialisieren*/
 if(!window.Weite && document.body && document.body.offsetWidth)
  {
   window.onresize = neuAufbau;
   Weite = Fensterweite();
   Hoehe = Fensterhoehe();
  }
//-->
</script>

<script type="text/javascript">
<!--
	var randbreite=100;
	var randhoehe=275;

	var imagewidth=<?php echo $imageWidth; ?>;
	var imageheight=<?php echo $imageHeight; ?>;
	var imageratio = imagewidth/imageheight;

if ( imagewidth > Weite-randbreite) {
	imagewidth=Weite-randbreite;
	imageheight=imagewidth/imageratio;
}

if (imageheight > Hoehe-randhoehe) {
	imageheight = Hoehe-randhoehe;
	imagewidth = imageheight*imageratio;
}
//-->
</script>
<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		xmlns:exsl="http://exslt.org/common"
		version="1.0"
		exclude-result-prefixes="exsl">
		
 <xsl:output method="html"/>
 
 <xsl:template match="/">
  <!-- Get title -->

  <xsl:processing-instruction name="php">
<![CDATA[
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
                !empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
                !empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
        print _("Security violation")."\n";
        exit;
}


$GALLERY_BASEDIR="../../";

require ("../../init.php");

]]>
  ?</xsl:processing-instruction>
  
  <html>
   <head>
    <title>Gallery Context Help</title>
    <xsl:processing-instruction name="php">
     echo getStyleSheetLink();
    ?</xsl:processing-instruction>
   </head>
   <body>
    <a name="top"></a>

    <xsl:apply-templates/>
   </body>
  </html>
  
 </xsl:template>

 <xsl:template match="item/title">
  <span class="popuphead">
   <xsl:apply-templates/>

   <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>

   <a href="#" onclick="javascript: window.close()">[Close Window]</a>   
  </span>
 </xsl:template>
 
 <xsl:template match="item/title" mode="var">
  <xsl:apply-templates/>
 </xsl:template>

 <xsl:template match="sect1/title">
  <span style="font-size: 13px; font-weight: bold">
   <a>
    <xsl:attribute name="name">
     <xsl:value-of select="../@id"/>    
    </xsl:attribute>
    
    <xsl:apply-templates/>
   </a>
  </span>
 </xsl:template>
 
 <xsl:template match="sect1">
  <xsl:apply-templates/>
 </xsl:template>

 <xsl:template match="sect2/title">
  <span style="font-size: 12px; font-weight: bold">
   <a>
    <xsl:attribute name="name">
     <xsl:value-of select="../@id"/>    
    </xsl:attribute>

    <xsl:apply-templates/>
   </a>
  </span>
 </xsl:template> 
 
 <xsl:template match="sect2">
  <xsl:apply-templates/>
 </xsl:template>
 
 <xsl:template match="link">
  <a>
   <xsl:attribute name="href" select="@linkend"/>
   <xsl:apply-templates/>
  </a>
 </xsl:template>
 
 <xsl:template match="ulink">
  <a href="#">
   <xsl:attribute name="onclick">
    <xsl:text>javascript:window.opener.location.href='</xsl:text><xsl:value-of select="@url"/><xsl:text>'; window.close();</xsl:text>
   </xsl:attribute>    
   <xsl:apply-templates/>
  </a>
 </xsl:template>
 
 <xsl:template match="img">
  <img>
   <xsl:attribute name="src" select="@src"/>
   <xsl:attribute name="align" select="@align"/> 
   <xsl:attribute name="height" select="@height"/>  
   <xsl:attribute name="width" select="@width"/>    
  </img>
 </xsl:template>
 
 <xsl:template match="para">
  <p>
   <xsl:apply-templates/>
  </p>
 </xsl:template>
 
 <xsl:template match="strong">
  <strong>
   <xsl:apply-templates/>
  </strong>
 </xsl:template>
 
 <xsl:template match="em">
  <em>
   <xsl:apply-templates/>
  </em>
 </xsl:template>
 
 <xsl:template match="list">
  <ul>
   <xsl:apply-templates/>
  </ul>
 </xsl:template>
 
 <xsl:template match="listitem">
  <li>
   <xsl:apply-templates/>
  </li>
 </xsl:template>
  
</xsl:stylesheet>

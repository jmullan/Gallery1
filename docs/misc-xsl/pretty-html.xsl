<?xml version="1.0" encoding="iso-8859-1"?>

<!--
Created by Andrew Lindeman
Inspired by the stylesheets used on the PHP documentation

$Id$
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

 <xsl:import href="../docbook-xsl/html/chunkfast.xsl"/>
 <xsl:import href="common.xsl"/> 

 <xsl:template name="chunk-element-content">
  <xsl:param name="prev"/>
  <xsl:param name="next"/>
  <xsl:param name="nav.context"/>
  <xsl:param name="content">
    <xsl:apply-imports/>
  </xsl:param>
  
  <html>
    <xsl:call-template name="html.head">
      <xsl:with-param name="prev" select="$prev"/>
      <xsl:with-param name="next" select="$next"/>
    </xsl:call-template>

    <body>
      <xsl:call-template name="body.attributes"/>

      <table width="100%" height="100%" cellpadding="0" cellspacing="0" class="table-whole">
       <tr>
        <td colspan="3" class="table-header" height="1">
         Gallery Logo
        </td>
       </tr>
       <tr>
        <td width="170" valign="top" class="table-menu">
         <xsl:call-template name="gallery.nav">
          <xsl:with-param name="node" select="."/>
         </xsl:call-template>
        </td>
        <td class="table-spacer" width="5">
         <xsl:text>  </xsl:text>
        </td>
        <td valign="top" class="table-content" width="*">
         <xsl:copy-of select="$content"/>
        </td>
       </tr>
      </table>
    </body>
  </html>
 </xsl:template>
 
 <xsl:template name="gallery.nav">
  <xsl:param name="node" select="/foo"/>
 
  <xsl:variable name="title">
   <xsl:apply-templates select="$node" mode="gallery.title.nochapter"/>
  </xsl:variable>
  
  <xsl:variable name="home" select="/*[1]"/>
 
  <xsl:variable name="parent" select="parent::*"/>
  
  <xsl:variable name="parent_title"> 
   <xsl:apply-templates select="$parent" mode="galleryweb.title.nochapter"/>
  </xsl:variable>
  
  <xsl:variable name="home_title">
   <xsl:apply-templates select="$home" mode="galleryweb.title.nochapter"/>
  </xsl:variable>
 
  <a>
   <xsl:attribute name="href">
    <xsl:call-template name="href.target">
     <xsl:with-param name="object" select="/*[1]"/>
    </xsl:call-template>
   </xsl:attribute>
   
   <xsl:attribute name="class">
    <xsl:text>up-up-link</xsl:text>
   </xsl:attribute>
   
   <img src="images/up-up.gif" border="0"/>       
   
   <xsl:value-of select="$home_title"/>
  </a>
  
  <hr class="hr-small" noshade="yes" align="left" size="1"/>
 
  <xsl:if test="$parent_title != '' and $home_title != $parent_title">
   <a>
    <xsl:attribute name="href">
     <xsl:call-template name="href.target">
      <xsl:with-param name="object" select="parent::*"/>
     </xsl:call-template>
    </xsl:attribute>
    
    <xsl:attribute name="class">
     <xsl:text>up-link</xsl:text>
    </xsl:attribute>
    
    <img src="images/up.gif" border="0"/>
    
    <xsl:value-of select="$parent_title"/>
   </a>
  </xsl:if>
  
  <ul type="square">
  
  <xsl:for-each select="../*">
   <xsl:variable name="ischunk">
    <xsl:call-template name="chunk"/>
   </xsl:variable>
   
   <xsl:if test="$ischunk = '1'">
    <li>
     <a>
      <xsl:attribute name="href">
       <xsl:call-template name="href.target">
        <xsl:with-param name="object" select="."/>
       </xsl:call-template>
      </xsl:attribute>
     
      <xsl:attribute name="class">
       <xsl:text>menu-link</xsl:text>
      </xsl:attribute>
      
       <xsl:choose>
        <xsl:when test="$node = .">
         <b>
          <xsl:apply-templates select="." mode="galleryweb.title.nochapter"/>
         </b>
        </xsl:when>
        <xsl:otherwise>
         <xsl:apply-templates select="." mode="galleryweb.title.nochapter"/>
        </xsl:otherwise>
       </xsl:choose>
     </a>
    </li>
   </xsl:if>
  </xsl:for-each>
  
  </ul>
 
 </xsl:template>

</xsl:stylesheet> 

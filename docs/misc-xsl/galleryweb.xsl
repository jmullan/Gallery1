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

 <xsl:param name="html.ext" select="'.php'"/>

 <!-- END CUSTOM VARS --> 
 
<xsl:template name="header.navigation">
 <xsl:param name="prev" select="/foo"/>
 <xsl:param name="next" select="/foo"/>
 <xsl:variable name="home" select="/*[1]"/>
 <xsl:variable name="up" select="parent::*"/>

 <xsl:processing-instruction name="php">
  <xsl:text disable-output-escaping="yes">
   $navigation = array (
        'next' =&gt; "</xsl:text>
        
   <xsl:call-template name="galleryweb.title">
    <xsl:with-param name="obj" select="$next"/>
   </xsl:call-template>
   
   <xsl:text disable-output-escaping="yes">",
        'prev' =&gt; "</xsl:text>
        
   <xsl:call-template name="galleryweb.title">
    <xsl:with-param name="obj" select="$prev"/>
   </xsl:call-template>

   <xsl:text disable-output-escaping="yes">",
        'home' =&gt; "</xsl:text>
        
   <xsl:call-template name="galleryweb.title">
    <xsl:with-param name="obj" select="$home"/>
   </xsl:call-template>
   
   <xsl:text disable-output-escaping="yes">",
        'up' =&gt; "</xsl:text>
        
   <xsl:call-template name="galleryweb.title">
    <xsl:with-param name="obj" select="$up"/>
   </xsl:call-template>   

   <xsl:text disable-output-escaping="yes">",
        'this' =&gt; "</xsl:text>
        
   <xsl:call-template name="galleryweb.title">
    <xsl:with-param name="obj" select="."/>
   </xsl:call-template> 
   
   <xsl:text disable-output-escaping="yes">");
   </xsl:text>

   <xsl:variable name="tmpnameme">
    <xsl:apply-templates select="." mode="object.title.markup"/>
   </xsl:variable>
   
   <xsl:variable name="tmpnameparent">
    <xsl:call-template name="galleryweb.title">
     <xsl:with-param name="obj" select="$up"/>
    </xsl:call-template>   
   </xsl:variable>

   <xsl:choose>
    <xsl:when test="$tmpnameparent = 'modules.php?op=modload&amp;name=GalleryDocs&amp;file=index&amp;page=index.php'">
     <xsl:variable name="tmptitle">
      <xsl:apply-templates select="." mode="galleryweb.title.nochapter"/>
     </xsl:variable>
     <xsl:variable name="tmpparenttitle">
     </xsl:variable>
    </xsl:when>
    <xsl:otherwise>
     <xsl:variable name="tmptitle">
      <xsl:value-of select="$tmpnameme"/>
     </xsl:variable>
     <xsl:variable name="tmpparenttitle">
      <xsl:value-of select="$tmpnameparent"/>
     </xsl:variable>
    </xsl:otherwise>
   </xsl:choose>
   
   <xsl:text disable-output-escaping="yes">
    $data = array (
        'title' =&gt; "</xsl:text>

   <xsl:choose>
    <xsl:when test="$tmpnameparent = 'modules.php?op=modload&amp;name=GalleryDocs&amp;file=index&amp;page=index.php'">
     <xsl:apply-templates select="." mode="galleryweb.title.nochapter"/>
    </xsl:when>
    <xsl:otherwise>
     <xsl:apply-templates select="." mode="object.title.markup"/>
    </xsl:otherwise>
   </xsl:choose>
   
   <xsl:text disable-output-escaping="yes">",
        'parent_title' =&gt; "</xsl:text>
   
   <xsl:choose>
    <xsl:when test="$tmpnameparent != 'index.php'">
     <xsl:apply-templates select="$up" mode="galleryweb.title.nochapter"/>
    </xsl:when>
   </xsl:choose>
  
   <xsl:text disable-output-escaping="yes">");
   
   if (!eregi("modules.php", $PHP_SELF)) {
   	die ("You can't access this file directly");
   }
   
   cleanNavigation();
   </xsl:text> 
 ?</xsl:processing-instruction>

 <!--Docbook Common Stuff-->

  <xsl:variable name="row1" select="$navig.showtitles != 0"/>
  <xsl:variable name="row2" select="count($prev) &gt; 0
                                    or (count($up) &gt; 0 
                                        and $up != $home
                                        and $navig.showtitles != 0)
                                    or count($next) &gt; 0"/>

  <xsl:if test="$suppress.navigation = '0' and $suppress.header.navigation = '0'">
    <div class="navheader">
      <xsl:if test="$row1 or $row2">
        <table width="100%" summary="Navigation header">
          <xsl:if test="$row1">
            <tr>
              <th colspan="3" align="center">
                <xsl:apply-templates select="." mode="object.title.markup"/>
              </th>
            </tr>
          </xsl:if>

          <xsl:if test="$row2">
            <tr>
              <td width="20%" align="left">
                <xsl:if test="count($prev)>0">
                  <a accesskey="p">
                    <xsl:attribute name="href">
                      <xsl:call-template name="href.target">
                        <xsl:with-param name="object" select="$prev"/>
                      </xsl:call-template>
                    </xsl:attribute>
                    <xsl:call-template name="navig.content">
                      <xsl:with-param name="direction" select="'prev'"/>
                    </xsl:call-template>
                  </a>
                </xsl:if>
                <xsl:text>&#160;</xsl:text>
              </td>
              <th width="60%" align="center">
                <xsl:choose>
                  <xsl:when test="count($up) > 0
                                  and $up != $home
                                  and $navig.showtitles != 0">
                    <xsl:apply-templates select="$up" mode="object.title.markup"/>
                  </xsl:when>
                  <xsl:otherwise>&#160;</xsl:otherwise>
                </xsl:choose>
              </th>
              <td width="20%" align="right">
                <xsl:text>&#160;</xsl:text>
                <xsl:if test="count($next)>0">
                  <a accesskey="n">
                    <xsl:attribute name="href">
                      <xsl:call-template name="href.target">
                        <xsl:with-param name="object" select="$next"/>
                      </xsl:call-template>
                    </xsl:attribute>
                    <xsl:call-template name="navig.content">
                      <xsl:with-param name="direction" select="'next'"/>
                    </xsl:call-template>
                  </a>
                </xsl:if>
              </td>
            </tr>
          </xsl:if>
        </table>
      </xsl:if>
      <xsl:if test="$header.rule != 0">
        <hr/>
      </xsl:if>
    </div>
  </xsl:if>
<!--End Common Stuff-->

</xsl:template>

<xsl:template name="galleryweb.title">
 <xsl:param name="obj" select="/foo"/>

 <xsl:call-template name="href.target">
  <xsl:with-param name="object" select="$obj"/>
 </xsl:call-template>


</xsl:template>



<xsl:template name="href.target.uri">
  <xsl:param name="object" select="."/>
  <xsl:variable name="ischunk">
    <xsl:call-template name="chunk">
      <xsl:with-param name="node" select="$object"/>
    </xsl:call-template>
  </xsl:variable>

  <xsl:apply-templates mode="chunk-filename" select="$object"/>

  <xsl:if test="$ischunk='0'">
    <xsl:text>#</xsl:text>
    <xsl:call-template name="object.id">
      <xsl:with-param name="object" select="$object"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="href.target">
  <xsl:param name="context" select="."/>
  <xsl:param name="object" select="."/>

  <xsl:variable name="href.to.uri">
    <xsl:call-template name="href.target.uri">
      <xsl:with-param name="object" select="$object"/>
    </xsl:call-template>
  </xsl:variable>

  <xsl:variable name="href.from.uri">
    <xsl:call-template name="href.target.uri">
      <xsl:with-param name="object" select="$context"/>
    </xsl:call-template>
  </xsl:variable>

  <xsl:variable name="href.to">
    <xsl:call-template name="trim.common.uri.paths">
      <xsl:with-param name="uriA" select="$href.to.uri"/>
      <xsl:with-param name="uriB" select="$href.from.uri"/>
      <xsl:with-param name="return" select="'A'"/>
    </xsl:call-template>
  </xsl:variable>

  <xsl:variable name="href.from">
    <xsl:call-template name="trim.common.uri.paths">
      <xsl:with-param name="uriA" select="$href.to.uri"/>
      <xsl:with-param name="uriB" select="$href.from.uri"/>
      <xsl:with-param name="return" select="'B'"/>
    </xsl:call-template>
  </xsl:variable>

  <xsl:variable name="depth">
    <xsl:call-template name="count.uri.path.depth">
      <xsl:with-param name="filename" select="$href.from"/>
    </xsl:call-template>
  </xsl:variable>

  <xsl:variable name="href">
   <!-- CUSTOM CODE -->
    <xsl:text>modules.php?op=modload&amp;name=GalleryDocs&amp;file=index&amp;page=</xsl:text>
   <!-- END CUSTOM CODE -->
    <xsl:call-template name="copy-string">
      <xsl:with-param name="string" select="'../'"/>
      <xsl:with-param name="count" select="$depth"/>
    </xsl:call-template>
    <xsl:value-of select="$href.to"/>
  </xsl:variable>

<!--
  <xsl:message>
    <xsl:text>In </xsl:text>
    <xsl:value-of select="name(.)"/>
    <xsl:text> (</xsl:text>
    <xsl:value-of select="$href.from"/>
    <xsl:text>,</xsl:text>
    <xsl:value-of select="$depth"/>
    <xsl:text>) </xsl:text>
    <xsl:value-of select="name($object)"/>
    <xsl:text> href=</xsl:text>
    <xsl:value-of select="$href"/>
  </xsl:message>
-->

  <xsl:value-of select="$href"/>
</xsl:template>

</xsl:stylesheet>

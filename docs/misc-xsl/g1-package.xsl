<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

 <xsl:import href="../docbook-xsl/html/chunkfast.xsl"/>
 
<xsl:template match="chapter">
  <xsl:if test="substring (@id, 0, 10) = 'gallery1-'">
   <xsl:call-template name="process-chunk"/>
  </xsl:if>
</xsl:template>
 
 <xsl:template match="preface|chapter|appendix|article" mode="toc">
  <xsl:param name="toc-context" select="."/>

  <xsl:if test="@id = 'preface' or substring (@id, 0, 10) = 'gallery1-'">

  <xsl:call-template name="subtoc">
    <xsl:with-param name="toc-context" select="$toc-context"/>
    <xsl:with-param name="nodes" select="section|sect1|glossary|bibliography|index
                                         |bridgehead[$bridgehead.in.toc != 0]"/>
  </xsl:call-template>
  
 </xsl:if> 
</xsl:template>
 
</xsl:stylesheet>

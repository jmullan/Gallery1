<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

  <xsl:strip-space elements="*"/>
  <xsl:output method="text"/>

  <xsl:template match="/book">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="chapter" priority="0.9">
    <xsl:for-each select="sect1/sect2/qandaset/qandadiv/qandaentry">
      <xsl:value-of select="../../../@id"/> | <xsl:value-of select="@id"/> | <xsl:value-of select="substring(normalize-space(question/para), 1, 50)"/>...
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="*" priority="0.1"/>
</xsl:stylesheet>

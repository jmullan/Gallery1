<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/TR/xhtml1/strict">
	<xsl:strip-space elements="doc chapter section"/>
	<xsl:output method="xml" indent="yes" encoding="iso-8859-1"/>
	<xsl:template match="locale">
		<tr>
			<xsl:apply-templates select="nr"/>
			<xsl:apply-templates select="language"/>
			<td>
				<xsl:attribute name="class"><xsl:value-of select="@scheme"/></xsl:attribute>
				<xsl:value-of select="@id"/>
			</td>
			<xsl:apply-templates select="percent_done"/>
			<xsl:apply-templates select="lines"/>
			<xsl:apply-templates select="translated"/>
			<xsl:apply-templates select="fuzzy"/>
			<xsl:apply-templates select="untranslated"/>
			<xsl:apply-templates select="obsolete"/>
		</tr>
	</xsl:template>
	<xsl:template match="nr">
		<td>
			<xsl:attribute name="class"><xsl:value-of select="@scheme"/></xsl:attribute>
			<xsl:choose>
				<xsl:when test="text() != 0">
					<xsl:apply-templates/>
				</xsl:when>
				<xsl:otherwise>
				&#160;
			</xsl:otherwise>
			</xsl:choose>
		</td>
	</xsl:template>
	<xsl:template match="language">
		<td>
			<xsl:attribute name="class"><xsl:value-of select="@scheme"/></xsl:attribute>
			<xsl:apply-templates/>
		</td>
	</xsl:template>
	<xsl:template match="percent_done">
		<td align="right">
			<xsl:attribute name="style"><xsl:value-of select="@style"/></xsl:attribute>
			<xsl:apply-templates/>
		</td>
	</xsl:template>
	<xsl:template match="lines">
		<td>
			<xsl:attribute name="class"><xsl:value-of select="@scheme"/></xsl:attribute>
			<xsl:apply-templates/>
		</td>
	</xsl:template>
	<xsl:template match="translated">
		<td>
			<xsl:attribute name="class"><xsl:value-of select="@scheme"/></xsl:attribute>
			<xsl:choose>
				<xsl:when test="text() != 0">
					<xsl:apply-templates/>
				</xsl:when>
				<xsl:otherwise>
				&#160;
			</xsl:otherwise>
			</xsl:choose>
		</td>
	</xsl:template>
	<xsl:template match="fuzzy">
		<td>
			<xsl:attribute name="class"><xsl:value-of select="@scheme"/></xsl:attribute>
			<xsl:choose>
				<xsl:when test="text() != 0">
					<xsl:apply-templates/>
				</xsl:when>
				<xsl:otherwise>
				&#160;
			</xsl:otherwise>
			</xsl:choose>
		</td>
	</xsl:template>
	<xsl:template match="untranslated">
		<td>
			<xsl:attribute name="class"><xsl:value-of select="@scheme"/></xsl:attribute>
			<xsl:choose>
				<xsl:when test="text() != 0">
					<xsl:apply-templates/>
				</xsl:when>
				<xsl:otherwise>
				&#160;
			</xsl:otherwise>
			</xsl:choose>
		</td>
	</xsl:template>
	<xsl:template match="obsolete">
		<td>
			<xsl:attribute name="class"><xsl:value-of select="@scheme"/></xsl:attribute>
			<xsl:choose>
				<xsl:when test="text() != 0">
					<xsl:apply-templates/>
				</xsl:when>
				<xsl:otherwise>
				&#160;
			</xsl:otherwise>
			</xsl:choose>
		</td>
	</xsl:template>

	<xsl:template match="total">
		<tr><td><xsl:attribute name="colspan"><xsl:value-of select="8"/></xsl:attribute>&#160;</td></tr>
		<tr><xsl:apply-templates/></tr>
	</xsl:template>

	<xsl:template match="languages">
		<td><xsl:apply-templates/></td>
	</xsl:template>

	<xsl:template match="t_percent_done">
		<td>
			<xsl:attribute name="align"><xsl:value-of select="@align"/></xsl:attribute>
			<xsl:attribute name="colspan"><xsl:value-of select="3"/></xsl:attribute>&#160;
			<xsl:apply-templates/> %
		</td>
		<td>
			<xsl:attribute name="colspan"><xsl:value-of select="4"/></xsl:attribute>
			&#160;
		</td>
	</xsl:template>

	<xsl:template match="report">
		<html>
			<head>
				<title>
			Localization Report for <xsl:value-of select="@date"/>
				</title>
				<link rel="stylesheet" type="text/css" href="g1-report.css"/>
			</head>
			<body>
				<h2>Localization Status Report for Gallery 1</h2>
				<h2>Build : <xsl:value-of select="@build"/>
				</h2>
				<h2>Generated: <xsl:value-of select="@date"/> at <xsl:value-of select="@time"/>
				</h2>
				<table align="center" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<th>Nr</th>
						<th>Language</th>
						<th>Locale</th>
						<th>Status</th>
						<th valign="bottom" style="width:30px;">A<br/>l<br/>l</th>
						<th valign="bottom" style="width:30px;">T<br/>r<br/>a<br/>n<br/>s<br/>l<br/>a<br/>t<br/>e<br/>d</th>
						<th valign="bottom" style="width:30px;">F<br/>u<br/>z<br/>z<br/>y</th>
						<th valign="bottom" style="width:30px;">U<br/>n<br/>t<br/>r<br/>a<br/>n<br/>s<br/>l<br/>a<br/>t<br/>e<br/>d</th>
						<th valign="bottom" style="width:30px;">O<br/>b<br/>s<br/>o<br/>l<br/>e<br/>t<br/>e</th>
					</tr>
					<xsl:apply-templates/>
				</table><br /><br />
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

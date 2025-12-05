<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="html" encoding="UTF-8" indent="yes" omit-xml-declaration="yes"/>

    <xsl:template match="/autoteenindus">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kuupäev</th>
                    <th>Kellaaeg</th>
                    <th>Nimi</th>
                    <th>Telefon</th>
                    <th>Teenus</th>
                    <th>Autonumber</th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="paev/broneering">
                    <tr>
                        <td><xsl:value-of select="../@kuupaev"/></td>
                        <td><xsl:value-of select="kellaaeg"/></td>
                        <td><xsl:value-of select="nimi"/></td>
                        <td><xsl:value-of select="telefon"/></td>
                        <td><xsl:value-of select="teenus"/></td>
                        <td><xsl:value-of select="@autonumber"/></td>
                    </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>

</xsl:stylesheet>

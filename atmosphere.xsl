<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" />

    <!-- Template principal -->
    <xsl:template match="/">
        <html>
            <head>
                <title>Prévisions Météo</title>
                <link rel="stylesheet" type="text/css" href="atmosphere.css" />
            </head>
            <body>
                <header>
                    <h1>Prévisions Météo</h1>
                </header>
                <main>
                    <section class="weather-section">
                        <div class="weather-card">
                            <h2>Matin</h2>
                            <xsl:apply-templates select="previsions/echeance[@hour = 6]" />
                        </div>
                        <div class="weather-card">
                            <h2>Midi</h2>
                            <xsl:apply-templates select="previsions/echeance[@hour = 12]" />
                        </div>
                        <div class="weather-card">
                            <h2>Soir</h2>
                            <xsl:apply-templates select="previsions/echeance[@hour = 18]" />
                        </div>
                    </section>
                </main>
            </body>
        </html>
    </xsl:template>

     <xsl:template match="echeance">
        <div class="weather-details">
            <div class="weather-temp">
                <span class="weather-icon">
                    <xsl:choose>
                        <xsl:when test="temperature/level/@val &lt; 0">❄️</xsl:when>
                        <xsl:otherwise>🌡️</xsl:otherwise>
                    </xsl:choose>
                </span>
                <span>
                    <xsl:value-of select="format-number(temperature/level[@val='2m'] - 273.15, '0.00')" /> °C
                </span>
            </div>
            <span class="weather-icon">
                <xsl:choose>
                    <xsl:when test="pluie &gt; 0">🌧️</xsl:when>
                    <xsl:otherwise>☀️</xsl:otherwise>
                </xsl:choose>
            </span>
        <div class="weather-wind">
            <span class="weather-icon">
                <xsl:choose>
                    <xsl:when test="vent_moyen/level/@val &gt; 20">💨</xsl:when>
                    <xsl:otherwise>🍃</xsl:otherwise>
                </xsl:choose>
            </span>
            <span>
                <xsl:value-of select="format-number(number(vent_moyen/level[@val='10m']), '#0.00')" /> km/h
            </span>
        </div>
        </div>
    </xsl:template>
</xsl:stylesheet>

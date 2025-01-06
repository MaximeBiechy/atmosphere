<?php
require_once 'circulation.php';
require_once 'qualiteAir.php';
require_once 'adresse.php';

$ip = $_SERVER['REMOTE_ADDR']; // ip du client
if ($ip != "127.0.0.1") {
    $url = "https://ipapi.co/$ip/xml/"; // api pour récupérer les coordonnées géographiques du client
} else {
    $url = "https://ipapi.co/xml/";
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
curl_setopt($ch, CURLOPT_PROXY, 'www-cache:3128');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$output = curl_exec($ch);
curl_close($ch);

if ($output === false) {
    echo('Erreur lors de la récupération des coordonnées géographiques.');
}

$xml = simplexml_load_string($output);

$latitude = $xml->latitude;
$longitude = $xml->longitude;

// api pour récupérer les informations météo
$infoClimatUrl = "https://www.infoclimat.fr/public-api/gfs/xml?_ll=$latitude,$longitude&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $infoClimatUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
curl_setopt($ch, CURLOPT_PROXY, 'www-cache:3128');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$output = curl_exec($ch);
curl_close($ch);

if ($output === false) {
    echo('Erreur lors de la récupération des informations météo.');
}

$xml = new DOMDocument;
if (!$xml->loadXML($output)) {
    echo('Erreur lors du chargement du xml');
}

$xsl = new DOMDocument;
$xsl->load('atmosphere.xsl');

$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl);

echo $proc->transformToXML($xml);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prévisions Météo</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <link rel="stylesheet" type="text/css" href="atmosphere.css" />
</head>
<body>

<div id="map"></div>
<h2 id="qualite" style="text-align: center;"></h2>
<div style="width: 100dvw; border-bottom: 1px solid black;"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
    var latitude = <?php if ($latitude) { echo $latitude; } else { echo 48.67103; } ?>;
    var longitude = <?php if ($longitude) { echo $longitude; } else { echo 6.15083; } ?>;

    var map = L.map('map').setView([latitude, longitude], 15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var marker = L.marker([latitude, longitude]).addTo(map);

    // Circulation
    var circulation = <?php echo getCirculation(); ?>;
    if (circulation && circulation.incidents) {
        circulation.incidents.forEach(function (element) {
            var marker = L.marker([element.location.polyline.split(' ')[0], element.location.polyline.split(' ')[1]]).addTo(map);
            marker.bindPopup(`<b>${element.description}</b><br>Début : ${element.starttime}<br>Fin : ${element.endtime}`);
        });
    } else {
        console.error('Erreur lors de la récupération des données de circulation.');
    }

    // Qualité de l'air
    var qualite = <?php echo getQualite(); ?>;
    if (qualite && qualite.features && qualite.features[0] && qualite.features[0].attributes) {
        document.getElementById('qualite').innerHTML = `Qualité de l'air : ${qualite.features[0].attributes.lib_qual}`;
    } else {
        console.error('Erreur lors de la récupération des données de qualité de l\'air.');
    }

    // Adresse
    var adresse = <?php echo getAdresse(); ?>;
    if (adresse && adresse.features && adresse.features[0] && adresse.features[0].geometry && adresse.features[0].geometry.coordinates) {
        var longitudeAdresse = adresse.features[0].geometry.coordinates[0];
        var latitudeAdresse = adresse.features[0].geometry.coordinates[1];

        var marker = L.circle([latitudeAdresse, longitudeAdresse], {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.5,
            radius: 250
        }).addTo(map);
    } else {
        console.error('Erreur lors de la récupération des données d\'adresse.');
    }
</script>

<div>
    <h3>API Utilisées</h3>
    <ul>
        <li><a href="https://ipapi.co/xml/">IP API</a></li>
        <li><a href="https://www.infoclimat.fr/public-api/gfs/xml?_ll=48.67103,6.15083&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2">InfoClimat API</a></li>
        <li><a href="https://carto.g-ny.org/data/cifs/cifs_waze_v2.json">Circulation API</a></li>
        <li><a href="https://services3.arcgis.com/Is0UwT37raQYl9Jj/arcgis/rest/services/ind_grandest/FeatureServer/0/query?where=lib_zone%3D%27Nancy%27&objectIds=&time=&geometry=&geometryType=esriGeometryEnvelope&inSR=&spatialRel=esriSpatialRelIntersects&resultType=none&distance=0.0&units=esriSRUnit_Meter&returnGeodetic=false&outFields=*&returnGeometry=true&featureEncoding=esriDefault&multipatchOption=xyFootprint&maxAllowableOffset=&geometryPrecision=&outSR=&datumTransformation=&applyVCSProjection=false&returnIdsOnly=false&returnUniqueIdsOnly=false&returnCountOnly=false&returnExtentOnly=false&returnQueryGeometry=false&returnDistinctValues=false&cacheHint=false&orderByFields=&groupByFieldsForStatistics=&outStatistics=&having=&resultOffset=&resultRecordCount=&returnZ=false&returnM=false&returnExceededLimitFeatures=true&quantizationParameters=&sqlFormat=none&f=pjson&token=">Qualité de l'air API</a></li>
        <li><a href="https://api-adresse.data.gouv.fr/search/?q=boulevard+charlemagne+nancy">Adresse API</a></li>
        <li><a href="https://github.com/MaximeBiechy/atmosphere">Github</a> </li>
    </ul>
</div>

</body>
</html>
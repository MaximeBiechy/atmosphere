<?php

function getAdresse() {
    $adresse = 'boulevard+charlemagne+nancy';
    $url = "https://api-adresse.data.gouv.fr/search/?q=$adresse";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    curl_setopt($ch, CURLOPT_PROXY, 'www-cache:3128');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    return curl_exec($ch);
}
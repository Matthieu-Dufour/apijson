<?php

    function appel($url){
        // $opts = array('http' => array('proxy' => 'tcp://www-cache.iutnc.univ-lorraine.fr:3128/', 'request_fulluri' => true));

        // $context = stream_context_create($opts);

        // $str = file_get_contents($url, false, $context);
        $str = file_get_contents($url);
        if(http_response_code() === 200){
            return $str;
        } else {
            echo "Errors";
            return null;
        }
    }

    $nantes = appel("https://geo.api.gouv.fr/communes?nom=Nantes&format=json&fields=centre");

    $json_nantes = json_decode($nantes);

    $lon = $json_nantes[0]->{'centre'}->{'coordinates'}[0];
    $lat = $json_nantes[0]->{'centre'}->{'coordinates'}[1];


    $trafic = appel('https://data.nantesmetropole.fr/api/records/1.0/search/?dataset=224400028_info-route-departementale&facet=nature&facet=type');

    $json_trafic = json_decode($trafic);

    $markers = [];

    for($nbInfos = 0; $nbInfos < sizeof($json_trafic->{'records'}); $nbInfos++){
        $marker_lon = $json_trafic->{'records'}[$nbInfos]->{'fields'}->{'localisation'}[0];
        $marker_lat = $json_trafic->{'records'}[$nbInfos]->{'fields'}->{'localisation'}[1];
        $marker_nom = $json_trafic->{'records'}[$nbInfos]->{'fields'}->{'nature'};
        $tableau = [$marker_lon,$marker_lat,$marker_nom];
        array_push($markers, $tableau);

    }

    $jsonmarkers = json_encode($markers);

    $mars = appel('https://api.nasa.gov/mars-photos/api/v1/rovers/curiosity/photos?sol=1000&camera=rhaz&api_key=kdlIh4yvdWnDv8ag5AYZpCrlYWU8dfU4V1fACMc0');

    $json_mars = json_decode($mars);
    
    $img_src =$json_mars->{'photos'}[sizeof($json_mars->{'photos'})-1]->{'img_src'};

$html = <<<HTML
        <!doctype html>
        <html>
        <head>
            <style>
                #map { height: 100vh; width: 100vh; }
            </style>
             <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css"
   integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA=="
   crossorigin=""/>
   <script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js"
   integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA=="
   crossorigin=""></script>
        </head>
        <body>

        <div id="map"></div>
        <img src='{$img_src}'/>

        <script type="text/javascript">

            markers = {$jsonmarkers};

            //coords nantes
            var xy = [{$lat}, {$lon}];

            // création de la map avec niveau de zoom
            var map = L.map('map').setView(xy, 8);
            
            // création du calque images
            L.tileLayer('http://korona.geog.uni-heidelberg.de/tiles/roads/x={x}&y={y}&z={z}', {
                maxZoom: 20
            }).addTo(map);

            markers.forEach(function(marker) {
            console.log(marker);
            L.marker([ marker[0] , marker[1] ]).addTo(map).bindPopup("<h3>" + marker[2] + "</h3>");
            });
            

        </script>
HTML;

    
echo $html . "</body></html>";
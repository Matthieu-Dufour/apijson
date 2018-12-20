<?php

    function appel($url){
        $opts = array('http' => array('proxy' => 'tcp://www-cache.iutnc.univ-lorraine.fr:3128/', 'request_fulluri' => true));

        $context = stream_context_create($opts);

        $str = file_get_contents($url , false, $context);
        if(http_response_code() === 200){
            return $str;
        } else {
            echo "Afficher les erreurs";
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

        // $markers .= "L.marker([" + $marker_lon + "," + $marker_lat + "]).addTo(map).bindPopup(<h3>" + $marker_nom + "</h3>);" ;
    }
    
    $markersSize = sizeof($markers);

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
        </head>
        <body>

        <div id="map"></div>


        <script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js"
   integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA=="
   crossorigin=""></script>
        <script>

            //coords nantes
            var xy = [{$lat}, {$lon}];

            // création de la map avec niveau de zoom
            var map = L.map('map').setView(xy, 6);
            
            // création du calque images
            L.tileLayer('http://korona.geog.uni-heidelberg.de/tiles/roads/x={x}&y={y}&z={z}', {
                maxZoom: 20
            }).addTo(map);

            console.log({$markersSize});
            for (var i = 0; i < {$markersSize}; i++) {
                for (var j = 0; j < 3; j++) {
                    console.log({$markers}[0][0]);
                }
            }
            
            

        </script>
HTML;

    
echo $html . "</body></html>";
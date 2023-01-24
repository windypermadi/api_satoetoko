<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://doit-sit.anteraja.id/satoe/serviceRates',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => '{
    "origin": "34.04.07",
    "destination": "34.04.07",
    "weight": 1000
}',
    CURLOPT_HTTPHEADER => array(
        'secret-access-key: SYRzSiaro+a9U0wa5munOw==',
        'access-key-id: Anteraja_x_satoe_SIT',
        'Content-Type: application/json'
    )
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

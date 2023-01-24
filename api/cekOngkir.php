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
        'Content-Type: application/json',
        'Cookie: AWSALB=QKJDW5IXgvhYjhdojtKEi8uXFaIqHije5rWaQ+gQlz34O4Ybp+8E+DhB2nBhicGRZ5m7BhCXFAYpUZvRobITwyQTBvcd/2ONt99bHyelkhlCevGDOFZl8jjFkkiZ; AWSALBCORS=QKJDW5IXgvhYjhdojtKEi8uXFaIqHije5rWaQ+gQlz34O4Ybp+8E+DhB2nBhicGRZ5m7BhCXFAYpUZvRobITwyQTBvcd/2ONt99bHyelkhlCevGDOFZl8jjFkkiZ; AWSALBTG=htYXCGidci5dV2PKpA1n8pHcz9lEbNi53HFQNpeFvfKXt5f2b2i0MS0IKNlT/cL1HTGsuW+lfgNmr3F6u8cB9T9Um43gSBOHZ+hU3DaHSu8XscYos4vykIqy9ANjvXsVoHHOkvfIK2gRSIk4lNXwaGE4xTTtYa9mZZ7bXRrVk3w8LABeamo=; AWSALBTGCORS=htYXCGidci5dV2PKpA1n8pHcz9lEbNi53HFQNpeFvfKXt5f2b2i0MS0IKNlT/cL1HTGsuW+lfgNmr3F6u8cB9T9Um43gSBOHZ+hU3DaHSu8XscYos4vykIqy9ANjvXsVoHHOkvfIK2gRSIk4lNXwaGE4xTTtYa9mZZ7bXRrVk3w8LABeamo='
    ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

<?php

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
    "weight": 2000
}',
    CURLOPT_HTTPHEADER => array(
        'secret-access-key: SYRzSiaro+a9U0wa5munOw==',
        'Content-Type: application/json',
        'access-key-id: Anteraja_x_satoe_SIT',
        'Cookie: AWSALB=HPiE2WO2LIuzoKOAS7x7Rwg1JCDCqoqjdF8OmrXd0iDg+OfgYdVZW385gMy3gTwmFVDV1XT8eVpoIqBKg3/XW7ohPlWKpZ9LhxeIsTF613PYOmnMLmEYxDeVK9g3; AWSALBCORS=HPiE2WO2LIuzoKOAS7x7Rwg1JCDCqoqjdF8OmrXd0iDg+OfgYdVZW385gMy3gTwmFVDV1XT8eVpoIqBKg3/XW7ohPlWKpZ9LhxeIsTF613PYOmnMLmEYxDeVK9g3; AWSALBTG=yBFt/n6AvZgPWV+fgSzhrCpx9dTEerR5GyxxZZyoiCsI2a3fOpUHEZKSpd4OpDPC8bpzeMKo672QqBbpy7Dzx4paiutXANieRqxKqqM8di5KtDDJBC4PWdJBFR3VNMMGlTL7f4ndGaFaTypYPGG5y5gb7Ht8w0/sspfH3aEix81G1tSGqJI=; AWSALBTGCORS=yBFt/n6AvZgPWV+fgSzhrCpx9dTEerR5GyxxZZyoiCsI2a3fOpUHEZKSpd4OpDPC8bpzeMKo672QqBbpy7Dzx4paiutXANieRqxKqqM8di5KtDDJBC4PWdJBFR3VNMMGlTL7f4ndGaFaTypYPGG5y5gb7Ht8w0/sspfH3aEix81G1tSGqJI='
    ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

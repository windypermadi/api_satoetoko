<?php
function KurangStok($sku, $cabang, $qty)
{
    //! Pengurangan stok ke pak bobby
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://103.137.254.78/test_api_satoe/apiv2_stok.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('tipe' => 'kurang', 'sku' => $sku, 'warehouse' => $cabang, 'qty' => $qty),
    ));

    $response2 = curl_exec($curl);
    curl_close($curl);

    $totalstok = json_decode($response2, true);

    if (http_response_code() == 200 && $totalstok['status'] == 200) {
        return $totalstok;
    } else {
        return false;
    }
}

function TambahStok($sku, $cabang, $qty)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://103.137.254.78/test_api_satoe/apiv2_stok.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('tipe' => 'tambah', 'sku' => $sku, 'warehouse' => $cabang, 'qty' => $qty),
    ));
    $response2 = curl_exec($curl);
    curl_close($curl);
    $totalstok = json_decode($response2, true);
    if (http_response_code() == 200 && $totalstok['status'] == 200) {
        return $totalstok;
    } else {
        return false;
    }
}


function CekStok($sku, $cabang)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://103.137.254.78/test_api_satoe/apiv2_stok.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('tipe' => 'cek', 'sku' => $sku, 'warehouse' => $cabang),
    ));

    $response1 = curl_exec($curl);
    curl_close($curl);
    $stok = json_decode($response1, true);

    if (http_response_code() == 200 && $stok['status'] == 200) {
        return $stok['pesan'][0]['stok'];
    } else {
        return 0;
    }
}

function InputTransaksi($data)
{

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://103.137.254.78/test_api_satoe/apiv2_transaksi.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array($data),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    echo $response;
}

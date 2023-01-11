<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

// $conn->query($query_alamat);
$query = $conn->query("SELECT * from metode_pembayaran WHERE status_aktif = 'Y' ORDER BY id_payment ASC");
$data = array();
foreach ($query as $key => $value) {
    $result[] = [
        'id_payment'    => $value['id_payment'],
        'icon_payment'    => $geticonpayment . $value['icon_payment'],
        'metode_pembayaran'     => $value['metode_pembayaran'],
        'nomor_payment'     => $value['nomor_payment'],
        'penerima_payment' => $value['penerima_payment'],
    ];
}
$response->data = $result;
$response->sukses(200);
die();
mysqli_close($conn);

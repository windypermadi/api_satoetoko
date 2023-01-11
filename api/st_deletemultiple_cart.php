<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$dataraw = json_decode(file_get_contents('php://input'), true);

//? LIST PRODUK
$dataproduk = $dataraw["produk"];
foreach ($dataproduk as $i => $key) {
    $cekitemdata = $conn->query("DELETE FROM user_keranjang WHERE id = '$key[id_cart]'");
}

if ($cekitemdata) {
    $response->data = null;
    $response->sukses(200);
} else {
    $response->data = null;
    $response->error(400);
}
die();
mysqli_close($conn);

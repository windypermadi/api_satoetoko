<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_login         = $_GET['id_login'];

if (isset($id_login)) {
    $data = $conn->query("SELECT count(id) as jumlah_semua FROM user_keranjang a
JOIN master_item b ON a.id_barang = b.id_master
LEFT JOIN variant c ON a.id_variant = c.id_variant
WHERE a.id_user = '$id_login' AND b.status_aktif = 'Y'")->fetch_assoc();

    $datadiskon = $conn->query("SELECT count(id) as jumlah_diskon FROM user_keranjang uk
JOIN master_item mi ON uk.id_barang = mi.id_master
LEFT JOIN variant v ON uk.id_variant = v.id_variant
WHERE uk.id_user = '$id_login'
AND (mi.diskon_rupiah != 0 OR v.diskon_rupiah_varian != 0) AND mi.status_aktif = 'Y'")->fetch_assoc();

    $data1['count_semua'] = "(" . $data['jumlah_semua'] . ")";
    $data1['count_diskon'] = "(" . $datadiskon['jumlah_diskon'] . ")";

    $response->data = $data1;
    $response->sukses(200);
} else {
    $response->data = null;
    $response->error(400);
}
die();
mysqli_close($conn);

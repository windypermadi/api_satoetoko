<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$iduser = $_GET['id_login'];

if ($iduser) {

    $data1 = $conn->query("SELECT count(id_transaksi) as jumlah FROM transaksi WHERE status_transaksi = '1' AND id_user = '$iduser'")->fetch_object();
    $data2 = $conn->query("SELECT count(id_transaksi) as jumlah FROM transaksi WHERE status_transaksi = '3' AND id_user = '$iduser'")->fetch_object();
    $data3 = $conn->query("SELECT count(id_transaksi) as jumlah FROM transaksi WHERE status_transaksi = '5' AND id_user = '$iduser'")->fetch_object();
    $data4 = $conn->query("SELECT count(id_transaksi) as jumlah FROM transaksi WHERE status_transaksi = '7' AND id_user = '$iduser'")->fetch_object();

    $data['menunggu'] = $data1->jumlah;
    $data['dikemas'] = $data2->jumlah;
    $data['dikirim'] = $data3->jumlah;
    $data['selesai'] = $data4->jumlah;

    $response->data = $data;
    $response->sukses(200);
} else {
    $response->data = NULL;
    $response->error(400);
}
mysqli_close($conn);

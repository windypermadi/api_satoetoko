<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$iduser = $_GET['iduser'];

$datalist = array();
$data = $conn->query("SELECT * FROM voucher_user a 
JOIN voucher b ON a.idvoucher = b.idvoucher
WHERE a.iduser = '$iduser' AND a.status_pakai = '0' AND tgl_mulai <= CURRENT_DATE() AND tgl_berakhir >= CURRENT_DATE();");
foreach ($data as $key => $value) {
    array_push($datalist, array(
        'iduser_voucher' => $value['iduser_voucher'],
        'kode_voucher' => $value['kode_voucher'],
        'nama_voucher' => $value['nama_voucher'],
        'deskripsi_voucher' => $value['deskripsi_voucher'],
        'nilai_voucher' => $value['nilai_voucher'],
        'minimal_transaksi' => $value['minimal_transaksi'],
        'tgl_mulai' => $value['tgl_mulai'],
        'tgl_berakhir' => $value['tgl_berakhir'],
    ));
}

if (isset($datalist[0])) {
    $response->code = 200;
    $response->message = 'result';
    $response->data = $datalist;
    $response->json();
    die();
} else {
    $response->code = 200;
    $response->message = 'Tidak ada data ditampilkan.';
    $response->data = [];
    $response->json();
    die();
}

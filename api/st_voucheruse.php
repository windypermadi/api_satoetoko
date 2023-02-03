<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$idvoucher = $_POST['idvoucher'];
$harga = $_POST['harga_produk'];
$ongkir = $_POST['harga_ongkir'];
$id_master = $_POST['id_master'];

$getmaster = $conn->query("SELECT * FROM master_item WHERE id_master = '$id_master'")->fetch_object();
$status_master_detail = $getmaster->status_master_detail;

$getvoucher = mysqli_query($conn, "SELECT * FROM voucher WHERE idvoucher = '$idvoucher'")->fetch_object();
$minimal_transaksi = $getvoucher->minimal_transaksi;

if ($harga <= $getvoucher->minimal_transaksi) {
    $response->code = 400;
    $response->message = 'Total belanja kurang dari minimal transaksi';
    $response->data = [];
    $response->json();
    die();
} else {
    if ($getvoucher->status_voucher == '1') {
        $total_potongan = $harga - ($getvoucher->nilai_voucher);
        $harga_disc = $harga - $total_potongan;
    } else if ($getvoucher->status_voucher == '2') {
        if ($ongkir != 0) {
            $total_potongan = $ongkir - $getvoucher->nilai_voucher;
            $harga_ongkir = $total_potongan;
        } else {
            $harga_ongkir = 0;
        }
    } else {
        $total_potongan = $harga - ($getvoucher->nilai_voucher);
        $harga_disc = $harga - $total_potongan;
    }
}

$data1['subtotal_produk'] = $harga;
$data1['subtotal_pengiriman'] = $ongkir;
$data1['subtotal_diskon'] = $total_potongan;
$data1['subtotal_diskon_pengiriman'] = $total_potongan;
$data1['subtotal'] = $harga;

if (isset($data1)) {
    $response->code = 200;
    $response->message = 'result';
    $response->data = $data1;
    $response->json();
    die();
} else {
    $response->code = 200;
    $response->message = 'Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini';
    $response->data = [];
    $response->json();
    die();
}
mysqli_close($conn);

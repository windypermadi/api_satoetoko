<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$idvoucher = $_POST['idvoucher'];
$harga = $_POST['harga'];
$id_master = $_POST['id_master'];

$cekminimaltransaksi = mysqli_query($conn, "SELECT * FROM voucher WHERE idvoucher = '$idvoucher'")->fetch_assoc();
$minimal_transaksi = $cekminimaltransaksi['minimal_transaksi'];

if ($harga <= $minimal_transaksi) {
    $response->code = 400;
    $response->message = 'Total belanja kurang dari minimal transaksi';
    $response->data = [];
    $response->json();
    die();
} else {
    if ($cekminimaltransaksi['status_voucher'] == '1') {
        $total_potongan = $harga - ($cekminimaltransaksi['nilai_voucher'] / 100);
        $harga_disc = $harga - $total_potongan;
    } else {
        $total_potongan = $harga - $cekminimaltransaksi['nilai_voucher'];
        $harga_disc = $total_potongan;
    }
}

$data1['harga_produk'] = (int)$harga;
$data1['diskon_rupiah'] = (int)$total_potongan;
$data1['diskon_persen'] = (int)$cekminimaltransaksi['nilai_voucher'];
$data1['voucher'] = (int)$total_potongan;
$data1['ppn_persen'] = '10%';
$data1['ppn_rupiah'] = $jumlahbayar * 0.1;
$data1['biaya_admin'] = 0;
$data1['total'] = (int)$harga_produk;

if (isset($result[0])) {
    $response->code = 200;
    $response->message = 'result';
    $response->data = $result;
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

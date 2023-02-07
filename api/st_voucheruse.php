<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_voucher_barang = $_POST['id_voucher_barang'];
$id_voucher_ongkir = $_POST['id_voucher_ongkir'];
$harga = $_POST['harga_produk'];
$ongkir = $_POST['harga_ongkir'];

//? get voucher barang
$getvoucherbarang = mysqli_query($conn, "SELECT * FROM voucher WHERE idvoucher = '$id_voucher_barang' AND status_voucher = '3'")->fetch_object();
$minimal_transaksi = $getvoucherbarang->minimal_transaksi;
//? get voucher ongkir
$getvoucherongkir = $conn->query("SELECT * FROM voucher WHERE idvoucher = '$id_voucher_ongkir' AND status_voucher = '2'")->fetch_object();

if (empty($id_voucher_barang)) {
    $total_potongan_barang = 0;
    $total_subtotal = $harga;
} else {
    if (isset($getvoucherbarang->idvoucher)) {
        if ($harga < $getvoucherbarang->minimal_transaksi) {
            $response->code = 400;
            $response->message = 'Total belanja kurang dari minimal transaksi';
            $response->data = [];
            $response->json();
            die();
        } else {
            $total_potongan_barang = (int)$getvoucherbarang->nilai_voucher;
        }
    } else {
        $total_potongan_barang = 0;
        $response->code = 400;
        $response->message = 'Voucher ini tidak sesuai.';
        $response->data = [];
        $response->json();
        die();
    }
}

if (!isset($ongkir) || $ongkir == NULL || $ongkir == '') {
    $response->code = 400;
    $response->message = 'Voucher ongkir tidak bisa digunakan';
    $response->data = [];
    $response->json();
    die();
} else {
    if (empty($id_voucher_ongkir)) {
        $total_potongan_ongkir = 0;
        $total_subtotal = $harga;
        $subtotal_ongkir = 0;
    } else {
        if (isset($getvoucherongkir->idvoucher)) {
            if ($harga < $getvoucherongkir->minimal_transaksi) {
                $response->code = 400;
                $response->message = 'Total belanja kurang dari minimal transaksi';
                $response->data = [];
                $response->json();
                die();
            } else {
                $total_potongan_ongkir = $ongkir - $getvoucherongkir->nilai_voucher;
                if ($total_potongan_ongkir <= 0) {
                    $subtotal_ongkir = (int)$ongkir;
                } else {
                    $subtotal_ongkir = (int)$getvoucherongkir->nilai_voucher;
                }
            }
        } else {
            $subtotal_ongkir = 0;
            $response->code = 400;
            $response->message = 'Voucher ini tidak sesuai.';
            $response->data = [];
            $response->json();
            die();
        }
    }
}

$data1['subtotal_produk'] = $harga;
$data1['subtotal_pengiriman'] = $ongkir;
$data1['subtotal_diskon'] = $total_potongan_barang;
$data1['subtotal_diskon_pengiriman'] = $subtotal_ongkir;
$data1['subtotal'] = ($harga + $ongkir) - ($total_potongan_barang + $subtotal_ongkir);

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

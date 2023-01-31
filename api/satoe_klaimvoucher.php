<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$iduser  = $_POST['iduser'] ?? '';
$idvoucher  = $_POST['idvoucher'] ?? '';

$cekkuota = mysqli_query($conn, "SELECT * FROM transaksi_voucher_mandiri WHERE idtransaksi_voucher_mandiri = '$idvoucher' AND qty_voucher_sisa != 0")->num_rows;
$cekvoucher = mysqli_query($conn, "SELECT * FROM voucher_user WHERE idvoucher = '$idvoucher' AND iduser = '$iduser'")->num_rows;
$getkuota = mysqli_query($conn, "SELECT qty_voucher_sisa FROM transaksi_voucher_mandiri WHERE idtransaksi_voucher_mandiri = '$idvoucher'")->fetch_assoc();
$stok = $getkuota['qty_voucher_sisa'];

if ($cekvoucher > 0) {
    $response->code = 400;
    $response->message = 'Voucher ini sudah diklaim.';
    $response->data = [];
    $response->json();
    die();
} else {
    $conn->begin_transaction();

    $query[] = $conn->query("INSERT INTO voucher_user SET iduser_voucher = UUID_SHORT(),
    iduser = '$iduser',
    idvoucher = '$idvoucher',
    tgl_klaim = CURRENT_TIME(),
    status_pakai = '0'");

    if (in_array(false, $query)) {
        $response->code = 400;
        $response->message = 'Mohon maaf kamu gagal mengeklaim voucher ini.';
        $response->data = '';
        $response->json();
        die();
    } else {
        $conn->commit();
        $response->code = 200;
        $response->message = 'Selamat kamu berhasil mengeklaim voucher ini.';
        $response->data = '';
        $response->json();
        die();
    }
}

<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_login         = $_POST['id_login'];
$id_cabang        = $_POST['id_cabang'];
$id_master        = $_POST['id_master'];
$id_variant       = $_POST['id_variant'] ?? '';
$jumlah           = $_POST['jumlah'];

if (isset($id_login) && isset($id_cabang) && isset($id_master) && isset($jumlah)) {

    $datastok = mysqli_fetch_object($conn->query("SELECT sum(jumlah) as jumlah FROM stok a JOIN cabang b ON a.id_warehouse = b.id_cabang WHERE a.id_barang = '$id_master' AND a.id_warehouse = '$id_cabang'"));

    if (empty($id_variant)) {
        //* CEK FLASHSALE
        $sekarang = "SELECT * FROM flashsale a 
        JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
        JOIN master_item c ON b.kd_barang = c.id_master
        WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND b.kd_barang = '$id_master' AND a.waktu_mulai <= NOW() AND a.waktu_selesai >= NOW()";
        $ceksekarang = $conn->query($sekarang)->num_rows;
        $getflash = $conn->query($sekarang)->fetch_object();

        $akandatang = "SELECT * FROM flashsale a 
        JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
        JOIN master_item c ON b.kd_barang = c.id_master
        WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND b.kd_barang = '$id_master' AND a.waktu_mulai >= NOW() AND a.waktu_selesai >= NOW()";
        $cekakandatang = $conn->query($akandatang)->num_rows;

        $q = "SELECT id,qty FROM user_keranjang WHERE id_user = 
                '$id_login' AND id_barang = '$id_master' AND id_gudang = '$id_cabang'";
        $cekitemdata = $conn->query($q);

        if ($cekitemdata->num_rows > 0) {
            if ($ceksekarang > 0) {
                $response->data = null;
                $response->message = 'Produk flashsale ini cuma dapat beli 1 produk';
                $response->error(400);
                die();
            } else {
                $data = $cekitemdata->fetch_object();
                $qty = $data->qty;
                $qty = $qty + $jumlah;
                $query = mysqli_query($conn, "UPDATE user_keranjang SET qty = '$qty' WHERE id = '$data->id'");
            }
        } else {
            $qty = $jumlah;
            $query = mysqli_query($conn, "INSERT INTO user_keranjang SET id = UUID(),
                        id_user='$id_login',
                        id_barang='$id_master',
                        id_variant='$id_variant',
                        id_gudang='$id_cabang',
                        qty='$qty'");
        }
    } else {
        $q = "SELECT id,qty FROM user_keranjang WHERE id_user = 
                '$id_login' AND id_barang = '$id_master' AND id_gudang = '$id_cabang' AND id_variant = '$id_variant'";
        $cekitemdata = $conn->query($q);
        // var_dump($cekitemdata->num_rows);
        // die();

        if ($cekitemdata->num_rows > 0) {
            $data = $cekitemdata->fetch_object();
            $qty = $data->qty;
            $qty = $qty + $jumlah;
            $query = mysqli_query($conn, "UPDATE user_keranjang SET qty = '$qty' WHERE id = '$data->id'");
        } else {
            $qty = $jumlah;
            $query = mysqli_query($conn, "INSERT INTO user_keranjang SET id = UUID(),
                        id_user='$id_login',
                        id_barang='$id_master',
                        id_variant='$id_variant',
                        id_gudang='$id_cabang',
                        qty='$qty'");
        }
    }

    if ($query) {
        $response->data = null;
        $response->sukses(200);
    } else {
        $response->data = null;
        $response->error(400);
    }
} else {
    $response->data = null;
    $response->error(400);
}
die();
mysqli_close($conn);

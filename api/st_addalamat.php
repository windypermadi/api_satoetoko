<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$data = [
    $id_login         = $_POST['id_login'],
    $provinsi         = $_POST['provinsi'],
    $kota         = $_POST['kota'],
    $kecamatan         = $_POST['kecamatan'],
    $kelurahan         = $_POST['kelurahan'],
    $alamat         = str_replace("\n", " ", $_POST['alamat']),
    $kodepos         = $_POST['kodepos'],
    $telepon_penerima         = $_POST['telepon_penerima'],
    $nama_penerima         = $_POST['nama_penerima'],
    $label_alamat         = $_POST['label_alamat'],
    $status_alamat_utama         = $_POST['status_alamat_utama'],
    $status_alamat_pengembalian         = $_POST['status_alamat_pengembalian']
];

foreach ($data as $v) {
    if (!empty($v)) {
        $valid = '1';
    } else {
        $valid = '0';
        break;
    }
}

if ($valid == '1') {

    if ($status_alamat_utama == 'Y') {
        $query = mysqli_query($conn, "UPDATE user_alamat SET status_alamat_utama = 'N' WHERE id_user = '$id_login'");
    }

    if ($status_alamat_pengembalian == 'Y') {
        $query = mysqli_query($conn, "UPDATE user_alamat SET status_alamat_pengembalian = 'N' WHERE id_user = '$id_login'");
    }

    $query2 = mysqli_query($conn, "INSERT INTO user_alamat SET id = UUID_SHORT(),
                        id_user='$id_login',
                        provinsi='$provinsi',
                        kota='$kota',
                        kecamatan='$kecamatan',
                        kelurahan='$kelurahan',
                        alamat='$alamat',
                        kodepos='$kodepos',
                        telepon_penerima='$telepon_penerima',
                        nama_penerima='$nama_penerima',
                        label_alamat='$label_alamat',
                        status_alamat_utama='$status_alamat_utama',
                        status_alamat_pengembalian='$status_alamat_pengembalian'");

    if ($query2) {
        $response->data = null;
        $response->sukses(200);
    } else {
        $response->data = null;
        $response->error(400);
    }
} else {
    $response->data = null;
    $response->error(404);
}
die();
mysqli_close($conn);

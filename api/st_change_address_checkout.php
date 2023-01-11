<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id             = $_POST['id'];

if (isset($id)) {
    $data_alamat = mysqli_query($conn, "SELECT * FROM user_alamat WHERE id = '$id'")->fetch_object();
    $gabung_alamat = $data_alamat->nama_penerima . " | " . $data_alamat->telepon_penerima . " " . $data_alamat->alamat
        . "," . $data_alamat->kelurahan . "," . $data_alamat->kecamatan . "," . $data_alamat->kota . "," . $data_alamat->provinsi . "," . $data_alamat->kodepos;

    $data1['id'] = $data_alamat->id;
    $data1['alamat'] = $gabung_alamat;

    $response->data = $data1;
    $response->sukses(200);
} else {
    $response->data = null;
    $response->error(400);
}

mysqli_close($conn);

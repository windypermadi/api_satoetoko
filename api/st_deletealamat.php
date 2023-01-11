<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id = $_POST['id'];

if ($id) {
    $cekid = $conn->query("SELECT * FROM user_alamat WHERE id = '$id'")->num_rows;
    if ($cekid > 0) {
        $query = $conn->query("DELETE FROM `user_alamat` WHERE id = '$id'");
        $response->data = NULL;
        $status = $query ? $response->sukses(200) : $response->error(400);
    } else {
        $response->data = "kode alamat tidak ditemukan.";
        $response->error(400);
    }
} else {
    $response->data = NULL;
    $response->error(400);
}
die();
mysqli_close($conn);

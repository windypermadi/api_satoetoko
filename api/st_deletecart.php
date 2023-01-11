<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_cart         = $_POST['id_cart'];

if (isset($id_cart)) {
    $cekitemdata = $conn->query("DELETE FROM user_keranjang WHERE id = '$id_cart'");
    if ($cekitemdata) {
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

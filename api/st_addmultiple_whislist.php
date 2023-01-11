<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$dataraw = json_decode(file_get_contents('php://input'), true);

//? LIST PRODUK
$dataproduk = $dataraw["produk"];
foreach ($dataproduk as $i => $key) {
    $getproduk[] = $conn->query("SELECT * FROM user_keranjang WHERE id = '$key[id_cart]'")->fetch_object();
}
foreach ($getproduk as $key => $value) {
    if ($value->id_variant != null) {
        $query = mysqli_query($conn, "INSERT INTO whislist_product SET id_whislist = UUID_SHORT(),
                        id_master='$value->id_barang',
                        id_variant='$value->id_variant',
                        id_login='$value->id_user',
                        create_at=NOW()");
    } else {
        $query = mysqli_query($conn, "INSERT INTO whislist_product SET id_whislist = UUID_SHORT(),
                        id_master='$value->id_barang',
                        id_login='$value->id_user',
                        create_at=NOW()");
    }
}
if ($query) {
    $response->data = null;
    $response->sukses(200);
} else {
    $response->data = null;
    $response->error(400);
}
die();
mysqli_close($conn);

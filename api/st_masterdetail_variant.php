<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_master = $_POST['id_master'];
$id_cabang = $_POST['id_cabang'];

if (isset($id_master) && isset($id_cabang)) {
    $datamaster = "SELECT * FROM master_item WHERE id_master = 
                '$id_master'";
    $cekitemdata = $conn->query($datamaster);
    $data = $cekitemdata->fetch_object();

    $variant = $conn->query("SELECT * FROM variant a JOIN stok b ON a.id_variant = b.id_varian WHERE a.id_master = '$id_master' AND b.id_warehouse = '$id_cabang' AND a.status_acc_var = '2' AND a.status_aktif_var = 'Y' AND a.status_hapus_var = 'N'");
    foreach ($variant as $key => $value) {
        $variants[] = [
            'id_variant' => $value['id_variant'],
            'keterangan_varian' => $value['keterangan_varian'],
            'harga_varian' => $value['harga_varian'],
            'diskon_rupiah_varian' => $value['diskon_rupiah_varian'],
            'diskon_persen_varian' => $value['diskon_persen_varian'],
            'image_varian' => $data->status_master_detail == '2' ? $getimagebukufisik . $value['image_varian'] : $getimagefisik . $value['image_varian'],
            'stok' => $value['jumlah'],
        ];
    }

    if ($variants) {
        $response->data = $variants;
        $response->sukses(200);
    } else {
        $response->data = [];
        $response->sukses(200);
    }
} else {
    $response->data = null;
    $response->error(400);
}
mysqli_close($conn);

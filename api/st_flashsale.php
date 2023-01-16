<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$limit = $_GET['limit'];
$offset = $_GET['offset'];

$data = $conn->query("SELECT * FROM flashsale a 
JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
JOIN master_item c ON b.kd_barang = c.id_master
WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N'");
foreach ($data as $key => $value) {

    //! untuk varian harga diskon atau enggak
    if ($value['diskon'] != 0) {
        $status_diskon = 'Y';
        $harga_produk = rupiah($value['harga_master']);
        $harga_tampil = rupiah($value['harga_master'] - $value['diskon']);

        // $status_varian_diskon = 'UPTO'; 
        // $varian = $conn->query("SELECT *, (harga_varian-diskon_rupiah_varian) as harga_varian_final FROM variant WHERE id_master = '$value[id_master]' ORDER BY harga_varian_final ASC")->fetch_all(MYSQLI_ASSOC);
        // // foreach ($varian as $key => $value) {
        // // }
        // $min_normal = $varian[0]['harga_varian'];
        // $max_normal = $varian[count($varian) - 1]['harga_varian'];

        // $min = $varian[0]['harga_varian_final'];
        // $max = $varian[count($varian) - 1]['harga_varian_final'];

        // $jumlah_diskon = $varian[count($varian) - 1]['diskon_persen_varian'];

        // //! varian ada diskon
        // if ($varian[0]['diskon_rupiah_varian'] != 0) {
        //     $status_diskon = 'Y';
        //     (float)$harga_disc = $varian->harga_varian - $varian->diskon_rupiah_varian;
        // } else {
        //     $status_diskon = 'N';
        //     (float)$harga_disc = $varian->diskon_rupiah_varian;
        // }

        // $harga_produk = rupiah($min_normal) . " - " . rupiah($max_normal);
        // $harga_tampil = rupiah($min) . " - " . rupiah($max);
    } else {

        $status_diskon = 'N';
        $harga_produk = rupiah($value['harga_master']);
        $harga_tampil = rupiah($value['harga_master']);
        
        // $jumlah_diskon = $value['diskon_persen'];
        // $status_varian_diskon = 'OFF';
        // if ($value['diskon_persen'] != 0) {
        //     $status_diskon = 'Y';
        //     (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
        // } else {
        //     $status_diskon = 'N';
        //     (float)$harga_disc = $value['harga_master'];
        // }

        // $harga_produk = rupiah($value['harga_master']);
        // $harga_tampil = rupiah($harga_disc);
    }

    $status_jenis_harga = '1';

    if ($value['status_master_detail'] == '2') {
        $imagegambar = $getimagebukufisik . $value['image_master'];
    } else {
        $imagegambar = $getimagefisik . $value['image_master'];
    }

    $data_produk[] = [
    'id_master' => $value['id_master'],
    'image_master' => $value['image_master'],
    'judul_master' => $value['judul_master'],
    'harga_produk' => $harga_produk,
    'harga_tampil;' => $harga_tampil,
    'status_diskon' => $status_diskon,
    'diskon' => $value['diskon'],
    'stok_total' => $value['stok_flashdisk'],
    'sisa_stok' => $value['stok_terjual_flashdisk']
];

    $result2[] = [
        'waktu_mulai' => $value['waktu_mulai'],
        'waktu_selesai' => $value['waktu_selesai'],
        'status_tampil' => 'Y',
        'data_produk' => $data_produk
    ];
}

if (isset($result2[0])) {
    $response->data = $result2;
    $response->sukses(200);
} else {
    $response->data = [];
    $response->sukses(200);
}
die;
mysqli_close($conn);

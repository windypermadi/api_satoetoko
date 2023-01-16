<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$limit = $_GET['limit'];
$offset = $_GET['offset'];

$result2 = array();
$data = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
    a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah , a.id_sub_kategori, c.nama_kategori, a.status_master_detail, a.status_varian
    FROM master_item a JOIN stok b ON a.id_master = b.id_barang
    JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub
    LEFT JOIN master_buku_detail d ON a.id_master = d.id_master
	LEFT JOIN master_fisik_detail e ON a.id_master = e.id_master
    WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' AND (d.id_master IS NOT NULL OR e.id_master IS NOT NULL) GROUP BY a.id_master ORDER BY a.tanggal_approve DESC LIMIT $offset, $limit");
foreach ($data as $key => $value) {

    //! untuk varian harga diskon atau enggak
    if ($value['status_varian'] == 'Y') {
        $status_varian_diskon = 'UPTO'; 
        $varian = $conn->query("SELECT *, (harga_varian-diskon_rupiah_varian) as harga_varian_final FROM variant WHERE id_master = '$value[id_master]' ORDER BY harga_varian_final ASC")->fetch_all(MYSQLI_ASSOC);
        // foreach ($varian as $key => $value) {
        // }
        $min_normal = $varian[0]['harga_varian'];
        $max_normal = $varian[count($varian) - 1]['harga_varian'];

        $min = $varian[0]['harga_varian_final'];
        $max = $varian[count($varian) - 1]['harga_varian_final'];

        $jumlah_diskon = $varian[count($varian) - 1]['diskon_persen_varian'];

        //! varian ada diskon
        if ($varian[0]['diskon_rupiah_varian'] != 0) {
            $status_diskon = 'Y';
            (float)$harga_disc = $varian->harga_varian - $varian->diskon_rupiah_varian;
        } else {
            $status_diskon = 'N';
            (float)$harga_disc = $varian->diskon_rupiah_varian;
        }

        $harga_produk = rupiah($min_normal) . " - " . rupiah($max_normal);
        $harga_tampil = rupiah($min) . " - " . rupiah($max);
    } else {
        $jumlah_diskon = $value['diskon_persen'];
        $status_varian_diskon = 'OFF';
        if ($value['diskon_persen'] != 0) {
            $status_diskon = 'Y';
            (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
        } else {
            $status_diskon = 'N';
            (float)$harga_disc = $value['harga_master'];
        }

        $harga_produk = rupiah($value['harga_master']);
        $harga_tampil = rupiah($harga_disc);
    }

    $status_jenis_harga = '1';

    if ($value['status_master_detail'] == '2') {
        $imagegambar = $getimagebukufisik . $value['image_master'];
    } else {
        $imagegambar = $getimagefisik . $value['image_master'];
    }

    $result2[] = [
        'waktu_mulai' => $value['id_master'],
        'waktu_selesai' => $value['judul_master'],
        'status_tampil' => $imagegambar,
        'data_produk' => $data_produk
    ];
}

$data_produk[] = [
    'id_master' => '',
    'image_master' => '',
    'judul_master' => '',
    'harga_produk' => '',
    'harga_tampi;' => '',
    'status_diskon' => '',
    'diskon' => '',
    'stok_total' => '',
    'sisa_stok' => ''
];

if (isset($result2[0])) {
    $response->data = $result2;
    $response->sukses(200);
} else {
    $response->data = [];
    $response->sukses(200);
}
die;
mysqli_close($conn);

<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$penerbit = $_GET['penerbit'] ?? '';

$q = $_GET['q'] ?? '';
$search = !empty($q) ? " AND med.penerbit LIKE '%$q%'" : "";
$limit = $_GET['limit'] ?? '';
$offset = $_GET['offset'] ?? '';

$query = "SELECT * FROM master_item mi JOIN master_ebook_detail med ON mi.id_master = med.id_master
WHERE mi.status_approve = '2' $search";

if (!empty($penerbit)) {
    $querys = $query . " AND med.penerbit LIKE '%$penerbit%' LIMIT $offset, $limit";
    $query = mysqli_query($conn, $querys);
    foreach ($query as $key => $value) {

        if ($value['status_ebook'] == '1') {
            //beli
            $status_ebook = '1';
        } else if ($value['status_ebook'] == '2') {
            //sewa
            $status_ebook = '2';
        } else if ($value['status_ebook'] == '3') {
            //beli dan sewa
            $status_ebook = '3';
        } else {
            $status_ebook = '1';
        }

        if ($value['diskon_persen'] != 0) {
            (float)$harga_potongan = (float)$value['harga_master'] * ((float)$value['diskon_persen'] / 100);
            (float)$harga_disc = $value['harga_master'] - $harga_potongan;
            $jumlah_diskon = $value['diskon_persen'];
        } else if ($value['diskon_rupiah'] != 0) {
            (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
            $jumlah_diskon = $value['diskon_rupiah'];
        } else {
            $harga_potongan = 0;
            $jumlah_diskon = "0";
            $harga_disc = (int)$value['harga_master'];
        }

        if ($value['diskon_sewa_persen'] != 0) {
            (float)$harga_potongan_sewa = (float)$value['harga_sewa'] * ((float)$value['diskon_sewa_persen'] / 100);
            (float)$harga_disc_sewa = $value['harga_sewa'] - $harga_potongan;
            $jumlah_diskon_sewa = $value['diskon_sewa_persen'];
        } else if ($value['diskon_sewa_rupiah'] != 0) {
            (float)$harga_disc_sewa = $value['harga_sewa'] - $value['diskon_sewa_rupiah'];
            $jumlah_diskon_sewa = $value['diskon_sewa_rupiah'];
        } else {
            $harga_potongan_sewa = 0;
            $jumlah_diskon_sewa = "0";
            $harga_disc_sewa = (int)$value['harga_sewa'];
        }

        if ($value['status_ebook'] == '1') {
            $harga_tampil = "Rp" . number_format($value['harga_master'], 0, ',', '.');
        } else {
            $harga_tampil = "Rp" . number_format($value['harga_sewa'], 0, ',', '.') . "-" . "Rp" . number_format($value['harga_master'], 0, ',', '.');
        }

        $status_diskon = $jumlah_diskon != $value['diskon_sewa_persen'] ? "UP TO" : "OFF";

        $result[] = [
            'id_master' => $value['id_master'],
            'judul_master' => $value['judul_master'],
            'image_master' => $urlimg . $value['image_master'],
            'status_ebook' => $value['status_ebook'],
            'rating_ebook' => 0,
            'nama_kategori' => $value['nama_kategori'],
            'sinopsis' => $value['sinopsis'],
            'penerbit' => $value['penerbit'],
            'lama_sewa' => $value['lama_sewa'],
            'harga_beli' => (int)$value['harga_master'],
            'diskon_beli' => (int)$jumlah_diskon,
            'diskon_beli_status' => $status_diskon,
            'harga_diskon_beli' => (int)$harga_disc,
            'harga_sewa' => (int)$value['harga_sewa'],
            'diskon_sewa' => (int)$value['diskon_sewa_rupiah'] != 0 ? (int)$value['diskon_sewa_persen'] : 0,
            'harga_diskon_sewa'    => (int)$value['diskon_sewa_rupiah'] != 0 ? $value['harga_sewa'] - $value['diskon_sewa_rupiah'] : (int)$value['harga_sewa'],
            'harga_tampil' => $harga_tampil,
        ];
    }
} else {
    $query = mysqli_query($conn, $query . " GROUP BY med.penerbit");
    foreach ($query as $key => $value) {
        $result[] = [
            'penerbit'    => $value['penerbit'],
        ];
    }
}

if ($result) {
    $response->data = $result;
    $response->sukses(200);
} else {
    $response->data = [];
    $response->sukses(200);
}
die();
mysqli_close($conn);

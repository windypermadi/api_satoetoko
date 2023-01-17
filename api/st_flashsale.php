<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$tag = $_GET['tag'];

switch ($tag) {
    case 'home':
        $data = $conn->query("SELECT * FROM flashsale WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND waktu_selesai > NOW() LIMIT 1");
        foreach ($data as $key => $value) {

            //! untuk varian harga diskon atau enggak
            if ($value['diskon'] != 0) {
                $status_diskon = 'Y';
                $harga_produk = rupiah($value['harga_master']);
                $harga_tampil = rupiah($value['harga_master'] - $value['diskon']);
            } else {

                $status_diskon = 'N';
                $harga_produk = rupiah($value['harga_master']);
                $harga_tampil = rupiah($value['harga_master']);
            }

            $status_jenis_harga = '1';

            if ($value['status_master_detail'] == '2') {
                $imagegambar = $getimagebukufisik . $value['image_master'];
            } else {
                $imagegambar = $getimagefisik . $value['image_master'];
            }

            $dataproduct = $conn->query("SELECT * FROM flashsale a 
    JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
    JOIN master_item c ON b.kd_barang = c.id_master
    WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' LIMIT 5");
            foreach ($dataproduct as $key => $key2) {
                $data_produk[] = [
                    'id_master' => $key2['id_master'],
                    'image_master' => $key2['image_master'],
                    'judul_master' => $key2['judul_master'],
                    'harga_produk' => $harga_produk,
                    'harga_tampil;' => $harga_tampil,
                    'status_diskon' => $status_diskon,
                    'diskon' => $key2['diskon'],
                    'stok_total' => $key2['stok_flashdisk'],
                    'sisa_stok' => $key2['stok_terjual_flashdisk']
                ];
            }

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
        break;
    case 'semua':
        $data = $conn->query("SELECT * FROM flashsale WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND waktu_selesai > NOW() ");
        foreach ($data as $key => $value) {

            //! untuk varian harga diskon atau enggak
            if ($value['diskon'] != 0) {
                $status_diskon = 'Y';
                $harga_produk = rupiah($value['harga_master']);
                $harga_tampil = rupiah($value['harga_master'] - $value['diskon']);
            } else {

                $status_diskon = 'N';
                $harga_produk = rupiah($value['harga_master']);
                $harga_tampil = rupiah($value['harga_master']);
            }

            $status_jenis_harga = '1';

            if ($value['status_master_detail'] == '2') {
                $imagegambar = $getimagebukufisik . $value['image_master'];
            } else {
                $imagegambar = $getimagefisik . $value['image_master'];
            }

            $dataproduct = $conn->query("SELECT * FROM flashsale a 
            JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
            JOIN master_item c ON b.kd_barang = c.id_master
            WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N'");
            foreach ($dataproduct as $key => $key2) {
                $data_produk[] = [
                    'id_master' => $key2['id_master'],
                    'image_master' => $key2['image_master'],
                    'judul_master' => $key2['judul_master'],
                    'harga_produk' => $harga_produk,
                    'harga_tampil;' => $harga_tampil,
                    'status_diskon' => $status_diskon,
                    'diskon' => $key2['diskon'],
                    'stok_total' => $key2['stok_flashdisk'],
                    'sisa_stok' => $key2['stok_terjual_flashdisk']
                ];
            }

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
        break;
}

die;
mysqli_close($conn);

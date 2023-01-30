<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_kategori = $_GET['id_kategori'] ?? '';

if (empty($id_kategori)) {
    $q = $_GET['q'] ?? '';
    if (empty($q)) {
        $query = mysqli_query($conn, "SELECT 
            c.id_kategori, 
            c.kode_kategori, 
            c.nama_kategori 
            FROM 
            kategori_sub a 
            JOIN master_item b ON a.id_sub = b.id_sub_kategori 
            JOIN kategori c ON a.parent_kategori = c.id_kategori 
            WHERE 
            c.jenis_kategori = '0' 
            AND c.status_tampil = 'Y' 
            AND c.status_hapus = 'N' 
            GROUP BY 
            c.id_kategori 
            ORDER BY 
            c.nama_kategori ASC;
            ");
    } else {
        $query = mysqli_query($conn, "SELECT 
            c.id_kategori, 
            c.kode_kategori, 
            c.nama_kategori 
            FROM 
            kategori_sub a 
            JOIN master_item b ON a.id_sub = b.id_sub_kategori 
            JOIN kategori c ON a.parent_kategori = c.id_kategori 
            WHERE 
            c.jenis_kategori = '0' 
            AND c.status_tampil = 'Y' 
            AND c.status_hapus = 'N'
            AND c.nama_kategori LIKE '%$q%'
            GROUP BY 
            c.id_kategori 
            ORDER BY 
            c.nama_kategori ASC;
            ");
    }
    foreach ($query as $key => $value) {
        $result[] = [
            'id_kategori'    => $value['id_kategori'],
            'kode_kategori'    => $value['kode_kategori'],
            'nama_kategori'     => $value['nama_kategori'],
            'icon_apps'     => $value['icon_apps'],
        ];
    }
} else {
    $idsub = $_GET['id_sub'] ?? '';
    if (!empty($idsub)) {
        $query = mysqli_query($conn, "SELECT * FROM master_item a 
            JOIN stok b ON a.id_master = b.id_barang 
            WHERE a.id_sub_kategori LIKE '$idsub';");
        foreach ($query as $key => $value) {
            //! untuk varian harga diskon atau enggak
            $varian_harga = 'N';
            switch ($varian_harga) {
                case 'N':
                    if ($value['diskon_persen'] != 0) {
                        $status_diskon = 'Y';
                        (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                    } else {
                        $status_diskon = 'N';
                        (float)$harga_disc = $value['harga_master'];
                    }

                    $harga_produk = rupiah($value['harga_master']);
                    $harga_tampil = rupiah($harga_disc);
                    break;
                default:
                    if ($value['diskon_persen'] != 0) {
                        $status_diskon = 'Y';
                        (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                    } else {
                        $status_diskon = 'N';
                        (float)$harga_disc = $value['harga_master'];
                    }

                    $harga_produk = "Rp" . number_format($value['harga_master'], 0, ',', '.') . " - " . "Rp" . number_format($value['harga_master'], 0, ',', '.');
                    $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.') . " - " . "Rp" . number_format($harga_disc, 0, ',', '.');
                    break;
            }

            $varian_diskon = 'N';
            if ($varian_diskon == 'N') {
                $status_varian_diskon = 'OFF';
            } else {
                $status_varian_diskon = 'UPTO';
            }

            $status_jenis_harga = '1';

            $result[] = [
                'id_master' => $value['id_master'],
                'judul_master' => $value['judul_master'],
                'image_master' => $getimagefisik . $value['image_master'],
                'harga_produk' => $harga_produk,
                'harga_tampil' => $harga_tampil,
                'status_diskon' => $status_diskon,
                'status_varian_diskon' => $status_varian_diskon,
                'status_jenis_harga' => $status_jenis_harga,
                'status_stok' => $value['jumlah'] > 0 ? 'Y' : 'N',
                'diskon' => $value['diskon_persen'] . "%",
                'total_dibeli' => $value['total_dibeli'] . " terjual",
                'rating_item' => 0,
            ];
        }
    } else {
        $query = mysqli_query($conn, "SELECT * FROM master_item a 
            JOIN stok b ON a.id_master = b.id_barang 
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub 
            JOIN kategori d ON c.parent_kategori = d.id_kategori 
            WHERE d.id_kategori LIKE '$id_kategori';");
        foreach ($query as $key => $value) {
            //! untuk varian harga diskon atau enggak
            $varian_harga = 'N';
            switch ($varian_harga) {
                case 'N':
                    if ($value['diskon_persen'] != 0) {
                        $status_diskon = 'Y';
                        (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                    } else {
                        $status_diskon = 'N';
                        (float)$harga_disc = $value['harga_master'];
                    }

                    $harga_produk = rupiah($value['harga_master']);
                    $harga_tampil = rupiah($harga_disc);
                    break;
                default:
                    if ($value['diskon_persen'] != 0) {
                        $status_diskon = 'Y';
                        (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                    } else {
                        $status_diskon = 'N';
                        (float)$harga_disc = $value['harga_master'];
                    }

                    $harga_produk = "Rp" . number_format($value['harga_master'], 0, ',', '.') . " - " . "Rp" . number_format($value['harga_master'], 0, ',', '.');
                    $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.') . " - " . "Rp" . number_format($harga_disc, 0, ',', '.');
                    break;
            }

            $varian_diskon = 'N';
            if ($varian_diskon == 'N') {
                $status_varian_diskon = 'OFF';
            } else {
                $status_varian_diskon = 'UPTO';
            }

            $status_jenis_harga = '1';

            $result[] = [
                'id_master' => $value['id_master'],
                'judul_master' => $value['judul_master'],
                'image_master' => $getimagefisik . $value['image_master'],
                'harga_produk' => $harga_produk,
                'harga_tampil' => $harga_tampil,
                'status_diskon' => $status_diskon,
                'status_varian_diskon' => $status_varian_diskon,
                'status_jenis_harga' => $status_jenis_harga,
                'status_stok' => $value['jumlah'] > 0 ? 'Y' : 'N',
                'diskon' => $value['diskon_persen'] . "%",
                'total_dibeli' => (int)$value['total_dibeli'],
                'rating_item' => 0,
            ];
        }
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

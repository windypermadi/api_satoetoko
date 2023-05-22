<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$penerbit = $_GET['penerbit'] ?? '';

$q = $_GET['q'] ?? '';
$search = !empty($q) ? " AND med.penerbit LIKE '%$q%'" : "";
$limit = $_GET['limit'] ?? '';
$offset = $_GET['offset'] ?? '';

$query = "SELECT * FROM master_item mi JOIN master_ebook_detail med ON mi.id_master = med.id_master WHERE mi.status_approve = '2' $search";

if (!empty($penerbit)) {
    $querys = $query . " AND med.penerbit = '$penerbit' LIMIT $offset, $limit";
    $query = mysqli_query($conn, $querys);
    foreach ($query as $key => $value) {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://103.137.254.78/test_api_satoe/apiv2_stok.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('tipe' => 'cek', 'sku' => $value['sku_induk'], 'warehouse' => '01'),
        ));

        $response1 = curl_exec($curl);
        curl_close($curl);
        $datastokserver = json_decode($response1, true);

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

        if ($value['status_master_detail'] == '2') {
            if (substr($value['image_master'], 0, 4) == 'http') {
                $imagegambar = $value['image_master'];
            } else {
                $imagegambar = $getimagebukufisik . $value['image_master'];
            }
        } else {
            if (substr($value['image_master'], 0, 4) == 'http') {
                $imagegambar = $value['image_master'];
            } else {
                $imagegambar = $getimagefisik . $value['image_master'];
            }
        }

        $result[] = [
            'id_master' => $value['id_master'],
            'judul_master' => $value['judul_master'],
            'image_master' => $imagegambar,
            'harga_produk' => $harga_produk,
            'harga_tampil' => $harga_tampil,
            'status_diskon' => $status_diskon,
            'status_varian_diskon' => $status_varian_diskon,
            'status_jenis_harga' => $status_jenis_harga,
            // 'status_stok' => $datastokserver['pesan'][0]['stok'] > 0 ? 'Y' : 'N',
            'diskon' => $value['diskon_persen'] . "%",
            'total_dibeli' => (int)$value['total_dibeli'],
            'rating_item' => 0,
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

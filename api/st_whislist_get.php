<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$tag = $_GET['tag'];

switch ($tag) {
    case "list":
        $id_user = $_GET['id_user'];
        $limit = $_GET['limit'];
        $offset = $_GET['offset'];

        $q = $_GET['q'] ?? '';

        if (!empty($q)) {
            $search = " AND a.judul_master LIKE '%$q%'";
        } else {
            $search = "";
        }

        $query = "SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
        a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah , a.id_sub_kategori, c.nama_kategori, a.status_master_detail, a.status_varian, a.slug_judul_master
        FROM master_item a JOIN stok b ON a.id_master = b.id_barang
        JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub
        LEFT JOIN master_buku_detail d ON a.id_master = d.id_master
        LEFT JOIN master_fisik_detail e ON a.id_master = e.id_master
        JOIN whislist_product f ON a.id_master = f.id_master
        WHERE f.id_login = '$id_user' AND a.status_aktif = 'Y' $search AND a.status_approve = '2' AND a.status_hapus = 'N' AND (d.id_master IS NOT NULL OR e.id_master IS NOT NULL) GROUP BY a.id_master ORDER BY a.tanggal_approve DESC LIMIT $offset, $limit";
        $data = $conn->query($query);

        foreach ($data as $key => $value) {
            //! untuk varian harga diskon atau enggak
            switch ($value['status_varian']) {
                case 'Y':
                    $status_varian_diskon = 'UPTO';
                    $varian = $conn->query("SELECT *, (harga_varian-diskon_rupiah_varian) as harga_varian_final FROM variant WHERE id_master = '$value[id_master]' ORDER BY harga_varian_final ASC")->fetch_all(MYSQLI_ASSOC);

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

                    $status_jenis_harga = '2';
                    break;

                default:

                    //? cek apakah barang ini masuk flashsale atau tidak
                    $dataproduct = $conn->query("SELECT *, (stok_flashdisk-stok_terjual_flashdisk) as sisa_stok FROM flashsale a 
                    JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
                    JOIN master_item c ON b.kd_barang = c.id_master
                    WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND a.waktu_mulai <= NOW() AND a.waktu_selesai >= NOW() AND b.kd_barang = '$value[id_master]'")->fetch_object();

                    $jumlah_diskon = $value['diskon_persen'];
                    $status_varian_diskon = 'OFF';
                    if ($value['diskon_persen'] != 0) {
                        $status_diskon = 'Y';
                        (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                    } else {
                        $status_diskon = 'N';
                        (float)$harga_disc = $value['harga_master'];
                    }

                    if (isset($dataproduct->id_flashsale)) {
                        (float)$harga_disc = $dataproduct->harga_master - ($dataproduct->harga_master * ($dataproduct->diskon / 100));
                        $harga_produk = rupiah($value['harga_master']);
                        $harga_tampil = rupiah($harga_disc);
                    } else {
                        $harga_produk = rupiah($value['harga_master']);
                        $harga_tampil = rupiah($harga_disc);
                    }

                    $status_jenis_harga = '1';
                    break;
            }

            if ($value['status_master_detail'] == '2') {
                $imagegambar = $getimagebukufisik . $value['image_master'];
            } else {
                $imagegambar = $getimagefisik . $value['image_master'];
            }

            $result2[] = [
                'id_master' => $value['id_master'],
                'judul_master' => $value['judul_master'],
                'image_master' => $imagegambar,
                'harga_produk' => $harga_produk,
                'harga_tampil' => $harga_tampil,
                'slug_judul_master' => $value['slug_judul_master'],
                'status_diskon' => $status_diskon,
                'status_varian_diskon' => $status_varian_diskon,
                'status_jenis_harga' => $status_jenis_harga,
                'status_stok' => $value['jumlah'] > 0 ? 'Y' : 'N',
                'diskon' => $jumlah_diskon . "%",
                'total_dibeli' => (int)$value['total_dibeli'],
                'rating_item' => 0
            ];
        }

        if (COUNT($result2) > 0) {
            $response->data = $result2;
            $response->sukses(200);
        } else {
            $response->data = [];
            $response->sukses(200);
        }
        die();
        break;
    case "detail":
        $id_user = $_GET['id_user'];
        $id_master = $_GET['id_master'];

        $query = $conn->query("SELECT * FROM whislist_product WHERE id_login = '$id_user' AND id_master = '$id_master'")->num_rows;

        if ($query > 0) {
            $response->data = "1";
            $response->sukses(200);
        } else {
            $response->data = "0";
            $response->sukses(200);
        }
        break;
}

mysqli_close($conn);

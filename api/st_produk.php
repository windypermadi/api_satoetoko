<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$limit = $_GET['limit'];
$offset = $_GET['offset'];
$tag = $_GET['tag'];

switch ($tag) {
    case 'terbaru':
        $q = $_GET['q'] ?? '';
        $result = array();
        if (empty($q)) {
            $data = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
            a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah, a.id_sub_kategori, c.nama_kategori
            FROM master_item a JOIN stok b ON a.id_master = b.id_barang
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' GROUP BY a.id_master ORDER BY a.tanggal_approve DESC LIMIT $offset, $limit");
        } else {
            $data = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
            a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah, a.id_sub_kategori, c.nama_kategori
            FROM master_item a JOIN stok b ON a.id_master = b.id_barang
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' AND a.judul_master LIKE '%$q%' GROUP BY a.id_master ORDER BY a.tanggal_approve DESC LIMIT $offset, $limit");
        }
        foreach ($data as $key => $value) {

            $datamaster = "SELECT * FROM master_item WHERE id_master = 
                '$value[id_master]'";
            $cekitemdata = $conn->query($datamaster);
            $data = $cekitemdata->fetch_object();

            //! untuk varian harga diskon atau enggak
            $varian_harga = 'N';
            if ($varian_harga == 'N') {

                if ($value['diskon_persen'] != 0) {
                    $status_diskon = 'Y';
                    (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                } else {
                    $status_diskon = 'N';
                    (float)$harga_disc = $value['harga_master'];
                }

                $harga_produk = "Rp" . number_format($value['harga_master'], 0, ',', '.');
                $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.');
            } else {

                if ($value['diskon_persen'] != 0) {
                    $status_diskon = 'Y';
                    (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                } else {
                    $status_diskon = 'N';
                    (float)$harga_disc = $value['harga_master'];
                }

                $harga_produk = "Rp" . number_format($value['harga_master'], 0, ',', '.') . " - " . "Rp" . number_format($value['harga_master'], 0, ',', '.');
                $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.') . " - " . "Rp" . number_format($harga_disc, 0, ',', '.');
            }

            $varian_diskon = 'N';
            if ($varian_diskon == 'N') {
                $status_varian_diskon = 'OFF';
            } else {
                $status_varian_diskon = 'UPTO';
            }

            $status_jenis_harga = '1';

            array_push($result, array(
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
            ));
        }
        break;
    case 'terlaris':
        $q = $_GET['q'] ?? '';
        $result = array();
        if (empty($q)) {
            $data = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
            a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah,a.id_sub_kategori, c.nama_kategori
            FROM master_item a JOIN stok b ON a.id_master = b.id_barang
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' GROUP BY a.id_master ORDER BY a.total_dibeli DESC LIMIT $offset, $limit");
        } else {
            $data = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
            a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah,a.id_sub_kategori, c.nama_kategori
            FROM master_item a JOIN stok b ON a.id_master = b.id_barang
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' AND a.judul_master LIKE '%$q%' GROUP BY a.id_master ORDER BY a.total_dibeli DESC LIMIT $offset, $limit");
        }
        foreach ($data as $key => $value) {

            //! untuk varian harga diskon atau enggak
            $varian_harga = 'N';
            if ($varian_harga == 'N') {

                if ($value['diskon_persen'] != 0) {
                    $status_diskon = 'Y';
                    (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                } else {
                    $status_diskon = 'N';
                    (float)$harga_disc = $value['harga_master'];
                }

                $harga_produk = "Rp" . number_format($value['harga_master'], 0, ',', '.');
                $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.');
            } else {

                if ($value['diskon_persen'] != 0) {
                    $status_diskon = 'Y';
                    (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                } else {
                    $status_diskon = 'N';
                    (float)$harga_disc = $value['harga_master'];
                }

                $harga_produk = "Rp" . number_format($value['harga_master'], 0, ',', '.') . " - " . "Rp" . number_format($value['harga_master'], 0, ',', '.');
                $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.') . " - " . "Rp" . number_format($harga_disc, 0, ',', '.');
            }

            $varian_diskon = 'N';
            if ($varian_diskon == 'N') {
                $status_varian_diskon = 'OFF';
            } else {
                $status_varian_diskon = 'UPTO';
            }

            $status_jenis_harga = '1';

            array_push($result, array(
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
            ));
        }
        break;
    case 'harga_termurah':
        $q = $_GET['q'] ?? '';
        $result = array();
        if (empty($q)) {
            $data = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
            a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah, a.id_sub_kategori, c.nama_kategori
            FROM master_item a JOIN stok b ON a.id_master = b.id_barang 
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' GROUP BY a.id_master ORDER BY a.harga_master ASC LIMIT $offset, $limit");
        } else {
            $data = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
            a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah, a.id_sub_kategori, c.nama_kategori
            FROM master_item a JOIN stok b ON a.id_master = b.id_barang
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' AND a.judul_master LIKE '%$q%' GROUP BY a.id_master ORDER BY a.harga_master ASC LIMIT $offset, $limit");
        }
        foreach ($data as $key => $value) {

            //! untuk varian harga diskon atau enggak
            $varian_harga = 'N';
            if ($varian_harga == 'N') {

                if ($value['diskon_persen'] != 0) {
                    $status_diskon = 'Y';
                    (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                } else {
                    $status_diskon = 'N';
                    (float)$harga_disc = $value['harga_master'];
                }

                $harga_produk = "Rp" . number_format($value['harga_master'], 0, ',', '.');
                $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.');
            } else {

                if ($value['diskon_persen'] != 0) {
                    $status_diskon = 'Y';
                    (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                } else {
                    $status_diskon = 'N';
                    (float)$harga_disc = $value['harga_master'];
                }

                $harga_produk = "Rp" . number_format($value['harga_master'], 0, ',', '.') . " - " . "Rp" . number_format($value['harga_master'], 0, ',', '.');
                $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.') . " - " . "Rp" . number_format($harga_disc, 0, ',', '.');
            }

            $varian_diskon = 'N';
            if ($varian_diskon == 'N') {
                $status_varian_diskon = 'OFF';
            } else {
                $status_varian_diskon = 'UPTO';
            }

            $status_jenis_harga = '1';

            array_push($result, array(
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
            ));
        }
        break;
    case 'harga_termahal':
        $q = $_GET['q'] ?? '';
        $result = array();
        if (empty($q)) {
            $data = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
            a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah, a.id_sub_kategori, c.nama_kategori
            FROM master_item a JOIN stok b ON a.id_master = b.id_barang
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' GROUP BY a.id_master ORDER BY a.harga_master DESC LIMIT $offset, $limit");
        } else {
            $data = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
            a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah, a.id_sub_kategori, c.nama_kategori
            FROM master_item a JOIN stok b ON a.id_master = b.id_barang
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' AND a.judul_master LIKE '%$q%' GROUP BY a.id_master ORDER BY a.harga_master DESC LIMIT $offset, $limit");
        }
        foreach ($data as $key => $value) {

            //! untuk varian harga diskon atau enggak
            $varian_harga = 'N';
            if ($varian_harga == 'N') {

                if ($value['diskon_persen'] != 0) {
                    $status_diskon = 'Y';
                    (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                } else {
                    $status_diskon = 'N';
                    (float)$harga_disc = $value['harga_master'];
                }

                $harga_produk = "Rp" . number_format($value['harga_master'], 0, ',', '.');
                $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.');
            } else {

                if ($value['diskon_persen'] != 0) {
                    $status_diskon = 'Y';
                    (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
                } else {
                    $status_diskon = 'N';
                    (float)$harga_disc = $value['harga_master'];
                }

                $harga_produk = "Rp" . number_format($value['harga_master'], 0, ',', '.') . " - " . "Rp" . number_format($value['harga_master'], 0, ',', '.');
                $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.') . " - " . "Rp" . number_format($harga_disc, 0, ',', '.');
            }

            $varian_diskon = 'N';
            if ($varian_diskon == 'N') {
                $status_varian_diskon = 'OFF';
            } else {
                $status_varian_diskon = 'UPTO';
            }

            $status_jenis_harga = '1';

            array_push($result, array(
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
            ));
        }
        break;
    default:
        $q = $_GET['q'] ?? '';
        $result = array();
        if (empty($q)) {
            $data = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
            a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah, a.id_sub_kategori, c.nama_kategori, a.status_varian
            FROM master_item a JOIN stok b ON a.id_master = b.id_barang
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' GROUP BY a.id_master ORDER BY a.tanggal_approve DESC LIMIT $offset, $limit");
        } else {
            $data = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
            a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah, a.id_sub_kategori, c.nama_kategori
            FROM master_item a JOIN stok b ON a.id_master = b.id_barang 
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' AND a.judul_master LIKE '%$q%' GROUP BY a.id_master ORDER BY a.tanggal_approve DESC LIMIT $offset, $limit");
        }
        foreach ($data as $key => $value) {


            $datamaster = "SELECT * FROM master_item WHERE id_master = 
                '$value[id_master]'";
            $cekitemdata = $conn->query($datamaster);
            $data2 = $cekitemdata->fetch_object();


            if ($value['status_varian'] == 'Y') {
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

                $status_jenis_harga = '2';
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

                $status_jenis_harga = '1';
                $harga_produk = rupiah($value['harga_master']);
                $harga_tampil = rupiah($harga_disc);
            }

            // if ($data2->status_master_detail == '2') {
            //     $imagegambar = $getimagebukufisik . $value['image_master'];
            // } else {
            //     $imagegambar = $getimagefisik . $value['image_master'];
            // }

            array_push($result, array(
                'id_master' => $value['id_master'],
                'judul_master' => $value['judul_master'],
                'image_master' => $data2->status_master_detail == '2' ? $getimagebukufisik . $value['image_master'] : $getimagefisik . $value['image_master'],
                'harga_produk' => $harga_produk,
                'harga_tampil' => $harga_tampil,
                'status_diskon' => $status_diskon,
                'status_varian_diskon' => $status_varian_diskon,
                'status_jenis_harga' => $status_jenis_harga,
                'status_stok' => $value['jumlah'] > 0 ? 'Y' : 'N',
                'diskon' => $jumlah_diskon . "%",
                'total_dibeli' => $value['total_dibeli'] . " terjual",
                'rating_item' => 0,
            ));
        }
        break;
}

if (isset($result[0])) {
    $response->code = 200;
    $response->message = 'result';
    $response->data = $result;
    $response->json();
    die();
} else {
    $response->code = 200;
    $response->message = 'Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini';
    $response->data = [];
    $response->json();
    die();
}
mysqli_close($conn);

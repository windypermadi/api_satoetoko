<?php
require_once('../config/koneksi.php');
include "response.php";
include "function/function_stok.php";
$response = new Response();

$idsub = $_GET['id_sub'] ?? '';

if (!empty($idsub)) {
    $q = $_GET['q'] ?? '';
    if (!empty($q)) {
        $search = " AND a.judul_master LIKE '%$q%'";
    } else {
        $search = "";
    }
    $limit = $_GET['limit'] ?? '';
    $offset = $_GET['offset'] ?? '';

    $query = $conn->query("SELECT a.id_master, a.image_master, a.judul_master, a.harga_master, a.diskon_rupiah, a.diskon_persen,
    a.total_dibeli, a.total_disukai, SUM(b.jumlah) as jumlah , a.id_sub_kategori, c.nama_kategori, a.status_master_detail, a.status_varian, a.sku_induk
    FROM master_item a LEFT JOIN stok b ON a.id_master = b.id_barang
    JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub
    LEFT JOIN master_buku_detail d ON a.id_master = d.id_master
	LEFT JOIN master_fisik_detail e ON a.id_master = e.id_master
    WHERE a.status_aktif = 'Y' AND a.status_approve = '2' AND a.status_hapus = 'N' AND (d.id_master IS NOT NULL OR e.id_master IS NOT NULL) AND a.id_sub_kategori = '$idsub' $search GROUP BY a.id_master ORDER BY jumlah DESC, a.tanggal_posting DESC LIMIT $offset, $limit");
    foreach ($query as $key => $value) {

        //! untuk varian harga diskon atau enggak
        if ($value['status_varian'] == 'Y') {

            $status_varian_diskon = 'UPTO';
            $varian = $conn->query("SELECT *, (harga_varian-diskon_rupiah_varian) as harga_varian_final FROM variant WHERE id_master = '$value[id_master]' ORDER BY harga_varian_final ASC")->fetch_all(MYSQLI_ASSOC);

            //! Cek Stok dari pak Bobby
            $datastokserver = CekStok($varian[0]['sku_induk'], '');

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
            $harga_produks = $min_normal != $max_normal ?  rupiah($min_normal) . " - " . rupiah($max_normal) : rupiah($min_normal);
            $harga_tampils = $min_normal != $max_normal ?  rupiah($min) . " - " . rupiah($max) : rupiah($min);

            $harga_produk = $harga_produks;
            $harga_tampil = $harga_tampils;
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

            //! Cek Stok dari pak Bobby
            $datastokserver = CekStok($value['sku_induk'], '');
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
            'status_stok' => $datastokserver > 0 ? 'Y' : 'N',
            'diskon' => $value['diskon_persen'] . "%",
            'total_dibeli' => (int)$value['total_dibeli'],
            'rating_item' => 0,
        ];
    }
} else {
    $q = $_GET['q'] ?? '';
    if (!empty($q)) {
        $search = " WHERE nama_kategori LIKE '%$q%'";
    } else {
        $search = "";
    }
    $query = mysqli_query($conn, "SELECT * FROM kategori_sub a 
            JOIN master_item b ON a.id_sub = b.id_sub_kategori $search
            GROUP BY a.id_sub ORDER BY a.nama_kategori");
    foreach ($query as $key => $value) {

        $result[] = [
            'id_kategori'    => $value['id_sub'],
            'kode_kategori'    => $value['kode_kategori'],
            'nama_kategori'     => $value['nama_kategori'],
            'icon_apps'     => $geticonkategori . $value['icon'],
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

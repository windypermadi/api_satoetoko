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

    if ($data->status_varian == 'Y') {
        $variant = $conn->query("SELECT * FROM variant a JOIN stok b ON a.id_variant = b.id_varian WHERE a.id_master = '$id_master' AND b.id_warehouse = '$id_cabang' AND a.status_acc_var = '2' AND a.status_aktif_var = 'Y' AND a.status_hapus_var = 'N'");
        foreach ($variant as $key => $value) {
            if ($data->status_master_detail == '2') {
                if (substr($value['image_varian'], 0, 4) == 'http') {
                    $imagegambar = $value['image_varian'];
                } else {
                    $imagegambar = $getimagebukufisik . $value['image_varian'];
                }
            } else {
                if (substr($value['image_varian'], 0, 4) == 'http') {
                    $imagegambar = $value['image_varian'];
                } else {
                    $imagegambar = $getimagefisik . $value['image_varian'];
                }
            }

            $variants[] = [
                'id_variant' => $value['id_variant'],
                'keterangan_varian' => $value['keterangan_varian'],
                'harga_varian' => $value['harga_varian'],
                'diskon_rupiah_varian' => $value['diskon_rupiah_varian'],
                'diskon_persen_varian' => $value['diskon_persen_varian'],
                'image_varian' => $imagegambar,
                'stok' => $value['jumlah'],
            ];
        }
    } else {
        $variant = $conn->query("SELECT * FROM master_item a LEFT JOIN stok b ON a.id_master = b.id_barang WHERE b.id_barang = '$id_master' AND b.id_warehouse = '$id_cabang' AND a.status_aktif = 'Y' AND a.status_approve = '2';");
        foreach ($variant as $key => $value) {

            //? cek apakah barang ini masuk flashsale atau tidak
            $dataproduct = $conn->query("SELECT *, (stok_flashdisk-stok_terjual_flashdisk) as sisa_stok FROM flashsale a 
                JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
                JOIN master_item c ON b.kd_barang = c.id_master
                WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND a.waktu_mulai <= CURRENT_DATE AND a.waktu_selesai >= CURRENT_DATE AND b.kd_barang = '$value[id_master]'")->fetch_object();

            if (isset($dataproduct->id_flashsale)) {
                (float)$harga_disc = $dataproduct->harga_master - ($dataproduct->harga_master * ($dataproduct->diskon / 100));
                $diskon_format = rupiah($harga_disc);
                $harga_master = rupiah($dataproduct->harga_master);
                $stok = $dataproduct->stok_flashdisk - $dataproduct->stok_terjual_flashdisk;
            } else {
                (float)$harga_disc = $value['harga_master'] - ($value['harga_master'] * ($value['diskon_rupiah']));
                $diskon_format = rupiah($harga_disc);
                $harga_master = rupiah($value['harga_master']);
                $stok = $value['jumlah'];
            }

            if ($data->status_master_detail == '2') {
                if (substr($value['image_varian'], 0, 4) == 'http') {
                    $imagegambar = $value['image_varian'];
                } else {
                    $imagegambar = $getimagebukufisik . $value['image_varian'];
                }
            } else {
                if (substr($value['image_varian'], 0, 4) == 'http') {
                    $imagegambar = $value['image_varian'];
                } else {
                    $imagegambar = $getimagefisik . $value['image_varian'];
                }
            }

            $variants[] = [
                'id_variant' => $value['id_master'],
                'keterangan_varian' => $value['judul_master'],
                'harga_varian' => $value['harga_master'],
                'diskon_rupiah_varian' => $value['diskon_rupiah_varian'],
                'diskon_persen_varian' => $value['diskon_persen_varian'],
                'image_varian' => $imagegambar,
                'stok' => $stok,
            ];
        }
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

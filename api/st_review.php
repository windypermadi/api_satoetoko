<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$tag = $_REQUEST['tag'];

if (!empty($tag)) {
    switch ($tag) {
            //! input rating per barang
        case 'showInput':
            $id_transaksi  = $_REQUEST['idTransaksi'];

            $query = $conn->query("SELECT mi.id_master, v.id_variant, td.id_transaksi_detail, mi.judul_master, v.keterangan_varian, mi.image_master, v.image_varian, td.id_barang,r.* FROM transaksi_detail td 
            LEFT JOIN master_item mi ON td.id_barang = mi.id_master
            LEFT JOIN variant v ON td.id_barang = v.id_variant
            LEFT JOIN review r ON td.id_transaksi_detail = r.id_detail_transaksi
            WHERE td.id_transaksi = '$id_transaksi' AND r.id_review IS NULL;");
            foreach ($query as $key => $value) {
                if ($value['id_variant'] != NULL) {
                    $getstatusmaster = $conn->query("SELECT b.status_master_detail FROM variant a JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$value[id_variant]'")->fetch_assoc();

                    $getvarian = $conn->query("SELECT b.judul_master, b.status_master_detail,a.id_master FROM variant a LEFT JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$value[id_variant]'")->fetch_assoc();

                    $judul_master = $getvarian['judul_master'];
                    $variasi = $value['keterangan_varian'];

                    if ($getstatusmaster['status_master_detail'] == '2') {
                        if (substr($value['image_varian'], 0, 4) == 'http') {
                            $image = $value['image_varian'];
                        } else {
                            $image = $getimagebukufisik . $value['image_varian'];
                        }
                    } else {
                        if (substr($value['image_varian'], 0, 4) == 'http') {
                            $image = $value['image_varian'];
                        } else {
                            $image = $getimagefisik . $value['image_varian'];
                        }
                    }
                } else {
                    $getstatusmaster = $conn->query("SELECT status_master_detail FROM master_item WHERE id_master = '$value[id_master]'")->fetch_assoc();

                    $judul_master = $value['judul_master'];
                    $variasi = "";

                    if ($getstatusmaster['status_master_detail'] == '2') {
                        if (substr($value['image_master'], 0, 4) == 'http') {
                            $image = $value['image_master'];
                        } else {
                            $image = $getimagebukufisik . $value['image_master'];
                        }
                    } else {
                        if (substr($value['image_master'], 0, 4) == 'http') {
                            $image = $value['image_master'];
                        } else {
                            $image = $getimagefisik . $value['image_master'];
                        }
                    }
                }

                $getprodukcoba[] = [
                    "id_transaksi_detail" => $value['id_transaksi_detail'],
                    "idProduct" => $value['id_variant'] != NULL ? $value['id_variant'] : $value['id_master'],
                    "judul_master" => $judul_master,
                    "variasi" => $variasi,
                    "image_master" => $image

                ];
            }
            if ($getprodukcoba) {
                $response->data = $getprodukcoba;
                $response->sukses(200);
            } else {
                $response->data = [];
                $response->sukses(200);
            }

            break;
        case 'add':
            $idDetail  = $_POST['idTransaksiDetail'];
            $idBarang  = $_POST['idProduct'];
            $idUser = $_POST['idUser'];
            $deskripsi = $_POST['text'];
            $rating = $_POST['rating'];
            $hideUser = $_POST['isHide'];
            if (!empty($idDetail) && !empty($idBarang) && !empty($idUser)) {
                $gettransaksi = $conn->query("SELECT id_transaksi FROM transaksi_detail WHERE id_transaksi_detail = '$idDetail'")->fetch_object();
                $cekRating = $conn->query("SELECT * FROM review WHERE id_user = '$idUser' AND id_barang = '$idBarang' AND id_detail_transaksi = '$idDetail'")->fetch_object();
                if (isset($cekRating->id_review)) {
                    $addReview = $conn->query("UPDATE review SET deskripsi = '$deskripsi',rating = $rating, tgl_edit = NOW() WHERE id_detail_transaksi = '$idDetail' AND id_barang = '$idBarang' AND id_user = '$idUser';");
                } else {
                    $addReview = $conn->query("INSERT INTO review SET id_review = UUID_SHORT(),
                    id_transaksi = '$gettransaksi->id_transaksi',
                    id_detail_transaksi = '$idDetail',
                    id_barang = '$idBarang',
                    id_user = '$idUser',
                    deskripsi = '$deskripsi',
                    rating = $rating,
                    tgl_review = NOW(),
                    hide_nick = '$hideUser'");
                }

                if ($addReview) {
                    $response->data = "Selamat kamu berhasil review produk ini.";
                    $response->sukses(200);
                } else {
                    $response->data = [];
                    $response->message = 'Ngebug.';
                    $response->error(400);
                }
            } else {
                $response->data = [];
                $response->message = 'ID tidak boleh ada yang kosong.';
                $response->error(400);
            }
            break;

        case 'edit':
            # code...
            break;
    }
} else {
    $response->data = [];
    $response->message = 'tag tidak ditemukan.';
    $response->error(400);
}

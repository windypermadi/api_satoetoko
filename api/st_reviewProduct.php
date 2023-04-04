<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$idUser = $_REQUEST['idUser'] ?? '';
$idProduct = $_REQUEST['idProduct'];

$getsum = $conn->query("SELECT SUM(rating) as rating, COUNT(id_review) as count_rating FROM review WHERE id_barang = '$idProduct'")->fetch_object();
$getaverage = $getsum->rating / $getsum->count_rating;
$getUser = $conn->query("SELECT * FROM data_user du 
JOIN review r ON du.id_login = r.id_user");
foreach ($getUser as $key => $value) {
    $user = [
        "nama" => $value['nama_user'],
        "profile" => $getprofile . $value['profil_user'],
        "isHide" => $value['hide_nick']
    ];
}

$getproduk = $conn->query("SELECT * FROM review WHERE id_barang = '$idProduct'");
foreach ($getproduk as $key => $value) {
    if ($value['id_variant'] != NULL) {
        $getstatusmaster = $conn->query("SELECT b.status_master_detail FROM variant a JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$value[id_variant]'")->fetch_assoc();

        $getjudulmaster = $conn->query("SELECT judul_master FROM variant a LEFT JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$value[id_variant]'")->fetch_assoc();

        $judul_master = $getjudulmaster['judul_master'];
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

    $headReview = [
        "totalReview" => $getaverage,
        "totalUlasan" => $getsum->count_rating
    ];

    $getlistreview = [
        "text" => $value['deskripsi'],
        "rating" => (float)$value['rating'],
        "foto" => 'foto',
        "video" => 'video',
        "varian" => 'varian',
        "date" => $value['tgl_review']
    ];

    $datakepala['head'] = $headReview;
    $datakepala['listReview'][] = [
        'user' => $user,
        'review' => $getlistreview
    ];
}
if ($datakepala) {
    $response->data = $datakepala;
    $response->sukses(200);
} else {
    $response->data = [];
    $response->sukses(200);
}

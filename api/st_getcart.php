<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_login         = $_GET['id_login'];
$tag              = $_GET['tag'];

if (isset($id_login)) {

    switch ($tag) {
        case 'semua':
            $data = $conn->query("SELECT * FROM user_keranjang a
            JOIN master_item b ON a.id_barang = b.id_master
            LEFT JOIN variant c ON a.id_variant = c.id_variant
            WHERE a.id_user = '$id_login';");
            $datalist = array();

            foreach ($data as $key) {

                $datamaster = "SELECT * FROM master_item WHERE id_master = 
                '$key[id_barang]'";
                $cekitemdata = $conn->query($datamaster);
                $data2 = $cekitemdata->fetch_object();

                //? cek apakah barang ini masuk flashsale atau tidak
                $dataproduct = $conn->query("SELECT *, (stok_flashdisk-stok_terjual_flashdisk) as sisa_stok FROM flashsale a 
                JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
                JOIN master_item c ON b.kd_barang = c.id_master
                WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND a.waktu_mulai >= CURRENT_DATE AND a.waktu_mulai <= CURRENT_TIME AND a.waktu_selesai >= CURRENT_DATE AND a.waktu_selesai >= CURRENT_TIME AND b.kd_barang = '$key[id_barang]'")->fetch_object();

                if ($dataproduct->id_flashsale) {
                    if ($dataproduct->sisa_stok != 0) {
                        //? tidak varian
                        //? masih ada stok flashsale
                        $status_diskon = 'Y';
                        (float)$harga_disc = $dataproduct->harga_master - ($dataproduct->harga_master * ($dataproduct->diskon / 100));

                        $harga_produk = rupiah($dataproduct->harga_master);
                        $harga_tampil = rupiah($harga_disc);
                        $harga_produk_int = $dataproduct->harga_master;
                        $harga_tampil_int = $harga_disc;

                        $cekstok = $dataproduct->sisa_stok;
                    } else {
                        //? tidak varian
                        //? stok habis di flashsale
                        if ($key['diskon_rupiah'] != 0) {
                            //? cek diskon biasa
                            $status_diskon = 'Y';
                            $harga_produk = rupiah($key['harga_master']);
                            $harga_tampil = rupiah($key['harga_master'] - $key['diskon_rupiah']);
                            $harga_produk_int = $key['harga_master'];
                            $harga_tampil_int = $harga_disc;
                        } else {
                            $status_diskon = 'N';
                            $harga_produk = rupiah($key['harga_master']);
                            $harga_tampil = rupiah($key['harga_master']);
                            $harga_produk_int = $key['harga_master'];
                            $harga_tampil_int = $harga_disc;
                        }
                        $cekstok = $conn->query("SELECT jumlah FROM user_keranjang a 
                        LEFT JOIN stok b ON a.id_barang = b.id_barang
                        WHERE a.id_user = '$id_login' AND a.id_barang = '$key[id_barang]'")->fetch_assoc();
                    }
                } else {
                    //! INI DIPENDING DULU FLASHSALE VARIAN
                    if ($key['status_varian'] == 'Y') {

                        $cekstok = $conn->query("SELECT jumlah FROM user_keranjang a 
                    LEFT JOIN stok b ON a.id_variant = b.id_varian
                    WHERE a.id_user = '$id_login' AND a.id_variant = '$key[id_variant]'")->fetch_assoc();

                        if ($key['diskon_persen_varian'] != 0) {
                            $status_diskon = 'Y';
                            $harga_disc = $key['harga_varian'] - $key['diskon_rupiah_varian'];
                        } else {
                            $status_diskon = 'N';
                            $harga_disc = $key['harga_varian'];
                        }

                        $harga_produk = "Rp" . number_format($key['harga_varian'], 0, ',', '.');
                        $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.');
                        $harga_produk_int = $key['harga_varian'];
                        $harga_tampil_int = $harga_disc;
                    } else {

                        $cekstok = $conn->query("SELECT jumlah FROM user_keranjang a 
                    LEFT JOIN stok b ON a.id_barang = b.id_barang
                    WHERE a.id_user = '$id_login' AND a.id_barang = '$key[id_barang]'")->fetch_assoc();

                        if ($key['diskon_persen'] != 0) {
                            $status_diskon = 'Y';
                            $harga_disc = $key['harga_master'] - $key['diskon_rupiah'];
                        } else {
                            $status_diskon = 'N';
                            $harga_disc = $key['harga_master'];
                        }

                        $harga_produk = "Rp" . number_format($key['harga_master'], 0, ',', '.');
                        $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.');
                        $harga_produk_int = $key['harga_master'];
                        $harga_tampil_int = $harga_disc;
                    }
                }

                // if (!is_null($value['id_variant'])) {
                //     $id_variant = $_GET['id_variant'];
                //     $data = $conn->query("SELECT * FROM user_keranjang a
                //     JOIN master_item b ON a.id_barang = b.id_master
                //     LEFT JOIN variant c ON a.id_variant = c.id_variant
                //     WHERE a.id_user = '$id_login' AND a.id_variant = '$id_variant';");
                //     $datalist = array();
                // } else {
                //     $data = $conn->query("SELECT * FROM user_keranjang a
                //     JOIN master_item b ON a.id_barang = b.id_master
                //     LEFT JOIN variant c ON a.id_variant = c.id_variant
                //     WHERE a.id_user = '$id_login';");
                //     $datalist = array();
                // }

                array_push($datalist, array(
                    'id' => $key['id'],
                    'image_master' => $data2->status_master_detail == '2' ? $getimagebukufisik . $key['image_master'] : $getimagefisik . $key['image_master'],
                    'judul' => $key['judul_master'],
                    'id_varian' => $key['id_variant'],
                    'varian' => $key['keterangan_varian'],
                    'harga_produk' => $harga_produk,
                    'harga_tampil' => $harga_tampil,
                    'harga_produk_int' => $harga_produk_int,
                    'harga_tampil_int' => $harga_tampil_int,
                    'status_diskon' => $status_diskon,
                    'qty' => $key['qty'],
                    'stok_saatini' => $cekstok['jumlah'],
                    'id_cabang' => $key['id_gudang'],
                ));
            }

            if ($datalist[0]) {
                $response->data = $datalist;
                $response->sukses(200);
            } else {
                $response->data = [];
                $response->sukses(200);
            }
            break;
        case 'diskon':
            $data = $conn->query("SELECT * FROM user_keranjang a
    JOIN master_item b ON a.id_barang = b.id_master
    LEFT JOIN variant c ON a.id_variant = c.id_variant
    WHERE a.id_user = '$id_login'");
            $datalist = array();

            foreach ($data as $key) {

                $datamaster = "SELECT * FROM master_item WHERE id_master = 
                '$key[id_barang]'";
                $cekitemdata = $conn->query($datamaster);
                $data2 = $cekitemdata->fetch_object();

                if ($key['status_varian'] == 'Y') {

                    $cekstok = $conn->query("SELECT jumlah FROM user_keranjang a 
                    LEFT JOIN stok b ON a.id_variant = b.id_varian
                    WHERE a.id_user = '$id_login' AND a.id_variant = '$key[id_variant]'")->fetch_assoc();

                    if ($key['diskon_persen_varian'] != 0) {
                        $status_diskon = 'Y';
                        $harga_disc = $key['harga_varian'] - $key['diskon_rupiah_varian'];
                    } else {
                        $status_diskon = 'N';
                        $harga_disc = $key['harga_varian'];
                    }

                    $harga_produk = "Rp" . number_format($key['harga_varian'], 0, ',', '.');
                    $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.');
                    $harga_produk_int = $key['harga_varian'];
                    $harga_tampil_int = $harga_disc;
                } else {

                    $cekstok = $conn->query("SELECT jumlah FROM user_keranjang a 
                    LEFT JOIN stok b ON a.id_barang = b.id_barang
                    WHERE a.id_user = '$id_login' AND a.id_barang = '$key[id_barang]'")->fetch_assoc();

                    if ($key['diskon_persen'] != 0) {
                        $status_diskon = 'Y';
                        $harga_disc = $key['harga_master'] - $key['diskon_rupiah'];
                    } else {
                        $status_diskon = 'N';
                        $harga_disc = $key['harga_master'];
                    }

                    $harga_produk = "Rp" . number_format($key['harga_master'], 0, ',', '.');
                    $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.');
                    $harga_produk_int = $key['harga_master'];
                    $harga_tampil_int = $harga_disc;
                }

                // if (!is_null($value['id_variant'])) {
                //     $id_variant = $_GET['id_variant'];
                //     $data = $conn->query("SELECT * FROM user_keranjang a
                //     JOIN master_item b ON a.id_barang = b.id_master
                //     LEFT JOIN variant c ON a.id_variant = c.id_variant
                //     WHERE a.id_user = '$id_login' AND a.id_variant = '$id_variant';");
                //     $datalist = array();
                // } else {
                //     $data = $conn->query("SELECT * FROM user_keranjang a
                //     JOIN master_item b ON a.id_barang = b.id_master
                //     LEFT JOIN variant c ON a.id_variant = c.id_variant
                //     WHERE a.id_user = '$id_login';");
                //     $datalist = array();
                // }

                array_push($datalist, array(
                    'id' => $key['id'],
                    'image_master' => $data2->status_master_detail == '2' ? $getimagebukufisik . $key['image_master'] : $getimagefisik . $key['image_master'],
                    'judul' => $key['judul_master'],
                    'id_varian' => $key['id_variant'],
                    'varian' => $key['keterangan_varian'],
                    'harga_produk' => $harga_produk,
                    'harga_tampil' => $harga_tampil,
                    'harga_produk_int' => $harga_produk_int,
                    'harga_tampil_int' => $harga_tampil_int,
                    'status_diskon' => $status_diskon,
                    'qty' => $key['qty'],
                    'stok_saatini' => $cekstok['jumlah'],
                    'id_cabang' => $key['id_gudang'],
                ));
            }

            if ($datalist[0]) {
                $response->data = $datalist;
                $response->sukses(200);
            } else {
                $response->data = [];
                $response->sukses(200);
            }
            break;
            //     case 'diskon':
            //         $data = $conn->query("SELECT * FROM user_keranjang a
            // JOIN master_item b ON a.id_barang = b.id_master
            // LEFT JOIN variant c ON a.id_variant = c.id_variant
            // WHERE a.id_user = '$id_login' AND b.diskon_persen != 0");
            //         $datalist = array();

            //         foreach ($data as $key) {

            //             $datamaster = "SELECT * FROM master_item WHERE id_master = 
            //             '$key[id_barang]'";
            //             $cekitemdata = $conn->query($datamaster);
            //             $data2 = $cekitemdata->fetch_object();

            //             if ($key['status_varian'] == 'Y') {

            //                 $cekstok = $conn->query("SELECT jumlah FROM user_keranjang a 
            //                 LEFT JOIN stok b ON a.id_variant = b.id_varian
            //                 WHERE a.id_user = '$id_login' AND a.id_variant = '$key[id_variant]'")->fetch_assoc();

            //                 if ($key['diskon_persen_varian'] != 0) {
            //                     $status_diskon = 'Y';
            //                     $harga_disc = $key['harga_varian'] - $key['diskon_rupiah_varian'];
            //                 } else {
            //                     $status_diskon = 'N';
            //                     $harga_disc = $key['harga_varian'];
            //                 }

            //                 $harga_produk = "Rp" . number_format($key['harga_varian'], 0, ',', '.');
            //                 $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.');
            //                 $harga_produk_int = $key['harga_varian'];
            //                 $harga_tampil_int = $harga_disc;
            //             } else {

            //                 $cekstok = $conn->query("SELECT jumlah FROM user_keranjang a 
            //                 LEFT JOIN stok b ON a.id_barang = b.id_barang
            //                 WHERE a.id_user = '$id_login' AND a.id_barang = '$key[id_barang]'")->fetch_assoc();

            //                 if ($key['diskon_persen'] != 0) {
            //                     $status_diskon = 'Y';
            //                     $harga_disc = $key['harga_master'] - $key['diskon_rupiah'];
            //                 } else {
            //                     $status_diskon = 'N';
            //                     $harga_disc = $key['harga_master'];
            //                 }

            //                 $harga_produk = "Rp" . number_format($key['harga_master'], 0, ',', '.');
            //                 $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.');
            //                 $harga_produk_int = $key['harga_master'];
            //                 $harga_tampil_int = $harga_disc;
            //             }

            //             // if (!is_null($value['id_variant'])) {
            //             //     $id_variant = $_GET['id_variant'];
            //             //     $data = $conn->query("SELECT * FROM user_keranjang a
            //             //     JOIN master_item b ON a.id_barang = b.id_master
            //             //     LEFT JOIN variant c ON a.id_variant = c.id_variant
            //             //     WHERE a.id_user = '$id_login' AND a.id_variant = '$id_variant';");
            //             //     $datalist = array();
            //             // } else {
            //             //     $data = $conn->query("SELECT * FROM user_keranjang a
            //             //     JOIN master_item b ON a.id_barang = b.id_master
            //             //     LEFT JOIN variant c ON a.id_variant = c.id_variant
            //             //     WHERE a.id_user = '$id_login';");
            //             //     $datalist = array();
            //             // }

            //             array_push($datalist, array(
            //                 'id' => $key['id'],
            //                 'image_master' => $data2->status_master_detail == '2' ? $getimagebukufisik . $key['image_master'] : $getimagefisik . $key['image_master'],
            //                 'judul' => $key['judul_master'],
            //                 'id_varian' => $key['id_variant'],
            //                 'varian' => $key['keterangan_varian'],
            //                 'harga_produk' => $harga_produk,
            //                 'harga_tampil' => $harga_tampil,
            //                 'harga_produk_int' => $harga_produk_int,
            //                 'harga_tampil_int' => $harga_tampil_int,
            //                 'status_diskon' => $status_diskon,
            //                 'qty' => $key['qty'],
            //                 'stok_saatini' => $cekstok['jumlah'],
            //                 'id_cabang' => $key['id_gudang'],
            //             ));
            //         }

            //         if ($datalist[0]) {
            //             $response->data = $datalist;
            //             $response->sukses(200);
            //         } else {
            //             $response->data = [];
            //             $response->sukses(200);
            //         }
            //         break;
            // case 'diskon':
            //     $data = $conn->query("SELECT * FROM user_keranjang a
            //     JOIN master_item b ON a.id_barang = b.id_master
            //     LEFT JOIN variant c ON a.id_variant = c.id_variant
            //     WHERE a.id_user = '$id_login' AND b.diskon_persen != 0");
            //     $datalist = array();

            //     foreach ($data as $key => $value) {

            //         $datamaster = "SELECT * FROM master_item WHERE id_master = 
            //                     '$key[id_barang]'";
            //         $cekitemdata = $conn->query($datamaster);
            //         $data2 = $cekitemdata->fetch_object();


            //         if ($value['diskon_persen'] != 0) {
            //             $status_diskon = 'Y';
            //             (float)$harga_disc = $value['harga_master'] - $value['diskon_rupiah'];
            //         } else {
            //             $status_diskon = 'N';
            //             (float)$harga_disc = $value['harga_master'];
            //         }

            //         $harga_produk = "Rp" . number_format($value['harga_master'], 0, ',', '.');
            //         $harga_tampil = "Rp" . number_format($harga_disc, 0, ',', '.');
            //         $harga_produk_int = $key['harga_master'];
            //         $harga_tampil_int = $harga_disc;

            //         if (!is_null($value['id_variant'])) {

            //             $cekstok = $conn->query("SELECT jumlah FROM user_keranjang a 
            //             LEFT JOIN stok b ON a.id_barang = b.id_barang
            //             WHERE a.id_user = '$id_login' AND a.id_variant = '$value[id_barang]'")->fetch_assoc();

            //             $id_variant = $_GET['id_variant'];
            //             $data = $conn->query("SELECT * FROM user_keranjang a
            //             JOIN master_item b ON a.id_barang = b.id_master
            //             LEFT JOIN variant c ON a.id_variant = c.id_variant
            //             WHERE a.id_user = '$id_login' AND a.id_variant = '$id_variant';");
            //             $datalist = array();
            //         } else {

            //             $cekstok = $conn->query("SELECT jumlah FROM user_keranjang a 
            //             LEFT JOIN stok b ON a.id_variant = b.id_varian
            //             WHERE a.id_user = '$id_login' AND a.id_variant = '$value[id_variant]'")->fetch_assoc();

            //             $data = $conn->query("SELECT * FROM user_keranjang a
            //             JOIN master_item b ON a.id_barang = b.id_master
            //             LEFT JOIN variant c ON a.id_variant = c.id_variant
            //             WHERE a.id_user = '$id_login';");
            //             $datalist = array();
            //         }

            //         array_push($datalist, array(
            //             'id' => $value['id'],
            //             'image_master' => $data2->status_master_detail == '2' ? $getimagebukufisik . $key['image_master'] : $getimagefisik . $key['image_master'],
            //             'judul' => $value['judul_master'],
            //             'varian' => $value['keterangan_varian'],
            //             'harga_produk' => $harga_produk,
            //             'harga_tampil' => $harga_tampil,
            //             'harga_produk_int' => $harga_produk_int,
            //             'harga_tampil_int' => $harga_tampil_int,
            //             'status_diskon' => $status_diskon,
            //             'qty' => $value['qty'],
            //             'stok_saatini' => $cekstok['jumlah'],
            //             'id_cabang' => $key['id_gudang'],
            //         ));
            //     }

            //     if ($datalist[0]) {
            //         $response->data = $datalist;
            //         $response->sukses(200);
            //     } else {
            //         $response->data = [];
            //         $response->sukses(200);
            //     }

            //     break;
            // case 'repeat':

            //     break;
    }
} else {
    $response->data = null;
    $response->error(400);
}
die();
mysqli_close($conn);

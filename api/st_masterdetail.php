<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_master = $_GET['id_master'];

if (isset($id_master)) {
    $datamaster = "SELECT * FROM master_item WHERE id_master = 
                '$id_master'";
    $cekitemdata = $conn->query($datamaster);
    $data = $cekitemdata->fetch_object();

    $sekarang = "SELECT * FROM flashsale a 
    JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
    JOIN master_item c ON b.kd_barang = c.id_master
    WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND b.kd_barang = '$id_master' AND a.waktu_mulai < NOW() AND a.waktu_selesai > NOW()";
    $ceksekarang = $conn->query($sekarang)->num_rows;
    $getflash = $conn->query($sekarang)->fetch_object();

    $akandatang = "SELECT * FROM flashsale a 
        JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
        JOIN master_item c ON b.kd_barang = c.id_master
        WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND b.kd_barang = '$id_master' AND a.waktu_mulai > NOW() AND a.waktu_selesai > NOW()";
    $cekakandatang = $conn->query($akandatang)->num_rows;

    if ($ceksekarang > 0) {
        $status_flashsale = '2';
        $stok_flashdisk = $getflash->stok_flashdisk;
        $stok_terjual_flashdisk = $getflash->stok_terjual_flashdisk;
        $sisa_stok_flash = $stok_flashdisk - $stok_terjual_flashdisk;
    } else {
        if ($cekakandatang > 0) {
            $status_flashsale = '1';
        } else {
            $status_flashsale = '0';
        }
    }

    $datastok = mysqli_fetch_object($conn->query("SELECT sum(jumlah) as jumlah, alamat_cabang FROM stok a JOIN cabang b ON a.id_warehouse = b.id_cabang WHERE a.id_barang = '$id_master';"));
    $warehousedata = $conn->query("SELECT * FROM stok a JOIN cabang b ON a.id_warehouse = b.id_cabang WHERE a.id_barang = '$id_master' AND b.status_aktif = 'Y' AND b.status_hapus = 'N' GROUP BY a.id_warehouse");
    foreach ($warehousedata as $key => $value) {
        $warehousedatas[] = [
            'id_cabang' => $value['id_cabang'],
            'kode_cabang' => $value['kode_cabang'],
            'nama_cabang' => $value['nama_cabang'],
            'alamat_lengkap_cabang' => $value['alamat_lengkap_cabang'],
            'alamat_cabang' => $value['alamat_cabang'],
            'stok' => $value['jumlah']
        ];
    }

    if ($status_flashsale == '2') {
        if ($sisa_stok_flash != 0) {
            $stokdata = $sisa_stok_flash;
        } else {
            $stokdata = $datastok->jumlah;
        }
    } else if ($status_flashsale == '1') {
        $stokdata = $datastok->jumlah;
    } else {
        $stokdata = $datastok->jumlah;
    }


    switch ($data->status_master_detail) {
        case '2':
            //? buku fisik
            // $datanew = mysqli_fetch_object($conn->query("SELECT a.id_master, a.judul_master, a.slug_judul_master, d.nama_kategori, a.harga_master, a.diskon_rupiah,
            //         a.diskon_persen, a.harga_sewa, a.diskon_sewa_rupiah, a.diskon_sewa_persen, b.deskripsi_produk, b.status_bahaya, b.merek,
            //         b.status_garansi, b.berat, b.dimensi, b.masa_garansi, b.konsumsi_daya, b.masa_garansi, b.negara_asal, b.tegangan, b.daya_listrik,
            //         b.masa_penyimpanan, b.tanggal_kadaluarsa, a.image_master, b.video_produk, b.gambar_1, b.gambar_2, b.gambar_3, a.status_varian FROM master_item a
            //         LEFT JOIN master_fisik_detail b ON a.id_master = b.id_master
            //         LEFT JOIN kategori_sub d ON a.id_sub_kategori = d.id_sub
            //         WHERE a.status_approve = '2' AND a.status_aktif = 'Y' AND a.status_hapus = 'N' AND a.id_master = '$id_master'"));

            $datanew = mysqli_fetch_object($conn->query("SELECT * FROM master_item a
                    LEFT JOIN master_buku_detail b ON a.id_master = b.id_master
                    WHERE a.status_approve = '2' AND a.status_aktif = 'Y' AND a.status_hapus = 'N' AND a.id_master = '$id_master'"));

            $deskripsi = $datanew->sinopsis;

            $imageurl = $conn->query("SELECT b.image_master, a.gambar_1, a.gambar_2, a.gambar_3 FROM master_buku_detail a
LEFT JOIN master_item b ON a.id_master = b.id_master WHERE a.id_master = '$data->id_master'");
            $imageurls = array();
            while ($key = mysqli_fetch_object($imageurl)) {
                array_push($imageurls, array(
                    'status_url' => '1',
                    'keterangan' => 'image',
                    'url' => $getimagebukufisik . $key->image_master,
                ));
                if ($key->gambar_1 != NULL) {
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $getimagebukufisik . $key->gambar_1,
                    ));
                }
                if ($key->gambar_2 != NULL) {
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $getimagebukufisik . $key->gambar_2,
                    ));
                }
                if ($key->gambar_3 != NULL) {
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $getimagebukufisik . $key->gambar_3,
                    ));
                }
            }

            if ($datanew->status_varian == 'Y') {
                $variant = $conn->query("SELECT * FROM variant a JOIN stok b ON a.id_variant = b.id_varian WHERE a.id_master = '$id_master'");
                foreach ($variant as $key => $value) {
                    $variants[] = [
                        'id_variant' => $value['id_variant'],
                        'keterangan_varian' => $value['keterangan_varian'],
                        'harga_varian' => $value['harga_varian'],
                        'diskon_rupiah_varian' => $value['diskon_rupiah_varian'],
                        'diskon_persen_varian' => $value['diskon_persen_varian'],
                        'image_varian' => $getimagebukufisik . $value['image_varian'],
                        'stok' => $value['jumlah'],
                    ];

                    $url_variants[] = [
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'image_varian' => $getimagebukufisik . $value['image_varian']
                    ];
                }
            } else {
                $variants = [];
                $url_variants = [];
            }
            break;
        case '3':
            //? barang fisik
            $datanew = mysqli_fetch_object($conn->query("SELECT a.id_master, a.judul_master, a.slug_judul_master, d.nama_kategori, a.harga_master, a.diskon_rupiah,
                    a.diskon_persen, a.harga_sewa, a.diskon_sewa_rupiah, a.diskon_sewa_persen, b.deskripsi_produk, b.status_bahaya, b.merek,
                    b.status_garansi, b.berat, b.dimensi, b.masa_garansi, b.konsumsi_daya, b.masa_garansi, b.negara_asal, b.tegangan, b.daya_listrik,
                    b.masa_penyimpanan, b.tanggal_kadaluarsa, a.image_master, b.video_produk, b.gambar_1, b.gambar_2, b.gambar_3, a.status_varian FROM master_item a
                    LEFT JOIN master_fisik_detail b ON a.id_master = b.id_master
                    LEFT JOIN kategori_sub d ON a.id_sub_kategori = d.id_sub
                    WHERE a.status_approve = '2' AND a.status_aktif = 'Y' AND a.status_hapus = 'N' AND a.id_master = '$id_master'"));

            $deskripsi = $datanew->deskripsi_produk;

            $imageurl = $conn->query("SELECT b.image_master, a.video_produk, a.gambar_1, a.gambar_2, a.gambar_3 FROM master_fisik_detail a
LEFT JOIN master_item b ON a.id_master = b.id_master WHERE a.id_master = '$data->id_master'");
            $imageurls = array();
            while ($key = mysqli_fetch_object($imageurl)) {
                array_push($imageurls, array(
                    'status_url' => '1',
                    'keterangan' => 'image',
                    'url' => $getimagefisik . $key->image_master,
                ));
                if ($key->video_produk != NULL) {
                    array_push($imageurls, array(
                        'status_url' => '2',
                        'keterangan' => 'video',
                        'url' => $getvideofisik . $key->video_produk,
                    ));
                }
                if ($key->gambar_1 != NULL) {
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $getimagefisik . $key->gambar_1,
                    ));
                }
                if ($key->gambar_2 != NULL) {
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $getimagefisik . $key->gambar_2,
                    ));
                }
                if ($key->gambar_3 != NULL) {
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $getimagefisik . $key->gambar_3,
                    ));
                }
            }

            if ($datanew->status_varian == 'Y') {
                $variant = $conn->query("SELECT * FROM variant a JOIN stok b ON a.id_variant = b.id_varian WHERE a.id_master = '$id_master'");
                foreach ($variant as $key => $value) {
                    $variants[] = [
                        'id_variant' => $value['id_variant'],
                        'keterangan_varian' => $value['keterangan_varian'],
                        'harga_varian' => $value['harga_varian'],
                        'diskon_rupiah_varian' => $value['diskon_rupiah_varian'],
                        'diskon_persen_varian' => $value['diskon_persen_varian'],
                        'image_varian' => $getimagefisik . $value['image_varian'],
                        'stok' => $value['jumlah'],
                    ];

                    $url_variants[] = [
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'image_varian' => $getimagefisik . $value['image_varian']
                    ];
                }
            } else {
                $variants = [];
                $url_variants = [];
            }
            break;
        default:
            $response->data = Null;
            $response->error(500);
            break;
    }

    if ($datanew->status_varian == 'Y') {
        $status_varian_diskon = 'UP TO';
        $varian = $conn->query("SELECT *, (harga_varian-diskon_rupiah_varian) as harga_varian_final FROM variant WHERE id_master = '$id_master' ORDER BY harga_varian_final ASC")->fetch_all(MYSQLI_ASSOC);
        // foreach ($varian as $key => $value) {
        // }
        if ($status_flashsale == '2') {
            if ($sisa_stok_flash != 0) {
                $min_normal = $varian[0]['harga_varian'];
                $max_normal = $varian[count($varian) - 1]['harga_varian'];

                $min = $varian[0]['harga_varian'] - ($datanew->harga_master * ($getflash->diskon / 100));
                $max = $varian[count($varian) - 1]['harga_varian'] - ($datanew->harga_master * ($getflash->diskon / 100));
            } else {
                $min_normal = $varian[0]['harga_varian'];
                $max_normal = $varian[count($varian) - 1]['harga_varian'];

                $min = $varian[0]['harga_varian_final'];
                $max = $varian[count($varian) - 1]['harga_varian_final'];
            }
        } else if ($status_flashsale == '1') {
            $min_normal = $varian[0]['harga_varian'];
            $max_normal = $varian[count($varian) - 1]['harga_varian'];

            $min = $varian[0]['harga_varian_final'];
            $max = $varian[count($varian) - 1]['harga_varian_final'];
        } else {
            $min_normal = $varian[0]['harga_varian'];
            $max_normal = $varian[count($varian) - 1]['harga_varian'];

            $min = $varian[0]['harga_varian_final'];
            $max = $varian[count($varian) - 1]['harga_varian_final'];
        }

        if ($status_flashsale == '2') {
            if ($sisa_stok_flash != 0) {
                $jumlah_diskon = $getflash->diskon;
            } else {
                $jumlah_diskon = $varian[count($varian) - 1]['diskon_persen_varian'];
            }
        } else if ($status_flashsale == '1') {
            $jumlah_diskon = $varian[count($varian) - 1]['diskon_persen_varian'];
        } else {
            $jumlah_diskon = $varian[count($varian) - 1]['diskon_persen_varian'];
        }

        //! varian ada diskon
        if ($varian[0]['diskon_rupiah_varian'] != 0) {
            $status_diskon = 'Y';
            if ($status_flashsale == '2') {
                if ($sisa_stok_flash != 0) {
                    (float)$harga_disc = $varian->diskon_rupiah_varian - ($datanew->harga_master * ($getflash->diskon / 100));
                } else {
                    (float)$harga_disc = $varian->harga_varian - $varian->diskon_rupiah_varian;
                }
            } else if ($status_flashsale == '1') {
                (float)$harga_disc = $varian->harga_varian - $varian->diskon_rupiah_varian;
            } else {
                (float)$harga_disc = $varian->harga_varian - $varian->diskon_rupiah_varian;
            }
        } else {
            $status_diskon = 'N';
            if ($status_flashsale == '2') {
                if ($sisa_stok_flash != 0) {
                    (float)$harga_disc = $varian->diskon_rupiah_varian - ($datanew->harga_master * ($getflash->diskon / 100));
                } else {
                    (float)$harga_disc = $varian->diskon_rupiah_varian;
                }
            } else if ($status_flashsale == '1') {
                (float)$harga_disc = $varian->diskon_rupiah_varian;
            } else {
                (float)$harga_disc = $varian->diskon_rupiah_varian;
            }
        }

        $status_jenis_harga = '2';
        $harga_produk = rupiah($min_normal) . " - " . rupiah($max_normal);
        $harga_tampil = rupiah($min) . " - " . rupiah($max);
    } else {
        $status_varian_diskon = 'OFF';
        if ($status_flashsale == '2') {
            if ($sisa_stok_flash != 0) {
                $jumlah_diskon = $getflash->diskon;
            } else {
                $jumlah_diskon = $datanew->diskon_persen;
            }
        } else if ($status_flashsale == '1') {
            $jumlah_diskon = $datanew->diskon_persen;
        } else {
            $jumlah_diskon = $datanew->diskon_persen;
        }
        if ($datanew->diskon_persen != 0) {
            $status_diskon = 'Y';
            if ($status_flashsale == '2') {
                if ($sisa_stok_flash != 0) {
                    (float)$harga_disc = $datanew->harga_master - ($datanew->harga_master * ($getflash->diskon / 100));
                } else {
                    (float)$harga_disc = $datanew->harga_master - $datanew->diskon_rupiah;
                }
            } else if ($status_flashsale == '1') {
                (float)$harga_disc = $datanew->harga_master - $datanew->diskon_rupiah;
            } else {
                (float)$harga_disc = $datanew->harga_master - $datanew->diskon_rupiah;
            }
        } else {
            $status_diskon = 'N';
            if ($status_flashsale == '2') {
                if ($sisa_stok_flash != 0) {
                    (float)$harga_disc = $datanew->harga_master - ($datanew->harga_master * ($getflash->diskon / 100));
                } else {
                    $harga_disc = $datanew->harga_master;
                }
            } else if ($status_flashsale == '1') {
                $harga_disc = $datanew->harga_master;
            } else {
                (float)$harga_disc = $datanew->harga_master;
            }
        }

        $status_jenis_harga = '1';
        $harga_produk = rupiah($datanew->harga_master);
        $harga_tampil = rupiah($harga_disc);
    }

    $cekwhislist = $conn->query("SELECT * FROM whislist_product WHERE id_master = '$id_master'")->num_rows;

    $data1['id_master'] = $datanew->id_master;
    $data1['judul_master'] = $datanew->judul_master;
    $data1['slug_judul_master'] = $datanew->slug_judul_master;
    $data1['deskripsi_produk'] = $deskripsi;
    $data1['harga_produk'] = $harga_produk;
    $data1['harga_tampil'] = $harga_tampil;
    $data1['status_diskon'] = $status_diskon;
    $data1['status_varian_diskon'] = $status_varian_diskon;
    $data1['status_jenis_harga'] = $status_jenis_harga;
    $data1['diskon'] = $jumlah_diskon . "%";
    $data1['total_dibeli'] = $datanew->total_dibeli . " terjual";
    $data1['rating_item'] = 0;
    $data1['status_whislist'] = $cekwhislist > 0 ? 'Y' : 'N';
    $data1['stok'] = $datastok->jumlah;
    $data1['warehouse'] = $warehousedatas;

    if ($datanew->status_bahaya == '1') {
        $bahaya = 'Tidak Berbahaya';
    } else if ($datanew->status_bahaya == '2') {
        $bahaya = 'Mengandung Baterai';
    } else if ($datanew->status_bahaya == '3') {
        $bahaya = 'Mengandung Magnet';
    } else if ($datanew->status_bahaya == '4') {
        $bahaya = 'Mengandung Cairan';
    } else if ($datanew->status_bahaya == '5') {
        $bahaya = 'Mengandung bahan Mudah Terbakar';
    }

    $data1['status_bahaya'] = $bahaya;
    $data1['merek'] = $datanew->merek;
    $data1['status_garansi'] = $datanew->status_garansi;
    $data1['negara_asal'] = $datanew->negara_asal;
    $data1['tanggal_kadaluarsa'] = $datanew->tanggal_kadaluarsa;
    $data1['berat'] = $datanew->berat;
    $data1['dimensi'] = $datanew->dimensi;
    $data1['masa_garansi'] = $datanew->masa_garansi;
    $data1['masa_penyimpanan'] = $datanew->masa_penyimpanan;
    $data1['dikirim_dari'] = $datastok->alamat_cabang;

    $data1['penulis'] = $datanew->penulis;
    $data1['penerbit'] = $datanew->penerbit;
    $data1['isbn'] = $datanew->isbn;
    $data1['deskripsi'] = $datanew->deskripsi;
    $data1['sinopsis'] = $datanew->sinopsis;
    $data1['tahun_terbit'] = $datanew->tahun_terbit;
    $data1['edisi'] = $datanew->edisi;
    $data1['halaman'] = $datanew->halaman;
    $data1['berat'] = $datanew->berat;
    $data1['status_varian'] = $datanew->status_varian;
    $data1['variant'] = $variants;
    $data1['url'] = $imageurls;
    $data1['url_variant'] = $url_variants;
    $data1['status_flashsale'] = $status_flashsale;

    $response->data = $data1;
    $response->sukses(200);
} else {
    $response->data = null;
    $response->error(400);
}

mysqli_close($conn);

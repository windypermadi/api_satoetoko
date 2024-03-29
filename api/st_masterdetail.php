<?php
require_once('../config/koneksi.php');
include "response.php";
include "function/function_stok.php";
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
    WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND b.kd_barang = '$id_master' AND a.waktu_mulai <= NOW() AND a.waktu_selesai >= NOW()";
    $ceksekarang = $conn->query($sekarang)->num_rows;
    $getflash = $conn->query($sekarang)->fetch_object();

    $akandatang = "SELECT * FROM flashsale a 
        JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
        JOIN master_item c ON b.kd_barang = c.id_master
        WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND b.kd_barang = '$id_master' AND a.waktu_mulai >= NOW() AND a.waktu_selesai >= NOW()";
    $cekakandatang = $conn->query($akandatang)->num_rows;

    if ($ceksekarang > 0) {
        $status_flashsale = '2';
        $waktu_flashsale = $getflash->waktu_selesai;
        $stok_flashdisk = $getflash->stok_flashdisk;
        $stok_terjual_flashdisk = $getflash->stok_terjual_flashdisk;
        $sisa_stok_flash = $stok_flashdisk - $stok_terjual_flashdisk;
    } else {
        if ($cekakandatang > 0) {
            $waktu_flashsale = $getflash->waktu_mulai;
            $status_flashsale = '1';
        } else {
            $waktu_flashsale = '0000-00-00 00:00:00';
            $status_flashsale = '0';
        }
    }

    $datastok = mysqli_fetch_object($conn->query("SELECT sum(jumlah) as jumlah, alamat_cabang FROM stok a JOIN cabang b ON a.id_warehouse = b.id_cabang WHERE a.id_barang = '$id_master';"));
    $warehousedata = "SELECT * FROM cabang GROUP BY id_cabang";
    $warehouseget = $conn->query($warehousedata);
    $warehousecek = $conn->query($warehousedata)->num_rows;

    //! Cek Stok dari pak Bobby
    $datastokserver = CekStok($data->sku_induk, '');

    if ($warehousecek > 0) {
        foreach ($warehouseget as $key => $value) {

            if ($status_flashsale == '2') {
                if ($sisa_stok_flash != 0) {
                    $stokwarehouse = $sisa_stok_flash;
                } else {
                    $stokwarehouse = $datastokserver > 0 ? $datastokserver : 0;
                }
            } else if ($status_flashsale == '1') {
                $stokwarehouse = $datastokserver > 0 ? $datastokserver : 0;
            } else {
                $stokwarehouse = $datastokserver > 0 ? $datastokserver : 0;
            }

            $warehousedatas[] = [
                'id_cabang' => $value['id_cabang'] != null ? $value['id_cabang'] : '',
                'kode_cabang' => $value['kode_cabang'],
                'nama_cabang' => $value['nama_cabang'],
                'alamat_lengkap_cabang' => $value['alamat_lengkap_cabang'],
                'alamat_cabang' => $value['alamat_cabang'],
                'stok' => (string)$stokwarehouse
            ];
        }
    } else {
        $warehousedatas = [];
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

    $datakategori = $conn->query("SELECT b.nama_kategori as subkategori, c.nama_kategori as kategori FROM master_item a JOIN kategori_sub b ON a.id_sub_kategori = b.id_sub 
                JOIN kategori c ON b.parent_kategori = c.id_kategori WHERE a.id_master = 
                '$id_master'")->fetch_object();

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
                if (substr($key->image_master, 0, 4) == 'http') {
                    $imagegambar = $key->image_master;
                } else {
                    $imagegambar = $getimagebukufisik . $key->image_master;
                }
                array_push($imageurls, array(
                    'status_url' => '1',
                    'keterangan' => 'image',
                    'url' => $imagegambar,
                ));
                if (!empty($key->gambar_1) and $key->gambar_1 != 'default.png') {
                    if (substr($key->gambar_1, 0, 4) == 'http') {
                        $imagegambar = $key->gambar_1;
                    } else {
                        $imagegambar = $getimagebukufisik . $key->gambar_1;
                    }
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $imagegambar,
                    ));
                }
                if (!empty($key->gambar_2) and $key->gambar_2 != 'default.png') {
                    if (substr($key->gambar_2, 0, 4) == 'http') {
                        $imagegambar = $key->gambar_2;
                    } else {
                        $imagegambar = $getimagebukufisik . $key->gambar_2;
                    }
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $imagegambar,
                    ));
                }
                if (!empty($key->gambar_3) and $key->gambar_3 != 'default.png') {
                    if (substr($key->gambar_3, 0, 4) == 'http') {
                        $imagegambar = $key->gambar_3;
                    } else {
                        $imagegambar = $getimagebukufisik . $key->gambar_3;
                    }
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $imagegambar,
                    ));
                }
            }

            if ($datanew->status_varian == 'Y') {
                $variant = $conn->query("SELECT * FROM variant a JOIN stok b ON a.id_variant = b.id_varian WHERE a.id_master = '$id_master' GROUP BY a.id_variant");
                foreach ($variant as $key => $value) {

                    //! Cek Stok dari pak Bobby
                    $datastokserver = CekStok($value['sku_induk'], '');

                    if (substr($value['image_varian'], 0, 4) == 'http') {
                        $imagegambar = $value['image_varian'];
                    } else {
                        $imagegambar = $getimagebukufisik . $value['image_varian'];
                    }

                    $variants[] = [
                        'id_variant' => $value['id_variant'],
                        'keterangan_varian' => $value['keterangan_varian'],
                        'harga_varian' => $value['harga_varian'],
                        'diskon_rupiah_varian' => $value['diskon_rupiah_varian'],
                        'diskon_persen_varian' => $value['diskon_persen_varian'],
                        'image_varian' => $imagegambar,
                        'stok' => $datastokserver > 0 ? $datastokserver : '0',
                    ];

                    $url_variants[] = [
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'image_varian' => $imagegambar
                    ];

                    $jumlahstokvar = $jumlahstokvar + $datastokserver;
                }
            } else {
                $variants = [];
                $url_variants = [];
            }

            if ($warehousecek > 0) {
                foreach ($warehouseget as $key => $value) {

                    if ($status_flashsale == '2') {
                        if ($sisa_stok_flash != 0) {
                            $stokwarehouse = $sisa_stok_flash;
                        } else {
                            $stokwarehouse = $datastokserver > 0 ? $datastokserver : 0;
                        }
                    } else if ($status_flashsale == '1') {
                        $stokwarehouse = $datastokserver > 0 ? $datastokserver : 0;
                    } else {
                        $stokwarehouse = $datastokserver > 0 ? $datastokserver : 0;
                    }

                    $warehousedatasvar[] = [
                        'id_cabang' => $value['id_cabang'] != null ? $value['id_cabang'] : '',
                        'kode_cabang' => $value['kode_cabang'],
                        'nama_cabang' => $value['nama_cabang'],
                        'alamat_lengkap_cabang' => $value['alamat_lengkap_cabang'],
                        'alamat_cabang' => $value['alamat_cabang'],
                        'stok' => (string)$jumlahstokvar
                    ];
                }
            } else {
                $warehousedatasvar = [];
            }
            break;
        case '3':
            //? barang fisik
            $datanew = mysqli_fetch_object($conn->query("SELECT a.id_master, a.judul_master, a.sku_induk, a.slug_judul_master, d.nama_kategori, a.harga_master, a.diskon_rupiah,
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
                if (substr($key->image_master, 0, 4) == 'http') {
                    $imagegambar = $key->image_master;
                } else {
                    $imagegambar = $getimagefisik . $key->image_master;
                }
                array_push($imageurls, array(
                    'status_url' => '1',
                    'keterangan' => 'image',
                    'url' => $imagegambar,
                ));
                if ($key->video_produk != NULL) {
                    array_push($imageurls, array(
                        'status_url' => '2',
                        'keterangan' => 'video',
                        'url' => $getvideofisik . $key->video_produk,
                    ));
                }
                if (!empty($key->gambar_1) and $key->gambar_1 != 'default.png') {
                    if (substr($key->gambar_1, 0, 4) == 'http') {
                        $imagegambar = $key->gambar_1;
                    } else {
                        $imagegambar = $getimagefisik . $key->gambar_1;
                    }
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $imagegambar,
                    ));
                }
                if (!empty($key->gambar_2) and $key->gambar_2 != 'default.png') {
                    if (substr($key->gambar_2, 0, 4) == 'http') {
                        $imagegambar = $key->gambar_2;
                    } else {
                        $imagegambar = $getimagefisik . $key->gambar_2;
                    }
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $imagegambar,
                    ));
                }
                if (!empty($key->gambar_3) and $key->gambar_3 != 'default.png') {
                    if (substr($key->gambar_3, 0, 4) == 'http') {
                        $imagegambar = $key->gambar_3;
                    } else {
                        $imagegambar = $getimagefisik . $key->gambar_3;
                    }
                    array_push($imageurls, array(
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'url' => $imagegambar,
                    ));
                }
            }

            if ($datanew->status_varian == 'Y') {
                $variant = $conn->query("SELECT * FROM variant a JOIN stok b ON a.id_variant = b.id_varian WHERE a.id_master = '$id_master' GROUP BY a.id_variant");
                foreach ($variant as $key => $value) {

                    //! Cek Stok dari pak Bobby
                    $datastokserver = CekStok($value['sku_induk'], '');

                    if (substr($value['image_varian'], 0, 4) == 'http') {
                        $imagegambar = $value['image_varian'];
                    } else {
                        $imagegambar = $getimagefisik . $value['image_varian'];
                    }

                    $variants[] = [
                        'id_variant' => $value['id_variant'],
                        'keterangan_varian' => $value['keterangan_varian'],
                        'harga_varian' => $value['harga_varian'],
                        'diskon_rupiah_varian' => $value['diskon_rupiah_varian'],
                        'diskon_persen_varian' => $value['diskon_persen_varian'],
                        'image_varian' => substr($value['image_varian'], 0, 4) == 'http' ? $value['image_varian'] :  $getimagefisik . $value['image_varian'],
                        'stok' => $datastokserver > 0 ? $datastokserver : '0',
                    ];

                    $url_variants[] = [
                        'status_url' => '1',
                        'keterangan' => 'image',
                        'image_varian' => substr($value['image_varian'], 0, 4) == 'http' ? $value['image_varian'] :  $getimagefisik . $value['image_varian']
                    ];
                }
                $jumlahstokvar = $jumlahstokvar + $datastokserver;
            } else {
                $variants = [];
                $url_variants = [];
            }

            if ($warehousecek > 0) {
                foreach ($warehouseget as $key => $value) {

                    if ($status_flashsale == '2') {
                        if ($sisa_stok_flash != 0) {
                            $stokwarehouse = $sisa_stok_flash;
                        } else {
                            $stokwarehouse = $datastokserver > 0 ? $datastokserver : 0;
                        }
                    } else if ($status_flashsale == '1') {
                        $stokwarehouse = $datastokserver > 0 ? $datastokserver : 0;
                    } else {
                        $stokwarehouse = $datastokserver > 0 ? $datastokserver : 0;
                    }

                    $warehousedatasvar[] = [
                        'id_cabang' => $value['id_cabang'] != null ? $value['id_cabang'] : '',
                        'kode_cabang' => $value['kode_cabang'],
                        'nama_cabang' => $value['nama_cabang'],
                        'alamat_lengkap_cabang' => $value['alamat_lengkap_cabang'],
                        'alamat_cabang' => $value['alamat_cabang'],
                        'stok' => (string)$jumlahstokvar
                    ];
                }
            } else {
                $warehousedatasvar = [];
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
        $harga_produks = $min_normal != $max_normal ?  rupiah($min_normal) . " - " . rupiah($max_normal) : rupiah($min_normal);
        $harga_tampils = $min_normal != $max_normal ?  rupiah($min) . " - " . rupiah($max) : rupiah($min);

        $harga_produk = $harga_produks;
        $harga_tampil = $harga_tampils;
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

    $cektotalterjual = $conn->query("SELECT COUNT(a.id_barang) as jumlah FROM transaksi_detail a JOIN transaksi b ON a.id_transaksi = b.id_transaksi WHERE b.status_transaksi = '7' AND a.id_barang = '$id_master'")->fetch_object();

    //! Cek Stok dari pak Bobby
    $datastokserver = CekStok($datanew->sku_induk, '');

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
    $data1['total_dibeli'] = $cektotalterjual->jumlah . " terjual";
    $data1['rating_item'] = 0;
    $data1['status_whislist'] = $cekwhislist > 0 ? 'Y' : 'N';
    $data1['stok'] = $datanew->status_varian == 'Y' ? (string)$jumlahstokvar : (string)$datastokserver;
    $data1['warehouse'] = $datanew->status_varian == 'Y' ? $warehousedatasvar : $warehousedatas;

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
    $data1['waktu_flashsale'] = $waktu_flashsale;
    $data1['status_master_detail'] = $data->status_master_detail;
    $data1['sub_kategori'] = $datakategori->subkategori;
    $data1['kategori'] = $datakategori->kategori;

    $response->data = $data1;
    $response->sukses(200);
} else {
    $response->data = null;
    $response->error(400);
}

mysqli_close($conn);

    <?php
    require_once('../config/koneksi.php');
    include "response.php";
    $response = new Response();

    $id_login         = $_GET['id_login'];
    $tag              = $_GET['tag'];

    $exp_date = date("Y-m-d H:i:s", strtotime("+24 hours"));

    if (isset($id_login)) {

        switch ($tag) {
            case 'semua':
                $data = $conn->query("SELECT a.id_transaksi, f.id_variant, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, b.harga_barang, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail, b.jumlah_beli, a.nomor_resi, a.tanggal_exp
                FROM transaksi a
                JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
                LEFT JOIN master_item c ON b.id_barang = c.id_master 
                LEFT JOIN stok d ON c.id_master = d.id_barang 
                LEFT JOIN cabang e ON a.id_cabang = e.id_cabang
                LEFT JOIN variant f ON b.id_barang = f.id_variant
                WHERE a.id_user = '$id_login' AND a.tanggal_exp >= NOW() GROUP BY a.id_transaksi ORDER BY a.tanggal_transaksi DESC");

                //status transaksi | total produk | batas transaksi
                $status_transaksi = 'Menunggu Pembayaran';

                foreach ($data as $key) {

                    if (!empty($key['id_variant'])) {
                        $getvar = $conn->query("SELECT b.status_master_detail, a.image_varian, b.judul_master FROM variant a 
                        JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$key[id_variant]'")->fetch_object();
                        if ($getvar->status_master_detail == '2') {
                            if (substr($getvar->image_varian, 0, 4) == 'http') {
                                $imagegambar = $getvar->image_varian;
                            } else {
                                $imagegambar = $getimagebukufisik . $getvar->image_varian;
                            }
                        } else {
                            if (substr($getvar->image_varian, 0, 4) == 'http') {
                                $imagegambar = $getvar->image_varian;
                            } else {
                                $imagegambar = $getimagefisik . $getvar->image_varian;
                            }
                        }
                        $judul = $getvar->judul_master;
                        $judul_varian = $key['keterangan_varian'];
                    } else {
                        if ($key['status_master_detail'] == '2') {
                            if (substr($key['image_master'], 0, 4) == 'http') {
                                $imagegambar = $key['image_master'];
                            } else {
                                $imagegambar = $getimagebukufisik . $key['image_master'];
                            }
                        } else {
                            if (substr($key['image_master'], 0, 4) == 'http') {
                                $imagegambar = $key['image_master'];
                            } else {
                                $imagegambar = $getimagefisik . $key['image_master'];
                            }
                        }
                        $judul = $key['judul_master'];
                        $judul_varian = $key['keterangan_varian'];
                    }

                    $getjumlah_produk = $conn->query("SELECT count(id_transaksi) as jumlah_produk FROM transaksi_detail
                    WHERE id_transaksi = '$key[id_transaksi]'")->fetch_assoc();

                    if ($getjumlah_produk['jumlah_produk'] > 1) {
                        $status_lebih_satu = 'Y';
                        $keterangan_lebih_satu = $getjumlah_produk['jumlah_produk'] - 1 . ' produk lainnya';
                    } else {
                        $status_lebih_satu = 'N';
                        $keterangan_lebih_satu = '';
                    }

                    $cek_jumlah = $conn->query("SELECT sum(jumlah_beli) FROM `transaksi_detail` WHERE `id_transaksi` LIKE '$key[id_transaksi]'")->fetch_assoc();

                    $date = date_create($key['tanggal_transaksi']);
                    date_add($date,  date_interval_create_from_date_string("1 days"));
                    $exp_date = date_format($date, "Y-m-d H:i:s");

                    $ambilditempat = $key['kurir_code'] == '00' ? 'Ambil Ditempat' : '';

                    $jumlah = $cek_jumlah['jumlah_beli'];

                    $result[] = [
                        'id_transaksi' => $key['id_transaksi'],
                        'exp_date' => $exp_date,
                        'total' => rupiah($key['total_harga_setelah_diskon']),
                        'status' => $key['status_transaksi'],
                        'status_transaksi' => $status_transaksi,
                        'status_ambil_ditempat' => $ambilditempat,
                        'nama_cabang' => $key['nama_cabang'],
                        'judul_master' => $judul,
                        'jumlah_beli' => 'x ' . $key['jumlah_beli'],
                        'harga_master' => rupiah($key['harga_barang']),
                        'harga_tampil' => rupiah($key['harga_diskon']),
                        'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                        'image_master' => $imagegambar,
                        'keterangan_varian' => $judul_varian,
                        'jumlah_produk' => $jumlah,
                        'status_lebih_satu' => $status_lebih_satu,
                        'keterangan_lebih_satu' => $keterangan_lebih_satu,
                        'nomor_resi' => $key['nomor_resi']
                    ];
                }

                if ($result) {
                    $response->data = $result;
                    $response->sukses(200);
                } else {
                    $response->data = [];
                    $response->sukses(200);
                }
                break;
            case 'sebelum':
                // $cekexpired = $conn->query("SELECT * FROM transaksi WHERE id_user = '$id_loid_logingin'");
                $data = $conn->query("SELECT a.id_transaksi, f.id_variant, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, b.harga_barang, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail, b.jumlah_beli, a.nomor_resi, a.tanggal_exp
                FROM transaksi a
                JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
                LEFT JOIN master_item c ON b.id_barang = c.id_master 
                LEFT JOIN stok d ON c.id_master = d.id_barang 
                LEFT JOIN cabang e ON a.id_cabang = e.id_cabang
                LEFT JOIN variant f ON b.id_barang = f.id_variant
                WHERE a.id_user = '$id_login' AND a.status_transaksi = '1' AND a.tanggal_exp >= NOW() GROUP BY a.id_transaksi ORDER BY a.tanggal_transaksi DESC");

                //status transaksi | total produk | batas transaksi
                $status_transaksi = 'Menunggu Pembayaran';

                foreach ($data as $key) {

                    if (!empty($key['id_variant'])) {
                        $getvar = $conn->query("SELECT b.status_master_detail, a.image_varian, b.judul_master FROM variant a 
                        JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$key[id_variant]'")->fetch_object();
                        if ($getvar->status_master_detail == '2') {
                            if (substr($getvar->image_varian, 0, 4) == 'http') {
                                $imagegambar = $getvar->image_varian;
                            } else {
                                $imagegambar = $getimagebukufisik . $getvar->image_varian;
                            }
                        } else {
                            if (substr($getvar->image_varian, 0, 4) == 'http') {
                                $imagegambar = $getvar->image_varian;
                            } else {
                                $imagegambar = $getimagefisik . $getvar->image_varian;
                            }
                        }
                        $judul = $getvar->judul_master;
                        $judul_varian = $key['keterangan_varian'];
                    } else {
                        if ($key['status_master_detail'] == '2') {
                            if (substr($key['image_master'], 0, 4) == 'http') {
                                $imagegambar = $key['image_master'];
                            } else {
                                $imagegambar = $getimagebukufisik . $key['image_master'];
                            }
                        } else {
                            if (substr($key['image_master'], 0, 4) == 'http') {
                                $imagegambar = $key['image_master'];
                            } else {
                                $imagegambar = $getimagefisik . $key['image_master'];
                            }
                        }
                        $judul = $key['judul_master'];
                        $judul_varian = $key['keterangan_varian'];
                    }

                    $getjumlah_produk = $conn->query("SELECT count(id_transaksi) as jumlah_produk FROM transaksi_detail
                    WHERE id_transaksi = '$key[id_transaksi]'")->fetch_assoc();

                    if ($getjumlah_produk['jumlah_produk'] > 1) {
                        $status_lebih_satu = 'Y';
                        $keterangan_lebih_satu = $getjumlah_produk['jumlah_produk'] - 1 . ' produk lainnya';
                    } else {
                        $status_lebih_satu = 'N';
                        $keterangan_lebih_satu = '';
                    }

                    $cek_jumlah = $conn->query("SELECT sum(jumlah_beli) FROM `transaksi_detail` WHERE `id_transaksi` LIKE '$key[id_transaksi]'")->fetch_assoc();

                    $date = date_create($key['tanggal_transaksi']);
                    date_add($date,  date_interval_create_from_date_string("1 days"));
                    $exp_date = date_format($date, "Y-m-d H:i:s");

                    $ambilditempat = $key['kurir_code'] == '00' ? 'Ambil Ditempat' : '';

                    $jumlah = $cek_jumlah['jumlah_beli'];

                    $result[] = [
                        'id_transaksi' => $key['id_transaksi'],
                        'exp_date' => $exp_date,
                        'total' => rupiah($key['total_harga_setelah_diskon']),
                        'status' => $key['status_transaksi'],
                        'status_transaksi' => $status_transaksi,
                        'status_ambil_ditempat' => $ambilditempat,
                        'nama_cabang' => $key['nama_cabang'],
                        'judul_master' => $judul,
                        'jumlah_beli' => 'x ' . $key['jumlah_beli'],
                        'harga_master' => rupiah($key['harga_barang']),
                        'harga_tampil' => rupiah($key['harga_diskon']),
                        'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                        'image_master' => $imagegambar,
                        'keterangan_varian' => $judul_varian,
                        'jumlah_produk' => $jumlah,
                        'status_lebih_satu' => $status_lebih_satu,
                        'keterangan_lebih_satu' => $keterangan_lebih_satu,
                        'nomor_resi' => $key['nomor_resi']
                    ];
                }

                if ($result) {
                    $response->data = $result;
                    $response->sukses(200);
                } else {
                    $response->data = [];
                    $response->sukses(200);
                }
                break;
            case 'dikemas':
                $q = "SELECT a.id_transaksi, f.id_variant, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, b.harga_barang, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail, b.jumlah_beli, a.nomor_resi, a.tanggal_exp
                FROM transaksi a
                JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
                LEFT JOIN master_item c ON b.id_barang = c.id_master 
                LEFT JOIN stok d ON c.id_master = d.id_barang 
                LEFT JOIN cabang e ON a.id_cabang = e.id_cabang
                LEFT JOIN variant f ON b.id_barang = f.id_variant
                WHERE a.id_user = '$id_login' AND a.status_transaksi = '3' GROUP BY a.id_transaksi ORDER BY a.tanggal_dibayar DESC";
                $data = $conn->query($q);
                // $data = $conn->query("SELECT a.id_transaksi, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, c.harga_master, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail, b.jumlah_beli, a.nomor_resi, a.tanggal_exp
                // FROM transaksi a
                // LEFT JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
                // LEFT JOIN master_item c ON b.id_barang = c.id_master 
                // LEFT JOIN stok d ON c.id_master = d.id_barang 
                // LEFT JOIN cabang e ON d.id_warehouse = e.id_cabang
                // LEFT JOIN variant f ON b.id_barang = f.id_variant
                // WHERE a.id_user = '$id_login' AND a.status_transaksi = '3' GROUP BY a.id_transaksi ORDER BY a.tanggal_dibayar DESC;");

                //status transaksi | total produk | batas transaksi
                $status_transaksi = 'Dikemas';

                foreach ($data as $key) {

                    if (!empty($key['id_variant'])) {
                        $getvar = $conn->query("SELECT b.status_master_detail, a.image_varian, b.judul_master FROM variant a 
                        JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$key[id_variant]'")->fetch_object();
                        if ($getvar->status_master_detail == '2') {
                            if (substr($getvar->image_varian, 0, 4) == 'http') {
                                $imagegambar = $getvar->image_varian;
                            } else {
                                $imagegambar = $getimagebukufisik . $getvar->image_varian;
                            }
                        } else {
                            if (substr($getvar->image_varian, 0, 4) == 'http') {
                                $imagegambar = $getvar->image_varian;
                            } else {
                                $imagegambar = $getimagefisik . $getvar->image_varian;
                            }
                        }
                        $judul = $getvar->judul_master;
                    } else {
                        if ($key['status_master_detail'] == '2') {
                            if (substr($key['image_master'], 0, 4) == 'http') {
                                $imagegambar = $key['image_master'];
                            } else {
                                $imagegambar = $getimagebukufisik . $key['image_master'];
                            }
                        } else {
                            if (substr($key['image_master'], 0, 4) == 'http') {
                                $imagegambar = $key['image_master'];
                            } else {
                                $imagegambar = $getimagefisik . $key['image_master'];
                            }
                        }
                        $judul = $key['judul_master'];
                    }

                    $getjumlah_produk = $conn->query("SELECT count(id_transaksi) as jumlah_produk FROM transaksi_detail
                    WHERE id_transaksi = '$key[id_transaksi]'")->fetch_assoc();

                    if ($getjumlah_produk['jumlah_produk'] > 1) {
                        $status_lebih_satu = 'Y';
                        $keterangan_lebih_satu = $getjumlah_produk['jumlah_produk'] - 1 . ' produk lainnya';
                    } else {
                        $status_lebih_satu = 'N';
                        $keterangan_lebih_satu = '';
                    }

                    $cek_jumlah = $conn->query("SELECT sum(jumlah_beli) FROM `transaksi_detail` WHERE `id_transaksi` LIKE '$key[id_transaksi]'")->fetch_assoc();

                    $date = date_create($key['tanggal_transaksi']);
                    date_add($date,  date_interval_create_from_date_string("3 days"));
                    $exp_date = date_format($date, "Y-m-d H:i:s");

                    $ambilditempat = $key['kurir_code'] == '00' ? 'Ambil Ditempat' : '';
                    $status_ambilditempat = $key['kurir_code'] == '00' ? 'Y' : 'N';
                    if ($status_ambilditempat == 'Y') {
                        $st_packing = $value['st_packing'];
                        if ($st_packing == '1') {
                            $ketambil = 'Belum di packing';
                        } else if ($st_packing == '2') {
                            $ketambil = 'Masih proses';
                        } else if ($st_packing == '3') {
                            $ketambil = 'Siap Diambil';
                        } else {
                            $ketambil = 'Sudah Diambil';
                        }
                    } else {
                        $ketambil = '';
                    }

                    $jumlah = $cek_jumlah['jumlah_beli'];

                    $result[] = [
                        'id_transaksi' => $key['id_transaksi'],
                        'exp_date' => $exp_date,
                        'total' => rupiah($key['total_harga_setelah_diskon']),
                        'status' => $key['status_transaksi'],
                        'status_transaksi' => $status_transaksi,
                        'status_ambil_ditempat' => $ambilditempat,
                        'status_ambil_ditempat_ket' => $ketambil,
                        'nama_cabang' => $key['nama_cabang'],
                        'judul_master' => $judul,
                        'jumlah_beli' => 'x ' . $key['jumlah_beli'],
                        'harga_master' => rupiah($key['harga_barang']),
                        'harga_tampil' => rupiah($key['harga_diskon']),
                        'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                        'image_master' => $imagegambar,
                        'keterangan_varian' => $key['keterangan_varian'],
                        'jumlah_produk' => $jumlah,
                        'status_lebih_satu' => $status_lebih_satu,
                        'keterangan_lebih_satu' => $keterangan_lebih_satu,
                        'nomor_resi' => $key['nomor_resi']
                    ];
                }

                if ($result) {
                    $response->data = $result;
                    $response->sukses(200);
                } else {
                    $response->data = [];
                    $response->sukses(200);
                }
                break;
            case 'dikirim':
                $data = $conn->query("SELECT a.id_transaksi, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, c.harga_master, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail, b.jumlah_beli , a.nomor_resi
                FROM transaksi a
                JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
                JOIN master_item c ON b.id_barang = c.id_master 
                JOIN stok d ON c.id_master = d.id_barang 
                JOIN cabang e ON d.id_warehouse = e.id_cabang
                LEFT JOIN variant f ON b.id_barang = f.id_variant
                WHERE a.id_user = '$id_login' AND a.status_transaksi = '5' GROUP BY a.id_transaksi ORDER BY a.tanggal_transaksi DESC");

                //status transaksi | total produk | batas transaksi
                $status_transaksi = 'Dikirim';

                foreach ($data as $key) {

                    $getjumlah_produk = $conn->query("SELECT count(id_transaksi) as jumlah_produk FROM transaksi_detail
                    WHERE id_transaksi = '$key[id_transaksi]'")->fetch_assoc();

                    if ($getjumlah_produk['jumlah_produk'] > 1) {
                        $status_lebih_satu = 'Y';
                        $keterangan_lebih_satu = $getjumlah_produk['jumlah_produk'] - 1 . ' produk lainnya';
                    } else {
                        $status_lebih_satu = 'N';
                        $keterangan_lebih_satu = '';
                    }

                    $cek_jumlah = $conn->query("SELECT sum(jumlah_beli) FROM `transaksi_detail` WHERE `id_transaksi` LIKE '$key[id_transaksi]'")->fetch_assoc();

                    $date = date_create($key['tanggal_transaksi']);
                    date_add($date,  date_interval_create_from_date_string("3 days"));
                    $exp_date = date_format($date, "Y-m-d H:i:s");

                    $ambilditempat = $key['kurir_code'] == '00' ? 'Ambil Ditempat' : '';

                    $jumlah = $cek_jumlah['jumlah_beli'];

                    if ($key['status_master_detail'] == '2') {
                        if (substr($key['image_master'], 0, 4) == 'http') {
                            $imagegambar = $key['image_master'];
                        } else {
                            $imagegambar = $getimagebukufisik . $key['image_master'];
                        }
                    } else {
                        if (substr($key['image_master'], 0, 4) == 'http') {
                            $imagegambar = $key['image_master'];
                        } else {
                            $imagegambar = $getimagefisik . $key['image_master'];
                        }
                    }

                    $result[] = [
                        'id_transaksi' => $key['id_transaksi'],
                        'exp_date' => $exp_date,
                        'total' => rupiah($key['total_harga_setelah_diskon']),
                        'status' => $key['status_transaksi'],
                        'status_transaksi' => $status_transaksi,
                        'status_ambil_ditempat' => $ambilditempat,
                        'nama_cabang' => $key['nama_cabang'],
                        'judul_master' => $key['judul_master'],
                        'jumlah_beli' => 'x ' . $key['jumlah_beli'],
                        'harga_master' => rupiah($key['harga_master']),
                        'harga_tampil' => rupiah($key['harga_diskon']),
                        'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                        'image_master' => $imagegambar,
                        'keterangan_varian' => $key['keterangan_varian'],
                        'jumlah_produk' => $jumlah,
                        'status_lebih_satu' => $status_lebih_satu,
                        'keterangan_lebih_satu' => $keterangan_lebih_satu,
                        'nomor_resi' => $key['nomor_resi']
                    ];
                }

                if ($result) {
                    $response->data = $result;
                    $response->sukses(200);
                } else {
                    $response->data = [];
                    $response->sukses(200);
                }
                break;
            case 'selesai':
                $data = $conn->query("SELECT a.id_transaksi, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, c.harga_master, f.image_varian, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.id_variant, f.keterangan_varian, c.status_master_detail, b.jumlah_beli, a.nomor_resi, f.harga_varian, f.diskon_rupiah_varian
                FROM transaksi a
                JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
                LEFT JOIN master_item c ON b.id_barang = c.id_master 
                LEFT JOIN stok d ON c.id_master = d.id_barang 
                LEFT JOIN cabang e ON d.id_warehouse = e.id_cabang
                LEFT JOIN variant f ON b.id_barang = f.id_variant
                WHERE a.id_user = '$id_login' AND a.status_transaksi = '7' GROUP BY a.id_transaksi ORDER BY a.tanggal_transaksi DESC;");

                //status transaksi | total produk | batas transaksi
                $status_transaksi = 'Transaksi Selesai';

                foreach ($data as $key) {

                    if ($key['id_variant'] != NULL) {
                        $getstatusmaster = $conn->query("SELECT b.status_master_detail FROM variant a JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$key[id_variant]'")->fetch_assoc();

                        $getjudulmaster = $conn->query("SELECT a.id_master, b.judul_master FROM variant a LEFT JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$key[id_variant]'")->fetch_assoc();

                        $getcabang = $conn->query("SELECT * FROM stok a LEFT JOIN cabang b ON a.id_warehouse = b.id_cabang WHERE a.id_varian = '$key[id_variant]'")->fetch_assoc();

                        $judul_master = $getjudulmaster['judul_master'];
                        $variasi = $key['keterangan_varian'];
                        $master = $getjudulmaster['id_master'];
                        $cabang = $getcabang['nama_cabang'];

                        $harga = $key['harga_varian'];
                        $harga_diskon = $key['harga_diskon'];

                        if ($getstatusmaster['status_master_detail'] == '2') {
                            if (substr($key['image_varian'], 0, 4) == 'http') {
                                $image = $key['image_varian'];
                            } else {
                                $image = $getimagebukufisik . $key['image_varian'];
                            }
                        } else {
                            if (substr($key['image_varian'], 0, 4) == 'http') {
                                $image = $key['image_varian'];
                            } else {
                                $image = $getimagefisik . $key['image_varian'];
                            }
                        }
                    } else {
                        $getstatusmaster = $conn->query("SELECT status_master_detail FROM master_item WHERE id_master = '$key[id_master]'")->fetch_assoc();

                        $judul_master = $key['judul_master'];
                        $variasi = "";
                        $master = $key['id_master'];
                        $cabang = $key['nama_cabang'];

                        $harga = $key['harga_master'];
                        $harga_diskon = $key['harga_diskon'];

                        if ($key['status_master_detail'] == '2') {
                            if (substr($key['image_master'], 0, 4) == 'http') {
                                $image = $key['image_master'];
                            } else {
                                $image = $getimagebukufisik . $key['image_master'];
                            }
                        } else {
                            if (substr($key['image_master'], 0, 4) == 'http') {
                                $image = $key['image_master'];
                            } else {
                                $image = $getimagefisik . $key['image_master'];
                            }
                        }
                    }

                    $getjumlah_produk = $conn->query("SELECT count(id_transaksi) as jumlah_produk FROM transaksi_detail
                    WHERE id_transaksi = '$key[id_transaksi]'")->fetch_assoc();

                    if ($getjumlah_produk['jumlah_produk'] > 1) {
                        $status_lebih_satu = 'Y';
                        $keterangan_lebih_satu = $getjumlah_produk['jumlah_produk'] - 1 . ' produk lainnya';
                    } else {
                        $status_lebih_satu = 'N';
                        $keterangan_lebih_satu = '';
                    }

                    $cek_jumlah = $conn->query("SELECT sum(jumlah_beli) FROM `transaksi_detail` WHERE `id_transaksi` LIKE '$key[id_transaksi]'")->fetch_assoc();

                    $date = date_create($key['tanggal_transaksi']);
                    date_add($date,  date_interval_create_from_date_string("3 days"));
                    $exp_date = date_format($date, "Y-m-d H:i:s");

                    $jumlah = $cek_jumlah['jumlah_beli'];

                    $ambilditempat = $key['kurir_code'] == '00' ? 'Ambil Ditempat' : '';
                    $status_ambilditempat = $key['kurir_code'] == '00' ? 'Y' : 'N';
                    if ($status_ambilditempat == 'Y') {
                        $st_packing = $value['st_packing'];
                        if ($st_packing == '1') {
                            $ketambil = 'Belum di packing';
                        } else if ($st_packing == '2') {
                            $ketambil = 'Masih proses';
                        } else if ($st_packing == '3') {
                            $ketambil = 'Siap Diambil';
                        } else {
                            $ketambil = 'Sudah Diambil';
                        }
                    } else {
                        $ketambil = '';
                    }

                    $result[] = [
                        'id_transaksi' => $key['id_transaksi'],
                        'exp_date' => $exp_date,
                        'total' => rupiah($key['total_harga_setelah_diskon']),
                        'status' => $key['status_transaksi'],
                        'status_transaksi' => $status_transaksi,
                        'status_ambil_ditempat' => $ambilditempat,
                        'status_ambil_ditempat_ket' => $ketambil,
                        'nama_cabang' => $cabang,
                        'judul_master' => $judul_master,
                        'jumlah_beli' => 'x ' . $key['jumlah_beli'],
                        'harga_master' => rupiah($harga),
                        'harga_tampil' => rupiah($harga_diskon),
                        'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                        'image_master' => $image,
                        'keterangan_varian' => $variasi,
                        'jumlah_produk' => $jumlah,
                        'status_lebih_satu' => $status_lebih_satu,
                        'keterangan_lebih_satu' => $keterangan_lebih_satu,
                        'nomor_resi' => $key['nomor_resi']
                    ];
                }

                if ($result) {
                    $response->data = $result;
                    $response->sukses(200);
                } else {
                    $response->data = [];
                    $response->sukses(200);
                }
                break;
            case 'dibatalkan':
                $data = $conn->query("SELECT a.id_transaksi, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, c.harga_master, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail, b.jumlah_beli, a.nomor_resi
                FROM transaksi a
                JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
                JOIN master_item c ON b.id_barang = c.id_master 
                JOIN stok d ON c.id_master = d.id_barang 
                JOIN cabang e ON d.id_warehouse = e.id_cabang
                LEFT JOIN variant f ON b.id_barang = f.id_variant
                WHERE a.id_user = '$id_login' AND a.status_transaksi = '9' OR (a.status_transaksi = '1' AND (a.tanggal_transaksi >= a.tanggal_exp)) GROUP BY a.id_transaksi ORDER BY a.tanggal_transaksi DESC");

                //status transaksi | total produk | batas transaksi
                $status_transaksi = 'Transaksi Dibatalkan';

                foreach ($data as $key) {

                    $getjumlah_produk = $conn->query("SELECT count(id_transaksi) as jumlah_produk FROM transaksi_detail
                    WHERE id_transaksi = '$key[id_transaksi]'")->fetch_assoc();

                    if ($getjumlah_produk['jumlah_produk'] > 1) {
                        $status_lebih_satu = 'Y';
                        $keterangan_lebih_satu = $getjumlah_produk['jumlah_produk'] - 1 . ' produk lainnya';
                    } else {
                        $status_lebih_satu = 'N';
                        $keterangan_lebih_satu = '';
                    }

                    $cek_jumlah = $conn->query("SELECT sum(jumlah_beli) FROM `transaksi_detail` WHERE `id_transaksi` LIKE '$key[id_transaksi]'")->fetch_assoc();

                    $date = date_create($key['tanggal_transaksi']);
                    date_add($date,  date_interval_create_from_date_string("3 days"));
                    $exp_date = date_format($date, "Y-m-d H:i:s");

                    $ambilditempat = $key['kurir_code'] == '00' ? 'Ambil Ditempat' : '';

                    $jumlah = $cek_jumlah['jumlah_beli'];

                    if ($key['status_master_detail'] == '2') {
                        if (substr($key['image_master'], 0, 4) == 'http') {
                            $imagegambar = $key['image_master'];
                        } else {
                            $imagegambar = $getimagebukufisik . $key['image_master'];
                        }
                    } else {
                        if (substr($key['image_master'], 0, 4) == 'http') {
                            $imagegambar = $key['image_master'];
                        } else {
                            $imagegambar = $getimagefisik . $key['image_master'];
                        }
                    }

                    $result[] = [
                        'id_transaksi' => $key['id_transaksi'],
                        'exp_date' => $exp_date,
                        'total' => rupiah($key['total_harga_setelah_diskon']),
                        'status' => '9',
                        'status_transaksi' => $status_transaksi,
                        'status_ambil_ditempat' => $ambilditempat,
                        'nama_cabang' => $key['nama_cabang'],
                        'judul_master' => $key['judul_master'],
                        'jumlah_beli' => 'x ' . $key['jumlah_beli'],
                        'harga_master' => rupiah($key['harga_master']),
                        'harga_tampil' => rupiah($key['harga_diskon']),
                        'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                        'image_master' => $imagegambar,
                        'keterangan_varian' => $key['keterangan_varian'],
                        'jumlah_produk' => $jumlah,
                        'status_lebih_satu' => $status_lebih_satu,
                        'keterangan_lebih_satu' => $keterangan_lebih_satu,
                        'nomor_resi' => $key['nomor_resi']
                    ];
                }

                if ($result) {
                    $response->data = $result;
                    $response->sukses(200);
                } else {
                    $response->data = [];
                    $response->sukses(200);
                }
                break;
            case 'dikembalikan':
                $data = $conn->query("SELECT a.id_transaksi, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, c.harga_master, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail, b.jumlah_beli, a.nomor_resi
                FROM transaksi a
                JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
                JOIN master_item c ON b.id_barang = c.id_master 
                JOIN stok d ON c.id_master = d.id_barang 
                JOIN cabang e ON d.id_warehouse = e.id_cabang
                LEFT JOIN variant f ON b.id_barang = f.id_variant
                WHERE a.id_user = '$id_login' AND a.status_transaksi = '11' GROUP BY a.id_transaksi ORDER BY a.tanggal_transaksi DESC;");

                //status transaksi | total produk | batas transaksi
                $status_transaksi = 'Pengembalian';

                foreach ($data as $key) {

                    $getjumlah_produk = $conn->query("SELECT count(id_transaksi) as jumlah_produk FROM transaksi_detail
                    WHERE id_transaksi = '$key[id_transaksi]'")->fetch_assoc();

                    if ($getjumlah_produk['jumlah_produk'] > 1) {
                        $status_lebih_satu = 'Y';
                        $keterangan_lebih_satu = $getjumlah_produk['jumlah_produk'] - 1 . ' produk lainnya';
                    } else {
                        $status_lebih_satu = 'N';
                        $keterangan_lebih_satu = '';
                    }

                    $cek_jumlah = $conn->query("SELECT sum(jumlah_beli) FROM `transaksi_detail` WHERE `id_transaksi` LIKE '$key[id_transaksi]'")->fetch_assoc();

                    $date = date_create($key['tanggal_transaksi']);
                    date_add($date,  date_interval_create_from_date_string("3 days"));
                    $exp_date = date_format($date, "Y-m-d H:i:s");

                    $ambilditempat = $key['kurir_code'] == '00' ? 'Ambil Ditempat' : '';

                    $jumlah = $cek_jumlah['jumlah_beli'];

                    if ($key['status_master_detail'] == '2') {
                        if (substr($key['image_master'], 0, 4) == 'http') {
                            $imagegambar = $key['image_master'];
                        } else {
                            $imagegambar = $getimagebukufisik . $key['image_master'];
                        }
                    } else {
                        if (substr($key['image_master'], 0, 4) == 'http') {
                            $imagegambar = $key['image_master'];
                        } else {
                            $imagegambar = $getimagefisik . $key['image_master'];
                        }
                    }

                    $result[] = [
                        'id_transaksi' => $key['id_transaksi'],
                        'exp_date' => $exp_date,
                        'total' => rupiah($key['total_harga_setelah_diskon']),
                        'status' => $key['status_transaksi'],
                        'status_transaksi' => $status_transaksi,
                        'status_ambil_ditempat' => $ambilditempat,
                        'nama_cabang' => $key['nama_cabang'],
                        'judul_master' => $key['judul_master'],
                        'jumlah_beli' => 'x ' . $key['jumlah_beli'],
                        'harga_master' => rupiah($key['harga_master']),
                        'harga_tampil' => rupiah($key['harga_diskon']),
                        'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                        'image_master' => $imagegambar,
                        'keterangan_varian' => $key['keterangan_varian'],
                        'jumlah_produk' => $jumlah,
                        'status_lebih_satu' => $status_lebih_satu,
                        'keterangan_lebih_satu' => $keterangan_lebih_satu,
                        'nomor_resi' => $key['nomor_resi']
                    ];
                }

                if ($result) {
                    $response->data = $result;
                    $response->sukses(200);
                } else {
                    $response->data = [];
                    $response->sukses(200);
                }
                break;
            case 'detail':
                $id_transaksi         = $_GET['id_transaksi'];

                $getproduk = $conn->query("SELECT c.id_master, b.total_harga_sebelum_diskon, b.harga_ongkir, b.total_harga_setelah_diskon, b.voucher_harga, b.voucher_ongkir, c.judul_master, c.image_master, a.jumlah_beli, a.harga_barang, a.diskon_barang, a.harga_diskon, b.invoice, d.id_variant, d.keterangan_varian, d.diskon_rupiah_varian, d.image_varian, b.status_transaksi, b.kurir_pengirim, b.kurir_code, b.kurir_service, b.metode_pembayaran, b.midtrans_transaction_status, b.midtrans_payment_type, b.midtrans_token, b.midtrans_redirect_url, b.alamat_penerima, b.nama_penerima, b.label_alamat, b.telepon_penerima, b.tanggal_transaksi, b.tanggal_dibayar, b.tanggal_diterima, b.tgl_packing, b.nomor_resi, c.status_master_detail, b.st_packing, DATE_ADD(b.tanggal_diterima, INTERVAL 7 DAY) as tanggal_diterima, b.catatan_pembeli, b.biaya_platform
                        FROM transaksi_detail a 
                        JOIN transaksi b ON a.id_transaksi = b.id_transaksi
                        LEFT JOIN master_item c ON a.id_barang = c.id_master
                        LEFT JOIN variant d ON a.id_barang = d.id_variant WHERE a.id_transaksi = '$id_transaksi'");

                $getjumlahsubtotal = $conn->query("SELECT SUM(jumlah_beli * harga_diskon) as getjumlahsubtotal, SUM(diskon_barang * jumlah_beli) as subdiskon_barang FROM transaksi_detail WHERE id_transaksi = '$id_transaksi'")->fetch_object();

                foreach ($getproduk as $key => $value) {

                    $jumlah_subtotal = $value['jumlah_beli'] * $value['harga_diskon'];
                    if ($value['id_variant'] != NULL) {
                        $getstatusmaster = $conn->query("SELECT b.status_master_detail FROM variant a JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$value[id_variant]'")->fetch_assoc();

                        $getjudulmaster = $conn->query("SELECT a.id_master, b.judul_master FROM variant a LEFT JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$value[id_variant]'")->fetch_assoc();

                        $judul_master = $getjudulmaster['judul_master'];
                        $variasi = $value['keterangan_varian'];
                        $master = $getjudulmaster['id_master'];

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
                        $master = $value['id_master'];

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
                        "id_master" => $master,
                        "judul_master" => $judul_master,
                        "variasi" => $variasi,
                        "image_master" => $image,
                        "status_diskon" => $value['diskon_barang'] != 0 ? 'Y' : 'N',
                        "jumlah_beli" => $value['jumlah_beli'],
                        "harga_produk" => $value['harga_barang'],
                        "harga_tampil" => $value['diskon_barang'] != 0 ? $value['harga_diskon'] : $value['harga_barang']
                    ];
                }

                $informasi_pengiriman =
                    [
                        'kurir_pengirim' => $value['kurir_pengirim'],
                        'kurir_code' => $value['kurir_code'],
                        'kurir_service' => $value['kurir_service'],
                        'nomor_resi' => $value['nomor_resi'],
                        'detail_pengiriman' => '',
                        'waktu_pengiriman' => ''
                    ];
                $getdatatotal =
                    [
                        'subtotal_produk' => (int)$getjumlahsubtotal->getjumlahsubtotal,
                        'subtotal_pengiriman' => (int)$value['harga_ongkir'],
                        'subtotal_diskon_barang' => (int)$value['voucher_harga'],
                        'subtotal_diskon_ongkir' => (int)$value['voucher_ongkir'],
                        'subtotal_ppn' => 0,
                        '' => 0,
                        'biaya_platform' => $value['biaya_platform'] != 0 ? (int)$value['biaya_platform'] : 0,
                        'subtotal' => (int)($value['total_harga_setelah_diskon']),
                    ];

                //? ADDRESS
                $gabung_alamat = $value['nama_penerima'] . " | " . $value['telepon_penerima'] . " " . $value['alamat_penerima'];
                $address =
                    [
                        'id_address' => "",
                        'address' => $gabung_alamat,
                    ];

                //?Data transaction
                $data = $conn->query("SELECT * FROM `transaksi` WHERE id_user = '$id_login' AND id_transaksi = '$id_transaksi'")->fetch_object();

                $date = date_create($data->tanggal_transaksi);
                date_add($date,  date_interval_create_from_date_string("1 days"));
                $exp_date = date_format($date, "Y-m-d H:i:s");

                //! Status Transaksi
                $status_transaksi = $value['status_transaksi'];
                if ($status_transaksi == '1') {
                    if ($exp_date <= date('Y-m-d H:i:s')) {
                        $status_transaksi = '9';
                        $status = 'Dibatalkan';
                    } else {
                        $status_transaksi = '1';
                        $status = 'Menunggu Pembayaran';
                    }
                } else if ($status_transaksi == '2') {
                    $status = 'Menunggu Verifikasi Pembayaran';
                } else if ($status_transaksi == '3') {
                    $status = 'Pembayaran Berhasil';
                } else if ($status_transaksi == '4') {
                    $status = 'Pembayaran Tidak Lengkap';
                } else if ($status_transaksi == '5') {
                    $status = 'Dikirim';
                } else if ($status_transaksi == '6') {
                    $status = 'Diterima';
                } else if ($status_transaksi == '7') {
                    $status = 'Transaksi Selesai';
                } else if ($status_transaksi == '8') {
                    $status = 'Expired';
                } else if ($status_transaksi == '9') {
                    $status = 'Dibatalkan';
                } else if ($status_transaksi == '10') {
                    $status = 'Pembayaran Ditolak';
                } else if ($status_transaksi == '11') {
                    $status = 'PengembalianBarang';
                } else {
                    $status = 'Expired';
                }

                $ambilditempat = $value['kurir_code'];
                $status_ambilditempat = $ambilditempat == '00' ? 'Y' : 'N';
                if ($status_ambilditempat == 'Y') {
                    $st_packing = $value['st_packing'];
                    if ($st_packing == '1') {
                        $ketambil = 'Belum di packing';
                    } else if ($st_packing == '2') {
                        $ketambil = 'Masih proses';
                    } else if ($st_packing == '3') {
                        $status_notifcod = 'Pesananmu siap diambil
                                Jam operasional toko 10.00 WIB - 17.00 WIB';
                        $ketambil = 'Siap Diambil';
                    } else {
                        $ketambil = 'Sudah Diambil';
                    }
                } else {
                    $ketambil = '';
                }

                switch ($status_transaksi) {
                    case '1':
                        $tanggal_waktu_transaksi = $value['tanggal_transaksi'];
                        $tanggal_waktu_dibayar = "0000-00-00 00:00:00";
                        $tanggal_waktu_pengiriman = "0000-00-00 00:00:00";
                        $tanggal_waktu_pembatalan = "0000-00-00 00:00:00";
                        $tanggal_waktu_selesai = "0000-00-00 00:00:00";
                        $tanggal_waktu_pengembalian = "0000-00-00 00:00:00";
                        break;
                    case '3':
                        $tanggal_waktu_transaksi = $value['tanggal_transaksi'];
                        $tanggal_waktu_dibayar = $value['tanggal_dibayar'];
                        $tanggal_waktu_pengiriman = "0000-00-00 00:00:00";
                        $tanggal_waktu_pembatalan = "0000-00-00 00:00:00";
                        $tanggal_waktu_selesai = "0000-00-00 00:00:00";
                        $tanggal_waktu_pengembalian = "0000-00-00 00:00:00";
                        break;
                    case '5':
                        $tanggal_waktu_transaksi = $value['tanggal_transaksi'];
                        $tanggal_waktu_dibayar = $value['tanggal_dibayar'];
                        $tanggal_waktu_pengiriman = $value['tgl_packing'];
                        $tanggal_waktu_pembatalan = "0000-00-00 00:00:00";
                        $tanggal_waktu_selesai = "0000-00-00 00:00:00";
                        $tanggal_waktu_pengembalian = "0000-00-00 00:00:00";
                        break;
                    case '7':
                        $tanggal_waktu_transaksi = $value['tanggal_transaksi'];
                        $tanggal_waktu_dibayar = $value['tanggal_dibayar'];
                        $tanggal_waktu_pengiriman = $value['tgl_packing'];
                        $tanggal_waktu_pembatalan = "0000-00-00 00:00:00";
                        $tanggal_waktu_selesai = $value['tanggal_diterima'];
                        $tanggal_waktu_pengembalian = "0000-00-00 00:00:00";
                        break;
                    case '9':
                        $tanggal_waktu_transaksi = $value['tanggal_transaksi'];
                        $tanggal_waktu_dibayar = $value['tanggal_dibayar'];
                        $tanggal_waktu_pengiriman = $value['tgl_packing'];
                        $tanggal_waktu_pembatalan = "0000-00-00 00:00:00";
                        $tanggal_waktu_selesai = "0000-00-00 00:00:00";
                        $tanggal_waktu_pengembalian = "0000-00-00 00:00:00";
                        break;
                    case '11':
                        $tanggal_waktu_transaksi = $value['tanggal_transaksi'];
                        $tanggal_waktu_dibayar = $value['tanggal_dibayar'];
                        $tanggal_waktu_pengiriman = $value['tgl_packing'];
                        $tanggal_waktu_pembatalan = "0000-00-00 00:00:00";
                        $tanggal_waktu_selesai = "0000-00-00 00:00:00";
                        $tanggal_waktu_pengembalian = "0000-00-00 00:00:00";
                        break;
                    default:
                        break;
                }

                $informasi_pesanan = [
                    'no_invoice' => $value['invoice'],
                    'tanggal_transaksi' => $tanggal_waktu_transaksi,
                    'tanggal_dibayar' => $tanggal_waktu_dibayar,
                    'tanggal_pengiriman' => $tanggal_waktu_pengiriman,
                    'tanggal_pembatalan' => $tanggal_waktu_pembatalan,
                    'tanggal_selesai' => $tanggal_waktu_selesai,
                    'tanggal_pengembalian' => $tanggal_waktu_pengembalian
                ];

                $getdatatransaction =
                    [
                        'id_transaksi' => $id_transaksi,
                        'status_transaksi' => $status_transaksi,
                        'ket_status_transaksi' => $status,
                        'ambil_ditempat' => $value['kurir_code'] == '00' ? 'Y' : 'N',
                        'ket_ambil_ditempat' => $ketambil
                    ];

                switch ($status_ambilditempat) {
                    case 'Y':
                        switch ($status_transaksi) {
                            case '1':
                                $status_notif = 'Menunggu Pembayaran';
                                $keterangan = 'Silahkan melakukan pembayaran paling lambat ' . $exp_date;
                                break;
                            case '3':
                                $ambilditempat = $value['kurir_code'];
                                $status_ambilditempat = $ambilditempat == '00' ? 'Y' : 'N';
                                if ($status_ambilditempat == 'Y') {
                                    $st_packing = $value['st_packing'];
                                    if ($st_packing == '1') {
                                        $ketambil = 'Belum di packing';
                                    } else if ($st_packing == '2') {
                                        $ketambil = 'Masih proses';
                                    } else if ($st_packing == '3') {
                                        $status_notifcod = 'Pesananmu siap diambil Jam operasional toko 10.00 WIB - 17.00 WIB';
                                        $ketambil = 'Siap Diambil';
                                    } else {
                                        $ketambil = 'Sudah Diambil';
                                    }
                                } else {
                                    $ketambil = '';
                                }
                                $status_notif = $status_notifcod;
                                $keterangan = '';
                                break;
                            case '5':
                                $status_notif = 'Pesananmu siap diambil
                                Jam operasional toko 10.00 WIB - 17.00 WIB';
                                $keterangan = '';
                                break;
                            case '7':
                                $status_notif = 'Pesanan selesai';
                                $keterangan = 'Pesananmu sudah diambil, silahkan beri penilaian terhadap produk ini';
                                break;
                            case '9':
                                $status_notif = 'Pesanan dibatalkan';
                                $keterangan = 'Kamu telah membatalkan pesanan ini. Cek rincian pembatalan untuk informasi lebih lanjut.';
                                break;
                            default:
                                $status_notif = '';
                                $keterangan = '';
                                break;
                        }
                        break;

                    default:
                        switch ($status_transaksi) {
                            case '1':
                                $status_notif = 'Menunggu Pembayaran';
                                $keterangan = 'Silahkan melakukan pembayaran paling lambat ' . $exp_date;
                                break;
                            case '3':
                                $status_notif = 'Pesananmu sedang dikemas oleh penjual';
                                $keterangan = 'Penjual harus mengatur pengiriman pesananmu';
                                break;
                            case '5':
                                $status_notif = 'Pesananmu sedang dalam perjalanan';
                                $keterangan = 'Produk pesananmu sedang diantarkan oleh kurir';
                                break;
                            case '7':
                                $status_notif = 'Pesanan selesai';
                                $keterangan = 'Nilai pesanan sebelum ' . $value['tanggal_diterima'];
                                break;
                            case '9':
                                $status_notif = 'Pesanan dibatalkan';
                                $keterangan = 'Kamu telah membatalkan pesanan ini. Cek rincian pembatalan untuk informasi lebih lanjut.';
                                break;
                            default:
                                $status_notif = '';
                                $keterangan = '';
                                break;
                        }
                        break;
                }

                $notif = [
                    'status' => $status_notif,
                    'keterangan' => $keterangan,
                ];

                $catatan = [
                    'catatan' => $value['catatan_pembeli'],
                ];

                //! Metode Pembayaran
                if ($value['metode_pembayaran'] == '0') {
                    $metode_pembayaran = 'Pembayaran Otomatis Midtrans';
                    $status_metode_pembayaran = '0';
                } else if ($value['metode_pembayaran'] == '1') {
                    $metode_pembayaran = 'Bank BCA (cek manual)';
                    $status_metode_pembayaran = '1';
                } else if ($value['metode_pembayaran'] == '2') {
                    $metode_pembayaran = 'Bank Mandiri (cek mandiri)';
                    $status_metode_pembayaran = '1';
                } else if ($value['metode_pembayaran'] == '3') {
                    $metode_pembayaran = 'E-money (cek mandiri)';
                    $status_metode_pembayaran = '1';
                }
                $metodepem =
                    [
                        'status_metode_pembayaran' => $status_metode_pembayaran,
                        'metode_pembayaran' => $metode_pembayaran,
                        'midtrans_transaction_status' => $value['midtrans_transaction_status'],
                        'midtrans_payment_type' => $value['midtrans_payment_type'],
                        'midtrans_token' => $value['midtrans_token'],
                        'midtrans_redirect_url' => $value['midtrans_redirect_url']
                    ];

                $data1['data_transaction'] = $getdatatransaction;
                $data1['data_address_buyer'] = $address;
                $data1['data_product'] = $getprodukcoba;
                $data1['data_price'] = $getdatatotal;
                $data1['data_payment'] = $metodepem;
                $data1['data_order'] = $informasi_pesanan;
                $data1['data_shipment'] = $informasi_pengiriman;
                $data1['data_faktur'] = '';
                $data1['notifikasi'] = $notif;
                $data1['catatan'] = $catatan;

                if ($data1) {
                    $response->data = $data1;
                    $response->sukses(200);
                } else {
                    $response->data = [];
                    $response->sukses(200);
                }
                break;
                die();
        }
    } else {
        $response->data = null;
        $response->error(400);
    }
    die();
    mysqli_close($conn);

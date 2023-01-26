<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_login         = $_GET['id_login'];
$tag              = $_GET['tag'];

// $exp_date = date("Y-m-d H:i:s", strtotime("+24 hours"));
date_default_timezone_set('Asia/Jakarta');

if (isset($id_login)) {

    switch ($tag) {
        case 'semua':
            $data = $conn->query("SELECT id_transaksi, invoice, tanggal_transaksi, total_harga_setelah_diskon, harga_ongkir, status_transaksi, kurir_code FROM `transaksi` WHERE id_user = '$id_login' ORDER BY tanggal_transaksi DESC");

            foreach ($data as $key) {

                $date = date_create($key['tanggal_transaksi']);
                date_add($date,  date_interval_create_from_date_string("1 days"));
                $exp_date = date_format($date, "Y-m-d H:i:s");

                // $start_date = $key['tanggal_transaksi'];
                // $exp_date = date($key['tanggal_transaksi'], strtotime("+24 hours"));
                // var_dump($start_date);
                // var_dump($exp_date);
                // die;

                if ($key['status_transaksi'] == '1') {
                    if ($exp_date <= date('Y-m-d H:i:s')) {
                        $status = '9';
                        $status_transaksi = 'Dibatalkan';
                    } else {
                        $status = '1';
                        $status_transaksi = 'Menunggu Pembayaran';
                    }
                } else if ($key['status_transaksi'] == '2') {
                    $status = '2';
                    $status_transaksi = 'Menunggu Verifikasi Pembayaran';
                } else if ($key['status_transaksi'] == '3') {
                    $status = '3';
                    $status_transaksi = 'Pembayaran Berhasil';
                } else if ($key['status_transaksi'] == '4') {
                    $status = '4';
                    $status_transaksi = 'Pembayaran Tidak Lengkap';
                } else if ($key['status_transaksi'] == '5') {
                    $status = '5';
                    $status_transaksi = 'Dikirim';
                } else if ($key['status_transaksi'] == '6') {
                    $status = '6';
                    $status_transaksi = 'Diterima';
                } else if ($key['status_transaksi'] == '7') {
                    $status = '7';
                    $status_transaksi = 'Transaksi Selesai';
                } else if ($key['status_transaksi'] == '8') {
                    $status = '8';
                    $status_transaksi = 'Expired';
                } else if ($key['status_transaksi'] == '9') {
                    $status = '9';
                    $status_transaksi = 'Dibatalkan';
                } else if ($key['status_transaksi'] == '10') {
                    $status = '10';
                    $status_transaksi = 'Pembayaran Ditolak';
                } else if ($key['status_transaksi'] == '11') {
                    $status = '11';
                    $status_transaksi = 'PengembalianBarang';
                } else {
                    $status = '12';
                    $status_transaksi = 'Expired';
                }

                $ambilditempat = $key['kurir_code'] == '00' ? 'Ambil Ditempat' : '';

                $result[] = [
                    'id_transaksi' => $key['id_transaksi'],
                    'invoice' => $key['invoice'],
                    'exp_date' => $exp_date,
                    'total' => $key['total_harga_setelah_diskon'],
                    'total_format' => rupiah($key['total_harga_setelah_diskon']),
                    'status' => $status,
                    'status_transaksi' => $status_transaksi,
                    'status_ambil_ditempat' => $ambilditempat
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
            $data = $conn->query("SELECT id_transaksi, invoice, tanggal_transaksi, total_harga_setelah_diskon, status_transaksi, kurir_code FROM `transaksi` WHERE id_user = '$id_login' AND (status_transaksi = '3' OR status_transaksi = '7') ORDER BY tanggal_transaksi DESC");

            foreach ($data as $key) {

                $date = date_create($key['tanggal_transaksi']);
                date_add($date,  date_interval_create_from_date_string("3 days"));
                $exp_date = date_format($date, "Y-m-d H:i:s");

                if ($key['status_transaksi'] == '1') {
                    $status_transaksi = 'Menunggu Pembayaran';
                } else if ($key['status_transaksi'] == '2') {
                    $status_transaksi = 'Menunggu Verifikasi Pembayaran';
                } else if ($key['status_transaksi'] == '3') {
                    $status_transaksi = 'Pembayaran Berhasil';
                } else if ($key['status_transaksi'] == '4') {
                    $status_transaksi = 'Pembayaran Tidak Lengkap';
                } else if ($key['status_transaksi'] == '5') {
                    $status_transaksi = 'Dikirim';
                } else if ($key['status_transaksi'] == '6') {
                    $status_transaksi = 'Diterima';
                } else if ($key['status_transaksi'] == '7') {
                    $status_transaksi = 'Transaksi Selesai';
                } else if ($key['status_transaksi'] == '8') {
                    $status_transaksi = 'Expired';
                } else if ($key['status_transaksi'] == '9') {
                    $status_transaksi = 'Dibatalkan';
                } else if ($key['status_transaksi'] == '10') {
                    $status_transaksi = 'Pembayaran Ditolak';
                } else if ($key['status_transaksi'] == '11') {
                    $status_transaksi = 'PengembalianBarang';
                } else {
                    $status_transaksi = 'Expired';
                }

                $ambilditempat = $key['kurir_code'] == '00' ? 'Ambil Ditempat' : '';

                $result[] = [
                    'id_transaksi' => $key['id_transaksi'],
                    'invoice' => $key['invoice'],
                    'exp_date' => $exp_date,
                    'total' => $key['total_harga_setelah_diskon'],
                    'total_format' => rupiah($key['total_harga_setelah_diskon']),
                    'status' => $key['status_transaksi'],
                    'status_transaksi' => $status_transaksi,
                    'status_ambil_ditempat' => $ambilditempat
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
        case 'batal':
            $data = $conn->query("SELECT id_transaksi, invoice, tanggal_transaksi, total_harga_setelah_diskon, status_transaksi, kurir_code FROM `transaksi` WHERE id_user = '$id_login' AND status_transaksi = '9' ORDER BY tanggal_transaksi DESC");

            foreach ($data as $key) {

                $date = date_create($key['tanggal_transaksi']);
                date_add($date,  date_interval_create_from_date_string("3 days"));
                $exp_date = date_format($date, "Y-m-d H:i:s");

                if ($key['status_transaksi'] == '1') {
                    $status_transaksi = 'Menunggu Pembayaran';
                } else if ($key['status_transaksi'] == '2') {
                    $status_transaksi = 'Menunggu Verifikasi Pembayaran';
                } else if ($key['status_transaksi'] == '3') {
                    $status_transaksi = 'Pembayaran Berhasil';
                } else if ($key['status_transaksi'] == '4') {
                    $status_transaksi = 'Pembayaran Tidak Lengkap';
                } else if ($key['status_transaksi'] == '5') {
                    $status_transaksi = 'Dikirim';
                } else if ($key['status_transaksi'] == '6') {
                    $status_transaksi = 'Diterima';
                } else if ($key['status_transaksi'] == '7') {
                    $status_transaksi = 'Transaksi Selesai';
                } else if ($key['status_transaksi'] == '8') {
                    $status_transaksi = 'Expired';
                } else if ($key['status_transaksi'] == '9') {
                    $status_transaksi = 'Dibatalkan';
                } else if ($key['status_transaksi'] == '10') {
                    $status_transaksi = 'Pembayaran Ditolak';
                } else if ($key['status_transaksi'] == '11') {
                    $status_transaksi = 'PengembalianBarang';
                } else {
                    $status_transaksi = 'Expired';
                }

                $ambilditempat = $key['kurir_code'] == '00' ? 'Ambil Ditempat' : '';

                $result[] = [
                    'id_transaksi' => $key['id_transaksi'],
                    'invoice' => $key['invoice'],
                    'exp_date' => $exp_date,
                    'total' => $key['total_harga_setelah_diskon'],
                    'total_format' => rupiah($key['total_harga_setelah_diskon']),
                    'status' => $key['status_transaksi'],
                    'status_transaksi' => $status_transaksi,
                    'status_ambil_ditempat' => $ambilditempat
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
            $data = $conn->query("SELECT * FROM `transaksi` WHERE id_user = '$id_login' AND id_transaksi = '$id_transaksi'")->fetch_object();

            $status_transaksi = $data->status_transaksi;
            $kurir_code = $data->kurir_code;

            if ($status_transaksi == '1') {
                $status = 'Menunggu Pembayaran';
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

            if ($kurir_code == '00') {
                $status_kurir = $data->kurir_pengirim;
                $ambil_ditempat = $data->ambil_ditempat;

                if ($ambil_ditempat == '1') {
                    $ambil_ditempat_ket = 'Belum Dipacking';
                } else if ($ambil_ditempat == '2') {
                    $ambil_ditempat_ket = 'Masih Diproses';
                } else if ($ambil_ditempat == '3') {
                    $ambil_ditempat_ket = 'Siap Diambil';
                } else if ($ambil_ditempat == '4') {
                    $ambil_ditempat_ket = 'Sudah Diambil';
                } else {
                    $ambil_ditempat_ket = 'Batal';
                }
            } else {
                $status_kurir = $data->kurir_pengirim;
                $ambil_ditempat = "";
            }

            if ($data->metode_pembayaran == '0') {
                $metode_pembayaran = 'Pembayaran Otomatis Midtrans';
                $status_metode_pembayaran = '0';
            } else if ($data->metode_pembayaran == '1') {
                $metode_pembayaran = 'Bank BCA (cek manual)';
                $status_metode_pembayaran = '1';
            } else if ($data->metode_pembayaran == '2') {
                $metode_pembayaran = 'Bank Mandiri (cek mandiri)';
                $status_metode_pembayaran = '1';
            } else if ($data->metode_pembayaran == '3') {
                $metode_pembayaran = 'E-money (cek mandiri)';
                $status_metode_pembayaran = '1';
            }

            $product = $conn->query("SELECT * FROM transaksi_detail td 
            LEFT JOIN master_item mi ON td.id_barang = mi.id_master 
            LEFT JOIN variant v ON td.id_barang = v.id_variant WHERE td.id_transaksi = '$id_transaksi'")->fetch_object();

            if (empty($product->judul_master)) {
                $nama_produk = $conn->query("SELECT * FROM master_item WHERE id_master = '$product->id_master'")->fetch_object();
                $produk_nama = $nama_produk->judul_master;
            } else {
                $produk_nama = $product->judul_master;
            }



            $data1['id_transaksi'] = $data->id_transaksi;
            $data1['invoice'] = $data->invoice;
            $data1['total_harga'] = $data->total_harga_setelah_diskon;
            $data1['metode_pembayaran'] = $metode_pembayaran;
            $data1['status_metode_pembayaran'] = $status_metode_pembayaran;
            $data1['status_transaksi'] = $status_transaksi;
            $data1['status_transaksi_ket'] = $status;
            $data1['tanggal_transaksi'] = $data->tanggal_transaksi;
            $data1['ambil_ditempat'] = $ambil_ditempat;
            $data1['ambil_ditempat_ket'] = $ambil_ditempat_ket;
            $data1['nama_produk'] = $produk_nama;
            $data1['midtrans_payment_type'] = $data->midtrans_payment_type;
            $data1['midtrans_transaction_status'] = $data->midtrans_transaction_status;
            $data1['midtrans_token'] = $data->midtrans_token;
            $data1['midtrans_redirect_url'] = $data->midtrans_redirect_url;

            if ($data1) {
                $response->data = $data1;
                $response->sukses(200);
            } else {
                $response->data = [];
                $response->sukses(200);
            }
            break;
        case 'detail_product':
            $id_transaksi         = $_GET['id_transaksi'];

            if ($id_transaksi) {
                $cektransaksi = $conn->query("SELECT * FROM transaksi WHERE id_transaksi = '$id_transaksi'")->num_rows;

                if ($cektransaksi > 0) {
                    $data = $conn->query("SELECT * FROM `transaksi` WHERE id_user = '$id_login' AND id_transaksi = '$id_transaksi'")->fetch_object();

                    $status_transaksi = $data->status_transaksi;
                    $kurir_code = $data->kurir_code;

                    if ($status_transaksi == '1') {
                        $status = 'Menunggu Pembayaran';
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

                    if ($kurir_code == '00') {
                        $status_kurir = $data->kurir_pengirim;
                        $ambil_ditempat = $data->ambil_ditempat;

                        if ($ambil_ditempat == '1') {
                            $ambil_ditempat_ket = 'Belum Dipacking';
                        } else if ($ambil_ditempat == '2') {
                            $ambil_ditempat_ket = 'Masih Diproses';
                        } else if ($ambil_ditempat == '3') {
                            $ambil_ditempat_ket = 'Siap Diambil';
                        } else if ($ambil_ditempat == '4') {
                            $ambil_ditempat_ket = 'Sudah Diambil';
                        } else {
                            $ambil_ditempat_ket = 'Batal';
                        }
                    } else {
                        $status_kurir = $data->kurir_pengirim;
                        $ambil_ditempat = "";
                    }

                    if ($data->metode_pembayaran == '1') {
                        $metode_pembayaran = 'Pembayaran Manual';
                    } else if ($data->metode_pembayaran == '2') {
                        $metode_pembayaran = 'Pembayaran Otomatis Midtrans';
                    } else {
                        $metode_pembayaran = 'Belum Memilih Metode Pembayaran';
                    }

                    //? ADDRESS
                    $query_alamat = "SELECT * FROM user_alamat WHERE status_alamat_utama = 'Y' AND id_user = '$dataraw[id_user]'";
                    $getalamat = $conn->query($query_alamat);
                    $data_alamat = $getalamat->fetch_object();
                    $gabung_alamat = $data_alamat->nama_penerima . " | " . $data_alamat->telepon_penerima . " " . $data_alamat->alamat
                        . "," . $data_alamat->kelurahan . "," . $data_alamat->kecamatan . "," . $data_alamat->kota . "," . $data_alamat->provinsi . "," . $data_alamat->kodepos;
                    $address =
                        [
                            'id_address' => $data_alamat->id,
                            'address' => $gabung_alamat,
                        ];

                    $getproduk = $conn->query("SELECT b.total_harga_sebelum_diskon, b.harga_ongkir, b.total_harga_setelah_diskon, b.voucher_harga, c.judul_master, c.image_master, a.jumlah_beli, a.harga_barang, a.diskon_barang, a.harga_diskon, b.invoice, d.id_variant, d.keterangan_varian, d.diskon_rupiah_varian, d.image_varian, b.status_transaksi, b.kurir_pengirim, b.kurir_code, b.kurir_service, b.metode_pembayaran, b.ambil_ditempat, b.midtrans_transaction_status, b.midtrans_payment_type, b.midtrans_token, b.midtrans_redirect_url FROM transaksi_detail a 
                JOIN transaksi b ON a.id_transaksi = b.id_transaksi
                LEFT JOIN master_item c ON a.id_barang = c.id_master
                LEFT JOIN variant d ON a.id_barang = d.id_variant WHERE a.id_transaksi = '$id_transaksi';");

                    foreach ($getproduk as $key => $value) {

                        $getstatusmaster = $conn->query("SELECT b.status_master_detail FROM variant a JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$value[id_variant]'")->fetch_assoc();

                        if ($value['id_variant'] != NULL) {
                            $judul_master = $value['keterangan_varian'];

                            if ($getstatusmaster['status_master_detail'] == '2') {
                                $image = $getimagebukufisik . $value['image_varian'];
                            } else {
                                $image = $getimagefisik . $value['image_varian'];
                            }
                        } else {
                            $judul_master = $value['judul_master'];
                            if ($getstatusmaster['status_master_detail'] == '2') {
                                $image = $getimagebukufisik . $value['image_master'];
                            } else {
                                $image = $getimagefisik . $value['image_master'];
                            }
                        }

                        $getprodukcoba[] = [
                            'id_transaksi' => $id_transaksi,
                            'judul_master' => $judul_master,
                            'image_master' => $image,
                            'jumlah_beli' => "x" . $value['jumlah_beli'],
                            'harga_produk' => rupiah($value['harga_barang']),
                            'harga_tampil' => rupiah($value['harga_barang'])
                        ];
                    }

                    if ($data->metode_pembayaran == '1') {
                        $payment = 'Pembayaran Manual';
                    } else {
                        $payment = $data->midtrans_payment_type;
                    }

                    $data1['id_transaksi'] = $data->id_transaksi;
                    $data1['invoice'] = $data->invoice;
                    $data1['total_harga'] = $data->total_harga_setelah_diskon + $data->harga_ongkir;
                    $data1['metode_pembayaran'] = $metode_pembayaran;
                    $data1['status_transaksi'] = $status_transaksi;
                    $data1['status_transaksi_ket'] = $status;
                    $data1['tanggal_transaksi'] = $data->tanggal_transaksi;
                    $data1['ambil_ditempat'] = $ambil_ditempat;
                    $data1['ambil_ditempat_ket'] = $ambil_ditempat_ket;
                    $data1['midtrans_payment_type'] = $data->midtrans_payment_type;
                    $data1['midtrans_transaction_status'] = $data->midtrans_transaction_status;
                    $data1['midtrans_token'] = $data->midtrans_token;
                    $data1['midtrans_redirect_url'] = $data->midtrans_redirect_url;
                    $data1['data_product'] = $getprodukcoba;

                    $data1['data_address_buyer'] = $data->alamat_penerima;
                    $data1['payment'] = $payment;

                    if ($data1) {
                        $response->data = $data1;
                        $response->sukses(200);
                    } else {
                        $response->data = [];
                        $response->sukses(200);
                    }
                } else {
                    $response->data = null;
                    $response->message = 'nomor transaksi ini tidak ditemukan.';
                    $response->error(400);
                }
            } else {
                $response->data = null;
                $response->message = 'nomor transaksi tidak ada.';
                $response->error(400);
            }
            break;
    }
} else {
    $response->data = null;
    $response->error(400);
}
die();
mysqli_close($conn);

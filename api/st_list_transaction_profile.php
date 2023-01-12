<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_login         = $_GET['id_login'];
$tag              = $_GET['tag'];

$exp_date = date("Y-m-d H:i:s", strtotime("+24 hours"));

if (isset($id_login)) {

    switch ($tag) {
        case 'sebelum':
            $data = $conn->query("SELECT a.id_transaksi, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, c.harga_master, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail FROM transaksi a
            JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
            JOIN master_item c ON b.id_barang = c.id_master 
            JOIN stok d ON c.id_master = d.id_barang 
            JOIN cabang e ON d.id_warehouse = e.id_cabang
            LEFT JOIN variant f ON b.id_barang = f.id_variant
            WHERE a.id_user = '$id_login' AND a.status_transaksi = '1' GROUP BY a.id_transaksi ORDER BY a.tanggal_transaksi DESC");

            //status transaksi | total produk | batas transaksi
            $status_transaksi = 'Menunggu Pembayaran';

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

                $result[] = [
                    'id_transaksi' => $key['id_transaksi'],
                    'exp_date' => $exp_date,
                    'total' => rupiah($key['total_harga_setelah_diskon']),
                    'status' => $key['status_transaksi'],
                    'status_transaksi' => $status_transaksi,
                    'status_ambil_ditempat' => $ambilditempat,
                    'nama_cabang' => $key['nama_cabang'],
                    'judul_master' => $key['judul_master'],
                    'harga_master' => rupiah($key['harga_diskon']),
                    'harga_tampil' => rupiah($key['harga_diskon']),
                    'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                    'image_master' => $key['status_master_detail'] == '2' ? $getimagebukufisik . $key['image_master'] : $getimagefisik . $key['image_master'],
                    'keterangan_varian' => $key['keterangan_varian'],
                    'jumlah_produk' => $jumlah,
                    'status_lebih_satu' => $status_lebih_satu,
                    'keterangan_lebih_satu' => $keterangan_lebih_satu
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
            $data = $conn->query("SELECT a.id_transaksi, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, c.harga_master, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail FROM transaksi a
            JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
            JOIN master_item c ON b.id_barang = c.id_master 
            JOIN stok d ON c.id_master = d.id_barang 
            JOIN cabang e ON d.id_warehouse = e.id_cabang
            LEFT JOIN variant f ON b.id_barang = f.id_variant
            WHERE a.id_user = '$id_login' AND a.status_transaksi = '3' GROUP BY a.id_transaksi ORDER BY a.tanggal_transaksi DESC;");

            //status transaksi | total produk | batas transaksi
            $status_transaksi = 'Dikemas';

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

                $result[] = [
                    'id_transaksi' => $key['id_transaksi'],
                    'exp_date' => $exp_date,
                    'total' => rupiah($key['total_harga_setelah_diskon']),
                    'status' => $key['status_transaksi'],
                    'status_transaksi' => $status_transaksi,
                    'status_ambil_ditempat' => $ambilditempat,
                    'nama_cabang' => $key['nama_cabang'],
                    'judul_master' => $key['judul_master'],
                    'harga_master' => rupiah($key['harga_diskon']),
                    'harga_tampil' => rupiah($key['harga_diskon']),
                    'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                    'image_master' => $key['status_master_detail'] == '2' ? $getimagebukufisik . $key['image_master'] : $getimagefisik . $key['image_master'],
                    'keterangan_varian' => $key['keterangan_varian'],
                    'jumlah_produk' => $jumlah,
                    'status_lebih_satu' => $status_lebih_satu,
                    'keterangan_lebih_satu' => $keterangan_lebih_satu
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
            $data = $conn->query("SELECT a.id_transaksi, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, c.harga_master, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail FROM transaksi a
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

                $result[] = [
                    'id_transaksi' => $key['id_transaksi'],
                    'exp_date' => $exp_date,
                    'total' => rupiah($key['total_harga_setelah_diskon']),
                    'status' => $key['status_transaksi'],
                    'status_transaksi' => $status_transaksi,
                    'status_ambil_ditempat' => $ambilditempat,
                    'nama_cabang' => $key['nama_cabang'],
                    'judul_master' => $key['judul_master'],
                    'harga_master' => rupiah($key['harga_diskon']),
                    'harga_tampil' => rupiah($key['harga_diskon']),
                    'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                    'image_master' => $key['status_master_detail'] == '2' ? $getimagebukufisik . $key['image_master'] : $getimagefisik . $key['image_master'],
                    'keterangan_varian' => $key['keterangan_varian'],
                    'jumlah_produk' => $jumlah,
                    'status_lebih_satu' => $status_lebih_satu,
                    'keterangan_lebih_satu' => $keterangan_lebih_satu
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
            $data = $conn->query("SELECT a.id_transaksi, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, c.harga_master, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail FROM transaksi a
            JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
            JOIN master_item c ON b.id_barang = c.id_master 
            JOIN stok d ON c.id_master = d.id_barang 
            JOIN cabang e ON d.id_warehouse = e.id_cabang
            LEFT JOIN variant f ON b.id_barang = f.id_variant
            WHERE a.id_user = '$id_login' AND a.status_transaksi = '7' GROUP BY a.id_transaksi ORDER BY a.tanggal_transaksi DESC;");

            //status transaksi | total produk | batas transaksi
            $status_transaksi = 'Transaksi Selesai';

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

                $result[] = [
                    'id_transaksi' => $key['id_transaksi'],
                    'exp_date' => $exp_date,
                    'total' => rupiah($key['total_harga_setelah_diskon']),
                    'status' => $key['status_transaksi'],
                    'status_transaksi' => $status_transaksi,
                    'status_ambil_ditempat' => $ambilditempat,
                    'nama_cabang' => $key['nama_cabang'],
                    'judul_master' => $key['judul_master'],
                    'harga_master' => rupiah($key['harga_diskon']),
                    'harga_tampil' => rupiah($key['harga_diskon']),
                    'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                    'image_master' => $key['status_master_detail'] == '2' ? $getimagebukufisik . $key['image_master'] : $getimagefisik . $key['image_master'],
                    'keterangan_varian' => $key['keterangan_varian'],
                    'jumlah_produk' => $jumlah,
                    'status_lebih_satu' => $status_lebih_satu,
                    'keterangan_lebih_satu' => $keterangan_lebih_satu
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
            $data = $conn->query("SELECT a.id_transaksi, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, c.harga_master, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail FROM transaksi a
            JOIN transaksi_detail b ON a.id_transaksi = b.id_transaksi
            JOIN master_item c ON b.id_barang = c.id_master 
            JOIN stok d ON c.id_master = d.id_barang 
            JOIN cabang e ON d.id_warehouse = e.id_cabang
            LEFT JOIN variant f ON b.id_barang = f.id_variant
            WHERE a.id_user = '$id_login' AND a.status_transaksi = '9' GROUP BY a.id_transaksi ORDER BY a.tanggal_transaksi DESC");

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

                $result[] = [
                    'id_transaksi' => $key['id_transaksi'],
                    'exp_date' => $exp_date,
                    'total' => rupiah($key['total_harga_setelah_diskon']),
                    'status' => $key['status_transaksi'],
                    'status_transaksi' => $status_transaksi,
                    'status_ambil_ditempat' => $ambilditempat,
                    'nama_cabang' => $key['nama_cabang'],
                    'judul_master' => $key['judul_master'],
                    'harga_master' => rupiah($key['harga_diskon']),
                    'harga_tampil' => rupiah($key['harga_diskon']),
                    'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                    'image_master' => $key['status_master_detail'] == '2' ? $getimagebukufisik . $key['image_master'] : $getimagefisik . $key['image_master'],
                    'keterangan_varian' => $key['keterangan_varian'],
                    'jumlah_produk' => $jumlah,
                    'status_lebih_satu' => $status_lebih_satu,
                    'keterangan_lebih_satu' => $keterangan_lebih_satu
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
            $data = $conn->query("SELECT a.id_transaksi, e.nama_cabang, c.judul_master, c.image_master, a.invoice, a.tanggal_transaksi, c.harga_master, b.harga_diskon, b.diskon_barang, a.total_harga_setelah_diskon, a.status_transaksi, a.kurir_code, f.keterangan_varian, c.status_master_detail FROM transaksi a
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

                $result[] = [
                    'id_transaksi' => $key['id_transaksi'],
                    'exp_date' => $exp_date,
                    'total' => rupiah($key['total_harga_setelah_diskon']),
                    'status' => $key['status_transaksi'],
                    'status_transaksi' => $status_transaksi,
                    'status_ambil_ditempat' => $ambilditempat,
                    'nama_cabang' => $key['nama_cabang'],
                    'judul_master' => $key['judul_master'],
                    'harga_master' => rupiah($key['harga_diskon']),
                    'harga_tampil' => rupiah($key['harga_diskon']),
                    'status_diskon' => $key['diskon_barang'] != 0 ? 'Y' : 'N',
                    'image_master' => $key['status_master_detail'] == '2' ? $getimagebukufisik . $key['image_master'] : $getimagefisik . $key['image_master'],
                    'keterangan_varian' => $key['keterangan_varian'],
                    'jumlah_produk' => $jumlah,
                    'status_lebih_satu' => $status_lebih_satu,
                    'keterangan_lebih_satu' => $keterangan_lebih_satu
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

            $getproduk = $conn->query("SELECT c.id_master, b.total_harga_sebelum_diskon, b.harga_ongkir, b.total_harga_setelah_diskon, b.voucher_harga, c.judul_master, c.image_master, a.jumlah_beli, a.harga_barang, a.diskon_barang, a.harga_diskon, b.invoice, d.id_variant, d.keterangan_varian, d.diskon_rupiah_varian, d.image_varian, b.status_transaksi, b.kurir_pengirim, b.kurir_code, b.kurir_service, b.metode_pembayaran, b.midtrans_transaction_status, b.midtrans_payment_type, b.midtrans_token, b.midtrans_redirect_url, b.alamat_penerima, b.nama_penerima, b.label_alamat, b.telepon_penerima, b.tanggal_transaksi, b.tanggal_dibayar, b.nomor_resi, c.status_master_detail
                FROM transaksi_detail a 
                JOIN transaksi b ON a.id_transaksi = b.id_transaksi
                LEFT JOIN master_item c ON a.id_barang = c.id_master
                LEFT JOIN variant d ON a.id_barang = d.id_variant WHERE a.id_transaksi = '$id_transaksi';");

            foreach ($getproduk as $key => $value) {

                if ($value['id_variant'] != NULL) {
                    $getstatusmaster = $conn->query("SELECT b.status_master_detail FROM variant a JOIN master_item b ON a.id_master = b.id_master WHERE a.id_variant = '$value[id_variant]'")->fetch_assoc();

                    $judul_master = $value['keterangan_varian'];

                    if ($getstatusmaster['status_master_detail'] == '2') {
                        $image = $getimagebukufisik . $value['image_varian'];
                    } else {
                        $image = $getimagefisik . $value['image_varian'];
                    }
                } else {
                    $getstatusmaster = $conn->query("SELECT status_master_detail FROM master_item WHERE id_master = '$value[id_master]'")->fetch_assoc();

                    $judul_master = $value['judul_master'];
                    if ($getstatusmaster['status_master_detail'] == '2') {
                        $image = $getimagebukufisik . $value['image_master'];
                    } else {
                        $image = $getimagefisik . $value['image_master'];
                    }
                }

                $getprodukcoba[] = [
                    "id_master" => $value['id_master'],
                    "judul_master" => $judul_master,
                    "image_master" => $image,
                    "jumlah_beli" => $value['jumlah_beli'],
                    "harga_produk" => $value['harga_barang'],
                    "harga_tampil" => $value['harga_diskon']
                ];
            }

            // $getproduct = $conn->query("");


            $informasi_pesanan = [
                'no_invoice' => $value['invoice'],
                'tanggal_transaksi' => $value['tanggal_transaksi'],
                'tanggal_dibayar' => $value['tanggal_dibayar'],
                'tanggal_pengiriman' => $value['tanggal_dibayar'],
                'tanggal_selesai' => $value['tanggal_dibayar']
            ];

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
                    'subtotal_produk' => rupiah($value['total_harga_sebelum_diskon']),
                    'subtotal_pengiriman' => rupiah($value['harga_ongkir']),
                    'subtotal_diskon' => rupiah($value['voucher_harga']),
                    'subtotal' => (rupiah($value['total_harga_sebelum_diskon'] + $value['harga_ongkir'])),
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

            //! Status Transaksi
            $status_transaksi = $value['status_transaksi'];
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

            $getdatatransaction =
                [
                    'id_transaksi' => $id_transaksi,
                    'status_transaksi' => $value['status_transaksi'],
                    'ket_status_transaksi' => $status,
                    'ambil_ditempat' => $value['ambil_ditempat'],
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

            if ($status_transaksi == '1') {
                $status_notif = 'Menunggu Pembayaran';
                $keterangan = 'Silahkan melakukan pembayaran paling lambat ' . $exp_date . ' dengan ' . $metode_pembayaran;
            } else if ($status_transaksi == '3') {
                $status_notif = 'Pesananmu sedang dikemas oleh penjual';
                $keterangan = 'Penjual harus mengatur pengiriman pesananmu paling lambat NULL';
            } else if ($status_transaksi == '5') {
                $status_notif = 'Pesananmu sedang dalam perjalanan';
                $keterangan = 'Produk diperkirakan akan sampai pada NULL';
            } else if ($status_transaksi == '7') {
                $status_notif = 'Pesanan selesai';
                $keterangan = 'Nilai pesanan sebelum NULL';
            } else if ($status_transaksi == '9') {
                $status_notif = 'Pesanan dibatalkan';
                $keterangan = 'Kamu telah membatalkan pesanan ini. Cek rincian pembatalan untuk informasi lebih lanjut.';
            }

            $notif = [
                'status' => $status_notif,
                'keterangan' => $keterangan,
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

            if ($data1) {
                $response->data = $data1;
                $response->sukses(200);
            } else {
                $response->data = [];
                $response->sukses(200);
            }
            break;
            die();


            // $status_transaksi = $data->status_transaksi;
            // $kurir_code = $data->kurir_code;

            // if ($status_transaksi == '1') {
            //     $status = 'Menunggu Pembayaran';
            // } else if ($status_transaksi == '2') {
            //     $status = 'Menunggu Verifikasi Pembayaran';
            // } else if ($status_transaksi == '3') {
            //     $status = 'Pembayaran Berhasil';
            // } else if ($status_transaksi == '4') {
            //     $status = 'Pembayaran Tidak Lengkap';
            // } else if ($status_transaksi == '5') {
            //     $status = 'Dikirim';
            // } else if ($status_transaksi == '6') {
            //     $status = 'Diterima';
            // } else if ($status_transaksi == '7') {
            //     $status = 'Transaksi Selesai';
            // } else if ($status_transaksi == '8') {
            //     $status = 'Expired';
            // } else if ($status_transaksi == '9') {
            //     $status = 'Dibatalkan';
            // } else if ($status_transaksi == '10') {
            //     $status = 'Pembayaran Ditolak';
            // } else if ($status_transaksi == '11') {
            //     $status = 'PengembalianBarang';
            // } else {
            //     $status = 'Expired';
            // }

            // if ($kurir_code == '00') {
            //     $status_kurir = $data->kurir_pengirim;
            //     $ambil_ditempat = $data->ambil_ditempat;

            //     if ($ambil_ditempat == '1') {
            //         $ambil_ditempat_ket = 'Belum Dipacking';
            //     } else if ($ambil_ditempat == '2') {
            //         $ambil_ditempat_ket = 'Masih Diproses';
            //     } else if ($ambil_ditempat == '3') {
            //         $ambil_ditempat_ket = 'Siap Diambil';
            //     } else if ($ambil_ditempat == '4') {
            //         $ambil_ditempat_ket = 'Sudah Diambil';
            //     } else {
            //         $ambil_ditempat_ket = 'Batal';
            //     }
            // } else {
            //     $status_kurir = $data->kurir_pengirim;
            //     $ambil_ditempat = "";
            // }

            // if ($data->metode_pembayaran == '1') {
            //     $metode_pembayaran = 'Pembayaran Manual';
            // } else if ($data->metode_pembayaran == '2') {
            //     $metode_pembayaran = 'Pembayaran Otomatis Midtrans';
            // } else {
            //     $metode_pembayaran = 'Belum Memilih Metode Pembayaran';
            // }

            // $product = $conn->query("SELECT * FROM transaksi_detail td JOIN master_item mi ON td.id_barang = mi.id_master WHERE td.id_transaksi")->fetch_object();

            // $data1['id_transaksi'] = $data->id_transaksi;
            // $data1['invoice'] = $data->invoice;
            // $data1['total_harga'] = $data->total_harga_setelah_diskon + $data->harga_ongkir;
            // $data1['metode_pembayaran'] = $metode_pembayaran;
            // $data1['status_transaksi'] = $status_transaksi;
            // $data1['status_transaksi_ket'] = $status;
            // $data1['tanggal_transaksi'] = $data->tanggal_transaksi;
            // $data1['ambil_ditempat'] = $ambil_ditempat;
            // $data1['ambil_ditempat_ket'] = $ambil_ditempat_ket;
            // $data1['nama_produk'] = $product->judul_master;
            // $data1['midtrans_payment_type'] = $data->midtrans_payment_type;
            // $data1['midtrans_transaction_status'] = $data->midtrans_transaction_status;
            // $data1['midtrans_token'] = $data->midtrans_token;
            // $data1['midtrans_redirect_url'] = $data->midtrans_redirect_url;

            // if ($data1) {
            //     $response->data = $data1;
            //     $response->sukses(200);
            // } else {
            //     $response->data = [];
            //     $response->sukses(200);
            // }
            // break;
    }
} else {
    $response->data = null;
    $response->error(400);
}
die();
mysqli_close($conn);

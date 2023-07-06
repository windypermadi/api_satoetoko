<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_transaksi = $_GET['id_transaksi'];

$query_select = "SELECT c.id_master, b.total_harga_sebelum_diskon, b.harga_ongkir, b.total_harga_setelah_diskon, b.voucher_harga, b.voucher_ongkir, c.judul_master, c.image_master, a.jumlah_beli, a.harga_barang, a.diskon_barang, a.harga_diskon, b.invoice, d.id_variant, d.keterangan_varian, d.diskon_rupiah_varian, d.image_varian, b.status_transaksi, b.kurir_pengirim, b.kurir_code, b.kurir_service, b.metode_pembayaran, b.midtrans_transaction_status, b.midtrans_payment_type, b.midtrans_token, b.midtrans_redirect_url, b.alamat_penerima, b.nama_penerima, b.label_alamat, b.telepon_penerima, b.tanggal_transaksi, b.tanggal_dibayar, b.tanggal_diterima, b.tgl_packing, b.nomor_resi, c.status_master_detail, b.st_packing, DATE_ADD(b.tanggal_diterima, INTERVAL 7 DAY) as tanggal_diterima, b.catatan_pembeli, b.biaya_platform, b.id_user, b.id_cabang, mp.metode_pembayaran as payment_metode
FROM transaksi_detail a
JOIN transaksi b ON a.id_transaksi = b.id_transaksi
LEFT JOIN master_item c ON a.id_barang = c.id_master
LEFT JOIN metode_pembayaran mp ON b.metode_pembayaran = mp.id_payment
LEFT JOIN variant d ON a.id_barang = d.id_variant WHERE a.id_transaksi = '$id_transaksi'";
$data = $conn->query($query_select);
var_dump($data);
die;

foreach ($data as $key => $value) {
    $result[] = [
        'idtrans' => $value['invoice'],
        'datetime' => $value['tanggal_transaksi'],
        'catatan' => $value['catatan_pembeli'],
        'idcust' => $value['id_user'],
        'namacust' => $value['nama_penerima'],
        'subtotal' => $value['total_harga_sebelum_diskon'],
        'ongkir' => $value['harga_ongkir'],
        'diskon_transaksi_rp' => $value['voucher_harga'] + $value['voucher_ongkir'],
        'grand_total_rp' => $value['total_harga_setelah_diskon'],
        'jenis_payment' => $value['payment_metode'],
        'jumlah_dibayar' => $value['total_harga_setelah_diskon'],
    ];
}

if ($result) {
    $response->data = $result;
    $response->sukses(200);
} else {
    $response->data = [];
    $response->sukses(200);
}

// 'idtrans' => $invoice,
// 'datetime' => $tanggal_sekarang,
// 'catatan' => $dataraw->catatan_pembeli,
// 'idcust' => $dataraw->id_user,
// 'namacust' => $nama_penerima,
// 'subtotal' => $dataraw->harga_normal,
// 'ongkir' => $data_ongkir_harga,
// 'diskon_transaksi_rp' => $dataraw->harga_voucher_barang,
// 'grand_total_rp' => $dataraw->jumlahbayar,
// 'jenis_payment' => $dataraw->id_payment,
// 'jumlah_dibayar' => $dataraw->jumlahbayar,
// ];

// $data['detail'] = '[{"datetime": ' . $tanggal_sekarang . ',"sku": ' . $getproduk->sku_induk . ',"nama_item": ' . $getproduk->judul_master . ',"harga_jual_satuan": ' . $dataproduk->harga_master . ',"qty": ' . $getproduk->qty . ',"diskon_detail": ' . $getproduk->diskon_rupiah . ',"sub_total": ' . $dataraw->harga_normal . ',"diskon_total": ' . $dataraw->harga_voucher_barang . ',"grand_total": ' . $dataraw->jumlahbayar . '},{"datetime": ' . $tanggal_sekarang . ',"sku": ' . $getproduk->sku_induk . ',"nama_item": ' . $getproduk->judul_master . ',"harga_jual_satuan": ' . $dataproduk->harga_master . ',"qty": ' . $getproduk->qty . ',"diskon_detail": ' . $getproduk->diskon_rupiah . ',"sub_total": ' . $dataraw->harga_normal . ',"diskon_total": ' . $dataraw->harga_voucher_barang . ',"grand_total": ' . $dataraw->jumlahbayar . '}]';

// echo $data;
// die;
// InputTransaksi($data);
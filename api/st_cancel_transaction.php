<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_transaksi         = $_POST['id_transaksi'];

$exp_date = date("Y-m-d H:i:s", strtotime("+24 hours"));

if (isset($id_transaksi)) {
    $cektransaksi = $conn->query("SELECT * FROM transaksi WHERE id_transaksi = '$id_transaksi' AND status_transaksi = '1'")->num_rows;
    if ($cektransaksi > 0) {

        // $getproduk = $conn->query("SELECT b.total_harga_sebelum_diskon, b.harga_ongkir, b.total_harga_setelah_diskon, b.voucher_harga, c.judul_master, c.image_master, a.jumlah_beli, a.harga_barang, a.diskon_barang, a.harga_diskon, b.invoice, d.id_variant, d.keterangan_varian, d.diskon_rupiah_varian, d.image_varian, b.status_transaksi, b.kurir_pengirim, b.kurir_code, b.kurir_service, b.metode_pembayaran, b.ambil_ditempat, b.midtrans_transaction_status, b.midtrans_payment_type, b.midtrans_token, b.midtrans_redirect_url FROM transaksi_detail a 
        //         JOIN transaksi b ON a.id_transaksi = b.id_transaksi
        //         LEFT JOIN master_item c ON a.id_barang = c.id_master
        //         LEFT JOIN variant d ON a.id_barang = d.id_variant WHERE a.id_transaksi = '$id_transaksi'");

        // foreach ($getproduk as $key => $value) {
        //     if ($value['id_variant'] != NULL) {
        //         $judul_master = $value['keterangan_varian'];
        //         $image = $value['image_varian'];
        //     } else {
        //         $judul_master = $value['judul_master'];
        //         $image = $value['image_master'];
        //     }

        //     $getprodukcoba[] = [
        //         'id_transaksi' => $id_transaksi,
        //         'judul_master' => $judul_master,
        //         'image_master' => $image,
        //         'jumlah_beli' => "x" . $value['jumlah_beli'],
        //         'harga_produk' => rupiah($value['harga_barang']),
        //         'harga_tampil' => rupiah($value['harga_barang'])
        //     ];
        // }

        $conn->begin_transaction();

        //! UPDATE STOK PRODUCT
        // $jml = $conn->query("SELECT jumlah FROM stok WHERE id_varian = '$u->id_variant'")->fetch_assoc();
        // $hasiljumlah = $jml['jumlah'] - $u->qty;

        // $query[] = $conn->query("UPDATE stok SET jumlah = '$hasiljumlah' WHERE id_varian = '$u->id_variant'");

        // //! UPDATE STOK HISTORY PRODUCT
        // $stokawal = $jml['jumlah'];
        // $query[] = $conn->query("INSERT INTO stok_history SET 
        // id_history = UUID_SHORT(),
        // tanggal_input = NOW(),
        // master_item = '$u->id_master',
        // varian_item = '$u->id_variant',
        // id_warehouse = '$u->id_gudang',
        // keterangan = 'TRANSAKSI MASUK',
        // masuk = '0',
        // keluar = '$u->qty',
        // stok_awal = '$stokawal',  
        // stok_sekarang = '$hasiljumlah'");

        //! DELETE USER TRANSAKSI
        $query[] = $conn->query("UPDATE transaksi SET status_transaksi = '9' WHERE id_transaksi = '$id_transaksi'");

        if (in_array(false, $query)) {
            $response->data = mysqli_error($conn);
            $response->error(400);
        } else {
            $conn->commit();
            $response->data = null;
            $response->sukses(200);
        }
    } else {
        $response->data = null;
        $response->message = "idtransaksi sudah tidak berlaku.";
        $response->error(400);
    }
} else {
    $response->data = null;
    $response->message = "idtransaksi tidak ada.";
    $response->error(400);
}
die();
mysqli_close($conn);

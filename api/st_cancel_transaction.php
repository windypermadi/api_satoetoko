<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_transaksi         = $_POST['id_transaksi'];

if (isset($id_transaksi)) {
    $cektransaksi = $conn->query("SELECT * FROM transaksi WHERE id_transaksi = '$id_transaksi'")->num_rows;
    if ($cektransaksi > 0) {

        $conn->begin_transaction();

        $getproduk = $conn->query("SELECT a.id_barang, a.jumlah_beli, d.id_variant, b.id_cabang FROM transaksi_detail a 
                JOIN transaksi b ON a.id_transaksi = b.id_transaksi
                LEFT JOIN master_item c ON a.id_barang = c.id_master
                LEFT JOIN variant d ON a.id_barang = d.id_variant WHERE a.id_transaksi = '$id_transaksi'");

        foreach ($getproduk as $key => $value) {
            if (!empty($value['id_variant'])) {
                $jml = $conn->query("SELECT jumlah FROM stok WHERE id_varian = '$value[id_variant]'")->fetch_assoc();
                $hasiljumlah = $jml['jumlah'] + $value['jumlah_beli'];

                $query[] = $conn->query("UPDATE stok SET jumlah = '$hasiljumlah' WHERE id_varian = '$value[id_variant]'");

                $query[] = $conn->query("UPDATE transaksi SET tanggal_dibatalkan = NOW() WHERE id_transaksi = '$id_transaksi'");
            } else {
                $jml = $conn->query("SELECT jumlah FROM stok WHERE id_barang = '$value[id_barang]'")->fetch_assoc();
                $hasiljumlah = $jml['jumlah'] + $value['jumlah_beli'];

                $query[] = $conn->query("UPDATE stok SET jumlah = '$hasiljumlah' WHERE id_barang = '$value[id_barang]'");

                $query[] = $conn->query("UPDATE transaksi SET tanggal_dibatalkan = NOW() WHERE id_transaksi = '$id_transaksi'");
            }
        }

        //! UPDATE STOK HISTORY PRODUCT
        $stokawal = $jml['jumlah'];
        $jumlahbeli = $value['jumlah_beli'];
        $query[] = $conn->query("INSERT INTO stok_history SET 
        id_history = UUID_SHORT(),
        tanggal_input = NOW(),
        master_item = '$value[id_barang]',
        varian_item = '$value[id_variant]',
        id_warehouse = '$value[id_cabang]',
        keterangan = 'PEMBATALAN TRANSAKSI',
        masuk = $jumlahbeli,
        keluar = 0,
        stok_awal = $stokawal,  
        stok_sekarang = $hasiljumlah");

        //! DELETE USER TRANSAKSI
        $query[] = $conn->query("UPDATE transaksi SET status_transaksi = '9' WHERE id_transaksi = '$id_transaksi'");

        if (in_array(false, $query)) {
            $response->data = mysqli_error($conn);
            $response->message = "query salah";
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

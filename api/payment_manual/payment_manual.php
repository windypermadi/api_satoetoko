<?php 
require_once('../../config/koneksi.php');

$invoice = $_GET['invoice'];

$cekinvoice = mysqli_query($conn, "SELECT invoice FROM ebook_transaksi WHERE invoice = '$invoice'")->num_rows;
if ($cekinvoice > 0){

    $conn->begin_transaction();
    $sql    = mysqli_query($conn,"SELECT a.id_transaksi_detail, a.status_pembelian, c.lama_sewa FROM ebook_transaksi_detail a
    JOIN ebook_transaksi b ON a.id_transaksi = b.id_transaksi
    JOIN master_ebook_detail c ON a.id_master = c.id_master
    WHERE b.invoice = '$invoice'");
    while($row = mysqli_fetch_array($sql)){
        if ($row['status_pembelian'] == '1'){
            $lama = 3650;
        } else if ($row['status_pembelian'] == '2'){
            $lama = $row['lama_sewa'];
        } 
         $stmt[]    = mysqli_query($conn,"UPDATE ebook_transaksi_detail SET tgl_expired = DATE_ADD(NOW(), INTERVAL '$lama' DAY) WHERE id_transaksi_detail = '$row[id_transaksi_detail]'");
    }

    $stmt[] = $conn->query("UPDATE ba_transaksi_ebook SET status_transaksi= '7', tanggal_dibayar = NOW(), tgl_aktif = DATE_ADD(NOW(), INTERVAL '$lama' DAY) WHERE invoice = '$invoice'");
    if (in_array(false, $stmt) OR in_array(0, $stmt)) {
        $conn->rollback();
        http_response_code(400);
        $respon['pesan'] = "Gagal edit transaksi.\nKlik `Mengerti` untuk menutup pesan ini";
        die(json_encode($respon)); 
    }else{
        $conn->commit();
        $respon['pesan'] = "Yeyee berhasil edit transasi dengan kode invoice ".$invoice;
        die(json_encode($respon));
    }
} else {
    http_response_code(400);
    $respon['pesan'] = "Nomor Invoice tidak ada";
    die(json_encode($respon)); 
}

mysqli_close($conn);
?>
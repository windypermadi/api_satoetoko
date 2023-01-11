<?php require_once('../../config/koneksi.php');
include "../response.php";
$response = new Response();
$get_raw = file_get_contents('php://input');
$query = mysqli_query($conn, "INSERT into midtrans_callback set data = '$get_raw'");

$data = json_decode($get_raw, true);

$date_now = date('Y-m-d H:i:s');
$order_id = $req->order_id;
$status_code = $req->status_code;
$gross_amount = $req->gross_amount;
$server_key = MIDTRANS_SERVER_KEY;
$signature_key = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);

if ($signature_key != $req->signature_key) {
    return $this->ajaxResponse('', 400, 'signature not valid');
}

$data_kepala = $this->db->query("SELECT * FROM ebook_transaksi a JOIN data_user b 
ON a.id_user=b.id_login WHERE a.invoice='$order_id'")->row();
if (is_null($data_kepala)) {
    return $this->ajaxResponse('', 400, 'Invoice tidak ditemukan');
}

if ($req->transaction_status == "settlement" or $req->transaction_status == "capture" or $req->transaction_status == "accept") {

    //PAYMENT MIDTRANS
    $transaksidetail = mysqli_fetch_object($conn->query("SELECT a.id_transaksi, a.id_transaksi_detail, a.status_pembelian, d.lama_sewa, c.id_supplier, a.harga_normal, a.harga_diskon, a.fee_toko, a.sub_total
FROM ebook_transaksi_detail a JOIN ebook_transaksi b ON a.id_transaksi=b.id_transaksi JOIN master_item c ON a.id_master=c.id_master JOIN master_ebook_detail d ON c.id_master=d.id_master WHERE b.invoice='$idtransaksi'"));

    $cektemp = $conn->query("SELECT * FROM saldo WHERE id_supplier = '$transaksidetail->id_supplier'")->num_rows;
    $temp = mysqli_fetch_object($conn->query("SELECT * FROM saldo WHERE id_supplier = '$transaksidetail->id_supplier' ORDER BY tanggal_posting DESC"));
    $saldo_akhir = $temp->saldo_akhir + $transaksidetail->sub_total;

    $this->db->trans_begin();

    $sql = $this->db->query("SELECT a.id_transaksi_detail, a.status_pembelian, d.lama_sewa FROM ebook_transaksi_detail a 
JOIN ebook_transaksi b ON a.id_transaksi=b.id_transaksi JOIN master_item c ON a.id_master=c.id_master JOIN master_ebook_detail d ON c.id_master=d.id_master WHERE b.invoice='$order_id'")->result();


    if (empty($sql)) {
        return $this->ajaxResponse('', 400, 'Data Tidak Ketemu');
    }

    foreach ($sql as $row) {
        if ($row->status_pembelian == '1') {
            $lama = 3650;
        } else if ($row->status_pembelian == '2') {
            $lama = $row->lama_sewa;
        }

        $this->db->query("UPDATE ebook_transaksi_detail SET tgl_expired = DATE_ADD(NOW(), 
INTERVAL '$lama'DAY) WHERE id_transaksi_detail='$row->id_transaksi_detail'");
    }

    $this->db->query("UPDATE ebook_transaksi SET status_transaksi= '7', tgl_dibayar = NOW(), payment_type='" . $req->payment_type . "' 
WHERE invoice='$order_id'");

    if ($cektemp == 0) {
        $this->db->query(
            "INSERT INTO saldo SET id_saldo='$transaction->id',
                        id_supplier='$transaksidetail->id_supplier',
                        keterangan='SALDO MASUK TRANSAKSI CUSTOMER',
                        id_transaksi='$transaksidetail->id_transaksi',
                        saldo_masuk='$transaksidetail->sub_total',
                        saldo_keluar=0,
                        saldo_awal=0,
                        saldo_akhir='$transaksidetail->sub_total'"
        );
    } else {
        $this->db->query(
            "INSERT INTO saldo SET 
id_saldo='$transaction->id',
                        id_supplier='$transaksidetail->id_supplier',
                        keterangan='SALDO MASUK TRANSAKSI CUSTOMER',
                        id_transaksi='$transaksidetail->id_transaksi',
                        saldo_masuk='$transaksidetail->sub_total',
                        saldo_keluar=0,
                        saldo_awal='$temp->saldo_akhir',
                        saldo_akhir='$saldo_akhir'"
        );
    }

    if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        return $this->ajaxResponse('', 400, 'Gagal transaksi.\nKlik `Mengerti` untuk menutup pesan ini');
    } else {
        $this->db->trans_commit();
        return $this->ajaxResponse('', 200, 'Yeyee berhasil melakukan transaksi dengan kode invoice ');
    }
} else if ($req->transaction_status == "pending") {
    $this->db->query("UPDATE ebook_transaksi SET status_transaksi= '1', tgl_dibayar = NOW(), payment_type='" . $req->payment_type . "'
WHERE invoice='$order_id'");
} else if ($req->transaction_status == "expire") {
    $this->db->query("UPDATE ebook_transaksi SET status_transaksi= '8', tgl_dibayar = NOW(), payment_type='" . $req->payment_type . "' 
WHERE invoice='$order_id'");
} else if ($req->transaction_status == "deny") {
    $this->db->query("UPDATE ebook_transaksi SET status_transaksi= '9', tgl_dibayar = NOW(), payment_type='" . $req->payment_type . "' 
WHERE invoice='$order_id'");
} else {
    $this->db->query("UPDATE ebook_transaksi SET status_transaksi= '9', tgl_dibayar = NOW(), payment_type='" . $req->payment_type . "' 
WHERE invoice='$order_id'");
}

mysqli_close($conn);

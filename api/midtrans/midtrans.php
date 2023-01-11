<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MidTrans extends M_Controller
{

    public function __construct()
    {
        parent::__construct();
        $stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
        $this->req = json_decode($stream_clean);
    }

    private $req;


    public function handlePayment()
    {
        $orderId = explode('_', $this->req->order_id);

        if (count($orderId) > 1) {
            return $this->fisikPayment($this->req, $orderId);
        } else {
            return $this->ebookPayment($this->req);
        }
    }

    private function fisikPayment($req, $orderId)
    {
        $signature_key = hash('sha512', $req->order_id . $req->status_code . $req->gross_amount . MIDTRANS_SERVER_KEY);
        if ($signature_key != $req->signature_key) {
            return $this->ajaxResponse('', 400, 'Signature not valid');
        }

        $id = @$orderId[3];
        $invoice = $orderId[0] . '_' . $orderId[1] . '_' . $orderId[2];
        $transaksi = $this->db->get_where('transaksi', ['invoice' => $invoice])->row();
        if (is_null($transaksi)) {
            return $this->ajaxResponse('', 400, 'Transaksi Fisik Tidak Ketemu');
        }

        if ($transaksi->status_transaksi != 1) {
            return $this->ajaxResponse('', 200, "Status Transaksi ($transaksi->status_transaksi), Midtrans 
                        ($transaksi->midtrans_transaction_status)");
        } else {
            $midtransTransactionStatus = $req->transaction_status;
            $midtransPaymenType = $req->payment_type;
            $statusTransaksi = $transaksi->status_transaksi;

            $this->db->trans_begin();
            $transaksiDetail = $this->db->query("SELECT t.id_transaksi, td.id_transaksi_detail, t.status_transaksi, t.midtrans_transaction_status, 
                                t.id_cabang, td.id_barang, td.jumlah_beli, td.id_supplier, td.fee_toko, td.sub_total, s.jumlah AS stok
                                FROM transaksi AS t JOIN transaksi_detail AS td ON t.id_transaksi=td.id_transaksi
                                JOIN stok AS s ON td.id_barang=s.id_barang AND t.id_cabang=s.id_warehouse
                                WHERE t.invoice='$invoice'")->result();

            if (empty($transaksiDetail)) {
                return $this->ajaxResponse('', 400, "Penyesuaian Stok Gagal");
            }

            if ($req->transaction_status == "settlement" or $req->transaction_status == "capture") {
                $dataSaldoSupplier = [];
                $dataInputSaldo = [];
                foreach ($transaksiDetail as $td) {
                    if (isset($dataSaldoSupplier[$td->id_supplier])) {
                        $dataSaldoSupplier[$td->id_supplier] += ($td->sub_total - $td->fee_toko);
                    } else {
                        $dataSaldoSupplier[$td->id_supplier]['saldo'] = ($td->sub_total - $td->fee_toko);
                        $dataSaldoSupplier[$td->id_supplier]['id_transaksi'] = $td->id_transaksi;
                    }
                }

                foreach ($dataSaldoSupplier as $key => $dsp) {
                    $temp = $this->db->query("SELECT * FROM saldo WHERE id_supplier='$key' ORDER BY tanggal_posting DESC")->row();
                    if ($this->db->get_where('saldo', ['id_supplier' => $key, 'id_transaksi' => $dsp['id_transaksi']])->row()) {
                        continue;
                    }
                    if (is_null($temp)) {
                        $dataInputSaldo = [
                            'id_supplier' => $key,
                            'keterangan' => 'SALDO MASUK TRANSAKSI CUSTOMER',
                            'id_transaksi' => $dsp['id_transaksi'],
                            'saldo_masuk' => $dsp['saldo'],
                            'saldo_keluar' => 0,
                            'saldo_awal' => 0,
                            'saldo_akhir' => $dsp['saldo']
                        ];
                    } else {
                        $dataInputSaldo = [
                            'id_supplier' => $key,
                            'keterangan' => 'SALDO MASUK TRANSAKSI CUSTOMER',
                            'id_transaksi' => $dsp['id_transaksi'],
                            'saldo_masuk' => $dsp['saldo'],
                            'saldo_keluar' => 0,
                            'saldo_awal' => $temp->saldo_akhir,
                            'saldo_akhir' => ($temp->saldo_akhir + $dsp['saldo'])
                        ];
                    }
                    $this->db->set('id_saldo', "uuid_short()", FALSE);
                    $this->db->insert('saldo', $dataInputSaldo);
                }

                $statusTransaksi = 3;
            } elseif ($req->transaction_status == "pending") {
                return $this->ajaxResponse('', 200, "Status Transaksi ($transaksi->status_transaksi), Midtrans 
                        ($transaksi->midtrans_transaction_status)");
            } else {
                $statusTransaksi = ($req->transaction_status == "expire") ? 8 : ($req->transaction_status == "deny" ? 10 : 9);
                foreach ($transaksiDetail as $td) {
                    $this->db->update('stok', [
                        'jumlah' => $td->jumlah_beli + $td->stok
                    ], [
                        'id_barang' => $td->id_barang,
                        'id_warehouse' => $td->id_cabang,
                    ]);

                    $this->db->insert('stok_history', [
                        'id_history' => $this->uuid->v4(TRUE),
                        'id_ref' => $td->id_transaksi,
                        'master_item' => $td->id_barang,
                        'id_warehouse' => $td->id_cabang,
                        'keterangan' => 'TRANSAKSI GAGAL',
                        'masuk' => $td->jumlah_beli,
                        'keluar' => 0,
                        'stok_awal' => $td->stok,
                        'stok_sekarang' => $td->jumlah_beli + $td->stok,
                    ]);
                }
            }

            $this->db->update('transaksi', [
                'status_transaksi' => $statusTransaksi,
                'midtrans_transaction_status' => $midtransTransactionStatus,
                'midtrans_payment_type' => $midtransPaymenType,
                'tanggal_dibayar' => NOW(),
            ], [
                'invoice' => $invoice
            ]);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return $this->ajaxResponse('', 500, 'Failed at DB Transactions to MidTrans.');
            } else {
                $this->db->trans_commit();
                return $this->ajaxResponse('', 200, 'Handle Payment Midtrans Success.');
            }
        }
    }

    private function ebookPayment($req) // Windi
    {
        $this->db->insert('midtrans_callback', [
            'data' => json_encode($req)
        ]);

        $date_now = date('Y-m-d H:i:s');
        $order_id = $req->order_id;
        $status_code = $req->status_code;
        $gross_amount = $req->gross_amount;
        $server_key = MIDTRANS_SERVER_KEY;
        $payment_type = $req->payment_type;
        $transaction_status = $req->transaction_status;
        $signature_key = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);

        if ($signature_key != $req->signature_key) {
            return $this->ajaxResponse('', 400, 'signature not valid');
        }

        $data_kepala = $this->db->query("SELECT * FROM ebook_transaksi a JOIN data_user b 
                        ON a.id_user = b.id_login WHERE a.invoice = '$order_id'")->row();
        if (is_null($data_kepala)) {
            return $this->ajaxResponse('', 400, 'Invoice tidak ditemukan');
        }

        if ($transaction_status == "settlement" or $transaction_status == "capture" or $transaction_status == "accept") {

            //ADD SALDO HISTORY
            $transaksidetail = $this->db->query("SELECT a.id_transaksi, a.id_transaksi_detail, a.status_pembelian, d.lama_sewa, 
                        c.id_supplier, a.harga_normal, a.harga_diskon, a.fee_toko, a.sub_total
                        FROM ebook_transaksi_detail a 
                        JOIN ebook_transaksi b ON a.id_transaksi=b.id_transaksi 
                        JOIN master_item c ON a.id_master=c.id_master 
                        JOIN master_ebook_detail d ON c.id_master=d.id_master 
                        WHERE b.invoice = '$order_id'")->row();
            // $transaksidetail = mysqli_fetch_object($this->db->query("SELECT a.id_transaksi, a.id_transaksi_detail, a.status_pembelian, d.lama_sewa, 
            //             c.id_supplier, a.harga_normal, a.harga_diskon, a.fee_toko, a.sub_total
            //             FROM ebook_transaksi_detail a 
            //             JOIN ebook_transaksi b ON a.id_transaksi=b.id_transaksi 
            //             JOIN master_item c ON a.id_master=c.id_master 
            //             JOIN master_ebook_detail d ON c.id_master=d.id_master 
            //             WHERE b.invoice = '$order_id'"));
            // $transaksidetail = $this->db->query($q);
            // $data = $transaksidetail->fetch_object();

            // $cektemp = $this->db->query("SELECT * FROM saldo WHERE id_supplier = '$transaksidetail->id_supplier'")->num_rows;
            $temp = $this->db->query("SELECT * FROM saldo WHERE id_supplier = '$transaksidetail->id_supplier' ORDER BY tanggal_posting DESC")->row();
            $saldo_akhir = $temp->saldo_akhir + $transaksidetail->sub_total;
            //ADD SALDO HISTORY
            if (!is_null($temp)) {
                $this->db->query("INSERT INTO saldo SET id_saldo = UUID_SHORT(),
                        id_supplier='$transaksidetail->id_supplier',
                        keterangan='SALDO MASUK TRANSAKSI CUSTOMER DARI APLIKASI',
                        id_transaksi='$transaksidetail->id_transaksi',
                        saldo_masuk='$transaksidetail->sub_total',
                        saldo_keluar=0,
                        saldo_awal='$temp->saldo_akhir',
                        saldo_akhir='$saldo_akhir'");
            } else {
                $this->db->query("INSERT INTO saldo SET id_saldo= UUID_SHORT(),
                        id_supplier='$transaksidetail->id_supplier',
                        keterangan='SALDO MASUK TRANSAKSI CUSTOMER DARI APPS',
                        id_transaksi='$transaksidetail->id_transaksi',
                        saldo_masuk='$transaksidetail->sub_total',
                        saldo_keluar=0,
                        saldo_awal=0,
                        saldo_akhir='$transaksidetail->sub_total'");
            }

            //ADD SALDO HISTORY
            $this->db->trans_begin();

            $sql = $this->db->query("SELECT a.id_transaksi_detail, a.status_pembelian, d.lama_sewa FROM ebook_transaksi_detail a 
                    JOIN ebook_transaksi b ON a.id_transaksi = b.id_transaksi
                    JOIN master_item c ON a.id_master = c.id_master
                    JOIN master_ebook_detail d ON c.id_master = d.id_master
                    WHERE b.invoice = '$order_id'")->result();

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
                    INTERVAL '$lama' DAY) WHERE id_transaksi_detail = '$row->id_transaksi_detail'");
            }

            $this->db->query("UPDATE ebook_transaksi SET status_transaksi= '7', tgl_dibayar = NOW(), payment_type='$payment_type' 
                WHERE invoice = '$order_id'");

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return $this->ajaxResponse('', 400, 'Gagal transaksi.\nKlik `Mengerti` untuk menutup pesan ini');
            } else {
                $this->db->trans_commit();
                return $this->ajaxResponse('', 200, 'Yeyee berhasil melakukan transaksi dengan kode invoice ');
            }
        } else if ($transaction_status == "pending") {
            $this->db->query("UPDATE ebook_transaksi SET status_transaksi= '1', tgl_dibayar = NOW(), payment_type='$payment_type'
                 WHERE invoice = '$order_id'");
        } else if ($transaction_status == "expire") {
            $this->db->query("UPDATE ebook_transaksi SET status_transaksi= '8', tgl_dibayar = NOW(), payment_type='$payment_type' 
                WHERE invoice = '$order_id'");
        } else if ($transaction_status == "deny") {
            $this->db->query("UPDATE ebook_transaksi SET status_transaksi= '9', tgl_dibayar = NOW(), payment_type='$payment_type' 
                WHERE invoice = '$order_id'");
        } else {
            $this->db->query("UPDATE ebook_transaksi SET status_transaksi= '9', tgl_dibayar = NOW(), payment_type='$payment_type' 
            WHERE invoice = '$order_id'");
        }
    }
}

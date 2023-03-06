<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$tag = $_REQUEST['tag'];
$total_item = 0;

switch ($tag) {
    case "detail_checkout":
        $id_user            = $_POST['id_user'];
        $id_master          = $_POST['id_master'];
        $status             = $_POST['status_pembelian'];
        $harga_normal       = $_POST['harga_normal'];
        $diskon             = $_POST['diskon'];
        $harga_diskon       = $_POST['harga_diskon'];

        $data = mysqli_fetch_object($conn->query("SELECT a.id_master, a.judul_master, a.image_master, c.nama_kategori, a.harga_master, a.diskon_rupiah, 
        a.diskon_persen, a.harga_sewa, a.diskon_sewa_rupiah, a.diskon_sewa_persen, b.sinopsis,
        b.penerbit, b.tahun_terbit, b.tahun_terbit, b.edisi, b.isbn, b.status_ebook, b.lama_sewa FROM master_item a 
        JOIN master_ebook_detail b ON a.id_master = b.id_master
        LEFT JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub WHERE a.status_master_detail = '1' AND a.id_master = '$id_master'"));

        $cekppn = mysqli_fetch_object($conn->query("SELECT * FROM profile"));
        // $ppn = $cekppn->pajak;
        $ppn = "0";

        if ($status == '1') {
            $diskon_persen = $data->diskon_persen;
            $diskon_rupiah = $data->diskon_rupiah;
            $harga_produk = $data->harga_master;
        } else if ($status == '2') {
            $diskon_persen = $data->diskon_sewa_persen;
            $diskon_rupiah = $data->diskon_sewa_rupiah;
            $harga_produk = $data->harga_sewa;
        } else {
            $response->code = 400;
            $response->message = 'status pembelian tidak ada.';
            $response->data = '';
            $response->json();
            die();
        }

        $ebooks = $conn->query("SELECT * FROM master_item a 
        LEFT JOIN kategori_sub b ON a.id_sub_kategori = b.id_sub
        LEFT JOIN master_ebook_detail c ON a.id_master = c.id_master
        WHERE a.id_master = '$id_master'");
        foreach ($ebooks as $key => $value) {
            $listebook[] = [
                'id_master' => $value['id_master'],
                'judul_master' => $value['judul_master'],
                'image_master' => $urlimg . $value['image_master'],
                'nama_kategori' => $value['nama_kategori'],
                'lama_sewa' => $status == '2' ? $value['lama_sewa'] : "",
            ];
        }

        $payments = $conn->query("SELECT * FROM metode_pembayaran WHERE status_aktif = 'Y' ORDER BY id_payment ASC");
        foreach ($payments as $key => $value) {
            $listmetode[] = [
                'id_payment' => $value['id_payment'],
                'icon_payment' => $geticonpayment . $value['icon_payment'],
                'metode_pembayaran' => $value['metode_pembayaran'],
                'nomor_payment' => $value['nomor_payment'],
                'penerima_payment' => $value['penerima_payment']
            ];
        }

        $vouchers = $conn->query("SELECT * FROM voucher_user a 
            JOIN voucher b ON a.idvoucher = b.idvoucher
            WHERE a.iduser = '$iduser' AND a.status_pakai = '0' AND tgl_mulai <= CURRENT_DATE() AND tgl_berakhir >= CURRENT_DATE();");
        foreach ($vouchers as $key => $value) {
            $listvoucher[] = [
                'idvoucher' => $value['idvoucher'],
                'nama_voucher' => $value['nama_voucher'],
                'deskripsi_voucher' => $value['deskripsi_voucher'],
                'nilai_voucher' => $value['nilai_voucher'],
                'minimal_transaksi' => $value['minimal_transaksi']
            ];
        }

        // $totalppn = $harga_produk * ((int)$ppn / 100);
        $totalppn = 0;

        $data1['produk'] = $listebook;
        $data1['kupon'] = $listvoucher;
        $data1['metode_pembayaran'] = $listmetode;
        $data1['harga_produk'] = (int)$harga_produk;
        $data1['diskon_rupiah'] = (int)$diskon_rupiah;
        $data1['diskon_persen'] = (int)$diskon_persen;
        $data1['voucher'] = 0;
        $data1['ppn_persen'] = $ppn . "%";
        $data1['ppn_rupiah'] = (int)$totalppn;
        $data1['biaya_admin'] = 0;
        $data1['total'] = ($harga_produk - $diskon_rupiah) + $totalppn;

        if ($data) {
            $response->code = 200;
            $response->message = 'success';
            $response->data = $data1;
            $response->json();
            die();
        } else {
            $response->code = 200;
            $response->message = mysqli_error($conn);
            $response->data = [];
            $response->json();
            die();
        }
        // }
        break;
    case "addtransaksi":
        $id_user            = $_POST['id_user'];
        $id_master          = $_POST['id_master'];
        $id_voucher         = $_POST['id_voucher'] ?? '';
        $id_payment         = $_POST['id_payment'];
        $status             = $_POST['status_pembelian'];
        $jumlahbayar        = $_POST['jumlahbayar'];
        $harga_normal       = $_POST['harga_normal'];
        $diskon             = $_POST['diskon'];
        $harga_diskon       = $_POST['harga_diskon'];

        $response = new Response();
        $exp_date = date("Y-m-d H:i:s", strtotime("+72 hours"));

        if ($id_payment != '0') {
            $status_payment = '2';
        } else {
            $status_payment = '1';
        }

        if ($diskon == 0) {
            $harga_diskon = 0;
        } else {
            $harga_diskon = (int)$data->harga_diskon;
        }

        $cekBarang = $conn->query("SELECT * FROM ebook_transaksi_detail a JOIN ebook_transaksi b WHERE a.id_master = '$id_master' AND a.tgl_expired >= NOW() AND b.status_transaksi = '7' AND a.status_pembelian = '$status'");

        if (isset($cekBarang)) {
            $response->code = 400;
            $response->message = "Ebook ini masih ada di buku saya.";
            $response->data = '';
            $response->json();
            die();
        } else {

            $conn->begin_transaction();

            $transaction = mysqli_fetch_object($conn->query("SELECT UUID_SHORT() as id"));
            $idtransaksi = createID('invoice', 'ebook_transaksi', 'TR');
            $invoice = id_ke_struk($idtransaksi);

            $data2 = mysqli_fetch_object($conn->query("SELECT b.lama_sewa, a.fee_produk FROM master_item a 
        JOIN master_ebook_detail b ON a.id_master = b.id_master
        JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub 
        WHERE a.status_master_detail = '1' AND a.id_master = '$id_master'"));

            //     $datasupplier = mysqli_fetch_object($conn->query("SELECT a.id_supplier,b.fee_admin FROM master_item a 
            // JOIN supplier b ON a.id_supplier = b.id_supplier
            // WHERE a.id_master = '$id_master';"));
            //     $feeadmin = $jumlahbayar * ($datasupplier->fee_admin / 100);

            $feetoko = $harga_diskon - ($harga_diskon * ($data2->fee_produk / 100));
            $subtotalfee = $jumlahbayar - $feetoko;

            $totalakhir = (int)$jumlahbayar - (int)$harga_diskon;

            $data[] = mysqli_query($conn, "INSERT INTO ebook_transaksi SET 
        id_transaksi = '$transaction->id',
        invoice = '$idtransaksi',
        id_user = '$id_user',
        tgl_pembelian = NOW(),
        status_transaksi = '1',
        status_payment = '$status_payment',
        batas_pembayaran = '$exp_date',
        total_pembayaran = '$jumlahbayar',
        kode_voucher = '$id_voucher',
        payment_type = '$id_payment',
        total_akhir_pembayaran = '$totalakhir'");

            $data[] = $conn->query("INSERT INTO ebook_transaksi_detail SET 
        id_transaksi_detail = UUID_SHORT(),
        id_transaksi = '$transaction->id',
        id_user = '$id_user',
        id_master = '$id_master',
        harga_normal = '$harga_normal',
        diskon = '$diskon',
        harga_diskon = '$harga_diskon',
        status_pembelian = '$status',
        fee_toko = '$feetoko',
        sub_total = '$subtotalfee',
        tgl_create = NOW()");

            $query = mysqli_query($conn, "SELECT * FROM metode_pembayaran WHERE id_payment = '$id_payment'")->fetch_assoc();
            $icon_payment = $geticonpayment . $query['icon_payment'];
            $metode_pembayaran = $query['metode_pembayaran'];
            $nomor_payment = $query['nomor_payment'];
            $penerima_payment = $query['penerima_payment'];

            if (in_array(false, $data)) {
                $response->code = 400;
                $response->message = mysqli_error($conn);
                $response->data = '';
                $response->json();
                die();
            } else {

                $querydata = mysqli_query($conn, "SELECT * FROM ebook_transaksi a 
            JOIN data_user b ON a.id_user = b.id_login
            WHERE a.id_transaksi = '$transaction->id'")->fetch_assoc();
                $invoice = $querydata['invoice'];
                $nama_user = $querydata['nama_user'];
                $payer_email = $querydata['email'];
                $no_telp = $querydata['notelp'];

                if ($id_payment == '0') {
                    $mtrans['transaction_details']['order_id'] = $invoice;
                    $mtrans['transaction_details']['gross_amount'] = $jumlahbayar;
                    $mtrans['credit_card']['secure'] = true;
                    $mtrans['customer_details']['first_name'] = $nama_user;
                    $mtrans['customer_details']['last_name'] = '';
                    $mtrans['customer_details']['email'] = $payer_email;
                    $mtrans['customer_details']['phone'] = $no_telp;
                    $mtrans_json = json_encode($mtrans);

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => MTRANS_URL,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $mtrans_json,
                        CURLOPT_HTTPHEADER => array(
                            'Authorization: Basic ' . base64_encode(MTRANS_SERVER_KEY),
                            'Content-Type: application/json'
                        ),
                    ));

                    $response_curl = curl_exec($curl);
                    curl_close($curl);

                    $responses = json_decode($response_curl, true);

                    $payment_url = $responses['redirect_url'];
                    $payment_token = $responses['token'];
                    $res['token'] = $payment_token;
                    $res['url_payment'] = $payment_url;

                    $query = mysqli_query($conn, "UPDATE ebook_transaksi SET token_payment = '$payment_token', url_payment = '$payment_url' WHERE id_transaksi= '$transaction->id'");

                    if (!isset($responses['token'])) {
                        respon_json_status_500();
                        $respon['pesan'] = "Sorry, we encountered internal server error. We will fix this soon.";
                        die(json_encode($respon));
                    }

                    $response->code = 200;
                    $response->message = 'result';
                    $response->data = $res;
                    $response->json();
                    die();
                } else {
                    $total_format = "Rp" . number_format($jumlahbayar, 0, ',', '.');

                    $result['batas_pembayaran'] = $exp_date;
                    $result['id_transaksi'] = $idtransaksi;
                    $result['invoice'] = $invoice;
                    $result['id_payment'] = $id_payment;
                    $result['icon_payment'] = $geticonpayment . $icon_payment;
                    $result['metode_pembayaran'] = $metode_pembayaran;
                    $result['nomor_payment'] = $nomor_payment;
                    $result['penerima_payment'] = $penerima_payment;
                    $result['total_harga'] = (int)$jumlahbayar;
                    $result['nomor_konfirmasi'] = GETWA;
                    $result['text_konfirmasi'] = "Halo Bapak/Ibu, Silahkan melakukan pembayaran manual dengan 
            mengirimkan bukti transaksi.\n\nBerikut informasi tagihan anda : 
                \nNomor Invoice : *$invoice*
                \nJumlah     : *$total_format*
                \nBank Transfer : *$metode_pembayaran*
                \nNo Rekening : *$nomor_payment*
                \nAtas Nama : *$penerima_payment*
                \n\nJika ada pertanyaan lebih lanjut, anda dapat membalas langsung pesan ini.
                \n\nTerimakasih\nHormat Kami, 
                \n\nTim SatoeToko";

                    $conn->commit();
                    $response->code = 200;
                    $response->message = 'done';
                    $response->data = $result;
                    $response->json();
                    die();
                }
            }
        }
        break;
    case "addtransaksifree":
        $id_user            = $_POST['id_user'];
        $id_master          = $_POST['id_master'];
        $id_voucher         = $_POST['id_voucher'] ?? '';
        $id_payment         = $_POST['id_payment'];
        $status             = $_POST['status_pembelian'];
        $jumlahbayar        = $_POST['jumlahbayar'];
        $harga_normal       = $_POST['harga_normal'];
        $diskon             = $_POST['diskon'];
        $harga_diskon       = $_POST['harga_diskon'];

        $response = new Response();
        $exp_date = date("Y-m-d H:i:s", strtotime("+72 hours"));

        if ($id_payment != '0') {
            $status_payment = '2';
        } else {
            $status_payment = '1';
        }

        if ($diskon == 0) {
            $harga_diskon = 0;
        } else {
            $harga_diskon = (int)$data->harga_diskon;
        }

        $cekBarang = $conn->query("SELECT * FROM ebook_transaksi_detail a JOIN ebook_transaksi b WHERE a.id_master = '$id_master' AND a.tgl_expired >= NOW() AND b.status_transaksi = '7'");

        if (isset($cekBarang)) {
            $response->code = 400;
            $response->message = "Ebook ini masih aktif";
            $response->data = '';
            $response->json();
            die();
        } else {

            $conn->begin_transaction();

            $transaction = mysqli_fetch_object($conn->query("SELECT UUID_SHORT() as id"));
            $idtransaksi = createID('invoice', 'ebook_transaksi', 'TR');
            $invoice = id_ke_struk($idtransaksi);

            $data2 = mysqli_fetch_object($conn->query("SELECT b.lama_sewa FROM master_item a 
            JOIN master_ebook_detail b ON a.id_master = b.id_master
            JOIN kategori_sub c ON a.id_sub_kategori = c.id_sub 
            WHERE a.status_master_detail = '1' AND a.id_master = '$id_master'"));
            $lama = $data2->lama_sewa;

            $totalakhir = (int)$jumlahbayar - (int)$harga_diskon;

            $data[] = mysqli_query($conn, "INSERT INTO ebook_transaksi SET 
            id_transaksi = '$transaction->id',
            invoice = '$idtransaksi',
            id_user = '$id_user',
            tgl_pembelian = NOW(),
            status_transaksi = '7',
            status_payment = '$status_payment',
            tgl_dibayar = NOW(),
            batas_pembayaran = '$exp_date',
            total_pembayaran = '$jumlahbayar',
            kode_voucher = '$id_voucher',
            payment_type = '$id_payment',
            total_akhir_pembayaran = '$totalakhir'");

            if ($status == '1') {
                $data[] = $conn->query("INSERT INTO ebook_transaksi_detail SET 
            id_transaksi_detail = UUID_SHORT(),
            id_transaksi = '$transaction->id',
            id_user = '$id_user',
            id_master = '$id_master',
            harga_normal = '$harga_normal',
            diskon = '$diskon',
            harga_diskon = '$harga_diskon',
            status_pembelian = '$status',
            tgl_create = NOW()");
            } else if ($status == '2') {
                $data[] = $conn->query("INSERT INTO ebook_transaksi_detail SET 
            id_transaksi_detail = UUID_SHORT(),
            id_transaksi = '$transaction->id',
            id_user = '$id_user',
            id_master = '$id_master',
            harga_normal = '$harga_normal',
            diskon = '$diskon',
            harga_diskon = '$harga_diskon',
            status_pembelian = '$status',
            tgl_create = NOW(),
            tgl_expired = DATE_ADD(NOW(), 
        INTERVAL '$lama' DAY)");
            }

            $query = mysqli_query($conn, "SELECT * FROM metode_pembayaran WHERE id_payment = '$id_payment'")->fetch_assoc();
            $icon_payment = $geticonpayment . $query['icon_payment'];
            $metode_pembayaran = $query['metode_pembayaran'];
            $nomor_payment = $query['nomor_payment'];
            $penerima_payment = $query['penerima_payment'];

            if (in_array(false, $data)) {
                $response->code = 400;
                $response->message = mysqli_error($conn);
                $response->data = '';
                $response->json();
                die();
            } else {
                $conn->commit();
                $response->code = 200;
                $response->message = 'Selamat transaksi kamu telah berhasil.';
                $response->data = '';
                $response->json();
                die();
            }
        }
        break;
    case "cancel_transaksi":
        $id_user            = $_POST['id_user'];
        $id_transaksi       = $_POST['id_transaksi'];

        $query = mysqli_query($conn, "UPDATE ebook_transaksi SET status_transaksi = '9' WHERE id_transaksi = '$id_transaksi' AND id_user = '$id_user'");
        if ($query) {
            $response->code = 200;
            $response->message = 'Transaksi berhasil dibatalkan';
            $response->data = '';
            $response->json();
            die();
        } else {
            $response->code = 400;
            $response->message = 'Transaksi gagal dibatalkan';
            $response->data = '';
            $response->json();
            die();
        }
        break;
    case "list_ongoing":
        $id_user = $_GET['id_user'];

        $query = mysqli_query($conn, "SELECT a.id_transaksi, a.invoice, a.tgl_pembelian, a.batas_pembayaran, a.status_transaksi, a.total_pembayaran, a.total_akhir_pembayaran FROM ebook_transaksi a 
        JOIN ebook_transaksi_detail b ON a.id_transaksi = b.id_transaksi
        WHERE a.status_transaksi = '1' AND a.id_user = '$id_user' AND a.batas_pembayaran >= NOW() ORDER BY a.tgl_pembelian DESC;");
        $result = array();
        while ($row = mysqli_fetch_array($query)) {
            $status_transaksi = $row['status_transaksi'];
            $batas_pembayaran = date('Y-m-d H:i:s', strtotime($row['batas_pembayaran']));
            $waktu_sekarang = date('Y-m-d H:i:s');
            if ($status_transaksi == '1') {
                $keterangan = 'Menunggu Pembayaran';
            } else if ($status_transaksi == '1' and $batas_pembayaran >= $waktu_sekarang) {
                $keterangan = 'Pembayaran Hangus';
            } else if ($status_transaksi == '2') {
                $keterangan = 'Menunggu Verifikasi Pembayaran';
            } else if ($status_transaksi == '3') {
                $keterangan = 'Pembayaran Berhasil';
            } else if ($status_transaksi == '4') {
                $keterangan = 'Pembayaran Tidak Lengkap';
            } else if ($status_transaksi == '5') {
                $keterangan = 'Dikirim';
            } else if ($status_transaksi == '6') {
                $keterangan = 'Diterima';
            } else if ($status_transaksi == '7') {
                $keterangan = 'Transaksi Selesai';
            } else if ($status_transaksi == '8') {
                $keterangan = 'Expired';
            } else if ($status_transaksi == '9') {
                $keterangan = 'Dibatalkan';
            } else if ($status_transaksi == '10') {
                $keterangan = 'Pembayaran Ditolak';
            } else {
                $keterangan = 'Pengembalian Barang';
            }

            array_push($result, array(
                'id_transaksi'              => $row['id_transaksi'],
                'invoice'                => id_ke_struk($row['invoice']),
                'tgl_pembelian'                => date('d F Y h:i:s A', strtotime($row['tgl_pembelian'])),
                'batas_pembayaran'          => date('d F Y h:i:s A', strtotime($row['batas_pembayaran'])),
                'status_transaksi'              => $row['status_transaksi'],
                'keterangan_status'              => $keterangan,
                'total_pembayaran'              => (int)$row['total_pembayaran'],
            ));
        }

        if (isset($result[0])) {
            $response->code = 200;
            $response->message = 'result';
            $response->data = $result;
            $response->json();
            die();
        } else {
            $response->code = 200;
            $response->message = 'Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini';
            $response->data = [];
            $response->json();
            die();
        }
        break;
    case "list_riwayat":
        $id_user = $_GET['id_user'];

        $query = mysqli_query($conn, "SELECT a.id_transaksi, a.invoice, a.tgl_pembelian, a.batas_pembayaran, a.status_transaksi, a.total_pembayaran, a.total_akhir_pembayaran FROM ebook_transaksi a 
            JOIN ebook_transaksi_detail b ON a.id_transaksi = b.id_transaksi
            WHERE a.status_transaksi != '1' OR a.batas_pembayaran <= NOW() AND a.id_user = '$id_user' ORDER BY a.tgl_pembelian DESC;");

        $result = array();
        while ($row = mysqli_fetch_array($query)) {
            $status_transaksi = $row['status_transaksi'];
            if ($status_transaksi == '1') {
                $keterangan = 'Menunggu Pembayaran';
            } else if ($status_transaksi == '2') {
                $keterangan = 'Menunggu Verifikasi Pembayaran';
            } else if ($status_transaksi == '3') {
                $keterangan = 'Pembayaran Berhasil';
            } else if ($status_transaksi == '4') {
                $keterangan = 'Pembayaran Tidak Lengkap';
            } else if ($status_transaksi == '5') {
                $keterangan = 'Dikirim';
            } else if ($status_transaksi == '6') {
                $keterangan = 'Diterima';
            } else if ($status_transaksi == '7') {
                $keterangan = 'Transaksi Selesai';
            } else if ($status_transaksi == '8') {
                $keterangan = 'Expired';
            } else if ($status_transaksi == '9') {
                $keterangan = 'Dibatalkan';
            } else if ($status_transaksi == '10') {
                $keterangan = 'Pembayaran Ditolak';
            } else {
                $keterangan = 'Pengembalian Barang';
            }

            array_push($result, array(
                'id_transaksi'              => $row['id_transaksi'],
                'invoice'                => id_ke_struk($row['invoice']),
                'tgl_pembelian'                => date('d F Y h:i:s A', strtotime($row['tgl_pembelian'])),
                'batas_pembayaran'          => date('d F Y h:i:s A', strtotime($row['batas_pembayaran'])),
                'status_transaksi'              => $row['status_transaksi'],
                'keterangan_status'              => $keterangan,
                'total_pembayaran'              => (int)$row['total_pembayaran'],
            ));
        }

        if (isset($result[0])) {
            $response->code = 200;
            $response->message = 'result';
            $response->data = $result;
            $response->json();
            die();
        } else {
            $response->code = 200;
            $response->message = 'Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini';
            $response->data = [];
            $response->json();
            die();
        }
        break;
    case "detail":
        $id_transaksi = $_GET['id_transaksi'];

        $data = mysqli_fetch_object($conn->query("SELECT * FROM ebook_transaksi_detail a
        JOIN ebook_transaksi e ON a.id_transaksi = e.id_transaksi WHERE a.id_transaksi = '$id_transaksi';"));

        $cekppn = mysqli_fetch_object($conn->query("SELECT * FROM profile"));
        // $ppn = $cekppn->pajak;
        $ppn = "0";
        $status_transaksi = $data->status_transaksi;
        $status_payment = $data->status_payment;

        if ($status_transaksi == '1') {
            $keterangan = 'Menunggu Pembayaran';
        } else if ($status_transaksi == '2') {
            $keterangan = 'Menunggu Verifikasi Pembayaran';
        } else if ($status_transaksi == '3') {
            $keterangan = 'Pembayaran Berhasil';
        } else if ($status_transaksi == '4') {
            $keterangan = 'Pembayaran Tidak Lengkap';
        } else if ($status_transaksi == '5') {
            $keterangan = 'Dikirim';
        } else if ($status_transaksi == '6') {
            $keterangan = 'Diterima';
        } else if ($status_transaksi == '7') {
            $keterangan = 'Transaksi Selesai';
        } else if ($status_transaksi == '8') {
            $keterangan = 'Expired';
        } else if ($status_transaksi == '9') {
            $keterangan = 'Dibatalkan';
        } else if ($status_transaksi == '10') {
            $keterangan = 'Pembayaran Ditolak';
        } else {
            $keterangan = 'Pengembalian Barang';
        }

        $invoice = id_ke_struk($data->invoice);
        $tgl_pembelian = $data->tgl_pembelian;
        $subtotal = (int)$data->total_pembayaran;
        $potongan_voucher = (int)$data->potongan_voucher;
        $diskon = $data->diskon;
        $status_pembelian = $data->status_pembelian;
        if ($diskon == 0) {
            $harga_diskon = 0;
        } else {
            $harga_diskon = (int)$data->harga_diskon;
        }
        $total = $subtotal - ($potongan_voucher + $harga_diskon);

        $listebook = array();
        $ebooks = $conn->query("SELECT b.judul_master, c.nama_kategori, b.image_master, b.harga_master, b.harga_sewa, a.status_pembelian, b.diskon_persen, b.diskon_rupiah, b.diskon_sewa_persen, b.diskon_sewa_rupiah, e.lama_sewa FROM ebook_transaksi_detail a
        JOIN master_item b ON a.id_master = b.id_master
        JOIN kategori_sub c ON b.id_sub_kategori = c.id_sub
        JOIN kategori d ON c.parent_kategori = d.id_kategori
        JOIN master_ebook_detail e ON a.id_master = e.id_master WHERE a.id_transaksi = '$id_transaksi';");
        foreach ($ebooks as $key => $value) {
            switch ($value['status_pembelian']) {
                case '1':
                    $harga = $value['harga_master'];
                    $diskon_rupiah = $value['diskon_rupiah'];
                    $diskon_persen = $value['diskon_persen'];
                    break;
                case '2':
                    $harga = $value['harga_sewa'];
                    $diskon_rupiah = $value['diskon_sewa_rupiah'];
                    $diskon_persen = $value['diskon_sewa_persen'];
                    break;
            }

            array_push($listebook, array(
                'judul_master' => $value['judul_master'],
                'image_master' => $urlimg . $value['image_master'],
                'nama_kategori' => $value['nama_kategori'],
                'harga_master' => (int)$harga,
                'diskon_rupiah' => (int)$diskon_rupiah,
                'diskon_persen' => $diskon_persen . "%",
                'harga_master_total' => (int)$harga - (int)$diskon_rupiah,
                'lama_sewa' => $value['status_pembelian'] == '2' ? $value['lama_sewa'] : "",
            ));
        }

        // $totalppn = $total * ((int)$ppn / 100);
        $totalppn = 0;

        $data1['id_transaksi'] = $id_transaksi;
        $data1['invoice'] = $invoice;
        $data1['tgl_pembelian'] = date('d F Y h:i:s A', strtotime($data->tgl_pembelian));
        $data1['status_transaksi'] = $status_transaksi;
        $data1['status_penilaian'] = 'N';
        $data1['status_payment'] = $status_payment;
        $data1['keterangan'] = $keterangan;
        $data1['status_pembelian'] = $data->status_pembelian == '1' ? 'Beli' : 'Sewa';
        $data1['subtotal'] = (int)$subtotal;
        $data1['harga_diskon'] = $harga_diskon;
        $data1['diskon'] = $diskon;
        $data1['voucher'] = $potongan_voucher;
        $data1['ppn_persen'] = $ppn . "%";
        $data1['ppn_rupiah'] = (int)$totalppn;
        $data1['biaya_admin'] = 0;
        $data1['total'] = (int)$total + (int)$totalppn;
        $data1['produk'] = $listebook;

        if ($data) {
            $response->code = 200;
            $response->message = 'success';
            $response->data = $data1;
            $response->json();
            die();
        } else {
            $response->code = 200;
            $response->message = mysqli_error($conn);
            $response->data = [];
            $response->json();
            die();
        }
        break;
    case "payment_transaksi":
        $id_transaksi       = $_POST['id_transaksi'];

        $data = mysqli_fetch_object($conn->query("SELECT * FROM ebook_transaksi_detail a
        JOIN ebook_transaksi e ON a.id_transaksi = e.id_transaksi WHERE a.id_transaksi = '$id_transaksi'"));

        $cekppn = mysqli_fetch_object($conn->query("SELECT * FROM profile"));
        $ppn = $cekppn->pajak;
        $status_transaksi = $data->status_transaksi;
        $batas_pembayaran = $data->batas_pembayaran;

        $subtotal = (int)$data->harga_normal;
        $potongan_voucher = (int)$data->potongan_voucher;
        $diskon = $data->diskon;
        $status_pembelian = $data->status_pembelian;
        $status_payment = $data->status_payment;
        $url_payment = $data->url_payment;
        $token = $data->token_payment;

        if ($diskon == 0) {
            $harga_diskon = 0;
        } else {
            $harga_diskon = (int)$data->harga_diskon;
        }
        $total = $subtotal - ($potongan_voucher + $harga_diskon);

        if ($status_payment == '1') {
            $result['token'] = $token;
            $result['url_payment'] = $url_payment;
            $response->code = 200;
            $response->message = 'done';
            $response->data = $result;
            $response->json();
            die();
        } else if ($status_payment == '2') {
            $query = mysqli_query($conn, "SELECT * FROM metode_pembayaran WHERE id_payment = '$data->payment_type'")->fetch_assoc();
            $icon_payment = $geticonpayment . $query['icon_payment'];
            $metode_pembayaran = $query['metode_pembayaran'];
            $nomor_payment = $query['nomor_payment'];
            $penerima_payment = $query['penerima_payment'];

            $id_transaksi = $id_transaksi;
            $invoice = id_ke_struk($data->invoice);
            $id_payment = $data->payment_type;
            $icon_payment = $icon_payment;
            $metode_pembayaran = $metode_pembayaran;
            $nomor_payment = $nomor_payment;
            $penerima_payment = $penerima_payment;
            $total = $data->total_akhir_pembayaran;
            // $total = $subtotal - ($potongan_voucher + $harga_diskon);
            $totalppn = $total * ((int)$ppn / 100);
            // $totalakhir = $total + $totalppn;
            $totalakhir = $total;
            $total_format = "Rp" . number_format($totalakhir, 0, ',', '.');

            $result['batas_pembayaran'] = $batas_pembayaran;
            $result['id_transaksi'] = $id_transaksi;
            $result['invoice'] = $invoice;
            $result['status_payment'] = $status_payment;
            $result['id_payment'] = $id_payment;
            $result['icon_payment'] = $geticonpayment . $icon_payment;
            $result['metode_pembayaran'] = $metode_pembayaran;
            $result['nomor_payment'] = $nomor_payment;
            $result['penerima_payment'] = $penerima_payment;
            $result['total_harga'] = (int)$totalakhir;
            $result['nomor_konfirmasi'] = GETWA;
            $result['text_konfirmasi'] = "Halo Bapak/Ibu, Silahkan melakukan pembayaran manual dengan 
                mengirimkan bukti transaksi.\n\nBerikut informasi tagihan anda : 
                    \nNomor Invoice : *$invoice*
                    \nJumlah     : *$total_format*
                    \nBank Transfer : *$metode_pembayaran*
                    \nNo Rekening : *$nomor_payment*
                    \nAtas Nama : *$penerima_payment*
                    \n\nJika ada pertanyaan lebih lanjut, anda dapat membalas langsung pesan ini.
                    \n\nTerimakasih\nHormat Kami, 
                    \n\nTim SatoeToko";
            $response->code = 200;
            $response->message = 'done';
            $response->data = $result;
            $response->json();
            die();
        }

        break;
    default:
        break;
}

mysqli_close($conn);

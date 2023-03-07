<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_transaksi         = $_POST['id_transaksi'];

$exp_date = date("Y-m-d H:i:s", strtotime("+24 hours"));

if (isset($id_transaksi)) {
    $cektransaksinum = $conn->query("SELECT * FROM transaksi WHERE id_transaksi = '$id_transaksi' AND status_transaksi = '1'")->num_rows;

    $cektransaksi = $conn->query("SELECT * FROM transaksi WHERE id_transaksi = '$id_transaksi' AND status_transaksi = '1'")->fetch_assoc();

    $getuser = $conn->query("SELECT * FROM transaksi a JOIN data_user b ON a.id_user = b.id_login WHERE a.id_transaksi = '$id_transaksi' AND a.status_transaksi = '1'")->fetch_assoc();
    if ($cektransaksinum > 0) {

        $metode_pembayaran = $cektransaksi['metode_pembayaran'];

        if ($metode_pembayaran == '0') {
            $mtrans['transaction_details']['order_id'] = $cektransaksi['invoice'];
            $mtrans['transaction_details']['gross_amount'] = $cektransaksi['total_harga_setelah_diskon'];
            $mtrans['credit_card']['secure'] = true;
            $mtrans['customer_details']['first_name'] = $getuser['nama_user'];
            $mtrans['customer_details']['last_name'] = '';
            $mtrans['customer_details']['email'] = $getuser['email'];
            $mtrans['customer_details']['phone'] = $getuser['notelp'];
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

            $query2 = mysqli_query($conn, "UPDATE transaksi SET midtrans_token = '$payment_token', midtrans_redirect_url = '$payment_url' WHERE id_transaksi= '$id_transaksi'");

            if (!isset($responses['token'])) {
                $response->data = null;
                $response->error(500);
                die();
            }

            $response->data = $res;
            $response->sukses(200);
            die();
        } else {
            //! METODE PEMBAYARAN
            $query_payment = "SELECT * FROM metode_pembayaran WHERE id_payment = '$metode_pembayaran'";
            $getpayment = $conn->query($query_payment);
            $data_payment = $getpayment->fetch_object();

            $total_format = rupiah($cektransaksi['total_harga_setelah_diskon'] + $cektransaksi['harga_ongkir']);
            $jumlahbayar = $cektransaksi['total_harga_setelah_diskon'] + $cektransaksi['harga_ongkir'];

            $invoice =  $cektransaksi['invoice'];
            $metode_pembayaran =  $cektransaksi['invoice'];

            $result['batas_pembayaran'] = $cektransaksi['tanggal_exp'];
            $result['id_transaksi'] = $cektransaksi['id_transaksi'];
            $result['invoice'] = $cektransaksi['invoice'];
            $result['icon_payment'] = $data_payment->icon_payment;
            $result['metode_pembayaran'] = $data_payment->metode_pembayaran;
            $result['nomor_payment'] = $data_payment->nomor_payment;
            $result['penerima_payment'] = $data_payment->penerima_payment;
            $result['total_harga'] = (int)$jumlahbayar;
            $result['nomor_konfirmasi'] = GETWA;
            $result['text_konfirmasi'] = "Halo Bapak/Ibu, Silahkan melakukan pembayaran manual dengan 
            mengirimkan bukti transaksi.\n\nBerikut informasi tagihan anda : 
                \nNomor Invoice : *$invoice*
                \nJumlah     : *$total_format*
                \nBank Transfer : *$data_payment->metode_pembayaran*
                \nNo Rekening : *$data_payment->nomor_payment*
                \nAtas Nama : *$data_payment->penerima_payment*
                \n\nJika ada pertanyaan lebih lanjut, anda dapat membalas langsung pesan ini.
                \n\nTerimakasih\nHormat Kami, 
                \n\nTim SatoeToko";

            $response->data = $result;
            $response->sukses(200);
            die();
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

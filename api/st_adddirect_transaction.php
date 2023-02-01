<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$dataraw = json_decode(file_get_contents('php://input'));
$dataraw2 = json_decode(file_get_contents('php://input'), true);

$exp_date = date("Y-m-d H:i:s", strtotime("+24 hours"));

//? GET SERVER DATETIME
date_default_timezone_set('Asia/Jakarta');
$tanggal_sekarang = date('Y-m-d  H:i:s');

$conn->begin_transaction();

$transaction = mysqli_fetch_object($conn->query("SELECT UUID_SHORT() as id"));
$idtransaksi = createID('in_transaksi', 'transaksi', 'TR');
$invoice = id_ke_struk_fisik($idtransaksi);

//? ADDRESS
$query_alamat = "SELECT * FROM user_alamat WHERE id = '$dataraw->id_alamat'";
$getalamat = $conn->query($query_alamat);
$data_alamat = $getalamat->fetch_object();
$label_alamat = $data_alamat->label_alamat;
$alamat = $data_alamat->alamat;
$telepon_penerima = $data_alamat->telepon_penerima;
$nama_penerima = $data_alamat->nama_penerima;
$provinsi = $data_alamat->provinsi;
$kota = $data_alamat->kota;
$kecamatan = $data_alamat->kecamatan;
$kelurahan = $data_alamat->kelurahan;
$kodepos = $data_alamat->kodepos;
$gabung_alamat = $data_alamat->alamat
    . "," . $data_alamat->kelurahan . "," . $data_alamat->kecamatan . "," . $data_alamat->kota . "," . $data_alamat->provinsi . "," . $data_alamat->kodepos;

//? ONGKIR
$data_ongkir_layanan = $dataraw2["data_ongkir"]["layanan"];
$data_ongkir_kode = $dataraw2["data_ongkir"]["kode"];
$data_ongkir_produk = $dataraw2["data_ongkir"]["produk"];
$data_ongkir_harga = $dataraw2["data_ongkir"]["harga"];

//? LIST PRODUK
$dataproduk = $dataraw2["produk"][0];
if (empty($dataproduk['id_variant'])) {
    $que = "SELECT b.id_master, b.judul_master,b.image_master,a.id_variant,
            c.keterangan_varian,b.harga_master, b.diskon_rupiah, c.harga_varian, c.diskon_rupiah_varian, 
            a.qty, c.diskon_rupiah_varian, d.berat as berat_buku, e.berat as berat_fisik, 
            b.status_master_detail, a.id_gudang, COUNT(a.id) as jumlah_produk,
            f.id_supplier FROM user_keranjang a
            JOIN master_item b ON a.id_barang = b.id_master
            LEFT JOIN variant c ON a.id_variant = c.id_variant
            LEFT JOIN master_buku_detail d ON b.id_master = d.id_master
            LEFT JOIN master_fisik_detail e ON b.id_master = e.id_master
            LEFT JOIN supplier f ON b.id_supplier = f.id_supplier
            WHERE a.id_barang = '$dataproduk[id_master]'";
} else {
    $que = "SELECT a.id_master, a.id_variant, b.judul_master, b.image_master, a.keterangan_varian, b.harga_master, b.diskon_rupiah, a.harga_varian, a.diskon_rupiah_varian,
    b.status_master_detail, e.id_supplier, d.berat as berat_fisik, c.berat as berat_buku, f.id_supplier, f.id_warehouse
    FROM variant a 
    JOIN master_item b ON a.id_master = b.id_master 
    LEFT JOIN master_buku_detail c ON b.id_master = c.id_master
    LEFT JOIN master_fisik_detail d ON b.id_master = d.id_master
    LEFT JOIN supplier e ON b.id_supplier = e.id_supplier
    LEFT JOIN stok f ON a.id_variant = f.id_varian
    WHERE a.id_variant = '$dataproduk[id_variant]' AND a.id_master = '$dataproduk[id_master]' AND f.id_warehouse = '$dataraw->id_cabang'";
}
$getproduk = $conn->query($que)->fetch_object();

if ($getproduk->status_master_detail == '2') {
    $berat += $getproduk->berat_buku * $getproduk->qty;
    $berat_detail = $getproduk->berat_buku * $getproduk->qty;
} else if ($getproduk->status_master_detail == '3') {
    $berat += $getproduk->berat_fisik * $getproduk->qty;
    $berat_detail = $getproduk->berat_fisik * $getproduk->qty;
}

if (empty(trim($dataraw->catatan_pembeli))) {
    $dataraw->catatan_pembeli = NULL;
}

//! UPDATE TABLE TRANSAKSI 
$query[] = mysqli_query($conn, "INSERT INTO transaksi SET 
        id_transaksi = '$transaction->id',
        pembuat_transaksi = 'F',
        in_transaksi = '$idtransaksi',
        invoice = '$invoice',
        id_user = '$dataraw->id_user',
        tanggal_transaksi = NOW(),
        catatan_pembeli = '$dataraw->catatan_pembeli',
        label_alamat = '$label_alamat',
        alamat_penerima = '$gabung_alamat',
        kodepos = '$kodepos',
        nama_penerima = '$nama_penerima',
        telepon_penerima = '$telepon_penerima',
        total_harga_sebelum_diskon = '$dataraw->harga_normal',
        total_harga_setelah_diskon = '$dataraw->jumlahbayar',
        total_berat = '$berat',
        harga_ongkir = '$data_ongkir_harga',
        voucher_harga = 0,
        voucher_harga_persen = 0,
        voucher_ongkir = 0,
        kurir_pengirim = '$data_ongkir_layanan',
        kurir_code = '$data_ongkir_kode',
        kurir_service = '$data_ongkir_produk',
        id_cabang = '$dataraw->id_cabang',
        metode_pembayaran = '$dataraw->id_payment'");


if ($getproduk->id_variant) {
    $diskon = ($getproduk->harga_varian) - ($getproduk->diskon_rupiah_varian);
    $diskon_format = "Rp" . number_format($diskon, 0, ',', '.');
    $harga_varian = "Rp" . number_format($getproduk->harga_varian, 0, ',', '.');
    $getprodukcoba[] = [
        'id_master' => $getproduk->id_master,
        'judul_master' => $getproduk->judul_master,
        'image_master' => $getproduk->image_master,
        'id_variant' => $getproduk->id_variant,
        'keterangan_varian' => $getproduk->keterangan_varian != null ? $getproduk->keterangan_varian : "",
        'qty' => $dataraw->qty,
        'harga_produk' => "Rp" . number_format($getproduk->harga_varian, 0, ',', '.'),
        'harga_tampil' => $getproduk->diskon_rupiah_varian != 0 ? ($diskon_format) : $harga_varian
    ];

    //! INSERT TRANSAKSI DETAIL ADA VARIANT
    $harga_diskon = $getproduk->harga_varian - $getproduk->diskon_rupiah_varian;
    $feetoko = $harga_diskon - ($harga_diskon * ($getproduk->fee_admin / 100));
    $sbtotal = round($harga_diskon * $getproduk->qty);

    $query[] = $conn->query("INSERT INTO transaksi_detail SET 
        id_transaksi_detail = UUID_SHORT(),
        id_transaksi = '$transaction->id',
        id_barang = '$getproduk->id_variant',
        id_supplier = '$getproduk->id_supplier',
        harga_barang = $getproduk->harga_varian,
        diskon_barang = $getproduk->diskon_rupiah_varian,
        harga_diskon = $harga_diskon,
        jumlah_beli = $dataraw->qty,
        berat = $berat_detail,
        fee_toko = $feetoko,  
        sub_total = $sbtotal");

    //! UPDATE STOK PRODUCT
    $jml = $conn->query("SELECT jumlah FROM stok WHERE id_varian = '$getproduk->id_variant'")->fetch_assoc();
    $total_dibeli2 = $jml['jumlah'];
    $hasiljumlah = $jml['jumlah'] - $dataraw->qty;

    $query[] = $conn->query("UPDATE stok SET jumlah = '$hasiljumlah' WHERE id_varian = '$getproduk->id_variant'");

    //! UPDATE JUMLAH PEMBELIAN BARANG
    // $total_dibeli = $conn->query("SELECT total_dibeli FROM id_master = '$getproduk->id_master'")->fetch_assoc();
    // $total_dibeli3 = $total_dibeli['total_dibeli'];
    // $query[] = $conn->query("UPDATE master_item SET total_dibeli = '$total_dibeli3' + '$total_dibeli2' WHERE id_master = '$getproduk->id_master'");

    //! UPDATE STOK HISTORY PRODUCT
    $stokawal = $jml['jumlah'];
    $query[] = $conn->query("INSERT INTO stok_history SET 
        id_history = UUID_SHORT(),
        tanggal_input = NOW(),
        master_item = '$getproduk->id_master',
        varian_item = '$getproduk->id_variant',
        id_warehouse = '$getproduk->id_gudang',
        keterangan = 'TRANSAKSI MASUK',
        masuk = '0',
        keluar = '$dataraw->qty',
        stok_awal = '$stokawal',  
        stok_sekarang = '$hasiljumlah'");
} else {
    $diskon = ($getproduk->harga_master) - ($getproduk->diskon_rupiah);
    $diskon_format = "Rp" . number_format($diskon, 0, ',', '.');
    $harga_master = "Rp" . number_format($getproduk->harga_master, 0, ',', '.');
    $getprodukcoba[] = [
        'id_master' => $getproduk->id_master,
        'judul_master' => $getproduk->judul_master,
        'image_master' => $getproduk->image_master,
        'id_variant' => $getproduk->id_variant,
        'keterangan_varian' => $getproduk->keterangan_varian != null ? $getproduk->keterangan_varian : "",
        'qty' => $getproduk->qty,
        'harga_produk' => $getproduk->harga_master,
        'harga_tampil' => $getproduk->diskon_rupiah != 0 ? ($diskon_format) : $harga_master
    ];

    //! INSERT TRANSAKSI DETAIL TIDAK ADA VARIANT
    $harga_diskon = $getproduk->harga_master - $getproduk->diskon_rupiah;
    $feetoko = $harga_diskon - ($harga_diskon * ($getproduk->fee_admin / 100));
    $sbtotal = round($harga_diskon * $getproduk->qty);

    $query[] = $conn->query("INSERT INTO transaksi_detail SET 
        id_transaksi_detail = UUID_SHORT(),
        id_transaksi = '$transaction->id',
        id_barang = '$getproduk->id_master',
        id_supplier = '$getproduk->id_supplier',
        harga_barang = $getproduk->harga_master,
        diskon_barang = $getproduk->diskon_rupiah,
        harga_diskon = $harga_diskon,
        jumlah_beli = $dataraw->qty,
        berat = $berat_detail,
        fee_toko = $feetoko,  
        sub_total = $sbtotal");

    //! UPDATE STOK PRODUCT
    $jml = $conn->query("SELECT jumlah FROM stok WHERE id_barang = '$getproduk->id_master'")->fetch_assoc();
    $total_dibeli2 = $jml['jumlah'];
    $hasiljumlah = $jml['jumlah'] - $dataraw->qty;

    $query[] = $conn->query("UPDATE stok SET jumlah = '$hasiljumlah' WHERE id_barang = '$getproduk->id_master'");

    //! UPDATE JUMLAH PEMBELIAN BARANG
    // $total_dibeli = $conn->query("SELECT total_dibeli FROM id_master = '$getproduk->id_master'")->fetch_assoc();
    // $total_dibeli3 = $total_dibeli['total_dibeli'];
    // $query[] = $conn->query("UPDATE master_item SET total_dibeli = '$total_dibeli3' + '$total_dibeli2' WHERE id_master = '$getproduk->id_master'");

    //! UPDATE STOK HISTORY PRODUCT
    $stokawal = $jml['jumlah'];
    $query[] = $conn->query("INSERT INTO stok_history SET 
        id_history = UUID_SHORT(),
        tanggal_input = NOW(),
        master_item = '$getproduk->id_master',
        varian_item = '$getproduk->id_variant',
        id_warehouse = '$getproduk->id_gudang',
        keterangan = 'TRANSAKSI MASUK',
        masuk = '0',
        keluar = '$dataraw->qty',
        stok_awal = '$stokawal',  
        stok_sekarang = '$hasiljumlah'");
}

if (in_array(false, $query)) {
    $response->data = mysqli_error($conn);
    $response->error(400);
} else {

    foreach ($dataproduk as $key => $value) {
        $deleteCart = mysqli_query($conn, "DELETE FROM user_keranjang WHERE id = '$value[id_cart]'");
    }

    $querydata = mysqli_query($conn, "SELECT * FROM transaksi a 
            JOIN data_user b ON a.id_user = b.id_login
            WHERE a.id_transaksi = '$transaction->id'")->fetch_assoc();
    $invoice = $querydata['invoice'];
    $nama_user = $querydata['nama_user'];
    $payer_email = $querydata['email'];
    $no_telp = $querydata['notelp'];

    if ($dataraw2['id_payment'] == '0') {
        $mtrans['transaction_details']['order_id'] = $invoice;
        $mtrans['transaction_details']['gross_amount'] = $dataraw->jumlahbayar;
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

        // if ($responses) {
        //     if (@$responses['error_messages']) {
        //         $response->data = null;
        //         $response->message = $responses['error_messages'];
        //         $response->error(501);
        //     }
        // }

        $payment_url = $responses['redirect_url'];
        $payment_token = $responses['token'];
        $res['token'] = $payment_token;
        $res['url_payment'] = $payment_url;

        $query2 = mysqli_query($conn, "UPDATE transaksi SET midtrans_token = '$payment_token', midtrans_redirect_url = '$payment_url' WHERE id_transaksi= '$transaction->id'");

        if (!isset($responses['token'])) {
            $response->data = null;
            $response->error(500);
            die();
        }

        $conn->commit();
        $response->data = $res;
        $response->sukses(200);
        die();
    } else {
        //? METODE PEMBAYARAN
        $query_payment = "SELECT * FROM metode_pembayaran WHERE id_payment = '$dataraw->id_payment'";
        $getpayment = $conn->query($query_payment);
        $data_payment = $getpayment->fetch_object();
        $metode_pembayaran = $data_payment->metode_pembayaran;

        $total_format = "Rp" . number_format($jumlahbayar, 0, ',', '.');

        $result['batas_pembayaran'] = $exp_date;
        $result['id_transaksi'] = $idtransaksi;
        $result['in_transaksi'] = $idtransaksi;
        $result['invoice'] = $invoice;
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
                \nBank Transfer : *$metode_pembayaran*
                \nNo Rekening : *$nomor_payment*
                \nAtas Nama : *$penerima_payment*
                \n\nJika ada pertanyaan lebih lanjut, anda dapat membalas langsung pesan ini.
                \n\nTerimakasih\nHormat Kami, 
                \n\nTim SatoeToko";

        $conn->commit();
        $response->data = $result;
        $response->sukses(200);
        die();
    }
}

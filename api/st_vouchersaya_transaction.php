<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$iduser = $_GET['iduser'];

$q = $_GET['q'] ?? '';
!empty($q) ? $search = " AND b.nama_voucher LIKE '%$q%'" : $search = "";
// $data = $conn->query("SELECT * FROM voucher 
// 			WHERE tipe_voucher = '3'
//             AND tgl_mulai <= NOW()
//             AND tgl_berakhir >= NOW()");
// foreach ($data as $key => $value) {
//     $datalist[] = [
//         'idvoucher' => $value['idvoucher'],
//         'kode_voucher' => $value['kode_voucher'],
//         'nama_voucher' => $value['nama_voucher'],
//         'deskripsi_voucher' => $value['deskripsi_voucher'],
//         'nilai_voucher' => (int)$value['nilai_voucher'],
//         'minimal_transaksi' => (int)$value['minimal_transaksi'],
//         'tgl_mulai' => $value['tgl_mulai'],
//         'tgl_berakhir' => $value['tgl_berakhir'],
//         'status_voucher' => $value['status_voucher'],
//         'ket_status' => '',
//         'status_klaim' => true
//     ];
// }

$data = $conn->query("SELECT * FROM voucher_user a 
            JOIN voucher b ON a.idvoucher=b.idvoucher 
            WHERE a.iduser = '$iduser' $search
            AND b.tgl_mulai <= NOW()
            AND b.tgl_berakhir >= NOW() AND a.status_pakai = '0'");
foreach ($data as $key => $value) {

    $cekpunya = $conn->query("SELECT * FROM voucher_user WHERE iduser = '$iduser' AND idvoucher = '$value[idvoucher]'")->fetch_object();
    $cekpunya->iduser_voucher ? $status = True : $status = False;
    $ketstatus = statusvoucher($value['status_voucher']);
    $datalist[] = [
        'idvoucher' => $value['idvoucher'],
        'kode_voucher' => $value['kode_voucher'],
        'nama_voucher' => $value['nama_voucher'],
        'deskripsi_voucher' => $value['deskripsi_voucher'],
        'nilai_voucher' => (int)$value['nilai_voucher'],
        'minimal_transaksi' => (int)$value['minimal_transaksi'],
        'tgl_mulai' => $value['tgl_mulai'],
        'tgl_berakhir' => $value['tgl_berakhir'],
        'status_voucher' => $value['status_voucher'],
        'ket_status' => $ketstatus,
        'status_klaim' => $status
    ];
}

if (isset($datalist[0])) {
    $response->data = $datalist;
    $response->sukses(200);
} else {
    $response->data = [];
    $response->sukses(200);
}
die();

function statusvoucher($val = null)
{
    switch ($val) {
        case '1':
            $ketstatus = 'Diskon Ebook';
            break;
        case '2':
            $ketstatus = 'Gratis Ongkir';
            break;
        case '3':
            $ketstatus = 'Diskon';
            break;
        default:
            $ketstatus = 'Unknown';
            break;
    }
    return $ketstatus;
}

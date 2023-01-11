<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$data = [
  'layanan' => $_POST['layanan'],
  'kode' => $_POST['kode'],
  'produk' => $_POST['produk'],
  'estimasi' => $_POST['estimasi'],
  'harga' => $_POST['harga'],
  'subtotal' => $_POST['subtotal'],
];
// foreach ($data as $v) {
//   if (!empty($v)) {
//     $valid = '1';
//   } else {
//     $valid = '0';
//     break;
//   }
// }
// if ($valid == '1') {
//? ONGKIR
$dataongkir = [
  'layanan' => $data['layanan'],
  'kode' => $data['kode'],
  'produk' => $data['produk'],
  'estimasi' => $data['estimasi'],
  'harga' => $data['harga'],
];

$getdatatotal =
  [
    'subtotal' => (string) ($data['subtotal'] + $data['harga']),
    'subtotal_produk' => $data['subtotal'],
    'subtotal_pengiriman' => $data['harga'],
    'subtotal_diskon' => "0",
  ];


$data1['data_ongkir'] = $dataongkir;
$data1['data_price'] = $getdatatotal;

$response->data = $data1;
$response->sukses(200);
// } else {
//   $response->data = null;
//   $response->error(400);
// }
mysqli_close($conn);

<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$getmaintenance = $conn->query("SELECT * FROM maintenance")->fetch_object();
$result = [
    'idmaintenance' => $getmaintenance->idmaintenance,
    'versi_aplikasi' => $getmaintenance->versi_aplikasi,
    'deskripsi' => $getmaintenance->deskripsi,
    'status' => $getmaintenance->status,
    'status_ket' => $getmaintenance->status == '0' ? 'Tidak sedang Maintenance' : 'Sedang Maintenance'
];

$response->data = $result;
$response->sukses(200);

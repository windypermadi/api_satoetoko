<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$dataraw = json_decode(file_get_contents('php://input'));

$query_alamat = "SELECT * FROM data_provinsi";
$getalamat = $conn->query($query_alamat);
if ($getalamat->num_rows < 1) {
    $response->data = null;
    $response->error(400);
}

foreach ($getalamat as $key => $value) {
    $result2[] = [
        'nama_provinsi' => $value['nama_provinsi']
    ];
}

$result['status']['code'] = 200;
$result['status']['text'] = 'Request Success';
$result['status']['description'] = '';
$result['content'] = $result2;

echo json_encode($result);

// $response->data = $result;
// $response->sukses(200);
die();

mysqli_close($conn);

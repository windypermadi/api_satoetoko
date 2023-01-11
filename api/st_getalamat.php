<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_login         = $_REQUEST['id_login'];

if (isset($id_login)) {

    $query_alamat = "SELECT * FROM user_alamat WHERE id_user = '$id_login' AND delete_status = 'N'";
    $getalamat = $conn->query($query_alamat);
    if ($getalamat->num_rows < 1) {
        $response->data = [];
        $response->sukses(200);
    }
    // $row = $result->fetch_array(MYSQLI_ASSOC);
    $rows = array();
    foreach ($getalamat as $key => $value) {
        array_push($rows, array(
            'id' => $value['id'],
            'nama_penerima' => $value['nama_penerima'],
            'telepon_penerima' => $value['telepon_penerima'],
            'alamat' => $value['alamat'] . "," . $value['kelurahan']
                . "," . $value['kecamatan'] . "," . $value['kota'] . "," . $value['provinsi'] . "," . $value['kodepos'],
            'status_alamat_utama' => $value['status_alamat_utama'],
        ));
    }

    // if ($rows) {
    //     $response->data = $rows;
    //     $response->sukses(200);
    // } else {
    //     $response->data = null;
    //     $response->error(200);
    // }
    // $result = $rows[0] ? 'sukses' : 'error';
    $response->data = $rows[0] ? $rows : null;
    $response->sukses(200);
} else {
    $response->data = null;
    $response->error(400);
}
die();
mysqli_close($conn);

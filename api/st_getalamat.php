<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_login   = $_REQUEST['id_login'];
$id         = $_REQUEST['id'] ?? '';

if (isset($id_login)) {

    if (!empty($id)) {
        $query_alamat = "SELECT * FROM user_alamat WHERE id_user = '$id_login' AND id = '$id' AND delete_status = 'N'";
        $getalamat = $conn->query($query_alamat)->fetch_object();

        $data1['id'] = $getalamat->id;
        $data1['provinsi'] = $getalamat->provinsi;
        $data1['kota'] = $getalamat->kota;
        $data1['kecamatan'] = $getalamat->kecamatan;
        $data1['kelurahan'] = $getalamat->kelurahan;
        $data1['alamat'] = $getalamat->alamat;
        $data1['kodepos'] = $getalamat->kodepos;
        $data1['telepon_penerima'] = $getalamat->telepon_penerima;
        $data1['nama_penerima'] = $getalamat->nama_penerima;
        $data1['label_alamat'] = $getalamat->label_alamatw hwu  ;
        $data1['status_alamat_utama'] = $getalamat->status_alamat_utama;
        $data1['status_alamat_pengembalian'] = $getalamat->status_alamat_pengembalian;

        if ($data1) {
            $response->data = $data1;
            $response->sukses(200);
        } else {
            $response->data = null;
            $response->error(200);
        }
    } else {
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
        $response->data = $rows[0] ? $rows : null;
        $response->sukses(200);
    }
} else {
    $response->data = null;
    $response->error(400);
}
die();
mysqli_close($conn);

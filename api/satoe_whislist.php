<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$tag = $_POST['tag'];

switch ($tag) {
    case "add":
        $id_master = $_POST['id_master'];
        $id_user = $_POST['id_user'];

        $cekdata = $conn->query("SELECT * FROM whislist_product WHERE id_master = '$id_master' AND id_login = '$id_user'")->num_rows;
        if ($cekdata > 0) {
            $data = $conn->query("DELETE FROM whislist_product WHERE id_master = '$id_master' AND id_login = '$id_user'");
            $pesan = 'Berhasil menghapus dari favorit';
        } else {
            $data = $conn->query("INSERT INTO whislist_product SET id_whislist = UUID_SHORT(),id_master = '$id_master', id_login = '$id_user'");
            $pesan = 'Berhasil menambahkan ke favorit';
        }

        if ($data) {
            $response->code = 200;
            $response->message = $pesan;
            $response->data = '';
            $response->json();
            die();
        } else {
            $response->code = 400;
            $response->data = '';
            $response->json();
            die();
        }
        break;
}

mysqli_close($conn);

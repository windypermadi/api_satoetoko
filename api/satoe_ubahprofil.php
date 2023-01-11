<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_user               = $_POST['id_user'];
$nama_user             = $_POST['nama_user'] ?? '';
$notelp                = $_POST['notelp'] ?? '';
$getdata = mysqli_query($conn, "SELECT * FROM data_user WHERE id_login = '$id_user'")->fetch_assoc();

if (!empty($_FILES['profil_user'])) {

    if (isset($_FILES['profil_user']['type'])) {
        $nama_file  = random_word(10) . ".png";
        $lokasi     = $_FILES['profil_user']['tmp_name'];

        if (move_uploaded_file($lokasi, "../../assets/images/pict-profile/" . $nama_file)) {
            $query = mysqli_query($conn, "UPDATE data_user SET nama_user = '$nama_user', notelp = '$notelp', profil_user = '$nama_file' WHERE id_login = '$id_user'");
            if ($query) {
                $result['id_user'] = $id_user;
                $result['nama_user'] = $nama_user;
                $result['notelp'] = $notelp;
                $result['email'] = $getdata['email'];
                $result['profil_user'] = $getprofile . $nama_file;

                $response = new Response();
                $response->code = 200;
                $response->message = 'Berhasil mengedit profil kamu';
                $response->data = $result;
                $response->json();
                die();
            } else {
                sendError("Gagal mengedit profil kamu");
            }
        } else {
            sendError("Upload file mengalami kegagalan");
        }
    } else {
        sendError("Format gambar tidak diperbolehkan");
    }
} else {
    $query = mysqli_query($conn, "UPDATE data_user SET nama_user = '$nama_user', notelp = '$notelp' WHERE id_login = '$id_user'");
    if ($query) {
        $result['id_user'] = $id_user;
        $result['nama_user'] = $nama_user;
        $result['notelp'] = $notelp;
        $result['email'] = $getdata['email'];
        $result['profil_user'] = $getprofile . $getdata['profil_user'];

        $response = new Response();
        $response->code = 200;
        $response->message = 'Berhasil mengedit profil kamu';
        $response->data = $result;
        $response->json();
        die();
    } else {
        sendError("Gagal mengedit profil kamu");
    }
}

mysqli_close($conn);

function sendError($msg)
{
    $response = new Response();
    $response->code = 400;
    $response->message = $msg;
    $response->data = '';
    $response->json();
    die();
}

function sendSuccess($msg)
{
    $response = new Response();
    $response->code = 200;
    $response->message = $msg;
    $response->data = '';
    $response->json();
    die();
}

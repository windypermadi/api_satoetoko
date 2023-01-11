<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$password_lama             = $_POST['password_lama'];
$password_baru             = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);
$id_user                   = $_POST['id_user'];

$cek = mysqli_query($conn, "SELECT password FROM data_user WHERE id_login = '$id_user' AND status_aktif = 'Y'")->fetch_assoc();
$cekpasswordlama = $cek['password'];

if (password_verify($password_lama, $cekpasswordlama)) {
    $update = mysqli_query($conn, "UPDATE data_user SET password = '$password_baru' WHERE id_login = '$id_user' AND status_aktif  = 'Y' AND status_remove  = 'N'");
    if ($update) {
        sendSuccess('Berhasil mengganti password kamu');
    } else {
        sendError('Gagal mengganti password kamu');
    }
} else {
    sendError('Password lama kamu salah silahkan coba lagi');
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

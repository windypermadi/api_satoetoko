<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_user             = $_REQUEST['id_user'];

$cek = mysqli_query($conn, "SELECT * FROM data_user WHERE id_login = '$id_user' AND status_aktif = 'Y' AND status_remove = 'N'")->num_rows;

if ($cek == 0) {
    sendError("Akun tidak ada");
} else {
    $update = mysqli_query($conn, "UPDATE data_user SET status_aktif = 'N', delete_at = NOW() WHERE id_login = '$id_user'");
    if ($update) {
        sendSuccess('Akun kamu berhasil dinonaktifkan, kamu bisa mengaktifkan akun kamu lagi untuk login sebelum 30 hari.');
    } else {
        sendError('Gagal menonaktifkan akun');
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

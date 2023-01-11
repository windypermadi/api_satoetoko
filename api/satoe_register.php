<?php
require_once('../config/koneksi.php');
include "response.php";
require 'PHPMailerold/PHPMailerAutoload.php';
$response = new Response();

$email_login            = $_POST['email_login'];
$no_hp                  = $_POST['no_hp'];
$nama                   = $_POST['nama'];
$pass_login             = password_hash($_POST['pass_login'], PASSWORD_DEFAULT);
$url_cek_token          = 'http://satoetoko.com/satoetoko_api/api/email/url_verifikasi_email.php?action=cek_token&token=' . $email_login;

$cek_email = mysqli_query($conn, "SELECT email FROM data_user WHERE email = '$email_login' AND status_aktif  = 'Y' AND status_remove  = 'N'")->num_rows;
$cek_hp = mysqli_query($conn, "SELECT notelp FROM data_user WHERE notelp = '$no_hp' AND status_aktif  = 'Y' AND status_remove  = 'N'")->num_rows;

if ($cek_email > 0) {
    $response->code = 400;
    $response->message = 'Email sudah terpakai mohon untuk menggunakan email yang berbeda!\nKlik `Mengerti` untuk menutup pesan ini';
    $response->data = '';
    $response->json();
    die();
} else {
    if ($cek_hp > 0) {
        $response->code = 400;
        $response->message = 'Nomor HP sudah terpakai mohon untuk menggunakan nomor HP yang berbeda!\nKlik `Mengerti` untuk menutup pesan ini';
        $response->data = '';
        $response->json();
        die();
    } else {
        $query = mysqli_query($conn, "INSERT INTO `data_user`(`id_login`, `nama_user`, `email`, `notelp`, `password`, `status_provider`)
        VALUES (UUID_SHORT(), '$nama', '$email_login', '$no_hp', '$pass_login', '1')");

        if ($query) {

            $response->code = 200;
            $response->message = 'Yey registrasi kamu berhasil.\n\nSelamat anda sudah berhasil mendaftar. Silahkan Login menggunakan Email dan Password yang sudah anda daftarkan. Terimakasih.';
            $response->data = '';
            $response->json();
            die();
        } else {
            $response->code = 400;
            $response->message = 'Gagal menambahkan Anggota baru!\nKlik `Mengerti` untuk menutup pesan ini';
            $response->data = '';
            $response->json();
            die();
        }
    }
}
mysqli_close($conn);

<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$email_login            = $_POST['email_login'];
$pass_login             = $_POST['pass_login'];

$cek_email = mysqli_query($conn, "SELECT * FROM data_user WHERE (email = '$email_login' OR notelp = '$email_login') AND status_aktif  = 'Y' AND status_remove  = 'N'")->num_rows;

if ($cek_email == 0) {
	$response->code = 400;
	$response->message = 'Akun ini tidak terdaftar, harap untuk melakukan registrasi terlebih dahulu!\nKlik `Mengerti` untuk menutup pesan ini';
	$response->data = '';
	$response->json();
	die();
} else {
	$cek_password = mysqli_query($conn, "SELECT id_login,password FROM data_user WHERE (email = '$email_login' OR notelp = '$email_login') AND status_aktif  = 'Y' AND status_remove  = 'N'")->fetch_assoc();
	$id_login = $cek_password['id_login'];
	if (password_verify($pass_login, $cek_password['password'])) {
		$update = mysqli_query($conn, "UPDATE data_user SET login_at = NOW() WHERE id_login = '$id_login' AND status_aktif  = 'Y' AND status_remove  = 'N'");
		if ($update) {
			$sql   = mysqli_query($conn, "SELECT * FROM data_user WHERE (email = '$email_login' OR notelp = '$email_login') AND status_aktif  = 'Y' AND status_remove  = 'N'")->fetch_assoc();

			$result['id_user']         = $sql['id_login'];
			$result['nama_user']       = $sql['nama_user'];
			$result['email']           = $sql['email'];
			$result['notelp']          = $sql['notelp'];
			$result['profil_user']     = $getprofile . $sql['profil_user'];

			$response->code = 200;
			$response->message = 'result';
			$response->data = $result;
			$response->json();
			die();
		}
	} else {
		$response->code = 400;
		$response->message = 'Password anda salah silahkan coba lagi!\nKlik `Mengerti` untuk menutup pesan ini';
		$response->data = '';
		$response->json();
		die();
	}
}

mysqli_close($conn);

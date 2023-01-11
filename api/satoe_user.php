<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id_user = $_POST['id_user'];

$data = mysqli_fetch_object($conn->query("SELECT *, (login_at + INTERVAL '1' MONTH) as tanggal_login FROM data_user WHERE id_login = '$id_user' AND status_aktif = 'Y' AND status_remove = 'N'"));
$sekarang = mysqli_query($conn, "SELECT NOW() as sekarang")->fetch_assoc();
if ($sekarang['sekarang'] <= $data->tanggal_login) {
	$data1['id_user']          = $data->id_login;
	$data1['nama_user']        = $data->nama_user;
	$data1['email']            = $data->email;
	$data1['notelp']           = $data->notelp;
	$data1['profil_user']      = $getprofile . $data->profil_user;
	$update = mysqli_query($conn, "UPDATE data_user SET login_at = NOW() WHERE id_login = '$data->id_login' AND status_aktif  = 'Y' AND status_remove  = 'N'");

	if ($update) {
		$response->code = 200;
		$response->message = 'result';
		$response->data = $data1;
		$response->json();
		die();
	}
} else {
	$response->code = 400;
	$response->message = 'Login sudah berakhir. Silahkan login ulang';
	$response->data = '';
	$response->json();
	die();
}
mysqli_close($conn);

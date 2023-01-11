<?php 
require_once('koneksi.php');

$id_user  = $_GET['id_user'];

$refer_by = generate_referal_lagi();
$query = mysqli_query($conn, "UPDATE loginuser_bahana SET ba_referal = '$refer_by' WHERE id_user = '$id_user'");

if ($query){
	$respon['pesan'] = "Yeyy kamu berhasil perbarui kode referalmu.\n\nKlik `OK` untuk menutup pemberitahuan ini.";
	die(json_encode($respon));
} else{ 
	http_response_code(400);
	$respon['pesan'] = "Yahh kamu gagal perbarui kode referalmu!\nKlik `Mengerti` untuk menutup pesan ini";
	die(json_encode($respon)); 
}

mysqli_close($conn);

?>
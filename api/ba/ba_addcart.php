<?php 
require_once('koneksi.php');

$id_user = $_POST['id_user'];
$id_buku = $_POST['id_buku'];

$cek_keranjang = mysqli_query($conn,"SELECT * FROM ba_keranjang_temp WHERE id_user = '$id_user' AND id_buku = '$id_buku'")->num_rows;

if ($cek_keranjang > 0){
	http_response_code(400);
	$respon['pesan'] = "Ebook ini sudah ada di keranjang kamu lho!\nKlik `Mengerti` untuk menutup pesan ini";
	die(json_encode($respon)); 
} else {
	$query = mysqli_query($conn, "INSERT INTO `ba_keranjang_temp`(`id_cart`, `id_buku`, `id_user`)
		VALUES (UUID(), '$id_buku', '$id_user')");
	if ($query){
		$respon['pesan'] = "Ebook berhasil masuk dalam keranjang.\nHKlik mengerti untuk menutup pesan ini.";
		die(json_encode($respon));
	} else{ 
		http_response_code(400);
		$respon['pesan'] = "Gagal menambahkan ebook dalam keranjang!\nKlik `Mengerti` untuk menutup pesan ini";
		die(json_encode($respon)); 
	}
}

mysqli_close($conn);

?>
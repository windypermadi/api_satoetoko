<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$q = $_GET['q'] ?? '';

if (!empty($q)) {
	$search = " AND a.nama_kategori LIKE '%$q%'";
} else {
	$search = "";
}

$query = mysqli_query($conn, "SELECT a.id_sub, a.kode_kategori, a.nama_kategori, a.icon FROM kategori_sub a 
JOIN kategori b ON a.parent_kategori = b.id_kategori
JOIN master_item c ON c.id_sub_kategori = a.id_sub
WHERE b.jenis_kategori = '1' AND c.status_master_detail = '1' $search GROUP BY a.id_sub ORDER BY a.nama_kategori ASC");

$result = array();
while ($row = mysqli_fetch_array($query)) {
	array_push($result, array(
		'id_sub'			=> $row['id_sub'],
		'kode_kategori'		=> $row['kode_kategori'],
		'nama_kategori'	    => $row['nama_kategori'],
		'icon'		        => $geticonkategori . $row['icon'],
	));
}

if (isset($result[0])) {
	$response->code = 200;
	$response->message = 'result';
	$response->data = $result;
	$response->json();
	die();
} else {
	$response->code = 200;
	$response->message = 'Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini';
	$response->data = [];
	$response->json();
	die();
}
mysqli_close($conn);

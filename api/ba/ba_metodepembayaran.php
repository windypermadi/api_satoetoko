<?php 
require_once('koneksi.php');

$query = mysqli_query($conn, "SELECT * from ba_metode_pembayaran");
$result = array();
while($row = mysqli_fetch_array($query)){
	array_push($result,array(
		'id_metode'	=> $row['id_metode'],
		'nama_metode'	=> $row['nama_metode'],
		'deskripsi'     => $row['deskripsi'],
		'icon'     => $row['icon'],
		'no_rekening' => $row['no_rekening'],

	));
}

echo json_encode($result);

mysqli_close($conn);

?>
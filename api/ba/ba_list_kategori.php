<?php 
require_once('koneksi.php');

$grand_jumlah = 0;
$query = mysqli_query($conn, "SELECT * ,
	(SELECT COUNT(id_buku) AS jml FROM ba_buku WHERE kd_kategori = kb.kode_kategori AND status_remove = 'N') AS jml
	FROM itemkategorinew AS kb ORDER BY kb.nama_kategori");
$result = array();
while($row = mysqli_fetch_array($query)){
	$grand_jumlah += $row['jml'];
	if($row['jml'] != 0){
		array_push($result,array(
			'id_kategori'	=> $row['kode_kategori'],
			'jumlah_buku'	=> $row['jml'],
			'nama_kategori'     => $row['nama_kategori'],
			'icon'	=> $geticonkategori.$row['icon'],
		));
	}
}

echo json_encode($result);

mysqli_close($conn);

?>
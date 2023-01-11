<?php 
require_once('koneksi.php');
//berisi 1 fisik 
// 2 ebook
$idstatus = $_GET['idstatus'];
$cari = $_GET['cari'];

if ($idstatus == '1'){
	$query = mysqli_query($conn, "SELECT * FROM itemmaster AS a
		INNER JOIN itemkategorinew AS b ON a.kdkategori = b.id_kategori 
		WHERE (a.judul LIKE '%$cari%'
		OR a.penulis LIKE '%$cari%'
		OR b.nama_kategori LIKE '%$cari%') 
		AND a.status_display  = 'Y'");
	$result = array();
	while($row = mysqli_fetch_array($query)){
		array_push($result,array(
			'id_buku'		=> $row['id_barang'],
			'harga_format'  => "Rp" . number_format($row['harga'],0,',','.'),
			'judul'			=> $row['judul'],
			'penulis'		=> $row['penulis'],
			'cover1'		=> $urlimg."/".$row['gambar1'],
			'cover2'		=> $urlimg."/".$row['gambar2'],
			'cover3'		=> $urlimg."/".$row['gambar3'],
			'tahun'     	=> $row['tahun'],
			'nama_kategori' => $row['nama_kategori'],
		));
	}

	echo json_encode(array(
			'data'	=> $result
		));

} else {
	$query = mysqli_query($conn, "SELECT * FROM ba_buku AS b
		INNER JOIN itemkategorinew AS k ON b.kdkategori=k.id_kategori 
		WHERE (b.judul LIKE '%$cari%'
		OR b.penulis LIKE '%$cari%'
		OR k.nama_kategori LIKE '%$cari%') 
		AND b.status_remove = 'N'
		AND b.status_gratis = 'N'");
	$result = array();
	while($row = mysqli_fetch_array($query)){
		array_push($result,array(
			'id_buku'				=> $row['id_buku'],	
			'harga_format'     		=> "Rp" . number_format($row['harga'],0,',','.'),
			'harga_sewa_format'		=> "Rp" . number_format($row['harga_sewa'],0,',','.'),
			'harga_potongan_format'	=> "Rp" . number_format($harga_potongan,0,',','.'),
			'jumlah_diskon' 		=> (string)$row['diskon_beli']."%",
			'judul'					=> $row['judul'],
			'penulis'				=> $row['penulis'],
			'cover'					=> $urlimg."/".$row['cover'],
			'nama_kategori'     	=> $row['nama_kategori'],
			'tgl_posting'   		=> date('d F Y h:i:s A', strtotime($row['tgl_posting'])),
		));
	}

	echo json_encode(array(
		'data'	=> $result
	));
}

mysqli_close($conn);

?>
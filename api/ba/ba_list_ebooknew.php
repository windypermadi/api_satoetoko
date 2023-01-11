<?php 
require_once('koneksi.php');

$majalah = mysqli_query($conn, "SELECT * FROM ba_buku a 
	INNER JOIN itemkategorinew b 
	ON a.kd_kategori = b.kode_kategori WHERE a.status_remove = 'N' AND a.kd_kategori_menu = 'M06' ORDER BY a.id_buku DESC LIMIT 10");
$renungan = mysqli_query($conn, "SELECT * FROM ba_buku a 
	INNER JOIN itemkategorinew b 
	ON a.kd_kategori = b.kode_kategori WHERE a.status_remove = 'N' AND a.kd_kategori_menu = 'M07' ORDER BY a.id_buku DESC LIMIT 10");
$terbaru = mysqli_query($conn, "SELECT * FROM ba_buku a 
	INNER JOIN itemkategorinew b 
	ON a.kd_kategori = b.kode_kategori WHERE a.status_remove = 'N' ORDER BY a.id_buku DESC LIMIT 10");
$sewa = mysqli_query($conn, "SELECT * FROM ba_buku a 
	INNER JOIN itemkategorinew b 
	ON a.kd_kategori = b.kode_kategori WHERE a.bisa_sewa = 'Y' AND a.status_remove = 'N' AND a.kd_kategori_menu NOT IN ('M06', 'M07') ORDER BY a.id_buku DESC LIMIT 10");
$beli = mysqli_query($conn, "SELECT * FROM ba_buku a 
	INNER JOIN itemkategorinew b 
	ON a.kd_kategori = b.kode_kategori WHERE a.bisa_beli = 'Y' AND a.status_remove = 'N' AND a.kd_kategori_menu NOT IN ('M06', 'M07') ORDER BY a.judul ASC LIMIT 10");

$hasil_majalah = array();
while($data_majalah = mysqli_fetch_array($majalah)){
	if(!empty($data_majalah['diskon_beli'])){
		(float)$harga_potongan = (float)$data_majalah['harga'] * ((float)$data_majalah['diskon_beli']/100);
		(float)$harga_disc = $data_majalah['harga'] - $harga_potongan;
		$status_diskon = 'Y';
	}
	array_push($hasil_majalah,array(
		'id_buku'	=> $data_majalah['id_buku'],	
		'bisa_beli'	=> $data_majalah['bisa_beli'],
		'bisa_sewa'	=> $data_majalah['bisa_sewa'],
		'harga_format'     => "Rp" . number_format($data_majalah['harga'],0,',','.'),
		'harga_sewa_format'=> "Rp" . number_format($data_majalah['harga_sewa'],0,',','.'),
		'jumlah_diskon_beli' => $data_majalah['diskon_beli'],
		'jumlah_diskon_sewa' => $data_majalah['diskon_sewa'],
		'harga_beli_diskon_format'=> "Rp" . number_format($data_majalah['harga_beli_setelah_diskon'],0,',','.'),
		'harga_sewa_diskon_format'=> "Rp" . number_format($data_majalah['harga_sewa_setelah_diskon'],0,',','.'),
		'judul'		=> $data_majalah['judul'],
		'penulis'	=> $data_majalah['penulis'],
		'cover'		=> $urlimg."/".$data_majalah['cover'],
		'nama_kategori'     => $data_majalah['nama_kategori'],
		'tgl_posting'   => date('d F Y h:i:s A', strtotime($data_majalah['tgl_posting'])),
	));
}

$hasil_renungan = array();
while($data_renungan = mysqli_fetch_array($renungan)){
	if(!empty($data_renungan['diskon_beli'])){
		(float)$harga_potongan = (float)$data_renungan['harga'] * ((float)$data_renungan['diskon_beli']/100);
		(float)$harga_disc = $data_renungan['harga'] - $harga_potongan;
		$status_diskon = 'Y';
	}
	array_push($hasil_renungan,array(
		'id_buku'	=> $data_renungan['id_buku'],	
		'bisa_beli'	=> $data_renungan['bisa_beli'],
		'bisa_sewa'	=> $data_renungan['bisa_sewa'],
		'harga_format'     => "Rp" . number_format($data_renungan['harga'],0,',','.'),
		'harga_sewa_format'=> "Rp" . number_format($data_renungan['harga_sewa'],0,',','.'),
		'jumlah_diskon_beli' => $data_renungan['diskon_beli'],
		'jumlah_diskon_sewa' => $data_renungan['diskon_sewa'],
		'harga_beli_diskon_format'=> "Rp" . number_format($data_renungan['harga_beli_setelah_diskon'],0,',','.'),
		'harga_sewa_diskon_format'=> "Rp" . number_format($data_renungan['harga_sewa_setelah_diskon'],0,',','.'),
		'judul'		=> $data_renungan['judul'],
		'penulis'	=> $data_renungan['penulis'],
		'cover'		=> $urlimg."/".$data_renungan['cover'],
		'nama_kategori'     => $data_renungan['nama_kategori'],
		'tgl_posting'   => date('d F Y h:i:s A', strtotime($data_renungan['tgl_posting'])),
	));
}

$hasil_terbaru = array();
while($data_terbaru = mysqli_fetch_array($terbaru)){
	if(!empty($data_terbaru['diskon_beli'])){
		(float)$harga_potongan = (float)$data_terbaru['harga'] * ((float)$data_terbaru['diskon_beli']/100);
		(float)$harga_disc = $data_terbaru['harga'] - $harga_potongan;
		$status_diskon = 'Y';
	}
	array_push($hasil_terbaru,array(
		'id_buku'	=> $data_terbaru['id_buku'],	
		'bisa_beli'	=> $data_terbaru['bisa_beli'],
		'bisa_sewa'	=> $data_terbaru['bisa_sewa'],
		'harga_format'     => "Rp" . number_format($data_terbaru['harga'],0,',','.'),
		'harga_sewa_format'=> "Rp" . number_format($data_terbaru['harga_sewa'],0,',','.'),
		'jumlah_diskon_beli' => $data_terbaru['diskon_beli'],
		'jumlah_diskon_sewa' => $data_terbaru['diskon_sewa'],
		'harga_beli_diskon_format'=> "Rp" . number_format($data_terbaru['harga_beli_setelah_diskon'],0,',','.'),
		'harga_sewa_diskon_format'=> "Rp" . number_format($data_terbaru['harga_sewa_setelah_diskon'],0,',','.'),
		'judul'		=> $data_terbaru['judul'],
		'penulis'	=> $data_terbaru['penulis'],
		'cover'		=> $urlimg."/".$data_terbaru['cover'],
		'nama_kategori'     => $data_terbaru['nama_kategori'],
		'tgl_posting'   => date('d F Y h:i:s A', strtotime($data_terbaru['tgl_posting'])),
	));
}
$hasil_beli = array();
while($data_beli = mysqli_fetch_array($beli)){
	if(!empty($data_beli['diskon_beli'])){
		(float)$harga_potongan = (float)$data_beli['harga'] * ((float)$data_beli['diskon_beli']/100);
		(float)$harga_disc = $data_beli['harga'] - $harga_potongan;
		$status_diskon = 'Y';
	}
	array_push($hasil_beli,array(
		'id_buku'	=> $data_beli['id_buku'],	
		'bisa_beli'	=> $data_beli['bisa_beli'],
		'bisa_sewa'	=> $data_beli['bisa_sewa'],
		'harga_format'     => "Rp" . number_format($data_beli['harga'],0,',','.'),
		'harga_sewa_format'=> "Rp" . number_format($data_beli['harga_sewa'],0,',','.'),
		'jumlah_diskon_beli' => $data_beli['diskon_beli'],
		'jumlah_diskon_sewa' => $data_beli['diskon_sewa'],
		'harga_beli_diskon_format'=> "Rp" . number_format($data_beli['harga_beli_setelah_diskon'],0,',','.'),
		'harga_sewa_diskon_format'=> "Rp" . number_format($data_beli['harga_sewa_setelah_diskon'],0,',','.'),
		'judul'		=> $data_beli['judul'],
		'penulis'	=> $data_beli['penulis'],
		'cover'		=> $urlimg."/".$data_beli['cover'],
		'nama_kategori'     => $data_beli['nama_kategori'],
		'tgl_posting'   => date('d F Y h:i:s A', strtotime($data_beli['tgl_posting'])),
	));
}
$hasil_sewa = array();
while($data_sewa = mysqli_fetch_array($sewa)){
	if(!empty($data_sewa['diskon_beli'])){
		(float)$harga_potongan = (float)$data_sewa['harga'] * ((float)$data_sewa['diskon_beli']/100);
		(float)$harga_disc = $data_sewa['harga'] - $harga_potongan;
		$status_diskon = 'Y';
	}
	array_push($hasil_sewa,array(
		'id_buku'	=> $data_sewa['id_buku'],	
		'bisa_beli'	=> $data_sewa['bisa_beli'],
		'bisa_sewa'	=> $data_sewa['bisa_sewa'],
		'harga_format'     => "Rp" . number_format($data_sewa['harga'],0,',','.'),
		'harga_sewa_format'=> "Rp" . number_format($data_sewa['harga_sewa'],0,',','.'),
		'jumlah_diskon_beli' => $data_sewa['diskon_beli'],
		'jumlah_diskon_sewa' => $data_sewa['diskon_sewa'],
		'harga_beli_diskon_format'=> "Rp" . number_format($data_sewa['harga_beli_setelah_diskon'],0,',','.'),
		'harga_sewa_diskon_format'=> "Rp" . number_format($data_sewa['harga_sewa_setelah_diskon'],0,',','.'),
		'judul'		=> $data_sewa['judul'],
		'penulis'	=> $data_sewa['penulis'],
		'cover'		=> $urlimg."/".$data_sewa['cover'],
		'nama_kategori'     => $data_sewa['nama_kategori'],
		'tgl_posting'   => date('d F Y h:i:s A', strtotime($data_sewa['tgl_posting'])),
	));
}

echo json_encode(array(
	'hasiL_majalah' => $hasil_majalah,
	'hasil_renungan' => $hasil_renungan,
	'hasil_terbaru'	=> $hasil_terbaru,
	'hasil_sewa'	=> $hasil_sewa,
	'hasil_beli'	=> $hasil_beli,
));

mysqli_close($conn);
?>
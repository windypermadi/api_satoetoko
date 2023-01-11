<?php 
require_once('koneksi.php');

$tag = $_GET['tag'];

switch ($tag){
	case "hasil_majalah" :
	$limit                           = $_GET['limit'];
	$offset                          = $_GET['offset'];
	$q                               = $_GET['q'];
	
	if (empty($q)){
		$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
			INNER JOIN itemkategorinew b 
			ON a.kd_kategori = b.kode_kategori WHERE a.status_remove = 'N' AND a.kd_kategori_menu = 'M06' ORDER BY a.id_buku DESC LIMIT $limit, $offset");
	} else {
		$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
			INNER JOIN itemkategorinew b 
			ON a.kd_kategori = b.kode_kategori WHERE a.judul LIKE '%$q%' AND a.status_remove = 'N' AND a.kd_kategori_menu = 'M06' ORDER BY a.id_buku DESC LIMIT $limit, $offset");
	}

	$result = array();
	while($row = mysqli_fetch_array($query)){
		if(!empty($row['diskon_beli'])){
			(float)$harga_potongan = (float)$row['harga'] * ((float)$row['diskon_beli']/100);
			(float)$harga_disc = $row['harga'] - $harga_potongan;
			$jumlah_diskon = $row['diskon_beli']."%";
		} else {
			$harga_potongan = 0;
			$jumlah_diskon = "0";
			$harga_disc = 0;
		}
		array_push($result,array(
			'id_buku'	=> $row['id_buku'],	
			'bisa_beli'	=> $row['bisa_beli'],
			'bisa_sewa'	=> $row['bisa_sewa'],
			'harga_format'     => "Rp" . number_format($row['harga'],0,',','.'),
			'harga_sewa_format'=> "Rp" . number_format($row['harga_sewa'],0,',','.'),
			'jumlah_diskon_beli' => $row['diskon_beli'],
			'jumlah_diskon_sewa' => $row['diskon_sewa'],
			'harga_beli_diskon_format'=> "Rp" . number_format($row['harga_beli_setelah_diskon'],0,',','.'),
			'harga_sewa_diskon_format'=> "Rp" . number_format($row['harga_sewa_setelah_diskon'],0,',','.'),
			'judul'		=> $row['judul'],
			'penulis'	=> $row['penulis'],
			'cover'		=> $urlimg."/".$row['cover'],
			'nama_kategori'     => $row['nama_kategori'],
			'tgl_posting'   => date('d F Y h:i:s A', strtotime($row['tgl_posting'])),
		));
	}

	if (isset($result[0])){
		echo json_encode($result);
	} else {
		http_response_code(400);
		if($limit == 0){
			$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
			$respon['kode']  = "0";
		}else{
			$respon['pesan'] = "Ini merupakan halaman terakhir!\nKlik `Mengerti` untuk menutup pesan ini";
			$respon['kode']  = "1";
		}
		echo json_encode($respon);
	}
	break;
	case "hasil_renungan" :
	$limit                           = $_GET['limit'];
	$offset                          = $_GET['offset'];
	$q                               = $_GET['q'];
	
	if (empty($q)){
		$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
			INNER JOIN itemkategorinew b 
			ON a.kd_kategori = b.kode_kategori WHERE a.status_remove = 'N' AND a.kd_kategori_menu = 'M07' ORDER BY a.id_buku DESC LIMIT $limit, $offset");
	} else {
		$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
			INNER JOIN itemkategorinew b 
			ON a.kd_kategori = b.kode_kategori WHERE a.judul LIKE '%$q%' AND a.status_remove = 'N' AND a.kd_kategori_menu = 'M07' ORDER BY a.id_buku DESC LIMIT $limit, $offset");
	}

	$result = array();
	while($row = mysqli_fetch_array($query)){
		if(!empty($row['diskon_beli'])){
			(float)$harga_potongan = (float)$row['harga'] * ((float)$row['diskon_beli']/100);
			(float)$harga_disc = $row['harga'] - $harga_potongan;
			$jumlah_diskon = $row['diskon_beli']."%";
		} else {
			$harga_potongan = 0;
			$jumlah_diskon = "0";
			$harga_disc = 0;
		}
		array_push($result,array(
			'id_buku'	=> $row['id_buku'],	
			'bisa_beli'	=> $row['bisa_beli'],
			'bisa_sewa'	=> $row['bisa_sewa'],
			'harga_format'     => "Rp" . number_format($row['harga'],0,',','.'),
			'harga_sewa_format'=> "Rp" . number_format($row['harga_sewa'],0,',','.'),
			'jumlah_diskon_beli' => $row['diskon_beli'],
			'jumlah_diskon_sewa' => $row['diskon_sewa'],
			'harga_beli_diskon_format'=> "Rp" . number_format($row['harga_beli_setelah_diskon'],0,',','.'),
			'harga_sewa_diskon_format'=> "Rp" . number_format($row['harga_sewa_setelah_diskon'],0,',','.'),
			'judul'		=> $row['judul'],
			'penulis'	=> $row['penulis'],
			'cover'		=> $urlimg."/".$row['cover'],
			'nama_kategori'     => $row['nama_kategori'],
			'tgl_posting'   => date('d F Y h:i:s A', strtotime($row['tgl_posting'])),
		));
	}

	if (isset($result[0])){
		echo json_encode($result);
	} else {
		http_response_code(400);
		if($limit == 0){
			$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
			$respon['kode']  = "0";
		}else{
			$respon['pesan'] = "Ini merupakan halaman terakhir!\nKlik `Mengerti` untuk menutup pesan ini";
			$respon['kode']  = "1";
		}
		echo json_encode($respon);
	}
	break;
	case "hasil_terbaru" :
	$limit                           = $_GET['limit'];
	$offset                          = $_GET['offset'];
	$q                               = $_GET['q'];
	
	if (empty($q)){
		$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
			INNER JOIN itemkategorinew b 
			ON a.kd_kategori = b.kode_kategori WHERE a.status_remove = 'N' ORDER BY a.id_buku DESC LIMIT $limit, $offset");
	} else {
		$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
			INNER JOIN itemkategorinew b 
			ON a.kd_kategori = b.kode_kategori WHERE a.judul LIKE '%$q%' AND a.status_remove = 'N' ORDER BY a.id_buku DESC LIMIT $limit, $offset");
	}

	$result = array();
	while($row = mysqli_fetch_array($query)){
		if(!empty($row['diskon_beli'])){
			(float)$harga_potongan = (float)$row['harga'] * ((float)$row['diskon_beli']/100);
			(float)$harga_disc = $row['harga'] - $harga_potongan;
			$jumlah_diskon = $row['diskon_beli']."%";
		} else {
			$harga_potongan = 0;
			$jumlah_diskon = "0";
			$harga_disc = 0;
		}
		array_push($result,array(
			'id_buku'	=> $row['id_buku'],	
			'bisa_beli'	=> $row['bisa_beli'],
			'bisa_sewa'	=> $row['bisa_sewa'],
			'harga_format'     => "Rp" . number_format($row['harga'],0,',','.'),
			'harga_sewa_format'=> "Rp" . number_format($row['harga_sewa'],0,',','.'),
			'jumlah_diskon_beli' => $row['diskon_beli'],
			'jumlah_diskon_sewa' => $row['diskon_sewa'],
			'harga_beli_diskon_format'=> "Rp" . number_format($row['harga_beli_setelah_diskon'],0,',','.'),
			'harga_sewa_diskon_format'=> "Rp" . number_format($row['harga_sewa_setelah_diskon'],0,',','.'),
			'judul'		=> $row['judul'],
			'penulis'	=> $row['penulis'],
			'cover'		=> $urlimg."/".$row['cover'],
			'nama_kategori'     => $row['nama_kategori'],
			'tgl_posting'   => date('d F Y h:i:s A', strtotime($row['tgl_posting'])),
		));
	}

	if (isset($result[0])){
		echo json_encode($result);
	} else {
		http_response_code(400);
		if($limit == 0){
			$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
			$respon['kode']  = "0";
		}else{
			$respon['pesan'] = "Ini merupakan halaman terakhir!\nKlik `Mengerti` untuk menutup pesan ini";
			$respon['kode']  = "1";
		}
		echo json_encode($respon);
	}
	break;
	case "hasil_sewa" :
	$limit                           = $_GET['limit'];
	$offset                          = $_GET['offset'];
	$q                               = $_GET['q'];
	
	if (empty($q)){
		$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
			INNER JOIN itemkategorinew b 
			ON a.kd_kategori = b.kode_kategori WHERE a.bisa_sewa = 'Y' AND a.status_remove = 'N' AND a.kd_kategori_menu NOT IN ('M06', 'M07') ORDER BY a.id_buku DESC LIMIT $limit, $offset");
	} else {
		$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
			INNER JOIN itemkategorinew b 
			ON a.kd_kategori = b.kode_kategori WHERE a.judul LIKE '%$q%' AND a.bisa_sewa = 'Y' AND a.status_remove = 'N' AND a.kd_kategori_menu NOT IN ('M06', 'M07') ORDER BY a.id_buku DESC LIMIT $limit, $offset");
	}

	$result = array();
	while($row = mysqli_fetch_array($query)){
		if(!empty($row['diskon_beli'])){
			(float)$harga_potongan = (float)$row['harga'] * ((float)$row['diskon_beli']/100);
			(float)$harga_disc = $row['harga'] - $harga_potongan;
			$jumlah_diskon = $row['diskon_beli']."%";
		} else {
			$harga_potongan = 0;
			$jumlah_diskon = "0";
			$harga_disc = 0;
		}
		array_push($result,array(
			'id_buku'	=> $row['id_buku'],	
			'bisa_beli'	=> $row['bisa_beli'],
			'bisa_sewa'	=> $row['bisa_sewa'],
			'harga_format'     => "Rp" . number_format($row['harga'],0,',','.'),
			'harga_sewa_format'=> "Rp" . number_format($row['harga_sewa'],0,',','.'),
			'jumlah_diskon_beli' => $row['diskon_beli'],
			'jumlah_diskon_sewa' => $row['diskon_sewa'],
			'harga_beli_diskon_format'=> "Rp" . number_format($row['harga_beli_setelah_diskon'],0,',','.'),
			'harga_sewa_diskon_format'=> "Rp" . number_format($row['harga_sewa_setelah_diskon'],0,',','.'),
			'judul'		=> $row['judul'],
			'penulis'	=> $row['penulis'],
			'cover'		=> $urlimg."/".$row['cover'],
			'nama_kategori'     => $row['nama_kategori'],
			'tgl_posting'   => date('d F Y h:i:s A', strtotime($row['tgl_posting'])),
		));
	}

	if (isset($result[0])){
		echo json_encode($result);
	} else {
		http_response_code(400);
		if($limit == 0){
			$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
			$respon['kode']  = "0";
		}else{
			$respon['pesan'] = "Ini merupakan halaman terakhir!\nKlik `Mengerti` untuk menutup pesan ini";
			$respon['kode']  = "1";
		}
		echo json_encode($respon);
	}
	break;
	case "hasil_beli" :
	$limit                           = $_GET['limit'];
	$offset                          = $_GET['offset'];
	$q                               = $_GET['q'];
	
	if (empty($q)){
		$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
			INNER JOIN itemkategorinew b 
			ON a.kd_kategori = b.kode_kategori WHERE a.bisa_beli = 'Y' AND a.status_remove = 'N' AND a.kd_kategori_menu NOT IN ('M06', 'M07') ORDER BY a.judul ASC LIMIT $limit, $offset");
	} else {
	$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
		INNER JOIN itemkategorinew b 
		ON a.kd_kategori = b.kode_kategori WHERE a.judul LIKE '%$q%' AND a.bisa_beli = 'Y' AND a.status_remove = 'N' AND a.kd_kategori_menu NOT IN ('M06', 'M07') ORDER BY a.judul ASC LIMIT $limit, $offset");
}

$result = array();
while($row = mysqli_fetch_array($query)){
	if(!empty($row['diskon_beli'])){
		(float)$harga_potongan = (float)$row['harga'] * ((float)$row['diskon_beli']/100);
		(float)$harga_disc = $row['harga'] - $harga_potongan;
		$jumlah_diskon = $row['diskon_beli']."%";
	} else {
		$harga_potongan = 0;
		$jumlah_diskon = "0";
		$harga_disc = 0;
	}
	array_push($result,array(
		'id_buku'	=> $row['id_buku'],	
		'bisa_beli'	=> $row['bisa_beli'],
		'bisa_sewa'	=> $row['bisa_sewa'],
		'harga_format'     => "Rp" . number_format($row['harga'],0,',','.'),
		'harga_sewa_format'=> "Rp" . number_format($row['harga_sewa'],0,',','.'),
		'jumlah_diskon_beli' => $row['diskon_beli'],
		'jumlah_diskon_sewa' => $row['diskon_sewa'],
		'harga_beli_diskon_format'=> "Rp" . number_format($row['harga_beli_setelah_diskon'],0,',','.'),
		'harga_sewa_diskon_format'=> "Rp" . number_format($row['harga_sewa_setelah_diskon'],0,',','.'),
		'judul'		=> $row['judul'],
		'penulis'	=> $row['penulis'],
		'cover'		=> $urlimg."/".$row['cover'],
		'nama_kategori'     => $row['nama_kategori'],
		'tgl_posting'   => date('d F Y h:i:s A', strtotime($row['tgl_posting'])),
	));
}

if (isset($result[0])){
	echo json_encode($result);
} else {
	http_response_code(400);
	if($limit == 0){
		$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
		$respon['kode']  = "0";
	}else{
		$respon['pesan'] = "Ini merupakan halaman terakhir!\nKlik `Mengerti` untuk menutup pesan ini";
		$respon['kode']  = "1";
	}
	echo json_encode($respon);
}
break;
}

mysqli_close($conn);

?>
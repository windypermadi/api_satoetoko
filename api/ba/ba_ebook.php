<?php 
require_once('koneksi.php');

$tag = $_GET['tag'];

switch ($tag){
	case "semua" :
	$limit                           = $_GET['limit'];
	$offset                          = $_GET['offset'];
	$q                               = $_GET['q'];
	
	if (empty($q)){
		$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
			INNER JOIN itemkategorinew b 
			ON a.kd_kategori = b.kode_kategori WHERE a.status_remove = 'N' ORDER BY a.judul ASC LIMIT $limit, $offset");
	} else {
		$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
			INNER JOIN itemkategorinew b 
			ON a.kd_kategori = b.kode_kategori WHERE a.judul LIKE '%$q%' AND a.status_remove = 'N' ORDER BY a.judul ASC LIMIT $limit, $offset");
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
	case "detail" :
	$id_buku = $_GET['id_buku'];
	$query = mysqli_query($conn, "SELECT * FROM ba_buku a 
		INNER JOIN itemkategorinew b 
		ON a.kd_kategori = b.kode_kategori WHERE a.id_buku = '$id_buku'");
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
		$data['id_buku']             = $row['id_buku'];
		$data['bisa_beli']           = $row['bisa_beli'];
		$data['bisa_sewa']           = $row['bisa_sewa'];
		$data['harga']             	 = $row['harga'];
		$data['harga_format']        = "Rp" . number_format($row['harga'],0,',','.');
		$data['harga_sewa']          = $row['harga_sewa'];
		$data['harga_sewa_format']   = "Rp" . number_format($row['harga_sewa'],0,',','.');

		$data['jumlah_diskon_beli']   = $row['diskon_beli'];
		$data['jumlah_diskon_sewa']   = $row['diskon_sewa'];
		
		$data['harga_beli_diskon_format']   = "Rp" . number_format($row['harga_beli_setelah_diskon'],0,',','.');
		$data['harga_sewa_diskon_format']   = "Rp" . number_format($row['harga_sewa_setelah_diskon'],0,',','.');
		
		$data['harga_beli_diskon']   = $row['harga_beli_setelah_diskon'];
		$data['harga_sewa_diskon']   = $row['harga_sewa_setelah_diskon'];
		$data['lama_sewa']           = $row['lama_sewa'];
		
		$data['judul']               = $row['judul'];
		$data['penulis']             = $row['penulis'];
		$data['cover']             	 = $urlimg."/".$row['cover'];
		$data['sinopsis']             	 = $row['sinopsis'];
		$data['ISBN']             	 = $row['ISBN'];
		$data['edisi']             	 = $row['edisi'];
		$data['nama_kategori']       = $row['nama_kategori'];
		$data['tahun_terbit']        = $row['tahun_terbit'];
		$data['edisi']             	 = $row['edisi'];
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
		echo json_encode($data);
	} else {
		http_response_code(400);
		$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
		echo json_encode($respon);
	}
	break;
	case "kategori":
	$limit                           = $_GET['limit'];
	$offset                          = $_GET['offset'];
	$idkategori                      = $_GET['idkategori'];
	$query = mysqli_query($conn, "SELECT * FROM ba_buku a INNER JOIN itemkategorinew b ON a.kd_kategori = b.kode_kategori WHERE a.kd_kategori = '$idkategori' AND a.status_remove = 'N' LIMIT $limit, $offset");
	
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
	case "kategoricari":
	$limit                           = $_GET['limit'];
	$offset                          = $_GET['offset'];
	$idkategori                      = $_GET['idkategori'];
	$cari                     		 = $_GET['q'];
	$query = mysqli_query($conn, "SELECT * FROM ba_buku a INNER JOIN itemkategorinew b ON a.kd_kategori = b.kode_kategori WHERE a.kd_kategori = '$idkategori' AND a.judul LIKE '%$cari%' LIMIT $limit, $offset");
	
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
	default:
	break;
}

mysqli_close($conn);

?>
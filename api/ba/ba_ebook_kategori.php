<?php 
require_once('koneksi.php');

if (empty($_GET['id_buku'])){
	$limit                           = $_GET['limit'];
	$offset                          = $_GET['offset'];
	$query = mysqli_query($conn, "SELECT * FROM ba_buku INNER JOIN ba_kategori_buku on ba_buku.kd_kategori = ba_kategori_buku.kd_kategori LIMIT $limit, $offset");
	
	$result = array();
	while($row = mysqli_fetch_array($query)){
		array_push($result,array(
			'id_buku'	=> $row['id_buku'],	
			'harga'     => $row['harga'],
			'harga_sewa'=> $row['harga_sewa'],
			'judul'		=> $row['judul'],
			'penulis'	=> $row['penulis'],
			'cover'		=> $urlimg."/".$row['cover'],
			'ISBN'      => $row['ISBN'],
			'edisi'     => $row['edisi'],
			'nama_kategori'     => $row['nama_kategori'],
			'tahun_terbit'     => $row['tahun_terbit'],
			'tgl_posting'   => $row['tgl_posting'],
			'tgl_update'     => $row['tgl_update'],
			'pdf_url'     => $urlpdf."/".$row['pdf_url'],
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
}else{
	
	$id_buku = $_GET['id_buku'];
	$query = mysqli_query($conn, "SELECT * FROM ba_buku 
		INNER JOIN ba_kategori_buku on ba_buku.kd_kategori = ba_kategori_buku.kd_kategori WHERE id_buku = '$id_buku'");
	$result = array();
	while($row = mysqli_fetch_array($query)){
		$data['id_buku']             = $row['id_buku'];
		$data['harga']             = $row['harga'];
		$data['harga_sewa']             = $row['harga_sewa'];
		$data['judul']             = $row['judul'];
		$data['penulis']             = $row['penulis'];
		$data['cover']             = $urlimg."/".$row['cover'];
		$data['ISBN']             = $row['ISBN'];
		$data['edisi']             = $row['edisi'];
		$data['nama_kategori']             = $row['nama_kategori'];
		$data['tahun_terbit']             = $row['tahun_terbit'];
		$data['pdf_url']             = $urlpdf."/".$row['pdf_url'];
		array_push($result,array(
			'id_buku'	=> $row['id_buku'],
			'harga'     => $row['harga'],
			'harga_sewa'=> $row['harga_sewa'],
			'judul'		=> $row['judul'],
			'penulis'	=> $row['penulis'],
			'cover'		=> $urlimg."/".$row['cover'],
			'ISBN'      => $row['ISBN'],
			'edisi'     => $row['edisi'],
			'sinopsis'       => $row['sinopsis'],
			'nama_kategori'     => $row['nama_kategori'],
			'tahun_terbit'     => $row['tahun_terbit'],
			'tgl_posting'   => $row['tgl_posting'],
			'tgl_update'     => $row['tgl_update'],
			'pdf_url'     => $urlpdf."/".$row['pdf_url'],
		));
	}
	
	if (isset($result[0])){
		echo json_encode($data);
	} else {
		http_response_code(400);
		$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
		$respon['kode']  = "0";
		echo json_encode($respon);
	}
}
mysqli_close($conn);

?>
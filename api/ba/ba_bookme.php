<?php 
require_once('koneksi.php');

$id_user                         = $_GET['id_user'];
$limit                           = $_GET['limit'];
$offset                          = $_GET['offset'];

$status                          = $_GET['status'];

switch ($status) {
	case '1':
	$query = mysqli_query($conn, "SELECT c.id_buku, c.judul, c.penulis, c.cover, d.nama_kategori, c.pdf_url FROM ba_transaksi_ebook_detail a 
		INNER JOIN ba_transaksi_ebook b on a.id_transaksi = b.id_transaksi
		INNER JOIN ba_buku c ON c.id_buku = a.id_buku
		LEFT JOIN itemkategorinew d ON d.kode_kategori = c.kd_kategori WHERE b.status_transaksi = '7' AND a.id_user = '$id_user' AND a.status_pembelian = '1' AND a.tgl_exp >= NOW() LIMIT $limit, $offset");

	$result = array();
	while($row = mysqli_fetch_array($query)){
		array_push($result,array(
			'id_buku' => $row['id_buku'], 
			'judul'   => $row['judul'],
			'penulis' => $row['penulis'],
			'cover'   => $urlimg."/".$row['cover'],
			'nama_kategori'     => $row['nama_kategori'],
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
	break;
	case '2':
	$query = mysqli_query($conn, "SELECT c.id_buku, c.judul, c.penulis, c.cover, d.nama_kategori, c.pdf_url FROM ba_transaksi_ebook_detail a 
		INNER JOIN ba_transaksi_ebook b on a.id_transaksi = b.id_transaksi
		INNER JOIN ba_buku c ON c.id_buku = a.id_buku
		LEFT JOIN itemkategorinew d ON d.kode_kategori = c.kd_kategori WHERE b.status_transaksi = '7' AND a.id_user = '$id_user' AND a.status_pembelian = '2' AND a.tgl_exp >= NOW() LIMIT $limit, $offset");

	$result = array();
	while($row = mysqli_fetch_array($query)){
		array_push($result,array(
			'id_buku' => $row['id_buku'], 
			'judul'   => $row['judul'],
			'penulis' => $row['penulis'],
			'cover'   => $urlimg."/".$row['cover'],
			'nama_kategori'     => $row['nama_kategori'],
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
	break;
	case '3':
	$query = mysqli_query($conn, "SELECT c.id_buku, c.judul, c.penulis, c.cover, d.nama_kategori, c.pdf_url FROM ba_buku_event a 
		LEFT JOIN ba_promo b ON a.id_promo = b.id_promo
		LEFT JOIN ba_buku c ON c.id_buku = b.file_buku_event
		LEFT JOIN itemkategorinew d ON d.kode_kategori = c.kd_kategori WHERE a.id_user = '$id_user' AND a.status_aktif = 'Y' LIMIT $limit, $offset");

	$result = array();
	while($row = mysqli_fetch_array($query)){
		array_push($result,array(
			'id_buku' => $row['id_buku'], 
			'judul'   => $row['judul'],
			'penulis' => $row['penulis'],
			'cover'   => $urlimg."/".$row['cover'],
			'nama_kategori'     => $row['nama_kategori'],
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
	break;
	
	default:
	http_response_code(400);
	break;
}
mysqli_close($conn);

?>
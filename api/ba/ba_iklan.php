<?php 
require_once('koneksi.php');
$tag = $_GET['tag'];

switch ($tag){
	case "primary" :
	if (empty($_GET['id_iklan'])){
		$query = mysqli_query($conn, "SELECT * FROM ba_iklan WHERE status_iklan = '1' AND 
			tgl_exp_iklan >= NOW() ORDER BY id_iklan DESC");
		$result = array();
		while($row = mysqli_fetch_array($query)){
			array_push($result,array(
				'id_iklan'			=> $row['id_iklan'],
				'nama_iklan'		=> $row['nama_iklan'],
				'file_iklan'		=> $urliklan.$row['file_iklan'],
				'link_iklan'		=> $row['link_iklan'],
				'status_iklan'		=> $row['status_iklan'],
				'tgl_posting_iklan'	=> date('d F Y h:i:s A', strtotime($row['tgl_posting_iklan'])),
				'tgl_exp_iklan'		=> date('d F Y h:i:s A', strtotime($row['tgl_exp_iklan'])),
			));
		}   

		if (isset($result[0])){
			echo json_encode($result);
		} else {
			http_response_code(400);
			$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
			echo json_encode($respon);
		}
	} else {
		$id_iklan = $_GET['id_iklan'];
		$query = mysqli_query($conn, "SELECT * FROM ba_iklan WHERE status_iklan = '1' AND id_iklan = '$id_iklan'");
		$result = array();
		while($row = mysqli_fetch_array($query)){
			array_push($result,array(
				'id_iklan'			=> $row['id_iklan'],
				'nama_iklan'		=> $row['nama_iklan'],
				'file_iklan'		=> $urliklan.$row['file_iklan'],
				'link_iklan'		=> $row['link_iklan'],
				'deskripsi_iklan'   => $row['deskripsi_iklan'],
				'tgl_posting_iklan'	=> date('d F Y h:i:s A', strtotime($row['tgl_posting_iklan'])),
				'tgl_exp_iklan'		=> date('d F Y h:i:s A', strtotime($row['tgl_exp_iklan'])),
			));
		}   

		if (isset($result[0])){
			echo json_encode($result);
		} else {
			http_response_code(400);
			$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
			echo json_encode($respon);
		}
	}
	break;
	case "sekunder" :
	if (empty($_GET['id_iklan'])){
		$query = mysqli_query($conn, "SELECT * FROM ba_iklan WHERE status_iklan = '2' AND 
			tgl_exp_iklan >= NOW() ORDER BY id_iklan DESC");
		$result = array();
		while($row = mysqli_fetch_array($query)){
			array_push($result,array(
				'id_iklan'			=> $row['id_iklan'],
				'nama_iklan'		=> $row['nama_iklan'],
				'file_iklan'		=> $urliklan.$row['file_iklan'],
				'link_iklan'		=> $row['link_iklan'],
				'status_iklan'		=> $row['status_iklan'],
				'tgl_posting_iklan'	=> date('d F Y h:i:s A', strtotime($row['tgl_posting_iklan'])),
				'tgl_exp_iklan'		=> date('d F Y h:i:s A', strtotime($row['tgl_exp_iklan'])),
			));
		}   

		if (isset($result[0])){
			echo json_encode($result);
		} else {
			http_response_code(400);
			$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
			echo json_encode($respon);
		}
	} else {
		$id_iklan = $_GET['id_iklan'];
		$query = mysqli_query($conn, "SELECT * FROM ba_iklan WHERE status_iklan = '2' AND id_iklan = '$id_iklan'");
		$result = array();
		while($row = mysqli_fetch_array($query)){
			array_push($result,array(
				'id_iklan'			=> $row['id_iklan'],
				'nama_iklan'		=> $row['nama_iklan'],
				'file_iklan'		=> $urliklan.$row['file_iklan'],
				'link_iklan'		=> $row['link_iklan'],
				'deskripsi_iklan'   => $row['deskripsi_iklan'],
				'tgl_posting_iklan'	=> date('d F Y h:i:s A', strtotime($row['tgl_posting_iklan'])),
				'tgl_exp_iklan'		=> date('d F Y h:i:s A', strtotime($row['tgl_exp_iklan'])),
			));
		}   

		if (isset($result[0])){
			echo json_encode($result);
		} else {
			http_response_code(400);
			$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
			echo json_encode($respon);
		}
	}
	break;
}
mysqli_close($conn);
?>
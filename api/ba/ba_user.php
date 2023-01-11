<?php 
require_once('koneksi.php');

$id_user = $_GET['id_user'];
$query = mysqli_query($conn, "SELECT * FROM loginuser_bahana WHERE id_user = '$id_user'");
$result = array();
while($row = mysqli_fetch_array($query)){
    
    if (empty($row['ba_referal'])){
        $status_referal = 'N';
    } else {
         $status_referal = 'Y';
    }
	$data['id_user']                = $row['id_user'];
	$data['nama_user']              = $row['nama_user'];
	$data['email_user']             = $row['email_user'];
	$data['telepon_user']           = $row['telepon_user'];
	$data['ba_referal']             = $row['ba_referal'];
	$data['status_referal']         = $status_referal;
	$data['foto_user']              = $getprofile.$row['foto_user'];
	$data['keranjang_belanja_user'] = $row['keranjang_belanja_user'];
	array_push($result,array(
		'id_user'					=> $row['id_user'],
		'nama_user'    				=> $row['nama_user'],
		'email_user'				=> $row['email_user'],
		'telepon_user'				=> $row['telepon_user'],
	    'ba_referal'				=> $row['ba_referal'],
		'foto_user'					=> $getprofile.$row['foto_user'],
		'keranjang_belanja_user'    => $row['keranjang_belanja_user'],
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

mysqli_close($conn);

?>
<?php 
require_once('koneksi.php');

$invoice = $_GET['no_invoice'];
$total   = $_GET['total'];

$query = mysqli_query($conn, "SELECT * FROM ba_payment_manual WHERE status_aktif = 'Y'");

$result = array();
while($row = mysqli_fetch_array($query)){
        $data['nomor_telp']         = GETWA;
        $data['deskripsi']         = 'Mohon konfirmasi pembayaran menghubungi nomor whatsapp berikut ini.';
        $data['no_invoice']         = $invoice;
        $data['total']              = "Rp" . number_format($total,0,',','.');
		array_push($result,array(
			'id_payment'	=> $row['id_payment'],
			'icon_payment'	=> $geticonkategori.$row['icon_payment'],
			'metode_pembayaran'     => $row['metode_pembayaran'],
			'nomor_payment'     => $row['nomor_payment'],
			'penerima_payment'     => $row['penerima_payment'],
		));
}

if (isset($result[0])){

      $result1['data_transaksi'] = $data;
      $result1['result'] = $result;

      echo json_encode($result1);
  } else {
    http_response_code(400);
    $respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
    echo json_encode($respon);
}

mysqli_close($conn);

?>
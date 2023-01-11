<?php
require_once('koneksi.php');

$tag = $_POST['tag'];
$total_item = 0;

switch ($tag) {
	case "addtransaksievent":
	$id_user = $_POST['id_user'];
	$id_promo = $_POST['id_promo'];
	$status  = $_POST['status'];
	$jumlahbayar = $_POST['jumlahbayar'];
	$event_nama_transaksi = $_POST['event_nama_transaksi'];

	$event = mysqli_query($conn, "SELECT * FROM ba_promo  WHERE id_promo = '$id_promo'")->fetch_assoc();
	$nama_event = $event['nama_promo'];
	
	$cek_transaksi = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail WHERE id_user = '$id_user' AND id_promo = '$id_promo'")->num_rows;

	if ($cek_transaksi > 0) {
		http_response_code(400);
		$respon['pesan'] = "Tidak dapat mendaftar event. Kamu sudah daftar event ini!\nKlik `Mengerti` untuk menutup pesan ini";
		die(json_encode($respon));
	} else {
		$idtransaksi = createID('id_transaksi', 'ba_transaksi_ebook', 'TR');
		$invoice = id_ke_struk($idtransaksi);
		$query = mysqli_query($conn, "INSERT INTO ba_transaksi_ebook (id_transaksi, invoice, id_user, batas_pembayaran, total_pembayaran, status_pembelian) 
			VALUES ('$idtransaksi', '$invoice', '$id_user', DATE_ADD(NOW(), INTERVAL + 2 DAY), '$jumlahbayar', '$status')");

		if ($query) {
			$queryselect = mysqli_query($conn, "INSERT INTO ba_transaksi_ebook_detail (id_transaksi_detail, id_transaksi, id_user, id_promo, status_pembelian, harga_normal, event_nama_transaksi)
				VALUES (UUID(), '$idtransaksi', '$id_user', '$id_promo', '$status', '$jumlahbayar', '$event_nama_transaksi')");

			if ($queryselect) {

				$filesertifikat = $getsertifikat."?nama=".$event_nama_transaksi."&judul=".$nama_event."&id_promo=".$id_promo;

				$id_sertifikat = createID('id_sertifikat', 'ba_sertifikat_event', 'ST');
				$addsertifikat = mysqli_query($conn, "INSERT INTO ba_sertifikat_event (id_sertifikat, id_transaksi, file_sertifikat, status_sertifikat)
					VALUES ('$id_sertifikat', '$idtransaksi', '$filesertifikat', 'Y')");

				if ($addsertifikat) {
					$response['pesan'] = "Berhasil menambahkan transaksi! ";
					$response['id_transaksi'] = $idtransaksi;
					$response['no_invoice'] = id_ke_struk($idtransaksi);
					$response['total'] = $jumlahbayar;
					die(json_encode($response));
				} else {
					http_response_code(400);
					$response['pesan'] = "Gagal menambahkan sertifikat";
					die(json_encode($response));
				}

			} else {
				http_response_code(400);
				$response['pesan'] = "ID Event tidak masuk di detail transaksi";
				die(json_encode($response));
			}
		} else {
			http_response_code(400);
			$response['pesan'] = "Gagal menambahkan transaksi!";
			die(json_encode($response));
		}
	}
	break;
	case "addfreeevent":
	$id_user = $_POST['id_user'];
	$id_promo = $_POST['id_promo'];
	$status  = $_POST['status'];
	$jumlahbayar = $_POST['jumlahbayar'];
	$event_nama_transaksi = $_POST['event_nama_transaksi'];

	$cek_transaksi = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail WHERE id_user = '$id_user' AND id_promo = '$id_promo'")->num_rows;

	if ($cek_transaksi > 0) {
		http_response_code(400);
		$respon['pesan'] = "Tidak dapat mendaftar event. Kamu sudah daftar event ini!\nKlik `Mengerti` untuk menutup pesan ini";
		die(json_encode($respon));
	} else {
		$idtransaksi = createID('id_transaksi', 'ba_transaksi_ebook', 'TR');
		$invoice = id_ke_struk($idtransaksi);
		$query = mysqli_query($conn, "INSERT INTO ba_transaksi_ebook (id_transaksi, invoice, id_user, batas_pembayaran, total_pembayaran, status_transaksi, status_pembelian, status_payment) 
			VALUES ('$idtransaksi', '$invoice', '$id_user', DATE_ADD(NOW(), INTERVAL + 2 DAY), '$jumlahbayar', '7', '$status', '3')");

		if ($query) {
			$queryselect = mysqli_query($conn, "INSERT INTO ba_transaksi_ebook_detail (id_transaksi_detail, id_transaksi, id_user, id_promo, status_pembelian, harga_normal, event_nama_transaksi)
				VALUES (UUID(), '$idtransaksi', '$id_user', '$id_promo', '$status', '$jumlahbayar', '$event_nama_transaksi')");

			if ($queryselect) {

				$filesertifikat = $getsertifikat."?nama=".$event_nama_transaksi."&judul=".$nama_event."&id_promo=".$id_promo;

				$id_sertifikat = createID('id_sertifikat', 'ba_sertifikat_event', 'ST');
				$addsertifikat = mysqli_query($conn, "INSERT INTO ba_sertifikat_event (id_sertifikat, id_transaksi, file_sertifikat, status_sertifikat)
					VALUES ('$id_sertifikat', '$idtransaksi', '$filesertifikat', 'Y')");

				if ($addsertifikat) {
					$response['pesan'] = "Berhasil menambahkan transaksi! ";
					$response['id_transaksi'] = $idtransaksi;
					$response['no_invoice'] = id_ke_struk($idtransaksi);
					$response['total'] = $jumlahbayar;
					die(json_encode($response));
				} else {
					http_response_code(400);
					$response['pesan'] = "Gagal menambahkan sertifikat";
					die(json_encode($response));
				}

			} else {
				http_response_code(400);
				$response['pesan'] = "ID Event tidak masuk di detail transaksi";
				die(json_encode($response));
			}
		} else {
			http_response_code(400);
			$response['pesan'] = "Gagal menambahkan transaksi!";
			die(json_encode($response));
		}
	}
	break;
	case "listtransaksi":
	$id_user = $_POST['id_user'];
	$id_transaksi = $_POST['id_transaksi'];

	if (!empty($id_transaksi)){
		$query = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail a 
			LEFT JOIN ba_promo b ON a.id_promo = b.id_promo 
			LEFT JOIN ba_transaksi_ebook c ON a.id_transaksi = c.id_transaksi
			WHERE a.id_user = '$id_user' AND a.status_pembelian = '3' AND a.id_transaksi = '$id_transaksi'");
		$result = array();
		while ($row = mysqli_fetch_array($query)) {
			$query2 = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail a 
				LEFT JOIN ba_promo b ON a.id_promo = b.id_promo
				LEFT JOIN ba_transaksi_ebook c ON a.id_transaksi = c.id_transaksi
				WHERE a.id_user = '$id_user' AND a.status_pembelian = '3' AND a.id_transaksi = '$id_transaksi'")->fetch_assoc();
			$status_transaksi = $query2['status_transaksi'];
			if ($status_transaksi == '1') {
				$keterangan = 'Menunggu Pembayaran';
			} else if ($status_transaksi == '2') {
				$keterangan = 'Menunggu Verifikasi Pembayaran';
			} else if ($status_transaksi == '3') {
				$keterangan = 'Pembayaran Berhasil';
			} else if ($status_transaksi == '4') {
				$keterangan = 'Pembayaran Tidak Lengkap';
			} else if ($status_transaksi == '5') {
				$keterangan = 'Dikirim';
			} else if ($status_transaksi == '6') {
				$keterangan = 'Diterima';
			} else if ($status_transaksi == '7') {
				$keterangan = 'Transaksi Selesai';
			} else if ($status_transaksi == '8') {
				$keterangan = 'Expired';
			} else if ($status_transaksi == '9') {
				$keterangan = 'Dibatalkan';
			} else if ($status_transaksi == '10') {
				$keterangan = 'Pembayaran Ditolak';
			} else {
				$keterangan = 'Pengembalian Barang';
			}
			array_push($result, array(
				'id_transaksi'                 => $row['id_transaksi'],
				'invoice'                      => $row['invoice'],
				'harga_normal'                 => "Rp" . number_format($harga_normal, 0, ',', '.'),
				'nama_promo'                   => $row['nama_promo'],
				'file_promo'                   => $urlpromo.$row['file_promo'],
				'link_promo'                   => $row['link_promo'],
				'ket_status_transaksi'         => $keterangan,
				'status_transaksi'             => $query2['status_transaksi'],
				'deskripsi_promo'              => $row['deskripsi_promo'],
				'tanggal_acara_event'          => date('d F Y h:i:s A', strtotime($row['tanggal_acara_event'])),
			));
		}

		if (isset($result[0])) {
			echo json_encode($result);
		} else {
			http_response_code(400);
			$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
			echo json_encode($respon);
		}
	} else {
		$query = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail a 
			LEFT JOIN ba_promo b ON a.id_promo = b.id_promo 
			LEFT JOIN ba_transaksi_ebook c ON a.id_transaksi = c.id_transaksi
			WHERE a.id_user = '$id_user' AND a.status_pembelian = '3' ORDER BY a.id_transaksi DESC");
		$result = array();
		while ($row = mysqli_fetch_array($query)) {
			if ($row['status_transaksi'] == '1') {
				$keterangan = 'Menunggu Pembayaran';
			} else if ($row['status_transaksi'] == '2') {
				$keterangan = 'Menunggu Verifikasi Pembayaran';
			} else if ($row['status_transaksi'] == '3') {
				$keterangan = 'Pembayaran Berhasil';
			} else if ($row['status_transaksi'] == '4') {
				$keterangan = 'Pembayaran Tidak Lengkap';
			} else if ($row['status_transaksi'] == '5') {
				$keterangan = 'Dikirim';
			} else if ($row['status_transaksi'] == '6') {
				$keterangan = 'Diterima';
			} else if ($row['status_transaksi'] == '7') {
				$keterangan = 'Transaksi Selesai';
			} else if ($row['status_transaksi'] == '8') {
				$keterangan = 'Expired';
			} else if ($row['status_transaksi'] == '9') {
				$keterangan = 'Dibatalkan';
			} else if ($row['status_transaksi'] == '10') {
				$keterangan = 'Pembayaran Ditolak';
			} else {
				$keterangan = 'Pengembalian Barang';
			}


			array_push($result, array(
				'id_transaksi'                 => $row['id_transaksi'],
				'invoice'                      => $row['invoice'],
				'harga_normal'                 => "Rp" . number_format($row['harga_normal'], 0, ',', '.'),
				'nama_promo'                   => $row['nama_promo'],
				'file_promo'                   => $urlpromo.$row['file_promo'],
				'link_promo'                   => $row['link_promo'],
				'ket_status_transaksi'         => $keterangan,
				'status_transaksi'             => $row['status_transaksi'],
				'deskripsi_promo'              => $row['deskripsi_promo'],
				'tanggal_acara_event'          => date('d F Y h:i:s A', strtotime($row['tanggal_acara_event'])),
			));
		}

		if (isset($result[0])) {
			echo json_encode($result);
		} else {
			http_response_code(400);
			$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
			echo json_encode($respon);
		}
	}
	break;
// 	case "listevent_transaction":
// 	$id_transaksi = $_POST['id_transaksi'];
// 	$query = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail a 
// 		LEFT JOIN ba_promo b ON a.id_promo = b.id_promo
// 		LEFT JOIN ba_transaksi_ebook c ON c.id_transaksi = a.id_transaksi
// 		WHERE a.id_transaksi = '$id_transaksi'");
// 	$result = array();
// 	while ($row = mysqli_fetch_array($query)) {

// 		if ($row['status_payment'] == '2') {
// 			$query3 = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook a LEFT JOIN ba_payment_manual b ON a.payment_type = b.id_payment WHERE a.id_transaksi = '$id_transaksi'")->fetch_assoc();
// 			$data['metode_pembayaran']         = $query3['metode_pembayaran'];
// 			$data['nomor_payment']         = $query3['nomor_payment'];
// 			$data['penerima_payment']         = $query3['penerima_payment'];
// 			$data['pesan'] = "";
// 			$data['nomor_telp']         = GETWA;
// 			$data['icon_payment']         = $geticonkategori . $query3['icon_payment'];
// 		}

// 		$status_transaksi = $row['status_transaksi'];
// 		if ($status_transaksi == '1') {
// 			$keterangan = 'Menunggu Pembayaran';
// 		} else if ($status_transaksi == '2') {
// 			$keterangan = 'Menunggu Verifikasi Pembayaran';
// 		} else if ($status_transaksi == '3') {
// 			$keterangan = 'Pembayaran Berhasil';
// 		} else if ($status_transaksi == '4') {
// 			$keterangan = 'Pembayaran Tidak Lengkap';
// 		} else if ($status_transaksi == '5') {
// 			$keterangan = 'Dikirim';
// 		} else if ($status_transaksi == '6') {
// 			$keterangan = 'Diterima';
// 		} else if ($status_transaksi == '7') {
// 			$keterangan = 'Transaksi Selesai';
// 		} else if ($status_transaksi == '8') {
// 			$keterangan = 'Expired';
// 		} else if ($status_transaksi == '9') {
// 			$keterangan = 'Dibatalkan';
// 		} else if ($status_transaksi == '10') {
// 			$keterangan = 'Pembayaran Ditolak';
// 		} else {
// 			$keterangan = 'Pengembalian Barang';
// 		}

// 		$harga = $row['harga_normal'];
// 		$diskon = $row['diskon'];
// 		$harga_setelah = $row['harga_normal'];
// 		array_push($result, array(
// 			'id_promo'              => $row['id_promo'],
// 			'harga'                 => $harga,
// 			'harga_format'          => "Rp" . number_format($harga, 0, ',', '.'),
// 			'tanggal_acara_event'   => date('d F Y h:i:s A', strtotime($row['tanggal_acara_event'])),
// 			'nama_promo'            => $row['nama_promo'],
// 			'status_transaksi'      => $row['status_transaksi'],
// 			'keterangan_status'     => $keterangan,
// 			'event_nama_transaksi'  => $row['event_nama_transaksi'],
// 			'file_promo'            => $urlpromo.$row['file_promo'],
// 			'deskripsi_promo'       => $row['deskripsi_promo'],
// 			'id_transaksi'          => $row['id_transaksi'],
// 			'invoice'               => $row['invoice'],
// 			'payment_type'          => $row['payment_type'],
// 			'status_payment'        => $row['status_payment'],
// 			'url_payment'           => $row['url_payment'],
// 		));
// 	}

// 	if (isset($result[0])) {

// 		$result1['data_transaksi'] = $data;
// 		$result1['result'] = $result;

// 		echo json_encode($result1);

// 	} else {
// 		http_response_code(400);
// 		$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
// 		echo json_encode($respon);
// 	}
// 	break;
	case "listsudahdibeli":
	$id_user = $_POST['id_user'];

	$query = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail a 
		LEFT JOIN ba_promo b ON a.id_promo = b.id_promo 
		LEFT JOIN ba_transaksi_ebook c ON a.id_transaksi = c.id_transaksi
		WHERE a.id_user = '$id_user' AND a.status_pembelian = '3' AND c.status_transaksi = '7' AND b.tgl_habispromo >= NOW() ORDER BY a.id_transaksi DESC");
	$result = array();
	while ($row = mysqli_fetch_array($query)) {
		$query2 = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail a 
			LEFT JOIN ba_promo b ON a.id_promo = b.id_promo 
			LEFT JOIN ba_transaksi_ebook c ON a.id_transaksi = c.id_transaksi 
			WHERE a.id_user = '$id_user' AND a.status_pembelian = '3' AND c.status_transaksi = '7' AND b.tanggal_acara_event > NOW()")->num_rows;
		if ($query2 > 0){
			$ket_acara = 'Event akan datang';
		} else {
			$ket_acara = 'Event sudah Kadaluarsa';
		}

		array_push($result, array(
			'id_transaksi'                 => $row['id_transaksi'],
			'harga_normal'                 => "Rp" . number_format($harga_normal, 0, ',', '.'),
			'nama_promo'                   => $row['nama_promo'],
			'file_promo'                   => $urlpromo.$row['file_promo'],
			'link_promo'                   => $row['link_promo'],
			'deskripsi_promo'              => $row['deskripsi_promo'],
			'ket_event'                    => $ket_acara,
			'status_link'                  => $row['status_link'],
			'tanggal_acara_event'          => date('d F Y h:i:s A', strtotime($row['tanggal_acara_event'])),
		));
	}

	if (isset($result[0])) {
		echo json_encode($result);
	} else {
		http_response_code(400);
		$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
		echo json_encode($respon);
	}
	break;
	case "sertifikat":
	$id_user = $_POST['id_user'];

	$query = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail a 
		LEFT JOIN ba_promo b ON a.id_promo = b.id_promo 
		LEFT JOIN ba_transaksi_ebook c ON a.id_transaksi = c.id_transaksi 
		LEFT JOIN ba_sertifikat_event d ON c.id_transaksi = d.id_transaksi 
		WHERE a.id_user = '$id_user' AND a.status_pembelian = '3' AND c.status_transaksi = '7' AND b.tanggal_acara_event < NOW() ORDER BY a.id_transaksi DESC");
	$result = array();
	while ($row = mysqli_fetch_array($query)) {
		array_push($result, array(
			'id_sertifikat'                => $row['id_sertifikat'],
			'nama_promo'                   => $row['nama_promo'],
			'file_promo'                   => $urlpromo.$row['file_promo'],
			'tanggal_acara_event'          => date('d F Y h:i:s A', strtotime($row['tanggal_acara_event'])),
			'file_sertifikat'              => $row['file_sertifikat']."&status=I",
			'file_sertifikat_download'     => $row['file_sertifikat']."&status=D",
		));
	}

	if (isset($result[0])) {
		echo json_encode($result);
	} else {
		http_response_code(400);
		$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
		echo json_encode($respon);
	}
	break;
	case "listevent":
	$id_promo = $_POST['id_promo'];
// 	$id_user = $_POST['id_user'];

	if (empty($id_promo)){
		$query = mysqli_query($conn, "SELECT * FROM ba_promo WHERE status = '3' AND tgl_habispromo >= NOW() AND status_rmv = 'N' ORDER BY tanggal_acara_event DESC");
		$result = array();
		while ($row = mysqli_fetch_array($query)) {  
			array_push($result, array(
				'id_promo'              => $row['id_promo'],
				'nama_promo'            => $row['nama_promo'],
				'file_promo'            => $urlpromo.$row['file_promo'],
				'link_promo'            => $row['link_promo'],
				'deskripsi_promo'       => $row['deskripsi_promo'],
				'tanggal_acara_event'   => $row['tanggal_acara_event'],
				'harga_event'           => "Rp" . number_format($row['harga_event'], 0, ',', '.'),
			));
		}

		if (isset($result[0])) {
			echo json_encode($result);
		} else {
			http_response_code(400);
			$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
			echo json_encode($respon);
		}
	} else {

			$query = mysqli_query($conn, "SELECT * FROM ba_promo WHERE status = '3' AND id_promo = '$id_promo'");
			$result = array();
			while ($row = mysqli_fetch_array($query)) {
				array_push($result, array(
					'id_promo'              => $row['id_promo'],
					'nama_promo'            => $row['nama_promo'],
					'file_promo'            => $urlpromo.$row['file_promo'],
					'link_promo'            => $row['link_promo'],
					'deskripsi_promo'       => $row['deskripsi_promo'],
					'tanggal_acara_event'   => $row['tanggal_acara_event'],
					'harga_event'           => "Rp" . number_format($row['harga_event'], 0, ',', '.'),
				));
			}

			if (isset($result[0])) {
				echo json_encode($result);
			} else {
				http_response_code(400);
				$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
				echo json_encode($respon);
			}

	}

	break;
	case "listtransactionevent_detail":
	$id_transaksi = $_POST['id_transaksi'];

	$query2 = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook WHERE id_transaksi = '$id_transaksi' ORDER BY tgl_beli DESC")->fetch_assoc();
	$status_transaksi = $query2['status_transaksi'];
	if ($status_transaksi == '1') {
		$keterangan = 'Menunggu Pembayaran';
	} else if ($status_transaksi == '2') {
		$keterangan = 'Menunggu Verifikasi Pembayaran';
	} else if ($status_transaksi == '3') {
		$keterangan = 'Pembayaran Berhasil';
	} else if ($status_transaksi == '4') {
		$keterangan = 'Pembayaran Tidak Lengkap';
	} else if ($status_transaksi == '5') {
		$keterangan = 'Dikirim';
	} else if ($status_transaksi == '6') {
		$keterangan = 'Diterima';
	} else if ($status_transaksi == '7') {
		$keterangan = 'Transaksi Selesai';
	} else if ($status_transaksi == '8') {
		$keterangan = 'Expired';
	} else if ($status_transaksi == '9') {
		$keterangan = 'Dibatalkan';
	} else if ($status_transaksi == '10') {
		$keterangan = 'Pembayaran Ditolak';
	} else {
		$keterangan = 'Pengembalian Barang';
	}

	if ($query2['status_payment'] == '2') {
		$query3 = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook a LEFT JOIN ba_payment_manual b ON a.payment_type = b.id_payment WHERE a.id_transaksi = '$id_transaksi'")->fetch_assoc();
		$data['metode_pembayaran']         = $query3['metode_pembayaran'];
		$data['nomor_payment']         = $query3['nomor_payment'];
		$data['penerima_payment']         = $query3['penerima_payment'];
		$data['pesan'] = "";
		$data['nomor_telp']         = GETWA;
		$data['icon_payment']         = $geticonkategori . $query3['icon_payment'];
	}

	$data['invoice']         = $query2['invoice'];
	$data['tgl_beli']         = date('d F Y h:i:s A', strtotime($query2['tgl_beli']));
	$data['status_transaksi']         = $query2['status_transaksi'];
	$data['status_payment']         = $query2['status_payment'];
	$data['keterangan_status']         = $keterangan;
	$data['total_pembayaran_format']        = "Rp" . number_format($query2['total_pembayaran'], 0, ',', '.');
	$data['url_payment']         = $query2['url_payment'];
	$data['payment_type']         = $query2['payment_type'];
	$data['token_payment']         = $query2['token_payment'];
	$data['tanggal_dibayar']         = date('d F Y h:i:s A', strtotime($query2['tanggal_dibayar']));

	$query = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail a 
		LEFT JOIN ba_promo b ON a.id_promo = b.id_promo WHERE a.id_transaksi = '$id_transaksi'");
	$result = array();
	while ($row = mysqli_fetch_array($query)) {
		$harga = $row['harga_normal'];
		$diskon = $row['diskon'];
		$harga_setelah = $row['harga_diskon'];
		array_push($result, array(
			'id_promo'              => $row['id_promo'],
			'harga'                => $harga,
			'harga_format' => "Rp" . number_format($harga, 0, ',', '.'),
			'tanggal_acara_event'        => date('d F Y h:i:s A', strtotime($row['tanggal_acara_event'])),
			'nama_promo'                => $row['nama_promo'],
			'event_nama_transaksi' => $row['event_nama_transaksi'],
			'file_promo'                => $urlpromo.$row['file_promo'],
			'deskripsi_promo'        => $row['deskripsi_promo'],
		));
	}

	if (isset($result[0])) {

		$result1['data_transaksi'] = $data;
		$result1['result'] = $result;

		echo json_encode($result1);
	} else {
		http_response_code(400);
		$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
		echo json_encode($respon);
	}
	break;
	case "listevent_transaction":
	$id_transaksi = $_POST['id_transaksi'];

	$query2 = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook WHERE id_transaksi = '$id_transaksi' ORDER BY tgl_beli DESC")->fetch_assoc();
	$status_transaksi = $query2['status_transaksi'];
	if ($status_transaksi == '1') {
		$keterangan = 'Menunggu Pembayaran';
	} else if ($status_transaksi == '2') {
		$keterangan = 'Menunggu Verifikasi Pembayaran';
	} else if ($status_transaksi == '3') {
		$keterangan = 'Pembayaran Berhasil';
	} else if ($status_transaksi == '4') {
		$keterangan = 'Pembayaran Tidak Lengkap';
	} else if ($status_transaksi == '5') {
		$keterangan = 'Dikirim';
	} else if ($status_transaksi == '6') {
		$keterangan = 'Diterima';
	} else if ($status_transaksi == '7') {
		$keterangan = 'Transaksi Selesai';
	} else if ($status_transaksi == '8') {
		$keterangan = 'Expired';
	} else if ($status_transaksi == '9') {
		$keterangan = 'Dibatalkan';
	} else if ($status_transaksi == '10') {
		$keterangan = 'Pembayaran Ditolak';
	} else {
		$keterangan = 'Pengembalian Barang';
	}

	if ($query2['status_payment'] == '2') {
		$query3 = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook a LEFT JOIN ba_payment_manual b ON a.payment_type = b.id_payment WHERE a.id_transaksi = '$id_transaksi'")->fetch_assoc();
		$data['metode_pembayaran']         = $query3['metode_pembayaran'];
		$data['nomor_payment']         = $query3['nomor_payment'];
		$data['penerima_payment']         = $query3['penerima_payment'];
		$data['pesan'] = "";
		$data['nomor_telp']         = GETWA;
		$data['icon_payment']         = $geticonkategori . $query3['icon_payment'];
	}

	$data['invoice']         = $query2['invoice'];
	$data['tgl_beli']         = date('d F Y h:i:s A', strtotime($query2['tgl_beli']));
	$data['status_transaksi']         = $query2['status_transaksi'];
	$data['status_payment']         = $query2['status_payment'];
	$data['keterangan_status']         = $keterangan;
	$data['total_pembayaran_format']        = "Rp" . number_format($query2['total_pembayaran'], 0, ',', '.');
	
	if ($query2['url_payment'] == null ){
		$query2['url_payment'] == '';
	} 
	
	$data['url_payment']         = $query2['url_payment'];
	$data['payment_type']         = $query2['payment_type'];
	$data['token_payment']         = $query2['token_payment'];
	$data['tanggal_dibayar']         = date('d F Y h:i:s A', strtotime($query2['tanggal_dibayar']));

	$query = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail a 
		LEFT JOIN ba_promo b ON a.id_promo = b.id_promo WHERE a.id_transaksi = '$id_transaksi'");
	$result = array();
	while ($row = mysqli_fetch_array($query)) {
		$harga = $row['harga_normal'];
		$diskon = $row['diskon'];
		$harga_setelah = $row['harga_diskon'];
		array_push($result, array(
			'id_promo'              => $row['id_promo'],
			'harga'                => $harga,
			'harga_format' => "Rp" . number_format($harga, 0, ',', '.'),
			'tanggal_acara_event'        => date('d F Y h:i:s A', strtotime($row['tanggal_acara_event'])),
			'nama_promo'                => $row['nama_promo'],
			'event_nama_transaksi' => $row['event_nama_transaksi'],
			'file_promo'                => $urlpromo.$row['file_promo'],
			'deskripsi_promo'        => $row['deskripsi_promo'],
		));
	}

	if (isset($result[0])) {

		$result1['data_transaksi'] = $data;
		$result1['result'] = $result;

		echo json_encode($result1);
	} else {
		http_response_code(400);
		$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
		echo json_encode($respon);
	}
	break;
	case "listfileevent":
	$id_user = $_POST['id_user'];

	$query = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail a 
		LEFT JOIN ba_promo b ON a.id_promo = b.id_promo 
		LEFT JOIN ba_transaksi_ebook c ON a.id_transaksi = c.id_transaksi
		WHERE a.id_user = '$id_user' AND a.status_pembelian = '3' AND c.status_transaksi = '7' ORDER BY a.id_transaksi DESC");
	$result = array();
	while ($row = mysqli_fetch_array($query)) {
	    $ket_acara = "segera";
	    
	    if ($row['status_file_event'] == ''){
	        $file_event = "";
	    } else if ($row['status_file_event'] == '1'){
	        $file_event = $getfileevent."/".$row['file_event'];
	    } else if ($row['status_file_event'] == '2'){
	         $file_event = $row['file_event'];
	    }

		array_push($result, array(
			'id_transaksi'                 => $row['id_transaksi'],
			'harga_normal'                 => "Rp" . number_format($harga_normal, 0, ',', '.'),
			'nama_promo'                   => $row['nama_promo'],
			'file_promo'                   => $urlpromo.$row['file_promo'],
			'link_promo'                   => $row['link_promo'],
			'deskripsi_promo'              => $row['deskripsi_promo'],
			'status_file_event'            => $file_event,
			'file_event'                   => $row['status_file_event'],
			'ket_event'                    => $ket_acara,
			'status_link'                  => $row['status_link'],
			'tanggal_acara_event'          => date('d F Y h:i:s A', strtotime($row['tanggal_acara_event'])),
		));
	}

	if (isset($result[0])) {
		echo json_encode($result);
	} else {
		http_response_code(400);
		$respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
		echo json_encode($respon);
	}
	break;
}

mysqli_close($conn);

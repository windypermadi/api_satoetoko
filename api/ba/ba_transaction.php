<?php
require_once('koneksi.php');

$tag = $_POST['tag'];
$total_item = 0;

switch ($tag) {
    case "addtransaction":
        $id_user            = $_POST['id_user'];
        $jumlahbayar        = $_POST['jumlahbayar'];

        $idtransaksi = createID('id_transaksi', 'ba_transaksi_ebook', 'TR');
        $invoice = id_ke_struk($idtransaksi);
        $query = mysqli_query($conn, "INSERT INTO ba_transaksi_ebook (id_transaksi, invoice, id_user, batas_pembayaran, total_pembayaran) 
        VALUES ('$idtransaksi', '$invoice', '$id_user', DATE_ADD(NOW(), INTERVAL + 2 DAY), '$jumlahbayar')");

        if ($query) {
            $queryselect = mysqli_query($conn, "INSERT INTO ba_transaksi_ebook_detail (id_transaksi_detail, id_transaksi, id_user, id_buku, status_pembelian, harga_normal, diskon, harga_diskon)
            SELECT UUID(), '$idtransaksi', id_user, id_buku, status, harga_normal, diskon, harga_diskon FROM ba_keranjang_temp WHERE id_user = '$id_user'");
            if ($queryselect) {
                $querydeletecart = mysqli_query($conn, "DELETE FROM ba_keranjang_temp WHERE id_user = '$id_user'");
                $response['pesan'] = "Berhasil menambahkan transaksi! ";
                $response['id_transaksi'] = $idtransaksi;
                $response['no_invoice'] = id_ke_struk($idtransaksi);
                $response['total'] = $jumlahbayar;
                die(json_encode($response));
            } else {
                http_response_code(400);
                $response['pesan'] = "Id buku tidak masuk di detail transaksi";
                die(json_encode($response));
            }
        } else {
            http_response_code(400);
            $response['pesan'] = "Gagal menambahkan transaksi!";
            die(json_encode($response));
        }
        break;
    case "addtransaction_direct":
        $id_user            = $_POST['id_user'];
        $id_buku            = $_POST['id_buku'];
        $status             = $_POST['status'];
        $jumlahbayar        = $_POST['jumlahbayar'];
        $harga_normal       = $_POST['harga_normal'];
        $diskon             = $_POST['diskon'];
        $harga_diskon       = $_POST['harga_diskon'];

        $cek_transaksi = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail WHERE id_user = '$id_user' AND id_buku = '$id_buku' AND tgl_exp >= NOW()")->num_rows;

        if ($cek_transaksi > 0) {
            http_response_code(400);
            $respon['pesan'] = "Kamu masih punya ebook ini lho, dibaca jangan dianggurin yaa!\nKlik `Mengerti` untuk menutup pesan ini";
            die(json_encode($respon));
        } else {
            $idtransaksi = createID('id_transaksi', 'ba_transaksi_ebook', 'TR');
            $invoice = id_ke_struk($idtransaksi);
            $query = mysqli_query($conn, "INSERT INTO ba_transaksi_ebook (id_transaksi, invoice, id_user, batas_pembayaran, total_pembayaran) 
        VALUES ('$idtransaksi', '$invoice', '$id_user', DATE_ADD(NOW(), INTERVAL + 2 DAY), '$jumlahbayar')");

            if ($query) {
                $queryselect = mysqli_query($conn, "INSERT INTO ba_transaksi_ebook_detail (id_transaksi_detail, id_transaksi, id_user, id_buku, status_pembelian, harga_normal, diskon, harga_diskon)
            VALUES (UUID(), '$idtransaksi', '$id_user', '$id_buku', '$status', '$harga_normal', '$diskon', '$harga_diskon')");

                if ($queryselect) {
                    $response['pesan'] = "Berhasil menambahkan transaksi! ";
                    $response['id_transaksi'] = $idtransaksi;
                    $response['no_invoice'] = id_ke_struk($idtransaksi);
                    $response['total'] = $jumlahbayar;
                    die(json_encode($response));
                } else {
                    http_response_code(400);
                    $response['pesan'] = "Id buku tidak masuk di detail transaksi";
                    die(json_encode($response));
                }
            } else {
                http_response_code(400);
                $response['pesan'] = "Gagal menambahkan transaksi!";
                die(json_encode($response));
            }
        }
        break;
    case "listbarang_transaction":
        $id_transaksi = $_POST['id_transaksi'];
        $query = mysqli_query($conn, "SELECT a.id_buku,b.judul,b.penulis,b.edisi,b.tahun_terbit,b.cover,b.penerbit,
        b.bisa_beli,b.bisa_sewa,a.harga_normal,a.diskon,a.harga_diskon,c.nama_kategori,a.status_pembelian AS status FROM ba_transaksi_ebook_detail a 
        LEFT JOIN ba_buku b ON a.id_buku = b.id_buku
        LEFT JOIN itemkategorinew c ON b.kd_kategori = c.id_kategori WHERE a.id_transaksi = '$id_transaksi'");
        $result = array();
        while ($row = mysqli_fetch_array($query)) {

            //         if ($row['status'] == '1'){
            // 			if ($row['diskon_beli'] != 0){
            //                 $harga = $row['harga'];
            //                 $diskon = $row['diskon_beli'];
            //                 $harga_setelah = $row['harga_beli_setelah_diskon'];
            //             } else {
            //                 $harga = $row['harga'];
            //                 $diskon = 0;
            //                 $harga_setelah = 0;
            //             }
            // 		} else if ($row['status'] == '2'){
            // 			if ($row['diskon_sewa'] != 0){
            //                 $harga = $row['harga_sewa'];
            //                 $diskon = $row['diskon_sewa'];
            //                 $harga_setelah = $row['harga_sewa_setelah_diskon'];
            //             } else {
            //                 $harga = $row['harga_sewa'];
            //                 $diskon = 0;
            //                 $harga_setelah = 0;
            //             }
            // 		}
            $harga = $row['harga_normal'];
            $diskon = $row['diskon'];
            $harga_setelah = $row['harga_diskon'];
            array_push($result, array(
                'id_buku'              => $row['id_buku'],
                'harga'                => $harga,
                'harga_format'         => "Rp" . number_format($harga, 0, ',', '.'),
                'harga_potongan'       => $harga_setelah,
                'harga_potongan_format' => "Rp" . number_format($harga_setelah, 0, ',', '.'),
                'jumlah_diskon'        => $diskon,
                'judul'                => $row['judul'],
                'penulis'              => $row['penulis'],
                'cover'                => $urlimg . "/" . $row['cover'],
                'nama_kategori'        => $row['nama_kategori'],
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
    case "listtransaction":
        $id_user = $_POST['id_user'];
        $query = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook WHERE status_pembelian = '1' AND id_user = '$id_user' ORDER BY tgl_beli DESC");
        $result = array();
        while ($row = mysqli_fetch_array($query)) {
            $status_transaksi = $row['status_transaksi'];
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
                'id_transaksi'              => $row['id_transaksi'],
                'invoice'                => $row['invoice'],
                'tgl_beli'                => date('d F Y h:i:s A', strtotime($row['tgl_beli'])),
                'status_transaksi'              => $row['status_transaksi'],
                'keterangan_status'              => $keterangan,
                'total_pembayaran_format'        => "Rp" . number_format($row['total_pembayaran'], 0, ',', '.'),
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
    case "listtransaction_detail":
        $id_transaksi = $_POST['id_transaksi'];

        $query2 = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook WHERE status_pembelian = '1' AND id_transaksi = '$id_transaksi' ORDER BY tgl_beli DESC")->fetch_assoc();
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

        $query = mysqli_query($conn, "SELECT a.id_buku,b.judul,b.penulis,b.edisi,b.tahun_terbit,b.cover,b.penerbit,
        b.bisa_beli,b.bisa_sewa,a.harga_normal,a.diskon,a.harga_diskon,c.nama_kategori,a.status_pembelian AS status FROM ba_transaksi_ebook_detail a 
        LEFT JOIN ba_buku b ON a.id_buku = b.id_buku
        LEFT JOIN itemkategorinew c ON b.kd_kategori = c.id_kategori WHERE a.id_transaksi = '$id_transaksi'");
        $result = array();
        while ($row = mysqli_fetch_array($query)) {
            $harga = $row['harga_normal'];
            $diskon = $row['diskon'];
            $harga_setelah = $row['harga_diskon'];
            array_push($result, array(
                'id_buku'              => $row['id_buku'],
                'harga'                => $harga,
                'harga_format'         => "Rp" . number_format($harga, 0, ',', '.'),
                'harga_potongan'       => $harga_setelah,
                'harga_potongan_format' => "Rp" . number_format($harga_setelah, 0, ',', '.'),
                'jumlah_diskon'        => $diskon,
                'judul'                => $row['judul'],
                'penulis'              => $row['penulis'],
                'cover'                => $urlimg . "/" . $row['cover'],
                'nama_kategori'        => $row['nama_kategori'],
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
    case "update_payment_manual":
        $idtransaksi           = $_POST['no_invoice'];
        $payment_type          = $_POST['payment_type'];
        $total                 = $_POST['total'];
        $status_payment        = $_POST['status_payment'];
        $total_format          = "Rp" . number_format($total, 0, ',', '.');
        $invoice               = id_ke_struk($idtransaksi);

        $query = mysqli_query($conn, "SELECT * FROM ba_payment_manual WHERE id_payment = '$payment_type'")->fetch_assoc();
        $metode_pembayaran = $query['metode_pembayaran'];
        $nomor_payment = $query['nomor_payment'];
        $penerima_payment = $query['penerima_payment'];

        // if ($status_payment != '1' OR $status_payment != '2'){
        //     $status_payment = '0';
        // } else {
        //     $status_payment = $_POST['status_payment'];
        // }

        $query2 = mysqli_query($conn, "UPDATE ba_transaksi_ebook SET status_payment = '$status_payment', payment_type = '$payment_type' WHERE id_transaksi = '$idtransaksi'");
        if ($query2) {
            $response['pesan'] = "Halo Bapak/Ibu, Silahkan melakukan pembayaran manual dengan mengirimkan bukti transaksi.\n\nBerikut informasi tagihan anda : \nNomor Invoice : *$invoice*\nJumlah     : *$total_format*\nBank Transfer : *$metode_pembayaran*\nNo Rekening : *$nomor_payment*\nAtas Nama : *$penerima_payment*\n\nJika ada pertanyaan lebih lanjut, anda dapat membalas langsung pesan ini.\n\nTerimakasih\nHormat Kami, \n\nTim Bahana Digital";
            $response['nomor_telp']         = GETWA;
            die(json_encode($response));
        } else {
            http_response_code(400);
            $response['pesan'] = "Gagal 500";
            die(json_encode($response));
        }
        break;
    default:
        break;
}

mysqli_close($conn);

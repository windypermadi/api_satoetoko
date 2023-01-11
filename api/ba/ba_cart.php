<?php
require_once('koneksi.php');

$tag = $_POST['tag'];
$total_item = 0;

switch ($tag) {
    case "addcart":
        $id_user = $_POST['id_user'];
        $id_buku = $_POST['id_buku'];
        $status  = $_POST['status'];

        $cek_transaksi = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail WHERE id_user = '$id_user' AND id_buku = '$id_buku' AND tgl_exp >= NOW()")->num_rows;

        $cek_keranjang = mysqli_query($conn, "SELECT * FROM ba_keranjang_temp WHERE id_user = '$id_user' AND id_buku = '$id_buku'")->num_rows;
        $getbuku = mysqli_query($conn, "SELECT * FROM ba_buku WHERE id_buku = '$id_buku'")->fetch_assoc();

        if ($cek_transaksi > 0) {
            http_response_code(400);
            $respon['pesan'] = "Kamu masih punya ebook ini lho, dibaca jangan dianggurin yaa!\nKlik `Mengerti` untuk menutup pesan ini";
            die(json_encode($respon));
        } else {
            if ($cek_keranjang > 0) {
                http_response_code(400);
                $respon['pesan'] = "Ebook ini sudah ada di keranjang kamu lho!\nKlik `Mengerti` untuk menutup pesan ini";
                die(json_encode($respon));
            } else {
                if (isset($id_buku)) {
                    if (isset($status)) {

                        if ($status == '1') {
                            $jumlah_diskon = $getbuku['diskon_beli'];
                            $total_tanpa_diskon = $getbuku['harga'];
                            if ($getbuku['diskon_beli'] != 0) {
                                $total_item = $getbuku['harga_beli_setelah_diskon'];
                            } else {
                                $total_item = $getbuku['harga'];
                            }
                        } else if ($status == '2') {
                            $jumlah_diskon = $getbuku['diskon_sewa'];
                            $total_tanpa_diskon = $getbuku['harga_sewa'];
                            if ($getbuku['diskon_sewa'] != 0) {
                                $total_item = $getbuku['harga_sewa_setelah_diskon'];
                            } else {
                                $total_item = $getbuku['harga_sewa'];
                            }
                        }

                        $query = mysqli_query($conn, "INSERT INTO `ba_keranjang_temp`(`id_cart`, `id_buku`, `id_user`, `status`, `harga_normal`, `diskon`, `harga_diskon`)VALUES (UUID(), '$id_buku', '$id_user', '$status', '$total_tanpa_diskon', '$jumlah_diskon', '$total_item')");

                        if ($query) {
                            $respon['pesan'] = "Ebook berhasil masuk dalam keranjang.\nHKlik mengerti untuk menutup pesan ini.";
                            die(json_encode($respon));
                        } else {
                            http_response_code(400);
                            $respon['pesan'] = "Gagal menambahkan ebook dalam keranjang!\nKlik `Mengerti` untuk menutup pesan ini";
                            die(json_encode($respon));
                        }
                    } else {
                        http_response_code(400);
                        $respon['pesan'] = "status keranjang tidak ada!\nKlik `Mengerti` untuk menutup pesan ini";
                        die(json_encode($respon));
                    }
                } else {
                    http_response_code(400);
                    $respon['pesan'] = "ebook tidak ada!\nKlik `Mengerti` untuk menutup pesan ini";
                    die(json_encode($respon));
                }
            }
        }
        break;
    case "carttotal":
        $id_user = $_POST['id_user'];

        $sql    = mysqli_query($conn, "SELECT b.harga,b.harga_sewa,b.diskon_beli,a.status,b.harga_beli_setelah_diskon,b.harga_sewa_setelah_diskon, b.diskon_sewa FROM ba_keranjang_temp a
		INNER JOIN ba_buku b ON a.id_buku = b.id_buku
		WHERE a.id_user = '$id_user'");
        $jumlah_barang = mysqli_num_rows($sql);

        while ($row = mysqli_fetch_array($sql)) {
            if ($row['status'] == '1') {
                if ($row['diskon_beli'] != 0) {
                    $total_item = $total_item + $row['harga_beli_setelah_diskon'];
                } else {
                    $total_item = $total_item + $row['harga'];
                }
            } else if ($row['status'] == '2') {
                if ($row['diskon_sewa'] != 0) {
                    $total_item = $total_item + $row['harga_sewa_setelah_diskon'];
                } else {
                    $total_item = $total_item + $row['harga_sewa'];
                }
            }
        }

        $result['jumlah_item']                     = $jumlah_barang;
        $result['total_pembayaran']                = $total_item;
        $result['total_pembayaran_format']            = "Rp" . number_format($total_item, 0, ',', '.');

        echo json_encode($result);
        break;

    case "listcart":
        $id_user = $_POST['id_user'];

        $query = mysqli_query($conn, "SELECT a.id_cart,a.id_buku,b.judul,b.penulis,b.edisi,b.tahun_terbit,b.cover,b.penerbit,
		b.bisa_beli,b.bisa_sewa,b.harga,b.harga_sewa,b.diskon_beli,c.nama_kategori,a.status,b.harga_beli_setelah_diskon,b.harga_sewa_setelah_diskon, b.diskon_sewa FROM ba_keranjang_temp a 
		LEFT JOIN ba_buku b ON a.id_buku = b.id_buku
		LEFT JOIN itemkategorinew c ON b.kd_kategori = c.id_kategori WHERE a.id_user = '$id_user' ORDER BY a.id_cart DESC");
        $result = array();
        while ($row = mysqli_fetch_array($query)) {
            $cek_transaksi = mysqli_query($conn, "SELECT * FROM ba_transaksi_ebook_detail WHERE id_user = '$id_user' AND id_buku = '$row[id_buku]' AND tgl_exp >= NOW()")->num_rows;
            if ($cek_transaksi > 0){
                $querydeletecart = mysqli_query($conn, "DELETE FROM ba_keranjang_temp WHERE id_user = '$id_user' AND id_buku = '$row[id_buku]'");  
            } 
            if ($row['status'] == '1') {
                if ($row['diskon_beli'] != 0) {
                    $harga = $row['harga'];
                    $diskon = $row['diskon_beli'];
                    $harga_setelah = $row['harga_beli_setelah_diskon'];
                } else {
                    $harga = $row['harga'];
                    $diskon = 0;
                    $harga_setelah = 0;
                }
            } else if ($row['status'] == '2') {
                if ($row['diskon_sewa'] != 0) {
                    $harga = $row['harga_sewa'];
                    $diskon = $row['diskon_sewa'];
                    $harga_setelah = $row['harga_sewa_setelah_diskon'];
                } else {
                    $harga = $row['harga_sewa'];
                    $diskon = 0;
                    $harga_setelah = 0;
                }
            }
            array_push($result, array(
                'id_buku'               => $row['id_buku'],
                'harga'                => $harga,
                'harga_format'            => "Rp" . number_format($harga, 0, ',', '.'),
                'harga_potongan'       => $harga_setelah,
                'harga_potongan_format' => "Rp" . number_format($harga_setelah, 0, ',', '.'),
                'jumlah_diskon'        => $diskon,
                'judul'                   => $row['judul'],
                'penulis'               => $row['penulis'],
                'cover'                   => $urlimg . "/" . $row['cover'],
                'nama_kategori'        => $row['nama_kategori'],
            ));
        }

        if (isset($result[0])) {
                echo json_encode($result);
        } else {
            http_response_code(400);
            $respon['pesan'] = "Tidak ada data yang ditampilkan!\nKlik `Mengerti` untuk menutup pesan ini";
            $respon['kode']  = "0";
            echo json_encode($respon);
        }
        break;
    case "deletecart":
        $id_user = $_POST['id_user'];
        $id_buku = $_POST['id_buku'];

        if (isset($id_buku)) {
            $query = mysqli_query($conn, "DELETE FROM ba_keranjang_temp WHERE id_user = '$id_user' AND id_buku = '$id_buku'");
            if ($query) {
                $respon['pesan'] = "Ebook ini berhasil dihapus dari keranjang.\nHKlik mengerti untuk menutup pesan ini.";
                die(json_encode($respon));
            } else {
                http_response_code(400);
                $respon['pesan'] = "Gagal menghapus ebook dari keranjang!\nKlik `Mengerti` untuk menutup pesan ini";
                die(json_encode($respon));
            }
        } else {
            http_response_code(400);
            $respon['pesan'] = "ebook tidak ada!\nKlik `Mengerti` untuk menutup pesan ini";
            die(json_encode($respon));
        }
        break;
    default:
        break;
}

mysqli_close($conn);

<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id             = $_POST['id'];
$jumlah         = $_POST['jumlah'];

if (isset($id)) {

    if ($jumlah == 0) {
        $getdata = $conn->query("SELECT * FROM user_keranjang WHERE id = '$id'");
        $data = $getdata->fetch_object();

        if ($getdata->num_rows == 0) {
            $response->data = null;
            $response->message = 'Data tidak ditemukan.';
            $response->error(400);
        } else {
            $cekitemdata = $conn->query("DELETE FROM user_keranjang WHERE id = '$id'");

            if ($cekitemdata) {
                $response->data = $data1;
                $response->sukses(200);
            } else {
                $response->data = null;
                $response->error(400);
            }
        }
    } else {
        $cekcart = $conn->query("SELECT * FROM user_keranjang WHERE id = '$id'")->num_rows;

        if ($cekcart > 0) {
            $data = $conn->query("SELECT * FROM user_keranjang a
        JOIN master_item b ON a.id_barang = b.id_master
        WHERE a.id = '$id'")->fetch_object();

            //! cek stok di varian dan tidak varian
            if ($data->status_varian == 'Y') {
                $cekstok = $conn->query("SELECT * FROM user_keranjang a
            JOIN stok b ON a.id_variant = b.id_varian
            JOIN variant c ON a.id_variant = c.id_variant
            WHERE a.id = '$id' GROUP BY a.id")->fetch_object();

                if ($cekstok->jumlah > 0) {
                    $query = mysqli_query($conn, "UPDATE user_keranjang SET qty = '$jumlah' WHERE id = '$cekstok->id'");

                    if ($cekstok->diskon_rupiah_varian != 0) {

                        $status_diskon = 'Y';
                        $harga_disc = $cekstok->harga_varian - $cekstok->diskon_rupiah_varian;

                        $harga_produk = rupiah($cekstok->harga_varian);
                        $harga_tampil = rupiah($harga_disc);
                        $harga_produk_int = $cekstok->harga_varian;
                        $harga_tampil_int = $harga_disc;
                    } else {
                        $status_diskon = 'N';
                        $harga_disc = $cekstok->harga_varian;

                        $harga_produk = rupiah($cekstok->harga_varian);
                        $harga_tampil = rupiah($harga_disc);
                        $harga_produk_int = $cekstok->harga_varian;
                        $harga_tampil_int = $harga_disc;
                    }

                    $keterangan_varian = "";
                } else {
                    $response->data = null;
                    $response->message = 'Stok untuk barang ini sudah habis.';
                    $response->error(400);
                }
            } else {
                $cekstok = $conn->query("SELECT * FROM user_keranjang a
            JOIN stok b ON a.id_barang = b.id_barang
            JOIN master_item c ON a.id_barang = c.id_master
            WHERE a.id = '$id' GROUP BY a.id")->fetch_object();

                if ($cekstok->jumlah > 0) {
                    $query = mysqli_query($conn, "UPDATE user_keranjang SET qty = '$jumlah' WHERE id = '$cekstok->id'");

                    if ($cekstok->diskon_rupiah_varian != 0) {

                        $status_diskon = 'Y';
                        $harga_disc = $cekstok->harga_master - $cekstok->diskon_rupiah;

                        $harga_produk = rupiah($cekstok->harga_master);
                        $harga_tampil = rupiah($harga_disc);
                        $harga_produk_int = $cekstok->harga_master;
                        $harga_tampil_int = $harga_disc;
                    } else {
                        $status_diskon = 'N';
                        $harga_disc = $cekstok->harga_master;

                        $harga_produk = rupiah($cekstok->harga_master);
                        $harga_tampil = rupiah($harga_disc);
                        $harga_produk_int = $cekstok->harga_master;
                        $harga_tampil_int = $harga_disc;
                    }

                    $keterangan_varian = $cekstok->keterangan_varian;
                } else {
                    $response->data = null;
                    $response->message = 'Stok untuk barang ini sudah habis.';
                    $response->error(400);
                }
            }
        } else {
            $response->data = null;
            $response->message = 'keranjang ini tidak ditemukan.';
            $response->error(400);
        }

        $data1 = [
            'id' => $cekstok->id,
            'image_master' => $cekstok->status_master_detail == '2' ? $getimagebukufisik . $cekstok->image_master : $getimagefisik . $cekstok->image_master,
            'judul' => $cekstok->judul_master,
            'id_varian' => $cekstok->id_variant,
            'varian' => $keterangan_varian,
            'harga_produk' => $harga_produk,
            'harga_tampil' => $harga_tampil,
            'harga_produk_int' => (int)$harga_produk_int,
            'harga_tampil_int' => (int)$harga_tampil_int,
            'status_diskon' => $status_diskon,
            'qty' => $cekstok->qty,
            'stok_saatini' => $cekstok->jumlah,
            'id_cabang' => $cekstok->id_gudang,
        ];

        if ($query) {
            $response->data = $data1;
            $response->sukses(200);
        } else {
            $response->data = null;
            $response->error(400);
        }
    }
} else {
    $response->data = null;
    $response->message = 'id cart tidak ditemukan.';
    $response->error(400);
}
die();
mysqli_close($conn);

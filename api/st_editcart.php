<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$id             = $_POST['id'];
$jumlah         = $_POST['jumlah'];

if (isset($id)) {

    $getdata = $conn->query("SELECT * FROM user_keranjang WHERE id = '$id'");
    $data = $getdata->fetch_object();

    $query = mysqli_query($conn, "UPDATE user_keranjang SET qty = '$jumlah' WHERE id = '$data->id'");

    $data2 = $conn->query("SELECT * FROM user_keranjang a
    JOIN master_item b ON a.id_barang = b.id_master
    LEFT JOIN variant c ON a.id_variant = c.id_variant
    WHERE a.id = '$id';")->fetch_object();

    if ($data2->status_varian == 'Y') {

        $cekstok = $conn->query("SELECT * FROM user_keranjang a
            JOIN stok b ON a.id_variant = b.id_varian
            JOIN variant c ON a.id_variant = c.id_variant
            WHERE a.id = '$id' GROUP BY a.id")->fetch_object();

        if ($data2->diskon_rupiah_varian != 0) {
            $status_diskon = 'Y';
            $harga_disc = $data2->harga_varian - $data2->diskon_rupiah_varian;
        } else {
            $status_diskon = 'N';
            $harga_disc = $data2->harga_varian;
        }

        $harga_produk = rupiah($data2->harga_varian);
        $harga_tampil = rupiah($harga_disc);
        $harga_produk_int = $data2->harga_varian;
        $harga_tampil_int = $harga_disc;
    } else {

        $cekstok = $conn->query("SELECT * FROM user_keranjang a
            JOIN stok b ON a.id_barang = b.id_barang
            JOIN master_item c ON a.id_barang = c.id_master
            WHERE a.id = '$id' GROUP BY a.id")->fetch_object();

        if ($data2->diskon_persen != 0) {
            $status_diskon = 'Y';
            $harga_disc = $data2->harga_master - $data2->diskon_rupiah;
        } else {
            $status_diskon = 'N';
            $harga_disc = $data2->harga_master;
        }

        $harga_produk = rupiah($data2->harga_master);
        $harga_tampil = rupiah($harga_disc);
        $harga_produk_int = $data2->harga_master;
        $harga_tampil_int = $harga_disc;
    }

    $data1 = [
        'id' => $data2->id,
        'image_master' => $data2->status_master_detail == '2' ? $getimagebukufisik . $data2->image_master : $getimagefisik . $data2->image_master,
        'judul' => $data2->judul_master,
        'id_varian' => $data2->id_variant,
        'varian' => $data2->keterangan_varian,
        'harga_produk' => $harga_produk,
        'harga_tampil' => $harga_tampil,
        'harga_produk_int' => $harga_produk_int,
        'harga_tampil_int' => $harga_tampil_int,
        'status_diskon' => $status_diskon,
        'qty' => $data2->qty,
        'stok_saatini' => $cekstok->jumlah,
        'id_cabang' => $data2->id_gudang,
    ];

    if ($jumlah == 0) {

        $getdata = $conn->query("SELECT * FROM user_keranjang WHERE id = '$id'");
        $data = $getdata->fetch_object();

        if ($getdata->num_rows == 0) {
            $response->data = "Nothing data in cart user";
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

        if ($data2->status_varian == 'Y') {

            $cekstok = $conn->query("SELECT * FROM user_keranjang a
            JOIN stok b ON a.id_variant = b.id_varian
            JOIN variant c ON a.id_variant = c.id_variant
            WHERE a.id = '$id' GROUP BY a.id")->fetch_object();

            if ($data2->diskon_rupiah_varian != 0) {
                $status_diskon = 'Y';
                $harga_disc = $data2->harga_varian - $data2->diskon_rupiah_varian;
            } else {
                $status_diskon = 'N';
                $harga_disc = $data2->harga_varian;
            }

            $harga_produk = rupiah($data2->harga_varian);
            $harga_tampil = rupiah($harga_disc);
            $harga_produk_int = $data2->harga_varian;
            $harga_tampil_int = $harga_disc;
        } else {

            $cekstok = $conn->query("SELECT * FROM user_keranjang a
            JOIN stok b ON a.id_barang = b.id_barang
            JOIN master_item c ON a.id_barang = c.id_master
            WHERE a.id = '$id' GROUP BY a.id")->fetch_object();

            if ($data2->diskon_persen != 0) {
                $status_diskon = 'Y';
                $harga_disc = $data2->harga_master - $data2->diskon_rupiah;
            } else {
                $status_diskon = 'N';
                $harga_disc = $data2->harga_master;
            }

            $harga_produk = rupiah($data2->harga_master);
            $harga_tampil = rupiah($harga_disc);
            $harga_produk_int = $data2->harga_master;
            $harga_produk_int = $harga_disc;
        }

        $data1 = [
            'id' => $data2->id,
            'image_master' => $data2->status_master_detail == '2' ? $getimagebukufisik . $data2->image_master : $getimagefisik . $data2->image_master,
            'judul' => $data2->judul_master,
            'id_varian' => $data2->id_variant,
            'varian' => $data2->keterangan_varian,
            'harga_produk' => $harga_produk,
            'harga_tampil' => $harga_tampil,
            'harga_produk_int' => $harga_produk_int,
            'harga_tampil_int' => $harga_tampil_int,
            'status_diskon' => $status_diskon,
            'qty' => $data2->qty,
            'stok_saatini' => $cekstok->jumlah,
            'id_cabang' => $data2->id_gudang,
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
    $response->error(400);
}
die();
mysqli_close($conn);

<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$iduser  = $_POST['iduser'];
$idtransaksi  = $_POST['idtransaksi'];

if (!empty($iduser) && !empty($idtransaksi)) {
    $que = "SELECT * FROM transaksi WHERE id_transaksi = '$idtransaksi'";
    $cektransaksi = $conn->query($que)->fetch_object();

    if (isset($cektransaksi->id_transaksi)) {
        //* cek ambil ditempat
        if ($cektransaksi->kurir_code == '00') {
            if ($cektransaksi->st_packing == '3') {
                $query = $conn->query("UPDATE transaksi SET status_transaksi = '7', tanggal_diterima = NOW(), st_packing = '4' WHERE id_transaksi = '$idtransaksi'");

                if ($query) {
                    $response->data = "Pesanan ini berhasil dikonfirmasi";
                    $response->sukses(200);
                } else {
                    $response->data = [];
                    $response->message = 'Pesanan ini gagal dikonfirmasi';
                    $response->error(400);
                }
            } else {
                $response->data = [];
                $response->message = 'Pesanan kamu belum dipacking';
                $response->error(400);
            }
        } else {
            //* cek transaksi
            if ($cektransaksi->status_transaksi == '5') {
                $query = $conn->query("UPDATE transaksi SET status_transaksi = '7', tanggal_diterima = NOW() WHERE id_transaksi = '$idtransaksi'");

                if ($query) {
                    $response->data = "Pesanan ini berhasil dikonfirmasi";
                    $response->sukses(200);
                } else {
                    $response->data = [];
                    $response->message = 'Pesanan ini gagal dikonfirmasi';
                    $response->error(400);
                }   
            } else {
                $response->data = [];
                $response->message = 'Pesanan kamu belum dikirim';
                $response->error(400);
            }
        }
    } else {
        $response->data = [];
        $response->message = 'Transaksi ini tidak sah';
        $response->error(400);
    }

    if ($cektransaksi->kurir_code == '00') {
        if ($cektransaksi->status_transaksi == '3') {
            if ($cektransaksi->st_packing == '3') {
            } else {
            }
        } else {
        }
    } else {
    }
} else {
    $response->data = [];
    $response->message = 'Kesalahan dalam server';
    $response->error(400);
}

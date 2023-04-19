<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$iduser  = $_POST['iduser'] ?? '';
$idvoucher  = $_POST['idvoucher'] ?? '';
$kode = $_POST['kode_voucher'] ?? '';

!empty($kode) ? $kodevoucher = " AND kode_voucher = '$kode'" : $kodevoucher = "";

if (!empty($iduser)) {
    $cekkuota = "SELECT * FROM voucher WHERE idvoucher = '$idvoucher' $kodevoucher AND kuota_voucher != 0";
    $cekkuota2 = $conn->query($cekkuota)->fetch_object();

    if (!empty($kode)) {
        $conn->begin_transaction();

        $voucherkode = $conn->query("SELECT * FROM voucher WHERE kode_voucher = '$kode'")->fetch_object();

        $query[] = $conn->query("UPDATE voucher SET kuota_voucher = '$cekkuota2->kuota_voucher'-1 WHERE kode_voucher = '$kode'");

        $query[] = $conn->query("INSERT INTO voucher_user SET iduser_voucher = UUID_SHORT(),
                    iduser = '$iduser',
                    idvoucher = '$voucherkode->idvoucher',
                    tgl_klaim = CURRENT_TIME(),
                    status_pakai = '0'");

        if (in_array(false, $query)) {
            $response->data = [];
            $response->message = 'Voucher ini gagal kamu upload.';
            $response->error(400);
        } else {
            $conn->commit();
            $response->data = "Selamat kamu berhasil klaim voucher ini.";
            $response->sukses(200);
        }
        die;
    } else {

        if (isset($cekkuota2->idvoucher)) {
            //? cek sudah klaim belum
            $query_klaim = "SELECT * FROM voucher_user WHERE idvoucher = '$idvoucher' AND iduser = '$iduser'";
            $cekklaim = $conn->query($query_klaim)->fetch_object();

            if (isset($cekklaim->iduser_voucher)) {
                //? jika sudah diklaim
                if ($cekkuota2->status_berulang == 'Y') {
                    if ($cekklaim->status_pakai == '1') {
                        $conn->begin_transaction();

                        $query[] = $conn->query("UPDATE voucher SET kuota_voucher = '$cekkuota2->kuota_voucher'-1 WHERE idvoucher = '$idvoucher' OR kode_voucher = '$kode'");

                        $query[] = $conn->query("INSERT INTO voucher_user SET iduser_voucher = UUID_SHORT(),
                    iduser = '$iduser',
                    idvoucher = '$idvoucher',
                    tgl_klaim = CURRENT_TIME(),
                    status_pakai = '0'");

                        if (in_array(false, $query)) {
                            $response->data = [];
                            $response->message = 'Voucher ini gagal kamu upload.';
                            $response->error(400);
                        } else {
                            $conn->commit();
                            $response->data = "Selamat kamu berhasil klaim voucher ini.";
                            $response->sukses(200);
                        }
                        die;
                    } else {
                        $response->data = [];
                        $response->message = 'Voucher ini belum dipakai.';
                        $response->error(400);
                    }
                } else {
                    $response->data = [];
                    $response->message = 'Kamu sudah klaim voucher ini.';
                    $response->error(400);
                }
            } else {
                $conn->begin_transaction();

                $query[] = $conn->query("UPDATE voucher SET kuota_voucher = '$cekkuota2->kuota_voucher'-1 WHERE idvoucher = '$idvoucher' OR kode_voucher = '$kode'");

                $query[] = $conn->query("INSERT INTO voucher_user SET iduser_voucher = UUID_SHORT(),
                iduser = '$iduser',
                idvoucher = '$idvoucher',
                tgl_klaim = CURRENT_TIME(),
                status_pakai = '0'");

                if (in_array(false, $query)) {
                    $response->data = [];
                    $response->message = 'Voucher ini gagal kamu upload.';
                    $response->error(400);
                } else {
                    $conn->commit();
                    $response->data = "Selamat kamu berhasil klaim voucher ini.";
                    $response->sukses(200);
                }
                die;
            }
        } else {
            $response->data = [];
            $response->message = 'Stok voucher ini sudah habis.';
            $response->error(400);
        }
    }
}

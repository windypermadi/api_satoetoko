<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$tag = $_REQUEST['tag'];
$iduser = $_REQUEST['iduser'];

switch ($tag) {
    case "semua":
        $q = $_GET['q'] ?? '';
        if (!empty($q)) {
            $search = " AND nama_voucher LIKE '%$q%'";
        } else {
            $search = "";
        }
        $data = $conn->query("SELECT * FROM voucher WHERE tgl_mulai <= NOW() AND tgl_berakhir >= NOW() $search AND kuota_voucher != 0 AND tipe_voucher != '2'");
        foreach ($data as $key => $value) {

            $cekpunya = $conn->query("SELECT * FROM voucher_user vu 
            JOIN voucher v ON vu.idvoucher = v.idvoucher 
            WHERE vu.iduser = '$iduser' AND vu.idvoucher = '$value[idvoucher]'")->fetch_object();
            if ($cekpunya->status_berulang == 'Y') {
                if ($cekpunya->status_pakai == '1') {
                    $status = False;
                } else {
                    $status = True;
                }
            } else {
                if ($cekpunya->iduser_voucher) {
                    $status = True;
                } else {
                    $status = False;
                }
            }

            $ketstatus = statusvoucher($value['status_voucher']);
            $datalist[] = [
                'idvoucher' => $value['idvoucher'],
                'kode_voucher' => $value['kode_voucher'],
                'nama_voucher' => $value['nama_voucher'],
                'deskripsi_voucher' => $value['deskripsi_voucher'],
                'nilai_voucher' => (int)$value['nilai_voucher'],
                'minimal_transaksi' => (int)$value['minimal_transaksi'],
                'tgl_mulai' => $value['tgl_mulai'],
                'tgl_berakhir' => $value['tgl_berakhir'],
                'status_voucher' => $value['status_voucher'],
                'ket_status' => $ketstatus,
                'status_klaim' => $status
            ];
        }

        if (isset($datalist[0])) {
            $response->data = $datalist;
            $response->sukses(200);
        } else {
            $response->data = [];
            $response->sukses(200);
        }
        die();
        break;
    case "kode":
        $q = $_REQUEST['kode'] ?? '';
        if (!empty($q)) {
            $search = " AND kode_voucher = '$q' AND tipe_voucher = '2'";
        }
        $data = $conn->query("SELECT * FROM voucher WHERE tgl_mulai <= NOW() AND tgl_berakhir >= NOW() AND kode_voucher = '$q' AND tipe_voucher = '2' AND kuota_voucher != 0");
        foreach ($data as $key => $value) {

            $cekpunya = $conn->query("SELECT * FROM voucher_user vu 
            JOIN voucher v ON vu.idvoucher = v.idvoucher 
            WHERE vu.iduser = '$iduser' AND vu.idvoucher = '$value[idvoucher]'")->fetch_object();
            if ($cekpunya->status_berulang == 'Y') {
                if ($cekpunya->status_pakai == '1') {
                    $status = False;
                } else {
                    $status = True;
                }
            } else {
                if ($cekpunya->iduser_voucher) {
                    $status = True;
                } else {
                    $status = False;
                }
            }

            $ketstatus = statusvoucher($value['status_voucher']);
            $datalist[] = [
                'idvoucher' => $value['idvoucher'],
                'kode_voucher' => $value['kode_voucher'],
                'nama_voucher' => $value['nama_voucher'],
                'deskripsi_voucher' => $value['deskripsi_voucher'],
                'nilai_voucher' => (int)$value['nilai_voucher'],
                'minimal_transaksi' => (int)$value['minimal_transaksi'],
                'tgl_mulai' => $value['tgl_mulai'],
                'tgl_berakhir' => $value['tgl_berakhir'],
                'status_voucher' => $value['status_voucher'],
                'ket_status' => $ketstatus,
                'status_klaim' => $status
            ];
        }

        if (isset($datalist[0])) {
            $response->data = $datalist;
            $response->sukses(200);
        } else {
            $response->data = [];
            $response->sukses(200);
        }
        die();
        break;
    case "ebook":
        $q = $_GET['q'] ?? '';
        if (!empty($q)) {
            $search = " AND nama_voucher LIKE '%$q%' AND tipe_voucher != '2'";
        } else {
            $search = "";
        }
        $data = $conn->query("SELECT * FROM voucher WHERE status_voucher = '1' $search AND tgl_mulai <= NOW() AND tgl_berakhir >= NOW() AND kuota_voucher != 0 AND tipe_voucher != '2'");
        foreach ($data as $key => $value) {

            $cekpunya = $conn->query("SELECT * FROM voucher_user vu 
            JOIN voucher v ON vu.idvoucher = v.idvoucher 
            WHERE vu.iduser = '$iduser' AND vu.idvoucher = '$value[idvoucher]'")->fetch_object();
            if ($cekpunya->status_berulang == 'Y') {
                if ($cekpunya->status_pakai == '1') {
                    $status = False;
                } else {
                    $status = True;
                }
            } else {
                if ($cekpunya->iduser_voucher) {
                    $status = True;
                } else {
                    $status = False;
                }
            }

            $ketstatus = statusvoucher($value['status_voucher']);
            $datalist[] = [
                'idvoucher' => $value['idvoucher'],
                'kode_voucher' => $value['kode_voucher'],
                'nama_voucher' => $value['nama_voucher'],
                'deskripsi_voucher' => $value['deskripsi_voucher'],
                'nilai_voucher' => (int)$value['nilai_voucher'],
                'minimal_transaksi' => (int)$value['minimal_transaksi'],
                'tgl_mulai' => $value['tgl_mulai'],
                'tgl_berakhir' => $value['tgl_berakhir'],
                'status_voucher' => $value['status_voucher'],
                'ket_status' => $ketstatus,
                'status_klaim' => $status
            ];
        }

        if (isset($datalist[0])) {
            $response->data = $datalist;
            $response->sukses(200);
        } else {
            $response->data = [];
            $response->sukses(200);
        }
        die();
        break;
    case "ongkir":
        $q = $_GET['q'] ?? '';
        if (!empty($q)) {
            $search = " AND nama_voucher LIKE '%$q%' AND tipe_voucher != '2'";
        } else {
            $search = "";
        }
        $data = $conn->query("SELECT * FROM voucher WHERE status_voucher = '2' AND tgl_mulai <= NOW() AND tgl_berakhir >= NOW() AND kuota_voucher != 0 AND tipe_voucher != '2'");
        foreach ($data as $key => $value) {

            $cekpunya = $conn->query("SELECT * FROM voucher_user vu 
            JOIN voucher v ON vu.idvoucher = v.idvoucher 
            WHERE vu.iduser = '$iduser' AND vu.idvoucher = '$value[idvoucher]'")->fetch_object();
            if ($cekpunya->status_berulang == 'Y') {
                if ($cekpunya->status_pakai == '1') {
                    $status = False;
                } else {
                    $status = True;
                }
            } else {
                if ($cekpunya->iduser_voucher) {
                    $status = True;
                } else {
                    $status = False;
                }
            }

            $ketstatus = statusvoucher($value['status_voucher']);
            $datalist[] = [
                'idvoucher' => $value['idvoucher'],
                'kode_voucher' => $value['kode_voucher'],
                'nama_voucher' => $value['nama_voucher'],
                'deskripsi_voucher' => $value['deskripsi_voucher'],
                'nilai_voucher' => (int)$value['nilai_voucher'],
                'minimal_transaksi' => (int)$value['minimal_transaksi'],
                'tgl_mulai' => $value['tgl_mulai'],
                'tgl_berakhir' => $value['tgl_berakhir'],
                'status_voucher' => $value['status_voucher'],
                'ket_status' => $ketstatus,
                'status_klaim' => $status
            ];
        }

        if (isset($datalist[0])) {
            $response->data = $datalist;
            $response->sukses(200);
        } else {
            $response->data = [];
            $response->sukses(200);
        }
        die;
        break;
    case "barang":
        if (!empty($q)) {
            $search = " AND nama_voucher LIKE '%$q%' AND tipe_voucher != '2'";
        } else {
            $search = "";
        }
        $data = $conn->query("SELECT * FROM voucher WHERE status_voucher = '3' AND tipe_voucher != '3' $search AND tgl_mulai <= NOW() AND tgl_berakhir >= NOW() AND tipe_voucher != '2'");
        foreach ($data as $key => $value) {

            $cekpunya = $conn->query("SELECT * FROM voucher_user vu 
            JOIN voucher v ON vu.idvoucher = v.idvoucher 
            WHERE vu.iduser = '$iduser' AND vu.idvoucher = '$value[idvoucher]'")->fetch_object();
            if ($cekpunya->status_berulang == 'Y') {
                if ($cekpunya->status_pakai == '1') {
                    $status = False;
                } else {
                    $status = True;
                }
            } else {
                if ($cekpunya->iduser_voucher) {
                    $status = True;
                } else {
                    $status = False;
                }
            }

            $ketstatus = statusvoucher($value['status_voucher']);

            $datalist[] = [
                'idvoucher' => $value['idvoucher'],
                'kode_voucher' => $value['kode_voucher'],
                'nama_voucher' => $value['nama_voucher'],
                'deskripsi_voucher' => $value['deskripsi_voucher'],
                'nilai_voucher' => (int)$value['nilai_voucher'],
                'minimal_transaksi' => (int)$value['minimal_transaksi'],
                'tgl_mulai' => $value['tgl_mulai'],
                'tgl_berakhir' => $value['tgl_berakhir'],
                'status_voucher' => $value['status_voucher'],
                'ket_status' => $ketstatus,
                'status_klaim' => $status
            ];
        }

        if (isset($datalist[0])) {
            $response->data = $datalist;
            $response->sukses(200);
        } else {
            $response->data = [];
            $response->sukses(200);
        }
        die;
        break;
}

function statusvoucher($val = null)
{
    switch ($val) {
        case '1':
            $ketstatus = 'Diskon Ebook';
            break;
        case '2':
            $ketstatus = 'Gratis Ongkir';
            break;
        case '3':
            $ketstatus = 'Diskon';
            break;
        default:
            $ketstatus = 'Unknown';
            break;
    }
    return $ketstatus;
}

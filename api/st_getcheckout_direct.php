<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$dataraw = json_decode(file_get_contents('php://input'));
$dataraw2 = json_decode(file_get_contents('php://input'), true);

//? LIST PRODUK
$dataproduk = $dataraw2["produk"][0];

if (empty($dataproduk['id_variant'])) {
    $que = "SELECT 
            b.id_master, 
            b.judul_master, 
            b.image_master, 
            b.harga_master, 
            b.diskon_rupiah, 
            d.berat as berat_buku, 
            e.berat as berat_fisik, 
            b.status_master_detail, 
            f.id_supplier,
            count(b.id_master) as jumlah_produk
            FROM 
            master_item b 
            LEFT JOIN master_buku_detail d ON b.id_master = d.id_master 
            LEFT JOIN master_fisik_detail e ON b.id_master = e.id_master 
            LEFT JOIN supplier f ON b.id_supplier = f.id_supplier 
            WHERE b.id_master = '$dataproduk[id_produk]'";
} else {
    $que = "SELECT 
            b.id_master, 
            b.judul_master, 
            b.image_master, 
            c.keterangan_varian, 
            c.harga_varian, 
            b.harga_master, 
            b.diskon_rupiah, 
            c.diskon_rupiah_varian, 
            d.berat as berat_buku, 
            e.berat as berat_fisik, 
            b.status_master_detail, 
            f.id_supplier,
            c.id_variant,
            count(b.id_master) as jumlah_produk
            FROM 
            master_item b 
            LEFT JOIN variant c ON b.id_master = c.id_master 
            LEFT JOIN master_buku_detail d ON b.id_master = d.id_master 
            LEFT JOIN master_fisik_detail e ON b.id_master = e.id_master 
            LEFT JOIN supplier f ON b.id_supplier = f.id_supplier 
            WHERE b.id_master = '$dataproduk[id_produk]' AND c.id_variant = '$dataproduk[id_variant]'";
}
$getproduk = $conn->query($que)->fetch_object();

if ($getproduk->status_master_detail == '2') {
    $berat += $getproduk->berat_buku * $getproduk->qty;
    $berat_detail = $getproduk->berat_buku * $getproduk->qty;
} else if ($getproduk->status_master_detail == '3') {
    $berat += $getproduk->berat_fisik * $getproduk->qty;
    $berat_detail = $getproduk->berat_fisik * $getproduk->qty;
}

//? cek apakah barang ini masuk flashsale atau tidak
$dataproduct = $conn->query("SELECT *, (stok_flashdisk-stok_terjual_flashdisk) as sisa_stok FROM flashsale a 
                JOIN flashsale_detail b ON a.id_flashsale = b.kd_flashsale
                JOIN master_item c ON b.kd_barang = c.id_master
                WHERE status_tampil_waktu = 'Y' AND status_remove_flashsale = 'N' AND a.waktu_mulai <= NOW() AND a.waktu_selesai >= NOW() AND b.kd_barang = '$dataproduk[id_produk]'")->fetch_object();

if ($getproduk->id_variant) {
    if (isset($dataproduct->id_flashsale)) {
        (float)$harga_disc = $dataproduct->harga_master - ($dataproduct->harga_master * ($dataproduct->diskon / 100));
        $diskon_format = rupiah($harga_disc);
        $harga_master = rupiah($u->harga_master);
    } else {
        $diskon = ($getproduk->harga_varian) - ($getproduk->diskon_rupiah_varian);
        $diskon_format = rupiah($diskon);
        $harga_varian = rupiah($getproduk->harga_varian);
    }

    if ($getproduk->status_master_detail == '2') {
        if (substr($getproduk->image_master, 0, 4) == 'http') {
            $imagegambar = $getproduk->image_master;
        } else {
            $imagegambar = $getimagebukufisik . $getproduk->image_master;
        }
    } else {
        if (substr($getproduk->image_master, 0, 4) == 'http') {
            $imagegambar = $getproduk->image_master;
        } else {
            $imagegambar = $getimagefisik . $getproduk->image_master;
        }
    }

    $getprodukcoba[] = [
        'id_cart' => "",
        'id_master' => $getproduk->id_master,
        'judul_master' => $getproduk->judul_master,
        'image_master' => $imagegambar,
        'id_variant' => $dataproduk['id_variant'],
        'keterangan_varian' => $getproduk->keterangan_varian != null ? $getproduk->keterangan_varian : "",
        'qty' => $dataraw->qty,
        'harga_produk' => rupiah($getproduk->harga_varian),
        'harga_tampil' => $getproduk->diskon_rupiah_varian != 0 ? ($diskon_format) : $harga_varian
    ];
} else {
    if (isset($dataproduct->id_flashsale)) {
        (float)$harga_disc = $dataproduct->harga_master - ($dataproduct->harga_master * ($dataproduct->diskon / 100));
        $diskon_format = rupiah($harga_disc);
        $harga_master = rupiah($u->harga_master);
    } else {
        $diskon = ($getproduk->harga_master) - ($getproduk->diskon_rupiah);
        $diskon_format = rupiah($diskon);
        $harga_master = rupiah($getproduk->harga_master);
    }

    if ($getproduk->status_master_detail == '2') {
        if (substr($getproduk->image_master, 0, 4) == 'http') {
            $imagegambar = $getproduk->image_master;
        } else {
            $imagegambar = $getimagebukufisik . $getproduk->image_master;
        }
    } else {
        if (substr($getproduk->image_master, 0, 4) == 'http') {
            $imagegambar = $getproduk->image_master;
        } else {
            $imagegambar = $getimagefisik . $getproduk->image_master;
        }
    }

    $getprodukcoba[] = [
        'id_cart' => "",
        'id_master' => $getproduk->id_master,
        'judul_master' => $getproduk->judul_master,
        'image_master' => $imagegambar,
        'id_variant' => "",
        'keterangan_varian' => "",
        'qty' => $dataraw->qty,
        'harga_produk' => $getproduk->harga_master,
        'harga_tampil' => $getproduk->diskon_rupiah != 0 ? ($diskon_format) : $harga_master
    ];
}

//? ADDRESS
$query_alamat = "SELECT * FROM user_alamat WHERE status_alamat_utama = 'Y' AND id_user = '$dataraw2[id_user]'";
$getalamat = $conn->query($query_alamat);
$data_alamat = $getalamat->fetch_object();
$gabung_alamat = $data_alamat->nama_penerima . " | " . $data_alamat->telepon_penerima . " " . $data_alamat->alamat
    . "," . $data_alamat->kelurahan . "," . $data_alamat->kecamatan . "," . $data_alamat->kota . "," . $data_alamat->provinsi . "," . $data_alamat->kodepos;
$address =
    [
        'id_address' => $data_alamat->id,
        'address' => $gabung_alamat,
    ];

//? ADDRESS SHIPPER
$query_alamat_shipper = "SELECT * FROM cabang WHERE id_cabang = '$dataraw2[idcabang]'";
$getalamat_shipper = $conn->query($query_alamat_shipper);
$data_alamat_shipper = $getalamat_shipper->fetch_object();
$gabung_alamat_shipper = $data_alamat_shipper->nama_cabang . " | " . $data_alamat_shipper->telepon_cabang . " " . $data_alamat_shipper->alamat_lengkap_cabang
    . "," . $data_alamat_shipper->kelurahan_cabang . "," . $data_alamat_shipper->kecamatan_cabang . "," . $data_alamat_shipper->kota_cabang . "," . $data_alamat_shipper->provinsi_cabang . "," . $data_alamat_shipper->kodepos_cabang;
$address_shipper =
    [
        'id_address' => $data_alamat_shipper->id_cabang,
        'address' => $gabung_alamat_shipper,
    ];

$platform = $conn->query("SELECT biaya_penanganan FROM profile")->fetch_object();
$getdatatotal =
    [
        'subtotal_produk' => $dataraw2['total'] * $dataraw2['qty'],
        'subtotal_pengiriman' => "0",
        'subtotal_diskon' => "0",
        'subtotal_diskon_pengiriman' => "0",
        'biaya_platform' => $platform->biaya_penanganan != 0 ? (string)$platform->biaya_penanganan : "0",
        'subtotal' => (string) (($dataraw2['total'] * $dataraw2['qty']) + $dataongkir['harga'] + $platform->biaya_penanganan),
    ];

$getqtyproduk =
    [
        'count_order' => $getproduk->jumlah_produk,
        'weight' => $berat,
    ];

//? ONGKIR
// $dataongkir = [
//     'layanan' => $dataongkir['layanan'],
//     'estimasi' => "Barang akan sampai dalam " . $dataongkir['estimasi'] . " hari",
//     'harga' => "Rp" . number_format($dataongkir['harga'], 0, ',', '.'),
// ];

//? VOUCHER
$voucherquery = "SELECT * FROM voucher WHERE idvoucher = '$dataraw2[id_voucher_barang]'";
$voucher = $conn->query($voucherquery)->fetch_assoc();

$ketstatus = statusvoucher($voucher['status_voucher']);
$getvoucher = [
    'idvoucher' => $voucher['idvoucher'],
    'kode_voucher' => $voucher['kode_voucher'],
    'nama_voucher' => $voucher['nama_voucher'],
    'deskripsi_voucher' => $voucher['deskripsi_voucher'],
    'nilai_voucher' => (int)$voucher['nilai_voucher'],
    'minimal_transaksi' => (int)$voucher['minimal_transaksi'],
    'tgl_mulai' => $voucher['tgl_mulai'],
    'tgl_berakhir' => $voucher['tgl_berakhir'],
    'status_voucher' => $voucher['status_voucher'],
    'ket_status' => $ketstatus,
    'status_klaim' => false
];

$data1['data_address_buyer'] = $address;
$data1['data_address_shipper'] = $address_shipper;
$data1['data_product'] = $getprodukcoba;
$data1['data_voucher'] = $getvoucher;
$data1['data_qty_product'] = $getqtyproduk;
$data1['data_price'] = $getdatatotal;

$response->data = $data1;
$response->sukses(200);

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

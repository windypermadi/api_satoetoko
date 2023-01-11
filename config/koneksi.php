<?php
// local
// static $password_db = '112233';
// static $IP = 'localhost';
// static $user_db = 'root';
// server
static $password_db = 'a123123123b@';
static $IP = 'localhost';
static $user_db = 'satoetoko_user';

define('HOST', $IP);
define('USER', $user_db);
define('PASS', $password_db);
// define('DB', 'satoetoko_deve_server');
define('DB', 'satoetoko_deve');
$conn = mysqli_connect(HOST, USER, PASS, DB) or die('Unable to Connect');

//database object adi	
$hostsi = $IP;
$usersi = $user_db;
$passsi = $password_db;
$dbsi   = "satoetoko_deve";
$db = new mysqli($hostsi, $usersi, $passsi, $dbsi);
if ($db->connect_errno) {
	printf("Connect DB failed: %s\n", $db->connect_error);
	exit;
	die;
}

//MIDTRANS SANDBOX
define('MTRANS_MERCHANT_ID', 'G072856707'); // SANDBOX
define('MTRANS_CLIENT_KEY', 'SB-Mid-client-O6Mh_Sby8GuFZlyU'); // SANDBOX
define('MTRANS_SERVER_KEY', 'SB-Mid-server-flH6WJ9GxCevDxtbjIacbWyy'); // SANDBOX
define('MTRANS_URL', 'https://app.sandbox.midtrans.com/snap/v1/transactions'); //SANDBOX

//MIDTRANS PRODUCTIONS
// define('MTRANS_MERCHANT_ID', 'G072856707'); // PROD
// define('MTRANS_CLIENT_KEY', 'Mid-client-bOCKckbWYz52gALt'); // PROD
// define('MTRANS_SERVER_KEY', 'Mid-server-smzxD9Be40AkecTlon1SfWJT'); // PROD
// define('MTRANS_URL', 'https://app.midtrans.com/snap/v1/transactions'); //PROD

define('GETWA', '628112845174'); // NOMOR WHATSAPP ADMIN

// $urlmain = "http://103.137.254.78/andibook";
// $urlimg = "http://103.137.254.78/andibook/image/";

$getkategoriicon = "https://devsertidemi.andipublisher.com/image/kategori/";
//satoetoko local
// $urlbanner = "localhost/assets/images/banner/";
// $geticonkategori = "localhost/assets/images/icon_kategori/";
// $getimageproduk = "localhost/assets/images/products/ebook/";
// $geticonpayment = "http://dev.satoetoko.com/assets/images/products/ebook/";

//satoetoko server production
// $urlpdf = "https://satoetoko.com/dashboard/files/upload/ebook/";
$urlpdf = "http://103.137.254.78/driveuji_1TB/hdbaru_1TB/satoetoko/pdf/";
// $urlpdf = "http://103.137.254.78/andibook/pdfs/";
$urlimg = "https://satoetoko.com/assets/images/products/ebook/";
$getprofile = "https://satoetoko.com/assets/images/pict-profile/";
$urlbanner = "https://satoetoko.com/assets/images/banner/";
$geticonkategori = "https://satoetoko.com/assets/images/icon_kategori/";
$getimageproduk = "https://satoetoko.com/assets/images/products/ebook/";
$geticonpayment = "https://satoetoko.com/assets/images/icon-payment/";
$getimagefisik = "https://satoetoko.com/assets/images/products/fisik/";
$getimagebukufisik = "https://satoetoko.com/assets/images/products/buku/";
$getvideofisik = "https://satoetoko.com/assets/images/products/fisik/";

//satoetoko server testing
// $urlimg = "https://dev.satoetoko.com/assets/images/products/ebook/";
// $urlpdf = "http://103.137.254.78/andibook/pdfs/";
// $getprofile = "https://dev.satoetoko.com/assets/images/pict-profile/";
// $urlbanner = "https://dev.satoetoko.com/assets/images/banner/";
// $geticonkategori = "https://dev.satoetoko.com/assets/images/icon_kategori/";
// $getimageproduk = "https://dev.satoetoko.com/assets/images/products/ebook/";
// $geticonpayment = "https://dev.satoetoko.com/assets/images/icon-payment/";

date_default_timezone_set("ASIA/JAKARTA");

function random_word($id = 20)
{
	$pool = '1234567890abcdefghijkmnpqrstuvwxyz';

	$word = '';
	for ($i = 0; $i < $id; $i++) {
		$word .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
	}
	return $word;
}

function rupiah($angka)
{
	$hasil = 'Rp ' . number_format($angka, 0, ",", ".");
	return $hasil;
}

function tanggal_indo($tanggal)
{
	$bulan = array(1 =>   'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
	$split = explode('-', $tanggal);
	return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}
$hari_indo = array(
	1 =>    'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'
);

function createID($search, $table, $kode)
{
	// CREATE ID
	$id_primary = $GLOBALS['conn']->query("SELECT max($search) as maxKode FROM $table");
	$id_primary = $id_primary->fetch_assoc();
	$id_primary = $id_primary['maxKode'];

	if (substr($id_primary, 2, 8) != date('Ymd')) {
		$noUrut = 0;
	} else {
		$noUrut = (int) substr($id_primary, 10, 10);
		if ($noUrut == 9999999999) {
			$noUrut = 0;
		} else {
			$noUrut++;
		}
	}
	$id_primary = $kode . date('Ymd') . sprintf("%010s", $noUrut);
	return $id_primary;
	// END CREATE ID
}

function id_ke_struk($string)
{
	$inisial = substr($string, 0, 2);
	$tgl = substr($string, 4, 6);
	$tgl = date_format(date_create($tgl), "dmy");
	$num = round(substr($string, 10));
	$no_nota = $inisial = $inisial . "-" . $tgl . "-" . $num;
	return $no_nota;
}

function id_ke_struk_fisik($string)
{
	$inisial = substr($string, 0, 2);
	$tgl = substr($string, 4, 6);
	$tgl = date_format(date_create($tgl), "dmy");
	$num = round(substr($string, 10));
	$no_nota = $inisial = $inisial . "_" . $tgl . "_" . $num;
	return $no_nota;
}

function respon_json_status_500($pesan = "")
{
	http_response_code(500);
	$respon['status'] = "500";
	$word = 'pesan';
	$respon[$word] = $pesan;
	die(json_encode($respon));
	exit();
}

function respon_json_status_200($pesan = "")
{
	http_response_code(200);
	$respon['status'] = "200";
	$word = 'pesan';
	$respon[$word] = $pesan;
	die(json_encode($respon));
	exit();
}

function respon_json_status_400($pesan = "")
{
	http_response_code(400);
	$respon['status'] = "400";
	$word = 'pesan';
	$respon[$word] = $pesan;
	die(json_encode($respon));
	exit();
}


function generate_referal($string)
{
	$inisial = substr(MD5($string), 0, 7);
	return $inisial;
}

function generate_referal_lagi()
{
	$inisial = substr(MD5(RAND()), 0, 7);
	return $inisial;
}

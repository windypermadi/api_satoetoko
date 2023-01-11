<?php
if (file_exists("../../../../../d3f1n151.php")) {
    include("../../../../../d3f1n151.php");
}
if (file_exists("../../../../d3f1n151.php")) {
    include("../../../../d3f1n151.php");
}
if (file_exists("../../../d3f1n151.php")) {
    include("../../../d3f1n151.php");
}
if (file_exists("../../d3f1n151.php")) {
    include("../../d3f1n151.php");
}
if (file_exists("../d3f1n151.php")) {
    include("../d3f1n151.php");
}

if (file_exists("d3f1n151.php")) {
    include("d3f1n151.php");
}

//include("admin/definisi/definisi.php");

static $password_db = 'a123123123b@';
static $IP = 'localhost';
static $user_db = 'andipubl_user';

define('HOST', $IP);
define('USER', $user_db);
define('PASS', $password_db);
define('DB', 'andipubl_isher');
$conn = mysqli_connect(HOST, USER, PASS, DB) or die('Unable to Connect');

//mode tes
define('KEY_XENDIT_PUBLIC', 'eG5kX2RldmVsb3BtZW50X0dVTWI5VWh0Q0VIUXdZb3pLY2pseVh0eDcxcVg5RWVHaDFqbFp4YVhic1pwUmJoZk9VRFJoQktnbkFRTk5XUzo=');

//MIDTRANS
define('MTRANS_MERCHANT_ID', 'G884156136'); // SANDBOX
define('MTRANS_CLIENT_KEY', 'SB-Mid-client-Vpopf8qtZfYISDts'); // SANDBOX
define('MTRANS_SERVER_KEY', 'SB-Mid-server-HiKzuWxAiKKktIpvfoSu5qCz'); // SANDBOX
define('MTRANS_URL', 'https://app.sandbox.midtrans.com/snap/v1/transactions'); //SANDBOX
define('MTRANS_SNAP', 'https://app.sandbox.midtrans.com/snap/snap.js');

// define('MTRANS_MERCHANT_ID', 'G884156136'); // PROD
// define('MTRANS_CLIENT_KEY', 'Mid-client-rcekzDhxXgnaScLS'); // PROD
// define('MTRANS_SERVER_KEY', 'Mid-server-g3ziVzUgD0RE2iKLSRxwnmi3'); // PROD
// define('MTRANS_URL', 'https://app.midtrans.com/snap/v1/transactions'); //PROD
// define('MTRANS_SNAP', 'https://app.midtrans.com/snap/snap.js');

define('GETWA', '628112845216'); // NOMOR WHATSAPP ADMIN

// $urlmain = "http://103.137.254.78/andibook";
$urlimg = "http://103.137.254.78/andibook/image";
$urlpdf = "http://103.137.254.78/andibook/pdfs";
$urlpromo = "http://andipublisher.com/images/promo/";
$urliklan = "http://andipublisher.com/images/iklan/";
// $urladmin = "http://103.137.254.78/andibook/admin/";
$getprofile = "http://andipublisher.com/images/user/profile/";
$getfotobarang = "http://andipublisher.com/images/barang/";
$geticonkategori = "http://andipublisher.com/images/kategori/";
//sertifikat
$getsertifikat = "http://andipublisher.com/application_api/sertifikat/sertifikat-event.php";
$getfileevent = "http://andipublisher.com/application_api/file_event/";

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

// tambahan func
function antiSQLi($string)
{
    return mysqli_real_escape_string($GLOBALS['conn'], $string);
}

function transactions_details($order, $amount, $name, $email, $phone)
{
    return array(
        'transaction_details' => array(
            'order_id' => $order,
            'gross_amount' => $amount
        ),
        'customer_details' => array(
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone
        )
    );
}

function set_notif($msg, $status)
{
    $_SESSION['notif']['pesan'] = $msg;
    $_SESSION['notif']['status'] = $status;
}

function notif()
{
    if (isset($_SESSION['notif'])) {
        echo '
            <div class="alert alert-' . $_SESSION['notif']['status'] . ' alert-dismissable show" role="alert">
                ' . $_SESSION['notif']['pesan'] . '
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
            </div>
        ';

        unset($_SESSION['notif']);
    }
}

function midtrans_status($invoice)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.sandbox.midtrans.com/v2/" . $invoice . "/status",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic ' . base64_encode(MTRANS_SERVER_KEY),
            'Content-Type: application/json'
        ),
    ));

    $response_curl = curl_exec($curl);

    curl_close($curl);

    $response = json_decode($response_curl, true);

    return $response;
}

function signatureKey_midtrans($order_id, $status, $amount, $server_key)
{
    $appStr = $order_id . $status . $amount . $server_key;

    return hash('sha512', $appStr);
}

function idr($nominal)
{
    return "<span>Rp.</span> " . number_format($nominal) . ",-";
}

function enkripsiDekripsi($string, $action)
{
    // you may change these values to your own
    $secret_key = '15saf fsFed5&sda6v Pkfasbdu asUK@';
    $secret_iv = '1597864002563154';

    $output = false;
    $encrypt_method = 'AES-256-CBC';
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ($action == 'enkripsi') {
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
    } else if ($action == 'dekripsi') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

function check_transaksi($id_transaksi, $invoice)
{
    $res = $GLOBALS['conn']->query("SELECT * FROM ba_transaksi_ebook WHERE id_transaksi = '$id_transaksi' AND invoice = '$invoice'")->num_rows;

    return $res;
}

function midtrans_payment($params)
{
    $mtrans_json = json_encode($params);
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => MTRANS_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $mtrans_json,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic ' . base64_encode(MTRANS_SERVER_KEY),
            'Content-Type: application/json'
        ),
    ));

    $response_curl = curl_exec($curl);

    curl_close($curl);

    $response = json_decode($response_curl, true);

    return $response;
}

function total_idr($nom)
{
    return "Rp " . number_format($nom, 0, ',', '.');
}

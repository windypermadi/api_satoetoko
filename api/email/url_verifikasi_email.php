<?
require_once('../koneksi.php');

if(isset($_GET['action']) == "cek_token"){
    $token = $_GET['token'];

    $query = mysqli_query($conn, "UPDATE loginuser_bahana 
    SET status_aktif_user = 'Y' WHERE email_user = '$token'");

    if ($query){
        echo "Berhasil verifikasi E-mail kamu. Silahkan login di aplikasi Bahana Digital";
        die();
    }else{
        echo "Gagal Verifikasi E-mail.";
        die();
    }
} 
?>
<?php 
require_once('koneksi.php');

$email_user = $_GET['email_user'];
$referal_hash = password_hash('bahana', PASSWORD_DEFAULT);

$cekemail = mysqli_query($conn, "SELECT * FROM loginuser_bahana WHERE email_user = '$email_user' AND status_aktif_user = 'Y' AND status_delete_user = 'N'")->num_rows;
if ($cekemail > 0){
    $respon['pesan'] = "Berhasil reset password.";
    $query = mysqli_query($conn, "UPDATE loginuser_bahana SET password_user = '$referal_hash' WHERE email_user = '$email_user'");
    die(json_encode($respon));
} else {
    http_response_code(400);
    $respon['pesan'] = "Tidak ada email yang terdaftar di aplikasi bahana digital!\nKlik `Mengerti` untuk menutup pesan ini";
    echo json_encode($respon);
}

mysqli_close($conn);

?>
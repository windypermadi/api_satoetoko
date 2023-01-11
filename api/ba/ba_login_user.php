<?php 
require_once('koneksi.php');

$email_login            = $_GET['email_login'];
$pass_login             = $_GET['pass_login'];

$cek_email = mysqli_query($conn, "SELECT * FROM loginuser_bahana WHERE (email_user = '$email_login' OR telepon_user = '$email_login') AND status_aktif_user  = 'Y' AND status_delete_user  = 'N'")->num_rows;

if ($cek_email == 0) {
    http_response_code(400);
    $respon['pesan'] = "Email ini tidak terdaftar, harap untuk melakukan registrasi terlebih dahulu!\nKlik `Mengerti` untuk menutup pesan ini";
    die(json_encode($respon)); 
} else {
    $sql   = "SELECT * FROM loginuser_bahana WHERE email_user = '$email_login' AND status_aktif_user  = 'Y' AND status_delete_user  = 'N'";
    $result = $conn->query($sql);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $id_user               = $row['id_user'];
    $nama_user             = $row['nama_user'];  
    $email_user            = $row['email_user'];
    $telepon_user          = $row['telepon_user'];
    $foto_user             = $getprofile.$row['foto_user'];
    if(password_verify($pass_login,$row['password_user'])){
        echo json_encode(array(
         'id_user'            => $id_user,
         'nama_user'          => $nama_user,
         'email_user'         => $email_user,
         'telepon_user'       => $telepon_user,
         'foto_user'          => $foto_user,
     ));
    }else{
       http_response_code(400);
       $respon['pesan'] = "Password anda salah silahkan coba lagi!\nKlik `Mengerti` untuk menutup pesan ini";
       die(json_encode($respon)); 
   }

}

mysqli_close($conn);
?>
<?php
require_once('../config/koneksi.php');
include "response.php";
require 'PHPMailerold/PHPMailerAutoload.php';
$response = new Response();

$email_login            = $_POST['email_login'];
$no_hp                  = $_POST['no_hp'];
$nama                   = $_POST['nama'];
$pass_login             = password_hash($_POST['pass_login'], PASSWORD_DEFAULT);
$url_cek_token          = 'http://satoetoko.com/satoetoko_api/api/email/url_verifikasi_email.php?action=cek_token&token=' . $email_login;

// if (!isset($_POST['referal'])){
//   $fkreferal = '';
// } else {
//   $fkreferal = $_POST['referal'];
//   $cekreferal = mysqli_query($conn, "SELECT ba_referal FROM loginuser_bahana WHERE ba_referal = '$fkreferal' AND status_aktif_user  = 'Y' AND status_delete_user  = 'N'")->num_rows;
//   if ($cekreferal == 0){
//     http_response_code(400);
//     $respon['pesan'] = "Tidak ada nomor referal!\nKlik `Mengerti` untuk menutup pesan ini";
//     die(json_encode($respon)); 
//   }
// }

$cek_email = mysqli_query($conn, "SELECT email FROM data_user WHERE email = '$email_login' AND status_aktif  = 'Y' AND status_remove  = 'N'")->num_rows;
$cek_hp = mysqli_query($conn, "SELECT notelp FROM data_user WHERE notelp = '$no_hp' AND status_aktif  = 'Y' AND status_remove  = 'N'")->num_rows;

if ($cek_email > 0) {
    $response->code = 400;
    $response->message = 'Email sudah terpakai mohon untuk menggunakan email yang berbeda!\nKlik `Mengerti` untuk menutup pesan ini';
    $response->data = '';
    $response->json();
    die();
} else {
    if ($cek_hp > 0) {
        $response->code = 400;
        $response->message = 'Nomor HP sudah terpakai mohon untuk menggunakan nomor HP yang berbeda!\nKlik `Mengerti` untuk menutup pesan ini';
        $response->data = '';
        $response->json();
        die();
    } else {
        //   $iduser = createID('id_user', 'loginuser_bahana', 'US');
        //   $referal = generate_referal_lagi();
        $query = mysqli_query($conn, "INSERT INTO `data_user`(`id_login`, `nama_user`, `email`, `notelp`, `password`, `status_provider`)
        VALUES (UUID_SHORT(), '$nama', '$email_login', '$no_hp', '$pass_login', '1')");

        if ($query) {
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->Host = 'mail.satoetoko.com';
            $mail->Port = 465;
            $mail->SMTPSecure = 'ssl';
            $mail->SMTPAuth = true;
            $mail->Username = 'akun@satoetoko.com';
            $mail->Password = 'a123123123b@';
            $mail->setFrom('akun@satoetoko.com', 'Satoetoko');
            $mail->addAddress($email_user, $email_user);
            $mail->isHTML(true);
            $mail->Subject = 'Verifikasi Email';
            $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
        <title>Verifikasi Email</title>
        <!-- FONT -->
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap" rel="stylesheet">
        </head>
        <body style="background:#f3f2ef;font-style: oblique;">
        <div class="email-check" style="max-width:500px; margin:50px auto; padding:20px; background:#fff;border-radius:3px; -webkit-box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.75);-moz-box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.75); box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.3);">
        <div class="email-container">
        <center><h3>VERIFIKASI EMAIL</h3></center>
        <hr><br>
        Hallo, ' . $nama . '.
        <br><br>
        <div align="justify">
        Selamat! Anda telah melakukan registrasi keanggotaan <b style="font-family: sans-serif; color:#21325E;"> SATOETOKO</b>.<br>
        Terimakasih telah mendaftar sebagai anggota <b style="font-family: sans-serif; color:#21325E;"> SATOETOKO</b>. Nikmati
        layanan terbaik kami untuk lebih menikmati pembelian di aplikasi.<br>
        Klik link di bawah untuk mengaktifkan akun anda:
        <br><br>
        </div>
        <center>
        <a href="' . $url_cek_token . '" class="text-white btn-success" style="padding: 8px 18px; background-color: #FC4F4F; border: none;border-radius: 5px; font-weight: bold; color: white;" target="__blank" >Verifikasi E-mail
        </a><br>
        </center>
        <br>
        <center>
        klik link di bawah ini jika tombol verifikasi diatas tidak berfungsi.<br><br>
        <a href="' . $url_cek_token . '">http://satoetoko.com/satoetoko_api/api/email/url_verifikasi_email.php?action=cek_token&token=' . $email_login . '</a></center><br><br>
        Untuk informasi lebih lanjut mengenai layanan kami:<br>
        <table style="margin-left: 25px;">
        <tr>
        <td style="padding-right: 15px" nowrap=""> 
        Call Center
        </td>
        <td>
        : 0811-2845-174
        </td>
        </tr>
        <tr>
        <td> 
        Website
        </td>
        <td>
        : www.satoetoko.com
        </td>
        </tr>
        <tr>
        <td style="padding-right: 15px"> 
        Addres
        </td>
        <td>
        : Jl. Beo No.38-40, Mrican, Caturtunggal, Kec. Depok, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55281
        </td>
        </tr>
        </table><br>
        <div style="text-align: left">
        Hormat Kami,<br><br><br>
        <b style="font-family: sans-serif; color:#21325E;"> SATOETOKO</b>.<br>
        </div>
        </div>
        </div>
        </body>
        </html>';

            if (!$mail->send()) {
                $response->code = 400;
                $response->message = 'Email tidak terkirim.';
                $response->data = '';
                $response->json();
                die();
            } else {
                $response->code = 200;
                $response->message = 'Yey registrasi kamu berhasil.\n\nSilahkan untuk verifikasi akun anda terlebih dahulu. Cek di KONTAK MASUK EMAIL atau di SPAM EMAIL.';
                $response->data = '';
                $response->json();
                die();
            }


            die(json_encode($respon));
        } else {
            $response->code = 400;
            $response->message = 'Gagal menambahkan Anggota baru!\nKlik `Mengerti` untuk menutup pesan ini';
            $response->data = '';
            $response->json();
            die();
        }
    }
}
mysqli_close($conn);

<?php
require_once('koneksi.php');
require 'PHPMailerold/PHPMailerAutoload.php';

$email_login            = $_POST['email_login'];
$url_cek_token          = 'https://andipublisher.com/application_api/email/url_verifikasi_email.php?action=cek_token&token='.$email_login;

$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPOptions = array(
  'ssl' => array(
    'verify_peer' => false,
    'verify_peer_name' => false,
    'allow_self_signed' => true
)
);
$mail->Host = 'mail.bahanadigital.com'; 
$mail->Port = 587; 
$mail->SMTPSecure = 'tsl'; 
$mail->SMTPAuth = true;
$mail->Username = 'info@bahanadigital.com'; 
$mail->Password = 'A123123123b@'; 
$mail->setFrom('info@bahanadigital.com', 'Bahana Digital');
$mail->addAddress($email_login, $nama);
$mail->isHTML(true);
$mail->Subject = 'Verifikasi E-mail';
$mail->Body = '
<!DOCTYPE html>
<html>
<head>
<title>Verifikasi E-mail</title>
<!-- FONT -->
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap" rel="stylesheet">
</head>
<body style="background:#f3f2ef;font-style: oblique;">
<div class="email-check" style="max-width:500px; margin:50px auto; padding:20px; background:#fff;border-radius:3px; -webkit-box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.75);-moz-box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.75); box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.3);">
<div class="email-container">
<center><h3>VERIFIKASI EMAIL</h3></center>
<hr><br>
Hallo, '.$email_login.'.
<br><br>
<div align="justify">
Selamat! Anda telah melakukan registrasi keanggotaan <b style="font-family: sans-serif; color:#21325E;"> BAHANA DIGITAL</b>.<br>
Terimakasih telah mendaftar sebagai anggota <b style="font-family: sans-serif; color:#21325E;"> BAHANA DIGITAL</b>. Nikmati
layanan terbaik kami untuk lebih menikmati membaca buku digital di aplikasi.<br>
Klik link di bawah untuk mengaktifkan akun anda:
<br><br>
</div>
<center>
<a href="'.$url_cek_token.'" class="text-white btn-success" style="padding: 8px 18px; background-color: #FC4F4F; border: none;border-radius: 5px; font-weight: bold; color: white;" target="__blank" >Verifikasi E-mail
</a><br>
</center>
<br>
Untuk informasi lebih lanjut mengenai layanan kami:<br>
<table style="margin-left: 25px;">
<tr>
<td style="padding-right: 15px" nowrap=""> 
Call Center
</td>
<td>
: +62811-2848-798
</td>
</tr>
<tr>
<td> 
Website
</td>
<td>
: www.bahanadigital.com
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
<b style="font-family: sans-serif; color:#21325E;"> BAHANA DIGITAL</b>.<br>
</div>
</div>
</div>
</body>
</html>';

if(!$mail->send()) {
  http_response_code(400);
  $respon['pesan'] = "Email tidak terkirim";
  die(json_encode($respon)); 
} else {
    $respon['pesan'] = "Silahkan untuk cek ulang email anda untuk memverifikasi email anda.\n\nCek di KONTAK MASUK EMAIL atau di SPAM EMAIL.";
    die(json_encode($respon)); 
}

mysqli_close($conn);
?>
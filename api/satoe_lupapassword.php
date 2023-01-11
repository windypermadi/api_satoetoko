<?php
require_once('../config/koneksi.php');
include "response.php";
require 'PHPMailerold/PHPMailerAutoload.php';
$response = new Response();

$email_user             = $_POST['email_user'];
$referal = generate_referal_lagi();
$referal_hash = password_hash($referal, PASSWORD_DEFAULT);

$cekdata = mysqli_query($conn, "SELECT * FROM data_user WHERE email = '$email_user' AND status_aktif = 'Y' AND status_remove = 'N';")->fetch_assoc();
$cek = mysqli_query($conn, "SELECT * FROM data_user WHERE email = '$email_user' AND status_aktif = 'Y' AND status_remove = 'N';")->num_rows;
if ($cek > 0) {
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
    $mail->Subject = 'Perubahan Password';

    $mail->Body = '
	<!DOCTYPE html>
	<html>
	<head>
	<title>Perubahan Password Aplikasi Satoetoko</title>
	<!-- FONT -->
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap" rel="stylesheet">
	</head>
	<body style="background:#f3f2ef;font-style: oblique;">
	<div class="email-check" style="max-width:500px; margin:50px auto; padding:20px; background:#fff;border-radius:3px; -webkit-box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.75);-moz-box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.75); box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.3);">
	<div class="email-container">
	<center><h3>Perubahan Password</h3></center>
	<hr><br>
	Hallo, ' . $cekdata['nama_user'] . '.
	<br><br>
	<div align="justify">
	Kamu meminta untuk melakukan perubahan password pada aplikasi <b style="font-family: sans-serif; color:#21325E;"> SATOETOKO</b>.<br>
	Silahkan memasukkan password dibawah ini untuk login aplikasi.<br><br>
	<center><b style="font-family: sans-serif; color:red;">Password : ' . $referal . '</b></center><br>
	Nikmati layanan terbaik kami untuk lebih menikmati di aplikasi.<br>
	<br>
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
        sendError("Email tidak terkirim");
    } else {
        $query = mysqli_query($conn, "UPDATE data_user SET password = '$referal_hash' WHERE email = '$email_user'");
        sendSuccess('Silahkan cek email anda. Kode password baru dikirim ke email anda jika di dalam kontak email tidak ada silahkan mengecek di spam email. Terimakasih');
    }
} else {
    sendError('Email yang kamu masukkan tidak terdaftar diaplikasi satoetoko');
}

mysqli_close($conn);

function sendError($msg)
{
    $response = new Response();
    $response->code = 400;
    $response->message = $msg;
    $response->data = '';
    $response->json();
    die();
}

function sendSuccess($msg)
{
    $response = new Response();
    $response->code = 200;
    $response->message = $msg;
    $response->data = '';
    $response->json();
    die();
}

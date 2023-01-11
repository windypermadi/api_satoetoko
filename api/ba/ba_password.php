<?php 
require_once('koneksi.php');
require 'PHPMailerold/PHPMailerAutoload.php';

$email_user = $_POST['email_user'];
$referal = generate_referal_lagi();
$referal_hash = password_hash($referal, PASSWORD_DEFAULT);

$cekemail = mysqli_query($conn, "SELECT * FROM loginuser_bahana WHERE email_user = '$email_user' AND status_aktif_user = 'Y' AND status_delete_user = 'N'")->num_rows;
if ($cekemail > 0){
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
	$mail->addAddress($email_user, $email_user);
	$mail->isHTML(true);
	$mail->Subject = 'Perubahan Password';

	$mail->Body = '
	<!DOCTYPE html>
	<html>
	<head>
	<title>Perubahan Password Bahana Digital</title>
	<!-- FONT -->
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap" rel="stylesheet">
	</head>
	<body style="background:#f3f2ef;font-style: oblique;">
	<div class="email-check" style="max-width:500px; margin:50px auto; padding:20px; background:#fff;border-radius:3px; -webkit-box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.75);-moz-box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.75); box-shadow: 0px 2px 17px 0px rgba(0,0,0,0.3);">
	<div class="email-container">
	<center><h3>Perubahan Password</h3></center>
	<hr><br>
	Hallo, '.$email_user.'.
	<br><br>
	<div align="justify">
	Anda meminta untuk melakukan perubahan password pada aplikasi <b style="font-family: sans-serif; color:#21325E;"> BAHANA DIGITAL</b>.<br>
	Silahkan memasukkan password dibawah ini untuk login aplikasi.<br><br>
	<center><b style="font-family: sans-serif; color:red;">Password : '.$referal.'</b></center><br>
	Nikmati layanan terbaik kami untuk lebih menikmati membaca buku digital di aplikasi.<br>
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
		$query = mysqli_query($conn, "UPDATE loginuser_bahana SET password_user = '$referal_hash' WHERE email_user = '$email_user'");
		$respon['pesan'] = "Silahkan cek email anda. Kode password baru dikirim ke email anda jika di dalam kontak email tidak ada silahkan mengecek di spam email. Terimakasih";
		die(json_encode($respon)); 
	}

} else {
	http_response_code(400);
	$respon['pesan'] = "Tidak ada email yang terdaftar di aplikasi bahana digital!\nKlik `Mengerti` untuk menutup pesan ini";
	echo json_encode($respon);
}

mysqli_close($conn);

?>
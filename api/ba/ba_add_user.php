<?php 
require_once('koneksi.php');
require 'PHPMailerold/PHPMailerAutoload.php';

$email_login            = $_POST['email_login'];
$no_hp                  = $_POST['no_hp'];
$nama                   = $_POST['nama'];
$pass_login             = password_hash($_POST['pass_login'], PASSWORD_DEFAULT);
$url_cek_token          = 'http://andipublisher.com/application_api/email/url_verifikasi_email.php?action=cek_token&token='.$email_login;

if (!isset($_POST['referal'])){
  $fkreferal = '';
} else {
  $fkreferal = $_POST['referal'];
  $cekreferal = mysqli_query($conn, "SELECT ba_referal FROM loginuser_bahana WHERE ba_referal = '$fkreferal' AND status_aktif_user  = 'Y' AND status_delete_user  = 'N'")->num_rows;
  if ($cekreferal == 0){
    http_response_code(400);
    $respon['pesan'] = "Tidak ada nomor referal!\nKlik `Mengerti` untuk menutup pesan ini";
    die(json_encode($respon)); 
  }
}

if (!empty($_FILES['uploadedfile'])) {
  if (isset($_FILES['uploadedfile']['type'])){

    $nama_file  = random_word(10).".png";
    $lokasi     = $_FILES['uploadedfile']['tmp_name'];

    if(move_uploaded_file($lokasi, "../images/user/profile/".$nama_file)){
      $cek_email = mysqli_query($conn, "SELECT email_user FROM loginuser_bahana WHERE email_user = '$email_login' AND status_aktif_user  = 'Y' AND status_delete_user  = 'N'")->num_rows;
      $cek_hp = mysqli_query($conn, "SELECT telepon_user FROM loginuser_bahana WHERE telepon_user = '$no_hp' AND status_aktif_user  = 'Y' AND status_delete_user  = 'N'")->num_rows;

      if ($cek_email > 0) {
        http_response_code(400);
        $respon['pesan'] = "Email sudah terpakai mohon untuk menggunakan email yang berbeda!\nKlik `Mengerti` untuk menutup pesan ini";
        die(json_encode($respon)); 
      } else {
        if ($cek_hp > 0){
          http_response_code(400);
          $respon['pesan'] = "Nomor HP sudah terpakai mohon untuk menggunakan nomor HP yang berbeda!\nKlik `Mengerti` untuk menutup pesan ini";
          die(json_encode($respon)); 
        } else {
          $iduser = createID('id_user', 'loginuser_bahana', 'US');
          $referal = generate_referal_lagi();
          $query = mysqli_query($conn, "INSERT INTO `loginuser_bahana`(`id_user`, `nama_user`, `email_user`, `telepon_user`, `password_user`, `foto_user`, `ba_referal`, `ba_fk_referal`) 
            VALUES ('$iduser', '$nama', '$email_login', '$no_hp', '$pass_login', '$nama_file', '$referal', '$fkreferal')");

          if ($query){
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
            <center>
            klik link di bawah ini jika tombol verifikasi diatas tidak berfungsi.<br><br>
            <a href="'.$url_cek_token.'">http://dev.andipublisher.com/application_api/email/url_verifikasi_email.php?action=cek_token&token='.$email_login.'</a></center><br><br>
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
              $respon['pesan'] = "Yey registrasi kamu berhasil.\n\nSilahkan untuk verifikasi akun anda terlebih dahulu. Cek di KONTAK MASUK EMAIL atau di SPAM EMAIL.";
            }

            die(json_encode($respon));
          } else{ 
            http_response_code(400);
            unlink("../images/user/profile/".$nama_file);
            $respon['pesan'] = "Gagal menambahkan image baru!\nKlik `Mengerti` untuk menutup pesan ini";
            die(json_encode($respon)); 
          }
        }
      }

    }else{
      http_response_code(400);
      $respon['pesan'] = "Upload file mengalami kegagalan!\nKlik `Mengerti` untuk menutup pesan ini";
      die(json_encode($respon)); 
    }   
  }else{
    http_response_code(400);
    $respon['pesan'] = "Format tidak diperbolehkan!\nKlik `Mengerti` untuk menutup pesan ini";
    die(json_encode($respon));
  }
} else {
  $cek_email = mysqli_query($conn, "SELECT email_user FROM loginuser_bahana WHERE email_user = '$email_login' AND status_aktif_user  = 'Y' AND status_delete_user  = 'N'")->num_rows;
  $cek_hp = mysqli_query($conn, "SELECT telepon_user FROM loginuser_bahana WHERE telepon_user = '$no_hp' AND status_aktif_user  = 'Y' AND status_delete_user  = 'N'")->num_rows;

  if ($cek_email > 0) {
    http_response_code(400);
    $respon['pesan'] = "Email sudah terpakai mohon untuk menggunakan email yang berbeda!\nKlik `Mengerti` untuk menutup pesan ini";
    die(json_encode($respon)); 
  } else {
    if ($cek_hp > 0){
      http_response_code(400);
      $respon['pesan'] = "Nomor HP sudah terpakai mohon untuk menggunakan nomor HP yang berbeda!\nKlik `Mengerti` untuk menutup pesan ini";
      die(json_encode($respon)); 
    } else {
      $iduser = createID('id_user', 'loginuser_bahana', 'US');
      $referal = generate_referal_lagi();
      $query = mysqli_query($conn, "INSERT INTO `loginuser_bahana`(`id_user`, `nama_user`, `email_user`, `telepon_user`, `password_user`, `ba_referal`, `ba_fk_referal`)
        VALUES ('$iduser', '$nama', '$email_login', '$no_hp', '$pass_login', '$referal', '$fkreferal')");

      if ($query){
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
        <center>
        klik link di bawah ini jika tombol verifikasi diatas tidak berfungsi.<br><br>
        <a href="'.$url_cek_token.'">http://dev.andipublisher.com/application_api/email/url_verifikasi_email.php?action=cek_token&token='.$email_login.'</a></center><br><br>
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
         $respon['pesan'] = "Yey registrasi kamu berhasil.\n\nSilahkan untuk verifikasi akun anda terlebih dahulu. Cek di KONTAK MASUK EMAIL atau di SPAM EMAIL.";
       }


       die(json_encode($respon));
     } else{ 
      http_response_code(400);
      $respon['pesan'] = "Gagal menambahkan Anggota baru!\nKlik `Mengerti` untuk menutup pesan ini";
      die(json_encode($respon)); 
    }
  }
}
}
mysqli_close($conn);
?>
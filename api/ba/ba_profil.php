<?php 
require_once('koneksi.php');

$id_user                = $_POST['id_user'];
$no_hp                  = $_POST['no_hp'];
$nama                   = $_POST['nama'];

if (!empty($_FILES['uploadedfile'])) {
    // FORMAT DIIZINKAN
    // $format_diizinkan["image/jpeg"]         = "";
    // $format_diizinkan["image/jpg"]          = "";
    // $format_diizinkan["image/png"]          = "";
    // END FORMAT DIIZINKAN

    if (isset($_FILES['uploadedfile']['type'])){

        $nama_file  = random_word(10).".png";
        $lokasi     = $_FILES['uploadedfile']['tmp_name'];

        if(move_uploaded_file($lokasi, "../images/user/profile/".$nama_file)){

            if (isset($_POST['pass_login'])){
                $pass_login = password_hash($_POST['pass_login'], PASSWORD_DEFAULT);
                $query = mysqli_query($conn, "UPDATE loginuser_bahana SET nama_user = '$nama', telepon_user = '$no_hp', password_user = '$pass_login', foto_user = '$nama_file' WHERE id_user = '$id_user'");

                if ($query){
                    $respon['pesan'] = "Profil kamu berhasil diperbarui\n\nKlik `OK` untuk menutup pemberitahuan ini.";
                    die(json_encode($respon));
                } else{ 
                    http_response_code(400);
                    unlink("../images/user/profile/".$nama_file);
                    $respon['pesan'] = "Gagal edit profil!\nKlik `Mengerti` untuk menutup pesan ini";
                    die(json_encode($respon)); 
                }

            } else {
                $query = mysqli_query($conn, "UPDATE loginuser_bahana SET nama_user = '$nama', telepon_user = '$no_hp', foto_user = '$nama_file' WHERE id_user = '$id_user'");

                if ($query){
                    $respon['pesan'] = "Profil kamu berhasil diperbarui\n\nKlik `OK` untuk menutup pemberitahuan ini.";
                    die(json_encode($respon));
                } else{ 
                    http_response_code(400);
                    unlink("../images/user/profile/".$nama_file);
                    $respon['pesan'] = "Gagal edit profil!\nKlik `Mengerti` untuk menutup pesan ini";
                    die(json_encode($respon)); 
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

    if (isset($_POST['pass_login'])){
        $pass_login = password_hash($_POST['pass_login'], PASSWORD_DEFAULT);
        $query = mysqli_query($conn, "UPDATE loginuser_bahana SET nama_user = '$nama', telepon_user = '$no_hp', password_user = '$pass_login' WHERE id_user = '$id_user'");

        if ($query){
            $respon['pesan'] = "Profil kamu berhasil diperbarui\n\nKlik `OK` untuk menutup pemberitahuan ini.";
            die(json_encode($respon));
        } else{ 
            http_response_code(400);
            $respon['pesan'] = "Gagal edit profil!\nKlik `Mengerti` untuk menutup pesan ini";
            die(json_encode($respon)); 
        }

    } else {
        $query = mysqli_query($conn, "UPDATE loginuser_bahana SET nama_user = '$nama', telepon_user = '$no_hp' WHERE id_user = '$id_user'");

        if ($query){
            $respon['pesan'] = "Profil kamu berhasil diperbarui\n\nKlik `OK` untuk menutup pemberitahuan ini.";
            die(json_encode($respon));
        } else{ 
            http_response_code(400);
            $respon['pesan'] = "Gagal edit profil!\nKlik `Mengerti` untuk menutup pesan ini";
            die(json_encode($respon)); 
        }

    }
}

mysqli_close($conn);
?>
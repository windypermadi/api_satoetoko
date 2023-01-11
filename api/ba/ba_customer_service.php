<?php 
require_once('koneksi.php');

$response['nomor_telp'] = GETWA;
die(json_encode($response));

mysqli_close($conn);

?>
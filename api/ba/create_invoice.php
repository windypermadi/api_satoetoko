<?php

include 'koneksi.php';


$data['external_id'] = $_POST['invoices'];
$data['amount'] = $_POST['pembayaran'];
$data['payer_email'] = $_POST['payer_email'];
$data['description'] = $_POST['description'];
$data['success_redirect_url'] = "https://andipublisher.com/privacy_policy.html";
$data['failure_redirect_url'] = "https://andipublisher.com/privacy_policy.html";
$send_data = json_encode($data);

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.xendit.co/v2/invoices',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $send_data,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Basic '.KEY_XENDIT_PUBLIC,
    'Cookie: visid_incap_2182539=3s6jicDgQ2CpyY1fH7gW0Lg/YWAAAAAAQUIPAAAAAADXcjOJjoaJMuJZdly6vmxh; nlbi_2182539=aPm+ep5VsElY3NApjjCKbQAAAAAIsAzhGAhD/BbRcV8FiFLF; incap_ses_1118_2182539=F6i3BERPBiv+r87VW++DD4Cwd2AAAAAA97avBcFSpqQGmTkh5qF5VA=='
  ),
));

$response = curl_exec($curl);

curl_close($curl);
// echo $response;

$db->query("UPDATE ba_transaksi_ebook SET data_req_inv='$response' WHERE invoice='$data[external_id]'");


$response = json_decode($response, true);

$res['url'] = $response['invoice_url'];
$res2[] = $res;
echo json_encode($res2);
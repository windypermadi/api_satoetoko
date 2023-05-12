<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$idcategory = $_GET['id'];

$query2 = "SELECT * FROM questions_answer WHERE kdcategory = '$idcategory'";
$data2 = $conn->query($query2);

foreach ($data2 as $key => $value) {
    $faq[] = [
        'idfaq' => $value['idfaq'],
        'question_faq' => $value['question_faq'],
        'answer_faq' => $value['answer_faq']
    ];
}

$result1['category'] = $result;
$result1['faq'] = $faq;

$response->data = $faq;
$response->sukses(200);
mysqli_close($conn);

<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$query = "SELECT * FROM questions_answer_category";
$data = $conn->query($query);

foreach ($data as $key => $value) {
    $result[] = [
        'idcategory' => $value['idcategory'],
        'title' => $value['title'],
        'icon' => $value['icon']
    ];
}

$query2 = "SELECT * FROM questions_answer";
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

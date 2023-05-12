<?php
require_once('../config/koneksi.php');
include "response.php";
$response = new Response();

$q = $_GET['q'] ?? '';

if (!empty($q)) {
    $search = " WHERE question_faq LIKE '%$q%' OR answer_faq LIKE '%$q%'";
} else {
    $search = "";
}

$query2 = "SELECT * FROM questions_answer $search";
$data2 = $conn->query($query2);

foreach ($data2 as $key => $value) {
    $faq[] = [
        'idfaq' => $value['idfaq'],
        'question_faq' => $value['question_faq'],
        'answer_faq' => $value['answer_faq']
    ];
}

if (isset($faq[0])) {
    $response->data = $faq;
    $response->sukses(200);
} else {
    $response->data = [];
    $response->sukses(200);
}
mysqli_close($conn);

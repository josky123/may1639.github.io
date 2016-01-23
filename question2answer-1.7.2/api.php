<?php

require_once "./api_classes/util.php";
require_once "./api_classes/users.php";
require_once "./api_classes/questions.php";
require_once "./api_classes/answers.php";
require './qa-include/qa-base.php';

header("access-control-allow-origin: *");
header('Content-Type: application/json');

$result = qa_db_query_raw("SELECT * FROM `qa_users`");

$prime = mysqli_fetch_array($result, MYSQLI_ASSOC);
echo "\n\n\n";
var_dump($prime);

$prime = mysqli_fetch_array($result, MYSQLI_ASSOC);
echo "\n\n\n";
var_dump($prime);



echo  mysqli_data_seek($result, 0);

$prime = mysqli_fetch_array($result, MYSQLI_ASSOC);
echo "\n\n\n";
var_dump($prime);

$prime = mysqli_fetch_array($result, MYSQLI_ASSOC);
echo "\n\n\n";
var_dump($prime);



mysqli_free_result($result);
?>
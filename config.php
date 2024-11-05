<?php

$db_host = '';
$db_user = 'stepavi2';
$db_password = 'webove aplikace';
$db_name = 'stepavi2';

$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
        die("We apologize. Database is not working. 
        We will fix the problem in the near future." . 
        $conn->connect_error);
}

?>
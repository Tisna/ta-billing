<?php
require_once('../connection.php');

function getListCostomer($connect) {
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $query = "SELECT * FROM tb_customer ORDER BY nama  ASC";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    $output = '';
    foreach ($result as $row) {
        $output .= '<option value="'.$row['id'].'" >'.$row['nama'].'</option>' ;
    }
    return $output;
}
function getListAdmin($connect) {
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $query = "SELECT * FROM tb_admin WHERE jabatan != 'admin' ORDER BY username  ASC";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    $output = '';
    foreach ($result as $row) {
        $output .= '<option value="'.$row['id'].'" >'.$row['username'].'</option>' ;
    }
    return $output;
}

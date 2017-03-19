<?php
require_once "ExcelFile.php";
$conn = mysqli_connect("localhost", "root", "GO", "horshamharley_data");

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$getData = mysqli_query($conn, "select * from version_2");
// $fp = fopen('/var/www/html/database/horsam_10_aug/csv' . rand() . '.csv', 'w');
$i = 1;
$variants = [];
$index = 0;
while ($row = mysqli_fetch_assoc($getData)) {

}
$objPHPExcel = open_excel_file("products_");
$labels = array_keys($variants[0]);
add_label($objPHPExcel, $labels);
$index = 3;
foreach ($variants as $variant) {
    add_excel_row($objPHPExcel, $index, $variant);
    $index++;
}
generate_file($objPHPExcel, "products_");
close_excel_file($objPHPExcel);

function seoUrl($string)
{
    $string = trim($string);
//Lower case everything
    $string = strtolower($string);
//Make alphanumeric (removes all other characters)
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
//Clean up multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
//Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    return $string;
}
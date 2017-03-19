<?php
/**
 * Created by PhpStorm.
 * User: govin
 * Date: 9/8/16
 * Time: 8:03 AM
 */

require_once "lib/ExcelFile.php";
require_once 'lib/DBConnection.php';
require_once 'lib/CommonFunctions.php';

$conn = dbConnect('dynomotive_data');

$sql = "SELECT product_exported.*, image_media.media_id, image_media.image as new_image FROM product_exported LEFT JOIN image_media on image_media.product_id= product_exported.id";
$query = mysqli_query($conn,$sql);
$ready=[];
$index=1;
$i =1;
while($row=mysqli_fetch_assoc($query)){

    $ready[$i] = $row;
    $i++;

    //$ready[] = $formatted_array;
}
$objPHPExcel = open_excel_file('import_with_media');
$labels = array_keys($ready[1]);
//print_r($labels);
add_label($objPHPExcel, $labels);
$index = 2;
foreach ($ready as $completed) {
    add_excel_row($objPHPExcel, $index, $completed);

    $index++;
}

generate_file($objPHPExcel, "import_with_media");
close_excel_file($objPHPExcel);
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

$conn = dbConnect('dynomotive_conceptdev');

$sql = 'SELECT product_image.product_id, media.id,media.file FROM `product_image` INNER JOIN media on product_image.media_id = media.id ';
$query = mysqli_query($conn,$sql);

print_r($query);
$formatted_array=[];
while($row=mysqli_fetch_assoc($query)){
    $formatted_array['product_id']=$row['product_id'];
    $formatted_array['media_id']=$row['id'];
    $formatted_array['image']=$row['file'];

    $ready[] = $formatted_array;
}
//print_r($ready);
$objPHPExcel = open_excel_file('image');
$labels = array_keys($ready[0]);
//print_r($labels);
add_label($objPHPExcel, $labels);
$index = 2;
foreach ($ready as $completed) {
    add_excel_row($objPHPExcel, $index, $completed);

    $index++;
}

generate_file($objPHPExcel, "image");
close_excel_file($objPHPExcel);
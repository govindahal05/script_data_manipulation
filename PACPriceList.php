<?php
require_once 'lib/ExcelFile.php';
require_once 'lib/DBConnection.php';
require_once 'lib/CommonFunctions.php';

$conn = dbConnect('horshamharley_data');

$sql = "SELECT * from product_related";
$query_result = mysqli_query($conn, $sql);

//var_dump($query_result);
//foreach()
$formated_row =[];
$prices =[];
while ($row = mysqli_fetch_assoc($query_result)) {/*
    $price['product_name'] = $row['product_name'];
    $price['related_product_name'] = $row['related_product_name'];*/
    $result = explode('||',$row['related_product_name']);
    foreach($result as $res){
        $price['product_name'] = $row['product_name'];
        $price['related_product_name'] = $res;

         $prices[]=$price;

    }


   /* $formated_row['sku'] = trim(substr($row['OPEA'], 0, 18));
   $formated_row['dealer_price'] = trim(round(substr($row['OPEA'], 49, 9)/100, 2))*1.1;
    $formated_row['retail_price'] = trim(round(substr($row['OPEA'], 58, 9)/100, 2))*1.1;

    $prices[]  = $formated_row;*/



}

$objPHPExcel = open_excel_file("related_product");
$labels = array_keys($prices[0]);
add_label($objPHPExcel, $labels);
$index = 2;
foreach ($prices as $variant) {
    add_excel_row($objPHPExcel, $index, $variant);

    $index++;
}

generate_file($objPHPExcel, "related_product");
close_excel_file($objPHPExcel);


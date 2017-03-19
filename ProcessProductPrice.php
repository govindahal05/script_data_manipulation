<?php
require_once "lib/ExcelFile.php";
require_once 'lib/DBConnection.php';
require_once 'lib/CommonFunctions.php';

$conn = dbConnect('horshamharley_data');
//$categories = [];
//$second_level_categories = [];
//$third_level_categories = [];

//Processiong Product
create_products($conn);

function create_products($conn)
{
    //common settings
    $table = "pac_import_ready";
    $table2 = "extracted_price";
    $getData = mysqli_query($conn, "SELECT * FROM $table left join $table2 on $table.variant_sku=$table2.sku limit 100");


    $variants = [];
    $index = 5;
    while ($row = mysqli_fetch_assoc($getData)) {
        $row['variant_rrp'] = $row['dealer_price']*1.1;
        $row['variant_rsp_price'] = $row['retail_price']*1.1;

        $variants[$index] = $row;
        $index++;
    }

    $objPHPExcel = open_excel_file("cleaned_products");
    $labels = array_keys($variants[5]);
    add_label($objPHPExcel, $labels);
    $index = 2;
    foreach ($variants as $variant) {
        add_excel_row($objPHPExcel, $index, $variant);
        $index++;
    }

    generate_file($objPHPExcel, "cleaned_products");
    close_excel_file($objPHPExcel);
}
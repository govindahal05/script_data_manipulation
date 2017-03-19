<?php
require_once "lib/ExcelFile.php";
require_once 'lib/DBConnection.php';
require_once 'lib/CommonFunctions.php';

$conn = dbConnect('horshamharley_data');
$categories = [];
$second_level_categories = [];
$third_level_categories = [];

//Processiong Product
create_products($conn, $categories, $second_level_categories, $third_level_categories);

function create_products($conn)
{
    //common settings
    $table = "pa_3rd";
    $getData = mysqli_query($conn, "SELECT * FROM $table order by name_extradescription");

    $variants = [];
    $index = 5;
    while ($row = mysqli_fetch_assoc($getData)) {
        $row['name_extradescription'] = trim(clean_name($row['name_extradescription']));
        $name = trim(filter_var(htmlspecialchars(clean_name($row['name_extradescription'])), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
        $row['slug'] = seoUrl(html_entity_decode($name));
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
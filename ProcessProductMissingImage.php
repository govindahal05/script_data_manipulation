<?php
require_once "lib/ExcelFile.php";
require_once 'lib/DBConnection.php';
require_once 'lib/CommonFunctions.php';

$conn = dbConnect('horshamharley_data');
create_products($conn);

/**
 * @param $conn
 * @param $categories
 * @param $second_level_categories
 * @param $third_level_categories
 */
function create_products($conn)
{

    $available_images = [];

    //common settings
    $table = "pac_images";
    $images_data = mysqli_query($conn, "SELECT * FROM $table");

    $images = [];
    while ($row = mysqli_fetch_assoc($images_data)) {
        $images[] = $row['image'];
    }

    //common settings
    $table = "pa_raw_clean";
    $available_images_data = mysqli_query($conn, "SELECT * FROM $table order by slug");

    while ($row = mysqli_fetch_assoc($available_images_data)) {
        foreach (explode(",", $row['Images']) as $image) {
            if (array_search($image, $images)) {
                $available_images[] = ['image' => $image];
            }
        }
    }


    $objPHPExcel = open_excel_file("products_available_images");
    $labels = array_keys($available_images[1]);
    add_label($objPHPExcel, $labels);
    $index = 2;
    foreach ($available_images as $image) {
        add_excel_row($objPHPExcel, $index, $image);
        $index++;
    }

    generate_file($objPHPExcel, "products_available_images");
    close_excel_file($objPHPExcel);


    dbClose($conn);
}
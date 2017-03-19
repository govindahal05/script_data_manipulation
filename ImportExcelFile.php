<?php
require_once "vendor/autoload.php";
require_once 'lib/DBConnection.php';

/**
 * @param $file_name
 * @return PHPExcel
 */
function open_excel_file($file_name)
{
    $objPHPExcel = PHPExcel_IOFactory::load($file_name);
    return $objPHPExcel;
}

/**
 * @param $objPHPExcel
 * @param $file_name
 */
function close_excel_file($objPHPExcel)
{
    unset($objPHPExcel);
}

function import_data()
{
    $database = 'dynomotive_data';
    $conn = dbConnect($database);
//    $file = '/home/govin/PhpstormProjects/scripts_from_mam/data/export/products_.csv';
    $file ='/media/govin/data/Element7Digital/database/product_exported.csv';
    $table = 'product_exported';

    $sql = "LOAD DATA LOCAL INFILE '{$file}' INTO TABLE $database.{$table} FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\\n' IGNORE 1 LINES";
    echo "SQL: $sql \n";
    mysqli_query($conn, $sql);

    if (mysqli_affected_rows($conn) == 1) {
        echo "\nThe data was successfully imported!";
    } else {
        echo mysqli_error($conn);
    };

    dbClose($conn);
}
import_data();
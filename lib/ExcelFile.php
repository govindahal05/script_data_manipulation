<?php
require_once "vendor/autoload.php";

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);
date_default_timezone_set('Europe/London');

/**
 * @param $file_name
 * @return PHPExcel
 */
function open_excel_file($file_name)
{
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()
        ->setTitle($file_name);

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

/**
 * @param $objPHPExcel
 * @param $col
 * @param $data
 */
function add_excel_row($objPHPExcel, $row, $data)
{
    $col = 0;
    foreach ($data as $index => $value) {
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValueByColumnAndRow($col++, $row, $value);
    }
}

/**
 * @param $objPHPExcel
 * @param $data
 */
function add_label($objPHPExcel, $labels)
{
    $col = 0;
    foreach ($labels as $index => $value) {
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValueByColumnAndRow($col++, 1, $value);
    }
}

/**
 * @param $objPHPExcel
 * @param $file_name
 * @throws Exception
 */
function  generate_file($objPHPExcel, $file_name)
{
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $objWriter->save("data/export/$file_name.xlsx");
    unset($objPHPExcel);
}
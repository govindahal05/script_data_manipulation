<?php

require_once "lib/ExcelFile.php";
require_once 'lib/DBConnection.php';
require_once 'lib/CommonFunctions.php';

$warehouses = [1 => 'wimmera', 2 => 'horsham'];
$conn = dbConnect('horsham-wimmera_conceptdev', 'root', 'GO');

try {
    $objReader = new PHPExcel_Reader_Excel5();
    $objPHPExcel = $objReader->load("data/horsam_pac_data/products_.xls");
} catch (Exception $e) {
    die('Error loading file ' . $e->getMessage());
}


foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
    $worksheetTitle = $worksheet->getTitle();
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    for ($row = 1; $row <= $highestRow; ++$row) {
        $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
            null,
            true,
            false);
        $data = $rowData[0];
        $name = $data[7];
        $data[2] = check_product_exists($conn, $name, $warehouses);
        add_excel_row($objPHPExcel, $row, $data);

    }
}

generate_file($objPHPExcel, "products_");
close_excel_file($objPHPExcel);
close_excel_file($objReader);

/**
 * @param $conn
 * @param $warehouses
 * @return array
 */
function check_product_exists($conn, $name, $warehouses)
{
    $sql = "SELECT * FROM product left outer join warehouse_item on product.id = warehouse_item.itemable_id WHERE `itemable_type` = 'Fdw\\\\Cart\\\\Models\\\\Product' and product.name ='$name'";

    $result = mysqli_query($conn, $sql);
    $wareh = [];
    print_r("\n" . $sql . "\n");
    print_r("$result->num_rows records found");
    if ($result->num_rows > 0) {

        while ($row = mysqli_fetch_assoc($result)) {
            $wareh[$warehouses[$row['warehouse_id']]] = $warehouses[$row['warehouse_id']];
        }
    }
    if (!isset($wareh['horsham'])) {
        $wareh['horsham'] = 'horsham';
    }

    return implode("||", $wareh);
}

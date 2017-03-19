<?php
require_once "lib/ExcelFile.php";
require_once 'lib/DBConnection.php';
require_once 'lib/CommonFunctions.php';

$warehouses = [1 => 'wimmera', 2 => 'horsham'];
$conn = dbConnect('horsham-wimmera_conceptdev', 'root', 'GO');

try {
    $objReader = new PHPExcel_Reader_Excel5();
    $objPHPExcel = $objReader->load("data/horsham_gm_file/category-1.xls");
} catch (Exception $e) {
    die('Error loading file ' . $e->getMessage());
}


foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
    $worksheetTitle = $worksheet->getTitle();
    print_r($worksheetTitle);
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    for ($row = 1; $row <= $highestRow; ++$row) {
        $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
            null,
            true,
            false);
        $data = $rowData[0];
        $category = $data[1];
        $data[0] = check_category_exists($conn, $category, $warehouses);
        add_excel_row($objPHPExcel, $row, $data);

    }
}

generate_file($objPHPExcel, "category_");
close_excel_file($objPHPExcel);
close_excel_file($objReader);


$categories = get_categories($conn, $warehouses);

/**
 * @param $conn
 * @param $warehouses
 * @return array
 */
function check_category_exists($conn, $warehouses)
{
    $sql = "SELECT * FROM category left outer join warehouse_item on category.id = warehouse_item.itemable_id WHERE `itemable_type` = 'Fdw\\\\Cart\\\\Models\\\\Category'";
    $result = mysqli_query($conn, $sql);
    $wareh = [];
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

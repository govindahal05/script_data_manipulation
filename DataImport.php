<?php

require_once "ExcelFile.php";
$conn = mysqli_connect("localhost", "root", "GO", "horshamharley_data");

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$getData = mysqli_query($conn, "select * from pa_2nd");
$i = 1;
$variants = [];
$index = 0;
while ($row = mysqli_fetch_assoc($getData)) {

    $formatted_array = [];

    /* $formatted_array['org_sizes'] = $old_sizes;
     $formatted_array['org_prices'] = $old_prices;*/
    $formatted_array['id'] = $index;
    $formatted_array['product_type'] = 'product';
    $formatted_array['warehouses'] = 'horsham';
    $formatted_array['category'] = '';
    $formatted_array['brand'] = 'harley-davidson';
    $formatted_array['type'] = '';
    $formatted_array['condition'] = 'new';
    $formatted_array['name'] = trim(filter_var($row['Name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
    $formatted_array['slug'] = seoUrl($formatted_array['name']);
    $formatted_array['short_description'] = $row['Name'];
    $formatted_array['long_description'] = $row['Description'];
    if ( $formatted_array['long_description']=='') {
        $formatted_array['long_description'] = $row['Name'];

    }
    $formatted_array['order'] = '';
    $formatted_array['status'] = 'Enabled';
    $formatted_array['is_deleted'] = 'No';
    $formatted_array['visibility'] = '1';
    $formatted_array['meta_description'] = '';
    $formatted_array['meta_keywords'] = '';
    $formatted_array['meta_title'] = '';
    $formatted_array['variant_id'] = $index;
    $formatted_array['variant_sku'] = "PACSKU{$index}";
    $formatted_array['variant_name'] = $formatted_array['name'];
    $formatted_array['variant_slug'] = seoUrl($formatted_array['variant_name']);
    $formatted_array['variant_is_deleted'] = 'No';
    $formatted_array['variant_width'] = 0;
    $formatted_array['variant_height'] = 0;
    $formatted_array['variant_depth'] = 0;;
    $formatted_array['variant_weight'] = 0;
    $formatted_array['variant_quantity'] = 0;
    $formatted_array['variant_rrp'] = 0;
    $formatted_array['variant_rsp_price'] = $row['Price'] * 1.1;
    $formatted_array['variant_special_price'] = 0;
    $formatted_array['variant_images'] = str_replace(",", "||", $row['Images']);
    if ($formatted_array['variant_images']=='') {
        $formatted_array['variant_images'] = ($row['Product Line Group'] == 'Parts')? "Genuine Motor Accessories Logo.jpg":"Genuine Motor Parts Logo.jpg";
    }
        $variants[] = $formatted_array;
        $index++;
//        $i++;
    }
//}
$objPHPExcel = open_excel_file("products_");
$labels = array_keys($variants[0]);
add_label($objPHPExcel, $labels);
$index = 2;
foreach ($variants as $variant) {
    add_excel_row($objPHPExcel, $index, $variant);
    $index++;
}
generate_file($objPHPExcel, "products_");
close_excel_file($objPHPExcel);

function seoUrl($string)
{
    $string = trim($string);
//Lower case everything
    $string = strtolower($string);
//Make alphanumeric (removes all other characters)
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
//Clean up multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
//Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    return $string;
}
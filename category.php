<?php
require_once "ExcelFile.php";
$conn = mysqli_connect("localhost", "root", "GO", "horshamharley_data");

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$getData = mysqli_query($conn, "select * from version_2");
$i = 1;
$variants = [];
$index = 0;
$category = [];
while ($row = mysqli_fetch_assoc($getData)) {

    $old_sizes = $row['Sizes'];
    $old_prices = $row['Retail_exGST'];
    $new_size_array = explode(",", $old_sizes);
    $new_price_array = explode(",", $old_prices);

    $categories[$row['Section']][$row['Sub_Category']] = $row['Sub_Categories'];

    $i = 1;
    foreach ($new_size_array as $key => $new_size) {
        $formatted_array = [];
        $formatted_array['id'] = $index;
        $formatted_array['product_type'] = 'variant';
        $formatted_array['warehouses'] = '';
        $formatted_array['category'] = '';
        $formatted_array['brand'] = '';
        $formatted_array['type'] = '';
        $formatted_array['condition'] = '';
        $formatted_array['name'] = filter_var($row['name_with_color'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $formatted_array['slug'] = seoUrl($formatted_array['name']);;
        $formatted_array['short_description'] = '';
        $formatted_array['long_description'] = '';
        $formatted_array['order'] = '';
        $formatted_array['status'] = '';
        $formatted_array['is_ deleted'] = '';
        $formatted_array['visibility'] = '';
        $formatted_array['meta_description'] = '';
        $formatted_array['meta_keywords'] = '';
        $formatted_array['meta_title'] = '';
        $formatted_array['variant_id'] = $index;
        $formatted_array['variant_sku'] = "GMSKU{$index}";
        $formatted_array['variant_name'] = filter_var($row['name_with_color'], FILTER_SANITIZE_STRING,
                FILTER_FLAG_STRIP_HIGH) . " " . $new_size;
        $formatted_array['variant_slug'] = seoUrl($row['name_with_color']);
        $formatted_array['variant_is_ deleted'] = 'No';
        $formatted_array['variant_width'] = 0;
        $formatted_array['variant_height'] = 0;
        $formatted_array['variant_depth'] = 0;
        $formatted_array['variant_weight'] = 0;
        $formatted_array['variant_quantity'] = 0;
        $formatted_array['variant_rrp'] = 0;
        $formatted_array['variant_rsp_price'] = (isset($new_price_array[$key])) ? $new_price_array[$key] * 1.1 : 0;
        $formatted_array['variant_special_price'] = 0;
        $formatted_array['variant_images'] = '';

        if ($i == 1) {
            $formatted_array['category'] = seoUrl(filter_var($row['name_with_color'], FILTER_SANITIZE_STRING,
                FILTER_FLAG_STRIP_HIGH));;

            $formatted_array['is_ deleted'] = 'No';
            $formatted_array['condition'] = 'new';
            $formatted_array['brand'] = 'horsham-harley';
            $formatted_array['warehouses'] = 'default';
            $formatted_array['status'] = 'Enabled';
            $formatted_array['visibility'] = 1;


            $formatted_array['variant_images'] = str_replace(",", "||", $row['Images']);
            $formatted_array['product_type'] = 'product';
            $formatted_array['meta_description'] = filter_var($row['FullKeywords'], FILTER_SANITIZE_STRING,
                FILTER_FLAG_STRIP_HIGH);
            $formatted_array['meta_keywords'] = filter_var($row['Keywords'], FILTER_SANITIZE_STRING,
                FILTER_FLAG_STRIP_HIGH);
            $formatted_array['short_description'] = filter_var($row['name_with_color'], FILTER_SANITIZE_STRING,
                FILTER_FLAG_STRIP_HIGH);
            $formatted_array['long_description'] = filter_var($row['Description'], FILTER_SANITIZE_STRING,
                FILTER_FLAG_STRIP_HIGH);

        }
        $variants[] = $formatted_array;
        $index++;
        $i++;
    }
}

$objPHPExcel = open_excel_file("products_");
$labels = array_keys($variants[0]);
add_label($objPHPExcel, $labels);
$index = 3;
foreach ($variants as $variant) {
    add_excel_row($objPHPExcel, $index, $variant);
    $index++;
}
generate_file($objPHPExcel, "products_");
close_excel_file($objPHPExcel);

create_categories($categories);

function create_categories($categories)
{
    $objPHPExcel = open_excel_file("categories");
    $labels = array_keys($categories[0]);
    print_r($labels);
    add_label($objPHPExcel, $labels);

    $index = 2;
    foreach ($categories as $key => $category) {
        $parent_category['id'] = 1;
        $parent_category['warehouses'] = 'default';
        $parent_category['parent_category'] = '';
        $parent_category['name'] = $key;
        $parent_category['image'] = '';
        $parent_category['slug'] = seoUrl($key);
        $parent_category['content'] = '';
        $parent_category['order'] = '';
        $parent_category['meta_description'] = '';
        $parent_category['meta_keywords'] = '';
        $parent_category['meta_title'] = '';
        $parent_category['status'] = '';
        $parent_category['is_deleted'] = '';
        $parent_category['visibility'] = '';
        $categories[] = $parent_category;
        add_excel_row($objPHPExcel, $index, $parent_category);
        $index++;
    }
    generate_file($objPHPExcel, "category-1");
    close_excel_file($objPHPExcel);


}

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
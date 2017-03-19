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

//Processing Categories
create_parent_categories($categories, 'Category-1');

//Processing Categories
create_child_categories($second_level_categories, 'Category-2');

//Processing Categories
create_child_categories($third_level_categories, 'Category-3');

function create_products($conn, &$categories, &$second_level_categories, &$third_level_categories)
{
    //common settings
    $table = "original_file_6th";
    $brand = "harley-davidson";
    $getData = mysqli_query($conn, "select * from $table");

    $i = 1;
    $variants = [];
    $index = 0;
    $product_id = 1;
    while ($row = mysqli_fetch_assoc($getData)) {

        $old_color = $row['ColourFeature'];
        $old_sizes = $row['Sizes'];
        $old_prices = $row['Retail_exGST'];
        $new_size_array = explode(",", $old_sizes);
        $new_price_array = explode(",", $old_prices);
        $new_colors_array = explode(",", $old_color);

        $words = explode(" ", $row['Section']);
        $initials = "";

        foreach ($words as $w) {
            $initials .= $w[0];
        }

        $first_level_cat = $row['Section'];
        $second_level_cat = ($row['Category'] == '') ? $row['Section'] : $row['Category'];
        $third_level_cat = ($row['Sub_Category'] == '') ? (($second_level_cat == '') ? $first_level_cat : $second_level_cat) : $row['Sub_Category'];


        $first_level_cat_slug = seoUrl("$first_level_cat - $initials");
        $second_level_cat_slug = seoUrl("$second_level_cat - $initials");
        $third_level_cat_slug = seoUrl("$third_level_cat - $initials");

        $categories[$first_level_cat_slug] = $first_level_cat;
        $second_level_categories[$first_level_cat_slug][$second_level_cat_slug] = $second_level_cat;
        $third_level_categories[$second_level_cat_slug][$third_level_cat_slug] = $third_level_cat;


        $i = 1;
        foreach ($new_size_array as $key => $new_size) {

            if (($i > 1) && $new_price_array[$key] == 0) {
                continue;
            }

            $formatted_array = [];
            $formatted_array['id'] = $product_id;
            $formatted_array['product_type'] = 'variation';
            $formatted_array['warehouses'] = '';
            $formatted_array['category'] = '';
            $formatted_array['brand'] = '';
            $formatted_array['type'] = '';
            $formatted_array['condition'] = '';
            $formatted_array['name'] = trim(filter_var($row['Name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH) . " " . $new_colors_array[0]);
            $formatted_array['slug'] = seoUrl($formatted_array['name']);
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
            $formatted_array['variant_sku'] = $row['SKU'] . "-" . $new_size;
            $formatted_array['variant_name'] = $formatted_array['name'] . " " . $new_size;
            $formatted_array['variant_slug'] = seoUrl($formatted_array['variant_name']);
            $formatted_array['variant_is_ deleted'] = 'No';
            $formatted_array['variant_width'] = 0;
            $formatted_array['variant_height'] = 0;
            $formatted_array['variant_depth'] = 0;
            $formatted_array['variant_weight'] = 0;
            $formatted_array['variant_quantity'] = 0;
            $formatted_array['variant_rrp'] = 0;
            $formatted_array['variant_rsp_price'] = (isset($new_price_array[$key])) ? $new_price_array[$key] * 1.1 : $new_price_array[$key];
            $formatted_array['variant_special_price'] = 0;
            $formatted_array['variant_images'] = '';
            $formatted_array['images_not_found'] = '';


            if ($i == 1) {

                $formatted_array['id'] = $product_id;
                $formatted_array['variant_sku'] = $row['SKU'];
                $formatted_array['category'] = seoUrl($third_level_cat_slug);
                $formatted_array['is_ deleted'] = 'No';
                $formatted_array['condition'] = 'new';
                $formatted_array['brand'] = $brand;
                $formatted_array['warehouses'] = 'horsham';
                $formatted_array['status'] = 'Enabled';
                $formatted_array['visibility'] = 1;
                $images = explode(",", $row['Images']);

                $image_found = '';
                foreach ($images as $image) {
                    if (!file_exists("images/$image")) {
                        $formatted_array['images_not_found'] .= "not found:" . "," . $image;
                    } else {
                        $image_found = ($image_found == '') ? $image : $image_found;
                    }
                }

                if ($images) {
                    $categories[$first_level_cat_slug] = $first_level_cat . "," . $image_found;
                    $second_level_categories[$first_level_cat_slug][$second_level_cat_slug] = $second_level_cat . "," . $image_found;
                    $third_level_categories[$second_level_cat_slug][$third_level_cat_slug] = $third_level_cat . "," . $image_found;

                }

                $formatted_array['variant_images'] = str_replace(",", "||", $row['Images']);
                $formatted_array['product_type'] = 'product';
                $formatted_array['meta_title'] = $formatted_array['name'];
                $formatted_array['meta_description'] = "We sell " . "'" . $formatted_array['name'] . "'" . " buy it online now.";
                $formatted_array['meta_keywords'] = trim(filter_var($row['Keywords'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
                $formatted_array['short_description'] = trim(filter_var($row['Name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
                $formatted_array['long_description'] = trim(filter_var($row['Description'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
                if ($formatted_array['long_description'] == '') {
                    $formatted_array['long_description'] = $formatted_array['name'];

                }

            }
            $variants[] = $formatted_array;
            $index++;
            $i++;
        }
        $product_id++;
    }

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
}

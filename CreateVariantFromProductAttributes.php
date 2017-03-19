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
    $search_images_in_array = false;

    if ($search_images_in_array) {
        $available_images = get_available_images($conn, 'pac_images');
    }

    //common settings
    $table = "original_file_6th";
    $brand = "harley-davidson";
    $getData = mysqli_query($conn, "select * from $table");

    $i = 1;
    $variants = [];
    $index = 0;
    $total_record = $getData->num_rows;
    $loop_count = 0;
    $product_id = 1;
    while ($row = mysqli_fetch_assoc($getData)) {
        print_r("\n" . round(++$loop_count / $total_record, 2) * 100 . "% completed \n");
        $old_color = $row['ColourFeature'];
        $old_sizes = $row['Sizes'];
        $old_prices = $row['Retail_exGST'];
        $new_size_array = explode(",", $old_sizes);
        $new_price_array = explode(",", $old_prices);
        $new_colors_array = explode(",", $old_color);

        $first_level_cat = $row['Section'];
        $temp_slug = $first_level_cat;
        $first_level_cat_slug = seoUrl($temp_slug);
        $first_level_cat_slug = slugExists($first_level_cat_slug, 'category') > 0 ? getUniqueSlug($first_level_cat_slug, 'category', 1) : $first_level_cat_slug;
        /*print_r("\n first category $first_level_cat_slug");
        if (search_array($first_level_cat_slug, $categories)) {
            print_r("\n first category $first_level_cat_slug duplicate found ");
            $first_level_cat_slug .= "-1";
        }*/

        $second_level_cat = ($row['Category'] == '') ? $row['Section'] : $row['Category'];
        $temp_slug = $second_level_cat . " " . get_initials($row['Section']);
        $second_level_cat_slug = seoUrl($temp_slug);
        if (slugExists($second_level_cat_slug, 'category') > 0) {
            $second_level_cat_slug = getUniqueSlug($second_level_cat_slug, 'category', 1);
        }
        /*print_r("\n check second category $second_level_cat_slug");
        if (search_array($second_level_cat_slug, $second_level_categories)) {
            print_r("\n second level category $first_level_cat_slug duplicate found ");
            $second_level_cat_slug .= "-1";
        }*/

        $third_level_cat = ($row['Sub_Category'] == '') ? (($second_level_cat == '') ? $first_level_cat : $second_level_cat) : $row['Sub_Category'];
        $temp_slug = $third_level_cat . " " . get_initials($temp_slug) . " " . get_initials($row['Section']);
        $third_level_cat_slug = seoUrl($temp_slug);
        if (slugExists($third_level_cat_slug, 'category') > 0) {
            $third_level_cat_slug = getUniqueSlug($third_level_cat_slug, 'category', 1);
        }
        /*print_r("\n check third category $second_level_cat_slug");
        if (search_array($third_level_cat_slug, $third_level_categories)) {
            print_r("\n third category $third_level_cat_slug duplicate found ");
            $third_level_cat_slug .= "-1";
        }*/

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
            $formatted_array['is_deleted'] = '';
            $formatted_array['visibility'] = '';
            $formatted_array['meta_description'] = '';
            $formatted_array['meta_keywords'] = '';
            $formatted_array['meta_title'] = '';
            $formatted_array['variant_id'] = $index;
            $formatted_array['variant_sku'] = $row['SKU'] . "-" . $new_size;
            $formatted_array['variant_name'] = $formatted_array['name'] . " " . $new_size;
            $formatted_array['variant_slug'] = seoUrl($formatted_array['variant_name']);
            $formatted_array['variant_is_deleted'] = 'No';
            $formatted_array['variant_width'] = 0;
            $formatted_array['variant_height'] = 0;
            $formatted_array['variant_depth'] = 0;
            $formatted_array['variant_weight'] = 0;
            $formatted_array['variant_quantity'] = 0;
            $formatted_array['variant_rrp'] = 0;
            $formatted_array['variant_rsp_price'] = (isset($new_price_array[$key])) ? $new_price_array[$key] * 1.1 : 0;
            $formatted_array['variant_special_price'] = 0;
            $formatted_array['variant_images'] = '';
            $formatted_array['images_not_found'] = '';


            if ($i == 1) {
                $formatted_array['id'] = $product_id;
                $formatted_array['variant_sku'] = $row['SKU'];
                $formatted_array['category'] = $third_level_cat_slug;
                $formatted_array['is_deleted'] = 'No';
                $formatted_array['condition'] = 'new';
                $formatted_array['brand'] = $brand;
                $formatted_array['warehouses'] = 'horsham';
                $formatted_array['status'] = 'Enabled';
                $formatted_array['visibility'] = 1;
                $images = explode(",", $row['Images']);


                $image_found = '';
                if ($search_images_in_array) {
                    foreach ($images as $image) {
                        if (!array_search($image, $available_images)) {
                            $formatted_array['images_not_found'] .= "not found:" . "," . $image;
                        } else {
                            $image_found = ($image_found == '') ? $image : $image_found;
                        }
                    }
                } else {
                    foreach ($images as $image) {
                        if (!file_exists("images/$image")) {
                            $formatted_array['images_not_found'] .= "not found:" . "," . $image;
                        } else {
                            $image_found = ($image_found == '') ? $image : $image_found;
                        }
                    }
                }

                if ($images) {
                    $categories[$first_level_cat_slug] = [$first_level_cat, $image_found];
                    $second_level_categories[$first_level_cat_slug][$second_level_cat_slug] = [$second_level_cat, $image_found];
                    $third_level_categories[$second_level_cat_slug][$third_level_cat_slug] = [$third_level_cat, $image_found];
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
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


/**
 * @param $conn
 * @param $categories
 * @param $second_level_categories
 * @param $third_level_categories
 */
function create_products($conn, &$categories, &$second_level_categories, &$third_level_categories)
{
    $search_images_in_array = true;

    if ($search_images_in_array) {
        $available_images = get_available_images($conn, 'pac_images');
    }
    //common settings
    $table = "pa_raw_clean";
    $brand = "harley-davidson";
    $getData = mysqli_query($conn, "SELECT * FROM $table order by slug");

    $variants = [];
    $index = 5;
    $unique_slug = '';
    $unique_fitment = '';
    $total_removed = 0;
    $duplicate = 0;
    $removed = [];
    $total_record = $getData->num_rows;
    $loop_count = 0;
    while ($row = mysqli_fetch_assoc($getData)) {

        print_r(round(++$loop_count / $total_record, 2) * 100 . "% completed \n");
        $first_level_cat = $row['Product Line Group'];
        $temp_slug = $first_level_cat . " " . get_initials($row['Product Line Group']);
        $first_level_cat_slug = seoUrl($temp_slug);
        $first_level_cat_slug = slugExists($first_level_cat_slug, 'category') > 0 ? getUniqueSlug($first_level_cat_slug, 'category', 1) : $first_level_cat_slug;


        $second_level_cat = ($row['Retail Product Line'] == '') ? $row['Product Line Group'] : $row['Retail Product Line'];
        $temp_slug = $second_level_cat . " " . get_initials($row['Product Line Group']);
        $second_level_cat_slug = seoUrl($temp_slug);
        $second_level_cat_slug = slugExists($second_level_cat_slug, 'category') > 0 ? getUniqueSlug($second_level_cat_slug, 'category', 1) : $second_level_cat_slug;


        $third_level_cat = ($row['Sub-Product Line'] == '') ? (($second_level_cat == '') ? $first_level_cat : $second_level_cat) : $row['Sub-Product Line'];
        $temp_slug = $third_level_cat . " " . get_initials($temp_slug);
        $third_level_cat_slug = seoUrl($temp_slug);
        $third_level_cat_slug = slugExists($third_level_cat_slug, 'category') > 0 ? getUniqueSlug($third_level_cat_slug, 'category', 1) : $third_level_cat_slug;


        $categories[$first_level_cat_slug] = [$first_level_cat, ''];
        $second_level_categories[$first_level_cat_slug][$second_level_cat_slug] = [$second_level_cat, ''];
        $third_level_categories[$second_level_cat_slug][$third_level_cat_slug] = [$third_level_cat, ''];

        $formatted_array = [];
        $formatted_array['id'] = $index;
        $formatted_array['product_type'] = 'product';
        $formatted_array['warehouses'] = 'horsham';
        $formatted_array['category'] = $third_level_cat_slug;
        $formatted_array['brand'] = $brand;
        $formatted_array['type'] = '';
        $formatted_array['condition'] = 'new';
        $formatted_array['name'] = trim(filter_var(htmlspecialchars(clean_name($row['name_extradescription'])), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
        $formatted_array['name'] = html_entity_decode($formatted_array['name'], ENT_QUOTES);

        $formatted_array['slug'] = seoUrl(html_entity_decode($formatted_array['name']));
        $formatted_array['short_description'] = $formatted_array['name'];

        $formatted_array['long_description'] = html_entity_decode(trim(filter_var($row['Description'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH)), ENT_QUOTES);

        if ($formatted_array['long_description'] == '') {
            $formatted_array['long_description'] = $formatted_array['name'];
        }

        $formatted_array['order'] = '';
        $formatted_array['status'] = 'Enabled';
        $formatted_array['is_deleted'] = 'No';
        $formatted_array['visibility'] = '1';
        $formatted_array['meta_description'] = "We sell '" . $formatted_array['name'] . "' buy it online now.";
        $formatted_array['meta_keywords'] = html_entity_decode(trim(filter_var($row['FullKeywords'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH)), ENT_QUOTES);

        $formatted_array['meta_title'] = $formatted_array['name'];
        $formatted_array['variant_id'] = $index;
        $formatted_array['variant_sku'] = $row['ProductId'];
        $formatted_array['variant_name'] = $formatted_array['name'];
        $formatted_array['variant_slug'] = seoUrl($formatted_array['variant_name']);

        if (search_array($formatted_array['slug'], $variants)) {
            $formatted_array['name'] .= "-1";
            $formatted_array['variant_name'] .= "-1";
            $formatted_array['slug'] .= "-1";
            $formatted_array['variant_slug'] .= "-1";

            $formatted_array['slug'] = slugExists($third_level_cat_slug, 'product') > 0 ? getUniqueSlug($formatted_array['slug'], 'product', 1) : $formatted_array['slug'];
            $formatted_array['variant_slug'] = slugExists($third_level_cat_slug, 'product_variant') > 0 ? getUniqueSlug($formatted_array['variant_slug'], 'product_variant', 1) : $formatted_array['variant_slug'];

        }

        $formatted_array['variant_is_deleted'] = 'No';
        $formatted_array['variant_width'] = 0;
        $formatted_array['variant_height'] = 0;
        $formatted_array['variant_depth'] = 0;
        $formatted_array['variant_weight'] = 0;
        $formatted_array['variant_quantity'] = 0;
        $formatted_array['variant_rrp'] = 0;
        $formatted_array['variant_rsp_price'] = $row['Price'] * 1.1;
        $formatted_array['variant_special_price'] = 0;
        $formatted_array['variant_admin_price'] = '';
        $formatted_array['variant_images'] = $row['Images'];
        $formatted_array['images_not_found'] = '';


        $images = explode(",", $formatted_array['variant_images']);

        $image_found = '';
        if ($search_images_in_array) {
            foreach ($images as $key => $image) {
                if (!array_search($image, $available_images)) {
                    $formatted_array['images_not_found'] .= "not found:$image";
                    unset($images[$key]);
                }
            }
        } else {
            foreach ($images as $key => $image) {
                if (!file_exists("images/$image")) {
                    $formatted_array['images_not_found'] .= "not found:$image";
                    unset($images[$key]);
                }
            }
        }

        $formatted_array['variant_images'] = implode("||", $images);
        if ($formatted_array['variant_images'] == '') {
            $formatted_array['variant_images'] = ($row['Product Line Group'] == "Parts") ? "part.jpg" : "accessories.jpg";
        }

        $image_found = (count($images) > 0) ? array_pop($images) : $formatted_array['variant_images'];

        $categories[$first_level_cat_slug] = [$first_level_cat, $image_found];
        $second_level_categories[$first_level_cat_slug][$second_level_cat_slug] = [$second_level_cat, $image_found];
        $third_level_categories[$second_level_cat_slug][$third_level_cat_slug] = [$third_level_cat, $image_found];


        $formatted_array['fitment'] = $row['Fitment'];
        if ($unique_slug != $formatted_array['slug']) {
            $unique_slug = $formatted_array['slug'];
            $duplicate = 1;
        } else {
            $duplicate++;
        }
        if ($index > 5) {
            if ($duplicate > 1) {
                if (($formatted_array['fitment'] != '') && ($unique_fitment == $formatted_array['fitment'])) {
                    $variants[$index - 1]['fitment'] .= "||" . $formatted_array['fitment'];
                    $removed[] = $row['ProductId'];
                    $total_removed++;
                    continue;
                } else {
                    if ($duplicate > 1) {
                        $formatted_array['name'] .= "-$duplicate";
                        $formatted_array['slug'] .= "-$duplicate";
                        $formatted_array['variant_name'] .= "-$duplicate";
                        $formatted_array['variant_slug'] .= "-$duplicate";
                    }

                }
            }

        }

        $unique_fitment = ($unique_fitment != $formatted_array['fitment']) ? $formatted_array['fitment'] : $unique_fitment;

        $variants[$index] = $formatted_array;
        $index++;

    }

    $objPHPExcel = open_excel_file("products_");
    $labels = array_keys($variants[5]);
    add_label($objPHPExcel, $labels);
    $index = 2;
    foreach ($variants as $variant) {
        add_excel_row($objPHPExcel, $index, $variant);
        $index++;
    }

    generate_file($objPHPExcel, "products_");
    close_excel_file($objPHPExcel);
    echo "\n $index records\n";
    echo "\n $total_removed records removed \n";
    echo "\n $total_removed " . implode(",", $removed) . " \n";
}


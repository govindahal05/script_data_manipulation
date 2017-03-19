<?php
/**
 * Created by PhpStorm.
 * User: govin
 * Date: 8/15/16
 * Time: 11:18 AM
 */<?php
require_once "ExcelFile.php";
$conn = mysqli_connect("localhost", "root", "GO", "horshamharley_data");

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$getData = mysqli_query($conn, "select * from pa_1st");
$i = 1;
$variants = [];
$index = 0;
while ($row = mysqli_fetch_assoc($getData)) {

    /* $old_sizes = $row['Sizes'];
     $old_prices = $row['Retail_exGST'];
     $old_color = $row['ColourFeature'];
     $new_size_array = explode(",", $old_sizes);
     $new_price_array = explode(",", $old_prices);
     $new_colors_array = explode(",", $old_color);
     // print_r($row);
     $first_parent = $row['Section'];
     $second_parent = $row['Category'];

     $i = 1;*/
//    foreach ($new_size_array as $key => $new_size) {
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
    $formatted_array['name'] = $row['Name']
        $formatted_array['slug'] = seoUrl($formatted_array['name']);;
        $formatted_array['short_description'] = $row['Name'];
        if(!$row['Description']){
            $formatted_array['long_description'] = $row['Name'];

        }

        $formatted_array['long_description'] = $row['Description'];
        $formatted_array['order'] = '';
        $formatted_array['status'] = 'Enabled';
        $formatted_array['is_deleted'] = 'No';
        $formatted_array['visibility'] = '1';
        $formatted_array['meta_description'] = '';
        $formatted_array['meta_keywords'] = '';
        $formatted_array['meta_title'] = '';
        $formatted_array['variant_id'] = $index;
        $formatted_array['variant_sku'] = "PACSKU{$index}";
        $formatted_array['variant_name'] = $formatted_array['Name'];
        $formatted_array['variant_slug'] = seoUrl($formatted_array['variant_name']);
        $formatted_array['variant_is_deleted'] = 'No';
        $formatted_array['variant_width'] = 0;
        $formatted_array['variant_height'] = 0;
        $formatted_array['variant_depth'] = 0;
        $formatted_array['variant_weight'] = 0;
        $formatted_array['variant_quantity'] = 0;
        $formatted_array['variant_rrp'] = 0;
        $formatted_array['variant_rsp_price'] = $row['Price'] * 1.1;
        $formatted_array['variant_special_price'] = 0;
        if(!$row['Images']){
            if($row['Product Line Group']=='Parts'){
                $formatted_array['variant_images'] = "Genuine Motor Parts Logo.jpg";
            }
            $formatted_array['variant_images'] = "Genuine Motor Accessories Logo.jpg";
        }
        $formatted_array['variant_images'] = $row['Images'];

//        $formatted_array['images_not_found'] = "";
       /* $formatted_array['first_parent'] = "";
        $formatted_array['first_parent_slug'] = "";
        $formatted_array['second_parent'] = "";
        $formatted_array['second_parent_slug'] = "";
        $formatted_array['child'] = "";
        $formatted_array['child_slug'] = "";*/

       /* if ($i == 1) {

            $formatted_array['is_ deleted'] = 'No';
            $formatted_array['condition'] = 'new';
            $formatted_array['brand'] = 'harley-davidson';
            $formatted_array['warehouses'] = 'default';
            $formatted_array['status'] = 'Enabled';
            $formatted_array['visibility'] = 1;
            $images = explode(",", $row['Images']);


            foreach ($images as $image) {
                if (!file_exists("images/$image")) {
                    $formatted_array['images_not_found'] .= "not found:"."," . $image;
                }/*else{
                    $result=copy("images/$image","upload/$image");
                    if($result){
                        echo $index;
                        echo "true \n";
                    }
                    echo $index;
                    echo "false \n";
                }*/
          /*  }


            $formatted_array['variant_images'] = str_replace(",", "||", $row['Images']);
            $image_list = explode(",", $row['Images']);
            $formatted_array['product_type'] = 'product';
            $formatted_array['meta_title'] = $formatted_array['name'];
            $formatted_array['meta_description'] = "We sell "."'".$formatted_array['name']."'"." buy it online now.";
            $formatted_array['meta_keywords'] = trim(filter_var($row['Keywords'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
            $formatted_array['short_description'] = trim(filter_var($row['Name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
            $formatted_array['long_description'] = trim(filter_var($row['Description'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));


            $first_parent1 = explode(" ", $row['Section']);
            $acronym = "";

            foreach ($first_parent1 as $w) {
                $acronym .= $w[0];
            }
            $formatted_array['second_parent'] = trim(filter_var($row['Category'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
            $formatted_array['second_parent_slug'] = seoUrl($formatted_array['second_parent'] . " " . $acronym);
    */

            /*$second_parent_short = explode(" ", $row['Category'])
                $acronym2 = "";

             foreach ($second_parent_short as $w2) {
                 $acronym2 .= $w2[0];
             }
            if (!$row['SubCategory'] > '') {
                $formatted_array['child'] = $formatted_array['second_parent'];
            } else {
                $formatted_array['child'] = $row['SubCategory'];

            }
            $formatted_array['child_slug'] = seoUrl($formatted_array['child'] . " " . $acronym2);
                        $formatted_array['category'] = $formatted_array['child_slug'];*/



//        }
//        $variants[] = $formatted_array;
//        $index++;
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
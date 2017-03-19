<?php
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
/**
 * @param $string
 * @return mixed|string
 */
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

/**
 * @param $str
 * @return mixed
 */
function clean_name($str)
{
    $str = str_replace(",", ", ", $str);
    $str = str_replace(",  ", ", ", $str);
    $str = str_replace(" - ", "-", $str);
    $str = str_replace(" -", "-", $str);
    $str = str_replace("- ", "-", $str);

    return $str;
}

/**
 * @param $slug
 * @param $count
 * @return string
 */
function getUniqueSlug($slug, $table, $count = 1, $conn = false)
{
    if ($conn == false) {
        $conn = dbConnect('horsham-wimmera_conceptdev');
    }

    $new_slug = $slug . "-" . $count;
    $slug_count = mysqli_query($conn, "SELECT * FROM $table where slug = '$new_slug'");

    if ($slug_count->num_rows > 0) {
        $new_slug = getUniqueSlug($slug, $table, $count + 1, $conn);
    }

    if ($conn) {
        mysqli_close($conn);
    }
    return $new_slug;
}

/**
 * @param $slug
 * @param $table
 * @return int
 */
function slugExists($slug, $table)
{
    $conn = dbConnect('horsham-wimmera_conceptdev');
    $slug_count = mysqli_query($conn, "SELECT * FROM $table where slug = '$slug'");
    $total = $slug_count->num_rows;
    mysqli_close($conn);
    return ($total > 0) ? $slug_count->num_rows : 0;
}

/**
 * @param $category
 */
function get_initials($category)
{
    $words = str_word_count($category,1);
    $initials = "";

    foreach ($words as $w) {
        $initials .= $w[0];
    }

    return $initials;

}


function create_parent_categories($categories, $filename)
{
    $objPHPExcel = open_excel_file($filename);
    $labels = [
        'id',
        'warehouses',
        'parent_category',
        'image',
        'name',
        'slug',
        'content',
        'order',
        'meta_description',
        'meta_keywords',
        'meta_title',
        'status',
        'is_deleted',
        'visibility'
    ];
    add_label($objPHPExcel, $labels);

    $index = 2;
    foreach ($categories as $slug => $properties) {
        $parent_category['id'] = '';
        $parent_category['warehouses'] = 'horsham';
        $parent_category['parent_category'] = '';
        $parent_category['image'] = $properties[1];
        $parent_category['name'] = $properties[0];
        $parent_category['slug'] = $slug;
        $parent_category['content'] = '';
        $parent_category['order'] = '';
        $parent_category['meta_description'] = '';
        $parent_category['meta_keywords'] = '';
        $parent_category['meta_title'] = '';
        $parent_category['status'] = 'Enabled';
        $parent_category['is_deleted'] = 'No';
        $parent_category['visibility'] = 1;
        add_excel_row($objPHPExcel, $index, $parent_category);
        $index++;
    }
    generate_file($objPHPExcel, $filename);
    close_excel_file($objPHPExcel);
}


function create_child_categories($categories, $filename)
{
    $objPHPExcel = open_excel_file($filename);
    $labels = [
        'id',
        'warehouses',
        'parent_category',
        'image',
        'name',
        'slug',
        'content',
        'order',
        'meta_description',
        'meta_keywords',
        'meta_title',
        'status',
        'is_deleted',
        'visibility'
    ];
    add_label($objPHPExcel, $labels);
    $index = 2;
    foreach ($categories as $parent_slug => $child_categories) {
        foreach ($child_categories as $slug => $properties) {
            if ($properties[0] == '') {
                continue;
            }
            $second_level_category['id'] = '';
            $second_level_category['warehouses'] = 'horsham';
            if($parent_slug==$slug){
                continue;
            }
            $second_level_category['parent_category'] = $parent_slug;
            $second_level_category['image'] = $properties[1];
            $second_level_category['name'] = $properties[0];
            $second_level_category['slug'] = $slug;
            $second_level_category['content'] = '';
            $second_level_category['order'] = '';
            $second_level_category['meta_description'] = '';
            $second_level_category['meta_keywords'] = '';
            $second_level_category['meta_title'] = '';
            $second_level_category['status'] = 'Enabled';
            $second_level_category['is_deleted'] = 'No';
            $second_level_category['visibility'] = 1;
            add_excel_row($objPHPExcel, $index, $second_level_category);
            $index++;
        }
    }
    generate_file($objPHPExcel, $filename);
    close_excel_file($objPHPExcel);
}


/**
 * @param $needle
 * @param $haystack
 * @return bool
 */
function search_array($str, $arr)
{
    if (in_array($str, $arr) || array_key_exists($str, $arr)) {
        return true;
    }
    foreach ($arr as $element) {
        if (is_array($element) && search_array($str, $element))
            return true;
    }
    return false;
}

function get_available_images($conn, $table)
{
    //common settings
    $images_data = mysqli_query($conn, "SELECT * FROM $table");

    $images = [];
    while ($row = mysqli_fetch_assoc($images_data)) {
        $images[] = $row['image'];
    }

    return $images;

}
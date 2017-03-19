<?php
require_once 'lib/ExcelFile.php';
require_once 'lib/DBConnection.php';
require_once 'lib/CommonFunctions.php';


$conn = dbConnect('horshamharley_data');

//col1	col2	 col3	col4	         col5	             col6                col7	        col8	              col9	                 col10	      col11	     col12	      col13	   col14	     col15	           col16	          col17	         col18	     col19	col20	             col21	col22	      col23	                 col24	     col25	        col26	col27	      col28	                        col29	                                                col30	     col31	        col32
//Type	Category Name	Product Name	Short Description	Long Description	Product Status	Online Shopping Status	Featured	Hot Product	Brand Name	Brand Image	Colour Applicable	Material Applicable	Size Applicable	Colour	Material	Size	Colour/Material/Size Image	Part Number	Quantity (Available)	Length(cm)	Width(cm)	Height(cm)	Weight(kg)	eBay Status	Other Image Files	Image Title	Image Alt	Ebay Freight Applicable(Yes/No)	Web Price	eBay Price

$table = "dm_data";
$sql_join = "SELECT * from $table";
$query_result = mysqli_query($conn, $sql_join);

if ($query_result->num_rows > 0) {
    $objPHPExcel = open_excel_file("products");
    $variants = [];
    $index = 2;
    while ($row = mysqli_fetch_assoc($query_result)) {
        //change the columns as per the fields
        $formatted_array['id'] = $row['product_id'];
        $formatted_array['product_type'] = 'product';
        $formatted_array['warehouses'] = 'default';
        $formatted_array['category'] = $row['col2'];
        $formatted_array['brand'] = $row['col10'];
        $formatted_array['type'] = '';
        $formatted_array['condition'] = '';
        $formatted_array['name'] = filter_var(($row['col1'] == 'P')?'':$row['col3'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $formatted_array['slug'] = seoUrl($formatted_array['col3']);;
        $formatted_array['short_description'] = filter_var($row['col4'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $formatted_array['long_description'] = filter_var($row['col5'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);;
        $formatted_array['order'] = '';
        $formatted_array['status'] = '';
        $formatted_array['is_ deleted'] = 'No';
        $formatted_array['visibility'] = 1;
        $formatted_array['meta_description'] = '';
        $formatted_array['meta_keywords'] = '';
        $formatted_array['meta_title'] = '';
        $formatted_array['variant_id'] = $index;
        $formatted_array['variant_sku'] = "DMSKU{$index}";
        $formatted_array['variant_name'] = filter_var(($row['col1'] == 'V')?'':$row['col3'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $formatted_array['variant_slug'] = seoUrl( $formatted_array['variant_name']);
        $formatted_array['variant_is_ deleted'] = 'No';
        $formatted_array['variant_width'] = 0;
        $formatted_array['variant_height'] = 0;
        $formatted_array['variant_depth'] = 0;
        $formatted_array['variant_weight'] = 0;
        $formatted_array['variant_quantity'] = 0;
        $formatted_array['variant_rrp'] = 0;
        $formatted_array['variant_rsp_price'] = $row['price'];
        $formatted_array['variant_special_price'] = 0;
        $formatted_array['variant_images'] = (isset($images[$row['product_id']]))?$images[$row['product_id']]:'';
        add_excel_row($objPHPExcel, $index++, $formatted_array);
    }

    add_label($objPHPExcel, array_keys($formatted_array));
    generate_file($objPHPExcel, "products");
    close_excel_file($objPHPExcel);

}
dbClose($conn);

?>
<?php

require_once "lib/ExcelFile.php";
require_once 'lib/DBConnection.php';
require_once 'lib/CommonFunctions.php';

$conn = dbConnect('hor_wim_live');

"SELECT manage_make.*,new_used_bikes.*,manage_models.*,prod_images.* FROM new_used_bikes left JOIN manage_make on new_used_bikes.make_id= manage_make.make_id left JOIN manage_models on new_used_bikes.make_id= manage_models.model_id left JOIN prod_images on new_used_bikes.img_session= prod_images.session_id "

"SELECT new_used_bikes.*, manage_make.make_name, manage_models.model_name, manufacture.mnfctr_name,(SELECT  GROUP_CONCAT(prod_images.image_name, "||")
FROM  prod_images
WHERE prod_images.session_id=new_used_bikes.img_session) as image, manage_models.model_name
FROM new_used_bikes
left JOIN manage_make on new_used_bikes.make_id= manage_make.make_id
left JOIN manage_models on new_used_bikes.model_id= manage_models.model_id
left JOIN manufacture on new_used_bikes.manufacture_id= manufacture.mnfctr_id
where new_used_bikes.manufacture_id = 2"


SELECT blog.*, blog_category.*
FROM blog
left JOIN blog_category on blog_category.blog_category_id= blog.blog_category_id
where blog.company_id = 1

SELECT blog_images.blog_id, blog_images.blog_image, blog.blog_id
FROM `blog_images`
LEFT JOIN blog on blog.blog_id= blog_images.blog_id
WHERE blog.company_id=1 AND blog.blog_id=blog_images.blog_id

SELECT blog_comments.blog_id, blog_comments.description
FROM `blog_comments`
LEFT JOIN blog on blog.blog_id= blog_comments.blog_id
WHERE blog.company_id=1 AND blog.blog_id=blog_comments.blog_id

SELECT blog_comments.blog_id, blog_comments.blog_comment_description
FROM `blog_comments`
LEFT JOIN blog on blog.blog_id= blog_comments.blog_id
WHERE blog.company_id=1 AND blog.blog_id=blog_comments.blog_id



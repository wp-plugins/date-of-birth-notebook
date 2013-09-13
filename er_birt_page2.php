<?php

//http://bhoover.com/wp_image_editor-wordpress-image-editing-tutorial/
//--------------------------
//http://www.fancybox.net/api
//Изменение размеров изображения, нужно придумать
$url_plugin_s = "$_SERVER[SCRIPT_NAME]?page=er_birtday_sett";
$url_content = content_url() . '/er_birthday/';
//$patch_er_birt=ABSPATH.'wp-content/er_birthday/';
echo "<form action=\"$url_plugin_s\" method=\"post\">";
echo '<input type="text" name="text_er_set"/>';
echo '<input type="submit" value="Отправить" name="er_set_post"/>';
//include_once includes_url().'/class-wp-image-editor.php ';
if ($_POST['er_set_post']) {

    echo "<br>" . "<strong>Сообщение из формы: </strong>" . $_POST['text_er_set'];
   //echo er_seze_image1("241287-6402.jpg");
}

//function er_seze_image1($file_patch) {
////stream( $mime_type = null );
//    $img = wp_get_image_editor(ABSPATH . 'wp-content/er_birthday/' . $file_patch);
//    if (!is_wp_error($img)) {
//        //Массив для множества размеров
//            $sizes_array =     array(
//        array ('width' => 100, 'height' => 100, 'crop' => true),
//    );
// 
//    $resize = $img->multi_resize( $sizes_array );
// 
//    foreach ($resize as $row) {
//        $img->save($row['file']);
//    }
//    }
//    return $row['file'];
//}

//echo includes_url().'/class-wp-image-editor.php ';
?>

<?php
/*
  Plugin Name: Date of birth notebook
  Plugin URI: http://www.zixn.ru/category/wp_create_plugin
  Description: Plug-in notepad or notebook, keep records of the names and dates, birthdays shows. Easy and simple.
  Version: 1.5
  Author: Djon
  Author URI: http://zixn.ru
 */
/*  Copyright 2013  Djon  (email: Ermak_not@mail.ru)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 */
?>
<?php
//include_once 'er_data.php';
//Подключаем функции загрзки
//include_once 'upload_fun.php';
//Активация плагина и создание таблиц
register_activation_hook(__FILE__, 'er_create_table');
//Удаляем настройки при диактивации плагина
register_deactivation_hook(__FILE__, 'er_drop_table');
//Создание таблицы плагина в mysql
global $er_db_version, $table_name;
$er_db_version = "2.2";
//Название таблицы
$table_name = $wpdb->prefix . "erbirthday";
//Адрес плагина
$url = "er_birthday";
//Время дла базы
$url_plugin = "$_SERVER[SCRIPT_NAME]?page=er_birthday";
//Опция в базе ВордПресс
$name_options = "er_birthday_delete_db";
$name_options_sort = "er_sortirovka";
//Варианты сортировки
$er_asc = "ASC";
$er_desc = "DESC";
//Папка создаваемая плагином
$patch_er_birt = ABSPATH . 'wp-content/er_birthday/';
//Путь до папки плагина как URL
$url_content = content_url() . '/er_birthday/';

//Создаём таблицу 27.08.2013 Добавил новое поле image
function er_create_table() {
    global $wpdb;
    global $er_db_version, $name_options_sort, $er_desc;
    //Создаем папку для плагина ABSPATCH это полный путь от корня до папки юзера на сервере
    @mkdir(ABSPATH . 'wp-content/er_birthday');
    $table_name = $wpdb->prefix . "erbirthday";
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {


        $sql = "CREATE TABLE " . $table_name . " (
	id INT PRIMARY KEY AUTO_INCREMENT,
        fio VARCHAR(60),
        data_fio TEXT,
        zametka TEXT,
        image TEXT,
        image_orig TEXT,
        image_put TEXT,
        image_puto TEXT
	);";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        //$welcome_text="Текст";
        //$rows_affected = $wpdb->insert($table_name, array('time' => current_time('mysql'), 'text' => $welcome_text));
//Опция версии в базе
        add_option("er_db_version", $er_db_version);
        //Опция удаления записей после деинстраляции плагина
        add_option($name_options, '0', '', 'yes');
        //Опция сортировки вывода данных
        add_option($name_options_sort, $er_desc, '', 'yes');
    }
}

//Запись данных в таблицу
function insert_table($fio, $data, $zametka, $image, $image_orig,$image_put,$image_puto) {
    global $wpdb, $table_name;
    $query_insert = $wpdb->prepare("INSERT INTO $table_name(fio,data_fio,zametka,image,image_orig,image_put,image_puto)
            VALUES('$fio','$data','$zametka','$image','$image_orig','$image_put','$image_puto')");
    $wpdb->query($query_insert);
}
//Подключаем фэнсибок для изображений 
function fancebook_er() {
    
    ?>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/wp-content/plugins/date-of-birth-notebook/fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />
    <script type="text/javascript" src="/wp-content/plugins/date-of-birth-notebook/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
    <script type="text/javascript">
        var jf = jQuery.noConflict();
        jf(document).ready(function() {

            jf("a.openbk").fancybox({
                'title':'Карточка Блокнота',
                'transitionIn': 'elastic',
                'transitionOut': 'elastic',
                'speedIn': 300,
                'speedOut': 300,
                'overlayShow': true,
                'hideOnContentClick': false,
                'type': 'iframe',
   
                'autoDimensions':true,
                'overlayColor': '#000000',
                'overlayOpacity': 0.5,
                'centerOnScroll': true,
                'padding': 10,
                'margin': 0,
                'scrolling': 'no'
            });

        });</script>
    <?php
}

//Ловим ПОСТ из формы и отправляем их в "запись данных"
function post_data_fio() {
    global $patch_er_birt, $url_content;
    $pravilo_date = "/[0-3]+[0-9]+\.[0-1]+[0-9]+\.[0-2]+[0-9]+[0-9]+[0-9]+/";
//Нажата ли кнопка - запись
    if (isset($_POST['zapisat'])) {
        $fio = trim($_POST['fio']);
        $data = trim($_POST['data_fio']);
        $zametka = ($_POST['zametka']);
        //Загрузка фотки
        $name_file = $_FILES['er_upfile']['name'];
        $tmp_file = $_FILES['er_upfile']['tmp_name'];
        //Изменил метот вордпресса на метод php
        //$er_up = wp_upload_bits($name_file, null, file_get_contents("$tmp_file"));
        move_uploaded_file($tmp_file, $patch_er_birt . $name_file);
        //функция ресайза изображения, описанна выше
        $img = wp_get_image_editor($patch_er_birt . $name_file);
        if (!is_wp_error($img)) {
            //Массив для множества размеров
            $sizes_array = array(
                array('width' => 100, 'height' => 100, 'crop' => true),
            );

            $resize = $img->multi_resize($sizes_array);

            foreach ($resize as $row) {
                $img->save($row['file']);
            }
        } //Конец ресайза

       // $image = '<p id="img_position"><a href="' . $url_content . $name_file . '" class="openbk" >Ориг.</a>' . '<img src="' . $url_content . $row['file'] . '"></p>';
        $image=$url_content . $row['file'];
        $image_orig=$url_content . $name_file;
        $image_put=$patch_er_birt.$row['file'];
        $image_puto=$patch_er_birt.$name_file;
        if ($fio !== "" and $data !== "") {
            if (preg_match_all($pravilo_date, $data, $result_data)) {
                $data = $result_data[0][0];
                insert_table($fio, $data, $zametka, $image,$image_orig,$image_put,$image_puto);
            } else {

                echo '<div id="er_warning">Вы ввели не верную дату!</div>';
            }
        } else {
            echo '<div id="er_warning">Одно или оба поля не заполнены!</div>';
        }
    }
}

//Постраничный вывод записей блокнота
function string_number($num_er, $page_count) {
    global $url_plugin;
    for ($i = 1; $i <= $page_count; $i++) {
        if ($i == $num_er) {
            echo "<a>" . $i . "</a>";
        } else {
            echo '<a href=' . $url_plugin . '&num_er=' . $i . '>' . $i . '</a>';
        }
        if ($i != $page_count)
            echo "|";
    }
    return true;
}

//Вывод всей инфы
function view_birthday_all() {
    global $wpdb, $table_name, $url_plugin, $er_asc, $er_desc, $name_options_sort;
    $prepare = 10;
    if (empty($_GET['num_er']) || ($_GET['num_er'] <= 0)) {
        $num_er = 1;
    } else {
        $num_er = (int) $_GET['num_er'];
    }

    echo '<div id="view_birthday_all">';
    echo "<form action=\"$url_plugin\" method=\"POST\">";
    //Некая защита для WP в формах
    wp_nonce_field('view_brthday_all_opt');
    //Количество записей в базе
    $count = count($wpdb->get_results("SELECT id FROM $table_name", ARRAY_A));
    //print_r($all_zap);
    $page_count = ceil($count / $prepare);
    if ($num_er > $page_count)
        $num_er = $page_count;
    $start_pos = ($num_er - 1) * $prepare;
    echo "<br><strong>";
    string_number($num_er, $page_count);
    echo "<br></strong>";
    //Для вывода 10 записей
    for ($i = 0; $i <= 10; $i++) {
        if (get_option($name_options_sort) == $er_desc)
            $result = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id DESC limit $start_pos,$prepare", ARRAY_A, $i);
        if (get_option($name_options_sort) == $er_asc)
            $result = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id ASC limit $start_pos,$prepare", ARRAY_A, $i);
        //Что бы не выводились пустые строки
        if ($result[fio] != NULL) {
            echo $result[id] . ") ";
            echo $result[fio] . "  ";
            echo $result[data_fio];
            echo "<input type=\"radio\" name=\"radio_info\" value=\"$result[id]\"/>";
            echo "</br>";
        }
    }

    echo "<input type=\"submit\" value=\"Показать\" name=\"redactor\"/>";
    echo "<input type=\"submit\" name=\"dr_actual\" value=\"Чей сегодня день?\"/>";
    echo "</form>";
    //Конец view_birthday_all
    echo '</div>';
}

//Диактивация плагина
function er_drop_table() {
    global $wpdb, $table_name, $name_options;
    if (get_option($name_options) == 1) {
        delete_option($name_options);
        delete_option('jal_db_version');
        $sql = "DROP TABLE $table_name";
        $wpdb->query($sql);
    } else {
        delete_option($name_options);
    }
}

////Создание меню в разделе Настройка
//function add_birthday_admin_pages() {
//    add_options_page('Блокнот Событий', 'Блокнот дней', '8', 'er_birthday', 'er_options_page');
//    
//}
//Создаем раздел меню (тест)
add_action('admin_menu', 'register_my_custom_menu_page');

//Добавляем раздел меню
function register_my_custom_menu_page() {
    add_menu_page('БлокнотWP', 'NotepadWP', '8', 'er_birthday', 'er_options_page', plugins_url('date-of-birth-notebook/images/iconka.png'));
}

//ПодМеню в разделе
//add_action('admin_menu', 'register_my_custom_submenu_page');
//
//function register_my_custom_submenu_page() {
//    add_submenu_page('er_birthday', 'Страница настроек', 'Settings', '8', 'er_birtday_sett', 'er_setting_birt');
//}
//
//function er_setting_birt() {
//    //Иконки настроек
//    echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
//    echo '<h2>Настройки NotepadWPe</h2>';
//    echo '</div>';
//    //Вторая страница
//    include_once 'er_birt_page2.php';
//}

//Отображение страницы плагина
function er_options_page() {
    global $url_plugin;
    echo '<div class="wrap"><div id="icon-edit-pages" class="icon32"></div>';
    echo "<h2>Блокнот</h2>";
    echo '</div>';
    echo "<p>Страница плагина: <a href='http://www.zixn.ru/229.html'>zixn.ru</a></p>";
    if (isset($_POST['redactor']) or isset($_POST['dr_actual']) or isset($_POST['delete']) or isset($_POST['update'])) {

        red_up_del();
    } else {

//Форма ввода данных пользователя
        echo "<form action=\"$url_plugin\" method=\"POST\" enctype=\"multipart/form-data\">";
        wp_nonce_field('view_brthday_all_opt');
        echo '<p> ФИО';
        echo '<input type="text" name="fio"/></p>';
        echo '<p> Дата';
        echo '<input type="text" name="data_fio"/>';
        echo '<em>Пример: 28.08.1987</em>';
        echo '</p>';
        echo '<p> Заметка<textarea cols="35" rows="5" name="zametka"></textarea></p>';
        echo '<input type="file" name="er_upfile">';
        echo '<input type="submit" value="Записать" name="zapisat"/>';
        echo '</form>';
    }
}

//Редактор, удалялка, апдейтор 
function red_up_del() {
    global $table_name, $url_plugin, $wpdb;
    //Нажата ли кнопка - Редактора
    //echo '<div id="er_updater">';
    if (isset($_POST['redactor'])) {
        $radio_info = trim($_POST['radio_info']);
        //echo $radio_info;
        //echo $radio_info;
        $select_info = "SELECT * FROM $table_name WHERE id=$radio_info";
        $v1 = $wpdb->get_row("SELECT * FROM $table_name WHERE id=$radio_info", ARRAY_A);
        // echo $v1[id] . " ";
        //echo $v1[fio] . " ";
        //echo $v1[data_fio] . " ";
        echo "<form action=\"$url_plugin\" method=\"POST\">";
        wp_nonce_field('view_brthday_all_opt');
        echo "<p><input type=\"text\" name=\"up_fio\" value=\"$v1[fio]\"/>ФИО</p>";
        echo "<p><input type=\"text\" name=\"up_data\" value=\"$v1[data_fio]\"/>Дата</p>";
        echo "<p><textarea cols=\"35\" rows=\"5\" name=\"up_zametka\">$v1[zametka]</textarea>Заметка</p>";
        echo "<input type=\"hidden\" name=\"up_id\" value=\"$v1[id]\"/>";
        echo "<input type=\"hidden\" name=\"up_image\" value=\"$v1[image_put]\"/>";
        echo "<input type=\"hidden\" name=\"up_image_orig\" value=\"$v1[image_puto]\"/>";
        // $image = '<p id="img_position"><a href="' . $url_content . $name_file . '" class="openbk" >Ориг.</a>' . '<img src="' . $url_content . $row['file'] . '"></p>';
        echo '<p id="img_position"><img src="' .$v1[image]. $row['file'] . '"></p>';
        echo '<p id="img_position"><a href="' .$v1[image_orig]. '" class="openbk" >Ориг.</a></p>';
        echo '<input type="submit" name="update" value="Обновить"/>';
        echo '<input type="submit" name="delete" value="Удалить"/>';
        echo '</form>';
    }
    //Обновляем запись
    if (isset($_POST['update'])) {
        $up_fio = $_POST['up_fio'];
        $up_data = $_POST['up_data'];
        $up_id = $_POST['up_id'];
        $up_zametka = $_POST['up_zametka'];
        //echo $up_data . "  " . $up_fio;
        //$update_query="UPDATE notepad_info SET fio=DjonPadla  WHERE id=19";
        $update_query = "UPDATE $table_name SET
        fio='$up_fio',
        data_fio='$up_data',
        zametka='$up_zametka'
            WHERE
                id='$up_id'
            ";
        $wpdb->query($update_query);
    }
    //Удаляем запись
    if (isset($_POST['delete'])) {
        $up_fio = $_POST['up_fio'];
        $up_data = $_POST['up_data'];
        $up_id = $_POST['up_id'];
        $up_zametka = $_POST['up_zametka'];
        $del_image=  trim($_POST['up_image']);
        $del_image_orig=trim($_POST['up_image_orig']);
        
        //echo $up_id;
//        $select_img="SELECT image FROM $table_name WHERE id='$up_id'";
//        $select_img_orig="SELECT image_orig FROM $table_name WHERE id='$up_id'";
        $delete_query = "DELETE FROM $table_name WHERE id='$up_id'";
        $wpdb->query($wpdb->prepare($delete_query));
        $select_img_a=$wpdb->get_row($select_img);
        $select_img_orig_a=$wpdb->get_row($select_img_orig);
//        echo $select_img_a."<br>";
//        echo $select_img_orig_a."<br>++++++++++++++++++++++++";
        
        if(!unlink($del_image))
            echo "Не могу удалить $del_image"."<br>";
        if(!unlink($del_image_orig))
            echo "Не могу удалить $del_image_orig";
        
    }
    //Проверяем у кого сегодня дни рождений
    if (isset($_POST['dr_actual'])) {
        $data_tek = trim(date("d.m."));
        $data_tek_yer = trim(date("d.m.o"));
        //echo $data_tek."<br>";
        $query_select_tec = "SELECT * FROM $table_name WHERE data_fio LIKE '$data_tek%'";
        if ($wpdb->get_row($query_select_tec)) {
            for ($j = 0; $j <= 5; $j++) {
                $result_day = $wpdb->get_row($query_select_tec, ARRAY_A, $j);
                if ($result_day[id] !== NULL) {
                    echo "id) " . $result_day[id] . " ";
                    echo $result_day[fio] . " - ";
                    echo $result_day[data_fio] . "<br>";
                }
            }
        } else {
            echo "Сегодня $data_tek_yer в базе нет информации о людях";
        }
    }
    //echo "</div>";
    //Конец view_birthday_all
    //echo '</div>';
}

//Настройки
function er_options() {
    global $url_plugin, $name_options, $name_options_sort, $er_desc, $er_asc;
    echo '<div id="er_options">';
    echo "<form action=\"$url_plugin\" method=\"POST\">";
    wp_nonce_field('view_brthday_all_opt');
    echo "<strong>Настройки плагина Блокнот Даты и Фамилии</strong>";
    $er_settings = array(
        'delete_base' => get_option($name_options),
        'sort_base' => get_option($name_options_sort),
    );



    if ($er_settings["delete_base"] == 0) {
        echo '<p><input type="checkbox" name="checkbox_opt_active" />Удалять записи при деактивации плагина</p>';
    } else {
        echo '<p><input type="checkbox" name="checkbox_opt_active" checked />Удалять записи при деактивации плагина</p>';
    }
    if ($er_settings["sort_base"] === "DESC") {
        echo '<p><input type="checkbox" name="checkbox_sort" checked />Сортировка записей в порядке убывания</p>';
    } else {
        echo '<p><input type="checkbox" name="checkbox_sort" />Сортировка записей в порядке убывания</p>';
    }


    echo '<input type="submit" name="eroptions_buton" value="Сохранить"/>';
    echo "</form>";
    if (isset($_POST['eroptions_buton'])) {
        if (isset($_POST['checkbox_opt_active'])) {
            //echo "Галочка стоит";
            update_option($name_options, 1);
        } else {
            //echo "Галочка НЕ стоит";
            update_option($name_options, 0);
        }
        if (isset($_POST['checkbox_sort'])) {
            //echo "Галочка стоит";
            update_option($name_options_sort, $er_desc);
        } else {
            //echo "Галочка НЕ стоит";

            update_option($name_options_sort, $er_asc);
        }
    }



    echo "<form action=\"$url_plugin\" method=\"POST\">";
    wp_nonce_field('view_brthday_all_opt');
    echo "<p>Внимание! Применять кнопку \"Очистить\" следует в случае полного избавления от плагина (Очистка базы от упоминаний
        о плагине)<br><input type=\"submit\" name=\"er_reset_base\" value=\"Очистить\"/></p>";
    if (isset($_POST['er_reset_base']))
        delete_option($name_options);


    echo '</div>';
}

//Загрузка изображений(Не использую)
function er_upload_form() {
    global $url_plugin, $patch_er_birt;
    ?>
    <div id="er_downlod_form">
        <form method="POST" action="<?php echo $url_plugin; ?>" enctype="multipart/form-data">
            <input type="file" name="er_upfile">
            <input type="submit" name="er_butzagruz" value="Загрузить">
        </form>
    </div>
    <?php
    //$uploaddir = wp_upload_dir();

    if (isset($_POST['er_butzagruz'])) {
        if ($_FILES) {


            $name_file = $_FILES['er_upfile']['name'];
            $tmp_file = $_FILES['er_upfile']['tmp_name'];
            $er_up = wp_upload_bits($name_file, null, file_get_contents("$tmp_file"));
            //var_dump($er_up);
            //print_r($er_up);
//            //Информация о загруженном файле1111
//            echo "Информация о загруженном файле <br>";
//            echo "Имя: " . $name_file . "</br>";
//            echo "Размер: " . $_FILES['er_upfile']['size'] . " КБ</br>";
//            echo "Временное имя: " . $tmp_file . "</br>";
        }
    }

    return $er_up['url'];
}

if (strpos($_SERVER['REQUEST_URI'], $url) == TRUE) {
    //er_upload_form();
}

//Блок стилей
function er_notepad_css() {
    echo '
<style type="text/css">
<!--
#but_redactor {
    position: relative;


}
#er_options {
position: absolute;
left: 50%;
top: 80px;
z-index: 1;
}

#view_birthday_all {
position: absolute;
left: 14%;
top: 360px;
z-index: 1;
}
#er_warning {
position: absolute;
left: 30%;
top: 100px;
color: red;
}
#pustoe_pole {
    position: relative;
   color: red;
}
#er_updater {

}
#er_downlod_form {
position: absolute;
top: 330px;
left: 220px;
z-index: 2;
}
#img_position{
position: absolute;
top: 90px;
left: 400px;
}
-->
</style>    
';
}

//Опции
//Активируем вывод всех записей, а так же редактирование
if (strpos($_SERVER['REQUEST_URI'], $url) == TRUE) {

    add_action('admin_head', 'view_birthday_all');
    // add_action('admin_head', 'red_up_del');
    add_action('wp_print_scripts', 'er_options');
    add_action('admin_head', 'fancebook_er');
    //Активация добавления данных
    add_action('admin_head', 'post_data_fio');
    //Формы так активировать НЕЛЬЗЯ
    //add_action('admin_head', 'er_upload_form');
//Активация стилей
    add_action('admin_head', 'er_notepad_css');
}

//Активация меню

add_action('admin_menu', 'add_birthday_admin_pages');
?>

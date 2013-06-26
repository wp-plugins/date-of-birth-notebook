<?php

/*
  Plugin Name: Date of birth notebook
  Plugin URI: http://www.zixn.ru/229.html
  Description: Plug-in notepad or notebook, keep records of the names and dates, birthdays shows. Easy and simple.
  Version: 1.0
  Author: Ermak
  Author URI: http://zixn.ru
 */
/*  Copyright 2013  Ermak  (email: Ermak_not@mail.ru)

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
//Активация плагина и создание таблиц
register_activation_hook(__FILE__, 'er_create_table');
//Удаляем настройки при диактивации плагина
register_deactivation_hook(__FILE__, 'er_drop_table');
//Создание таблицы плагина в mysql
global $er_db_version, $table_name;
$er_db_version = "1.0";
//Название таблицы
$table_name = $wpdb->prefix . "erbirthday";
//Адрес плагина
$url = "er_birthday";
//Время дла базы
$url_plugin = "$_SERVER[SCRIPT_NAME]?page=er_birthday";
//Опция в базе ВордПресс
$name_options="er_birthday_delete_db";

function er_create_table() {
    global $wpdb;
    global $er_db_version;
add_option($name_options,'0','','yes');
    $table_name = $wpdb->prefix . "erbirthday";
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {

        $sql = "CREATE TABLE " . $table_name . " (
	id INT PRIMARY KEY AUTO_INCREMENT,
        fio VARCHAR(60),
        data_fio TEXT
	);";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        //$welcome_text="Текст";
        //$rows_affected = $wpdb->insert($table_name, array('time' => current_time('mysql'), 'text' => $welcome_text));

        add_option("er_db_version", $er_db_version);
    }
}

//Запись данных в таблицу
function insert_table($fio, $data) {
    global $wpdb, $table_name;
    $query_insert = $wpdb->prepare("INSERT INTO $table_name(fio,data_fio)
            VALUES('$fio','$data')");
    $wpdb->query($query_insert);
}

//Ловим ПОСТ из формы и отправляем их в "запись данных"
function post_data_fio() {
    $pravilo_date = "/[0-3]+[0-9]+\.[0-1]+[0-9]+\.[0-2]+[0-9]+[0-9]+[0-9]+/";
//Нажата ли кнопка - запись
    if (isset($_POST['zapisat'])) {
        $fio = trim($_POST['fio']);
        $data = trim($_POST['data_fio']);
        if ($fio !== "" and $data !== "") {
            if (preg_match_all($pravilo_date, $data, $result_data)) {
                $data = $result_data[0][0];
                insert_table($fio, $data);
            } else {

                echo '<div id="er_warning">Вы ввели не верную дату!</div>';
            }
        } else {
            echo '<div id="er_warning">Одно или оба поля не заполнены!</div>';
        }
    }
}

//Вывод всей инфы
function view_birthday_all() {
    global $wpdb, $table_name, $url_plugin;
    //Проверка по адресу страницы
    //Выводим информацию из таблицы
    //$select = "SELECT * FROM $table_name, ARRAY_A";
    //$res=$wpdb->get_row($select, ARRAY_A);
    //foreach ($wpdb->get_row($select, ARRAY_A) as $k => $v) {
    echo '<div id="view_birthday_all">';
    echo "<form action=\"$url_plugin\" method=\"POST\">";
    //Некая защита для WP в формах
    wp_nonce_field('view_brthday_all_opt');
    //Для вывода 10 записей
    for ($i = 0; $i <= 10; $i++) {
        $result = $wpdb->get_row("SELECT * FROM $table_name", ARRAY_A, $i);
        //Что бы не выводились пустые строки
        if ($result[fio] != NULL) {
            echo $result[id] . ") ";
            echo $result[fio] . "  ";
            echo $result[data_fio];
            echo "<input type=\"radio\" name=\"radio_info\" value=\"$result[id]\"/>";
            echo "</br>";
        }
    }

    echo "<input type=\"submit\" value=\"Правка\" name=\"redactor\"/>";
    echo "<input type=\"submit\" name=\"dr_actual\" value=\"Чей сегодня день?\"/>";
    echo "</form>";
}

//Показывае ошибки базы
//Диактивация плагина
function er_drop_table() {
    global $wpdb, $table_name,$name_options;
    if(get_option($name_options)==1) {
        delete_option($name_options);
    delete_option('jal_db_version');
    $sql = "DROP TABLE $table_name";
    $wpdb->query($sql);
    } else {
        delete_option($name_options);
    }
    
}

//Создание меню в разделе Настройка
function add_birthday_admin_pages() {
    add_options_page('Блокнот Событий', 'Блокнот дней', '8', 'er_birthday', 'er_options_page');
}

//Отображение страницы плагина
function er_options_page() {
    global $url_plugin;
    echo "<h2>Блокнот</h2>";
    echo "<p>Автор плагина: <a href='http://www.zixn.ru/229.html'>zixn.ru</a></p>";
//Форма ввода данных пользователя
    echo "<form action=\"$url_plugin\" method=\"POST\">";
    wp_nonce_field('view_brthday_all_opt');
    echo '<p> ФИО';
    echo '<input type="text" name="fio"/></p>';
    echo '<p> Дата';
    echo '<input type="text" name="data_fio"/>';
    echo '<em>Пример: 28.08.1987</em>';
    echo '</p>';
    echo '<input type="submit" value="Записать" name="zapisat"/>';
    echo '</form>';
}

//Редактор, удалялка, апдейтор 
function red_up_del() {
    global $table_name, $url_plugin, $wpdb;
    //Нажата ли кнопка - Редактора
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
        echo "<input type=\"hidden\" name=\"up_id\" value=\"$v1[id]\"/>";
        echo '<input type="submit" name="update" value="Обновить"/>';
        echo '<input type="submit" name="delete" value="Удалить"/>';
        echo '</form>';
    }
    //Обновляем запись
    if (isset($_POST['update'])) {
        $up_fio = $_POST['up_fio'];
        $up_data = $_POST['up_data'];
        $up_id = $_POST['up_id'];
        //echo $up_data . "  " . $up_fio;
        //$update_query="UPDATE notepad_info SET fio=DjonPadla  WHERE id=19";
        $update_query = "UPDATE $table_name SET
        fio='$up_fio',
        data_fio='$up_data'
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
        //echo $up_id;
        $delete_query = "DELETE FROM $table_name WHERE id='$up_id'";
        $wpdb->query($wpdb->prepare($delete_query));
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
    //Конец view_birthday_all
    echo '</div>';
}

//Настройки
function er_options() {
    global $url_plugin,$name_options;
    echo '<div id="er_options">';
    echo "<form action=\"$url_plugin\" method=\"POST\">";
    wp_nonce_field('view_brthday_all_opt');
    echo "<strong>Настройки плагина Блокнот Даты и Фамилии</strong>";
    
    if(get_option($name_options)==0) {
    echo '<p><input type="checkbox" name="checkbox_opt_active" />Удалять записи при деактивации плагина</p>';
    }
    else {
        echo '<p><input type="checkbox" name="checkbox_opt_active" checked />Удалять записи при деактивации плагина</p>';
    }
    echo '<input type="submit" name="eroptions_buton" value="Сохранить"/>';
    echo "</form>";
    if(isset($_POST['eroptions_buton'])) {
        if(isset($_POST['checkbox_opt_active'])){
            echo "Галочка стоит";
            update_option($name_options,1);
        } else {
            echo "Галочка НЕ стоит";
            update_option($name_options,0);
            
        }
    }
    
    
    echo "<form action=\"$url_plugin\" method=\"POST\">";
    wp_nonce_field('view_brthday_all_opt');
    echo "<p>Внимание! Применять кнопку \"Очистить\" следует в случае полного избавления от плагина (Очистка базы от упоминаний
        о плагине)<br><input type=\"submit\" name=\"er_reset_base\" value=\"Очистить\"/></p>";
    if(isset($_POST['er_reset_base'])) 
        delete_option($name_options);
    
    
    echo '</div>';
}

//Блок стилей
function djo_css() {
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
top: 230px;
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
#logon {
position: absolute;
left: 18%;
top: 0%; 
z-index: -1;
}
-->
</style>    
';
}
//Опции




//Активируем вывод всех записей, а так же редактирование
if (strpos($_SERVER['REQUEST_URI'], $url) == TRUE) {

    add_action('admin_head', 'view_birthday_all');
    add_action('admin_head', 'red_up_del');
    add_action('admin_head', 'er_options');
    //Активация добавления данных
    add_action('admin_head', 'post_data_fio');
//Активация стилей
    add_action('admin_head', 'djo_css');
}

//Активация меню

        add_action('admin_menu', 'add_birthday_admin_pages');

?>
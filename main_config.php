<?php
set_time_limit(0);
require_once 'class/core_lib.php';

// Данные к БД
$mysql_conf = array
				('host'      => 'localhost',
				'user'      => 'root',
				'pass'      => '',
				'db'      => 'xrumer',
				'port'      => 3306);

// Основные параметры
$password = 'password124'; //ваш пароль от зареганых акков в проекте
$xrumer_report = 'E:\Xrumer\Xrum19\Logs\pbn_sites\Posting.2020.01\Success.txt'; // Указываем путь фаила "Успешные" от Xrumer
$xrumer_new_report = './temp.txt.bak'; // не трогать.
$str_url = implode("	", file('./check_url.txt', FILE_IGNORE_NEW_LINES)); // фаил с ссылками которые будем искать

$threads = '1'; // потоков (больше 6 потоков не советую ставить)
$threads_timeout = 8000; // Максимальное время ответа сайта в мс.

//Яндекс ИКС
$key = '12b01b30186dccae6e2e54486d4d3968'; // если xevil на внешнем адресе, создаем уникальный key
$xrumer_host = '127.0.0.1:80'; // Адрес Xevil , по умолчанию 127.0.0.1:80 (Также в настройках Xevil укажите Anigate)

$thread = '1'; // потоки на решение иконок Яндекс Икс
$limit = '100';

$db = new safeMysql($mysql_conf);
$mcurl = new mcurl();


?>




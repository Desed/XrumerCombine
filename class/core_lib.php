<?php
require_once 'simple_html_dom_lib.php'; // библиотека для парсинга
require_once 'xevil_captcha_lib.php'; // для решения капчи
require_once 'safemysql.class.php'; // БД
require_once 'other_function.php'; // прочее
include 'main_config.php'; // конфиг

function multi_curl($data) {

	global $threads, $threads_timeout;
	
		$multi = curl_multi_init();
		$channels = array();
	
		foreach ($data as &$data_url) {
			
		$url = $data_url['url'];
		mysql_create_string($data_url);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_ENCODING, "gzip");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $threads*($threads_timeout/1000)); 
			curl_setopt($ch, CURLOPT_TIMEOUT_MS, $threads*$threads_timeout); //timeout in seconds
			curl_multi_add_handle($multi, $ch);
			$channels[$url] = $ch;
		}

		$active = null;
		do {
			$mrc = curl_multi_exec($multi, $active);
		} while ($active > 0);
		
		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($multi) == -1) {
				continue;
			}
		
			do {
				$mrc = curl_multi_exec($multi, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
		

		
		foreach ($channels as $url => $channel) {
		$html = new simple_html_dom();	//создаем объект
		$out = curl_multi_getcontent($channel); //получаем контент
		$html->load($out);	//помещаем наш контент
		$result .= collection_html($html, $url);
		curl_multi_remove_handle($multi, $channel); //кикаем поток
		}
		return $result;
		unset($channel, $url);
		curl_multi_close($multi);
}


function collection_html($html, $url) {
	link_processing($html, $url);
}


function link_processing($html, $url) {
	
	$hrefsRel = extract_hrefsRel($html, $url); // получаем массив с атрибутами ссылок
		$rel_type = sorting_hrefArr_follow($hrefsRel);
		
	$xrumer_data = array();	
		
	$lang = extract_lang($html); // узнаем язык сайта
	$title = extract_title($html); // получаем title страницы
	$h1 = extract_h1($html); // получаем первый h1 страницы
	$domain_info = domain_info($url); // узнаем уровень домена

			$global_array = [
								"url" => $url,
								"url_host" => $domain_info['url_host'],
								"domain_level" => $domain_info['domain_level'],
								"lang" => $lang,
								"title" => $title,
								"h1" => $h1,
								"href_rel" => $rel_type
								];
			
			mysql_update_processing_data($global_array);
			mysql_update_yaX();

}


function mysql_update_processing_data($global_array) {
	global $db;
	
$db->query("UPDATE `xrumer_db` SET `url_host`=?s, `domain_level`=?i, `lang`=?s, `title`=?s, `h1`=?s, `href_rel`=?s WHERE (`url`=?s);",
		$global_array['url_host'],
		$global_array['domain_level'], 
		$global_array['lang'],
		$global_array['title'], 
		$global_array['h1'],
		$global_array['href_rel'],
		$global_array['url']);
		
}



function mysql_create_string($xrumer_report_data) {
	global $db;
	
	$db->query("INSERT INTO `xrumer_db` (`url`, `recaptcha`, `user_login`, `user_password`, `date`) VALUES (?s,?i,?s,?s, NOW())",
		$xrumer_report_data['url'],
		$xrumer_report_data['recaptcha'],
		$xrumer_report_data['login'],
		$xrumer_report_data['password']);
}

function mysql_update_yaX() {
	global $db;
	
	$get_list_db = $db->getCol("SELECT `url_host` FROM `xrumer_db` where `domain_level` = 2 and `ya_x` is null");
	
	if ($get_list_db) {
			$captcha_answer_arr = yandex_x_urls($get_list_db);
	
		foreach ($captcha_answer_arr as $answer) {
			$url = str_replace(array("https://yandex.ru/cycounter?", "&theme=light&lang=ru"), array("", ""), $answer['source']);
			
			if ($answer['answer'] <= 10) {
				$answer['answer'] = 0;
			}
			
			$db->query("UPDATE `xrumer_db` SET `ya_x` = ?i where `url_host` = ?s and `ya_x` is null", $answer['answer'], $url);
		}
	}
	
}

function yandex_x_urls($array_url) {
	$yandex_img_array = [];
	foreach ($array_url as $url) {
		$yandex_img_array[] = "https://yandex.ru/cycounter?{$url}&theme=light&lang=ru";
	}
	return captcha_decision($yandex_img_array);
}

function domain_info($url) {
	$url_host = parse_url($url, PHP_URL_HOST);
	$array_url_host = explode(".", $url_host);
	
	if ($array_url_host[0] == 'www') {
		$level_count = count($array_url_host)-1;
	} else {
		$level_count = count($array_url_host);
	}
	
	$domain_info = ["url_host" => $url_host,
					"domain_level" => $level_count
	];

	return $domain_info;
}


function sorting_hrefArr_follow($hrefsRel) {
	global $str_url;
		
	$rel_type = [];
		foreach ($hrefsRel as &$href_link) {
			if (stristr($str_url, $href_link['href']))
				if (stripos($href_link['rel'], 'nofollow')) {
					$rel_type = ['nofollow'];
				} else {
					$rel_type = ['dofollow'];
				}
				
			if ($rel_type == null) {
				$rel_type = ['no_links'];
			}
		}
		
		return $rel_type['0'];
	}
 
function extract_hrefsRel($html, $url) {

	$rel_check_array = []; // создаем пустой массив который мы заполним нужными ссылками
		foreach ($html->find('a') as &$article) { // находим все ссылки на странице
			$attr_array = $article->attr; //получаем атрибуты ссылок
			$href =	parse_url($attr_array['href'], PHP_URL_HOST); //убираем все лишнее у ссылок
				if (iconv_strlen($href) > 4 && parse_url($url, PHP_URL_HOST) != $href) { //ссылка должна быть не короче 4х символов, исключаем ссылки которые принадлежат сайту
					$rel_check_array[] = [
									"href" => $href,
									"rel" => $attr_array['rel']
									];
				}
		}

		return $rel_check_array;
	
}


function extract_lang($html) {

	$attr_lang = null; // создаем пустой массив который мы заполним нужными ссылками
		foreach ($html->find('html') as &$article) { // находим все ссылки на странице
			$attr_lang = $article->lang; //получаем атрибуты ссылок
		}
	
	return $attr_lang;
}

function extract_h1($html) {

	$h1_title = []; // создаем пустой массив который мы заполним нужными ссылками
		foreach ($html->find('h1') as &$article) { // находим все ссылки на странице
			$h1_title[] = $article->plaintext; //получаем атрибуты ссылок
		}
	$h1_title =	trim_tabs($h1_title['0']);

	return $h1_title;
}

function extract_title($html) {

	$title = []; // создаем пустой массив который мы заполним нужными ссылками
		foreach ($html->find('title') as &$article) { // находим все ссылки на странице
			$title[] = $article->plaintext; //получаем атрибуты ссылок
		}
	$title = trim_tabs($title['0']);

	return $title;
}


function mysql_trash_badLinks() {
	global $db;
	$db->query("DELETE FROM `xrumer_db` where href_rel is null");
}

function xrumer_report_processing() {
	global $password, $xrumer_report, $xrumer_new_report, $db;
	

		if (file_exists($xrumer_new_report)) {
			$count_new_report = xrumer_report_countString($xrumer_new_report)-1;
		} else {
			file_put_contents($xrumer_new_report,"");
			$count_new_report = 0;
		}

		$count_xrumer_report = xrumer_report_countString($xrumer_report);
		
	$sum_count_file_new = $db->getOne("SELECT sum(count_file_new) as sum_count_file_new FROM `xrumer_log`");
	$last_count = $db->getOne("SELECT count_file as sum_count_file_new FROM `xrumer_log` where `guid` = (SELECT MAX(guid) FROM `xrumer_log`)");


	if  ($count_xrumer_report > $sum_count_file_new) {
		$count_cut_string = $count_xrumer_report - $sum_count_file_new;
		
		delete_fileInCheck($xrumer_new_report);
		xrumer_report_copy($xrumer_report, $xrumer_new_report);
		$content_cut = xrumer_read_reportAfterString($xrumer_new_report, $sum_count_file_new);

		file_put_contents($xrumer_new_report, $content_cut);
		$db->query("INSERT INTO `xrumer_log` (`count_file`, `count_file_new`, `date`) VALUES ('{$count_xrumer_report}', '{$count_cut_string}', NOW())");
	}
	
	if ($count_xrumer_report == $sum_count_file_new) {
		file_put_contents($xrumer_new_report,"");
	}
	
		return read_file2($xrumer_new_report, $password);
}

function threads_data($file) {
	global $threads;

	$data = array_diff($file, array(''));
	$data = array_chunk($data, $threads, true);
	//$count_data = round((count($file)/$threads), 0);
	$count_data = count($file);
	$counter = 0;

	foreach ($data as $key => $val) {
		$counter++;
		$start = microtime(true);
		$position = round($counter*($threads), 0);
		$result = multi_curl($val);
$time = microtime(true) - $start;
	printf('%.4F sec. Progress: ' .$position. '/' .$count_data .PHP_EOL, $time);
		}
}

for ($i = 1; ; $i++) {
	$report_file = xrumer_report_processing();
	if ($report_file) {
	$count_new = count($report_file);
	mysql_trash_badLinks();
		echo "-- Create a new file count:{$count_new} report links --" .PHP_EOL;
	} else {
		echo '-- Waiting for new data --' .PHP_EOL;
	}
	threads_data($report_file);
	sleep(3);
}


?>




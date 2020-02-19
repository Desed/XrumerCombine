<?php

	 function trim_tabs($str) {
		$text = hex2bin(str_replace('c2a0', '20', bin2hex($str)));
		while( strpos($text,'  ')!==false){
			$text = str_replace("  ", " ", $text);
		}
		$text = html_entity_decode(trim($text));
		$text = strip_tags($text);
		return mysql_escape_mimic($text);
	}
	
	
	 function mysql_escape_mimic($inp) {
		if(is_array($inp))
			return array_map(__METHOD__, $inp);
	
		if(!empty($inp) && is_string($inp)) {
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
		}
	
		return $inp;
	}
	
	 function read_file($file) {
	$file_url = file($file, FILE_IGNORE_NEW_LINES); // фаил с ссылками или отчетом	
	
		foreach ($file_url as &$url) {
		preg_match("/(.*?) /", $url, $parse_url);
		if ($parse_url[1] == null) {
			$urls .= $url.PHP_EOL;
		} else {
			$urls .= $parse_url[0].PHP_EOL;
		}
			}
		$files = explode(PHP_EOL, $urls);
		
	return $files;
	}
	
	function read_file2($file, $password) {

		$file_url = file($file, FILE_IGNORE_NEW_LINES); // фаил с ссылками или отчетом	
		$xrumer_log_data = [];
			foreach ($file_url as &$url) {
				$data = log_passParser($url, $password);
				$xrumer_log_data[$data['url']] = $data;
			}
			
		return $xrumer_log_data;
	}
	
	function log_passParser($url,$pass) {
		
		preg_match("/(.*?)Result:(.*)использован никнейм \"(.*?)\"/", $url, $parse_url);
	
		if(stristr($url, 'ReCaptcha2')) {
			$recaptcha = '1';
		} else {
			$recaptcha = '0';
		}
		
		$xrumer_complete = [
						"url" => xrumer_trash_word($parse_url[1]),
						"login" => xrumer_trash_word($parse_url[3]),
						"password" => xrumer_trash_word($pass),
						"recaptcha" => xrumer_trash_word($recaptcha)
						];
			
		return $xrumer_complete;
	
	}
	
	function xrumer_trash_word($text) {
			$text = preg_replace('#[ Активация ]#', ' ', $text);
			$text = preg_replace('#\[#', '', $text);
			$text = preg_replace('#\]#', '', $text);
			$text = preg_replace('#      #', ' ', $text);
			return trim_tabs($text);
	}

	function readTheFile($path) {
		$lines = [];
		$handle = fopen($path, "r");
	
		while(!feof($handle)) {
			$lines[] = trim(fgets($handle));
		}
	
		fclose($handle);
		return $lines;
	}
	
	
	function xrumer_report_countString($report) {
		return count(readTheFile($report));
	}

	function xrumer_read_reportAfterString($report, $string_n) {
			$content = readTheFile($report);
			
			for($i = 0; $i < count($content); ++$i) {
				if ($i >= $string_n) {
					$content_cut .= $content[$i] .PHP_EOL;
				}
			}
			
			return $content_cut;
	}
 

	function xrumer_report_copy($xrumer_report, $xrumer_new_report)
	{
		if (file_exists($xrumer_new_report) == false) {
			copy($xrumer_report, $xrumer_new_report);
			
		$content = file_get_contents($xrumer_report);
		$content_utf8 = iconv('CP1251', 'UTF-8', $content);
		file_put_contents($xrumer_new_report, $content_utf8);
		}
	}
	
	function delete_fileInCheck($file) {
		if (file_exists($file)) {
			unlink($file);
		}
	}
?>

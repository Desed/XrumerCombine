<?php
class mcurl {

    var $timeout = 20; // максимальное время загрузки страницы в секундах  
    var $threads = 10; // количество потоков   

    var $all_useragents = array(  
    "Opera/9.23 (Windows NT 5.1; U; ru)",  
    "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.4;MEGAUPLOAD 1.0",  
    "Mozilla/5.0 (Windows; U; Windows NT 5.1; Alexa Toolbar; MEGAUPLOAD 2.0; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7;MEGAUPLOAD 1.0",  
    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",  
    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",  
    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",  
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; Maxthon; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; Media Center PC 5.0; InfoPath.1)",  
    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",  
    "Opera/9.10 (Windows NT 5.1; U; ru)",  
    "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1; aggregator:Tailrank; http://tailrank.com/robot) Gecko/20021130",  
    "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",  
    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",  
    "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",  
    "Opera/9.22 (Windows NT 6.0; U; ru)",  
    "Opera/9.22 (Windows NT 6.0; U; ru)",  
    "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",  
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",  
    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; MRSPUTNIK 1, 8, 0, 17 HW; MRA 4.10 (build 01952); .NET CLR 1.1.4322; .NET CLR 2.0.50727)",  
    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)",  
    "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9"  
    );  

    function multiget($urls, &$result)  
    {  
        $threads = $this->threads;  
        $useragent = $this->all_useragents[array_rand($this->all_useragents)];  

        $i = 0;  
        for($i=0;$i<count($urls);$i=$i+$threads)  
        {  
            $urls_pack[] = array_slice($urls, $i, $threads);  
        }  
        foreach($urls_pack as $pack)  
        {  
            $mh = curl_multi_init(); unset($conn);  
            foreach ($pack as $i => $url)  
            {  
                $conn[$i]=curl_init(trim($url));  
                curl_setopt($conn[$i],CURLOPT_RETURNTRANSFER, 1);  
                curl_setopt($conn[$i],CURLOPT_TIMEOUT, $this->timeout);  
                curl_setopt($conn[$i],CURLOPT_USERAGENT, $useragent);  
                curl_multi_add_handle ($mh,$conn[$i]);  
            }  
            do { $n=curl_multi_exec($mh,$active); usleep(100); } while ($active);  
            foreach ($pack as $i => $url)  
            {  
                $result[]=curl_multi_getcontent($conn[$i]);  
                curl_close($conn[$i]);  
            }  
            curl_multi_close($mh);  
        }  

    } 
    
    function multipost($urls, $post = array(), &$result)  
    {  
        $threads = $this->threads;  
        $useragent = $this->all_useragents[array_rand($this->all_useragents)];  

        $mh = curl_multi_init(); unset($conn);  
        foreach ($urls as $i => $url)  
        {  
            $conn[$i]=curl_init(trim($url));  
            curl_setopt($conn[$i],CURLOPT_RETURNTRANSFER, 1);  
            curl_setopt($conn[$i],CURLOPT_TIMEOUT, $this->timeout);  
            curl_setopt($conn[$i],CURLOPT_USERAGENT, $useragent);  
            if(!empty($post[$i])){
                curl_setopt($conn[$i], CURLOPT_POST, true);
                curl_setopt($conn[$i], CURLOPT_POSTFIELDS, http_build_query($post[$i]));                
            }
            curl_multi_add_handle ($mh,$conn[$i]);  
        }  
        do { $n=curl_multi_exec($mh,$active); usleep(100); } while ($active);  
        foreach ($urls as $i => $url)  
        {  
            $result[$i]=curl_multi_getcontent($conn[$i]);  
            curl_close($conn[$i]);  
        }  
        curl_multi_close($mh);  
    }    
}

function curl_in($img) {
	$ch = curl_init($img);

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)');
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	return curl_exec($ch);

	curl_close ($ch);
}

function captcha_decision($files) {
	global $key, $thread, $limit, $mcurl, $xrumer_host;
	
	$answer_xevil = [];
	
	if($thread > $limit){
		$thread = $limit;
	}
	
	$captcha_ids = array();
	$new_captcha_ids = array();
	do{
		
		// Капчи в работе
		if(!empty($captcha_ids)){
			foreach($captcha_ids as $captchaid => $value){
				$answer = curl_in("http://{$xrumer_host}/res.php?action=get&id=$captchaid&key=$key");
				if(substr_count($answer, 'OK')> 0 || substr_count($answer, 'ERROR')> 0){
					$new_captcha_ids[$captchaid] = $value;
					$new_captcha_ids[$captchaid]['answer'] = $answer;
					$new_captcha_ids[$captchaid]['date_end'] = (float)microtime(true);
					unset($captcha_ids[$captchaid]);
				}
			}
		}
		
		if(count($captcha_ids) < $limit && !empty($files)){
			$urls = array();
			$post = array();
			$mcurl->threads = $limit - count($captcha_ids);  
			if($mcurl->threads > $thread){
				$mcurl->threads = $thread;
			}
			$mcurl->timeout = 20;    
			
			$files_slice = array_slice($files, 0, $mcurl->threads); 
			foreach($files_slice as $_key => $img){        
				$post[$_key]['method'] = 'base64';
				$post[$_key]['key'] = $key;
				$type = pathinfo($img, PATHINFO_EXTENSION);
				$data_image = curl_in($img);
				$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data_image);
				$post[$_key]['body'] = $base64;
				$urls[$_key] = "http://{$xrumer_host}/in.php";
			}
			array_splice($files, 0, (count($files) - $mcurl->threads) * -1);
			if((count($files) - $mcurl->threads) <= 0){
				unset($files);
			}
			
			unset($result);    
			$mcurl->multipost($urls, $post, $result);
			foreach($result as $_key => $id){
				if(substr_count($id, 'OK') > 0){
					$id = substr(strrchr($id, '|'), 1);
					$captcha_ids[$id] = array(
						'img' => $files_slice[$_key],
						'status' => 'OK'
					);
				}
			}      
		}
	
		// Ответ дан, записываем
		if(!empty($new_captcha_ids)){
			foreach($new_captcha_ids as $captchaid => $value){
					$answer_xevil[] = array(
						'source' => $value['img'],
						'status' => $value['status'],
						'answer' => str_replace('OK|', '', $value['answer']),
						'speed' => round(((float)microtime(true) - $value['date_end']), 3)
					);
			unset($new_captcha_ids[$captchaid]);
			}
		}  

		
		usleep(500000);
		
		if(empty($files) && empty($new_captcha_ids) && empty($captcha_ids)){
			return $answer_xevil;
			break;
		}
	}while(true);
		
	die('END');
}

?>

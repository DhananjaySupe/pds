<?php

	if (!function_exists('root_url')) {
		function root_url($uri = "")
		{
			$url_parts = parse_url(base_url());
			return $url_parts['scheme'] . '://' . $url_parts['host'] . (isset($url_parts['port']) ? ':' . $url_parts['port'] : '') . '/' . ltrim($uri, '/');
		}
	}

	if (!function_exists('app_config')) {
		function app_config($key = '')
		{
			$AppConfig = new \Config\AppConfig();
			return isset($AppConfig->$key) ? $AppConfig->$key : '';
		}
	}

	if (!function_exists('site_title')) {
		function site_title($title = '')
		{
			$AppConfig = new \Config\AppConfig();
			if ($AppConfig->appName) {
				$title = empty($title) ? $AppConfig->appName : $title . ' | ' . $AppConfig->appName;
			}
			return $title;
		}
	}

	if (!function_exists('body_selectors')) {
		function body_selectors($class = '', $id = '')
		{
			$selectors = '';
			if (!empty($id)) {
				$selectors = ' id="' . (is_array($id) ? implode(' ', $id) : $id) . '"';
			}
			if (!empty($class)) {
				$selectors = ' class="' . (is_array($class) ? implode(' ', $class) : $class) . '"';
			}
			return $selectors;
		}
	}

	if (!function_exists('site_styles')) {
		function site_styles($css = array())
		{
			$AppConfig = new \Config\AppConfig();
			$styles = $AppConfig->defaultCSS;
			if ($css && count($css) > 0) {
				array_splice($styles, count($styles) - 1, 0, $css);
			}
			$sitestyles = '';
			if ($styles && count($styles) > 0) {
				foreach ($styles as $key => $item) {
					$itemprop = '';
					if (is_array($item)) {
						$itemprop = isset($item[1]) ? $item[1] : '';
						$item = $item[0];
					}
					$parsed = parse_url($item);
					if (empty($parsed['scheme'])) {
						$item = site_url($item);
					}
					$sitestyles .= "\t<link href='{$item}' rel='stylesheet'" . (!empty($itemprop) ? ' ' . $itemprop : '') . " />\n";
				}
			}
			return $sitestyles;
		}
	}

	if (!function_exists('site_scripts')) {
		function site_scripts($js = array())
		{
			$AppConfig = new \Config\AppConfig();
			$scripts = $AppConfig->defaultJS;
			if ($js && count($js) > 0) {
				array_splice($scripts, count($scripts) - 1, 0, $js);
			}
			$sitescripts = '';
			if ($scripts && count($scripts) > 0) {
				foreach ($scripts as $key => $item) {
					$itemprop = '';
					if (is_array($item)) {
						$itemprop = isset($item[1]) ? $item[1] : '';
						$item = $item[0];
					}
					$parsed = parse_url($item);
					if (empty($parsed['scheme'])) {
						$item = site_url($item);
					}
					$sitescripts .= "\t<script src='{$item}'" . (!empty($itemprop) ? ' ' . $itemprop : '') . "></script>\n";
				}
			}
			return $sitescripts;
		}
	}

	if (!function_exists('site_meta')) {
		function site_meta($metatags = array())
		{
			$AppConfig = new \Config\AppConfig();
			$defaultmeta = $AppConfig->defaultMETA;
			if ($defaultmeta && count($defaultmeta) > 0) {
				foreach ($defaultmeta as $item) {
					$metatags[] = $item;
				}
			}
			$sitemeta = '';
			if ($metatags && count($metatags) > 0) {
				foreach ($metatags as $key => $item) {
					$sitemeta .= "\t" . $item . "\n";
				}
			}
			return $sitemeta;
		}
	}

	if (!function_exists('activate_menu')) {
		function activate_menu($name = '', $class = false)
		{
			$request = \Config\Services::request();
			$activepage = $request->uri->getSegment(1);
			return ($activepage == $name) ? ($class ? ' active' : ' class="active"') : '';
		}
	}

	if (!function_exists('activate_account_menu')) {
		function activate_account_menu($name = '')
		{
			$request = \Config\Services::request();
			$path = $request->uri->getPath();
			$pos = strpos($path, $name);
			return ($pos !== false) ? ' active' : '';
		}
	}

	if (!function_exists('alert_message')) {
		function alert_message($message = array(), $type = "success")
		{
			$alert = '';
			if (!is_array($message) && !empty($message)) {
				$alert = '<div class="alert alert-' . $type . ' fade in"><button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button><p>' . $message . '</p></div>';
				} else {
				if (count($message) > 0) {
					$alert = '<div class="alert alert-' . $type . ' fade in"><button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>';
					if (count($message) > 1) {
						$alert .= '<ul>';
						foreach ($message as $k => $msg) {
							$alert .= '<li>' . $msg . '</li>';
						}
						$alert .= '</ul>';
						} else {
						$alert .= '<p>' . $message[0] . '</p>';
					}
					$alert .= "</div>";
				}
			}
			return $alert;
		}
	}

	if (!function_exists('fullname')) {
		function fullname($firstname = "", $lastname = "")
		{
			return trim($firstname . ' ' . $lastname);
		}
	}

	if (!function_exists('slugify')) {
		function slugify($string)
		{
			return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
		}
	}
	if (!function_exists('previousUrl')) {
		function previousUrl($url='')
		{
			if(empty($url)){
				if(isset($_SERVER['HTTP_REFERER'])){
					$referer = filter_var($_SERVER['HTTP_REFERER'], FILTER_VALIDATE_URL);
					if (!empty($referer)) {
						$url = $referer;
						} else {
						$url ="javascript:history.go(-1)";
					}
					} else {
					$url ="javascript:history.go(-1)";
				}
			}
			return $url;
		}
	}
	if (!function_exists('paging')) {
		function paging($page = 1, $records = 0, $length = 25)
		{
			$totalpages = ceil($records / $length);
			if ($totalpages < 1) {
				$totalpages = 1;
			}
			if ($page > $totalpages) {
				$page = $totalpages;
			}
			$offset = (($page - 1) * $length);
			$from = $records > 0 ? ($offset + 1) : 0;
			$to = (int) ($totalpages == $page ? $records : ($from + $length) - 1);
			$paging = array('from' => $from, 'to' => $to, 'totalrecords' => (int) $records, 'totalpages' => $totalpages, 'currentpage' => $page, 'offset' => $offset, 'length' => $length);
			return $paging;
		}
	}

	if (!function_exists('pagingLink')) {
		function pagingLink($current_page, $total_pages, $url="", $links = 2, $list_class = 'tp-shop-pagination mt-20')
		{
			$last = $total_pages;
			$start = (($current_page - $links) > 0) ? $current_page - $links : 1;
			$end = (($current_page + $links) < $last) ? $current_page + $links : $last;

			$html = '<div class="' . $list_class . '">';
			$html .= '<div class="tp-pagination">';
			$html .= '<nav><ul>';

			// Previous button
			$prev_disabled = ($current_page <= 1) ? ' disabled' : '';
			$html .= '<li><a href="'.generateUrl(array('page'=>$current_page-1),$url).'" class="tp-pagination-prev prev page-numbers'.$prev_disabled.'" title="Previous page">';
			$html .= '<svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">';
			$html .= '<path d="M1.00017 6.77879L14 6.77879" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>';
			$html .= '<path d="M6.24316 11.9999L0.999899 6.77922L6.24316 1.55762" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>';
			$html .= '</svg></a></li>';

			// First page
			if ($start > 1) {
				$html .= '<li><a href="'.generateUrl(array('page'=>1),$url).'">1</a></li>';
				if ($start > 2) {
					$html .= '<li><span class="dots">...</span></li>';
				}
			}

			// Page numbers
			for ($i = $start; $i <= $end; $i++) {
				if ($current_page == $i) {
					$html .= '<li><span class="current">' . $i . '</span></li>';
				} else {
					$html .= '<li><a href="'.generateUrl(array('page'=>$i),$url).'">' . $i . '</a></li>';
				}
			}

			// Last page
			if ($end < $last) {
				if ($end < $last - 1) {
					$html .= '<li><span class="dots">...</span></li>';
				}
				$html .= '<li><a href="'.generateUrl(array('page'=>$last),$url).'">' . $last . '</a></li>';
			}

			// Next button
			$next_disabled = ($current_page >= $last) ? ' disabled' : '';
			$html .= '<li><a href="'.generateUrl(array('page'=>$current_page+1),$url).'" class="next page-numbers'.$next_disabled.'" title="Next page">';
			$html .= '<svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">';
			$html .= '<path d="M13.9998 6.77883L1 6.77883" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>';
			$html .= '<path d="M8.75684 1.55767L14.0001 6.7784L8.75684 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>';
			$html .= '</svg></a></li>';

			$html .= '</ul></nav></div></div>';
			return $html;
		}
	}

	function removeEmptyParagraphs($content){
		$content = str_replace("<p></p>", "", $content);
		return $content;
	}

	if (!function_exists('moneyFormat')) {
		function moneyFormat($amount, $decimal = 0)
		{
			return '₹' . number_format(($amount * 1), $decimal);
		}
	}

	if (!function_exists('contentCleanup')) {
		function contentCleanup($content)
		{
			$content = rtrim($content, '<br>');
			$content = str_replace(array('<p> </p>', '<p></p>', '<div> </div>', '<div></div>'), '', $content);
			return $content;
		}
	}

	if (!function_exists('phoneCleanup')) {
		function phoneCleanup($phone)
		{
			return preg_replace('/\D+/', '', $phone);
		}
	}

	if (!function_exists('phonePattern')) {
		function phonePattern($phone)
		{
			$phone =  preg_replace('/\D+/', '', $phone);
			if(  preg_match( '/^(\d{4})(\d{3})(\d{3})$/', $phone,  $matches ) )
			{
				$result = $matches[1] . ' ' .$matches[2] . ' ' . $matches[3];
				return $result;
				}else{
				return $phone;
			}
		}
	}

	if (!function_exists('nl2sms')) {
		function nl2sms($text)
		{
			return str_replace(array('<br>', '<br/>', '<br />', '/n', '/r/n'), '%0a', $text);
		}
	}

	if (!function_exists('deleteFile')) {
		function deleteFile($file = "")
		{
			if (!empty($file) && file_exists($file)) {
				unlink($file);
			}
		}
	}

	if (!function_exists('array_key_last')) {
		/**
			* Polyfill for array_key_last() function added in PHP 7.3.
			*
			* Get the last key of the given array without affecting
			* the internal array pointer.
			*
			* @param array $array An array
			*
			* @return mixed The last key of array if the array is not empty; NULL otherwise.
		*/
		function array_key_last($array)
		{
			$key = null;
			if (is_array($array)) {

				end($array);
				$key = key($array);
			}
			return $key;
		}
	}

	if(!function_exists('replaceWord')) {
		function replaceWord($search, $replace, $subject) {
			return str_replace($search, $replace, $subject);
		}
	}

	if(!function_exists('replaceWordList')) {
		function replaceWordList($wordlist, $subject) {
			foreach($wordlist as $key => $val) {
				$subject = str_replace($key, $val, $subject);
			}
			return $subject;
		}
	}

	if(!function_exists('phpDate')){
		function phpDate($date)
		{
			if($date){
				$date = str_replace(array('/','.',' '),'-',$date);
				return date('Y-m-d',strtotime($date));
			}else{
				return null;
			}
		}
	}

	if(!function_exists('generateUrl')){
		function generateUrl($values = array(), $url = '')
		{
			if(empty($url)){
				$url = current_url() . (!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '');
			}
			$uri = new \CodeIgniter\HTTP\URI($url);
			if(is_array($values) && count($values)>0){
				foreach($values as $key => $val) {
					$uri->addQuery($key, $val);
				}
			}

			return (string) $uri;
		}
	}
	if(!function_exists('generateNewUrl')){
		function generateNewUrl($values = array(), $url = '')
		{
			if(!empty($url)){
				if(is_array($values) && count($values)>0){
					$parms='';
					foreach($values as $key => $val) {
						$parms = $parms.'/'.$key."/".$val;
					}
					$uri = $url.$parms;
				}
			}

			return (string) $uri;
		}
	}

	if(!function_exists('text2Array')){
		function text2Array($values)
		{
			$values = str_replace(array("\n", "\r"), ',', $values);
			$values = explode(",", $values);
			foreach ($values as $k => $val) {
				$v = trim($val);
				if(strlen($v)==0){
					unset($values[$k]);
					} else {
					$values[$k] = $v;
				}
			}
			return $values;
		}
	}

	if(!function_exists('urlfileExist')){
		function urlfileExist($url)
		{
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			return $code == 200 ? true : false;
		}
	}

	if(!function_exists('initials')){
		function initials($name='')
		{
			$name  = strtoupper($name);
			$words = explode(" ",$name);
			$firtsname = reset($words);
			$lastname  = end($words);
			return substr($firtsname,0,1).substr($lastname ,0,1);
		}
	}

	if (!function_exists('sanitizationString')) {
		function sanitizationString($string = '')
		{
			return filter_var($string, FILTER_SANITIZE_STRING);
		}
	}

	if (!function_exists('sanitizationEmail')) {
		function sanitizationEmail($email = '')
		{
			return filter_var($email, FILTER_SANITIZE_EMAIL);
		}
	}

	if (!function_exists('sanitizationDecimal')) {
		function sanitizationDecimal($number = '')
		{
			return filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		}
	}

	if (!function_exists('sanitizationInt')) {
		function sanitizationInt($number = '')
		{
			return filter_var($number, FILTER_SANITIZE_NUMBER_INT);
		}
	}

	if(!function_exists('applicationDate')){
		function applicationDate($date)
		{
			$len = strlen($date);
			if($len < 11){
				$date = str_replace(array('/','.',' '),'-',$date);
				} else {
				$date = str_replace(array('/','.'),'-',$date);
			}
			return date('d M Y',strtotime($date));
		}
	}

	if(!function_exists('applicationDateTime')){
		function applicationDateTime($datetime)
		{
			$datetime = str_replace(array('/','.'),'-',$datetime);
			return date('d M Y, h:i a',strtotime($datetime));
		}
	}
	if (!function_exists('getRemoteIPAddress')) {
		function getRemoteIPAddress() {
			$ipaddress = '';
			if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
			else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
			else
            $ipaddress = 'UNKNOWN';
			return $ipaddress;
		}
	}


	if(!function_exists('numToWord')){
		function numToWord($number)
		{
			$no = round($number);
			$point = round($number - $no, 2) * 100;
			$hundred = null;
			$digits_1 = strlen($no);
			$i = 0;
			$str = array();
			$words = array('0' => '', '1' => 'One', '2' => 'Two',
			'3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
			'7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
			'10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
			'13' => 'Thirteen', '14' => 'Fourteen',
			'15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
			'18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
			'30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
			'60' => 'Sixty', '70' => 'Seventy',
			'80' => 'Eighty', '90' => 'Ninety');
			$digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
			while ($i < $digits_1) {
				$divider = ($i == 2) ? 10 : 100;
				$number = floor($no % $divider);
				$no = floor($no / $divider);
				$i += ($divider == 10) ? 1 : 2;
				if ($number) {
					$plural = (($counter = count($str)) && $number > 9) ? 's' : null;
					$hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
					$str [] = ($number < 21) ? $words[$number] .
					" " . $digits[$counter] . $plural . " " . $hundred :
					$words[floor($number / 10) * 10]
					. " " . $words[$number % 10] . " "
					. $digits[$counter] . $plural . " " . $hundred;
				} else
				$str[] = null;
			}
			$str = array_reverse($str);
			return $amount_word = implode('', $str);
		}
	}

	if (!function_exists('GetUserCode')) {
		function GetUserCode() {
			return date('YmdHis').rand(10000, 99999);
		}
	}

	if (!function_exists('validateEmail')) {
		function validateEmail($email) {
			return filter_var(sanitizationEmail($email), FILTER_VALIDATE_EMAIL) !== false;
		}
	}
	if (!function_exists('validatePhone')) {
		function validatePhone($phone) {
			// Remove any non-numeric characters for validation
			$phone = preg_replace('/[^0-9]/', '', $phone);
			// Check if phone number has valid length (adjust based on your requirements)
			return strlen($phone) >= 10 && strlen($phone) <= 15;
		}
	}

	if (!function_exists('validateCSRF')) {
		function validateCSRF($request): bool
		{
			$security = \CodeIgniter\Config\Services::security();
			$tokenName = $security->getTokenName();
			$expectedToken = $security->getHash();

			$providedToken = $request->getPost($tokenName)
				?? $request->getHeaderLine('X-CSRF-TOKEN');

			return $providedToken === $expectedToken;
		}
	}

	if (!function_exists('sendOtp')) {
		function sendOtp($user_id) {
			$model = null;
			$model = new \App\Models\UserModel();
			if($model){
				$user = $model->find($user_id);
				$otp = rand(100000, 999999);
				$user['otp'] = $otp;
				$user['otp_expiry'] = date('Y-m-d H:i:s', strtotime('+10 minutes'));
				$user['otp_attempts'] = 0;
				$AppConfig = new \Config\AppConfig();
				if ($AppConfig->twoFactorAuth['enabled']) {
					if($AppConfig->twoFactorAuth['send']['email']){
						//send otp to email
						$email = $user['email'];
					}
					if($AppConfig->twoFactorAuth['send']['sms']){
						//send otp to sms
						$phone = $user['phone'];
					}
					if($AppConfig->twoFactorAuth['send']['whatsapp']){
						//send otp to whatsapp
						$whatsapp = $user['phone'];
					}
					$model->update($user_id, $user);
					return true;
				}
			} else {
				return false;
			}
		}
	}

	if (!function_exists('getPdfStyles')) {
		function getPdfStyles()
		{
			return '
			<style>
				body {
					font-family: Arial, sans-serif;
					font-size: 10px;
					line-height: 1.4;
					margin: 0;
					padding: 10px;
				}

				.report-header {
					text-align: center;
					margin-bottom: 20px;
					border-bottom: 2px solid #333;
					padding-bottom: 10px;
				}

				.report-header h1 {
					color: #2c3e50;
					margin: 0 0 10px 0;
					font-size: 18px;
				}

				.report-header p {
					margin: 5px 0;
					font-size: 11px;
				}

				.data-table {
					width: 100%;
					border-collapse: collapse;
					margin-top: 15px;
					font-size: 9px;
				}

				.data-table th {
					background-color: #4472C4;
					color: white;
					font-weight: bold;
					text-align: center;
					padding: 8px 4px;
					border: 1px solid #333;
					font-size: 9px;
				}

				.data-table td {
					padding: 6px 4px;
					border: 1px solid #333;
					text-align: left;
					vertical-align: top;
					font-size: 8px;
				}

				.data-table tr:nth-child(even) {
					background-color: #f9f9f9;
				}

				.no-data {
					text-align: center;
					font-style: italic;
					color: #7f8c8d;
					margin: 20px 0;
				}
			</style>';
		}
	}

	if (!function_exists('detectAudioLanguage')) {
		function detectAudioLanguage($base64Audio) {
			$ch = curl_init('https://meity-auth.ulcacontrib.org/ulca/apis/v0/model/compute');
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_HTTPHEADER => [
					'ulcaApiKey: 2156620a9d-d8d5-4b37-860a-aa61cf5942bc',
					'userID: 53f6dec39c2f48e489a023a5295f1fa4',
					'Content-Type: application/json'
				],
				CURLOPT_POSTFIELDS => json_encode([
					"modelId" => "66cf53e58cfc565ee0d1a8e2",
					"task" => "audio-lang-detection",
					"audioContent" => $base64Audio,
					"source" => "mixed"
				])
			]);
			$response = curl_exec($ch);
			if (curl_errno($ch)) throw new Exception('cURL Error: ' . curl_error($ch));
			curl_close($ch);

			$data = json_decode($response, true);
			return $data['output'][0]['langPrediction'][0]['langCode'] ?? '-';
		}
	}


	if (!function_exists('audioToText')) {
		function audioToText($sourceLanguage, $base64Audio) {

			//get serviceId from bhashini_configuration  pipelineResponseConfig task type asr/config/language/sourceLanguage == $sourceLanguage then set serviceId

			$bhashiniLanguage = getBhashiniLanguage($sourceLanguage, 'language_en');
			if($bhashiniLanguage){
				$sourceLanguageCode = $bhashiniLanguage['code'];
			}

			$bhashiniConfiguration = file_get_contents(base_url('assets/json/bhashini_configuration.json'));
			$bhashiniConfiguration = json_decode($bhashiniConfiguration, true);
			$serviceId = '';
			foreach ($bhashiniConfiguration['pipelineResponseConfig'] as $key => $value) {
				if($value['taskType'] == 'asr') {
					foreach($value['config'] as $key => $config){
						if($config['language']['sourceLanguage'] == $sourceLanguageCode) {
							$serviceId = $config['serviceId'];
							break;
						}
					}
				}
			}

			if($serviceId == ''){
				return '-';
			}

			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://dhruva-api.bhashini.gov.in/services/inference/pipeline',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{
				"pipelineTasks": [
					{
						"taskType": "asr",
						"config": {
							"language": {
								"sourceLanguage": "'.trim($sourceLanguageCode).'"
							},
							"serviceId": "'.trim($serviceId).'",
							"audioFormat": "flac",
							"samplingRate": 16000
						}
					}
				],
				"inputData": {
					"audio": [
						{
							"audioContent": "'.trim($base64Audio).'"
						}
					]
				}
			}',
			CURLOPT_HTTPHEADER => array(
				'Accept:  */*',
				'User-Agent:  Thunder Client (https://www.thunderclient.com)',
				'Authorization: gbes7KiCpI3uqHoYH5OY_TPgPVZ67lsDXT65ZTUFKJ752fvm_xROvoac9yuUdw2V',
				'Content-Type: application/json'
			),
			));

			$response = curl_exec($curl);
			curl_close($curl);

			$data = json_decode($response, true);
			return isset($data['pipelineResponse'][0]['output'][0]['source']) ? $data['pipelineResponse'][0]['output'][0]['source'] : '-';
		}
	}

	if (!function_exists('textToText')) {
		function textToText($sourceLanguage, $targetLanguageCode, $text) {

			$bhashiniLanguage = getBhashiniLanguage($sourceLanguage, 'language_en');
			if($bhashiniLanguage){
				$sourceLanguageCode = $bhashiniLanguage['code'];
			}else{
				$sourceLanguageCode = $sourceLanguage;
			}

			$bhashiniConfiguration = file_get_contents(base_url('assets/json/bhashini_configuration.json'));
			$bhashiniConfiguration = json_decode($bhashiniConfiguration, true);
			$serviceId = '';
			foreach ($bhashiniConfiguration['pipelineResponseConfig'] as $key => $value) {
				if($value['taskType'] == 'translation') {
					foreach($value['config'] as $key => $config){
						if($config['language']['sourceLanguage'] == $sourceLanguageCode && $config['language']['targetLanguage'] == $targetLanguageCode) {
							$serviceId = $config['serviceId'];
							break;
						}
					}
				}
			}

			if($serviceId == ''){
				return '-';
			}

			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://dhruva-api.bhashini.gov.in/services/inference/pipeline',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{
				"pipelineTasks": [
					{
						"taskType": "translation",
						"config": {
							"language": {
								"sourceLanguage": "'.trim($sourceLanguageCode).'",
								"targetLanguage": "'.trim($targetLanguageCode).'"
							},
							"serviceId": "'.trim($serviceId).'"
						}
					}
				],
				"inputData": {
					"input": [
						{
							"source": "'.trim($text).'"
						}
					]
				}
			}',
			CURLOPT_HTTPHEADER => array(
				'Accept:  */*',
				'User-Agent:  Thunder Client (https://www.thunderclient.com)',
				'Authorization: gbes7KiCpI3uqHoYH5OY_TPgPVZ67lsDXT65ZTUFKJ752fvm_xROvoac9yuUdw2V',
				'Content-Type: application/json'
			),
			));

			$response = curl_exec($curl);
			curl_close($curl);

			$data = json_decode($response, true);
			return isset($data['pipelineResponse'][0]['output'][0]['target']) ? $data['pipelineResponse'][0]['output'][0]['target'] : '-';
		}
	}

	if (!function_exists('textToAudio')) {
		function textToAudio($sourceLanguage, $text) {

			$bhashiniLanguage = getBhashiniLanguage($sourceLanguage, 'language_en');
			if($bhashiniLanguage){
				$sourceLanguageCode = $bhashiniLanguage['code'];
			}else{
				$sourceLanguageCode = $sourceLanguage;
			}

			$bhashiniConfiguration = file_get_contents(base_url('assets/json/bhashini_configuration.json'));
			$bhashiniConfiguration = json_decode($bhashiniConfiguration, true);
			$serviceId = '';
			foreach ($bhashiniConfiguration['pipelineResponseConfig'] as $key => $value) {
				if($value['taskType'] == 'tts') {
					foreach($value['config'] as $key => $config){
						if($config['language']['sourceLanguage'] == $sourceLanguageCode ) {
							$serviceId = $config['serviceId'];
							break;
						}
					}
				}
			}
			if($serviceId == ''){
				return '-';
			}


			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://dhruva-api.bhashini.gov.in/services/inference/pipeline',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{
				"pipelineTasks": [
					{
						"taskType": "tts",
						"config": {
							"language": {
								"sourceLanguage": "'.trim($sourceLanguageCode).'"
							},
							"serviceId": "'.trim($serviceId).'",
							"gender": "female",
							"samplingRate": 8000
						}
					}
				],
				"inputData": {
					"input": [
						{
							"source": "'.trim($text).'"
						}
					]
				}
			}',
			CURLOPT_HTTPHEADER => array(
				'Accept:  */*',
				'User-Agent:  Thunder Client (https://www.thunderclient.com)',
				'Authorization: gbes7KiCpI3uqHoYH5OY_TPgPVZ67lsDXT65ZTUFKJ752fvm_xROvoac9yuUdw2V',
				'Content-Type: application/json'
			),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			$data = json_decode($response, true);
			return isset($data['pipelineResponse'][0]['audio'][0]['audioContent']) ? $data['pipelineResponse'][0]['audio'][0]['audioContent'] : '-';
		}
	}


	if (!function_exists('getBhashiniLanguage')) {
		function getBhashiniLanguage($value, $type = 'code') {
			$bhashiniLanguages = file_get_contents(base_url('assets/json/bhashini_languages.json'));
			$bhashiniLanguages = json_decode($bhashiniLanguages, true);

			foreach ($bhashiniLanguages as $k => $language) {
				if($type == 'code'){
					if($language['code'] == $value){
						return $language;
					}
				}
				else if($type == 'language_en'){
					if($language['language_en'] == $value){
						return $language;
					}
				}
				else if($type == 'language_local'){
					if($language['language_local'] == $value){
						return $language;
					}
				}
			}
			return null;
		}
	}

	if (!function_exists('addFace')) {
		function addFace($imageUrl, $externalId) {
			$AppConfig = new \Config\AppConfig();
			if($AppConfig->recognize['mxface']['enabled']){
				addFaceWithMxFace($imageUrl, $externalId);
			}
			if($AppConfig->recognize['luxand']['enabled']){
				addFaceWithLuxand($imageUrl, $externalId);
			}
			if($AppConfig->recognize['inhouse']['enabled']){
				addFaceWithInhouse($imageUrl, $externalId);
			}
		}
	}


	if (!function_exists('addFaceWithMxFace')) {
		function addFaceWithMxFace($imageUrl, $externalId) {
			$AppConfig = new \Config\AppConfig();
			$url = $AppConfig->recognize['mxface']['url'];
			$subscriptionkey = $AppConfig->recognize['mxface']['subscriptionkey'];

			$encoded_image = base64_encode(file_get_contents($imageUrl));

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url . 'v3/FaceIdentity',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => json_encode(array(
					'groupId' => $AppConfig->recognize['mxface']['group_id'],
					'image' => $encoded_image,
					'externalId' => $externalId, // TODO: generate externalId
				)),
				CURLOPT_HTTPHEADER => array(
					'subscriptionkey: ' . $subscriptionkey,
					'Content-Type: application/json'
				),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			return $response;
		}
	}

	if (!function_exists('addFaceWithLuxand')) {
		function addFaceWithLuxand($imageUrl) {
			$AppConfig = new \Config\AppConfig();
			$url = $AppConfig->recognize['luxand']['url'];
			$apitoken = $AppConfig->recognize['luxand']['apitoken'];

			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => $url . 'v2/person',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => array('name' => '','photos'=> new CURLFILE($imageUrl),'store' => '1','collections' => '','unique' => '0'),
			CURLOPT_HTTPHEADER => array(
				'token: '.$apitoken
			),
			));

			$response = curl_exec($curl);
			if (curl_errno($curl)) {
				echo "cURL Error: " . curl_error($curl);
			}
			curl_close($curl);
			return $response;

			//james-person.jpg
			//{"status": "success", "uuid": "6901590d-c442-11f0-86d3-0242ac120002", "name": "", "faces": [{"uuid": "68c2c109-c442-11f0-86d3-0242ac120002", "url": "https://faces.nyc3.digitaloceanspaces.com/68c2c109-c442-11f0-86d3-0242ac120002.jpg"}], "collections": []}

		}
	}

	if (!function_exists('addFaceWithInhouse')) {
		function addFaceWithInhouse($imageUrl, $externalId) {
			$AppConfig = new \Config\AppConfig();
			$url = $AppConfig->recognize['inhouse']['url'];
			$apitoken = $AppConfig->recognize['inhouse']['apitoken'];
		}
	}

	/* OLD 1 to 1 Compare with MXFace API */
	if (!function_exists('compareWithMxFaceApi')) {
		function compareWithMxFaceApi($lostImageUrl, $foundImageUrl) {
			$AppConfig = new \Config\AppConfig();
			if($AppConfig->recognize['mxface']['enabled']){
				$url = $AppConfig->recognize['mxface']['url'];
				$subscriptionkey = $AppConfig->recognize['mxface']['subscriptionkey'];

				$encoded_image1 = base64_encode(file_get_contents($lostImageUrl));
				$encoded_image2 = base64_encode(file_get_contents($foundImageUrl));

				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $url . 'verify',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => json_encode(array(
					'encoded_image1' => $encoded_image1,
					'encoded_image2' => $encoded_image2,
				)),
				CURLOPT_HTTPHEADER => array(
					'subscriptionkey: ' . $subscriptionkey,
					'Content-Type: application/json'
				),
				));

				$response = curl_exec($curl);
				curl_close($curl);

				if ($response === false || empty($response)) {
					return -1;
				}

				$data = json_decode($response, true);
				if (!is_array($data)) {
					return -1;
				}

				if (isset($data['matchedFaces']) && is_array($data['matchedFaces']) && !empty($data['matchedFaces'])) {
					$first = $data['matchedFaces'][0];
					if (isset($first['confidence'])) {
						return (float) $first['confidence'];
					}
				}

				// Some MXFace responses may return alternate fields; if not present, treat as failure
				return -1;
			}

			// API disabled
			return -1;
		}
	}

	/* API Face Matching */
	if (!function_exists('compareFaces')) {
		function compareFaces($imageUrl) {
			$AppConfig = new \Config\AppConfig();
			if($AppConfig->recognize['mxface']['enabled']){
				return mapFacesWithMxFace($imageUrl);
			}
			if($AppConfig->recognize['luxand']['enabled']){
				return mapFacesWithLuxand($imageUrl);
			}
			if($AppConfig->recognize['inhouse']['enabled']){
				return mapFacesWithInhouse($imageUrl);
			}
		}
	}

	if (!function_exists('mapFacesWithMxFace')) {
		function mapFacesWithMxFace($imageUrl) {
			$AppConfig = new \Config\AppConfig();
			$url = $AppConfig->recognize['mxface']['url'];
			$groupId = $AppConfig->recognize['mxface']['group_id'];
			$subscriptionkey = $AppConfig->recognize['mxface']['subscriptionkey'];
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url . 'v3/FaceIdentity/search',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS => json_encode(array(
				'groupIds' => array($groupId),
				'encoded_Image' => base64_encode(file_get_contents($imageUrl)),
				'limit' => 10
			  )),
			  CURLOPT_HTTPHEADER => array(
				'subscriptionkey: ' . $subscriptionkey,
				'Content-Type: application/json'
			  ),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			$data = json_decode($response, true);
			$id = isset($data['searchedIdentities'][0]['identityConfidences'][0]['identity']['externalId']) ? $data['searchedIdentities'][0]['identityConfidences'][0]['identity']['externalId'] : null;

			$matches = array();
			if($id){
				$LostOrFound = substr($id, 0, 1) == 'L' ? 'Lost' : 'Found';
				$ListingId  = substr($id, 1);
				if($LostOrFound == 'Lost'){
					$lostPeopleModel = new LostPeopleModel();
					$lostPerson = $lostPeopleModel->select('first_name, last_name, photo, created_at, updated_at')->where('lost_id', $ListingId)->first();
					if($lostPerson){
						$matches[] = array(
							'id' => $ListingId,
							'url' => site_url('lost-people/view/' . $ListingId),
							'name' => fullname($lostPerson['first_name'], $lostPerson['last_name']),
							'match' => (string) (float) round(100),
							'last_updated' => applicationDateTime(date('Y-m-d H:i:s'))
						);
					}
				} else {
					$foundPeopleModel = new FoundPeopleModel();
					$foundPerson = $foundPeopleModel->select('first_name, last_name, photo, created_at, updated_at')->where('found_id', $ListingId)->first();
					if($foundPerson){
						$matches[] = array(
							'id' => $ListingId,
							'url' => site_url('found-people/view/' . $ListingId),
							'name' => fullname($foundPerson['first_name'], $foundPerson['last_name']),
							'match' => (string) (float) round(100),
							'last_updated' => applicationDateTime(date('Y-m-d H:i:s'))
						);
					}
				}
			}
			return $matches;
		}
	}

	if (!function_exists('mapFacesWithLuxand')) {
		function mapFacesWithLuxand($imageUrl) {
			$AppConfig = new \Config\AppConfig();
			if($AppConfig->recognize['luxand']['enabled']){
				$url = $AppConfig->recognize['luxand']['url'];
				$apitoken = $AppConfig->recognize['luxand']['apitoken'];

				$curl = curl_init();

				curl_setopt_array($curl, array(
				CURLOPT_URL => $url . 'photo/search/v2',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => array('photo'=> new CURLFILE($imageUrl),'collections' => ''),
				CURLOPT_HTTPHEADER => array(
					'token: '.$apitoken
				),
				));

				$response = curl_exec($curl);
				if (curl_errno($curl)) {
					echo "cURL Error: " . curl_error($curl);
				}
				curl_close($curl);
					$data = json_decode($response, true);
					return array('probability' => isset($data[0]['probability']) ? $data[0]['probability']*100 : null, 'uuid' => isset($data[0]['uuid']) ? $data[0]['uuid'] : null);

					//[{"name":"","probability":0.9992859959602356,"rectangle":{"left":58,"top":24,"right":144,"bottom":110},"uuid":"6901590d-c442-11f0-86d3-0242ac120002","collections":[]}]
			}
			return array('probability' => null, 'uuid' => null);
		}
	}

	if (!function_exists('mapFacesWithInhouse')) {
		function mapFacesWithInhouse($imageUrl) {
			$AppConfig = new \Config\AppConfig();
			$url = $AppConfig->recognize['inhouse']['url'];
			$apitoken = $AppConfig->recognize['inhouse']['apitoken'];
		}
	}
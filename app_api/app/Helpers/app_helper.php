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
			$updat_details = array();
			$model = new \App\Models\UserModel();
			if($model){
				$user = $model->find($user_id);
				$otp = rand(100000, 999999);
				$updat_details['otp'] = $otp;
				$updat_details['otp_expiry'] = date('Y-m-d H:i:s', strtotime('+10 minutes'));
				$updat_details['otp_attempts'] = 0;
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
					$model->update($user_id, $updat_details);
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
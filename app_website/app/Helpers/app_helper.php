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
				// Append page-level scripts at the end so global helpers (e.g. showLoader/hideLoader in script.js)
				// are defined before any page scripts execute.
				$scripts = array_merge($scripts, $js);
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

	/**
	 * Attendance helper: returns year + month name for a given date.
	 * If year cannot be derived, falls back to $defaultYear (requested: 2025).
	 *
	 * @return array{year:int, month:string}
	 */
	if (!function_exists('attendance_year_month')) {
		function attendance_year_month(?string $date = null, int $defaultYear = 2025): array
		{
			$date = $date ?: date('Y-m-d');
			$ts = strtotime($date);
			if ($ts === false) {
				return [
					'year'  => $defaultYear,
					'month' => 'January',
				];
			}

			$year = (int) date('Y', $ts);
			if ($year <= 0) {
				$year = $defaultYear;
			}

			$month = date('F', $ts); // January, February, ...

			return [
				'year'  => $year,
				'month' => $month,
			];
		}
	}



	if (!function_exists('upload_image')) {
		function upload_image($file, string $basePath, string $existingFilename = '', $options = array()): array
		{
			// Allow passing a boolean as 4th argument for square crop (backward compatible)
			// Example: upload_image($_FILES['x'] ?? null, 'uploads/users', '', true)
			if (is_bool($options)) {
				$options = array('crop_square' => $options, 'create_thumb' => true);
			} elseif (!is_array($options)) {
				$options = array();
			}

			// create_thumb defaults to true
			$createThumb = array_key_exists('create_thumb', $options) ? (bool) $options['create_thumb'] : true;

			// No new file uploaded - keep existing
			if (empty($file) || !is_array($file) || empty($file['name'])) {
				$basePath = trim($basePath, "/\\");
				return array(
					'success' => true,
					'filename' => $existingFilename,
					'path' => !empty($existingFilename)
						? ($createThumb ? ($basePath . '/thumb/' . $existingFilename) : ($basePath . '/' . $existingFilename))
						: '',
					'large_path' => !empty($existingFilename)
						? ($createThumb ? ($basePath . '/large/' . $existingFilename) : ($basePath . '/' . $existingFilename))
						: '',
					'thumb_path' => !empty($existingFilename) ? ($createThumb ? ($basePath . '/thumb/' . $existingFilename) : '') : '',
				);
			}

			$allowedExtensions = $options['allowed_extensions'] ?? array('jpeg', 'jpg', 'png', 'webp', 'gif');
			$maxSizeBytes = $options['max_size_bytes'] ?? (10 * 1024 * 1024);
			$cropSquare = !empty($options['crop_square']);

			$fileNameOriginal = (string) ($file['name'] ?? '');
			$fileSize = (int) ($file['size'] ?? 0);
			$fileTmp = (string) ($file['tmp_name'] ?? '');
			$fileError = (int) ($file['error'] ?? 0);

			if ($fileError !== 0) {
				return array('success' => false, 'filename' => $existingFilename, 'path' => '', 'large_path' => '', 'thumb_path' => '', 'message' => 'File upload failed');
			}

			$fileExt = strtolower(pathinfo($fileNameOriginal, PATHINFO_EXTENSION));
			if (empty($fileExt) || !in_array($fileExt, $allowedExtensions, true)) {
				return array('success' => false, 'filename' => $existingFilename, 'path' => '', 'large_path' => '', 'thumb_path' => '', 'message' => "Extension not allowed, please choose a JPEG, PNG, GIF or WebP file.");
			}
			if ($fileSize > $maxSizeBytes) {
				return array('success' => false, 'filename' => $existingFilename, 'path' => '', 'large_path' => '', 'thumb_path' => '', 'message' => 'File size must be 10 MB or less');
			}
			if (empty($fileTmp) || !is_file($fileTmp)) {
				return array('success' => false, 'filename' => $existingFilename, 'path' => '', 'large_path' => '', 'thumb_path' => '', 'message' => 'File upload failed');
			}

			// Resolve base path to absolute
			$isAbsolute = (bool) preg_match('/^[A-Za-z]:\\\\|^\\//', $basePath);
			$basePathRel = $isAbsolute ? '' : trim($basePath, "/\\");
			if (!empty($basePathRel)) {
				$basePathRel = str_replace('\\', '/', $basePathRel);
			}
			$baseDirAbs = $isAbsolute ? rtrim($basePath, "/\\") : rtrim(FCPATH, "/\\") . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $basePathRel);

			$largeDirAbs = $baseDirAbs . DIRECTORY_SEPARATOR . 'large';
			$thumbDirAbs = $baseDirAbs . DIRECTORY_SEPARATOR . 'thumb';

			if (!is_dir($largeDirAbs)) {
				mkdir($largeDirAbs, 0755, true);
			}
			if (!is_dir($thumbDirAbs)) {
				mkdir($thumbDirAbs, 0755, true);
			}

			// Sizes: allow config override via app_config('imageSizes')
			$imageSizes = $options['image_sizes'] ?? app_config('imageSizes');
			if (!is_array($imageSizes) || !isset($imageSizes['large'], $imageSizes['thumb'])) {
				$imageSizes = array('large' => array(800, 600), 'thumb' => array(340, 255));
			}

			$newFilename = md5(uniqid((string) rand(), true)) . '.' . $fileExt;

			try {
				$largeW = (int) ($imageSizes['large'][0] ?? 800);
				$largeH = (int) ($imageSizes['large'][1] ?? 600);
				$thumbW = (int) ($imageSizes['thumb'][0] ?? 340);
				$thumbH = (int) ($imageSizes['thumb'][1] ?? 255);

				$largeSquare = (int) ($options['square_size_large'] ?? min($largeW, $largeH));
				$thumbSquare = (int) ($options['square_size_thumb'] ?? min($thumbW, $thumbH));
				if ($largeSquare <= 0) {
					$largeSquare = 600;
				}
				if ($thumbSquare <= 0) {
					$thumbSquare = 255;
				}

				$imageresize = new \App\Libraries\ImageResize($fileTmp);
				$imageresize->quality_jpg = 90;
				$imageresize->quality_png = 8;
				$imageresize->quality_webp = 90;

				$savedLargeAbs = '';
				$savedThumbAbs = '';
				$savedSingleAbs = '';

				if ($createThumb) {
					if ($cropSquare) {
						$imageresize->crop($largeSquare, $largeSquare, false, \App\Libraries\ImageResize::CROPCENTER);
					} else {
						$imageresize->resizeToBestFit($largeW, $largeH);
					}
					if (!$imageresize->save($largeDirAbs . DIRECTORY_SEPARATOR . $newFilename)) {
						return array('success' => false, 'filename' => $existingFilename, 'path' => '', 'large_path' => '', 'thumb_path' => '', 'message' => 'File upload failed');
					}
					$savedLargeAbs = $largeDirAbs . DIRECTORY_SEPARATOR . $newFilename;

					$thumbResize = new \App\Libraries\ImageResize($savedLargeAbs);
					$thumbResize->quality_jpg = 85;
					$thumbResize->quality_png = 7;
					$thumbResize->quality_webp = 85;
					if ($cropSquare) {
						$thumbResize->crop($thumbSquare, $thumbSquare, false, \App\Libraries\ImageResize::CROPCENTER);
					} else {
						$thumbResize->resizeToBestFit($thumbW, $thumbH);
					}
					$imageBinary = $thumbResize->getImageAsString();
					if (!file_put_contents($thumbDirAbs . DIRECTORY_SEPARATOR . $newFilename, $imageBinary)) {
						@unlink($savedLargeAbs);
						return array('success' => false, 'filename' => $existingFilename, 'path' => '', 'large_path' => '', 'thumb_path' => '', 'message' => 'File upload failed');
					}
					$savedThumbAbs = $thumbDirAbs . DIRECTORY_SEPARATOR . $newFilename;
				} else {
					// No thumb requested: save a single image directly inside base folder (not /large)
					if (!is_dir($baseDirAbs)) {
						mkdir($baseDirAbs, 0755, true);
					}

					if ($cropSquare) {
						$imageresize->crop($largeSquare, $largeSquare, false, \App\Libraries\ImageResize::CROPCENTER);
					} else {
						$imageresize->resizeToBestFit($largeW, $largeH);
					}

					if (!$imageresize->save($baseDirAbs . DIRECTORY_SEPARATOR . $newFilename)) {
						return array('success' => false, 'filename' => $existingFilename, 'path' => '', 'large_path' => '', 'thumb_path' => '', 'message' => 'File upload failed');
					}
					$savedSingleAbs = $baseDirAbs . DIRECTORY_SEPARATOR . $newFilename;
				}

				// Upload to S3 if enabled in AppConfig
				$AppConfig = new \Config\AppConfig();
				$s3Enabled = isset($AppConfig->S3['enabled']) && (bool) $AppConfig->S3['enabled'];
				$s3Urls = array('large' => '', 'thumb' => '', 'file' => '');
				if ($s3Enabled) {
					// We need a relative prefix for S3 keys
					if (empty($basePathRel)) {
						// Cannot derive S3 key prefix from absolute path
						if (!empty($savedLargeAbs)) {
							@unlink($savedLargeAbs);
						}
						if (!empty($savedThumbAbs)) {
							@unlink($savedThumbAbs);
						}
						if (!empty($savedSingleAbs)) {
							@unlink($savedSingleAbs);
						}
						return array('success' => false, 'filename' => $existingFilename, 'path' => '', 'large_path' => '', 'thumb_path' => '', 'message' => 'S3 upload failed: invalid base path');
					}

					try {
						$s3 = new \App\Libraries\AwsS3();
						$uploads = array();
						if ($createThumb) {
							$uploads[] = array('key' => $basePathRel . '/large/' . $newFilename, 'file' => $savedLargeAbs);
							$uploads[] = array('key' => $basePathRel . '/thumb/' . $newFilename, 'file' => $savedThumbAbs);
						} else {
							$uploads[] = array('key' => $basePathRel . '/' . $newFilename, 'file' => $savedSingleAbs);
						}

						foreach ($uploads as $u) {
							if (empty($u['file']) || !is_file($u['file'])) {
								throw new \RuntimeException('S3 upload failed: missing local file');
							}
							$ok = $s3->upload(array('key' => $u['key'], 'file' => $u['file']));
							if (!$ok) {
								throw new \RuntimeException($s3->error ?: 'S3 upload failed');
							}

							// capture urls
							if ($createThumb && str_contains($u['key'], '/large/')) {
								$s3Urls['large'] = $s3->url($u['key']);
							} elseif ($createThumb && str_contains($u['key'], '/thumb/')) {
								$s3Urls['thumb'] = $s3->url($u['key']);
							} else {
								$s3Urls['file'] = $s3->url($u['key']);
							}
						}

						// delete old files in S3 (best-effort)
						if (!empty($existingFilename)) {
							@$s3->delete($basePathRel . '/large/' . $existingFilename);
							@$s3->delete($basePathRel . '/thumb/' . $existingFilename);
							@$s3->delete($basePathRel . '/' . $existingFilename);
						}
					} catch (\Throwable $e) {
						// cleanup newly created local files
						if (!empty($savedLargeAbs)) {
							@unlink($savedLargeAbs);
						}
						if (!empty($savedThumbAbs)) {
							@unlink($savedThumbAbs);
						}
						if (!empty($savedSingleAbs)) {
							@unlink($savedSingleAbs);
						}
						log_message('error', 'S3 upload error: ' . $e->getMessage());
						return array('success' => false, 'filename' => $existingFilename, 'path' => '', 'large_path' => '', 'thumb_path' => '', 'message' => 'S3 upload failed');
					}
				}

				// Remove old files
				if (!empty($existingFilename)) {
					// Remove from all possible locations (in case behavior changed)
					@unlink($largeDirAbs . DIRECTORY_SEPARATOR . $existingFilename);
					@unlink($thumbDirAbs . DIRECTORY_SEPARATOR . $existingFilename);
					@unlink($baseDirAbs . DIRECTORY_SEPARATOR . $existingFilename);
				}

				$largeRel = $isAbsolute ? '' : ($createThumb ? ($basePathRel . '/large/' . $newFilename) : ($basePathRel . '/' . $newFilename));
				$thumbRel = $isAbsolute ? '' : ($createThumb ? ($basePathRel . '/thumb/' . $newFilename) : '');

				return array(
					'success' => true,
					'filename' => $newFilename,
					'path' => $createThumb ? $thumbRel : $largeRel,
					'large_path' => $largeRel,
					'thumb_path' => $thumbRel,
					's3_large_url' => $s3Urls['large'] ?? '',
					's3_thumb_url' => $s3Urls['thumb'] ?? '',
					's3_url' => $s3Urls['file'] ?? '',
				);
			} catch (\Throwable $e) {
				log_message('error', 'Error processing image upload: ' . $e->getMessage());
				return array('success' => false, 'filename' => $existingFilename, 'path' => '', 'large_path' => '', 'thumb_path' => '', 'message' => 'Error processing image.');
			}
		}
	}
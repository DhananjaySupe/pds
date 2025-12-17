<?php namespace App\Controllers;

	class Captcha extends BaseController
	{
		public function generate()
		{
			helper('text');

			$code = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
			session()->set('captcha_code', $code);

			// Create the image
			$img = imagecreatetruecolor(100, 40);
			$bg = imagecolorallocate($img, 255, 255, 255);
			$textColor = imagecolorallocate($img, 0, 0, 0);

			imagefilledrectangle($img, 0, 0, 100, 40, $bg);
			imagestring($img, 5, 30, 10, $code, $textColor);

			// Output the image
			header('Content-Type: image/png');
			imagepng($img);
			imagedestroy($img);
		}
	}
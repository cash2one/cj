<?php
/**
 * 水印
 * $Author$
 * $Id$
 */

class voa_c_frontend_news_watermark extends voa_c_frontend_news_base {

	public function execute() {

		// 要打入水印的名字
		$text = $this->_user['m_username'];
		// 缓存的名字
		$filename = md5(rstrtolower($text)) . '.png';

		$save_dir = voa_h_func::get_attachdir(startup_env::get('domain')) . DIRECTORY_SEPARATOR . 'watermark' . DIRECTORY_SEPARATOR;
		if (!is_dir($save_dir)) {
			rmkdir($save_dir, 0777);
		}
		$file_path = $save_dir . $filename;

		if (!file_exists($file_path)) {
			$size = mb_strlen($text, 'utf-8') > 6 ? 10 : 20;

			$block = imagecreatetruecolor(160, 150);//建立一个画板
			$bg = imagecolorallocatealpha($block, 0, 0, 0, 127);//拾取一个完全透明的颜色，不要用imagecolorallocate拾色
			//$color = imagecolorallocate($block, 233, 233, 233);//字体拾色
			$color = imagecolorclosestalpha($block, 0, 0, 0, 100);// 透明字
			//imagealphablending($block, false);//关闭混合模式，以便透明颜色能覆盖原画板
			imagefill($block, 0, 0, $bg);//填充
			imagettftext($block, $size, 38, 70, 60, $color, ROOT_PATH . "/apps/voa/cyadmin_www/static/fonts/YaHei.ttf", $text);
			//imagerotate($block, 145, 0);
			imagesavealpha($block, true);//设置保存PNG时保留透明通道信息

			imagepng($block, $file_path);//生成图片
			imagedestroy($block);
		}

		$this->response->set_raw_header("content-type:image/png");
		echo file_get_contents($file_path);
		$this->response->stop();

		return true;
	}

}

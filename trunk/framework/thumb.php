<?php
/**
 * 缩微图类
 * $Author$
 * $Id$
 */

class thumb {
	var $ratio = 0;
	var $quality = 75;
	var $fix_ratio = 0;
	var $fix_width = 0;
	var $fix_height = 0;
	var $dir = '';
	var $widths = array();
	static $instance = NULL;

	public static function instance($sets = array()) {
		if(is_null(self::$instance)) {
			self::$instance = new self($sets);
		}

		return self::$instance;
	}

	/**
	 * 初始化
	 * @return thumb
	 */
	public function thumb($sets) {
		$this->widths = $sets['widths'];
		$this->quality = $sets['quality'];
		$this->fix_ratio = $sets['fix_ratio'];
		$this->fix_width = $sets['fix_width'];
		$this->fix_height = $sets['fix_height'];
		$this->dir = $sets['dir'];
	}

	/**
	 * 设置路径
	 * @param string $dir 路径字串
	 * @return boolean
	 */
	public function set_dir($dir) {
		$this->dir = $dir;
		return true;
	}

	/**
	 * 设置子目录
	 * @param string $subdir 子目录
	 */
	public function set_subdir($subdir = NULL) {
		if($subdir) {
			$this->dir = $this->dir.slash($subdir, 0, 1);
			if(!is_dir($this->dir)) {
				mkdir($this->dir, 0777);
				fclose(fopen($this->dir.'index.htm', 'w'));
			}
		}

		return true;
	}

	/**
	 * 生产图片
	 * @param string $image 图片地址
	 * @param string $mime 文件mime
	 * @return 图片信息
	 */
	public function create_image($image, $mime) {
		switch($mime) {
			case 'image/jpeg':
				$tmp_im = @imageCreateFromJPEG($image);
				break;
			case 'image/gif':
				$tmp_im = @imageCreateFromGIF($image);
				break;
			case 'image/png':
				$tmp_im = @imageCreateFromPNG($image);
				break;
			default:
				$tmp_im = @ImageCreateFromString($image);
				break;
		}

		return $tmp_im;
	}

	/**
	 * 输出图片
	 * @param resource $tmp_im 图片地址
	 * @param string $image 目标地址
	 * @param string $mime 文件mime
	 */
	public function output_image($tmp_im, $image, $mime) {
		switch($mime) {
			case 'image/gif':
				@imageGIF($tmp_im, $image);
				break;
			case 'image/png':
				@imagePNG($tmp_im, $image);
				break;
			default:
				@imageJPEG($tmp_im, $image, $this->quality);
				break;
		}

		return;
	}

	/**
	 * 生产缩微图
	 * @param string $imgdir 图片地址
	 * @param string $subdir 缩微图子目录
	 * @return true;
	 */
	public function go($imgdir, $thumb_width = 0, $thumb_height = 0, $subdir = NULL) {
		if (0 >= $thumb_width && 0 >= $thumb_height) {
			return false;
		}

		$thumb_width = 0 >= $thumb_width ? $thumb_height : $thumb_width;
		$thumb_height = 0 >= $thumb_height ? $thumb_width : $thumb_height;

		$formerdir = $imgdir;
		$thumbdir = $imgdir.'_'.$thumb_width.'_'.$thumb_height.'.jpg';

		/** 未指定子目录时 */
		if($subdir) {
			$this->set_subdir($subdir);
			$thumbdir = $this->dir.trim(substr(strrchr($thumbdir, '/'), 1));
		}

		$image_info = @getimagesize($formerdir);
		$image = $this->create_image($formerdir, $image_info['mime']);

		$width	= $image_info[0];
		$height	= $image_info[1];

		if($width <= $thumb_width && $height <= $thumb_height) {
			@copy($formerdir, $thumbdir);
			return true;
		} else {
			list($x, $y, $target_w, $target_h, $source_w, $source_h) = $this->_calc_width_height(
				$thumb_width, $thumb_height, $width, $height
			);
		}

		$im = @imagecreatetruecolor($target_w, $target_h);
		@imagecopyresampled($im, $image, 0, 0, $x, $y, $target_w, $target_h, $source_w, $source_h);

		$this->output_image($im, $thumbdir, $image_info['mime']);
		@ImageDestroy($image);
		@ImageDestroy($im);
		return true;
	}

	protected function _calc_width_height($thumb_width, $thumb_height, $width, $height) {

		if (0 < $this->fix_width) {
			return $this->_calc_fix_width($thumb_width, $thumb_height, $width, $height);
		} elseif (0 < $this->fix_height) {
			return $this->_calc_fix_height($thumb_width, $thumb_height, $width, $height);
		}

		$target_ratio = $thumb_width / $thumb_height;
		$source_ratio = $width / $height;

		if($target_ratio >= $source_ratio) {
			return $this->_calc_fix_height($thumb_width, $thumb_height, $width, $height);
		} else {
			return $this->_calc_fix_width($thumb_width, $thumb_height, $width, $height);
		}
	}

	protected function _calc_fix_width($thumb_width, $thumb_height, $width, $height) {

		$target_ratio = $thumb_width / $thumb_height;
		$source_ratio = $width / $height;
		$target_w = $thumb_width;
		$target_w = $target_w > $width ? $width : $target_w;
		$target_h = floor($target_w / $source_ratio);
		$x = $y = 0;
		$source_w = $width;
		$source_h = floor($source_w / $source_ratio);

		return array($x, $y, $target_w, $target_h, $source_w, $source_h);
	}

	protected function _calc_fix_height($thumb_width, $thumb_height, $width, $height) {

		$target_ratio = $thumb_width / $thumb_height;
		$source_ratio = $width / $height;
		$target_h = $thumb_height;
		$target_h = $target_h > $height ? $height : $target_h;
		$target_w = floor($target_h * $source_ratio);
		$x = floor(($width - ($height * $source_ratio)) / 2);
		$y = 0;
		$source_h = $height;
		$source_w = floor($source_h * $source_ratio);

		return array($x, $y, $target_w, $target_h, $source_w, $source_h);
	}
}

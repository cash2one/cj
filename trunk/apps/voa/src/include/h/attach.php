<?php
/**
 * 附件信息
 * $Author$
 * $Id$
 */

class voa_h_attach {

	/**
	 * 读取本地附件
	 * @param string $filename 文件路径
	 * @param int $wh 图片的宽/高
	 * @param number $range
	 */
	public static function get_local_file($filename, $wh, $range = 0) {
		/** 如果是图片 */
		if (0 < $wh) {
			self::get_image_file($filename, $wh);
		}

		$fp = fopen($filename, 'rb');
		if($fp) {
			fseek($fp, $range);
			if(function_exists('fpassthru')) {
				fpassthru($fp);
			} else {
				echo @fread($fp, filesize($filename));
			}
		}

		fclose($fp);
		flush();
		ob_flush();
		return true;
	}

	/**
	 * 获取实际图片地址
	 * @param string $filename 图片地址
	 * @param int $wh 图片的宽/高
	 * @return boolean
	 */
	public static function get_image_file(&$filename, $wh) {
		$widths = config::get('voa.attachment.thumb_widths');
		/** 宽度为 0, 或者不在配置范围内 */
		if (0 >= $wh || !in_array($wh, $widths)) {
			return true;
		}

		/** 如果源文件不存在 */
		if (!file_exists($filename)) {
			return true;
		}

		$thumb_filename = $filename.'_'.$wh.'_'.$wh.'.jpg';
		if (file_exists($thumb_filename)) {
			$filename = $thumb_filename;
			return true;
		}

		// 缩微图配置
		$sets = array(
			'widths' => config::get('voa.attachment.thumb_widths'),
			'quality' => config::get('voa.attachment.thumb_quality'),
			'fix_ratio' => config::get('voa.attachment.thumb_fix_ratio'),
			'fix_width' => config::get('voa.attachment.thumb_fix_width'),
			'fix_height' => config::get('voa.attachment.thumb_fix_height'),
			'dir' => config::get('voa.attachment.dir')
		);
		thumb::instance($sets)->go($filename, $wh, $wh);
		$filename = $thumb_filename;
		return true;
	}

	/**
	 * 给出附件的at_id返回附件访问的绝对url地址
	 * @param number $at_id 附件id
	 * @param int $width 宽度
	 * @return string
	 */
	public static function attachment_url($at_id, $width = 0) {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme.$sets['domain'].'/attachment/read/'.$at_id;
		if (0 < $width) {
			$url .= '/'.$width;
		}

		/** 临时去除 */
		// FIXME 临时去除附件浏览验证扰码
		//$url .= '?ts='.startup_env::get('timestamp').'&sig='.self::attach_sig_create($at_id);

		return $url;
	}

	/**
	 * 附件 sig 生成
	 * @param int $at_id 附件id
	 * @param int $timestamp 时间戳
	 */
	public static function attach_sig_create($at_id, $timestamp = 0) {

		return voa_h_func::sig_create($at_id, $timestamp);
	}

	/**
	 * 验证 sig
	 * @param int $at_id 附件id
	 * @param int $timestamp 时间戳
	 * @param string $sig 验证串
	 * @return boolean
	 */
	public static function attach_sig_check($at_id, $timestamp, $sig) {
		return $sig == self::attach_sig_create($at_id, $timestamp);
	}

}

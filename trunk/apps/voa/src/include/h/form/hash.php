<?php
/**
 * voa_h_form_hash
 *
 * $Author$
 * $Id$
 */

class voa_h_form_hash {

	/**
	 * _key
	 * 加密用的key
	 *
	 * @var string
	 */
	private static $_key = '';


	/**
	 * generate
	 * 生成hash
	 *
	 * @param  mixed $id
	 * @return string HASH串
	 */
	public static function generate($id, $timestamp = null, $random = '') {

		if (empty(self::$_key)) {
			self::$_key = config::get('voa.formhash_secret');
		}

		if (!$id) {
			//return false;
		}

		if (!$timestamp) {
			$timestamp = startup_env::get('timestamp');
		}

		if (empty($random)) {
			$random = random(8);
		}

		//$part = substr($timestamp, 0, 5);
		//$result = md5(md5($id.$part.self::$_key).self::$_key);
		$ymdhis = rgmdate($timestamp, 'YmdHis');
		$md5 = md5(md5($ymdhis . $random . self::$_key) . self::$_key);
		$result = substr($md5, 0, 2) . substr($md5, -8) . $ymdhis . $random;

		return $result;
	}

	/**
	 * check
	 * 检查hash是否正确
	 *
	 * @param  mixed $id 当前表单的唯一串
	 * @param  string $hash HASH串
	 * @return boolean
	 */
	public static function check($id, $hash) {

		if (!$id) {
			//return false;
		}

		$timestamp = rstrtotime(preg_replace('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '\1-\2-\3 \4:\5:\6', substr($hash, 10, 14)));
		$random = substr($hash, -8);
		//$time = startup_env::get('timestamp');
		return self::generate($id, $timestamp, $random) == $hash;
	}

}

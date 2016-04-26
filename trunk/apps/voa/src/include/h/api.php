<?php
/**
 * Api 调用
 * @author zhuxun37
 */

class voa_h_api {

	// 秘钥
	protected $_secret = 'this is api secret';

	/**
	 * 初始化 api 类
	 * @return Ambigous <\PHPRPC_Client>
	 */
	public static function instance($secret = '') {

		static $instance = array();
		$secret = empty($secret) ? config::get('voa.api_secret') : $secret;
		$key = md5($secret);
		if (!isset($instance[$key])) {
			$instance[$key] = new voa_h_api($secret);
		}

		return $instance[$key];
	}

	// 初始化
	public function __construct($secret) {

		$this->_secret = $secret;
	}

	// get 请求
	public function getapi(&$result, $url, $data) {

		$ts = time();
		$data['sig'] = $this->create($data, $ts);
		$data['ts'] = $ts;
		$result = array();
		return voa_h_func::get_json_by_post_and_header($result, $url, $data, array(), 'GET');
	}

	// post 请求
	public function postapi(&$result, $url, $data) {

		$ts = time();
		$data['sig'] = $this->create($data, $ts);
		$data['ts'] = $ts;
		$result = array();
		return voa_h_func::get_json_by_post($result, $url, $data);
	}

	/**
	 * 生成 sig 值, 需要注意的是, $source 里面不要加时间戳和秘钥, 不能使用 ts 和 sig 键值
	 * @param mixed $source 源字串
	 * @param int $timestamp 时间戳
	 * @return string
	 */
	public function create($source, $timestamp = 0, $authkey = '') {

		// 强制转换成数组
		$source = (array)$source;
		if (!empty($source['ts']) && 0 >= $timestamp) {
			$timestamp = $source['ts'];
		}

		unset($source['ts'], $source['sig']);
		// 参数数组
		$source[] = 0 >= $timestamp ? NOW_TIME : $timestamp;
		if (empty($authkey)) {
			$source[] = $this->_secret;
		} else {
			$source[] = $authkey;
		}

		// 排序
		sort($source, SORT_STRING);

		return sha1(implode($source));
	}

}

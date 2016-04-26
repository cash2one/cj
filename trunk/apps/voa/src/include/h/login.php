<?php
/**
 * Class voa_h_login
 * 登录
 * @create-time: 2015-06-19
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */

class voa_h_login {

	/**
	 * 生成sig值
	 * @param $source 源字串
	 * @param int $timestamp 时间戳
	 * @return string 加密字串
	 */
	public static function sig_create($source, &$timestamp = 0) {

		// 强制转换成数组
		$source = (array)$source;
		// 如果没传时间戳
		$timestamp = 0 < $timestamp ? $timestamp : startup_env::get('timestamp');
		// 取秘钥
		$secret = config::get(startup_env::get('app_name') . '.xdf_auth_key');

		// 参数数组
		$source[] = $timestamp;
		$source[] = $secret;

		// 排序
		sort($source, SORT_STRING);

		return sha1(implode($source));
	}

	/**
	 * 检查默认 sig
	 * @param array $params 外部参数
	 * @return bool
	 */
	public static function sig_check($params) {
		$ts = 0;
		$sig = '';
		// 取出时间戳和 sig
		if(!empty($params['ts'])){
			$ts = (int)$params['ts'];
		}
		if(!empty($params['sig'])){
			$sig = (string)$params['sig'];
		}

		// 删除键值
		unset($params['ts'], $params['sig']);

		// 如果 sig 不相等
		if ($sig != self::sig_create($params, $ts)) {
			return false;
		}

		return true;
	}

	/**
	 * 生成code
	 */
	public static function code_create() {

		//获取配置认证秘钥
		$auth_key = config::get("voa.xdf_auth_key");

		//加密，生成code
		$scode = sha1('auth_key = ' . $auth_key . ' & timestamp = ' . startup_env::get('timestamp'));

		return $scode;
	}
}

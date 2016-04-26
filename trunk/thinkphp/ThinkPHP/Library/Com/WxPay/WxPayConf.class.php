<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/10
 * Time: 下午3:16
 */

namespace Com\WxPay;

class WxPayConf {

	//=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	public static $appid = '';
	//受理商ID，身份标识
	public static $mchid = '';
	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	public static $key = '';
	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	public static $appsecret = '';

	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	const SSLCERT_PATH = '/xxx/xxx/xxxx/WxPayPubHelper/cacert/apiclient_cert.pem';
	const SSLKEY_PATH = '/xxx/xxx/xxxx/WxPayPubHelper/cacert/apiclient_key.pem';

	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
	public static $notify_url = 'https://test.vchangyi.com/wepay.php';

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	public static $curl_timeout = 30;

	static function instance() {

		static $object;
		if (empty($object)) {
			$object = new self();
		}

		return $object;
	}

	public function __construct() {

		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');

		self::$appid = isset($sets['mp_appid']) ? $sets['mp_appid'] : '';
		self::$appsecret = isset($sets['mp_appsecret']) ? $sets['mp_appsecret'] : '';
		self::$mchid = isset($sets['mchid']) ? $sets['mchid'] : '';
		self::$key = isset($sets['mchkey']) ? $sets['mchkey'] : '';
		if (!empty($sets['domain'])) {
			self::$notify_url = sprintf(cfg('NOTIFY_URL'), $sets['domain']);
		}
		self::$curl_timeout = cfg('CURL_TIMEOUT');
	}
}

WxPayConf::instance();
<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/10
 * Time: 下午4:48
 */

namespace Com\WxPay;

/**
 * JSAPI支付——H5网页端调起支付接口
 */
class JsApi extends CommonUtil {
	var $code;//code码，用以获取openid
	var $openid;//用户的openid
	var $parameters;//jsapi参数，格式为json
	var $prepay_id;//使用统一支付接口得到的预支付id
	var $curl_timeout;//curl超时时间

	function __construct() {

		//设置curl超时时间
		$this->curl_timeout = WxPayConf::$curl_timeout;
	}

	/**
	 *    作用：生成可以获得code的url
	 */
	function createOauthUrlForCode($redirectUrl) {

		$urlObj["appid"] = WxPayConf::$appid;
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE" . "#wechat_redirect";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);

		return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;
	}

	/**
	 *    作用：生成可以获得openid的url
	 */
	function createOauthUrlForOpenid() {

		$urlObj["appid"] = WxPayConf::$appid;
		$urlObj["secret"] = WxPayConf::$appsecret;
		$urlObj["code"] = $this->code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);

		return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
	}


	/**
	 *    作用：通过curl向微信提交code，以获取openid
	 */
	function getOpenid() {

		$url = $this->createOauthUrlForOpenid();
		//初始化curl
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOP_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//运行curl，结果以jason形式返回
		$res = curl_exec($ch);
		curl_close($ch);
		//取出openid
		$data = json_decode($res, true);
		$this->openid = $data['openid'];

		return $this->openid;
	}

	/**
	 *    作用：设置prepay_id
	 */
	function setPrepayId($prepayId) {

		$this->prepay_id = $prepayId;
	}

	/**
	 *    作用：设置code
	 */
	function setCode($code_) {

		$this->code = $code_;
	}

	/**
	 *    作用：设置jsapi的参数
	 */
	public function getParameters() {

		$jsApiObj["appId"] = WxPayConf::$appid;
		$timeStamp = time();
		$jsApiObj["timeStamp"] = "$timeStamp";
		$jsApiObj["nonceStr"] = $this->createNoncestr(6);
		$jsApiObj["package"] = "prepay_id=$this->prepay_id";
		$jsApiObj["signType"] = "MD5";
		$jsApiObj["paySign"] = $this->getSign($jsApiObj);
		$this->parameters = $jsApiObj;

		return $this->parameters;
	}
}
<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/10
 * Time: 下午4:33
 */

namespace Com\WxPay;

use Think\Log;

/**
 * 订单查询接口
 */
class OrderQuery extends WxpayClient {

	function __construct() {

		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/pay/orderquery";
		//设置curl超时时间
		$this->curl_timeout = WxPayConf::$curl_timeout;
	}

	/**
	 * 生成接口参数xml
	 */
	function createXml() {

		try {
			//检测必填参数
			if ($this->parameters["out_trade_no"] == null && $this->parameters["transaction_id"] == null) {
				throw new \Exception ("订单查询接口中，out_trade_no、transaction_id至少填一个！" . "<br>");
			}

			$this->parameters["appid"] = WxPayConf::$appid;//公众账号ID
			$this->parameters["mch_id"] = WxPayConf::$mchid;//商户号
			$this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
			$this->parameters["sign"] = $this->getSign($this->parameters);//签名
			return $this->arrayToXml($this->parameters);
		} catch (\Exception $e) {
			Log::record($e->getMessage(), Log::ERR);
			die;
		}
	}

}
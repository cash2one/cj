<?php
/**
 * 微信支付接口基类
 * $Author$
 * $Id$
 */

class voa_wepay_base {


	public function __construct() {

		return true;
	}

	/**
	 * 	作用：产生随机字符串，不长于32位
	 */
	public function _create_nonce_str($length = 10) {

		$chars = "0123456789";
		$str = "";
		for ( $i = 0; $i < $length; $i++ )  {
			$str.= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}

		return $str;
	}

	/**
	 * 	作用：格式化参数，签名过程需要使用
	 */
	protected function _fmt_biz_q_para_map($para_map, $urlencode) {

		$buff = "";
		ksort($para_map);
		foreach ($para_map as $k => $v) {
		    if($urlencode) {
			   $v = urlencode($v);
			}

			$buff .= $k . "=" . $v . "&";
		}

		$req_par;
		if (strlen($buff) > 0) {
			$req_par = substr($buff, 0, strlen($buff) - 1);
		}

		return $req_par;
	}

	/**
	 * 作用：设置地址签名
	 *
	 */
	protected function _get_address_sign($infolist) {

		$js_api_obj["accesstoken"] = $infolist['accesstoken'];
		$js_api_obj["appId"] = $infolist['appid'];
		$js_api_obj["nonceStr"] = $infolist['nonceStr'];
	    $js_api_obj["timeStamp"] = $infolist['timeStamp'];;
		$js_api_obj["url"] = $infolist['url'];

		$params = array();
	    foreach ($js_api_obj as $k => $v) {
	    	$params[strtolower($k)] = $v;
	    }

	    //签名步骤一：按字典序排序参数
	    ksort($params);
	    $string = $this->_fmt_biz_q_para_map($params, false);
	    $result_ = sha1($string);
	    return $result_;
	}
}

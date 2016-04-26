<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/10
 * Time: 下午4:27
 */

/**
 * 微信支付帮助库
 * ====================================================
 * 接口分三种类型：
 * 【请求型接口】--Wxpay_client_
 *        统一支付接口类--UnifiedOrder
 *        订单查询接口--OrderQuery
 *        退款申请接口--Refund
 *        退款查询接口--RefundQuery
 *        对账单接口--DownloadBill
 *        短链接转换接口--ShortUrl
 * 【响应型接口】--Wxpay_server_
 *        通用通知接口--Notify
 *        Native支付——请求商家获取商品信息接口--NativeCall
 * 【其他】
 *        静态链接二维码--NativeLink
 *        JSAPI支付--JsApi
 * =====================================================
 * 【CommonUtil】常用工具：
 *        trimString()，设置参数时需要用到的字符处理函数
 *        createNoncestr()，产生随机字符串，不长于32位
 *        formatBizQueryParaMap(),格式化参数，签名过程需要用到
 *        getSign(),生成签名
 *        arrayToXml(),array转xml
 *        xmlToArray(),xml转 array
 *        postXmlCurl(),以post方式提交xml到对应的接口url
 *        postXmlSSLCurl(),使用证书，以post方式提交xml到对应的接口url
 */

namespace Com\WxPay;

/**
 * 所有接口的基类
 */
class CommonUtil {

	function __construct() {
		// do nothing.
	}

	function trimString($value) {

		$ret = null;
		if ($value != null) {
			$ret = $value;
			if (strlen($ret) == 0) {
				$ret = null;
			}
		}

		return $ret;
	}

	/**
	 *    作用：产生随机字符串，不长于32位
	 */
	public function createNoncestr($length = 32) {

		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i ++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}

		return $str;
	}

	/**
	 *    作用：格式化参数，签名过程需要使用
	 */
	function formatBizQueryParaMap($paraMap, $urlencode) {

		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v) {
			if ($urlencode) {
				$v = urlencode($v);
			}
			$buff .= $k . "=" . $v . "&";
		}

		$reqPar = '';
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff) - 1);
		}

		return $reqPar;
	}

	/**
	 *    作用：生成签名
	 */
	public function getSign($obj) {

		//签名步骤一：按字典序排序参数
		ksort($obj);

		$String = $this->formatBizQueryParaMap($obj, false);

		//签名步骤二：在string后加入KEY
		$String = $String . "&key=" . WxPayConf::$key;

		//签名步骤三：MD5加密
		$String = md5($String);

		//签名步骤四：所有字符转为大写
		$result_ = strtoupper($String);

		return $result_;
	}

	/**
	 *    作用：array转xml
	 */
	function arrayToXml($arr) {

		$xml = "<xml>";
		foreach ($arr as $key => $val) {
			if (is_numeric($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			} else {
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}
		}

		$xml .= "</xml>";

		return $xml;
	}

	/**
	 *    作用：将xml转为array
	 */
	public function xmlToArray($xml) {

		//将XML转为array
		$array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

		return $array_data;
	}

	/**
	 *    作用：以post方式提交xml到对应的接口url
	 */
	public function postXmlCurl($xml, $url, $second = 30) {

		//初始化curl
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, false);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		curl_close($ch);
		//返回结果
		if ($data) {
			//curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			\Think\Log::record("curl出错，错误码:$error" . "<br>");
			\Think\Log::record("<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>");
			curl_close($ch);

			return false;
		}
	}

	/**
	 *    作用：使用证书，以post方式提交xml到对应的接口url
	 */
	function postXmlSSLCurl($xml, $url, $second = 30) {

		$ch = curl_init();
		//超时时间
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, false);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//设置证书
		//使用证书：cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLCERT, WxPayConf::SSLCERT_PATH);
		//默认格式为PEM，可以注释
		curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLKEY, WxPayConf::SSLKEY_PATH);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$data = curl_exec($ch);
		//返回结果
		if ($data) {
			curl_close($ch);

			return $data;
		} else {
			$error = curl_errno($ch);
			\Think\Log::record("curl出错，错误码:$error" . "<br>");
			\Think\Log::record("<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>");
			curl_close($ch);

			return false;
		}
	}

	/**
	 *    作用：打印数组
	 */
	function printErr($wording = '', $err = '') {

		\Think\Log::record($wording);
		\Think\Log::record(var_export($err, true));
	}
}
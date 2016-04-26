<?php
/**
 * 微信红包类
 */
namespace Common\Common\Wepay;
class voa_wepay_redpack {
	// 红包参数
	public $parameters = array();
	// 请求的错误信息
	public $errmsg = '';
	// 请求的错误码
	public $errcode = 0;
	// 红包密钥
	private $__mchkey; // 密角
	// 发送红包的接口url
	const SEND_REDPACK_URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
	// domain
	protected $_domain = '';

	public function __construct() {

		// 获取商户号和密钥
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->__mchkey = $sets['mchkey'];

		// 获取二级域名
		$domains = explode('.', $sets['domain']);
		$this->_domain = $domains[0];

		// 获取红包配置
		$p_sets = voa_h_cache::get_instance()->get('plugin.redpack.setting', 'oa');

		// 配置参数
		$this->parameters = array(
			//'mch_id' => $sets['mchid'],
			//'mch_billno' => voa_h_redpack::billno($sets['mchid']),
			'wxappid' => $p_sets['wxappid'],
			'nonce_str' => random(16)
		);
	}

	/**
	 * 参数赋值
	 *
	 * @param unknown $options
	 */
	private function __set_parameter($options) {

		$this->parameters = array_merge($this->parameters, $options);
	}

	/**
	 * 获取给定的参数值
	 *
	 * @param string $parameter
	 * @return multitype:
	 */
	private function __get_parameter($parameter) {

		return $this->parameters[$parameter];
	}

	/**
	 * 签名参数是否定义检查
	 *
	 * @return boolean
	 */
	private function __check_sign_parameters() {

		if ($this->parameters["nonce_str"] == null || $this->parameters["mch_billno"] == null
				|| $this->parameters["mch_id"] == null || $this->parameters["wxappid"] == null
				|| $this->parameters["nick_name"] == null || $this->parameters["send_name"] == null
				|| $this->parameters["re_openid"] == null || $this->parameters["total_amount"] == null
				|| $this->parameters["max_value"] == null || $this->parameters["total_num"] == null
				|| $this->parameters["wishing"] == null || $this->parameters["client_ip"] == null
				|| $this->parameters["act_name"] == null || $this->parameters["remark"] == null
				|| $this->parameters["min_value"] == null) {
			return false;
		}

		return true;
	}

	/**
	 * 获取签名字符串
	 * 例如：
	 * appid： wxd930ea5d5a258f4f
	 * mch_id： 10000100
	 * device_info： 1000
	 * Body： test
	 * nonce_str： ibuaiVcKdpRxkhJA
	 * 第一步：对参数按照 key=value 的格式，并按照参数名 ASCII 字典序排序如下：
	 * stringA="appid=wxd930ea5d5a258f4f&body=test&device_info=1000&mch_i
	 * d=10000100&nonce_str=ibuaiVcKdpRxkhJA";
	 * 第二步：拼接支付密钥：
	 * stringSignTemp="stringA&key=192006250b4c09247ec02edce69f6a2d"
	 * sign=MD5(stringSignTemp).toUpperCase()="9A0A8659F005D6984697E2CA0A
	 * 9CF3B7"
	 *
	 * @return boolean|string
	 */
	private function __get_sign() {

		if (null == $this->__mchkey || '' == $this->__mchkey) {
			return $this->__set_error(70010, '红包密钥不能为空！');
		}

		if ($this->__check_sign_parameters() == false) { // 检查生成签名参数
			return $this->__set_error(70011, '生成签名参数缺失！');
		}

		ksort($this->parameters);
		$unSignParaString = $this->formatQueryParaMap($this->parameters);
		$signStr = $unSignParaString . "&key=" . $this->__mchkey;
		return strtoupper(md5($signStr));
	}

	// 格式化签名参数
	private function formatQueryParaMap($paraMap) {

		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v) {
			if (null != $v && "null" != $v && "sign" != $k) {
				$buff .= $k . "=" . $v . "&";
			}
		}

		$reqPar = '';
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff) - 1);
		}

		return $reqPar;
	}

	/**
	 * 生成红包接口XML信息
	 *
	 * @param number $retcode
	 * @param string $reterrmsg
	 * @return boolean|string
	 */
	private function __create_redpack_xml($retcode = 0, $reterrmsg = "ok") {

		$sign = $this->__get_sign();
		if (! $sign) {
			return false;
		}

		$this->__set_parameter(array('sign' => $sign));
		return $this->__array_to_xml($this->parameters);
	}

	/**
	 * 数组转XML格式
	 *
	 * @param array $arr
	 * @return string
	 */
	private function __array_to_xml(array $arr) {

		$xml = "<xml>";
		foreach ($arr as $key => $val) {
			if (is_numeric($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			} else
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
		}

		$xml .= "</xml>";
		return $xml;
	}

	/**
	 * 使用证书发送post请求
	 *
	 * @param string $vars
	 * @param number $timeout 请求超时时间，单位：秒，默认：30秒
	 * @param array $aHeader 额外的header头信息
	 * @return mixed|boolean
	 */
	private function __curl_post_ssl($vars, $timeout = 20, $aHeader = array()) {

		$ch = curl_init();
		// 超时时间
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, self::SEND_REDPACK_URL);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		// ssl证书
		curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLCERT, ROOT_PATH . '/apps/voa/src/config/wepay/'.$this->_domain.'/apiclient_cert.pem');
		curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLKEY, ROOT_PATH . '/apps/voa/src/config/wepay/'.$this->_domain.'/apiclient_key.pem');
		curl_setopt($ch, CURLOPT_CAINFO, ROOT_PATH . '/apps/voa/src/config/wepay/'.$this->_domain.'/rootca.pem');

		if (count($aHeader) >= 1) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		$data = curl_exec($ch);
		logger::error($data);
		if ($data) {
			curl_close($ch);
			return $data;
		} else {
			$errno = curl_errno($ch);
			$err = curl_error($ch);
			curl_close($ch);
			logger::error(self::SEND_REDPACK_URL . '|' . print_r($vars, true) . '|' . $errno . '|' . $err);
			return $this->__set_error(70012, '服务器繁忙, 请稍后再试', $errno);
		}
	}

	/**
	 * 根据参数发送红包
	 *
	 * @param array $options $options = array(
	 *        'nick_name' => '玫琳凯', //提供方名称
	 *        'send_name' => '玫琳凯', //红包发送者名称
	 *        're_openid' => $openid, //接收者
	 *        'total_amount' => $money, //付款金额，单位分,和min_value,max_value必须一样
	 *        'min_value' => $money,
	 *        'max_value' => $money,
	 *        'total_num' => 1, //人数只能为1
	 *        'wishing' => '感谢您参与投票！',
	 *        'client_ip' => '127.0.0.1',
	 *        'act_name' => "投票抢红包活动", //活劢名称
	 *        'remark' => '感谢您参与投票', //备注信息
	 *        );
	 */
	public function send($options, &$send_result = array()) {

		$this->__set_parameter($options);
		$postXml = $this->__create_redpack_xml();
		if ($this->errcode) {
			return false;
		}

		$responseXml = $this->__curl_post_ssl($postXml);
		if ($this->errcode) {
			return false;
		}

		if ($responseXml === false) {
			return false;
		}

		try {
			$r = simplexml_load_string($responseXml, 'SimpleXMLElement');
		} catch (Exception $e) {
			logger::error("1:".$postXml."\n---------------\n".$responseXml);
		}

		// 网络错误
		if (empty($r->return_code)) {
			logger::error("2:".$postXml."\n---------------\n".$responseXml);
			return $this->__set_error(7000, '与微信通讯发生意外网络错误');
		}

		// 通讯失败
		if (rstrtolower($r->return_code) != 'success') {
			logger::error("3:".$postXml."\n---------------\n".$responseXml);
			return $this->__set_error(70001, '服务器繁忙, 稍候再试');
		}

		// 通讯成功，处理交易结果
		if (empty($r->result_code)) {
			$self_errcode = 70002;
			$errmsg = '未知的微信错误结果';
			$errcode = 0;
			if (! empty($r->err_code)) {
				$self_errcode = 70002;
				$errmsg = '服务器繁忙, 请稍候再试';
				$errcode = $r->err_code;
			}

			logger::error("4:".$postXml."\n---------------\n".$responseXml);
			return $this->__set_error($self_errcode, $errmsg, $errcode);
		}

		// 交易错误
		if (rstrtolower($r->result_code) != 'success') {
			logger::error("5:".$postXml."\n---------------\n".$responseXml);
			return $this->__set_error(70003, "服务器繁忙, 请稍候再试", $r->err_code);
		}

		// 交易成功，返回的结果集
		$send_result = array(
			'mch_billno' => (string)$r->mch_billno,  // 商户订单号
			'mch_id' => (string)$r->mch_id,  // 商户号
			'wxappid' => (string)$r->wxappid,  // 公众账号appid
			're_openid' => (string)$r->re_openid,  // 用户openid
			'total_amount' => (string)$r->total_amount
		); // 付款金额

		logger::error("send success");
		return true;
	}

	/**
	 * 设置错误信息
	 *
	 * @param number $self_errcode
	 * @param string $errmsg
	 * @param string $errcode
	 */
	private function __set_error($self_errcode, $errmsg, $errcode = '') {

		$this->errcode = $self_errcode;
		$this->errmsg = ($errcode ? $errcode . ':' : '').$errmsg;
		return $self_errcode > 0 ? false : true;
	}

}

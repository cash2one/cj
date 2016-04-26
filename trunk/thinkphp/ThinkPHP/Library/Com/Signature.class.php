<?php
/**
 * 企业号接口, 前面操作
 * Signature.class.php
 * $author$
 */

namespace Com;
use Think\Log;

class Signature {

	// Wxqy Service 方法
	protected $_serv = null;
	// xml 默认模板
	protected $_xml_tpl = '<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>';

	public function __construct(&$serv) {

		$this->_serv = $serv;
	}

	// 检测来自微信请求的URL是否有效
	public function check_signature() {

		// 如果有值, 则说明是加密消息
		$sig = I('get.msg_signature', '');
		if (empty($sig)) {
			return $this->check_signature_v1();
		} else {
			return $this->check_signature_v2();
		}
	}

	/**
	 * 第一版验证
	 *
	 * @return boolean
	 */
	public function check_signature_v1() {

		// 获取参数
		$signature = I("get.signature", '');
		$timestamp = I("get.timestamp", 0);
		$nonce = I("get.nonce", '');

		// 计算 hash 签名
		$tmpArr = array($this->_serv->get_token(), (string)$timestamp, (string)$nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		// 判断签名是否正确
		if ($tmpStr == $signature) {
			$this->_serv->retstr = I('get.echostr', 'ok');
			return true;
		} else {
			Log::record("check signature error:{$signature}\t{$timestamp}\t{$nonce}");
			return false;
		}
	}

	/**
	 * 第二版验证
	 *
	 * @return boolean
	 */
	public function check_signature_v2() {

		// 获取参数
		$signature = I("get.msg_signature", '');
		$timestamp = I("get.timestamp", 0);
		$nonce = I("get.nonce", '');
		// 鉴权字串
		$echostr = '';
		// XML
		$xml = '';
		// 获取鉴权字串和XML
		if (!$this->_get_echostr_xml($echostr, $xml)) {
			return false;
		}

		// 获取加密配置信息
		$sets = array();
		if (!$this->_serv->get_sets($sets)) {
			return false;
		}

		$qywx_cb = \Com\Wechat\QywxCallback::instance($sets);
		list($errno, $content) = $qywx_cb->from_tx($xml, $signature, $nonce, $timestamp);
		if (0 == $errno) { // 接收成功
			if (empty($echostr)) { // 非鉴权消息
				$this->_serv->set_xml_from_wx($content);
			} else {
				$this->_serv->retstr = $content;
			}

			return true;
		} else {
			Log::record('error:' . $errno . "\n" . var_export($_GET, true) . "\n" . $xml);
			return false;
		}
	}

	/**
	 * 获取鉴权字串和XML
	 * @param string $echostr 鉴权字串
	 * @param string $xml XML
	 * @return boolean
	 */
	protected function _get_echostr_xml(&$echostr, &$xml) {

		$echostr = I("get.echostr", '');
		if (empty($echostr)) { // 非鉴权请求
			$xml = (string)file_get_contents("php://input");
			$xml_tree = new DOMDocument();
			$xml_tree->loadXML($xml);
			$array_e = $xml_tree->getElementsByTagName('Encrypt');
		} else {
			$format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
			$xml = sprintf($format, $echostr);
		}

		return true;
	}

}

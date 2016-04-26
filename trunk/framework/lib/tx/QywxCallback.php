<?php
/**
 * 对公众平台发送给公众账号的消息加解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */

// ------------------------------------------------------------------------
/**
 * error code 说明：
 -40001 ： 签名验证错误
 -40002 :  xml解析失败
 -40003 :  sha加密生成签名失败
 -40004 :  encodingAesKey 非法
 -40005 :  appid 校验错误
 -40006 :  aes 加密失败
 -40007 ： aes 解密失败
 -40008 ： 解密后得到的buffer非法
 */

/**
 * cb_sha1 class
 *
 * 计算公众平台的消息签名接口.
 */
class cb_sha1 {
	/**
	 * 用SHA1算法生成安全签名
	 * @param string $token 票据
	 * @param string $timestamp 时间戳
	 * @param string $nonce 随机字符串
	 * @param string $encrypt 密文消息
	 */
	public function getSHA1($token, $timestamp, $nonce, $encrypt_msg) {
		//排序
		try {
			$array = array($encrypt_msg, $token, $timestamp, $nonce);
			sort($array, SORT_STRING);
			$str = implode($array);
			return array(0, sha1($str));
		} catch (Exception $e) {
			print $e . "\n";
			return array(-40003, null);
		}
	}

}

/**
 * cb_xml_parse class
 *
 * 提供提取消息格式中的密文及生成回复消息格式的接口.
 */
class cb_xml_parse {

	/**
	 * 提取出xml数据包中的加密消息
	 * @param string $xmltext 待提取的xml字符串
	 * @return string 提取出的加密消息字符串
	 */
	public function extract($xmltext) {
		try {
			$xml = new DOMDocument();
			$xml->loadXML($xmltext);
			$array_e = $xml->getElementsByTagName('Encrypt');
			$array_a = $xml->getElementsByTagName('ToUserName');
			$encrypt = $array_e->item(0)->nodeValue;
			$tousername = $array_a->item(0)->nodeValue;
			return array(0, $encrypt, $tousername);
		} catch(Exception $e) {
			print $e . "\n";
			return array(-40002, null, null);
		}
	}

	/**
	 * 生成xml消息
	 * @param string $encrypt 加密后的消息密文
	 * @param string $signature 安全签名
	 * @param string $timestamp 时间戳
	 * @param string $nonce 随机字符串
	 */
	public function generate($encrypt, $signature, $timestamp, $nonce) {
		$format = "<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MsgSignature><![CDATA[%s]]></MsgSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>";
		return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
	}

}

/**
 * cb_pkcs7encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class cb_pkcs7encoder {
	public static $block_size = 32;

	/**
	 * 对需要加密的明文进行填充补位
	 * @param $text 需要进行填充补位操作的明文
	 * @return 补齐明文字符串
	 */
	function encode($text) {
		$block_size = cb_pkcs7encoder::$block_size;
		$text_length = strlen($text);
		//计算需要填充的位数
		$amount_to_pad = cb_pkcs7encoder::$block_size - ($text_length % cb_pkcs7encoder::$block_size);
		if ($amount_to_pad == 0) {
			$amount_to_pad = cb_pkcs7encoder::block_size;
		}
		//获得补位所用的字符
		$pad_chr = chr($amount_to_pad);
		$tmp = "";
		for ($index = 0; $index < $amount_to_pad; $index++) {
			$tmp .= $pad_chr;
		}
		return $text . $tmp;
	}

	/**
	 * 对解密后的明文进行补位删除
	 * @param decrypted 解密后的明文
	 * @return 删除填充补位后的明文
	 */
	function decode($text) {

		$pad = ord(substr($text, -1));
		if ($pad < 1 || $pad > 31) {
			$pad = 0;
		}
		return substr($text, 0, (strlen($text) - $pad));
	}

}

/**
 * cb_prpcrypt class
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class cb_prpcrypt {
	public $key;

	function cb_prpcrypt($k) {
		$this->key = base64_decode($k . "=");
	}

	/**
	 * 对明文进行加密
	 * @param string $text 需要加密的明文
	 * @return string 加密后的密文
	 */
	public function encrypt($text, $appid) {

		try {
			//获得16位随机字符串，填充到明文之前
			$random = $this->getRandomStr();
			$text = $random . pack("N", strlen($text)) . $text . $appid;
			// 网络字节序
			$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
			$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			$iv = substr($this->key, 0, 16);
			//使用自定义的填充方式对明文进行补位填充
			$pkc_encoder = new cb_pkcs7encoder;
			$text = $pkc_encoder->encode($text);
			mcrypt_generic_init($module, $this->key, $iv);
			//加密
			$encrypted = mcrypt_generic($module, $text);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
			//使用BASE64对加密后的字符串进行编码
			return array(0, base64_encode($encrypted));
		} catch(Exception $e) {
			print $e;
			return array(-40006, null);
		}
	}

	/**
	 * 对密文进行解密
	 * @param string $encrypted 需要解密的密文
	 * @return string 解密得到的明文
	 */
	public function decrypt($encrypted, $appid) {

		try {
			//使用BASE64对需要解密的字符串进行解码
			$ciphertext_dec = base64_decode($encrypted);
			$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			$iv = substr($this->key, 0, 16);
			mcrypt_generic_init($module, $this->key, $iv);
			//解密
			$decrypted = mdecrypt_generic($module, $ciphertext_dec);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
		} catch(Exception $e) {
			return array(-40007, null);
		}
		try {
			//去除补位字符
			$pkc_encoder = new cb_pkcs7encoder;
			$result = $pkc_encoder->decode($decrypted);
			//去除16位随机字符串,网络字节序和AppId
			if (strlen($result) < 16)
				return "";
			$content = substr($result, 16, strlen($result));
			$len_list = unpack("N", substr($content, 0, 4));
			$xml_len = $len_list[1];
			$xml_content = substr($content, 4, $xml_len);
			$from_appid = substr($content, $xml_len + 4);
		} catch(Exception $e) {
			print $e;
			return array(-40008, null);
		}
		if (trim($from_appid) != $appid)
			return array(-40005, null);
		return array(0, $xml_content);

	}

	/**
	 * 随机生成16位字符串
	 * @return string 生成的字符串
	 */
	function getRandomStr() {

		$str = "";
		$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($str_pol) - 1;
		for ($i = 0; $i < 16; $i++) {
			$str .= $str_pol[mt_rand(0, $max)];
		}
		return $str;
	}

}

/**
 * 加密/解密消息
 */
class qywx_callback {
	// AES key
	protected $_aes_key = '';
	// token
	protected $_token = '';
	// corpid
	protected $_corp_id = '';
	// nonce from tx
	protected $_nonce = '';
	// timestamp from tx
	protected $_timestamp = '';

	static function &instance($sets = array()) {
		static $object;
		if(empty($object)) {
			$object	= new self($sets);
		}

		return $object;
	}

	public function __construct($sets) {

		$this->_corp_id = $sets['corp_id'];
		$this->_token = $sets['token'];
		$this->_aes_key = $sets['aes_key'];
		$this->_timestamp = time();
	}

	/**
	 * 公众平台发送消息给第三方（第三方接收公众平台消息）
	 * 1. 利用收到的密文生成安全签名,进行签名验证;
	 * 2. 若验证通过，则提取xml中的加密消息；
	 * 3. 对消息进行解密。
	 * @param string $text xml格式加密消息
	 * @param string $msg_sign 正确的安全签名
	 * @return string 解密后的明文
	 */
	public function from_tx($text, $msg_sign, $nonce, $timestamp = null) {

		// 判断密钥长度是否正确
		if (strlen($this->_aes_key) != 43) {
			return array(-40004, null);
		}

		$pc = new cb_prpcrypt($this->_aes_key);
		// 提取密文
		$xmlparse = new cb_xml_parse;
		$array = $xmlparse->extract($text);
		$ret = $array[0];
		if ($ret != 0) {
			return array($ret, null);
		}

		if ($timestamp == null) {
			$timestamp = time();
		}

		$this->_nonce = $nonce;
		$this->_timestamp = $timestamp;

		$encrypt = $array[1];
		$touser_name = $array[2];
		// 验证安全签名
		$sha1 = new cb_sha1;
		$array = $sha1->getSHA1($this->_token, $timestamp, $nonce, $encrypt);
		$ret = $array[0];
		if ($ret != 0) {
			return array($ret, null);
		}

		$signature = $array[1];
		if ($signature != $msg_sign) {
			return array(-40001, null);
		}

		return $pc->decrypt($encrypt, $this->_corp_id);
	}

	/**
	 * 回复消息给公众平台
	 * 1. 对要发送的消息进行AES-CBC加密；
	 * 2. 生成安全签名；
	 * 3. 将消息密文和安全签名打包成xml格式。
	 */
	public function to_tx($text, $timestamp) {

		$pc = new cb_prpcrypt($this->_aes_key);
		// 加密
		$array = $pc->encrypt($text, $this->_corp_id);
		$ret = $array[0];
		if ($ret != 0) {
			return array($ret, null);
		}

		if ($timestamp == null) {
			$timestamp = $this->_timestamp;
		}

		$encrypt = $array[1];
		// 生成安全签名
		$sha1 = new cb_sha1;
		$array = $sha1->getSHA1($this->_token, $timestamp, $this->_nonce, $encrypt);
		$ret = $array[0];
		if ($ret != 0) {
			return array($ret, null);
		}

		$signature = $array[1];
		// 生成发送的xml
		$xmlparse = new cb_xml_parse;
		$xmltext = $xmlparse->generate($encrypt, $signature, $timestamp, $this->_nonce);
		return array(0, $xmltext);
	}
}





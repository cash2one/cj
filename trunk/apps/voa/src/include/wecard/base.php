<?php
/**
 * 门禁操作接口基类
 * $Author$
 * $Id$
 */

class voa_wecard_base {
	// 开门代码
	protected $_open_code = '19400000%s%02d00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';

	// 设置门禁内网ip
	protected $_set_ip = '19960000%s%sFFFFFF00%s55AAAA5500000000000000000000000000000000000000000000000000000000000000000000000000000000';

	// 外网ip
	protected $_wanip = '';

	// 构造函数, 初始化
	public function __construct() {
		// do nothing.
	}

	/**
	 * 设置门禁ip(只在内网运行)
	 * @param int $sn 门禁标识
	 * @param string $ip ip
	 * @param string $netmask 子网掩码
	 * @param int $port 端口
	 * @return boolean
	 */
	protected function _setip($sn, $ip, $netmask, $port) {

		$code = sprintf($this->_set_ip, $this->_sn2bcd($sn, 8), $this->_ip2hex($ip), $this->_ip2hex($netmask));
		$this->_send_cmd($code, $port);
		return true;
	}

	/**
	 * 开指定门禁
	 * @param int $sn 门禁标识
	 * @param int $index 门禁编号
	 * @param int $post 端口
	 * @return boolean
	 */
	protected function _open($sn, $index, $ip, $port) {

		$code = sprintf($this->_open_code, $this->_sn2bcd($sn, 8), sprintf('%02d', $index));
		$this->_send_cmd($code, $ip, $port);
		return true;
	}

	/**
	 * 发送指令
	 * @param string $code 指令代码
	 * @param int $port 端口
	 */
	protected function _send_cmd($code, $ip, $port) {

		$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		$code = self::hex2string($code);
		$len = strlen($code);
		socket_sendto($sock, $code, $len, 0, $ip, $port);
		socket_close($sock);
	}

	/**
	 * ip转bcd码
	 * @param string $ip ip地址
	 * @return string
	 */
	protected function _ip2hex($ip) {

		$ips = explode('.', $ip);
		$ar = array();
		foreach ($ips as $_ip) {
			$ar[] = dechex($_ip);
		}

		return implode('', $ar);
	}

	/**
	 * 整型转bcd码
	 * @param int $sn 整型数值
	 * @return string
	 */
	protected function _sn2bcd($sn, $min_len = 0) {

		$hex = strtoupper(dechex($sn));
		if (0 < $len) {
			$hex = sprintf('%0' . $min_len . 's', $hex);
		}

		$len = strlen($hex);
		$ar = array();
		for ($i = 0; $i < 4; $i ++) {
			array_unshift($ar, sprintf('%02s', substr($hex, $i * 2, 2)));
		}

		return implode('', $ar);
	}

	/**
	 * 16进制转字串
	 *
	 * @param string $hex 16进制数
	 * @return string
	 */
	public static function hex2string($hex) {

		$string = '';
		for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
			$string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
		}

		return $string;
	}

	/**
	 * 字串转16进制
	 * @param string $string 字串
	 * @return string
	 */
	public static function string2hex($string) {

		$hex = '';
		for ($i = 0; $i < strlen($string); $i ++) {
			$hex .= dechex(ord($string[$i]));
		}

		return $hex;
	}
}

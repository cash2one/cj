<?php
/**
 * XXTEA 加密算法;
 *
 * $Author$
 * $Id$
 */

namespace Common\Common;
use Think\Log;

class Xxtea {

	var $_auth_key = '';

	/**
	 * 初始化;
	 *
	 * @param array $auth_key 初始化参数;
	 * @return xxtea
	 */
	function xxtea($auth_key) {

		$this->_auth_key = $auth_key;
	}

	/**
	 * 加密字串;
	 *
	 * @param string $str
	 * @param string $key
	 * @return 返回加密后的字串;
	 */
	function encrypt($str, $key = NULL) {

		$key = $key ? $key : $this->_auth_key;
		if (empty($str)) {
			return "";
		}

		$v = $this->str2long($str, true);
		$k = $this->str2long($key, false);
		if (count($k) < 4) {
			for($i = count($k); $i < 4; $i ++) {
				$k[$i] = 0;
			}
		}

		$n = count($v) - 1;

		$z = $v[$n];
		$y = $v[0];
		$delta = 0x9E3779B9;
		$q = floor(6 + 52 / ($n + 1));
		$sum = 0;
		while (0 < $q --) {
			$sum = $this->int32($sum + $delta);
			$e = $sum >> 2 & 3;
			for($p = 0; $p < $n; $p ++) {
				$y = $v[$p + 1];
				$mx = $this->int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ $this->int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
				$z = $v[$p] = $this->int32($v[$p] + $mx);
			}

			$y = $v[0];
			$mx = $this->int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ $this->int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
			$z = $v[$n] = $this->int32($v[$n] + $mx);
		}

		return $this->long2str($v, false);
	}

	/**
	 * 字串的转换 long -> string;
	 *
	 * @param string $v
	 * @param boolean $w
	 * @return 返回字串;
	 */
	function long2str($v, $w) {

		$len = count($v);
		$s = array();
		for($i = 0; $i < $len; $i ++) {
			$s[$i] = pack("V", $v[$i]);
		}

		if ($w) {
			return substr(join('', $s), 0, $v[$len - 1]);
		} else {
			return join('', $s);
		}
	}

	/**
	 * 字串的转换 string -> long;
	 *
	 * @param unknown_type $s
	 * @param unknown_type $w
	 * @return unknown
	 */
	function str2long($s, $w) {

		$v = unpack("V*", $s . str_repeat("\0", (4 - strlen($s) % 4) & 3));
		$v = array_values($v);
		if ($w) {
			$v[count($v)] = strlen($s);
		}

		return $v;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $n
	 * @return unknown
	 */
	function int32($n) {

		while ($n >= 2147483648) {
			$n -= 4294967296;
		}

		while ($n <= - 2147483649) {
			$n += 4294967296;
		}

		return (int)$n;
	}

	/**
	 * 解密字串;
	 *
	 * @param string $str
	 * @param string $key
	 * @return 返回解密后的字串;
	 */
	function decrypt($str, $key = NULL) {

		$key = $key ? $key : $this->_auth_key;
		if (empty($str)) {
			return "";
		}

		$v = $this->str2long($str, false);
		$k = $this->str2long($key, false);
		if (count($k) < 4) {
			for($i = count($k); $i < 4; $i ++) {
				$k[$i] = 0;
			}
		}

		$n = count($v) - 1;

		$z = $v[$n];
		$y = $v[0];
		$delta = 0x9E3779B9;
		$q = floor(6 + 52 / ($n + 1));
		$sum = $this->int32($q * $delta);
		while ($sum != 0) {
			$e = $sum >> 2 & 3;
			for($p = $n; $p > 0; $p --) {
				$z = $v[$p - 1];
				$mx = $this->int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ $this->int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
				$y = $v[$p] = $this->int32($v[$p] - $mx);
			}

			$z = $v[$n];
			$mx = $this->int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ $this->int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
			$y = $v[0] = $this->int32($v[0] - $mx);
			$sum = $this->int32($sum - $delta);
		}

		return $this->long2str($v, true);
	}

}

<?php
/**
 * errcode
 * 错误码和错误消息库
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class errcode {

	/** 错误码 */
	public static $errcode = 0;
	/** 错误信息 */
	public static $errmsg = '';

	/**
	 * 全局输出定义错误码信息
	 * @param string $const_string 错误码常量文字，格式为：[number]:[string]
	 * @return boolean
	 */
	public static function set_errmsg($const_string) {

		self::$errcode = -449;
		self::$errmsg = 'default error';
		if (preg_match('/^\s*(\d+)\s*\:\s*(.+)$/', $const_string, $match)) {
			// 分离 错误代码 和 错误消息
			self::$errcode = (int)$match[1];
			self::$errmsg = (string)$match[2];
		} else {
			// 错误代码定义出错
			self::$errcode = -440;
			self::$errmsg = '代码定义错误"'.$const_string.'"';
		}

		if (!preg_match('/\%\w/i', self::$errmsg, $matches)) {
			// 错误消息描述内未发现变量，则直接输出
			return false;
		}

		// 获取给定的参数
		$values = func_get_args();
		// 列出变量值
		unset($values[0]);
		if (empty($values)) {
			// 如果变量值不存在
			return false;
		}

		// 变量个数 与 值的个数 相差数
		$count = count(preg_split('/\%\w/i', self::$errmsg)) - count($values);
		if ($count > 0) {
			// 变量个数 多于 给定值个数，则补充值的个数，避免出错
			for ($i = 0; $i < $count; $i++) {
				$values[] = '';
			}
		}
		// 转义变量名
		self::$errmsg = preg_replace('/\%\s+$/is', '', vsprintf(self::$errmsg, $values));

		return false;
	}

}

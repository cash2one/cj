<?php
/**
 * help_exception
 * help 异常类
 *
 * $Author$
 * $Id$
 */

class help_exception extends Exception {

	/**
	 * 构造方法
	 * @param mixed $message 错误信息
	 * @param number $code 错误码
	 */
	public function __construct($message, $code = 0) {

		// 如果 $message 是 Exception 实例
		if ($message instanceof Exception) {
			parent::__construct($message->getMessage(), intval($message->getCode()));
		} else {
			parent::__construct($message, intval($code));
		}
	}

}

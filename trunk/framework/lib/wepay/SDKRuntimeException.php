<?php
/**
 * SDK 异常
 * @author zhuxun37
 *
 */

class wepay_exception extends Exception {

	public function errorMessage() {

		return $this->getMessage();
	}

}

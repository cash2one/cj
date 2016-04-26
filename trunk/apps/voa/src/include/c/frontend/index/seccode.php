<?php
/**
 * voa_c_frontend_index_seccode
 * 安全
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_index_seccode extends voa_c_frontend_base {

	public function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		if (!isset($_GET['code'])) {
			return $this->generate_seccode($_GET['code']);
		}

		$code = $_GET['code'];
		if (!empty($code) && !preg_match('/^[abcdefghijklmnopqrstuvwxyz0123456789]+$/i', $code)) {
			exit;
		}

		$width = (int)$_GET['width'];
		$height = (int)$_GET['height'];
		if (300 < $width) {
			$width = 0;
		}
		if (100 < $height) {
			$height = 0;
		}
		$seccode = new seccode();
		$seccode->doimg($code, $width, $height);exit;
	}

	/**
	 * 生成验证码
	 * @param string $code 验证码
	 * @return boolean
	 */
	public function generate_seccode($code) {

		$formhash = $_POST['formhash'];
		$rpc = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Seccode/');
		if (!$code = $rpc->generate_seccode($formhash)) {
			$this->_json_message(array(
				'errcode' => 100,
				'errmsg' => '短消息发送错误, 请稍后重试',
				'result' => ''
			));
			return true;
		}

		// 如果返回错误
		if (is_object($code) && 'PHPRPC_Error' == get_class($code)) {
			$this->_json_message(array(
				'errcode' => $code->getNumber(),
				'errmsg' => $code->getMessage(),
				'result' => ''
			));
			return true;
		}

		$this->_json_message(array(
			'errcode' => 0,
			'errmsg' => '',
			'result' => $code
		));
		return true;
	}

}

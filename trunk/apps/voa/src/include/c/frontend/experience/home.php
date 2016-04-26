<?php
/**
 * 登录页
 * $Author$
 * $Id$
 */

class voa_c_frontend_experience_home extends voa_c_frontend_experience_base {

	public function execute() {

		/** 处理表单提交 */
		if ($_POST) {
			/** 调用接口, 入 center 库 */
			$auth_key = config::get('voa.rpc.client.auth_key');

			/** 检测参数 */
			/** 检测用户名 */
			$userret = null;
			if (!$this->_validator_username($_POST['account'], $userret)) {
				/** n为失败, 此时info内容会弹出提示 */
				$this->_json_message(array(
					"status" => "n", 
					"info" => $userret
				));
				return true;
			}

			/** 检测手机 */
			$mobileret = null;
			if (!$this->_validator_mobile($_POST['phone'], $mobileret)) {
				/** n为失败, 此时info内容会弹出提示 */
				$this->_json_message(array(
					"status" => "n", 
					"info" => $mobileret
				));
				return true;
			}

			/** 添加账号必要参数 */
			$args = array(
				'm_username' => $_POST['account'],
				'm_email' => $_POST['phone'].'@vchangyi.com',
				'm_mobilephone' => $_POST['phone'],
				'm_active' => '1'
			);

			/** 开通体验号 */
			$client = new voa_client_experience($auth_key);
			$experienceret = null;
			$result = $client->open($args, $experienceret);
			if ($result) {
				/** y为成功 */
				$this->_json_message(array(
					"status" => "y", 
					"info" => ''
				));
				return true;
			} else {
				/** n为失败, 此时info内容会弹出提示 */
				$this->_json_message(array(
					"status" => "n", 
					"info" => $experienceret['error']
				));
				return true;
			}
			
		}
		$this->_output('experience/post');
	}

	/**
	 * 验证用户名
	 * @param string $string 用户名
	 * @param string $error_msg <strong style="color:red">(引用结果)</strong>验证错误信息
	 * @return boolean
	 */
	protected function _validator_username($string,  &$error_msg = '') {
		/** 使用字节长验证 */
		if (!validator::is_username($string, 16)) {
			$error_msg = '姓名中不可包含标点符号';
			return false;
		}
		return true;
	}

	/**
	 * 验证手机
	 * @param string $string 手机
	 * @param string $error_msg <strong style="color:red">(引用结果)</strong>验证错误信息
	 * @return boolean
	 */
	protected function _validator_mobile($string,  &$error_msg = '') {
		/** 使用格式验证 */
		if (!validator::is_mobile($string)) {
			$error_msg = '手机号格式不正确';
			return false;
		}
		return true;
	}

}

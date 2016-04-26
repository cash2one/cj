<?php
/**
 * sms.php
 * 手机短信发送 ，通过uc接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_adminer_sms extends voa_c_admincp_adminer_base {

	public function execute() {

		$mobilephone = (string)$this->request->post('mobilephone');
		if (!$mobilephone || !validator::is_mobile($mobilephone)) {
			$this->_output(1001, '请正确输入手机号码');
			return false;
		}

		// 获取管理员信息
		$uda_adminer_get = &uda::factory('voa_uda_frontend_adminer_get');
		$adminer = array();
		$adminergroup = array();
		if (!$uda_adminer_get->adminer_by_account($mobilephone, $adminer, $adminergroup)) {
			$this->_output($uda_adminer_get->errcode, $uda_adminer_get->errmsg);
			return;
		}

		if (!empty($_POST['formhash'])) {
			// rpc 调用
			$rpc = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Mobile/');
			$mobile = $_POST['mobilephone'];
			$ip = controller_request::get_instance()->get_client_ip();
			$formhash = $_POST['formhash'];
			$seccode = $_POST['seccode'];
			if (!$result = $rpc->send_smscode($mobile, $ip, $formhash, $seccode, 'pwdreset')) {
				$this->_output(100, '短消息发送错误, 请稍后重试');
				return true;
			}

			// 如果返回错误
			if (is_object($result) && 'PHPRPC_Error' == get_class($result)) {
				$this->_output($result->getNumber(), $result->getMessage());
				return true;
			}
		} else { // 旧接口, 已弃用
			// 传输客户端的IP到短信发送服务
			$crypt_xxtea = new crypt_xxtea(config::get('voa.auth_key'));
			$ipinfo = startup_env::get('timestamp')."\t".controller_request::get_instance()->get_client_ip();
			$ipinfo = rbase64_encode($crypt_xxtea->encrypt($ipinfo));

			// 接口需要的参数
			$params = array(
				'mobilephone' => $mobilephone,
				'action' => 'oaresetpwd',
				'ipinfo' => $ipinfo
			);

			// 呼叫oa uc api接口
			$r = $this->_call_oauc_api('smscode', $params, false);
			if (!isset($r['errcode'])) {
				$this->_output(103, '请求UC短信发送接口发生错误');
			}
		}

		$this->_output($r['errcode'], $r['errmsg'], $r['result']);
		return true;
	}

}

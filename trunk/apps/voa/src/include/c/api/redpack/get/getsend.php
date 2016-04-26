<?php
/**
 * getsend.php
 * 领取并支付红包金额给接受者接口
 * $Author$
 * $Id$
 */

class voa_c_api_redpack_get_getsend extends voa_c_api_redpack_base {

	public function execute() {

		return true;
		// 红包id
		$redpack_id = (int)$this->request->get('redpack_id', 0);
		// 获取 wx_openid
		$openid = '';
		if (!$this->_get_wx_openid($openid)) {
			$this->_set_errcode('400:请刷新页面, 重新领取');
			return true;
		}

		try {
			$uda_send = new voa_uda_frontend_redpack_send();
			$request = array(
				'rpid' => $redpack_id,
				'openid' => $openid,
				'uid' => $this->_member['m_uid']
			);
			$result = array();
			$uda_send->doit($request, $result);

			// 返回结果
			$this->_result = array(
				'money' => number_format($result['money'] / 100, 2)
			);

		} catch (help_exception $h) {
			return $this->_api_error_message($h);
		} catch (Exception $e) {
			return $this->_api_system_message($e);
		}

	}

}


<?php
/**
 * send.php
 * 发送微信红包
 * $Author$
 * $Id$
 */

class voa_c_api_redpack_post_send extends voa_c_api_redpack_base {

	public function execute() {

		// 获取 wx_openid
		$openid = '';
		if (!$this->_get_wx_openid($openid)) {
			$this->_set_errcode('400:请刷新页面, 重新领取');
			return true;
		}

		// 分配红包
		$uda_rp = new voa_uda_frontend_redpack_send();
		$result = array();
		$params = array(
			'redpack_id' => $this->request->get('redpack_id'),
			'uid' => $this->_member['m_uid'],
			'openid' => $this->_member['wx_openid']
		);

		try {
			if (!$uda_rp->doit($params, $result)) {
				$this->_errcode = $uda_rp->errcode;
				$this->_errmsg = $uda_rp->errmsg;
				return true;
			}

		} catch (help_exception $e) {
			$this->_errcode = $e->getCode();
			$this->_errmsg = $e->getMessage();
			return true;
		} catch (Exception $e) {
			$this->_set_errcode('500:服务器繁忙');
			return true;
		}

		$this->_result = $result;
	}

}

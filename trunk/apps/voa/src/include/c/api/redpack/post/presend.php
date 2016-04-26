<?php
/**
 * presend.php
 * 领取红包, 红包信息入库, 但不进行发送操作
 * $Author$
 * $Id$
 */

class voa_c_api_redpack_post_presend extends voa_c_api_redpack_base {

	public function execute() {

		// 获取 wx_openid
		$openid = '';
		if (!$this->_get_wx_openid($openid)) {
			$this->_set_errcode('400:请刷新页面, 重新领取');
			return true;
		}

		// 每天 8:00 前不让发红包
		$hi = (int)rgmdate(startup_env::get('timestamp'), 'Hi');
		if ($hi < 800) {
			$this->_set_errcode('500:红包休息中, 每天8点准时开放');
			return true;
		}

		// 分配红包
		$uda_rp = new voa_uda_frontend_redpack_presend();
		$result = array();
		$params = array(
			'redpack_id' => $this->request->get('redpack_id'),
			'uid' => $this->_member['m_uid'],
			'username' => $this->_member['m_username'],
			'openid' => $this->_member['wx_openid']
		);
		try {
			if (!$uda_rp->doit($params, $result)) {
				$this->_errcode = $uda_rp->errcode;
				$this->_errmsg = $uda_rp->errmsg;
				return true;
			}

			$this->_result = $result;
			//$result['url'] = '/frontend/redpack/getsign?redpack_id='.$this->request->get('redpack_id');
		} catch (help_exception $e) {
			$this->_errcode = $e->getCode();
			$this->_errmsg = $e->getMessage();
			return true;
		} catch (Exception $e) {
			$this->_set_errcode('500:服务器繁忙');
			return true;
		}
	}

}

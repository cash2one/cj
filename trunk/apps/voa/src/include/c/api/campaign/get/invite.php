<?php
/**
 * 活动->报名
 * 并返回电子邀请函信息
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_get_invite extends voa_c_api_campaign_base {

	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		// 需要的参数
		$fields = array(
			'regid' => array('type' => 'int', 'required' => true)
		);
		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}

		$d = new voa_d_oa_campaign_reg();
		$reg = $d->get($this->_params['regid']);

		//获取销售姓名
		$m = new voa_d_oa_member();
		$member = $m->fetch($reg['saleid']);

		//返回二维码信息
		$this->_result = array(
			'salename' => $member['m_username'],
			'salemobile' => $member['m_mobilephone'],
			'qrcode' => '/api/campaign/get/qrcode?regid='.$reg['id'],
			'avatar' => voa_h_user::avatar($reg['saleid'])
		);

		return true;
	}

}

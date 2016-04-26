<?php
/**
 * voa_c_frontend_invite_join
 * 加入人员填写信息
 * Created by zhoutao.
 * Created Time: 2015/7/9  16:51
 */

class voa_c_frontend_invite_join extends voa_c_frontend_invite_base {

	protected $sex_array = array(
		//0 => "保密",
		1 => "男",
		2 => "女"
	);

	//不强制登录，允许外部访问
	protected function _before_action($action) {
		$this->_auto_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	public function execute () {
		// 获取加密m_uid
		$en_m_uid = $this->request->get('m_uid');
		// 解密m_uid
		$m_uid = '';
		$this->_deciphering($en_m_uid, $m_uid);
		if (empty($m_uid)) {
			$this->_no_content('缺少必要的参数', null, null, '发生错误');
		}

		// 扩展字段
		$custom = $this->_invite_setting['custom'];
		if($custom) $custom = unserialize($custom);
		else $custom = array();
		$this->view->set('requiremobile', -1 < strpos($this->_invite_setting['requirefield'], 'mobile') ? 1 : null);
		$this->view->set('navtitle', '填写信息');
		$this->view->set('sex', $this->sex_array);
		$this->view->set('invite_uid', $m_uid); // 邀请人m_uid
		// 扩展字段
		$this->view->set('custom', $custom);
		$this->_output('mobile/invite/join');
		return true;
	}

}

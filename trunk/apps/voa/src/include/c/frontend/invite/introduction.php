<?php
/**
 * voa_c_frontend_invite_introduction
 * 企业号介绍
 * Created by zhoutao.
 * Created Time: 2015/7/8  17:13
 */

class voa_c_frontend_invite_introduction extends voa_c_frontend_invite_base {

	//不强制登录，允许外部访问
	protected function _before_action($action) {
		$this->_auto_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}
		
	public function execute() {
		if (startup_env::get('wbs_uid')) {
			$this->_no_authority('您已属于内部人员，无须申请加入', null, null, '发生错误');
		}
		// 获取二维码创建时间并解密
		$en_timestamp = $this->request->get('timestamp');
		$de_timestamp = null;
		if (!empty($en_timestamp)) {
			$this->_deciphering($en_timestamp, $de_timestamp);
		} else {
			$this->_no_content('缺少必要参数！', null, null, '发生错误');
		}
		// 获取邀请人ID(已加密)
		$en_m_uid = $this->request->get('m_uid');
		if (empty($en_m_uid)) {
			$this->_no_content('缺少必要参数！', null, null, '发生错误');
		}

		// 从缓存获取配置信息
		$timestamp = startup_env::get('timestamp');

		// 对比时间
		$allow = null;
		if ((int)$de_timestamp + $this->_invite_setting['overdue'] <= $timestamp) {
			$this->_no_authority('该二维码已失效,请联系邀请人！', null, null, '已过期');
			return false;
		}

		// 企业logo
		if (!empty($this->_invite_setting['logo'])) {
			$logo = voa_h_attach::attachment_url($this->_invite_setting['logo']);
			$this->view->set('logo', $logo); // logo图片地址
		}

		// 企业号信息
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');

		$this->view->set('sitedata', $sets);
		$this->view->set('m_uid', $en_m_uid);
		$this->view->set('navtitle', $sets['sitename'] . '企业号');
		$this->view->set('introduction', $this->_invite_setting['introduction']);
		$this->_output('mobile/invite/introduction');
	}

}

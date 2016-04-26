<?php
/**
 * 邀请人员列表
 * $Author ppker
 * date 07-08
 */

class voa_c_frontend_invite_list extends voa_c_frontend_invite_base {

	public function execute() {
		// 获取当前人的信息
		$m_uid = startup_env::get('wbs_uid');
		// 判断是否在可邀请列表里
		$primary_id = explode(',', $this->_invite_setting['primary_id']);
		if (!in_array($m_uid, $primary_id)) {
			$this->_no_authority('您尚无权限邀请人员，请联系管理员开通', null, null, '发生错误');
		}

		// 登陆者的m_uid
		// $m_uid = intval(startup_env::get("wbs_uid"));
		$this->view->set('navtitle', '我的邀请');
		// 传递域名呀
		$domain1 = config::get('voa.oa_http_scheme');
		$domain2 = $domain1 . $this->_setting['domain'];
		$this->view->set('domain2', $domain2);
		// $this->view->set('m_uid', $m_uid);
		$this->_output('mobile/invite/list');
	}

}

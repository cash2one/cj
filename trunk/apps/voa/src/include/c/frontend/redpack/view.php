<?php
/**
 * view.php
 * 查看红包详情操作
 * $Author$
 * $Id$
 */
class voa_c_frontend_redpack_view extends voa_c_frontend_redpack_base {

	public function execute() {

		$redpack_id = (int)$this->request->get('redpack_id', 0);
		// 获取 wx_openid
		$openid = '';
		if (!$this->_get_wx_openid($openid)) {
			$this->_error_message('服务器繁忙, 请刷新页面');
			return true;
		}

		// 获取当前红包
		$serv_rp = &service::factory('voa_s_oa_redpack');
		if (!$redpack = $serv_rp->get($redpack_id)) {
			$this->_error_message('该红包不存在');
		}

		// 判断是否有权限领取红包
		$can_get = true;
		if (0 < $redpack['total']) {
			$uda_ab = &uda::factory('voa_uda_frontend_redpack_abstract');
			$can_get = $uda_ab->has_privilege($redpack_id, $this->_user['m_uid']);
		}

		// 读取红包记录
		$rplog = array();
		$serv_rplog = &service::factory('voa_s_oa_redpack_log');
		if (!$rplog = $serv_rplog->fetch_by_openid_redpackid($openid, $redpack_id)) {
			$rplog = array();
		}

		if (!empty($rplog)) {
			$serv_rplog->format($rplog);
		}

		// 判断是否已经领取了红包
		$has_got = true;
		if (empty($rplog) && $can_get
				&& (0 == $redpack['endtime'] || (0 < $redpack['endtime'] && $redpack['endtime'] > startup_env::get('timestamp')))
				&& (0 == $redpack['redpacks'] || (0 < $redpack['redpacks'] && $redpack['redpacks'] > $redpack['times']))) {
			$has_got = false;
		}

		$this->view->set('redpack_id', $redpack_id);
		$this->view->set('redpack', $redpack);
		$this->view->set('rplog', $rplog);
		$this->view->set('can_get', $can_get);
		$this->view->set('has_got', $has_got);

		$this->_output('mobile/redpack/view');
	}

}

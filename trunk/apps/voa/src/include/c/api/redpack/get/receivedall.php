<?php
/**
 * receivedall.php
 * 所有收到的红包
 * $Author$
 * $Id$
 */

class voa_c_api_redpack_get_receivedall extends voa_c_api_redpack_base {

	public function execute() {

		// 取红包日志
		$params = $this->request->getx();
		$params['uid'] = $this->_member['m_uid'];
		$logs = array();
		$uda_rl = &uda::factory('voa_uda_frontend_redpack_receivelist');
		if (!$uda_rl->doit($logs, $params)) {
			$this->_errcode = $uda_rl->errcode;
			$this->_errmsg = $uda_rl->errmsg;
			return true;
		}

		// 获取红包ID
		$redpack_ids = array();
		foreach ($logs as $_log) {
			$redpack_ids[] = $_log['redpack_id'];
		}

		// 取红包列表
		$redpacks = array();
		$serv_rp = &service::factory('voa_s_oa_redpack');
		if (!empty($redpack_ids)) {
			$redpacks = $serv_rp->list_by_pks($redpack_ids);
		}

		// 重组数据
		foreach ($logs as &$_log) {
			$_log['from_username'] = $redpacks[$_log['redpack_id']]['m_username'];
		}

		$this->_result['list'] = $logs;
	}

}

<?php
/**
 * redpacklog.php
 * 获取红包领取记录
 * $Author$
 * $Id$
 */

class voa_c_api_redpack_get_redpacklog extends voa_c_api_redpack_base {

	public function execute() {

		$redpack_id = $this->request->get('redpack_id', 0);
		// 读取红包日志
		$uda_log = uda::factory('voa_uda_frontend_redpack_getlogs');
		$params = array('redpack_id' => $redpack_id);
		$list = array();

		try {
			if (!$uda_log->doit($params, $list)) {
				$this->_errcode = $uda_log->errcode;
				$this->_errmsg = $uda_log->errmsg;
				return true;
			}
		} catch (help_exception $e) {
			$this->_errcode = $e->getCode();
			$this->_errmsg = $e->getMessage();
			return true;
		} catch (Exception $e) {
			$this->_errcode = '500';
			$this->_errmsg = '服务器繁忙';
			return true;
		}

		// 获取 uid 列表
		$uids = array();
		foreach ($list as $_v) {
			$uids[] = $_v['m_uid'];
		}

		// 读取用户信息
		$serv_m = &service::factory('voa_s_oa_member');
		$users = $serv_m->fetch_all_by_ids($uids);

		// 获取用户头像
		foreach ($list as &$_v) {
			$_v['avatar'] = voa_h_user::avatar($_v['m_uid'], $users[$_v['m_uid']]);
		}

		$this->_result['list'] = $list;
		return true;
	}

}

<?php
/**
 * list.php
 * 红包列表
 * $Author$
 * $Id$
 */
class voa_c_frontend_redpack_list extends voa_c_frontend_redpack_base {

	public function execute() {

		// 取红包日志
		$year = (int)$this->request->get('year', 0);
		$serv_t = &service::factory('voa_s_oa_redpack_total');
		$redpack_total = $serv_t->get_by_uid_year($this->_user['m_uid'], $year);
		if (empty($redpack_total)) {
			$redpack_total = array(
				'money' => 0,
				'rp_count' => 0,
				'highest_count' => 0
			);
		}

		// 格式化
		$serv_t->format($redpack_total);

		$this->view->set('redpack_total', $redpack_total);
		$this->view->set('year', $year);
		$this->_output('mobile/redpack/list');
	}

}

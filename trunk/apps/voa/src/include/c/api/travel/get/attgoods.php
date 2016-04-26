<?php
/**
 * voa_c_api_travel_get_attgoods
 * 获取客户关注的产品信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_attgoods extends voa_c_api_travel_abstract {

	public function execute() {

		// 获取分页参数
		$page = (int)$this->_get('page');
		list($start, $perpage, $page) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		// 只取自己的
		$this->_ptname['conds'] = array(
			'uid' => $this->_member['m_uid']
		);

		// 获取意向产品信息
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_travel_customer2goods', $this->_ptname);
		if (!$uda->list_all($this->_params, array($start, $perpage), $list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = empty($list) ? array() : array_values($list);

		return true;
	}

}


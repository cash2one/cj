<?php
/**
 * voa_c_api_travel_get_remark
 * 获取备注信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_remark extends voa_c_api_travel_abstract {
	// 最大 limit 值
	protected $_max_limit = 100;

	public function execute() {

		// 获取分页参数
		$page = (int)$this->_get('page');
		$limit = (int)$this->_get('limit');
		$limit = 0 >= $limit ? $this->_max_limit : $limit;
		list($start, $perpage, $page) = voa_h_func::get_limit($page, $limit);

		// 只取自己的
		$this->_ptname['conds'] = array(
			'uid' => $this->_member['m_uid']
		);

		// 删除商品信息
		$total = 0;
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_travel_customer2remark', $this->_ptname);
		if (!$uda->list_all($this->_params, array($start, $perpage), $list, $total)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = array(
			'total' => $total,
			'data' => empty($list) ? array() : array_values($list)
		);

		return true;
	}

}


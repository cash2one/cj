<?php
/**
 * voa_c_api_travel_get_goodstable
 * 获取表格信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_goodstable extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 读取数据
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_goods_table', $this->_ptname);
		if (!$uda->list_all($this->_params, $list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = empty($list) ? array() : array_values($list);

		return true;
	}

}

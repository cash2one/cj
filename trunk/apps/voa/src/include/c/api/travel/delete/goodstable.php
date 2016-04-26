<?php
/**
 * voa_c_api_travel_delete_goodstable
 * 删除表格信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_delete_goodstable extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 如果不是管理员
		if (0 == $this->_is_admin) {
			$this->_set_errcode(voa_errcode_oa_goods::NO_PRIVILEGES);
			return true;
		}

		// 获取表格ID
		$tid = (int)$this->_get('tid');
		if (empty($tid)) {
			$this->_set_errcode(voa_errcode_oa_goods::TABLE_ID_IS_EMPTY);
			return true;
		}

		// 删除表格信息
		$uda = &uda::factory('voa_uda_frontend_goods_table', $this->_ptname);
		if (!$uda->delete($this->_params)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		return true;
	}

}

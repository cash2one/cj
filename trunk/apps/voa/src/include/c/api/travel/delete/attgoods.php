<?php
/**
 * voa_c_api_travel_delete_attgoods
 * 删除客户关注产品的关联信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_delete_attgoods extends voa_c_api_travel_abstract {

	public function execute() {

		// 取关注id
		$cgid = (int)$this->_get('cgid');
		if (empty($cgid)) {
			$this->_set_errcode(voa_errcode_oa_travel::CGID_IS_EMPTY);
			return true;
		}

		// 删除商品信息
		$uda = &uda::factory('voa_uda_frontend_travel_customer2goods', $this->_ptname);
		if (!$uda->delete($cgid)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		return true;
	}

}


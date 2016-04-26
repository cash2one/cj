<?php
/**
 * voa_c_api_travel_delete_remark
 * 删除备注信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_delete_remark extends voa_c_api_travel_abstract {

	public function execute() {

		// 获取备注ID
		$crk_id = (int)$this->_get('crk_id');
		if (empty($crk_id)) {
			$this->_set_errcode(voa_errcode_oa_travel::CRK_ID_IS_EMPTY);
			return true;
		}

		// 删除商品信息
		$uda = &uda::factory('voa_uda_frontend_travel_customer2remark', $this->_ptname);
		if (!$uda->delete($this->_member, $crk_id)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		return true;
	}

}


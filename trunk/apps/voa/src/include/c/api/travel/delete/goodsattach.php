<?php
/**
 * voa_c_api_travel_delete_goodsattach
 * 删除分类信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_delete_goodsattach extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 获取附件id
		$gaid = (int)$this->_get('gaid');
		if (empty($gaid)) {
			$this->_set_errcode(voa_errcode_oa_travel::GAID_IS_EMPTY);
			return true;
		}

		// 删除表格信息
		$uda = &uda::factory('voa_uda_frontend_goods_attach', $this->_ptname);
		if (!$uda->delete($gaid)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		return true;
	}

}

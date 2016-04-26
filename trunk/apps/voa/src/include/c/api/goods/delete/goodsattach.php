<?php
/**
 * voa_c_api_goods_delete_goodsattach
 * 删除分类信息
 * $Author$
 * $Id$
 */

class voa_c_api_goods_delete_goodsattach extends voa_c_api_goods_abstract {

	public function execute() {

		// 删除表格信息
		$uda = &uda::factory('voa_uda_frontend_goods_attach', $this->_ptname);
		if (!$uda->delete($this->_params)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		return true;
	}

}

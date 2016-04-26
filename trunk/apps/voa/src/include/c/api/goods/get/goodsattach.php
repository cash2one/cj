<?php
/**
 * voa_c_api_goods_get_goodsattach
 * 获取分类信息
 * $Author$
 * $Id$
 */

class voa_c_api_goods_get_goodsattach extends voa_c_api_goods_abstract {

	public function execute() {

		// 读取数据
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_goods_attach', $this->_ptname);
		if (!$uda->list_all($this->_params, $list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = empty($list) ? array() : array_values($list);

		return true;
	}

}


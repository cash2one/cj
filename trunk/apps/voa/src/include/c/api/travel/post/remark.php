<?php
/**
 * voa_c_api_travel_post_remark
 * 新增/编辑备注信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_post_remark extends voa_c_api_travel_abstract {

	public function execute() {

		// 获取表格 crk_id
		$crk_id = 0;
		if (isset($this->_params['crk_id'])) {
			$crk_id = (int)$this->_params['crk_id'];
		}

		// 删除商品信息
		$uda = &uda::factory('voa_uda_frontend_travel_customer2remark', $this->_ptname);
		if (0 < $crk_id) {
			if (!$uda->update($this->_member, $this->_params, $crk_id)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}
		} else {
			$remark = array();
			if (!$uda->add($this->_member, $this->_params, $remark)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}

			$this->_result = $remark;
		}

		return true;
	}

}


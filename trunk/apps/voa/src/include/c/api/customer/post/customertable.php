<?php
/**
 * voa_c_api_customer_post_customertable
 * 更新表格信息
 * $Author$
 * $Id$
 */

class voa_c_api_customer_post_customertable extends voa_c_api_customer_abstract {

	public function execute() {

		// 获取表格 tid
		$tid = (int)$this->_params['tid'];

		// 更新数据
		$uda = &uda::factory('voa_uda_frontend_customer_table', $this->_ptname);
		if (0 < $tid) {
			if (!$uda->update($this->_member, $this->_params, $tid)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}
		} else {
			$table = array();
			if (!$uda->add($this->_params, $table)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}

			$this->_result = $table;
		}

		return true;
	}

}

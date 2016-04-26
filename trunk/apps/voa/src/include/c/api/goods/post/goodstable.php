<?php
/**
 * voa_c_api_goods_post_goodstable
 * 更新表格信息
 * $Author$
 * $Id$
 */

class voa_c_api_goods_post_goodstable extends voa_c_api_goods_abstract {

	public function execute() {

		// 获取表格 tid
		$tid = (int)$this->_params['tid'];

		// 更新数据
		$uda = &uda::factory('voa_uda_frontend_goods_table', $this->_ptname);
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

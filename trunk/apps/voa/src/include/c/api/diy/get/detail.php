<?php
/**
 * voa_c_api_diy_get_detail
 * 获取 diy 数据
 * $Author$
 * $Id$
 */

class voa_c_api_diy_get_detail extends voa_c_api_diy_abstract {

	public function execute() {

		// 初始化 uda
		$uda_diy = new voa_uda_frontend_diy_data_get();
		$this->init_uda_for_data($uda_diy);

		try {
			$data = array();
			$uda_diy->execute($this->_params, $data);

			$this->_result = $data;
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		return true;
	}

}

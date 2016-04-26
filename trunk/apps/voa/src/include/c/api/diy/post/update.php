<?php
/**
 * voa_c_api_diy_post_update
 * 更新 diy 数据
 * $Author$
 * $Id$
 */

class voa_c_api_diy_post_update extends voa_c_api_diy_abstract {

	public function execute() {

		// 初始化 uda
		$uda_diy = new voa_uda_frontend_diy_data_update();
		$this->init_uda_for_data($uda_diy);

		try {
			$data = array();
			$uda_diy->execute($this->_params, $data);
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		return true;
	}

}

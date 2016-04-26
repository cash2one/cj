<?php
/**
 * voa_c_api_travel_get_specialtopic
 * 获取专题信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_specialtopic extends voa_c_api_travel_abstract {

	public function execute() {

		// 读取数据
		$material = array();
		$uda = &uda::factory('voa_uda_frontend_travel_material_get');
		if (!$uda->execute($this->request->getx(), $material)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = $material;

		return true;
	}

}

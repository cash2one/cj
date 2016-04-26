<?php
/**
 * 巡店新建门店配置信息
 * voa_c_api_inspect_get_shopsetting
 * $Author$
 * $Id$
 */

class voa_c_api_inspect_get_shopsetting extends voa_c_api_inspect_base {

	public function execute() {

		// 输出结果
		$this->_result = array(
			'shops' => $this->_shops,
			'regions' => $this->_regions,
		);

		return true;
	}

}

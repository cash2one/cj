<?php
/**
 * 审批流程列表
 * $Author$
 * $Id$
 */

class voa_c_api_askfor_get_templates extends voa_c_api_askfor_base {

	public function execute() {

		$templates = array();
		$uda  = &uda::factory('voa_uda_frontend_askfor_template_list');
		$uda->template_list($templates);
		$format  = &uda::factory('voa_uda_frontend_askfor_format');
		$data = $format->askfor_template($templates);

		// 输出结果
		$this->_result = array(
			'data' => empty($data) ? array() : array_values($data)
		);
		return true;

	}

}


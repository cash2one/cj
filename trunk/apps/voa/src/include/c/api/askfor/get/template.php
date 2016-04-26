<?php
/**
 * 取得特定审批流程（用于新增审批页面初始化）
 * $Author$
 * $Id$
 */

class voa_c_api_askfor_get_template extends voa_c_api_askfor_base {

	public function execute() {

		/*需要的参数*/
		$fields = array(
			'aft_id' => array('type' => 'int', 'required' => true),	//审批流程ID
		);
		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			return false;
		}

		/*审批标题检查*/
		if (empty($this->_params['aft_id'])) {
			return $this->_set_errcode(voa_errcode_api_askfor::AFT_ID_NOT_EXIST);
		}

		/** 审批流程ID */
		$aft_id = rintval($this->_params['aft_id']);

		/** 取得审批流程 */
		$template = array();
		$uda  = &uda::factory('voa_uda_frontend_askfor_template_get');
		$uda->template_get($template);
		if (empty($template)) {
			return $this->_set_errcode(voa_errcode_api_askfor::TEMPLATE_NOT_EXIST);
		}

		// 输出结果
		$this->_result = array(
			'aft_id' => $template['aft_id'],
			'name' => $template['name'],
			//'m_uid' => $template['m_uid'],
			//'m_username' => $template['m_username'],
			'upload_image' => $template['upload_image'],
			'cols' => isset($template['cols']) ? array_values($template['cols']) : array()

		);
		return true;

	}

}


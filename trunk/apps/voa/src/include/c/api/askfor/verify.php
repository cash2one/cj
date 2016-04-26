<?php
/**
 * voa_c_api_askfor_verify
 * 审批操作相关
 * $Author$
 * $Id$
 */

class voa_c_api_askfor_verify extends voa_c_api_askfor_base {
	/** 审批信息 */
	protected $_askfor = array();
	/** 当前进度 */
	protected $_proc = array();

	protected function _chk_permit() {

		/** 判断当前审批是否存在 */
		$af_id = rintval($this->request->get('af_id'));
		$serv_ao = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$this->_askfor = $serv_ao->fetch_by_id($af_id);
		if (empty($this->_askfor)) {
			//$this->_error_message('askfor_not_exist', get_referer());
			$this->_set_errcode(voa_errcode_api_askfor::ASKFOR_NOT_EXIST);
			return false;
		}

		/** 读取当前进度信息 */
		$serv_p = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$this->_proc = $serv_p->fetch_by_id($this->_askfor['afp_id']);
		
		if (empty($this->_proc)) {
			//$this->_error_message('askfor_proc_error', get_referer());
			$this->_set_errcode(voa_errcode_api_askfor::PROC_ERROR);
			return false;
		}
		/** 判断当前用户是否有审核权限 */
		if ($this->_proc['m_uid'] != startup_env::get('wbs_uid')) {
			$this->_set_errcode(voa_errcode_api_askfor::ASKFOR_FORBIDDEN);
			return false;
		}
		if (voa_d_oa_askfor_proc::STATUS_NORMAL != $this->_proc['afp_status']) {
			$this->_set_errcode(voa_errcode_api_askfor::ASKFOR_DUPLICTE_USER);
			return false;
		}
		return true;
	}
}

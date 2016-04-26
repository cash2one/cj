<?php
/**
 * voa_c_api_askoff_verify
 * 请假操作相关
 * $Author$
 * $Id$
 */

class voa_c_api_askoff_verify extends voa_c_api_askoff_base {
	/** 请假信息 */
	protected $_askoff = array();
	/** 当前进度 */
	protected $_proc = array();

	protected function _chk_permit() {

		/** 判断当前请假是否存在 */
		$ao_id = rintval($this->request->get('ao_id'));
		$serv_ao = &service::factory('voa_s_oa_askoff', array('pluginid' => startup_env::get('pluginid')));
		$this->_askoff = $serv_ao->fetch_by_id($ao_id);
		if (empty($this->_askoff)) {
			//$this->_error_message('askoff_not_exist', get_referer());
			$this->_set_errcode(voa_errcode_api_askoff::ASKOFF_NOT_EXIST);
			return false;
		}

		/** 读取当前进度信息 */
		$serv_p = &service::factory('voa_s_oa_askoff_proc', array('pluginid' => startup_env::get('pluginid')));
		$this->_proc = $serv_p->fetch_by_id($this->_askoff['aopc_id']);
		if (empty($this->_proc)) {
			//$this->_error_message('askoff_proc_error', get_referer());
			$this->_set_errcode(voa_errcode_api_askoff::ASKOFF_PROC_ERROR);
			return false;
		}

		/** 判断当前用户是否有审核权限 */
		if ($this->_proc['m_uid'] != startup_env::get('wbs_uid') || voa_d_oa_askoff_proc::STATUS_NORMAL != $this->_proc['aopc_status']) {
			//$this->_error_message('askoff_forbidden', get_referer());
			$this->_set_errcode(voa_errcode_api_askoff::ASKOFF_FORBIDDEN);
			return false;
		}
		return true;
	}
}

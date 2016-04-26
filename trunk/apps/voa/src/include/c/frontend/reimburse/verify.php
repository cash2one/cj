<?php
/**
 * 报销操作相关
 * $Author$
 * $Id$
 */

class voa_c_frontend_reimburse_verify extends voa_c_frontend_reimburse_base {
	/** 报销信息 */
	protected $_reimburse = array();
	/** 当前进度 */
	protected $_proc = array();

	protected function _chk_permit() {

		/** 判断当前报销是否存在 */
		$rb_id = rintval($this->request->get('rb_id'));
		$serv_rb = &service::factory('voa_s_oa_reimburse', array('pluginid' => startup_env::get('pluginid')));
		$this->_reimburse = $serv_rb->fetch_by_id($rb_id);
		if (empty($this->_reimburse)) {
			$this->_error_message('reimburse_not_exist', get_referer());
		}

		/** 读取当前进度信息 */
		$serv_p = &service::factory('voa_s_oa_reimburse_proc', array('pluginid' => startup_env::get('pluginid')));
		$this->_proc = $serv_p->fetch_by_id($this->_reimburse['rbpc_id']);
		if (empty($this->_proc)) {
			$this->_error_message('reimburse_proc_error', get_referer());
		}

		/** 判断当前用户是否有审核权限 */
		if ($this->_proc['m_uid'] != startup_env::get('wbs_uid') || voa_d_oa_reimburse_proc::STATUS_NORMAL != $this->_proc['rbpc_status']) {
			$this->_error_message('reimburse_forbidden', get_referer());
		}

		return true;
	}
}

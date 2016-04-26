<?php
/**
 * voa_c_admincp_office_superreport_template
* 企业后台/微办公管理/超级报表/模板列表
* Create By YanWenzhong
* $Author$
* $Id$
*/
class voa_c_admincp_office_superreport_add extends voa_c_admincp_office_superreport_base {

	public function execute() {

		$stc_id = $this->request->get('stc_id');

		try {
			// 读取数据
			$list = array();
			$uda = &uda::factory('voa_uda_frontend_superreport_tablecol');
			$uda->member = array('m_uid' => $this->_user['ca_id']);
			$uda->init_tablecol($stc_id,  $list);
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		$this->message('success', '继续配置模板', get_referer($this->cpurl($this->_module, $this->_operation, 'config', $this->_module_plugin_id)), true);

	}

}

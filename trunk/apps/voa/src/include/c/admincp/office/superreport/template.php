<?php
/**
 * voa_c_admincp_office_superreport_template
* 企业后台/微办公管理/超级报表/模板列表
* Create By YanWenzhong
* $Author$
* $Id$
*/
class voa_c_admincp_office_superreport_template extends voa_c_admincp_office_superreport_base {

	public function execute() {

		try {
			// 载入uda类
			$uda_templates = &uda::factory('voa_uda_frontend_superreport_listtemplates');
			// 取回模板
			$templates = array();
			$uda_templates->result($templates);
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}
//print_r($templates);die;
		//保留字段
		$reserve_field = unserialize($this->_p_sets['reserve_field']);

		// 注入模板变量
		$this->view->set('templates', empty($templates) ? array() : array_values($templates));
		$this->view->set('fields', $reserve_field);
		$this->view->set('add_url', $this->cpurl($this->_module, $this->_operation, 'add', $this->_module_plugin_id));
		// 输出模板
		$this->output('office/superreport/template');
	}

}

<?php
/**
 * cpcustomer.php
 * 销售的个人主页
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_cpcustomer extends voa_c_frontend_travel_base {

	public function execute() {

		// 应用默认标题栏名称
		// 应用模板顶部也可以自定义 {$navtitle = '应用名称'}会覆盖掉此默认的名称
		//$this->view->set('navtitle', $this->_plugin['cp_name']);

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/cpcustomer');
	}

}

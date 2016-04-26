<?php
/**
 * cplist.php
 * 商品列表
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_cplist extends voa_c_frontend_travel_base {

	public function execute() {

		// 应用默认标题栏名称
		// 应用模板顶部也可以自定义 {$navtitle = '应用名称'}会覆盖掉此默认的名称
		//$this->view->set('navtitle', $this->_plugin['cp_name']);

		$this->view->set('classid', (int)$this->request->get('classid'));
		$this->view->set('page', (int)$this->request->get('page'));
		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/cplist');
	}

}

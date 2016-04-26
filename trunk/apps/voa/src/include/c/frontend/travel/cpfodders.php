<?php
/**
 * cpfodders.php
 * 商品素材列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_cpfodders extends voa_c_frontend_travel_base {

	public function execute() {

		$this->view->set('classid', (int)$this->request->get('classid'));
		$this->view->set('page', (int)$this->request->get('page'));
		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/cpfodders');
	}

}

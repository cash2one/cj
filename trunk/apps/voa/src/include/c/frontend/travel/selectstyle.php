<?php
/**
 * selectstyle.php
 * 选择规格
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_selectstyle extends voa_c_frontend_travel_basemp {

	public function execute() {

		$this->view->set('goodsid', $this->request->get('goodsid'));

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/selectstyle');
	}

}

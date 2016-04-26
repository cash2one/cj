<?php
/**
 * cpviewgoods.php
 * 查看商品详情
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_cpviewgoods extends voa_c_frontend_travel_base {

	public function execute() {

		$this->view->set('goodsid', $this->request->get('goodsid'));

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/cpviewgoods');
	}

}

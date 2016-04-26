<?php
/**
 * list.php
 * 移动CRM/商品列表
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_list extends voa_c_frontend_travel_basemp {

	public function execute() {

		//购物车数量
		$cart = new voa_uda_frontend_travel_cart();
		$cart_total = 0;
		$cart->get_cart_total($this->_user['openid'],$cart_total);

		$this->view->set('classid', (int)$this->request->get('classid'));
		$this->view->set('page', (int)$this->request->get('page'));
		$this->view->set('cart_total',$cart_total);

		// 应用默认标题栏名称
		// 应用模板顶部也可以自定义 {$navtitle = '应用名称'}会覆盖掉此默认的名称
		//$this->view->set('navtitle', $this->_plugin['cp_name']);

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/list');
	}

}

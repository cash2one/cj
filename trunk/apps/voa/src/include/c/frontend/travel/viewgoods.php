<?php
/**
 * viewgoods.php
 * 查看商品详情
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_travel_viewgoods extends voa_c_frontend_travel_basemp {

	public function execute() {

		//购物车数量
		$cart = new voa_uda_frontend_travel_cart();
		$cart_total = 0;
		$cart->get_cart_total($this->_user['openid'],$cart_total);

		$this->view->set('goodsid', $this->request->get('goodsid'));
		$this->view->set('page', (int)$this->request->get('page'));
		$this->view->set('cart_total',$cart_total);

		// 引入应用模板
		$this->_output('mobile_v1/'.$this->get_tpl_style().'/viewgoods');
	}

}

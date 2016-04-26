<?php
/**
 * 购物车-删除购物车中产品
 * $Author$	linshiling
 * $Id$
 * url:/api/order/get/cartdelete?cartid=6
 */
class voa_c_api_order_get_cartdelete extends voa_c_api_order_abstract {

	// 创建订单接口
	public function execute() {

		try {
			$cart = new voa_uda_frontend_travel_cart();
			$cartid = (array)$_GET['cartid'];
			// 删除购物车中产品
			$rs = $cart->delete($this->_member['openid'], $cartid);
			if (! $rs) {
				return $this->_set_errcode('修改产品数量失败');
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}


		return true;
	}
}

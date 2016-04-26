<?php
/**
 * 购物车-修改产品数量
 * $Author$	linshiling
 * $Id$
 * url:/api/order/get/cartupdate?cartid=6&num=10
 */

class voa_c_api_order_get_cartupdate extends voa_c_api_order_abstract {

	// 创建订单接口
	public function execute() {

		try {
			$cart = new voa_uda_frontend_travel_cart();
			$cartid = intval($_GET['cartid']);
			$num = intval($_GET['num']);
			if ($num < 1) {
				return $this->_set_errcode('400:数量不能小于1');
			}

			// 修改购物车中产品数量
			$rs = $cart->updatenum($this->_member['openid'], $cartid, $num);
			if (! $rs) {
				return $this->_set_errcode('401:修改产品数量失败:' . $cart->errmsg);
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		return true;
	}
}

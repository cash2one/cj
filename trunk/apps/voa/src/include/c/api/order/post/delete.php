<?php
/**
 * voa_c_api_order_post_new
 * 删除订单
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_post_delete extends voa_c_api_order_abstract {

	// 删除订单接口
	public function execute() {

		try {
			$openid = $this->_member['openid'];
			if (! $openid) {
				return $this->_set_errcode('无法获取openid');
			}
			$orderid = intval($_POST['orderid']);
			if (! $orderid) {
				return $this->_set_errcode('订单id错误');
			}

			$order = array();
			$rs = $this->uda->get_order($orderid, $order);
			if (! $rs) {
				return $this->_set_errcode($this->uda->errmsg);
			}
			if (! $order) {
				$this->_result = $orderid . '此订单不存在';
				return true;
			}
			if ($order['customer_openid'] != $openid) {
				return $this->_set_errcode('不能删除别人的订单');
			}

			// 不能删除已支付的订单
			$payed = array(
				voa_d_oa_travel_order::$PAY_SECCESS,
				voa_d_oa_travel_order::$PAY_SEND,
				voa_d_oa_travel_order::$PAY_SIGN
			);
			if (in_array($order['order_status'], $payed)) {
				return $this->_set_errcode('不能删除已支付的订单');
			}

			$this->uda->delete($orderid);
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}


		return true;
	}
}

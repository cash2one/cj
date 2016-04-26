<?php
/**
 * voa_c_api_order_post_new
 * 修改订单状态
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_post_update extends voa_c_api_order_abstract {

	// 修改订单状态接口
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
				return $this->_set_errcode('此订单不存在');
			}

			$data = array();

			if ($order['order_status'] < voa_d_oa_travel_order::$PAY_SECCESS) {
				$data['order_status'] =voa_d_oa_travel_order::$PAY_CANCEL;
				$this->uda->update($orderid,$data);
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}


		return true;
	}
}

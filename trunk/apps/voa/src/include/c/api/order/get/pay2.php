<?php
/**
 * voa_c_api_order_get_pay2
 * 继续支付
 * 返回支付参数
 * $Author$	linshiling
 * $Id$
 */

class voa_c_api_order_get_pay2 extends voa_c_api_order_abstract {

	//创建订单接口
	public function execute() {


		try {
			$orderid = intval($_GET['orderid']);
			if(!$orderid) {
				return $this->_set_errcode('订单id错误');
			}

			//继续支付
			$rs = $this->uda->get_order($orderid, $order);
			if(!$rs || !$order) {
				return $this->_set_errcode('获取订单信息错误');
			}
			$sn = $order['ordersn'];
			if(!$sn) {
				return $this->_set_errcode('订单编号错误');
			}
			$openid = $order['customer_openid'];
			if($openid != $this->_member['openid']) {
				//return $this->_set_errcode('openid不匹配');
			}


			//微信下单并返回支付参数
			$wepay = &service::factory('voa_wepay_service');
			$params = array();
			$rs = $wepay->wxpay2($params, $order['wx_orderid']);
			if(!$rs) {
				return $this->_set_errcode($this->uda->errmsg);
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		$this->_result = $params;

		return true;
	}
}

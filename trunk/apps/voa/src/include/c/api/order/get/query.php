<?php
/**
 * voa_c_api_order_get_query
 * 前端通知后台支付成功,但不是太可靠.
 * 默认情况下订单状态是(支付中)
 * 1.如果状态已经是成功,说明notify已经返回成功了,这里返回OK
 * 2.如果状态支付中,实时查询微信端订单状态,如果正确返回OK,否则什么也不做
 * 返回支付参数
 * $Author$	linshiling
 * $Id$
 */

class voa_c_api_order_get_query extends voa_c_api_order_abstract {

	//创建订单接口
	public function execute() {


		try {
			$orderid = intval($_GET['orderid']);
			if(!$orderid) {
				return $this->_set_errcode('订单id错误');
			}
			//返回订单
			$rs = $this->uda->get_order($orderid, $order);
			if(!$rs || !$order) {
				return $this->_set_errcode('获取订单信息错误');
			}
			$sn = $order['ordersn'];
			if(!$sn) {
				return $this->_set_errcode('订单编号错误');
			}
			$wx_orderid = $order['wx_orderid'];
			if(!$wx_orderid) {
				return $this->_set_errcode('微信订单号错误');
			}

			//如果已支付成功(说明已经微信已经notify过了)
			if($order['order_status'] == voa_d_oa_travel_order::$PAY_SECCESS) {
				return true;
			}

			//如果是支付中或未支付,说明notify没有来过,就调用查询接口
			if($order['order_status'] == voa_d_oa_travel_order::$PAY_ING || $order['order_status'] == voa_d_oa_travel_order::$PAY_NOT) {
				logger::error('前端成功,后台未成功,发起查询请求');
				$wepay = &service::factory('voa_wepay_service');
				$wx_order = array();
				$rs = $wepay->get_order($wx_order, $wx_orderid);
				logger::error('订单信息返回:'.var_export($wx_order, true));
				$d = new voa_d_oa_travel_order();
				if($wx_order['trade_state'] == 'SUCCESS') {
					$update = array(
						'order_status'	=>	voa_d_oa_travel_order::$PAY_SECCESS,
						'pay_time'		=>	startup_env::get('timestamp')
					);
					$rs = $d->update_by_conds($orderid, $update);
					if($rs) {
						return true;
					}else{
						return $this->_set_errcode('修改订单状态出错2');
					}
				}else{
					if($order['order_status'] != voa_d_oa_travel_order::$PAY_ING) {
						$update = array(
							'order_status'	=>	voa_d_oa_travel_order::$PAY_NOT
						);
						$rs = $d->update_by_conds($orderid, $update);
						if(!$rs) {
							return $this->_set_errcode('修改订单状态出错3');
						}
					}

					//查询也未成功
					return $this->_set_errcode('支付结果未返回,请稍候刷新订单页');
				}
			}

		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		return true;
	}
}

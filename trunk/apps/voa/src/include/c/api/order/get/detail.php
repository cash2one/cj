<?php
/**
 * voa_c_api_order_get_list
 * 订单详情
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_get_detail extends voa_c_api_order_abstract {

	public function execute() {


		try {
			$openid = $this->_member['openid'];
			//$openid = 'o06msuFDO7_xZOdSNAHZq6fe_zJ0';
			if(!$openid) {
				return $this->_set_errcode('无法获取openid');
			}

			$orderid = intval($_GET['orderid']);
			if(!$orderid) {
				return $this->_set_errcode('订单id错误');
			}

			$order = array();
			$rs = $this->uda->get_order($orderid, $order);
			if(!$rs) {
				return $this->_set_errcode($this->uda->errmsg);
			}
			if(!$order) {
				return $this->_set_errcode($orderid.'订单不存在');
			}
			if($order['customer_openid'] != $openid) {
				return $this->_set_errcode('不能查看别人的订单');
			}

			// 订单
			if ($order['order_status'] == voa_d_oa_travel_order::$PAY_NOT || $order['order_status'] == voa_d_oa_travel_order::$PAY_ING) {
				if ($order['created'] + 3600 > startup_env::get('timestamp')) {
					$order['repay'] = 1;
				} else {
					$order['repay'] = 0;
					// 一个小时未付款，设置订单状态（已取消）
					$order['order_status'] = voa_d_oa_travel_order::$PAY_CANCEL;
					$this->uda->update($orderid, array('order_status' => $order['order_status']));
					$order['_order_status'] ='已取消';
				}
			}else {
				$order['repay'] = 0;
			}


		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		// 读快递分类缓存
		$p_sets=voa_h_cache::get_instance()->get('plugin.goods.goodsexpress', 'oa');
		foreach ($p_sets as $k => $v) {
			if ((string)$v['expid'] ==  (string)$order['expid']) {
				$order['express_name'] = $v['exptype'];
				$order['express_price'] = $v['expcost'];
			}
		}

		$this->_result = $order;

		return true;
	}
}

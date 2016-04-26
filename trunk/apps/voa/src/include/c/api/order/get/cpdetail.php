<?php
/**
 * voa_c_api_order_get_cpdetail
 * 订单详情(企业号用户)
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_get_cpdetail extends voa_c_api_order_abstract {

	public function execute() {

		try {

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

			$d = new voa_d_oa_travel_ordergoods();
			$goods=$d->get_by_conds(array('order_id'=>$order['orderid']));
			if (!empty($goods)) {
                 if ($goods['saleuid'] != $this->_member['m_uid']) {
                 	return $this->_set_errcode('300:不能查看别人的订单');
                 }
			}else{
				return $this->_set_errcode('400:订单不存在');
			}


			// 订单
			if ($order['order_status'] == voa_d_oa_travel_order::$PAY_NOT || $order['order_status'] == voa_d_oa_travel_order::$PAY_ING) {
				if ($order['created'] + 3600 > startup_env::get('timestamp')) {
					$order['repay'] = 1;
				} else {
					$order['repay'] = 0;
					//一个小时未付款，设置订单状态（已取消）
					$order['order_status'] =voa_d_oa_travel_order::$PAY_CANCEL;
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
// var_dump($order);exit;
		$this->_result = $order;

		return true;
	}
}

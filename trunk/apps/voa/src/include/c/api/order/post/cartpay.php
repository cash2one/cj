<?php
/**
 * voa_c_api_order_post_cartpay
 * 购物车支付接口
 * 1.创建本地订单
 * 2.将购物车物品转移入订单产品表
 * 3.创建微信订单
 * 4.返回支付参数
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_post_cartpay extends voa_c_api_order_abstract {

	// 立即购买接口,返回支付参数
	public function execute() {

		try {
			// $_POST['sale_id'] = $this->_member['saleid'];

			if (! $_POST['cartids']) {
				return $this->_set_errcode('400:post数据为空');
			}

			$openid = $this->_member['openid'];
			if (! $openid) {
				return $this->_set_errcode('401:无法获取openid');
			}

			// 订单入库
			$sn = rgmdate(time(), 'YmdHis') . rand(100000, 999999); // 随机生成订单编号
			$data = array(
				'ordersn' => $sn,
				'customer_name' => $_POST['name'],
				'customer_openid' => $openid,
				'expid'=>intval($_POST['expid']),
				'mobile' => $_POST['phone'],
				'address' => $_POST['adr']
			);
			// 读销售信息,先从post参数读,如果没有再读客户默认绑定人
			$sale_id = 0;
			if (!empty($_POST['sale_id'])) {
				$sale_id = intval($_POST['sale_id']);
			}
			if (! $sale_id) {
				$sale_id = intval($this->_member['saleid']);
			}
			$data['sale_id'] = $sale_id;

			$order = array();
			$rs = $this->uda->cart_insert($data, $order, $_POST['cartids']);
			if (! $rs || ! $order) {
				return $this->_set_errcode('403:' . $this->uda->errmsg);
			}

			// 微信下单并返回支付参数
			$options = array(
				'openid' => $openid,
				'order_id' => $sn,
				'total_fee' => $order['amount'],
				'goods_name' => $order['goods_name']
			);
			$wepay = &service::factory('voa_wepay_service');
			$params = array();
			$rs = $wepay->wxpay($params, $wx_orderid, $options);
			if (! $rs) {
				return $this->_set_errcode($this->uda->errmsg);
			}

			if ($wx_orderid) {
				$rs = $this->uda->set_wx_order($sn, $wx_orderid);
				if (! $rs) {
					return $this->_set_errcode('402:关联微信订单号失败');
				}
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		$this->_result = array('orderid' => $order['orderid'], 'pay_params' => $params);

		return true;
	}
}

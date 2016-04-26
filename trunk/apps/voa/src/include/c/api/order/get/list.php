<?php
/**
 * voa_c_api_order_get_list
 * 订单列表
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_get_list extends voa_c_api_order_abstract {

	public function execute() {

		try {
			// $openid = 'o06msuFDO7_xZOdSNAHZq6fe_zJ0';
			if (!empty($_GET['type']) && $_GET['type'] == 'client') {
				// 客户端
				$openid = $this->_member['openid'];
				// $openid = 'o06msuFDO7_xZOdSNAHZq6fe_zJ0';
				if (! $openid) {
					return $this->_set_errcode('400:无法获取openid');
				}

				$where = array(
					'customer_openid' => $openid
				);
			} else {
				$where = array(
					'saleuid' => $this->_member['m_uid']
				);
			}

			if (! empty($_GET['today'])) {
				$where['pay_time > ?'] = mktime(0, 0, 0, date('m'), data('d'), data('Y'));
			}
			// $where = array('customer_openid' => $openid);

			$page = $_GET['page'] ? intval($_GET['page']) : 1;
			$size = $_GET['limit'] ? intval($_GET['limit']) : 20;
			$list = array();
			if (!empty($_GET['type']) && $_GET['type'] == 'client') {
				$rs = $this->uda->get_customer_list($where, $page, $size, $list, $total);
			}else {
				$rs = $this->uda->get_list($where, $page, $size, $list, $total);
			}
			if (! $rs) {
				return $this->_set_errcode($this->uda->errmsg);
			}

			// 读取产品图片
			$d_data = new voa_d_oa_goods_data();
			$goods_id = array();
			foreach ($list as $_order) {
				foreach ($_order['goods_list'] as $_goods) {
					$goods_id[] = $_goods['goods_id'];
				}
			}


			// 取封面键值
			$tablecols = voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa');
			$f_id = 0;
			foreach ($tablecols as $_col) {
				if ('cover' == $_col['fieldalias']) {
					$f_id = $_col['field'];
				}
			}

			// 读取数据
			$goodslist = $d_data->list_by_conds(array(
				'dataid' => $goods_id
			));
			$covers = array();
			$goodslist = is_array($goodslist) ? $goodslist : array();
			foreach ($goodslist as $_goods) {
				$_ar = unserialize($_goods['diys']);
				if (!empty($_ar[$f_id])) {
					$covers[$_goods['dataid']] = '/attachment/read/' . $_ar[$f_id];
				} else {
					$covers[$_goods['dataid']] = '/attachment/read/0';
				}
			}

			// 把封面图放回订单信息
			foreach ($list as &$_order) {
				foreach ($_order['goods_list'] as &$_goods) {
					$_goods['cover'] = empty($covers[$_goods['goods_id']]) ? '' : $covers[$_goods['goods_id']];
				}

				// 订单
				if ($_order['created'] + 7200 * 0.8 > startup_env::get('timestamp')) {
					$_order['repay'] = 1;
				} else {
					$_order['repay'] = 0;
				}
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}
		$this->_result = array('list' => $list, 'total' => $total);

		return true;
	}
}

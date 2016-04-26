<?php
/**
 * 购物车产品列表
 * /api/order/get/cartlist
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_get_cartlist extends voa_c_api_order_abstract {

	public function execute() {

		try {
			$page = (int)$this->_get('page');
			$limit = (int)$this->_get('limit');
			$perpage = 10;
			if (!empty($this->_p_sets['perpage'])) {
				$perpage = $this->_p_sets['perpage'];
			}
			$limit = 0 >= $limit ? $perpage : $limit;
			list($start, $perpage, $page) = voa_h_func::get_limit($page, $limit);

			// 查询条件
			$conds = array();
			$cartids = $this->request->get('cartids');
			if (! empty($cartids)) {
				$cartids = is_array($cartids) ? $cartids : explode(",", $cartids);
				$conds['cartid'] = $cartids;
			}

			$cart = new voa_uda_frontend_travel_cart();
			$list = array();
			$rs = $cart->get_list($this->_member['openid'], $list, $conds, array($start, $perpage));
			if (! $rs) {
				return $this->_set_errcode($this->uda->errmsg);
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		$pay = $this->request->get('pay');
		if (!empty($pay)) {
			$p_sets = voa_h_cache::get_instance()->get('plugin.goods.goodsexpress', 'oa');
			$list['p_sets'] = $p_sets;
		}
		$this->_result = $list;

		return true;
	}
}

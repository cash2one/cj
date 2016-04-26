<?php
/**
 * voa_c_api_travel_get_customerbuyhist
 * 获取客户购买的商品信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_customerbuyhist extends voa_c_api_travel_abstract {

	public function execute() {

		// 获取分页参数
		$customer_id = (int)$this->_get('cid');
		
		// 读取数据
		$total = 0;
		$list = array();
		
		$uda = new voa_uda_frontend_travel_order();
		$uda->get_goods_by_id($customer_id, $list, $total, 'customer_id');
		
		$this->_result = array(
			'total' => $total,
			'data' => empty($list) ? array() : array_values($list)
		);

		return true;
	}

}

<?php
/**
 * voa_c_api_order_get_list
 * 订单列表
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_get_listtosales extends voa_c_api_order_abstract {

	public function execute() {

		try {
			
			$pay_time = 0;
			if ($_GET['today']) {
				$pay_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
			}			
			
			$uda = new voa_uda_frontend_travel_order(); 
			$sale_id = 0;
			if ($this->_member['m_uid']) {
				$sale_id = $this->_member['m_uid'];
			}
			$uda->get_goods_by_id($sale_id, $list, $total, 'sale_id', $pay_time);
			
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		$this->_result = array('list' => $list, 'total' => $total);

		return true;
	}
}

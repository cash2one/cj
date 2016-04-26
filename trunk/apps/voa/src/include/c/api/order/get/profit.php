<?php
/**
 * 我的业绩,按周
 * /api/order/get/profit
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_get_profit extends voa_c_api_order_abstract {

	public function execute() {

		try {

			//本周业绩
			$startdate = addslashes($_GET['startdate']);
			$enddate = addslashes($_GET['enddate']);
			$where = array(
				'sale_id'	=>	$this->_member['m_uid'],
				'created>?'	=>	strtotime($startdate),
				'created<?'	=>	strtotime($enddate) + 86399
			);
			$profit = 0;
			$rs = $this->uda->profit($where, $profit);
			if(!$rs) {
				return $this->_set_errcode('获取本周业绩失败');
			}

			//本周订单列表
			$list = array();
			$rs = $this->uda->get_list($where, 1, 9999, $list);
			if(!$rs) {
				return $this->_set_errcode($this->uda->errmsg);
			}

		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		$this->_result = array('profit' => $profit, 'list' => $list);

		return true;
	}
}

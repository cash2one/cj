<?php
/**
 * 直销员首页接口
 * /api/order/get/index
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_get_index extends voa_c_api_order_abstract {

	public function execute() {
		//$this->_member['m_uid'] = 'o06msuFDO7_xZOdSNAHZq6fe_zJ0';
		//调试参数,最后注释掉
		try {

			//昨天提成
			$yesterday = 0;
			$where = array(
				'sale_id'	=>	$this->_member['m_uid'],
				'created>?'	=>	strtotime('yesterday'),
				'created<?'	=>	strtotime('today'),
				'order_status' => 2
			);
			$rs = $this->uda->profit($where, $yesterday);

			if(!$rs) {
				return $this->_set_errcode($this->uda->errmsg);
			}

			//今日提成
			$today = 0;
			$where = array(
				'sale_id'	=>	$this->_member['m_uid'],
				'created>?'	=>	strtotime('today'),
				'order_status' => 2
			);
			$rs = $this->uda->profit($where, $today);

			//今日订单数
			$where = array(
				'sale_id'	=>	$this->_member['m_uid'],
				'created>?'	=>	strtotime('today'),
			);
			$rs = $this->uda->get_list($where, 1, 999, $list, $order_num);

		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		$this->_result = array('yesterday' => $yesterday, 'today' => $today, 'order_num' => $order_num);

		return true;
	}
}

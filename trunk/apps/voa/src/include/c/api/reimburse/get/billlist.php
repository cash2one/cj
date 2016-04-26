<?php
/**
 * voa_c_api_reimburse_list
 * 搜索报销列表
 * $Author$
 * $Id$
 */

class voa_c_api_reimburse_get_billlist extends voa_c_api_reimburse_base {


	public function execute() {

		/** 读取清单信息 */
		$bills = array();
		$this->_get_bill_by_uid($bills, startup_env::get('wbs_uid'), 0, 0, self::BILL_NORMAL);


		// 输出结果
		$this->_result = array(
			'data' => $bills ? array_values($bills) : array()
		);

		return true;
	}


}

<?php
/**
 * voa_c_api_travel_get_turnover
 * 统计业绩
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_turnover extends voa_c_api_travel_goodsabstract {

	public function execute() {

		$uda_to = new voa_uda_frontend_travel_turnover_get();
		// 取销售提成/业绩
		$params = array(
			'saleuid' => array($this->_member['m_uid']),
			'start_date' => $this->request->get('start_date'),
			'end_date' => $this->request->get('end_date')
		);
		$total = array();
		if (!$uda_to->execute($params, $to_total)) {
			$this->_error_message('销售信息读取失败');
			return true;
		}

		$this->_result = $to_total;
	}

}

<?php
/**
 * voa_c_api_superreport_get_month
 * 获取超级报表月报
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_get_month extends voa_c_api_superreport_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 门店ID
			'csp_id' => array('type' => 'int', 'required' => true),
			// 年份
			'year' => array('type' => 'int', 'required' => false),
			// 月份
			'month' => array('type' => 'int', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		if (empty($this->_params['year'])) {
			$this->_params['year'] = rgmdate(time(), 'Y');
		}
		if (empty($this->_params['month'])) {
			$this->_params['month'] = rgmdate(time(), 'm');
		}



		try {
			// 获取数据
			$result = array();
			$uda = &uda::factory('voa_uda_frontend_superreport_month', $this->_ptname);
			$uda->get_monthreport($this->_params,  $result);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		// 输出结果
		$this->_result = array(
			'csp_id' => $this->_params['csp_id'],
			'csp_name' => $result['csp_name'],
			'year' => $this->_params['year'],
			'month' => $this->_params['month'],
			'report' => isset($result['report']) ? $result['report'] : array()
		);

		return true;
	}

}


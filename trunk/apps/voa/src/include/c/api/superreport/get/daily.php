<?php
/**
 * voa_c_api_superreport_get_daily
 * 获取超级报表日报数据
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_get_daily extends voa_c_api_superreport_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 门店ID
			'csp_id' => array('type' => 'int', 'required' => true),
			// 日期
			'date' => array('type' => 'string', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		if (empty($this->_params['date'])) {
			$this->_params['date'] = rgmdate(time(), 'Y-m-d');
		}

		$uid = $this->_member['m_uid'];
		$this->_params['uid'] = $uid;

		// 获取数据
		$result = array();
		$uda = &uda::factory('voa_uda_frontend_superreport_daily', $this->_ptname);

		try{
			$uda->get_daily($this->_params,  $result);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		$this->_result = empty($result) ? array('reporttime' => $this->_params['date']) : $result;


		return true;
	}

}


<?php
/**
 * voa_c_api_superreport_get_shops
 * 获取    已提交报表|未提交报表 |全部    门店列表
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_get_monthshops extends voa_c_api_superreport_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 标识
			'year' => array('type' => 'string', 'required' => false),
			// 时间
			'month' => array('type' => 'string', 'required' => false)
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		if (!$this->_params['year']) {
			$this->_params['year'] = rgmdate(time(),'Y');;
		}
		if (!$this->_params['month']) {
			$this->_params['month'] = rgmdate(time(),'m');
		}

		$this->_params['uid'] = $this->_member['m_uid'];

		// 获取数据
		$result = array();
		$uda = &uda::factory('voa_uda_frontend_superreport_shops', $this->_ptname);

		try{
			$uda->list_shops_for_month($this->_params,  $result);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		// 输出结果
		$this->_result = array(
			'year' => $this->_params['year'],
			'month' => $this->_params['month'],
			'list' => $result['list'],
			'total' => $result['total'],
		);

		return true;
	}

}


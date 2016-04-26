<?php
/**
 * voa_c_api_superreport_get_shops
 * 获取    已提交报表|未提交报表 |全部    门店列表
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_get_shops extends voa_c_api_superreport_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 标识
			'ident' => array('type' => 'string', 'required' => false),
			// 时间
			'date' => array('type' => 'string', 'required' => false)
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		if (!$this->_params['ident']) {
			$this->_params['ident'] = 3;
		}
		if (!$this->_params['date']) {
			$this->_params['date'] = rgmdate(time(),'Y-m-d');
		}
		$this->_params['uid'] = $this->_member['m_uid'];

		// 获取数据
		$result = array();
		$uda = &uda::factory('voa_uda_frontend_superreport_shops', $this->_ptname);

		try{
			$uda->list_shops($this->_params,  $result);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		// 输出结果
		$this->_result = array(
			'ident' => $this->_params['ident'],
			'date' => $this->_params['date'],
			'all_list' => $result['all_list'],
			'all_total' => $result['all_total'],
			'submited_list' => $result['submited_list'],
			'submited_total' => $result['submited_total'],
			'unsubmited_list' => $result['unsubmited_list'],
			'unsubmited_total' => $result['unsubmited_total']
		);

		return true;
	}

}


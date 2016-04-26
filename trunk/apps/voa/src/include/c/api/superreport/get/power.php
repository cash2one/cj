<?php
/**
 * voa_c_api_superreport_get_power
 * 获取用户权限
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_get_power extends voa_c_api_superreport_abstract {

	public function execute() {

		// 获取数据
		$result = array();
		$uda = &uda::factory('voa_uda_frontend_superreport_power', $this->_ptname);
		$uda->member = $this->_member;

		try{
			$uda->get_power($this->_params,  $result);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		// 输出结果
		$this->_result = $result;

		return true;

	}

}


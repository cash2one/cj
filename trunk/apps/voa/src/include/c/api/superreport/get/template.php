<?php
/**
 * voa_c_api_superreport_get_template
 * 获取超级报表模板
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_get_template extends voa_c_api_superreport_abstract {

	public function execute() {

		try {
			// 获取数据
			$result = array();
			$uda = &uda::factory('voa_uda_frontend_superreport_template', $this->_ptname);
			$uda->member = $this->_member;
			$uda->get_template($this->_params,  $result);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
			$this->_result = array();
			return true;
		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}

		// 输出结果
		$this->_result = array(
			'int' => $result['int'] ? array_values($result['int']) : array(),
			'text' => $result['text'] ? array_values($result['text']) : array()
		);

		return true;

	}

}


<?php
/**
 * voa_c_api_reimburse_post_billnew
 * 新增报销清单
 * $Author$
 * $Id$
 */

class voa_c_api_reimburse_post_billnew extends voa_c_api_reimburse_base {

	public function execute() {

		// 需要的参数
		$fields = array(
			/** 类型 */
			'type' => array('type' => 'string_trim', 'required' => true),
			/** 账单发生时间 */
			'time' => array('type' => 'string_trim', 'required' => true),
			/** 花费 */
			'expend' => array('type' => 'string_trim', 'required' => true),
			/** 原因 */
			'reason' => array('type' => 'string_trim', 'required' => true),
		);

		// 基本验证检查
		if (!$this->_check_params($fields)) {
			//return false;
		}
		// 类型检查
		if (empty($this->_params['type'])) {
			return $this->_set_errcode(voa_errcode_api_reimburse::NEW_TYPE_NULL);
		}
		// 账单发生时间检查
		if (empty($this->_params['time'])) {
			return $this->_set_errcode(voa_errcode_api_reimburse::NEW_TIME_NULL);
		}
		// 花费检查
		if (empty($this->_params['expend'])) {
			return $this->_set_errcode(voa_errcode_api_reimburse::NEW_EXPEND_NULL);
		}
		// 原因检查
		if (empty($this->_params['reason'])) {
			return $this->_set_errcode(voa_errcode_api_reimburse::NEW_REASON_NULL);
		}

		//入库操作
		if (!$this->_add()) {
			return false;
		}

		$this->_result = array(
			'id' => $this->_return['rbb_id']
		);

		return true;

	}

	/**
	 * 新增账单
	 * @return boolean
	 */
	protected function _add() {
		$uda = &uda::factory('voa_uda_frontend_reimburse_insert');
		/** 报销清单信息 */
		$bill = array();
		if (!$uda->reimburse_bill_new($bill)) {
			//$this->_error_message($uda->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}
		$this->_return = $bill;

		return true;
	}
}

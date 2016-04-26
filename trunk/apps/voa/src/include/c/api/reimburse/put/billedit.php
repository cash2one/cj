<?php
/**
 * voa_c_api_reimburse_put_billedit
 * 编辑报销清单
 * $Author$
 * $Id$
 */

class voa_c_api_reimburse_put_billedit extends voa_c_api_reimburse_base {

	public function execute() {
		// 需要的参数
		$fields = array(
			/** 明细表ID */
			'id' => array('type' => 'string_trim', 'required' => true),
			/** 类型 */
			'type' => array('type' => 'int', 'required' => true),
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

		$rbb_id = $this->_params['id'];
		$serv_b = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		$bill = $serv_b->fetch_by_id($rbb_id);
		if (empty($rbb_id) || empty($bill)) {
			//$this->_error_message('reimburse_bill_is_not_exists');
			return $this->_set_errcode(voa_errcode_api_reimburse::REIMBURSE_BILL_IS_NOT_EXISTS);
		}

		if ($bill['m_uid'] != $this->_member['m_uid']) {
			//$this->_error_message('no_privilege');
			return $this->_set_errcode(voa_errcode_api_reimburse::NO_PRIVILEGE);
		}

		/** 格式化清单数据 */
		$fmt = &uda::factory('voa_uda_frontend_reimburse_format');
		if (!$fmt->reimburse_bill($bill)) {
			//$this->_error_message($fmt->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		//入库操作
		if (!$this->_edit($bill)) {
			return false;
		}

		$this->_result = array(
			'id' => $this->_return['rbb_id']
		);

		return true;
	}

	/**
	 * 获取索引值
	 * @param unknown $type
	 */
	protected function _get_type_index($type, &$type_index) {
		foreach ($this->_p_sets['types'] as $k => $v) {
			if ($type == $k) {
				break;
			}

			$type_index ++;
		}

		return true;
	}

	/**
	 * 修改操作
	 * @param unknown $bill
	 * @return boolean
	 */
	protected function _edit($bill) {
		$uda = &uda::factory('voa_uda_frontend_reimburse_update');
		/** 报销清单信息 */
		if (!$uda->reimburse_bill_update($bill)) {
			//$this->_error_message($uda->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}
		$this->_return = $bill;

		return true;
	}
}

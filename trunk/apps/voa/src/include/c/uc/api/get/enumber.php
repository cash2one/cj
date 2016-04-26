<?php
/**
 * enumber
 * 企业号检查接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_api_get_enumber extends voa_c_uc_api_base {

	public function execute() {

		// 可接受的参数
		$fields = array(
			// 企业号
			'enumber' => array('type' => 'string', 'required' => true),
			// 检查类型：exists检查企业号是否存在、validator校验合法性
			'type' => array('type' => 'string', 'required' => true)
		);

		// 基本变量检查和过滤
		$this->_check_params($fields);

		$this->_params['enumber'] = trim($this->_params['enumber']);
		$this->_params['type'] = rstrtolower($this->_params['type']);
		if (!in_array($this->_params['type'], array('exists', 'validator'))) {
			$this->_set_errcode(voa_errcode_uc_enumber::EN_TYPE_ERROR);
			return false;
		}

		$uda_enterprise = &uda::factory('voa_uda_uc_enterprise');
		/*
		 * 企业号检查：
		 * 企业号格式非法 或 企业号存在均返回FALSE
		 * 企业号合法且不存在企业号则返回TRUE
		 */
		$check_result = $uda_enterprise->check_enterprise_enumber($this->_params['enumber'], 0);


		if ($this->_params['type'] == 'exists') {
			// 检查企业号是否存在

			if ($check_result) {
				// 企业号不存在
				$this->errcode = 0;
				$this->errmsg = '';
				$this->result[$this->_params['type']] = 0;
			} else {
				if ($uda_enterprise->errcode == voa_errcode_uc_system::UC_ENUMBER_EXISTS) {
					// 企业号存在
					$this->errcode = 0;
					$this->errmsg = '';
					$this->result[$this->_params['type']] = 1;
				} else {
					// 企业号不存在
					$this->errcode = 0;
					$this->errmsg = '';
					$this->result[$this->_params['type']] = 0;
				}
			}

		} elseif ($this->_params['type'] == 'validator') {
			// 校验企业号输入合法性

			$this->errcode = $uda_enterprise->errcode > 1 ? $uda_enterprise->errcode : 0;
			$this->errmsg = $uda_enterprise->errcode > 1 ? $uda_enterprise->errmsg : '';
			$this->result[$this->_params['type']] = $check_result ? 1 : 0;
		}

		return true;
	}

}

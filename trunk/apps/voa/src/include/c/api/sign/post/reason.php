<?php

/**
 * 更新备注
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_sign_post_reason extends voa_c_api_sign_base {

	public function execute() {

		// 接受的参数
		$fields = array (
			'id' => array ('type' => 'int', 'required' => true),
			'reason' => array ('type' => 'string', 'required' => true)
		);

		// 基本变量检查
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 验证备注
		if (!validator::is_string_count_in_range($this->_params['reason'], 0, 240)) {
			return $this->_set_errcode(voa_errcode_api_sign::REASON_TOO_LONG, 240);
		}

		/** 入库操作 */
		if (!$this->_insert()) {
			return $this->_set_errcode(voa_errcode_api_sign::INSERT_FAIL);
		}

		return true;
	}

	/** 提交编辑 */
	protected function _edit() {
		$serv_sr = &Service::factory('voa_s_oa_sign_detail');

		$data = array (
			'sd_reason' => $this->_params['reason']
		);
		$conds['sr_id'] = $this->_params['id'];
		if (!$serv_sr->update_by_conds($conds, $data)) {
			return false;
		}

		return true;
	}

	/** 提交插入 */
	protected function _insert() {
		$serv_sr = &Service::factory('voa_s_oa_sign_detail');
		if (!empty($this->_params['reason'])) {
			$data = array (
				'sd_reason' => $this->_params['reason'],
				'sr_id' => $this->_params['id']
			);
			if (!$serv_sr->insert($data)) {
				return false;
			}
		}

		return true;
	}

}

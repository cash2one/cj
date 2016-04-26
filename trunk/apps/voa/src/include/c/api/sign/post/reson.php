<?php

/**
 * 备注操作
 * $Author$
 * $Id$
 */
class voa_c_api_sign_post_reson extends voa_c_api_sign_base {

	public function execute() {
		// 验证数据
		if (!$this->__execute()) {
			return false;
		}

		// 验证备注
		if (empty($this->_params['reson'])) {
			return $this->_set_errcode(voa_errcode_api_sign::RESON_NO_NULL);
		}

		$sr_id = $this->_params['id'];

		/** 读取签到信息 */
		$serv_sr = &service::factory('voa_s_oa_sign_record', array ('pluginid' => startup_env::get('pluginid')));
		$this->_record = $serv_sr->fetch_by_id($sr_id);

		/** 检查是否有存在 */
		if (empty($this->_record)) {
			return $this->_set_errcode(voa_errcode_api_sign::NO_PRIVILEGE);
		}

		/** 读取签到备注信息 */
		$serv_sr = &service::factory('voa_s_oa_sign_detail', array ('pluginid' => startup_env::get('pluginid')));
		$this->_detail = $serv_sr->fetch_all_by_sr_id($sr_id);

		/** 入库操作 */
		if (!empty($this->_detail)) {
			if (!$this->_edit()) {
				return $this->_set_errcode(voa_errcode_api_sign::EDIT_FAIL);

				return false;
			}
		} else {
			if (!$this->_insert()) {
				return $this->_set_errcode(voa_errcode_api_sign::INSERT_FAIL);

				return false;
			}
		}

		return true;
	}

	/**
	 * 验证数据
	 * @return bool
	 */
	private function __execute() {
		// 接受的参数
		$fields = array (
			'id' => array ('type' => 'int', 'required' => true),
			'reson' => array ('type' => 'string', 'required' => true)
		);

		// 基本变量检查
		if (!$this->_check_params($fields)) {
			return false;
		}

		return true;
	}

	/** 提交编辑 */
	protected function _edit() {
		$params['sd_reason'] = $this->_params['reson'];
		$condition['sr_id'] = $this->_params['id'];

		$serv_sr = &service::factory('voa_s_oa_sign_detail', array ('pluginid' => startup_env::get('pluginid')));
		if (!$serv_sr->update($params, $condition)) {
			return false;
		}

		return true;
	}

	/** 提交插入 */
	protected function _insert() {
		$params['sd_reason'] = $this->_params['reson'];

		$serv_sr = &service::factory('voa_s_oa_sign_detail', array ('pluginid' => startup_env::get('pluginid')));
		if (!$serv_sr->insert($params)) {
			return false;
		}

		return true;
	}

}

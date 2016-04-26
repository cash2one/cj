<?php

/**
 * 添加备注
 * $Author$
 * $Id$
 */
class voa_c_frontend_sign_reply extends voa_c_frontend_sign_base {

	public function execute() {
		/** 如果不是 post 提交 */
		if (!$this->_is_post()) {
			$this->_error_message('submit_invalid');

			return false;
		}

		$sr_id = $this->request->get('sr_id');
		if (empty($sr_id)) {
			$this->_error_message('请先签到后再添加备注');

			return false;
		}
		/** 读取签到备注信息 */
		$serv_sr = &service::factory('voa_s_oa_sign_detail', array('pluginid' => startup_env::get('pluginid')));
		$this->_detail = $serv_sr->fetch_all_by_sr_id($sr_id);

		/** 入库操作 */
		$msg = '备注添加成功';
		if (!empty($this->_detail)) {
			$msg = '备注修改成功';
			if (!$this->_edit()) {
				$this->_error_message('edit_fail');

				return false;
			}
		} else {
			if (!$this->_insert()) {
				$this->_error_message('insert_fail');

				return false;
			}
		}

		/** 提示操作成功 */
		$this->_success_message($msg, "/frontend/sign/index");
	}

	/** 提交编辑 */
	protected function _edit() {
		$params['sd_reason'] = $this->request->get('message');
		$condition['sr_id'] = $this->request->get('sr_id');

		$serv_sr = &service::factory('voa_s_oa_sign_detail', array('pluginid' => startup_env::get('pluginid')));
		if (!$serv_sr->update($params, $condition)) {
			return false;
		}

		return true;
	}

	/** 提交插入 */
	protected function _insert() {
		$params['sd_reason'] = $this->request->get('message');
		$params['sr_id'] = $this->request->get('sr_id');
		$serv_sr = &service::factory('voa_s_oa_sign_detail', array('pluginid' => startup_env::get('pluginid')));
		if (!$serv_sr->insert($params)) {
			return false;
		}

		return true;
	}
}

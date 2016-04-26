<?php
/**
 * voa_c_admincp_office_project_edit
 * 企业后台/微办公管理/工作台/编辑项目
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_project_edit extends voa_c_admincp_office_project_base {

	public function execute() {

		$p_id = $this->request->get('p_id');
		$p_id = rintval($p_id, false);
		$project = $this->_get_project($this->_module_plugin_id, $p_id);
		if (!$project) {
			$this->message('error', '指定项目不存在或已被删除');
		}

		if ($this->_is_post()) {
			$update = $this->_project_field_check($_POST, $project);
			if (empty($update)) {
				$this->message('error', '项目信息未发生变动无须提交更新');
			}
			if (!is_array($update)) {
				$this->message('error', $update);
			}
			$this->_service_single('project', $this->_module_plugin_id, 'update', $update, array('p_id' => $p_id));
			$this->message('success', '更新项目信息操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
		}

		$this->view->set('project', $project);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('p_id' => $p_id)));

		$this->output('office/project/edit_form');

	}

	/**
	 * 检查项目更新字段
	 * @param array $new
	 * @param array $old
	 * @return array | string
	 */
	protected function _project_field_check($new = array(), $old = array()) {
		/** 待更新的字段 */
		$update = array();

		/** 项目名称 */
		if (isset($new['p_subject']) && is_string($new['p_subject']) && $new['p_subject'] != $old['p_subject']) {
			if (!$new['p_subject'] || !validator::is_len_in_range($new['p_subject'], 1, 254)) {
				return '项目名称长度必须小于255字节';
			}
			$update['p_subject'] = $new['p_subject'];
		}

		/** 设置相关的开启状态 */
		if (isset($new['p_status']) && is_scalar($new['p_status']) && in_array($new['p_status'], $this->_project_open_config) && $new['p_status'] != $old['p_status']) {
			if ($new['p_status'] == $this->_project_open_config['close']) {
				//如果当前设置为关闭
				$update['p_status'] = $new['p_status'];
			} elseif ($old['p_status'] == $this->_project_open_config['close']) {
				//如果设置为开启，若之前状态为关闭则更新
				$update['p_status'] = $this->_project_open_config['open'];
			}
		}

		/** 更新项目时间 */
		if (isset($new['p_begintime']) && isset($new['p_endtime'])) {
			if (!validator::is_date($new['p_begintime'])) {
				return '请正确设置项目开启时间';
			}
			if (!validator::is_date($new['p_endtime'])) {
				return '请正确设置项目关闭时间';
			}
			$begintime = rstrtotime($new['p_begintime'].' 00:00:00');
			$endtime = rstrtotime($new['p_endtime'].' 23:59:59');

			if ($begintime >= $endtime) {
				return '项目结束时间必须大于开始时间';
			}

			if ($old['p_endtime'] > startup_env::get('timestamp')) {
				//如果原来项目结束时间未到期，则判断项目时间与当前时间关系
				if ($endtime != $old['p_endtime'] && $endtime < startup_env::get('timestamp')) {
					return '项目结束时间必须大于当前时间';
				}
			}

			if ($begintime != $old['p_begintime']) {
				$update['p_begintime'] = $begintime;
			}
			if ($endtime != $old['p_endtime']) {
				$update['p_endtime'] = $endtime;
			}
		}

		/** 项目备注 */
		if (isset($new['p_message']) && is_scalar($new['p_message']) && $new['p_message'] != $old['p_message']) {
			if ($new['p_message'] && !validator::is_len_in_range($new['p_message'], 0, 5000)) {
				return '项目备注文字长度不能超过5000字节';
			}
			$update['p_message'] = $new['p_message'];
		}

		return $update;
	}

}

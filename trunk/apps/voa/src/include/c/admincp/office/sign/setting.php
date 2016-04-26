<?php

/**
 * voa_c_admincp_office_sign_setting
 * 企业后台/微办公管理/考勤签到/考勤设置
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sign_setting extends voa_c_admincp_office_sign_base {
	public function execute() {
		$post = $this->request->postx();

		$serv_set = &service::factory('voa_s_oa_sign_setting');
		if (!empty($post)) {
			$data = array();

			$this->getact($post, $data);
			$result = $this->update($data);

			if ($result) {
				$this->message('success', '修改成功', $this->cpurl($this->_module, $this->_operation, 'setting', $this->_module_plugin_id), false);
			} else {
				$this->message('error', '修改失败');
			}
		}
		//发送数据
		$conds['ss_key'] = 'late_range';
		$conds_r['ss_key IN (?)'] = array('late_range', 'leave_early_range', 'ibeacon_set', 'permission');

		// 页面列表数据
		$list = $serv_set->list_by_conds($conds_r);
		foreach ($list as $val) {
			if ($val['ss_key'] == 'late_range') {
				$late_range = $val['ss_value'];
			}
			if ($val['ss_key'] == 'leave_early_range') {
				$leave_early_range = $val['ss_value'];
			}
			if ($val['ss_key'] == 'permission') {
				$permission = $val['ss_value'];
			}
		}

		$this->view->set('late_range', $late_range);
		$this->view->set('leave_early_range', $leave_early_range);
		$this->view->set('permission', $permission);
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		$this->output('office/sign/setting');
	}

	/**
	 * 处理获取的数据
	 * @param $in
	 * @param $data
	 */
	public function getact($in, &$data) {
		if (empty($in['late_range']) && $in['late_range'] != 0) {
			$this->message('error', '请设置迟到时间范围');
		}
		if (empty($in['leave_early_range']) && $in['leave_early_range'] != 0) {
			$this->message('error', '请设置早退时间范围');
		}
		$data['late_range'] = $in['late_range'];
		$data['leave_early_range'] = $in['leave_early_range'];
		if (empty($in['permission'])) {
			$data['permission'] = '0';
		} else {
			$data['permission'] = '1';
		}

	}

	/**
	 * 更新数据
	 * @param $data
	 * @return mixed
	 */
	public function update($data) {
		$serv_set = &service::factory('voa_s_oa_sign_setting');

		$serv_set->update('late_range', array('ss_value' => $data['late_range']));
		$serv_set->update('leave_early_range', array('ss_value' => $data['leave_early_range']));
		$serv_set->update('permission', array('ss_value' => $data['permission']));

		return $data;
	}

}

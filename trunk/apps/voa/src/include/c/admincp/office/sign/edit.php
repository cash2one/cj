<?php

/**
 * voa_c_admincp_office_sign_edit
 * 企业后台/微办公管理/考勤签到/编辑状态
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sign_edit extends voa_c_admincp_office_sign_base {

	public function execute() {

		$sr_id = $this->request->get('sr_id');
		$sr_id = rintval($sr_id, false);
		if (!$sr_id || !($signRecord = parent::_get_sign_record($sr_id))) {
			$this->message('error', '指定签到记录不存在');
		}

		if ($this->_is_post()) {
			$this->_reset_sign_status_submit($this->_module_plugin_id, $signRecord);
		}

		list($detailTotal, $detailMulti, $detailList) = self::_get_detail($this->_module_plugin_id, $sr_id, 10);

		$this->view->set('signRecord', $signRecord);
		$this->view->set('signStatusSet', $this->_sign_status_set);
		$this->view->set('signStatus', $this->_sign_status);
		$this->view->set('detailList', $detailList);
		$this->view->set('detailTotal', $detailTotal);
		$this->view->set('detailMulti', $detailMulti);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('sr_id' => $sr_id)));

		$this->output('office/sign/edit');
	}

	/**
	 * 获取指定签到记录的重设备注日志
	 * @param number $sr_id
	 * @param number $perpage
	 * @return array(total, multi, list)
	 */
	protected function _get_detail($cp_pluginid, $sr_id = 0, $perpage = 10) {
		$total = $this->_service_single('sign_detail', $cp_pluginid, 'count_all_by_sr_id', $sr_id);
		$list = array();
		$multi = '';
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$tmp = $this->_service_single('sign_detail', $cp_pluginid, 'fetch_all_by_sr_id', $sr_id, $pagerOptions['start'], $pagerOptions['per_page']);
			foreach ($tmp AS $_id => $_data) {
				$list[$_id] = $this->_format_sign_detail($_data);
			}
			unset($tmp);
		}

		return array($total, $multi, $list);
	}

	/**
	 * 提交重设签到状态
	 * @param array $record
	 */
	protected function _reset_sign_status_submit($cp_pluginid, $record, $check_reason = true) {
		$sr_id = $record['sr_id'];
		$sr_status = $this->request->post('sr_status');
		$sd_reason = $this->request->post('sd_reason');
		if (!is_scalar($sr_status) || !is_scalar($sd_reason)) {
			$this->message('error', '提交数据格式错误');
		}
		if (!isset($this->_sign_status[$sr_status])) {
			$this->message('error', '新签到状态设置错误');
		}
		if ($sr_status == $record['sr_status']) {
			$this->message('error', '签到状态未发生变更无须进行提交');
		}
		if ($check_reason && !validator::is_len_in_range($sd_reason, 1, 255)) {
			$this->message('error', '备注说明文字必须要填写且长度限制在 255字节以内');
		}
		$sd_reason = $this->_sign_status[$record['sr_status']] . '-' . $this->_sign_status[$sr_status] . "\r\n" . $sd_reason;

		try {
			$this->_service_single('sign_detail', $cp_pluginid, 'begin', null);

			$this->_service_single('sign_detail', $cp_pluginid, 'insert', array(
				'sr_id' => $sr_id,
				'sd_reason' => $sd_reason
			));

			$this->_service_single('sign_record', $cp_pluginid, 'update', array('sr_status' => $sr_status), array('sr_id' => $sr_id));

			$this->_service_single('sign_detail', $cp_pluginid, 'commit', null);
		} catch (Exception $e) {
			$this->_service_single('sigin_detail', $cp_pluginid, 'rollback', null);
			logger::error($e);
			throw new controller_exception($e->getMessage(), $e->getCode());
		}

		$this->message('success', '重设签到状态操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('sr_id' => $sr_id))));
	}

}

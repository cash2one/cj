<?php
/**
 * voa_c_admincp_office_vote_verify
 * 企业后台/应用宝/微评选/审核投票
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_vote_verify extends voa_c_admincp_office_vote_base {

	public function execute() {

		$set = $this->request->get('set');
		$v_id = $this->request->get('v_id');
		if (!$v_id || !($vote = parent::_get_vote($this->_module_plugin_id, $v_id))) {
			$this->message('error', '指定评选不存在或已删除');
		}

		if (!is_scalar($set) || !isset($this->_vote_status[$set])) {
			$this->message('error', '设置评选状态值出错');
		}

		if ($set != $vote['v_status']) {
			$this->_service_single('vote', $this->_module_plugin_id, 'update', array('v_status' => $set), array('v_id' => $v_id));
		}

		$this->message('success', '评选状态设置操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);

	}

}

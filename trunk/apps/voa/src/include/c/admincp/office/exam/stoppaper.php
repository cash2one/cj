<?php
/**
* 终止试卷
* Create By wogu
* $Author$
* $Id$
*/
class voa_c_admincp_office_exam_stoppaper extends voa_c_admincp_office_exam_base {

	public function execute() {
		$id = intval($this->request->get('id'));
		if (!$id) {
			$this->message('error', '请指定要终止的试卷');
		}
		$reason = htmlspecialchars($this->request->get('reason'));
		$uda = &uda::factory('voa_uda_frontend_exam_paper');
		if ($uda->stop_paper($id, $reason, $this->_module_plugin['cp_agentid'], $this->_setting['domain'], $this->_user['ca_username'])) {
			$this->message('success', '指定试卷终止完毕', $this->cpurl($this->_module, $this->_operation, 'paperlist', $this->_module_plugin_id));
		} else {
			$this->message('error', '指定试卷终止失败');
		}
	}

}

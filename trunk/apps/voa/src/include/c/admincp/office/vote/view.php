<?php
/**
 * voa_c_admincp_office_vote_view
 * 企业后台/应用宝/微评选/浏览投票详情
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_vote_view extends voa_c_admincp_office_vote_base {

	public function execute() {

		$v_id = $this->request->get('v_id');
		$v_id = rintval($v_id, false);
		if ($v_id < 1 || !($vote = parent::_get_vote($this->_module_plugin_id, $v_id))) {
			$this->message('error', '指定投票不存在或已被删除');
		}

		$this->view->set('vote', $vote);

		/** 投票选项 */
		$options = $this->_get_vote_option($this->_module_plugin_id, $v_id);
		$this->view->set('options', $options);
		/** 允许投票的用户列表 */
		$this->view->set('permitUsers', $this->_get_vote_permit_user($this->_module_plugin_id, $v_id));
		/** 投票记录 */
		$perpage = 10;
		list($total, $multi, $voteList) = $this->_get_vote_mem($this->_module_plugin_id, $v_id, $perpage, $options);
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('voteList', $voteList);
		$this->view->set('editUrl', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('v_id' => $v_id)));


		$this->output('office/vote/view');

	}

}

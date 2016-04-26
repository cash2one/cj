<?php
/**
 * refuse.php
 * 执行人操作：拒绝接单
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_workorder_post_refuse extends voa_c_api_workorder_abstract {

	public function execute() {

		// 参数
		$fields = array(
			// 工单ID
			'id' => array('type' => 'int', 'required' => true),
			// 拒绝接单的原因
			'reason' => array('type' => 'string_trim', 'required' => true)
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 待传送给执行方法的参数
		$request = array(
			'woid' => $this->_params['id'],
			'uid' => $this->_member['m_uid'],
			'action' => voa_d_oa_workorder::ACTION_REFUSE,
			'admin' => false,
			'data' => array(
				'reason' => $this->_params['reason']
			),
		);
		// 请求统一处理方法类
		$uda_operate = &uda::factory('voa_uda_frontend_workorder_action_operate');
		if (!$uda_operate->go($request)) {
			$this->_errcode = $uda_operate->errcode;
			$this->_errmsg = $uda_operate->errmsg;
			return false;
		}
		// 发送消息
		$uda_wxqynotice = &uda::factory('voa_uda_frontend_workorder_wxqynotice');
		$uda_wxqynotice->refuse($this->_params['id'], $this->_member['m_uid'], $this->session);

		// 无具体的返回
		$this->_result = array();

		return true;
	}

}

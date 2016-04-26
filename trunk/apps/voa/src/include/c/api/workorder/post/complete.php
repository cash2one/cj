<?php
/**
 * complete.php
 * 执行人操作：完成工单执行
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_workorder_post_complete extends voa_c_api_workorder_abstract {

	public function execute() {

		// 参数
		$fields = array(
			// 工单ID
			'id' => array('type' => 'int', 'required' => true),
			// 完成说明
			'caption' => array('type' => 'string_trim', 'required' => true),
			// 上传的附件ID列表，字符串，多个id之间使用半角逗号分隔
			'at_ids' => array('type' => 'string_trim', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 待传送给执行方法的参数
		$request = array(
			'woid' => $this->_params['id'],
			'uid' => $this->_member['m_uid'],
			'action' => voa_d_oa_workorder::ACTION_COMPLETE,
			'admin' => false,
			'data' => array(
				'caption' => $this->_params['caption'],
				'at_ids' => $this->_params['at_ids']
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
		$uda_wxqynotice->complete($this->_params['id'], $this->_member['m_uid'], $this->session);

		// 无返回
		$this->_result = array();

		return true;
	}

}

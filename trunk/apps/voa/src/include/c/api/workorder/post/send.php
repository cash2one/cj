<?php
/**
 * send.php
 * 派单 - 新派单
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_workorder_post_send extends voa_c_api_workorder_abstract {

	public function execute() {

		// 需要的参数
		$fields = array(
			// 接收人
			'receiver_uids' => array('type' => 'string_trim', 'required' => true),
			// 工单备注
			'remark' => array('type' => 'string_trim', 'required' => true),
			// 联系人
			'contacter' => array('type' => 'string_trim', 'required' => true),
			// 联系电话
			'phone' => array('type' => 'string_trim', 'required' => true),
			// 联系地址
			'address' => array('type' => 'string_trim', 'required' => true),
		);

		// 基本验证检查
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 工单数据
		$data = $this->_params;
		$data['uid'] = $this->_member['m_uid'];

		// 新工单ID
		$woid = 0;
		// 新工单信息
		$workorder = array();

		// 建立新工单
		$uda_send = &uda::factory('voa_uda_frontend_workorder_action_send');
		if (!$uda_send->create($data, $workorder)) {
			$this->_errcode = $uda_send->errcode;
			$this->_errmsg = $uda_send->errmsg;
			return false;
		}

		// 格式化工单输出
		$uda_format = &uda::factory('voa_uda_frontend_workorder_format');
		// 格式化后的工单信息
		$format = array();
		$uda_format->workorder($workorder, $format, $this->_date_format);
		// 输出结果
		$this->_result = $format;

		// 发送消息
		$uda_wxqynotice = &uda::factory('voa_uda_frontend_workorder_wxqynotice');
		$uda_wxqynotice->create($workorder['woid'], $this->_member['m_uid'], $this->session);

		return true;
	}

}

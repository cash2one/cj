<?php
/**
 * config.php
 * 移动派单 - 配置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_workorder_get_config extends voa_c_api_workorder_abstract {

	public function execute() {

		// 不需要外部参数
		$uda_get = &uda::factory('voa_uda_frontend_workorder_get');
		$plugin_setting = $uda_get->plugin_setting;

		$this->_result = array(
			'wostate' => (array)$uda_get->wostate,// 工单状态名映射
			'worstate' => (array)$uda_get->worstate,// 接收人状态名映射
			'action' => (array)$uda_get->actions,// 操作动作名映射

			'role' => (array)$uda_get->attachment_roles,// 当前浏览者角色身份

			'complete_max_attachment' => (int)voa_d_oa_workorder_attachment::COUNT_MAX,// 完成工单最多上传的附件数
			'complete_min_attachment' => (int)$plugin_setting['complete_upload_count_min'],// 完成工单最少上传的附件数

			'send_max_attachment' => (int)voa_d_oa_workorder_attachment::COUNT_MAX,// 派单时最多上传的附件数
			'send_min_attachment' => (int)$plugin_setting['send_upload_count_min'],// 派单时最少上传的附件数

			'address_length_max' => (int)$plugin_setting['rule_address'][1],// 联系地址最大长度
			'address_length_min' => (int)$plugin_setting['rule_address'][0],// 联系地址最短长度

			'caption_length_max' => (int)$plugin_setting['rule_caption'][1],// 工单完成说明最大长度
			'caption_length_min' => (int)$plugin_setting['rule_caption'][0],// 工单完成说明最短长度

			'contacter_length_max' => (int)$plugin_setting['rule_contacter'][1],// 联系人最大长度
			'contacter_length_min' => (int)$plugin_setting['rule_contacter'][0],// 联系人最短长度

			'phone_length_max' => (int)$plugin_setting['rule_phone'][1],// 联系电话最大长度
			'phone_length_min' => (int)$plugin_setting['rule_phone'][0],// 联系电话最短长度

			'reason_length_max' => (int)$plugin_setting['rule_reason'][1],// 操作原因最大长度
			'reason_length_min' => (int)$plugin_setting['rule_reason'][0],// 操作原因最短长度

			'remark_length_max' => (int)$plugin_setting['rule_remark'][1],// 工单备注最大长度
			'remark_length_min' => (int)$plugin_setting['rule_remark'][0],// 工单备注最短长度

		);

		return true;
	}

}

<?php
/**
 * voa_c_admincp_setting_secret_modify
 * 企业后台/系统设置/秘密/更改设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_tool_secret_setting extends voa_c_admincp_setting_base {

	public function execute() {

		/** 当前操作的数据表 */
		$this->_current_operation_table = 'secret';

		/** 定义变量设置数组 */
		$this->_current_keys_setting = array(
				/*
				'perpage' => array(
						'type' => 'number',
						'id' => 'perpage',
						'name' => 'perpage',
						'comment' => '每页显示的秘密主题数量',
						'title' => '每页显示主题数',
						'max' => 30,
						'min' => 1
				),*/
				/*
				'is_secret' => array(
						'type' => 'yesorno',
						'id' => 'is_secret',
						'name' => 'is_secret',
						'comment' => '选择“是”，则全部发言为匿名状态',
						'title' => '是否匿名发言',
				),
				*/
		);

		/** 以后动作交由 voa_c_admincp_setting_base->_after_action()方法来接管 */

	}

	/**
	 * 验证变量值
	 */
	protected function _validator_setting_value() {
		$setting = $this->_current_keys_setting;
		if (isset($this->_current_change_data['perpage'])) {
			if (!validator::is_int($this->_current_change_data['perpage'])) {
				$this->message('error', $setting['perpage']['title'].' 必须为大于零的整数');
			}
			if ($this->_current_change_data['perpage'] < $setting['perpage']['min'] || $this->_current_change_data['perpage'] > $setting['perpage']['max']) {
				$this->message('error', $setting['perpage']['title'].' 应该设置为'.$setting['perpage']['min'].'到'.$setting['perpage']['max'].'之间的整数');
			}
		} elseif (isset($this->_current_change_data['is_secret'])) {
			//$this->_current_change_data['is_secret'] = $this->_current_change_data['is_secret'] ? 1 : 0;
		}
	}

}

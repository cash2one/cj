<?php
/**
 * voa_c_admincp_setting_vnote_modify
 * 企业后台/系统设置/备忘录/设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_vnote_setting extends voa_c_admincp_setting_base {

	public function execute() {

		/** 当前操作的数据表 */
		$this->_current_operation_table = 'vnote';

		/** 定义变量设置数组 */
		$this->_current_keys_setting = array(
				/*
				'perpage' => array(
						'type' => 'number',
						'id' => 'perpage',
						'name' => 'perpage',
						'comment' => '默认：10',
						'title' => '每页分页数',
						'max' => 50,
						'min' => 1
				),*/
		);

		/** 以后动作交由 voa_c_admincp_setting_base->_after_action()方法来接管 */

	}

	protected function _validator_setting_value() {
		foreach ($this->_current_change_data as $key => $value) {
			if (!isset($this->_current_keys_setting[$key])) {
				unset($this->_current_change_data[$key]);
				continue;
			}
			$setting = $this->_current_keys_setting[$key];
			if ($setting['type'] == 'number') {
				$value = trim($value);
				if (!validator::is_int($value)) {
					$this->message('error', $setting['title'].' 必须为大于零的整数');
				}
				if (isset($setting['min']) && $value < $setting['min']) {
					$this->message('error', $setting['title'].' 应该设置为'.$setting['min'].'到'.$setting['max'].'之间的整数');
				}
				if (isset($setting['max']) && $value > $setting['max']) {
					$this->message('error', $setting['title'].' 应该设置为'.$setting['min'].'到'.$setting['max'].'之间的整数');
				}
				$this->_current_change_data[$key] = $value;
			} elseif ($setting['type'] == 'yesorno') {
				$this->_current_change_data[$key] = $value ? 1 : 0;
			}
		}
	}

}

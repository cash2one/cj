<?php
/**
 * voa_c_cyadmin_setting_common_modify
 * 主站后台/系统设置/系统环境设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_setting_common_modify extends voa_c_cyadmin_setting_base {

	public function execute() {

		// 当前操作的数据表
		$this->_current_operation_table = 'common';

		// 定义变量设置数组
		$this->_current_keys_setting = array(
				'dateformat' => array(
						'type' => 'text',
						'id' => 'dateformat',
						'name' => 'dateformat',
						'placeholder' => 'Y-m-d',
						'comment' => '日期格式设置，默认为：Y-m-d',
						'title' => '日期格式设置'
				),
				'timeformat' => array(
						'type' => 'text',
						'id' => 'timeformat',
						'name' => 'timeformat',
						'placeholder' => 'H:i',
						'comment' => '时间格式设置，默认为：H:i',
						'title' => '时间格式设置'
				),
				'sitename' => array(
						'type' => 'text',
						'id' => 'sitename',
						'name' => 'sitename',
						'placeholder' => '输入您的网站名称',
						'title' => '网站名称'
				),
		);

		/** 以后动作交由 voa_c_cyadmin_setting_base->_after_action()方法来接管 */

	}

	/**
	 * 检查common变量值
	 */
	protected function _validator_setting_value(){
		if (isset($this->_current_change_data['dateformat'])) {
			$this->_current_change_data['dateformat'] = trim($this->_current_change_data['dateformat']);
			if (!preg_match('/^[dmnjy\-]+$/i', $this->_current_change_data['dateformat'])) {
				$this->message('error', '请正确设置日期格式，如：Y-m-d');
			}
			if ($this->_current_change_data['dateformat'] != rhtmlspecialchars($this->_current_change_data['dateformat'])) {
				$this->message('error', '日期格式内禁止包含特殊字符');
			}
			$this->_current_change_data['dateformat'] =
			str_ireplace(array('mm', 'dd', 'yyyy', 'yy'), array('n', 'j', 'Y', 'y'), $this->_current_change_data['dateformat']);
		}
		if (isset($this->_current_change_data['timeformat'])) {
			$this->_current_change_data['timeformat'] = trim($this->_current_change_data['timeformat']);
			if (!preg_match('/^[his\:]+$/i', $this->_current_change_data['timeformat'])) {
				$this->message('error', '请正确设置时间格式，如：H:i');
			}
			$this->_current_change_data['timeformat'] = str_ireplace(
					array('hh', 'ii', 'ss', 'HH'), array('H', 'i', 's', 'H'), $this->_current_change_data['timeformat']);
		}
		if (isset($this->_current_change_data['sitename'])) {
			$this->_current_change_data['sitename'] = trim($this->_current_change_data['sitename']);
			if (!validator::is_len_in_range($this->_current_change_data['sitename'], 2, 100)) {
				$this->message('error', '网站名称长度应该介于2到100字节之间');
			}
		}
	}

}

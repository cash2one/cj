<?php
/**
 * voa_c_admincp_setting_project_modify
 * 企业后台/系统设置/工作台:设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_project_setting extends voa_c_admincp_setting_base {

	public function execute() {

		/** 当前操作的数据表 */
		$this->_current_operation_table = 'project';

		/** 定义变量设置数组 */
		$this->_current_keys_setting = array(
			'procvs' => array(
				'type' => 'text',
				'id' => 'procvs',
				'name' => 'procvs',
				'comment' => '设置可选的进度值，每个数值时间使用半角逗号分隔，如：20,40,60,80,100。注：设置太多数值可能不利于员工操作。',
				'title' => '设置进度可选值'
			),
			'upload_image' => array(
				'type' => 'yesorno',
				'id' => 'upload_image',
				'name' => 'upload_image',
				'comment' => '选择“是”则发布新任务时允许上传图片，否则不允许。',
				'title' => '是否允许上传任务图片'
			),
			'upload_image_min_count' => array(
				'type' => 'number',
				'id' => 'upload_image_min_count',
				'name' => 'upload_image_min_count',
				'comment' => '如果设置非零的正整数（不能大于5），则要求员工必须至少上传该数量的图片。设置为“0”，则不做限制。',
				'title' => '要求至少上传的图片数'
			),
			'upload_image_max_count' => array(
				'type' => 'number',
				'id' => 'upload_image_max_count',
				'name' => 'upload_image_max_count',
				'comment' => '最多允许上传的图片数，请设置5以内的正整数',
				'title' => '最多允许上传的图片数'
			)
		);

		/** 以后动作交由 voa_c_admincp_setting_base->_after_action()方法来接管 */

	}

	/**
	 * 验证变量值
	 */
	protected function _validator_setting_value() {

		$setting = $this->_current_keys_setting;
		// 检查进度值
		if (isset($this->_current_change_data['procvs'])) {
			$title = $setting['procvs']['title'];
			$value = $this->_current_change_data['procvs'];
			$value = trim($value);
			$value = str_replace('，', ',', $value);
			$value = preg_replace('/[^0-9,]/', '', $value);
			$value = preg_replace('/,+/', ',', $value);
			$parse = explode(',', $value);
			sort($parse);
			$value = array();
			foreach ($parse as $num) {
				if (isset($value[$num]) || !$num) {
					continue;
				}

				if (!validator::is_int($num) || $num < 1) {
					$this->message('error', $title.' 内必须使用正整数');
					break;
				}

				if ($num > 100) {
					$this->message('error', $title.' 内的数字不能大于100');
					break;
				}

				$value[$num] = $num;
			}

			if (count($value) > 100) {
				$this->message('error', $title.' 必须至少设置2个数字');
			}

			if (!isset($value[100])) {
				$this->message('error', $title.' 必须以100结束');
			}

			sort($value);
			$this->_current_change_data['procvs'] = implode(',', $value);
		}

		if (isset($this->_current_change_data['upload_image'])) {
			$this->_current_change_data['upload_image'] = $this->_current_change_data['upload_image'] ? 1 : 0;
		}

		if (isset($this->_current_change_data['upload_image_max_count'])) {
			$this->_current_change_data['upload_image_max_count'] = (int)$this->_current_change_data['upload_image_max_count'];
			if ($this->_current_change_data['upload_image_max_count'] > 5 || $this->_current_change_data['upload_image_max_count'] <= 0) {
				$this->_current_change_data['upload_image_max_count'] = 5;
			}
		}

		if (isset($this->_current_change_data['upload_image_min_count'])) {
			$this->_current_change_data['upload_image_min_count'] = (int)$this->_current_change_data['upload_image_min_count'];
			if ($this->_current_change_data['upload_image_min_count'] > 5 || $this->_current_change_data['upload_image_min_count'] < 0) {
				$this->_current_change_data['upload_image_min_count'] = 0;
			}
			if (isset($this->_current_change_data['upload_image_max_count']) && $this->_current_change_data['upload_image_max_count'] < $this->_current_change_data['upload_image_min_count']) {
				$this->_current_change_data['upload_image_min_count'] = 0;
			}
		}

	}

}

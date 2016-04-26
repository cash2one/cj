<?php
/**
 * voa_c_admincp_setting_askfor_modify
 * 企业后台/系统设置/审批流/设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askfor_setting extends voa_c_admincp_setting_base {

	public function execute() {

		/** 当前操作的数据表 */
		$this->_current_operation_table = 'askfor';

		/** 定义变量设置数组 */
		$this->_current_keys_setting = array(
			/*
			 'perpage' => array(
			 	'type' => 'number',
			 	'id' => 'perpage',
			 	'name' => 'perpage',
			 	'comment' => '微信界面每页显示的审批数量',
			 	'title' => '每页显示数',
			 	'max' => 30,
			 	'min' => 1
			 ),*/
			'upload_image' => array(
				'type' => 'yesorno',
				'id' => 'upload_image',
				'name' => 'upload_image',
				'comment' => '选择“是”则发布新审批时允许上传图片，否则不允许。',
				'title' => '是否允许上传审批图片'
			),
			'upload_image_min_count' => array(
				'type' => 'number',
				'id' => 'upload_image_min_count',
				'name' => 'upload_image_min_count',
				'comment' => '如果设置非零的正整数（不能大于10），则要求员工必须至少上传该数量的图片。设置为“0”，则不做限制。',
				'title' => '要求至少上传的图片数'
			),
			'upload_image_max_count' => array(
				'type' => 'number',
				'id' => 'upload_image_max_count',
				'name' => 'upload_image_max_count',
				'comment' => '最多允许上传的图片数，请设置10以内的正整数',
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
		if (isset($this->_current_change_data['perpage'])) {
			if (!validator::is_int($this->_current_change_data['perpage'])) {
				$this->message('error', $setting['perpage']['title'].' 必须为大于零的整数');
			}
			if ($this->_current_change_data['perpage'] < $setting['perpage']['min'] || $this->_current_change_data['perpage'] > $setting['perpage']['max']) {
				$this->message('error', $setting['perpage']['title'].' 应该设置为'.$setting['perpage']['min'].'到'.$setting['perpage']['max'].'之间的整数');
			}
		}
	}

}
